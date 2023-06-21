<?php

namespace Api;

use DateTime;
use Model\DeltaChargeModel;
use Model\ExtraBatteryModel;
use Model\HistoryModel;

class DataFromSaveHistory
{
    private $date;

    public function __construct(array $data = [])
    {
        $this->date = date('Y-m-d');
        if (!empty($data['date'])) {
            $date = date('Y-m-d', strtotime($data['date']));
            if ($date < $this->date) {
                $this->date = $date;
            }
        }
    }

    public function getDataFromDateSum(): string
    {
        $input = 0;
        $output = 0;

        $historyModel = new HistoryModel();
        $historyData = $historyModel->getHistoryFromDate($this->date);

        foreach ($historyData as $data) {
            $minutes = [];
            $inputHour = 0;
            $outputHour = 0;
            foreach ($data as $row) {
                $minutes[$row['minute']] = 1;
                $inputHour += $row['wats_in'];
                $outputHour += $row['wats_out'];
            }
            $input += $inputHour / count($data) * count($minutes) / 60;
            $output += $outputHour / count($data) * count($minutes) / 60;
        }

        $deltaChargeModel = new DeltaChargeModel();
        $countedDataBeforeConnect = $deltaChargeModel->getCountedDataFromDate($this->date);
        if (!empty($countedDataBeforeConnect)) {
            if ($countedDataBeforeConnect > 0) {
                $input += $countedDataBeforeConnect['counted_wats'];
            } else {
                $output += abs($countedDataBeforeConnect['counted_wats']);
            }
        }

        $extraBatterySum = $this->getSumDataFromExtraBattery($this->date);
        if ($extraBatterySum > 0) {
            $output -= $extraBatterySum;
        }

        return json_encode(['input' => $input, 'output' => $output]);
    }

    public function getDataFromTodayHourByHour(): string
    {
        $dataHourByHour = [];
        $dataFromExtraBatteryByHour = [];

        $historyModel = new HistoryModel();
        $historyData = $historyModel->getHistoryFromDate($this->date);
        foreach ($historyData as $hour => $data) {
            $minutes = [];
            $inputHour = 0;
            $outputHour = 0;
            foreach ($data as $row) {
                $minutes[$row['minute']] = 1;
                $inputHour += $row['wats_in'];
                $outputHour += $row['wats_out'];
            }
            $dataHourByHour[$hour]['input'] = $inputHour / count($data) * count($minutes) / 60;
            $dataHourByHour[$hour]['output'] = $outputHour / count($data) * count($minutes) / 60;
        }

        $extraBatteryModel = new ExtraBatteryModel();
        $extraBatteryData = $extraBatteryModel->getDataFromExtraBattery($this->date);
        foreach ($extraBatteryData as $hour => $data) {
            $minutes = [];
            $outputHour = 0;
            foreach ($data as $row) {
                $minutes[$row['minute']] = 1;
                $outputHour += $row['wats'];
            }
            $dataFromExtraBatteryByHour[$hour]['output'] = $outputHour / count($data) * count($minutes) / 60;
        }
        foreach ($dataFromExtraBatteryByHour as $hour => $data) {
            $dataHourByHour[$hour]['output'] -= $data['output'];
            if ($dataHourByHour[$hour]['output'] < 0) {
                $dataHourByHour[$hour]['output'] = 0;
            }
        }

        return json_encode($dataHourByHour);
    }

    public function getSumDataFromExtraBattery(string $date): float
    {
        $extraBatterySum = 0;
        $extraBatteryModel = new ExtraBatteryModel();
        $extraBatteryData = $extraBatteryModel->getDataFromExtraBattery($date);

        foreach ($extraBatteryData as $data) {
            $minutes = [];
            $input = 0;
            foreach ($data as $row) {
                $minutes[$row['minute']] = 1;
                $input += $row['wats'];
            }
            $extraBatterySum += $input / count($data) * count($minutes) / 60;
        }

        return $extraBatterySum;
    }

    public function getDataFromExtraBatterySum(): string
    {
        $extraBatterySum = $this->getSumDataFromExtraBattery($this->date);

        return json_encode(['extraBatterySum' => $extraBatterySum]);
    }

    public function getActualData(): string
    {
        $dateStart = date('Y-m-d H:i:s', strtotime("-1 minutes"));

        $historyModel = new HistoryModel();
        $historyData = $historyModel->getLastRowFromHistory($dateStart);
        if ($historyData['input'] == 0) {
            $extraBatteryModel = new ExtraBatteryModel();
            $extraBatteryLastRow = $extraBatteryModel->getLastRowFromExtraBattery($dateStart);
            $historyData = array_merge($historyData, $extraBatteryLastRow);
        }

        return json_encode($historyData);
    }

    public function getCalculatedDataWithNoInternet()
    {
        $calculatedData = [];
        $lastData = [];
        $historyModel = new HistoryModel();
        $allDataFromHistory = $historyModel->getAllHistoryDataFromDay($this->date);
        foreach ($allDataFromHistory as $row) {
            if (!$lastData) {
                $lastData = $row;
                continue;
            }

            $startDate = new DateTime($lastData[$historyModel::COLUMN_DATE_TIME]);
            $sinceStart = $startDate->diff(new DateTime($row[$historyModel::COLUMN_DATE_TIME]));
            if ($sinceStart->h >= 1 || $sinceStart->i >= 1) {
                if ($lastData[$historyModel::COLUMN_HOUR] == $row[$historyModel::COLUMN_HOUR]) {
                    $countedWats = ($row[$historyModel::COLUMN_BATTERY_LEVEL] - $lastData[$historyModel::COLUMN_BATTERY_LEVEL]) * 10;
                    $calculatedData[$row[$historyModel::COLUMN_HOUR]]['countedWats'] += $countedWats;
                    if (!isset($calculatedData[$row[$historyModel::COLUMN_HOUR]]['startBattery'])) {
                        $calculatedData[$row[$historyModel::COLUMN_HOUR]]['startBattery'] = $lastData[$historyModel::COLUMN_BATTERY_LEVEL];
                    }
                    $calculatedData[$row[$historyModel::COLUMN_HOUR]]['endBattery'] = $row[$historyModel::COLUMN_BATTERY_LEVEL];
                } else {
                    $hoursToAdd = $row[$historyModel::COLUMN_HOUR] - $lastData[$historyModel::COLUMN_HOUR] + 1;
                    $countedWats = ($row[$historyModel::COLUMN_BATTERY_LEVEL] - $lastData[$historyModel::COLUMN_BATTERY_LEVEL]) * 10 / $hoursToAdd;
                    $hour = $lastData[$historyModel::COLUMN_HOUR];
                    do {
                        $calculatedData[$hour]['countedWats'] += $countedWats;
                        if (!isset($calculatedData[$row[$historyModel::COLUMN_HOUR]]['startBattery'])) {
                            $calculatedData[$hour]['startBattery'] = $lastData[$historyModel::COLUMN_BATTERY_LEVEL];
                        }
                        $calculatedData[$hour]['endBattery'] = $row[$historyModel::COLUMN_BATTERY_LEVEL];
                        $hour++;
                    } while ($hour <= $row[$historyModel::COLUMN_HOUR]);
                }
            }

            $lastData = $row;
        }

        return $calculatedData;
    }

    public function getDataForCountCharge()
    {

    }
}