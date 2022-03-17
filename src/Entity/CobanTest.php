<?php

namespace App\Entity;

use App\Repository\CobanTestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CobanTestRepository::class)]
class CobanTest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'smallint')]
    private $statusCode;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $statusMsg;

    #[ORM\Column(type: 'string', length: 15)]
    private $imei;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private $simCard;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $keyword;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deviceTime;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isFromGps;

    #[ORM\Column(type: 'string', length: 20)]
    private $latitude;

    #[ORM\Column(type: 'string', length: 20)]
    private $longitude;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $speed;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $direction;

    #[ORM\Column(type: 'float', nullable: true)]
    private $altitude;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $accState;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $doorState;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private $jammer;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $temperature;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function parse($data,$clientGoogleApiKey){

        //must contain 19 data block separated by commas to be a status
        if (strlen($data) && $data[strlen($data)-1] != ";") {
            $this->statusCode = -10;
            $this->statusMsg = 'data log missing ";"';
            return $this;
        }

        //eliminate last ";"
        $data = substr($data, 0, -1);

        $logAy = explode(",", $data);

        //must contain 19 data block separated by commas to be a status
        if (count($logAy) != 13 && count($logAy) != 19) {
            $this->statusCode = -11;
            $this->statusMsg = 'malformed or not compatible data log';
            return $this;
        }

        if (count($logAy) == 13) {
            $logAy[13] = $logAy[14] = $logAy[15] = $logAy[16] = $logAy[17] = $logAy[18] = "";
            $this->statusCode = 2;
            $this->statusMsg = 'warning: data has only 13 inputs';
        }

        $imei = substr($logAy[0], strpos($logAy[0], "imei:") + 5, 15);
        //imei has size 15
        if (strlen($imei) != 15) {
            $this->statusCode = -12;
            $this->statusMsg = 'malformed imei';
            return $this;
        }

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

        if(strlen($longitude) != 11){
            $this->statusCode = -13;
            $this->statusMsg = 'longitude must have 11 characters, it has '.strlen($longitude);
            return $this;
        }

        $strdate = '20'.$localTime[0].$localTime[1].'-'.$localTime[2].$localTime[3].'-'.$localTime[4].$localTime[5].
            ' '.$localTime[6].$localTime[7].':'.$localTime[8].$localTime[9].':'.$localTime[10].$localTime[11];

        if(!strtotime($strdate)){
            $this->statusCode = -14;
            $this->statusMsg = 'localtime is unreadable';
            return $this;
        }

        $fstFuel = str_replace('%', '', $logAy[16]);
        $secFuel = str_replace('%', '', $logAy[17]);
        if (is_numeric($logAy[18])) {
            $temperature = round($logAy[18], 2);
        } else {
            $temperature = '';
        }

        if(strtolower($gpsIsValid) == 'f'){
            $coord = $this->gps2Map($latitude,$longitude,$sn,$ew);
            $latitude = $coord[0];
            $longitude = $coord[1];
        } else {
            /**
             * SET IT FOR EACH CLIENT $clientGoogleApiKey
             */

            $coord = $this->erb2Map($clientGoogleApiKey,$longitude,$latitude);
            if($coord['success']){
                $latitude = $coord['latitude'];
                $longitude = $coord['longitude'];
            } else {
                $this->statusCode = -15;
                $this->statusMsg = 'ERB parse error: '.$coord['error'];
                return $this;
            }
        }

        $this->imei = $imei;
        $this->simCard = $cellphone;
        $this->keyword = $keyword;
        $this->deviceTime = $strdate;
        $this->isFromGps = strtolower($gpsIsValid) == 'f';
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->speed = $speed;
        $this->direction = $directionAddr;
        $this->altitude = $altitude;
        $this->accState = ($accState == 1);
        $this->doorState = ($doorState == 1);
        $this->jammer = ($fstFuel);
        $this->temperature = $temperature;
        $this->createdAt = date("Y-m-d H:i:s");
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusMsg(): ?string
    {
        return $this->statusMsg;
    }

    public function setStatusMsg(?string $statusMsg): self
    {
        $this->statusMsg = $statusMsg;

        return $this;
    }

    public function getImei(): ?string
    {
        return $this->imei;
    }

    public function setImei(string $imei): self
    {
        $this->imei = $imei;

        return $this;
    }

    public function getSimCard(): ?string
    {
        return $this->simCard;
    }

    public function setSimCard(?string $simCard): self
    {
        $this->simCard = $simCard;

        return $this;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(?string $keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getDeviceTime(): ?string
    {
        return $this->deviceTime;
    }

    public function setDeviceTime(?string $deviceTime): self
    {
        $this->deviceTime = $deviceTime;

        return $this;
    }

    public function getIsFromGps(): ?bool
    {
        return $this->isFromGps;
    }

    public function setIsFromGps(?string $isFromGps): self
    {
        $this->isFromGps = $isFromGps;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getSpeed(): ?string
    {
        return $this->speed;
    }

    public function setSpeed(?string $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(?string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getAltitude(): ?string
    {
        return $this->altitude;
    }

    public function setAltitude(?string $altitude): self
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getAccState(): ?string
    {
        return $this->accState;
    }

    public function setAccState(?string $accState): self
    {
        $this->accState = $accState;

        return $this;
    }

    public function getDoorState(): ?string
    {
        return $this->doorState;
    }

    public function setDoorState(string $doorState): self
    {
        $this->doorState = $doorState;

        return $this;
    }

    public function getJammer(): ?string
    {
        return $this->jammer;
    }

    public function setJammer(?string $jammer): self
    {
        $this->jammer = $jammer;

        return $this;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    private function gps2Map($lat, $long, $sn, $ew)
    {
        if (empty($lat) || empty($long) || empty($sn) || empty($ew)) {
            return array(false, false);
        }
        $latDg = substr($lat, 0, 2);
        $latMin = substr($lat, 2);
        $longDg = substr($long, 0, 3);
        $longMin = substr($long, 3);

        $latMin = round($latMin / 60, 6);
        $longMin = round($longMin / 60, 6);

        $latitude = strtolower($sn) == 'n' ? $latDg + $latMin : -($latDg + $latMin);
        $longitude = strtolower($ew) == 'e' ? $longDg + $longMin : -($longDg + $longMin);

        return array($latitude, $longitude);
    }

    private function erb2Map($googleGeoApiKey, $cid, $lac, $mcc = 724, $mnc = 18)
    {

        if (empty($cid) || empty($lac)) {
            return [
                'success' => false,
                'error' => 'Undefined CID/LAC',
                'latitude' => '',
                'longitude' => ''
            ];
        }

        $DadosLBS['homeMobileCountryCode'] = $mcc;
        $DadosLBS['homeMobileNetworkCode'] = $mnc;
        $DadosLBS['radioType'] = 'gsm';
        $DadosLBS['carrier'] = 'Vodafone';
        $DadosLBS['considerIp'] = false;
        $DadosLBS['cellTowers'] = [
            [
                'mobileCountryCode' => $mcc,
                'mobileNetworkCode' => $mnc,
                'locationAreaCode' => hexdec(strtolower($lac)),
                'cellId' => hexdec(strtolower($cid))
            ],
        ];


//Ver detalhes da API no https://developers.google.com/maps/documentation/geolocation/intro?hl=pt-br

        $service_url = "https://www.googleapis.com/geolocation/v1/geolocate";

//Chave de acesso
        $Curl_Data = array(
            'key' => $googleGeoApiKey
        );

        $CurlQueryString = http_build_query($Curl_Data);

//Preparando o método a ser enviado os dados
        $Metodo = array(
            CURLOPT_URL => $service_url . '?' . $CurlQueryString // Define URL to be called
        );

//Criando s string de dados
        $DadosPost = json_encode($DadosLBS);

//Preparando as opções padrões do CUrl
        $Curl_Adicional_Options = array(
            CURLOPT_CUSTOMREQUEST => "POST"
        , CURLOPT_POSTFIELDS => $DadosPost
        , CURLOPT_RETURNTRANSFER => true              // return web page
        , CURLOPT_CONNECTTIMEOUT => 15               // time-out on connect
        , CURLOPT_TIMEOUT => 15               // time-out on response
        , CURLOPT_FAILONERROR => true             //
        , CURLOPT_HEADER => false            // don't return headers
        , CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($DadosPost)
            ) // Dados para o cabeçalho do post
        , CURLOPT_FOLLOWLOCATION => true             // follow redirects
        , CURLOPT_MAXREDIRS => 10               // stop after 10 redirects
        , CURLOPT_SSL_VERIFYPEER => false
        , CURLOPT_SSL_VERIFYHOST => false
        );

        $Curl_Options = array_replace_recursive($Metodo, $Curl_Adicional_Options);

        $cURLConn = curl_init();
        curl_setopt_array($cURLConn, $Curl_Options);

        $vDados['Curl']['Output'] = curl_exec($cURLConn);
        $vDados['Curl']['Error'] = curl_error($cURLConn);
        $vDados['Curl']['ErrorNum'] = curl_errno($cURLConn);
        $vDados['Curl']['ErrorMsg'] = curl_strerror($vDados['Curl']['ErrorNum']);
        $vDados['Curl']['Info'] = curl_getinfo($cURLConn);

        curl_close($cURLConn);
        $lat = $lng = $error = '';

        if ($vDados['Curl']['ErrorNum'] != 0) {
            $success = false;
            $error = $vDados['Curl']['ErrorMsg'];
        } else {
            try {
                $success = true;
                $data = json_decode($vDados['Curl']['Output']);
                $lat = $data->location->lat;
                $lng = $data->location->lng;
            } catch (Exception $e) {
                $success = false;
                $error = "Error: Cannot create object";
            }
        }

        return [
            'success' => $success,
            'error' => $error,
            'latitude' => $lat,
            'longitude' => $lng
        ];
    }

    public function __toString()
    {
        return "[".$this->getStatusCode()."]".$this->getStatusMsg();
    }
}
