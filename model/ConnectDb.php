<?php

namespace Model;

use mysqli;

class ConnectDb
{
    CONST SHORT = 0;
    CONST SHORT_COLUMN = 1;
    CONST SHORT_ROW = 2;
    CONST FULL_ARRAY = 3;

    public $db;

    public function __construct()
    {
        $this->db = $this->connectDb();
    }

    private function connectDb()
    {
        return new mysqli($_ENV['DB_SERVER'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_BASE'], $_ENV['DB_PORT']);
    }

    public function select(
        int $type,
        array $columns,
        string $table,
        array $where = [],
        int $limit = 0,
        array $sortArray = ['sortBy' => '']
    ) {
        $columnsToSql = implode(',', $columns);
        $whereToSql = isset($where) ? self::createWhere($where) : '';
        $sort = $this->createSortBy($sortArray);
        $limitSql = $this->createLimit($limit);

        $sql = <<<SQL
SELECT
    {$columnsToSql}
FROM
    {$table}
{$whereToSql}
{$sort}
{$limitSql}
SQL;

        return $this->selectDataByType($type, $sql);
    }

    public function insert(string $table, array $columnsAndData): void
    {
        $columns = implode(',', array_keys($columnsAndData));
        $values = implode("','", $columnsAndData);

        $sql = <<<SQL
INSERT INTO {$table} ({$columns}) VALUES ('{$values}')
SQL;

        $this->db->query($sql);
    }

    public function update(string $table, array $columnsAndData, array $whereCondition = []): void
    {
        $dataArray = [];

        foreach ($columnsAndData as $key=>$value) {
            $dataArray[] = $key . "=" . "'" . $value . "'";
        }
        $whereToSql = !empty($whereCondition) ? self::createWhere($whereCondition) : '';

        $sql = "UPDATE {$table} SET " . implode(',', $dataArray) . " " . $whereToSql . " LIMIT 1";

        $this->db->query($sql);
    }


    public function delete(int $dbType, string $table, array $whereCondition = [])
    {
        $this->db = $this->getDbToRunSql($dbType);

        $whereToSql = self::createWhere($whereCondition);

        $sql = <<<SQL
DELETE FROM {$table} {$whereToSql}
SQL;
        $this->db->query($sql);
    }

    public function selectDataByType(int $type, string $sql, int $dbType = 0)
    {
        switch ($type) {
            case self::SHORT:
                $dbData = $this->fetchOne($sql);
                break;
            case self::SHORT_COLUMN:
                $dbData = $this->fetchCol($sql);
                break;
            case self::SHORT_ROW:
                $dbData = $this->fetchRow($sql);
                break;
            case self::FULL_ARRAY:
                $dbData = $this->fetchArray($sql);
                break;
            default:
                $dbData = false;
        }

        return $dbData;
    }

    private function createWhere(array $where): string
    {
        $whereToSql = '';

        if (!empty($where)) {
            $part = 0;
            $whereToSql = 'WHERE';
            foreach ($where as $key => $whereVal) {
                foreach ($whereVal as $dbColumn => $value) {
                    if ($part) {
                        $whereToSql .= ' AND ';
                    }
                    if ($key == 'NOT NULL') {
                        $whereToSql .= " " . $dbColumn . " IS " . $key;
                    } else {
                        $whereToSql .= " " . $dbColumn . " " . $key .  " '" . $this->esc($value) . "' " ;
                    }
                    $part++;
                }
            }
        }

        return $whereToSql;
    }

    private function createSortBy(array $sortArray): string
    {
        $sort = '';

        if (!empty($sortArray['sortBy'])) {
            $sort = 'ORDER BY ' . $sortArray['sortBy'];
        }
        if (!empty($sortArray['sortOrder']) && !empty($sort)) {
            $sort .= ' ' . $sortArray['sortOrder'];
        }

        return $sort;
    }


    private function createLimit(int $limit): string
    {
        $limitSql = '';

        if ($limit > 0) {
            $limitSql = 'LIMIT ' . $limit;
        }

        return $limitSql;
    }


    private function fetchOne(string $sql)
    {
        $result = $this->db->query($sql);

        if($result===false || $this->numRows($result)==0) {
            return false;
        }

        $row = $result->fetch_row();;

        if ($row === false) {
            return false;
        }

        return $row[0];
    }

    private function fetchCol(string $sql)
    {
        $data = [];

        $result = $this->db->query($sql);

        if($result && $this->numRows($result) > 0) {
            while ($row = $result->fetch_array()) {
                $data[] = $row[0];
            }
        }

        return $data;
    }

    private function fetchRow(string $sql)
    {
        $result = $this->db->query($sql);

        if ($result === false || $this->numRows($result) == 0) {
            return false;
        }

        $row = $result->fetch_assoc();

        if($row === false) {
            return false;
        }

        return $row;
    }

    private function fetchArray(string $sql)
    {
        $data = [];
        $result = $this->db->query($sql);

        if($result && $this->numRows($result) > 0) {
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['id']) && !isset($data[$row['id']])) {
                    $data[$row['id']] = $row;
                } else {
                    $data[] = $row;
                }
            }
        }

        return $data;
    }

    private function esc(string $string)
    {
        return $this->db->real_escape_string($string);
    }

    private function numRows($res)
    {
        return $res->num_rows;
    }
}