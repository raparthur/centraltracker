<?php

namespace App\AppBundle;

abstract class AbstractDevice
{

    const UNDEFINED_TYPE = 0;
    const LOGIN_TYPE = 1;
    const HEARTBEAT_TYPE = 2;
    const TRACK_TYPE = 3;

    abstract static function parseResponse(string $data, string $googleApiKey): DeviceResponse;
    abstract static function extractDataType(string $data): int;

    static protected function gps2Map($lat, $long, $sn, $ew)
    {
        if (empty($lat) || empty($long) || empty($sn) || empty($ew)) {
            return array(false, false);
        }
        $DegreeMinLat = explode(".",$lat);
        $dgLat = intval($DegreeMinLat[0]/100);
        $minLat = substr($DegreeMinLat[0], -2).'.'.$DegreeMinLat[1];
        $minLat = round($minLat/60,6);
        $latitude = $dgLat+$minLat;

        $DegreeMinLong = explode(".",$long);
        $dgLong = intval($DegreeMinLong[0]/100);
        $minLong = substr($DegreeMinLong[0], -2).'.'.$DegreeMinLong[1];
        $minLong = round($minLong/60,6);
        $longitude = $dgLong+$minLong;

        $latitude = strtolower($sn) == 'n' ? $latitude : -$latitude;
        $longitude = strtolower($ew) == 'e' ? $longitude : -$longitude;

        return array($latitude, $longitude);
    }

    static protected function erb2Map($googleGeoApiKey, $cid, $lac, $mcc = 724, $mnc = 18)
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

}