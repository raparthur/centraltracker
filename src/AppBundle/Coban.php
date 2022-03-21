<?php


namespace App\AppBundle;

use App\Entity\TrackEvent;

class Coban extends AbstractDevice
{
    static function parseResponse(string $data, string $googleApiKey): DeviceResponse
    {
        $dataType = self::extractDataType($data);

        $deviceResponse = new DeviceResponse();
        $deviceResponse->setEventType($dataType);

        switch ($dataType){
            case self::LOGIN_TYPE:
                $statusCode = 1;
                $statusMsg = 'success';
                $event = 'LOAD';
                $deviceResponse->setStatusCode($statusCode);
                $deviceResponse->setStatusMsg($statusMsg);
                $deviceResponse->setEvent($event);
                return $deviceResponse;
            case self::HEARTBEAT_TYPE:
                $statusCode = 1;
                $statusMsg = 'success';
                $event = 'ON';
                $deviceResponse->setStatusCode($statusCode);
                $deviceResponse->setStatusMsg($statusMsg);
                $deviceResponse->setEvent($event);
                return $deviceResponse;
            case self::TRACK_TYPE:
                $statusCode = 1;
                $statusMsg = 'success';
                $event = new TrackEvent();
                $dt = new \DateTime("now", new \DateTimeZone('America/Sao_Paulo'));
                $event->setCreatedAt($dt->format("Y-m-d H:i:s"));
                //########### TRACK INPUT VALIDATION ###############
                //must contain 19 data block separated by commas to be a status
                if (strlen($data) && $data[strlen($data)-1] != ";") {
                    $statusCode = -10;
                    $statusMsg = 'data log missing ";"';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    $deviceResponse->setEvent($event);
                    return $deviceResponse;
                }

                //eliminate last ";"
                $data = substr($data, 0, -1);
                $logAy = explode(",", $data);

                //must contain 19 data block separated by commas to be a status
                if (count($logAy) != 13 && count($logAy) != 19) {
                    $statusCode = -11;
                    $statusMsg = 'invalid track data';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    $deviceResponse->setEvent($event);
                    return $deviceResponse;
                }

                $imei = substr($logAy[0], strpos($logAy[0], "imei:") + 5, 15);
                //imei has size 15
                if (strlen($imei) != 15) {
                    $statusCode = -12;
                    $statusMsg = 'invalid imei';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    $deviceResponse->setEvent($event);
                    return $deviceResponse;
                }

                //####### SUCCESS, BUT HAS ONLY 13 FIELDS ############
                if ($statusCode == 1 && count($logAy) == 13) {
                    $logAy[13] = $logAy[14] = $logAy[15] = $logAy[16] = $logAy[17] = $logAy[18] = "";
                    $statusCode = 2;
                }
                //####################################################

                $keyword = $logAy[1];
                $localTime = $logAy[2];
                $cellphone = $logAy[3];
                $gpsIsValid = $logAy[4];
                $utc = $logAy[5];
                $av = $logAy[6];
                $latitude = $logAy[7];
                $sn = $logAy[8];
                $longitude = $logAy[9];
                $ew = $logAy[10];
                $speed = $logAy[11];
                $directionAddr = $logAy[12];
                $altitude = $logAy[13];
                $accState = $logAy[14] ? 1 : 0;
                $doorState = $logAy[15] ? 1 : 0;

                if(strtolower($gpsIsValid) == 'f' && strlen($longitude) != 11){
                    $statusCode = -13;
                    $statusMsg = 'longitude must have 11 characters, it has '.strlen($longitude);
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    $deviceResponse->setEvent($event);
                    return $deviceResponse;
                }

                $strdate = '20'.$localTime[0].$localTime[1].'-'.$localTime[2].$localTime[3].'-'.$localTime[4].$localTime[5].
                    ' '.$localTime[6].$localTime[7].':'.$localTime[8].$localTime[9].':'.$localTime[10].$localTime[11];

                if(!strtotime($strdate)){
                    $statusCode = -14;
                    $statusMsg = 'localtime is unreadable';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    $deviceResponse->setEvent($event);
                    return $deviceResponse;
                }

                //####### VALIDATION PASSED FROM HERE ON ##########
                $fstFuel = str_replace('%', '', $logAy[16]);
                $secFuel = str_replace('%', '', $logAy[17]);
                if (is_numeric($logAy[18])) {
                    $temperature = round($logAy[18], 2);
                } else {
                    $temperature = '';
                }

                if(strtolower($gpsIsValid) == 'f'){
                    //coban coordinates to google coordinates
                    $coord = self::gps2Map($latitude,$longitude,$sn,$ew);
                    $latitude = $coord[0];
                    $longitude = $coord[1];
                } else {
                    //coban LAC to google coordinates
                    $coord = self::erb2Map($googleApiKey,$longitude,$latitude);
                    if($coord['success']){
                        $latitude = $coord['latitude'];
                        $longitude = $coord['longitude'];
                    } else {
                        $statusCode = -15;
                        $statusMsg = 'ERB parse error: '.$coord['error'];
                        $deviceResponse->setStatusCode($statusCode);
                        $deviceResponse->setStatusMsg($statusMsg);
                        $deviceResponse->setEvent($event);
                        return $deviceResponse;
                    }
                }

                $deviceResponse->setStatusMsg($statusMsg);
                $deviceResponse->setStatusCode($statusCode);
                $event->setImei($imei);
                $event->setSimCard($cellphone);
                $event->setKeyword($keyword);
                $event->setDeviceTime($strdate);
                $event->setIsFromGps(strtolower($gpsIsValid) == 'f');
                $event->setLatitude($latitude);
                $event->setLongitude($longitude);
                $event->setSpeed($speed);
                $event->setDirection($directionAddr);
                $event->setAltitude($altitude);
                $event->setAccState($accState == 1);
                $event->setDoorState($doorState == 1);
                $event->setJammer($fstFuel);
                $event->setTemperature($temperature);
                $deviceResponse->setEvent($event);
                return $deviceResponse;
            //#########################################################
            default:
                $statusCode = 0;
                $statusMsg = 'could not handle input data';
                $event = null;
                $deviceResponse->setStatusCode($statusCode);
                $deviceResponse->setStatusMsg($statusMsg);
                $deviceResponse->setEvent($event);
                return $deviceResponse;

        }

    }

}