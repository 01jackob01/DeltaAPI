<?php

namespace Api;

use Model\ConnectDb;
use Model\ExtraBatteryModel;

class SaveDataFromDeltaToDb extends ConnectDb
{
    public function saveData($date, $data)
    {
        if ((int)$date['hour'] >= 17 && (int)$data->wattsInSum > 70) {
            $extraBatteryModel = new ExtraBatteryModel();
            $extraBatteryModel->insertDataFromExtraBattery($date, $data);
            $data->wattsInSum = 0;
        }

        $sql = <<<SQL
INSERT INTO 
    history (battery_level, time_charge_discharge, wats_in, wats_out, date_time, hour, minute) 
VALUES
    ({$data->soc}, {$data->remainTime}, {$data->wattsInSum}, {$data->wattsOutSum}, '{$date['date']}', {$date['hour']}, {$date['minute']})
SQL;
        $this->db->query($sql);
    }

    public function getDataOfExecute()
    {
        $time = time();
        $hour = date('H', $time);
        $minute = date('i', $time);
        $date = date('Y-m-d H:i', $time);
        $dateSeconds = str_split(date('s', $time));
        if (count($dateSeconds) == 1) {
            if ($dateSeconds[0] < 5) {
                $date .= ':00';
            } else {
                $date .= ':05';
            }
        } else {
            if ($dateSeconds[1] < 5) {
                $date .= ':' . $dateSeconds[0] . '0';
            } else {
                $date .= ':' . $dateSeconds[0] . '5';
            }
        }

        return ['date' => $date, 'hour' => $hour, 'minute' => $minute];
    }
}