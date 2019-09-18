<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class DBManager
 */
class DBManager
{
    /**
     * @return array
     */
    public static function getTables()
    {
        $tables = [];
        $rows   = Shop::DB()->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'", 2);

        foreach ($rows as $row) {
            $tables[] = current($row);
        }

        return $tables;
    }

    /**
     * @param string $table
     * @return array
     */
    public static function getColumns($table)
    {
        $list    = [];
        $table   = Shop::DB()->escape($table);
        $columns = Shop::DB()->query("SHOW FULL COLUMNS FROM `{$table}`", 2);

        foreach ($columns as $column) {
            $column->Type_info    = self::parseType($column->Type);
            $list[$column->Field] = $column;
        }

        return $list;
    }

    /**
     * @param string $table
     * @return array
     */
    public static function getIndexes($table)
    {
        $list    = [];
        $table   = Shop::DB()->escape($table);
        $indexes = Shop::DB()->query("SHOW INDEX FROM `{$table}`", 2);

        foreach ($indexes as $index) {
            $container = (object)[
                'Index_type' => 'INDEX',
                'Columns'    => []
            ];

            if (!isset($list[$index->Key_name])) {
                $list[$index->Key_name] = $container;
            }

            $list[$index->Key_name]->Columns[$index->Column_name] = $index;
        }
        foreach ($list as $key => $item) {
            if (count($item->Columns) > 0) {
                $column = reset($item->Columns);
                if ($column->Key_name === 'PRIMARY') {
                    $list[$key]->Index_type = 'PRIMARY';
                } elseif ($column->Index_type === 'FULLTEXT') {
                    $list[$key]->Index_type = 'FULLTEXT';
                } elseif ((int)$column->Non_unique === 0) {
                    $list[$key]->Index_type = 'UNIQUE';
                }
            }
        }

        return $list;
    }

    /**
     * @param string      $database
     * @param string|null $table
     * @return array|mixed
     */
    public static function getStatus($database, $table = null)
    {
        $database = Shop::DB()->escape($database);

        if ($table !== null) {
            $table = Shop::DB()->escape($table);

            return Shop::DB()->query("SHOW TABLE STATUS FROM `{$database}` WHERE name='{$table}'", 1);
        }

        $list   = [];
        $status = Shop::DB()->query("SHOW TABLE STATUS FROM `{$database}`", 2);
        foreach ($status as $s) {
            $list[$s->Name] = $s;
        }

        return $list;
    }

    /**
     * @param string $type
     * @return object
     */
    public static function parseType($type)
    {
        $result = (object)[
            'Name'     => null,
            'Size'     => null,
            'Unsigned' => false
        ];
        $types  = explode(' ', $type);

        if (isset($types[1]) && $types[1] === 'unsigned') {
            $result->Unsigned = true;
        }

        if (preg_match('/([a-z]+)(?:\((.*)\))?/', $types[0], $m)) {
            $result->Size = 0;
            $result->Name = $m[1];
            if (isset($m[2])) {
                $size         = explode(',', $m[2]);
                $size         = count($size) === 1 ? $size[0] : $size;
                $result->Size = $size;
            }
        }

        return $result;
    }
}
