<?php

use Api\DataFromDeltaApi;
use Api\SaveDataFromDeltaToDb;
use Dotenv\Dotenv;

require '../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$errors = 0;
$getData = new DataFromDeltaApi();
$saveDataFromDeltaToDb = new SaveDataFromDeltaToDb();
for ($i = 1 ; $i <= 30 ; $i++) {
    $dataFromDelta = $getData->getDataFromDeltaApi();
    $decodedDeltaData = json_decode($dataFromDelta);

    if ($decodedDeltaData->code != 0) {
        $errors++;
        if ($errors >= 2) {
            die;
        }
        sleep(10);
        continue;
    }

    $date = $saveDataFromDeltaToDb->getDataOfExecute();
    $saveDataFromDeltaToDb->saveData($date, $decodedDeltaData->data);
    sleep(10);
}