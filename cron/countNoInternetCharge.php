<?php

use Api\DataFromSaveHistory;
use Dotenv\Dotenv;
use Model\DeltaChargeModel;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$deltaChargeModel = new DeltaChargeModel();
$dataFromSaveHistory = new DataFromSaveHistory();
$calculatedData = $dataFromSaveHistory->getCalculatedDataWithNoInternet();
$hoursAdded = $deltaChargeModel->getHoursAddedToDb(date('Y-m-d'));

foreach ($calculatedData as $hour => $dataFromHour) {
    if (in_array($hour, $hoursAdded)) {
        $deltaChargeModel->updateCountedData(
            $dataFromHour['countedWats'],
            $dataFromHour['startBattery'],
            $dataFromHour['endBattery'],
            $hour
        );
    } else {
        $deltaChargeModel->insertCountedData(
            $dataFromHour['countedWats'],
            $dataFromHour['startBattery'],
            $dataFromHour['endBattery'],
            $deltaChargeModel::TYPE_MIDDLE_DAY,
            $hour
        );
    }
}