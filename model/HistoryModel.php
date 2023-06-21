<?php

namespace Model;

class HistoryModel extends ConnectDb
{
    CONST TABLE = history;

    CONST COLUMN_ID = 'id';
    CONST COLUMN_BATTERY_LEVEL = 'battery_level';
    CONST COLUMN_TIME_CHARGE_DISCHARGE = 'time_charge_discharge';
    CONST COLUMN_WATS_IN = 'wats_in';
    CONST COLUMN_WATS_OUT = 'wats_out';
    CONST COLUMN_DATE_TIME = 'date_time';
    CONST COLUMN_HOUR = 'hour';
    CONST COLUMN_MINUTE = 'minute';

    public function getHistoryFromDate($date)
    {
        $data = [];
        $dateStart = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';
        $where = ['>' => ['date_time' => $dateStart], '<' => ['date_time' => $dateEnd]];
        $dataFromHistory = $this->select(self::FULL_ARRAY, ['*'], self::TABLE, $where);
        foreach ($dataFromHistory as $row) {
            $data[$row['hour']][] = $row;
        }

        return $data;
    }

    public function getLastRowFromHistory(string $dateStart)
    {
        $select = [self::COLUMN_WATS_IN, self::COLUMN_WATS_OUT, self::COLUMN_BATTERY_LEVEL];
        $where = ['>' => ['date_time' => $dateStart]];
        $sort = ['sortBy' => 'id', 'sortOrder' => 'DESC'];
        $dataFromHistory = $this->select(self::SHORT_ROW, $select, self::TABLE, $where, 1, $sort);

        $actualData['input'] = $dataFromHistory[self::COLUMN_WATS_IN] ?? 0;
        $actualData['output'] = $dataFromHistory[self::COLUMN_WATS_OUT] ?? 0;
        $actualData['battery'] = $dataFromHistory[self::COLUMN_BATTERY_LEVEL] ?? 0;
        $actualData['type'] = 'Solar';

        return $actualData;
    }

    public function getLastAndFirstBatteryState()
    {
        $data = [];
        $dateToday = date('Y-m-d 00:00:00');

        $sql = <<<SQL
SELECT
    *, 'today' as date_from
FROM
    history
WHERE
    id = (SELECT MIN(id) FROM history WHERE date_time > '{$dateToday}')
UNION
SELECT
    *, 'yesterday' as date_from
FROM
    history
WHERE
    id = (SELECT MAX(id) FROM history WHERE date_time < '{$dateToday}')
SQL;

        $dataForCount = $this->selectDataByType(self::FULL_ARRAY, $sql);
        foreach ($dataForCount as $row) {
            if ($row['date_from'] == 'today') {
                $data['today'] = $row;
            } else {
                $data['yesterday'] = $row;
            }
        }

        return $data;
    }

    public function getAllHistoryDataFromDay(string $date)
    {
        return $this->select(self::FULL_ARRAY, ['*'], self::TABLE, ['LIKE' => [self::COLUMN_DATE_TIME => $date . '%']]);
    }
}