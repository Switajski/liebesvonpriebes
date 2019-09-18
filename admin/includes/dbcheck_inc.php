<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return array
 */
function getDBStruct()
{
    $cDBStruct_arr = [];
    $oData_arr     = Shop::DB()->query("SHOW TABLES", 10);
    foreach ($oData_arr as $oData) {
        $cTable                 = $oData[0];
        $cDBStruct_arr[$cTable] = [];
        $oCol_arr               = Shop::DB()->query("SHOW COLUMNS FROM " . $cTable, 2);
        if ($oCol_arr !== false && is_array($oCol_arr)) {
            foreach ($oCol_arr as $oCol) {
                $cDBStruct_arr[$cTable][] = $oCol->Field;
            }
        }
    }

    return $cDBStruct_arr;
}

/**
 * @return array|bool|mixed
 */
function getDBFileStruct()
{
    $cDateiPfad  = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5;
    $cDateiListe = $cDateiPfad . 'dbstruct_' . JTL_VERSION . '.json';
    if (!file_exists($cDateiListe)) {
        return false;
    }
    $cJSON         = file_get_contents($cDateiListe);
    $oDBFileStruct = json_decode($cJSON);
    if (is_object($oDBFileStruct)) {
        $oDBFileStruct = get_object_vars($oDBFileStruct);
    }

    return $oDBFileStruct;
}

/**
 * @param array $cDBFileStruct_arr
 * @param array $cDBStruct_arr
 * @return array
 */
function compareDBStruct($cDBFileStruct_arr, $cDBStruct_arr)
{
    $cDBError_arr = [];
    foreach ($cDBFileStruct_arr as $cTable => $cColumn_arr) {
        if (!array_key_exists($cTable, $cDBStruct_arr)) {
            $cDBError_arr[$cTable] = 'Tabelle nicht vorhanden';
        } else {
            foreach ($cColumn_arr as $cColumn) {
                if (!in_array($cColumn, $cDBStruct_arr[$cTable], true)) {
                    $cDBError_arr[$cTable] = "Spalte $cColumn in $cTable nicht vorhanden";
                    break;
                }
            }
        }
    }

    return $cDBError_arr;
}

/**
 * @param string $action
 * @param array  $tables
 * @return array|bool
 */
function doDBMaintenance($action, array $tables)
{
    $tableString = implode(', ', $tables);

    switch ($action) {
        case 'optimize' :
            $cmd = 'OPTIMIZE TABLE ';
            break;
        case 'analyze' :
            $cmd = 'ANALYZE TABLE ';
            break;
        case 'repair' :
            $cmd = 'REPAIR TABLE ';
            break;
        case 'check' :
            $cmd = 'CHECK TABLE ';
            break;
        default :
            return false;
    }

    return (count($tables) > 0)
        ? Shop::DB()->query($cmd . $tableString, 2)
        : false;
}
