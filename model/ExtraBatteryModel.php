<?php

namespace Model;

class ExtraBatteryModel extends ConnectDb
{
    CONST TABLE = 'extra_battery';

    CONST COLUMN_ID = 'id';
    CONST COLUMN_WATS = 'wats';
    CONST COLUMN_DATE_TIME = 'date_time';
    CONST COLUMN_HOUR = 'hour';
    CONST COLUMN_MINUTE = 'minute';

    public function getDataFromExtraBattery(string $date): array
    {
        $data = [];
        $dateStart = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';
        $where = ['>' => ['date_time' => $dateStart], '<' => ['date_time' => $dateEnd]];
        $dataFromExtraBattery = $this->select(self::FULL_ARRAY, ['*'], self::TABLE, $where);
        foreach ($dataFromExtraBattery as $row) {
            $data[$row['hour']][] = $row;
        }

        return $data;
    }

    public function getLastRowFromExtraBattery(string $dateStart): array
    {
        $actualData = [
            'input' => 0,
            'type' => 'Solar',
        ];

        $select = [self::COLUMN_WATS, self::COLUMN_HOUR, self::COLUMN_MINUTE];
        $where = ['>' => ['date_time' => $dateStart]];
        $sort = ['sortBy' => 'id', 'sortOrder' => 'DESC'];
        $dataFromExtraBattery = $this->select(self::SHORT_ROW, $select, self::TABLE, $where, 1, $sort);

        if ($dataFromExtraBattery['hour'] == date('H') && ((int)$dataFromExtraBattery['minute'] - (int)date('i')) <= 1){
            $actualData['input'] = $dataFromExtraBattery['wats'];
            $actualData['type'] = 'Dodatkowa bateria';
        }

        return $actualData;
    }

    public function insertDataFromExtraBattery(array $date, object $data): void
    {
        $insertData = [
            self::COLUMN_WATS => $data->wattsInSum,
            self::COLUMN_DATE_TIME => $date['date'],
            self::COLUMN_HOUR => $date['hour'],
            self::COLUMN_MINUTE => $date['minute'],
        ];

        $this->insert(self::TABLE, $insertData);
    }
}