<?php

namespace Model;

class DeltaChargeModel extends ConnectDb
{
    CONST TABLE = 'delta_charge';

    const COLUMN_ID = 'id';
    const COLUMN_BATTERY_START = 'battery_start';
    const COLUMN_BATTERY_NOW = 'battery_now';
    const COLUMN_COUNTED_WATS = 'counted_wats';
    const COLUMN_DATE = 'date';
    const COLUMN_HOUR = 'hour';
    const COLUMN_TYPE = 'type';

    const TYPE_MORNING_COUNT = 1;
    const TYPE_MIDDLE_DAY = 2;

    public function getCountedDataFromDate(string $date): array
    {
        return $this->select(self::SHORT_ROW, ['SUM(counted_wats) as counted_wats'], self::TABLE, ['=' => ['date' => $date]]);
    }

    public function getHoursAddedToDb(string $date): array
    {
        return $this->select(self::SHORT_COLUMN, [self::COLUMN_HOUR], self::TABLE, ['=' => [self::COLUMN_DATE => $date], 'NOT NULL' => [self::COLUMN_HOUR => NULL]]);
    }

    public function insertCountedData(int $countedInput, int $batteryStart, int $batteryNow, string $type, int $hour = NULL): void
    {
        $insertData = [
            self::COLUMN_BATTERY_START => $batteryStart,
            self::COLUMN_BATTERY_NOW   => $batteryNow,
            self::COLUMN_COUNTED_WATS  => $countedInput,
            self::COLUMN_HOUR          => $hour,
            self::COLUMN_DATE          => date('Y-m-d'),
            self::COLUMN_TYPE          => $type,
        ];

        $this->insert(self::TABLE, $insertData);
    }

    public function updateCountedData(int $countedInput, int $batteryStart, int $batteryNow, int $hour = NULL): void
    {
        $updateData = [
            self::COLUMN_BATTERY_START => $batteryStart,
            self::COLUMN_BATTERY_NOW   => $batteryNow,
            self::COLUMN_COUNTED_WATS  => $countedInput,
        ];

        $this->update(self::TABLE, $updateData, ['=' => [self::COLUMN_DATE => date('Y-m-d'), self::COLUMN_HOUR => $hour]]);
    }
}