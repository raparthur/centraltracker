<?php


namespace App\AppBundle;

use App\Entity\HeartbeatEvent;
use App\Entity\LoginEvent;
use App\Entity\TrackEvent;
use Twig\Error\RuntimeError;

class Coban extends AbstractDevice
{
    static function parseResponse(string $data, string $googleApiKey): DeviceResponse
    {
        $dataType = self::extractDataType($data);

        $deviceResponse = new DeviceResponse();
        $deviceResponse->setEventType($dataType);
        $dt = new \DateTime("now", new \DateTimeZone('America/Sao_Paulo'));
        $deviceResponse->setCreatedAt($dt->format("Y-m-d H:i:s"));

        switch ($dataType){
            case LoginEvent::TYPE:
                $event = new LoginEvent();
                $imei = substr($data, strpos($data, "##,imei:")+8,  15);
                if($imei && is_numeric($imei)){
                    $event->setImei($imei); //todo
                    $event->setResponse('LOAD');
                    $deviceResponse->setStatusCode(1);
                    $deviceResponse->setStatusMsg('success');
                    $deviceResponse->setEvent($event);
                } else {
                    $statusCode = -12;
                    $statusMsg = 'invalid imei';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                }
                return $deviceResponse;
            case HeartbeatEvent::TYPE:
                $event = new LoginEvent();
                $imei = substr($data, 0,  15);
                if($imei && is_numeric($imei)){
                    $event->setImei($imei); //todo
                    $event->setResponse('ON');
                    $deviceResponse->setStatusCode(1);
                    $deviceResponse->setStatusMsg('success');
                    $deviceResponse->setEvent($event);
                } else {
                    $statusCode = -12;
                    $statusMsg = 'invalid imei';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                }
                return $deviceResponse;
            case TrackEvent::TYPE:
                //########### TRACK INPUT VALIDATION ###############
                //must contain 19 data block separated by commas
                if (strlen($data) && $data[strlen($data)-1] != ";") {
                    $statusCode = -10;
                    $statusMsg = 'data log missing ";"';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
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
                    return $deviceResponse;
                }

                $imei = substr($logAy[0], strpos($logAy[0], "imei:") + 5, 15);
                //imei has size 15
                if (strlen($imei) != 15) {
                    $statusCode = -12;
                    $statusMsg = 'invalid imei';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    return $deviceResponse;
                }

                //####### SUCCESS, BUT HAS ONLY 13 FIELDS ############
                if (count($logAy) == 13) {
                    $logAy[13] = $logAy[14] = $logAy[15] = $logAy[16] = $logAy[17] = $logAy[18] = "";
                    $deviceResponse->setStatusCode(2);
                    $deviceResponse->setStatusMsg('track log has only 13 fields');
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

                if(strtolower($gpsIsValid) == 'f' && strlen($longitude) < 9){
                    $statusCode = -13;
                    $statusMsg = 'longitude must have at least 9 characters, it has '.strlen($longitude);
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    return $deviceResponse;
                }

                $event = new TrackEvent();

                if($localTime && is_array($localTime) && count($localTime) == 12){
                    $strdate = '20'.$localTime[0].$localTime[1].'-'.$localTime[2].$localTime[3].'-'.$localTime[4].$localTime[5].
                        ' '.$localTime[6].$localTime[7].':'.$localTime[8].$localTime[9].':'.$localTime[10].$localTime[11];

                    if(!strtotime($strdate)){
                        $statusCode = 3;
                        $statusMsg = 'localtime is unreadable';
                        $deviceResponse->setStatusCode($statusCode);
                        $deviceResponse->setStatusMsg($statusMsg);
                        $event->setDeviceTime(null);
                    } else {
                        $event->setDeviceTime($strdate);
                    }

                } else {
                    $statusCode = 3;
                    $statusMsg = 'localtime is unreadable';
                    $deviceResponse->setStatusCode($statusCode);
                    $deviceResponse->setStatusMsg($statusMsg);
                    $event->setDeviceTime(null);
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
                        $statusCode = -16;
                        $statusMsg = 'ERB parse error: '.$coord['error'];
                        $deviceResponse->setStatusCode($statusCode);
                        $deviceResponse->setStatusMsg($statusMsg);
                        return $deviceResponse;
                    }
                }

                $statusCode = 1;
                $statusMsg = 'success';
                $deviceResponse->setStatusCode($statusCode);
                $deviceResponse->setStatusMsg($statusMsg);
                $event->setImei($imei);
                $event->setSimCard($cellphone);
                $event->setKeyword($keyword);
                $event->setIsFromGps(strtolower($gpsIsValid) == 'f');
                $event->setLatitude($latitude);
                $event->setLongitude($longitude);
                $event->setSpeed($speed);
                $event->setDirection($directionAddr);
                $event->setAltitude($altitude);
                $event->setAccState($accState == 1);
                $event->setDoorState($doorState == 1);
                $event->setJammer($fstFuel);
                $event->setTowerSignal($secFuel);
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
                return $deviceResponse;

        }

    }

    static function extractDataType($data): int{

        $data = trim($data);

        //Matching parts of string. Must maintain the following check order since the first is contained in the next

        //check login: ##,imei:{var},A;
        if(strlen($data) == 26 && strpos($data, "##,imei:") !== false && strpos($data, ",A;") !== false){
            return LoginEvent::TYPE;
        }

        //check track: imei:{var};
        if(strlen($data) > 34 && strpos($data, "imei:") !== false && strpos($data, ";") !== false){
            return TrackEvent::TYPE;
        }

        //check heartbeat: {var};
        if(strlen($data) == 16 && strpos($data, ";") !== false){
            return HeartbeatEvent::TYPE;
        }

        return 0;
    }

}