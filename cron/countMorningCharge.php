<?php

require '../vendor/autoload.php';

use Model\DeltaChargeModel;
use Model\HistoryModel;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$historyModel = new HistoryModel();
$batteryState = $historyModel->getLastAndFirstBatteryState();
if ((int)$batteryState['today']['battery_level'] > (int)$batteryState['yesterday']['battery_level']) {
    $countedInput = ((int)$batteryState['today']['battery_level'] - (int)$batteryState['yesterday']['battery_level']) * 10;
    $deltaChargeModel = new DeltaChargeModel();
    $deltaChargeModel->insertCountedData(
        $countedInput,
        $batteryState['yesterday']['battery_level'],
        $batteryState['today']['battery_level'],
        $deltaChargeModel::TYPE_MORNING_COUNT
    );
}

echo 'Zakończono sprawdzanie';