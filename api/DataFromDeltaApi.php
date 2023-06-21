<?php

namespace Api;

class DataFromDeltaApi
{
    public function getDataFromDeltaApi()
    {
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL            => "https://api.ecoflow.com/iot-service/open/api/device/queryDeviceQuota?sn=" . $_ENV['DELTA_SERIAL_NUMBER'],
                CURLOPT_HEADER         => "0",
                CURLOPT_HTTPHEADER     => $this->getHeaderValueForAPi(),
                CURLOPT_RETURNTRANSFER => 1,
            ]
        );
        $response = curl_exec($ch);

        return $response;
    }

    private function getHeaderValueForAPi()
    {
        $array = [
            'Content-Type: application/json',
            'appKey: ' . $_ENV['DELTA_APP_KEY'],
            'secretKey: ' . $_ENV['DELTA_SECRET_KEY'],
        ];

        return $array;
    }
}