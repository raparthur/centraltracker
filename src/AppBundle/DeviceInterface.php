<?php

namespace App\AppBundle;

interface DeviceInterface
{
    const UNDEFINED_TYPE = 0;
    const LOGIN_TYPE = 1;
    const HEARTBEAT_TYPE = 2;
    const TRACK_TYPE = 3;

    static function parseResponse(string $data, string $googleApiKey): DeviceResponse;
    static function extractDataType(string $data): int;

}