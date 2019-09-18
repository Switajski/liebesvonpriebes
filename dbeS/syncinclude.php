<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
define('DEFINES_PFAD', '../includes/');
define('FREIDEFINIERBARER_FEHLER', '8');

define('FILENAME_XML', 'data.xml');
define('FILENAME_KUNDENZIP', 'kunden.jtl');
define('FILENAME_BESTELLUNGENZIP', 'bestellungen.jtl');

define('LIMIT_KUNDEN', 100);
define('LIMIT_VERFUEGBARKEITSBENACHRICHTIGUNGEN', 100);
define('LIMIT_UPLOADQUEUE', 100);
define('LIMIT_BESTELLUNGEN', 100);

define('AUTO_SITEMAP', 1);
define('AUTO_RSS', 1);

require_once DEFINES_PFAD . 'config.JTL-Shop.ini.php';
require_once DEFINES_PFAD . 'defines.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'error_handler.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'parameterhandler.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Shop.php';

$shop = Shop::getInstance();
error_reporting(SYNC_LOG_LEVEL);
if (!is_writable(PFAD_SYNC_TMP)) {
    syncException('Fehler beim Abgleich: Das Shop-Verzeichnis dbeS/' . PFAD_SYNC_TMP . ' ist nicht durch den Web-User beschreibbar!', 8);
}
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ImageCloud.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Path.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.StringHandler.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceDB.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceMail.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Nice.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Synclogin.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Shopsetting.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Lieferadresse.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Rechnungsadresse.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Sprache.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Jtllog.php';
require_once PFAD_ROOT . PFAD_DBES . 'xml_tools.php';
require_once PFAD_ROOT . PFAD_PCLZIP . 'pclzip.lib.php';
require_once PFAD_ROOT . PFAD_DBES . 'mappings.php';

if (!function_exists('Shop')) {
    /**
     * @return Shop
     */
    function Shop()
    {
        return Shop::getInstance();
    }
}

//datenbankverbindung aufbauen
$DB = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.JTLCache.php';
$cache = JTLCache::getInstance();
$cache->setJtlCacheConfig();

$GLOBALS['bSeo'] = true; //compatibility!
// Liste aller Hooks, die momentan im Shop gebraucht werden könnten
// An jedem Hook hängt ein Array mit Plugin die diesen Hook benutzen
$oPluginHookListe_arr = Plugin::getHookList();
//globale Sprache
$oSprache = Sprache::getInstance(true);

/**
 * @param string     $cacheID
 * @param array|null $tags
 */
function clearCacheSync($cacheID, $tags = null)
{
    $cache = Shop::Cache();
    $cache->flush($cacheID);
    if ($tags !== null) {
        $cache->flushTags($tags);
    }
}

/**
 * @param string $color
 * @return array|bool
 */
function html2rgb($color)
{
    if ($color[0] === '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) === 6) {
        list($r, $g, $b) = [
            $color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]
        ];
    } elseif (strlen($color) === 3) {
        list($r, $g, $b) = [
            $color[0] . $color[0],
            $color[1] . $color[1],
            $color[2] . $color[2]
        ];
    } else {
        return false;
    }

    return [hexdec($r), hexdec($g), hexdec($b)];
}

/**
 *
 */
function checkFile()
{
    Jtllog::writeLog('incoming: ' . $_FILES['data']['name'] .
        ' size:' . $_FILES['data']['size'], JTLLOG_LEVEL_DEBUG, false, 'syncinclude_xml');

    if ($_FILES['data']['error'] || (isset($_FILES['data']['size']) && $_FILES['data']['size'] == 0)) {
        Jtllog::writeLog('ERROR: incoming: ' . $_FILES['data']['name'] . ' size:' . $_FILES['data']['size'] .
            ' err:' . $_FILES['data']['error'], JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
        $cFehler = 'Fehler beim Datenaustausch - Datei kam nicht an oder Größe 0!';
        switch ($_FILES['data']['error']) {
            case 0:
                $cFehler = 'Datei kam an, aber Dateigröße 0 [0]';
                break;
            case 1:
                $cFehler = 'Dateigröße > upload_max_filesize directive in php.ini [1]';
                break;
            case 2:
                $cFehler = 'Dateigröße > MAX_FILE_SIZE [2]';
                break;
            case 3:
                $cFehler = 'Datei wurde nur zum Teil hochgeladen [3]';
                break;
            case 4:
                $cFehler = 'Es wurde keine Datei hochgeladen [4]';
                break;
            case 6:
                $cFehler = 'Es fehlt ein TMP-Verzeichnis für HTTP Datei-Uploads! Bitte an Hoster wenden! [6]';
                break;
            case 7:
                $cFehler = 'Datei konnte nicht auf Datenträger gespeichert werden! [7]';
                break;
            case 8:
                $cFehler = 'Dateiendung nicht akzeptiert, bitte an Hoster werden! [8]';
                break;
        }

        syncException($cFehler . "\n" . print_r($_FILES, true), 8);
    } else {
        move_uploaded_file($_FILES['data']['tmp_name'], PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']));
        $_FILES['data']['tmp_name'] = PFAD_SYNC_TMP . basename($_FILES['data']['tmp_name']);
    }
}

/**
 * @return bool
 */
function auth()
{
    if (!isset($_POST['userID'], $_POST['userPWD'])) {
        return false;
    }
    $cName      = $_POST['userID'];
    $cPass      = $_POST['userPWD'];
    $loginDaten = Shop::DB()->query("SELECT * FROM tsynclogin", 1);

    return ($cName === $loginDaten->cName && $cPass === $loginDaten->cPass);
}

/**
 * @param string $tablename
 * @param object $object
 * @return mixed
 */
function DBinsert($tablename, $object)
{
    $key = Shop::DB()->insert($tablename, $object);
    if (!$key) {
        Jtllog::writeLog('DBinsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' .
            print_r($object, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
    }

    return $key;
}

/**
 * @param string   $tablename
 * @param array    $object_arr
 * @param int|bool $del
 */
function DBDelInsert($tablename, $object_arr, $del)
{
    if (is_array($object_arr)) {
        if ($del) {
            Shop::DB()->query("DELETE FROM $tablename", 4);
        }
        foreach ($object_arr as $object) {
            //hack? unset arrays/objects that would result in nicedb exceptions
            foreach (get_object_vars($object) as $key => $var) {
                if (is_array($var) || is_object($var)) {
                    unset($object->$key);
                }
            }
            $key = Shop::DB()->insert($tablename, $object);
            if (!$key) {
                Jtllog::writeLog('DBDelInsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' .
                    print_r($object, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
            }
        }
    }
}

/**
 * @param string     $tablename
 * @param array      $object_arr
 * @param string     $pk1
 * @param string|int $pk2
 */
function DBUpdateInsert($tablename, $object_arr, $pk1, $pk2 = 0)
{
    if (is_array($object_arr)) {
        foreach ($object_arr as $object) {
            if (isset($object->$pk1) && !$pk2 && $pk1 && $object->$pk1) {
                Shop::DB()->delete($tablename, $pk1, $object->$pk1);
            }
            if (isset($object->$pk2) && $pk1 && $pk2 && $object->$pk1 && $object->$pk2) {
                Shop::DB()->delete($tablename, [$pk1, $pk2], [$object->$pk1, $object->$pk2]);
            }
            $key = Shop::DB()->insert($tablename, $object);
            if (!$key) {
                Jtllog::writeLog('DBUpdateInsert fehlgeschlagen! Tabelle: ' . $tablename . ', Objekt: ' .
                    print_r($object, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
            }
        }
    }
}

/**
 * @param string $tableName
 * @param array $rows
 * @param array $pks
 * @return array
 */
function DBInsertOnExistUpdate($tableName, $rows, $pks)
{
    $result = array_fill_keys($pks, []);
    if (!is_array($rows)) {
        return $result;
    }
    if (!is_array($pks)) {
        $pks = [(string)$pks];
    }

    foreach ($rows as $row) {
        foreach ($pks as $pk) {
            if (!isset($row->$pk)) {
                Jtllog::writeLog('DBInsertOnExistUpdate fehlgeschlagen! PK nicht vorhanden! Tabelle: ' . $tableName
                    . ', Objekt: ' . print_r($row, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');

                continue 2;
            }
            $result[$pk][] = $row->$pk;
        }

        $insData = [];
        $updData = [];
        $params  = [];
        foreach ($row as $name => $value) {
            if ($value === '_DBNULL_') {
                $value = null;
            } elseif ($value === null) {
                $value = '';
            }

            if (strtolower($value) === 'now()') {
                $insData[$name] = $value;
                if (!in_array($name, $pks)) {
                    $updData[] = $name . ' = ' . $value;
                }
            } else {
                $insData[$name] = ':' . $name;
                $params[$name]  = $value;
                if (!in_array($name, $pks)) {
                    $updData[] = $name . ' = :' . $name;
                }
            }
        }
        $stmt = 'INSERT' . (count($updData) > 0 ? ' ' : ' IGNORE ') . 'INTO ' . $tableName
                    . '(' . implode(', ', array_keys($insData)) . ')
                    VALUES (' . implode(', ', $insData) . ')' . (count($updData) > 0 ? ' ON DUPLICATE KEY
                    UPDATE ' . implode(', ', $updData) : '');

        if (!Shop::DB()->queryPrepared($stmt, $params, 4)) {
            Jtllog::writeLog('DBInsertOnExistUpdate fehlgeschlagen! Tabelle: ' . $tableName . ', Objekt: ' .
                print_r($row, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
        }
    }

    return $result;
}

/**
 * @param string $tableName
 * @param array  $pks
 * @param string $excludeKey
 * @param array  $excludeValues
 */
function DBDeleteByKey($tableName, $pks, $excludeKey = '', $excludeValues = [])
{
    $whereKeys = [];
    $params    = [];
    foreach ($pks as $name => $value) {
        $whereKeys[]   = $name . ' = :' . $name;
        $params[$name] = $value;
    }
    if (empty($excludeKey) || !is_array($excludeValues)) {
        $excludeValues = [];
    }
    $stmt = 'DELETE FROM ' . $tableName . '
                WHERE ' . implode(' AND ', $whereKeys) . (count($excludeValues) > 0 ? '
                    AND ' . $excludeKey . ' NOT IN (' . implode(', ', $excludeValues) . ')' : '');

    if (!Shop::DB()->queryPrepared($stmt, $params, 4)) {
        Jtllog::writeLog('DBDeleteByKey fehlgeschlagen! Tabelle: ' . $tableName . ', PK: ' .
            print_r($pks, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude_xml');
    }
}

/**
 * @param array $elements
 * @param string $child
 * @return array
 */
function getObjectArray($elements, $child)
{
    $obj_arr = [];
    if (is_array($elements) && (is_array($elements[$child]) || is_array($elements[$child . ' attr']))) {
        $cnt = count($elements[$child]);
        if (is_array($elements[$child . ' attr'])) {
            $obj = new stdClass();
            if (is_array($elements[$child . ' attr'])) {
                $keys = array_keys($elements[$child . ' attr']);
                foreach ($keys as $key) {
                    if (!$elements[$child . ' attr'][$key]) {
                        Jtllog::writeLog($child . '->' . $key . ' fehlt! XML:' .
                            $elements[$child], JTLLOG_LEVEL_ERROR, false, 'syncinclude');
                    }
                    $obj->$key = $elements[$child . ' attr'][$key];
                }
            }
            if (is_array($elements[$child])) {
                $keys = array_keys($elements[$child]);
                foreach ($keys as $key) {
                    $obj->$key = $elements[$child][$key];
                }
            }
            $obj_arr[] = $obj;
        } elseif ($cnt > 1) {
            for ($i = 0; $i < $cnt / 2; $i++) {
                unset($obj);
                $obj = new stdClass();
                if (is_array($elements[$child][$i . ' attr'])) {
                    $keys = array_keys($elements[$child][$i . ' attr']);
                    foreach ($keys as $key) {
                        if (!$elements[$child][$i . ' attr'][$key]) {
                            Jtllog::writeLog($child . '[' . $i . ']->' . $key .
                                ' fehlt! XML:' . $elements[$child], JTLLOG_LEVEL_ERROR, false, 'syncinclude');
                        }

                        $obj->$key = $elements[$child][$i . ' attr'][$key];
                    }
                }
                if (is_array($elements[$child][$i])) {
                    $keys = array_keys($elements[$child][$i]);
                    foreach ($keys as $key) {
                        $obj->$key = $elements[$child][$i][$key];
                    }
                }
                $obj_arr[] = $obj;
            }
        }
    }

    return $obj_arr;
}

/**
 * @param string $file
 * @param bool   $isDir
 * @return bool
 */
function removeTemporaryFiles($file, $isDir = false)
{
    if (!KEEP_SYNC_FILES) {
        return $isDir ? @rmdir($file) : @unlink($file);
    }

    return false;
}

/**
 * @param array $arr
 * @param array $cExclude_arr
 * @return array
 */
function buildAttributes(&$arr, $cExclude_arr = [])
{
    $attr_arr = [];
    if (is_array($arr)) {
        $keys     = array_keys($arr);
        $keyCount = count($keys);
        for ($i = 0; $i < $keyCount; $i++) {
            if (!in_array($keys[$i], $cExclude_arr)) {
                if ($keys[$i]{0} === 'k') {
                    $attr_arr[$keys[$i]] = $arr[$keys[$i]];
                    unset($arr[$keys[$i]]);
                }
            }
        }
    }

    return $attr_arr;
}

/**
 * @param string       $zip
 * @param object|array $xml_obj
 */
function zipRedirect($zip, $xml_obj)
{
    $xmlfile = fopen(PFAD_SYNC_TMP . FILENAME_XML, 'w');
    fwrite($xmlfile, strtr(XML_serialize($xml_obj), "\0", ' '));
    fclose($xmlfile);
    if (file_exists(PFAD_SYNC_TMP . FILENAME_XML)) {
        $archive = new PclZip(PFAD_SYNC_TMP . $zip);
        if ($archive->create(PFAD_SYNC_TMP . FILENAME_XML, PCLZIP_OPT_REMOVE_ALL_PATH)) {
            //unlink(PFAD_SYNC_TMP . FILENAME_XML);
            readfile(PFAD_SYNC_TMP . $zip);
            exit;
        } else {
            syncException($archive->errorInfo(true));
        }
    }
}

/**
 * @param stdClass $obj
 * @param array    $xml
 */
function mapAttributes(&$obj, $xml)
{
    if (is_array($xml)) {
        $keys = array_keys($xml);
        if (is_array($keys)) {
            if ($obj === null) {
                $obj = new stdClass();
            }
            foreach ($keys as $key) {
                $obj->$key = $xml[$key];
            }
        }
    } else {
        Jtllog::writeLog('mapAttributes kein Array: XML:' .
            print_r($xml, true), JTLLOG_LEVEL_ERROR, false, 'syncinclude');
    }
}

/**
 * @param array $array
 * @return bool
 */
function is_assoc(array $array)
{
    return count(array_filter(array_keys($array), 'is_string')) > 0;
}

/**
 * @param stdClass $obj
 * @param array    $xml
 * @param array    $map
 */
function mappe(&$obj, $xml, $map)
{
    if ($obj === null) {
        $obj = new stdClass();
    }

    if (!is_assoc($map)) {
        foreach ($map as $key) {
            $obj->$key = isset($xml[$key]) ? $xml[$key] : null;
        }
    } else {
        foreach ($map as $key => $value) {
            $val = null;
            if (isset($value) && empty($xml[$key])) {
                $val = $value;
            } elseif (isset($xml[$key])) {
                $val = $xml[$key];
            }
            $obj->$key = $val;
        }
    }
}

/**
 * @param array  $xml
 * @param string $name
 * @param array  $map
 * @return array
 */
function mapArray($xml, $name, $map)
{
    $obj_arr = [];
    if ((isset($xml[$name]) && is_array($xml[$name])) ||
        (isset($xml[$name . ' attr']) && is_array($xml[$name . ' attr']))
    ) {
        if (isset($xml[$name . ' attr']) && is_array($xml[$name . ' attr'])) {
            $obj = new stdClass();
            mapAttributes($obj, $xml[$name . ' attr']);
            mappe($obj, $xml[$name], $map);

            return [$obj];
        }
        if (count($xml[$name]) > 2) {
            $cnt = count($xml[$name]) / 2;
            for ($i = 0; $i < $cnt; $i++) {
                if (!isset($obj_arr[$i]) || $obj_arr[$i] === null) {
                    $obj_arr[$i] = new stdClass();
                }
                mapAttributes($obj_arr[$i], $xml[$name][$i . ' attr']);
                mappe($obj_arr[$i], $xml[$name][$i], $map);
            }
        }
    }

    return $obj_arr;
}

/**
 * @param object $oXmlTree
 * @param array  $cMapping_arr
 * @return stdClass
 */
function JTLMapArr($oXmlTree, $cMapping_arr)
{
    $oMapped = new stdClass();
    foreach ($oXmlTree->Attributes() as $cKey => $cVal) {
        $oMapped->{$cKey} = utf8_decode((string)$cVal);
    }
    foreach ($cMapping_arr as $cMap) {
        if (isset($oXmlTree->{$cMap})) {
            $oMapped->{$cMap} = utf8_decode((string)$oXmlTree->{$cMap});
        }
    }

    return $oMapped;
}

/**
 * @param array  $xml
 * @param string $tabelle
 * @param array  $map
 * @param int $del
 */
function XML2DB($xml, $tabelle, $map, $del = 1)
{
    if (isset($xml[$tabelle]) && is_array($xml[$tabelle])) {
        $obj_arr = mapArray($xml, $tabelle, $map);
        DBDelInsert($tabelle, $obj_arr, $del);
    }
}

/**
 * @param array      $xml
 * @param string     $tabelle
 * @param array      $map
 * @param string     $pk1
 * @param int|string $pk2
 */
function updateXMLinDB($xml, $tabelle, $map, $pk1, $pk2 = 0)
{
    if ((isset($xml[$tabelle]) && is_array($xml[$tabelle])) ||
        (isset($xml[$tabelle . ' attr']) && is_array($xml[$tabelle . ' attr']))
    ) {
        $obj_arr = mapArray($xml, $tabelle, $map);

        DBUpdateInsert($tabelle, $obj_arr, $pk1, $pk2);
    }
}

/**
 * @param array $xml
 * @param string $table
 * @param array $map
 * @param array $pks
 * @return array
 */
function insertOnExistsUpdateXMLinDB($xml, $table, $map, $pks)
{
    if ((isset($xml[$table]) && is_array($xml[$table])) ||
        (isset($xml[$table . ' attr']) && is_array($xml[$table . ' attr']))
    ) {
        $rows = mapArray($xml, $table, $map);

        return DBInsertOnExistUpdate($table, $rows, $pks);
    }

    return array_fill_keys($pks, []);
}

/**
 * @param object $oArtikel
 * @param array  $oKundengruppe_arr
 * @global JTLSmarty $smarty
 */
function fuelleArtikelKategorieRabatt($oArtikel, $oKundengruppe_arr)
{
    Shop::DB()->delete('tartikelkategorierabatt', 'kArtikel', (int)$oArtikel->kArtikel);
    if (is_array($oKundengruppe_arr) && count($oKundengruppe_arr) > 0) {
        foreach ($oKundengruppe_arr as $oKundengruppe) {
            $oMaxRabatt = Shop::DB()->query(
                "SELECT tkategoriekundengruppe.fRabatt, tkategoriekundengruppe.kKategorie
                    FROM tkategoriekundengruppe
                    JOIN tkategorieartikel 
                        ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
                        AND tkategorieartikel.kArtikel = {$oArtikel->kArtikel}
                    LEFT JOIN tkategoriesichtbarkeit
                        ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                        AND tkategoriesichtbarkeit.kKundengruppe = {$oKundengruppe->kKundengruppe}
                    WHERE tkategoriesichtbarkeit.kKategorie IS NULL
                        AND tkategoriekundengruppe.kKundengruppe = {$oKundengruppe->kKundengruppe}
                    ORDER BY tkategoriekundengruppe.fRabatt DESC
                    LIMIT 1", 1
            );

            if (isset($oMaxRabatt->fRabatt) && $oMaxRabatt->fRabatt > 0) {
                $oArtikelKategorieRabatt                = new stdClass();
                $oArtikelKategorieRabatt->kArtikel      = $oArtikel->kArtikel;
                $oArtikelKategorieRabatt->kKundengruppe = $oKundengruppe->kKundengruppe;
                $oArtikelKategorieRabatt->kKategorie    = $oMaxRabatt->kKategorie;
                $oArtikelKategorieRabatt->fRabatt       = $oMaxRabatt->fRabatt;

                Shop::DB()->insert('tartikelkategorierabatt', $oArtikelKategorieRabatt);
                // Clear Artikel Cache
                $cache = Shop::Cache();
                $cache->flushTags([CACHING_GROUP_ARTICLE . '_' . $oArtikel->kArtikel]);
            }
        }
    }
}

/**
 * @param int $kKategorie
 */
function fuelleKategorieRabatt($kKategorie)
{
    Shop::DB()->delete('tartikelkategorierabatt', 'kKategorie', (int)$kKategorie);
    Shop::DB()->queryPrepared(
        "INSERT INTO tartikelkategorierabatt (
            SELECT tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie,
                   MAX(tkategoriekundengruppe.fRabatt) fRabatt
            FROM tkategoriekundengruppe
            INNER JOIN tkategorieartikel ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
            LEFT JOIN tkategoriesichtbarkeit ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                AND tkategoriesichtbarkeit.kKundengruppe = tkategoriekundengruppe.kKundengruppe
            WHERE tkategoriekundengruppe.kKategorie = :categoryID
                AND tkategoriesichtbarkeit.kKategorie IS NULL
            GROUP BY tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie
            HAVING MAX(tkategoriekundengruppe.fRabatt) > 0
        )",
        [
            'categoryID' => (int)$kKategorie
        ],
        10
    );
    Shop::Cache()->flushTags([CACHING_GROUP_CATEGORY . '_' . $kKategorie]);
}

/**
 * @param object $oArtikel
 */
function versendeVerfuegbarkeitsbenachrichtigung($oArtikel)
{
    if ($oArtikel->fLagerbestand > 0 && $oArtikel->kArtikel) {
        $Benachrichtigungen = Shop::DB()->selectAll(
            'tverfuegbarkeitsbenachrichtigung',
            ['nStatus', 'kArtikel'],
            [0, $oArtikel->kArtikel]
        );
        if (is_array($Benachrichtigungen) && count($Benachrichtigungen) > 0) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
            require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
            require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kampagne.php';

            $Artikel = new Artikel();
            $Artikel->fuelleArtikel($oArtikel->kArtikel, Artikel::getDefaultOptions());
            // Kampagne
            $oKampagne = new Kampagne(KAMPAGNE_INTERN_VERFUEGBARKEIT);
            if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
                $cSep           = (strpos($Artikel->cURL, '.php') === false) ? '?' : '&';
                $Artikel->cURL .= $cSep . $oKampagne->cParameter . '=' . $oKampagne->cWert;
            }
            foreach ($Benachrichtigungen as $Benachrichtigung) {
                $obj                                   = new stdClass();
                $obj->tverfuegbarkeitsbenachrichtigung = $Benachrichtigung;
                $obj->tartikel                         = $Artikel;
                $obj->tartikel->cName                  = StringHandler::htmlentitydecode($obj->tartikel->cName);
                $mail                                  = new stdClass();
                $mail->toEmail                         = $Benachrichtigung->cMail;
                $mail->toName                          = ($Benachrichtigung->cVorname || $Benachrichtigung->cNachname)
                    ? ($Benachrichtigung->cVorname . ' ' . $Benachrichtigung->cNachname)
                    : $Benachrichtigung->cMail;
                $obj->mail                             = $mail;
                sendeMail(MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR, $obj);

                $upd                    = new stdClass();
                $upd->nStatus           = 1;
                $upd->dBenachrichtigtAm = 'now()';
                $upd->cAbgeholt         = 'N';
                Shop::DB()->update(
                    'tverfuegbarkeitsbenachrichtigung',
                    'kVerfuegbarkeitsbenachrichtigung',
                    $Benachrichtigung->kVerfuegbarkeitsbenachrichtigung,
                    $upd
                );
            }
        }
    }
}

/**
 * @param int   $kArtikel
 * @param int   $kKundengruppe
 * @param float $fVKNetto
 */
function setzePreisverlauf($kArtikel, $kKundengruppe, $fVKNetto)
{
    $kArtikel      = (int)$kArtikel;
    $kKundengruppe = (int)$kKundengruppe;
    $fVKNetto      = (float)$fVKNetto;

    $oPreis_arr = Shop::DB()->query(
        "SELECT kPreisverlauf, fVKNetto, dDate, IF(dDate = CURDATE(), 1, 0) bToday
            FROM tpreisverlauf
            WHERE kArtikel = {$kArtikel}
	            AND kKundengruppe = {$kKundengruppe}
            ORDER BY dDate DESC LIMIT 2", 2
    );

    if (!empty($oPreis_arr[0]) && (int)$oPreis_arr[0]->bToday === 1) {
        // price for today exists
        if (round($oPreis_arr[0]->fVKNetto * 100) === round($fVKNetto * 100)) {
            // return if there is no difference
            return;
        }
        if(!empty($oPreis_arr[1]) && round($oPreis_arr[1]->fVKNetto * 100) === round($fVKNetto * 100)) {
            // delete todays price if the new price for today is the same as the latest price
            Shop::DB()->delete('tpreisverlauf', 'kPreisverlauf', (int)$oPreis_arr[0]->kPreisverlauf);
        } else {
            // update if prices are different
            Shop::DB()->update('tpreisverlauf', 'kPreisverlauf', (int)$oPreis_arr[0]->kPreisverlauf, (object)[
                'fVKNetto' => $fVKNetto,
            ]);
            // Clear Artikel Cache
            Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $kArtikel]);
        }
    } else {
        // no price for today exists
        if (!empty($oPreis_arr[0]) && round($oPreis_arr[0]->fVKNetto * 100) === round($fVKNetto * 100)) {
            // return if there is no difference
            return;
        }
        Shop::DB()->insert('tpreisverlauf', (object)[
            'kArtikel'      => $kArtikel,
            'kKundengruppe' => $kKundengruppe,
            'fVKNetto'      => $fVKNetto,
            'dDate'         => 'now()',
        ]);
        // Clear Artikel Cache
        Shop::Cache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $kArtikel]);
    }
}

/**
 * @param string $cFehler
 */
function unhandledError($cFehler)
{
    Jtllog::writeLog($cFehler, JTLLOG_LEVEL_ERROR);
    syncException($cFehler, FREIDEFINIERBARER_FEHLER);
}

/**
 * @param int $size
 * @return string
 */
function convert($size)
{
    $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

    return @round($size / pow(1024, ($i = (int)floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

/**
 * @param string $cMessage
 * @return string
 */
function translateError($cMessage)
{
    if (preg_match('/Maximum execution time of (\d+) second.? exceeded/', $cMessage, $cMatch_arr)) {
        $nSeconds = (int)$cMatch_arr[1];
        $cMessage = utf8_decode("Maximale Ausführungszeit von $nSeconds Sekunden überschritten");
    } elseif (preg_match("/Allowed memory size of (\d+) bytes exhausted/", $cMessage, $cMatch_arr)) {
        $nLimit   = (int)$cMatch_arr[1];
        $cMessage = utf8_decode("Erlaubte Speichergröße von $nLimit Bytes erschöpft");
    }

    return $cMessage;
}

/**
 * @param mixed $output
 * @return string
 */
function handleError($output)
{
    if (function_exists('error_get_last')) {
        $error = error_get_last();
        if ($error['type'] == 1) {
            $cError  = translateError($error['message']) . "\n";
            $cError .= 'Datei: ' . $error['file'];
            Jtllog::writeLog($cError, JTLLOG_LEVEL_ERROR);

            return $cError;
        }
    }

    return $output;
}

/**
 * @param null|stdClass $oArtikelPict
 * @param int           $kArtikel
 * @param int           $kArtikelPict
 */
function deleteArticleImage($oArtikelPict = null, $kArtikel = 0, $kArtikelPict = 0)
{
    $kArtikelPict = (int)$kArtikelPict;
    if ($oArtikelPict === null && $kArtikelPict > 0) {
        $oArtikelPict = Shop::DB()->select('tartikelpict', 'kArtikelPict', $kArtikelPict);
        $kArtikel     = isset($oArtikelPict->kArtikel) ? (int)$oArtikelPict->kArtikel : 0;
    }
    // Das Bild ist eine Verknüpfung
    if (isset($oArtikelPict->kMainArtikelBild) && $oArtikelPict->kMainArtikelBild > 0 && $kArtikel > 0) {
        // Existiert der Artikel vom Mainbild noch?
        $oMainArtikel = Shop::DB()->query(
            "SELECT kArtikel
                FROM tartikel
                WHERE kArtikel =
                (
                    SELECT kArtikel
                        FROM tartikelpict
                        WHERE kArtikelPict = " . (int)$oArtikelPict->kMainArtikelBild . "
                )", 1
        );
        // Main Artikel existiert nicht mehr
        if (!isset($oMainArtikel->kArtikel) || $oMainArtikel->kArtikel == 0) {
            // Existiert noch eine andere aktive Verknüpfung auf das Mainbild?
            $oArtikelPictPara_arr = Shop::DB()->query(
                "SELECT kArtikelPict
                    FROM tartikelpict
                    WHERE kMainArtikelBild = " . (int)$oArtikelPict->kMainArtikelBild . "
                        AND kArtikel != " . (int)$kArtikel, 2
            );
            // Lösche das MainArtikelBild
            if (count($oArtikelPictPara_arr) === 0) {
                // Bild von der Platte löschen
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_MINI . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_KLEIN . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_NORMAL . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $oArtikelPict->cPfad);
                // Bild vom Main aus DB löschen
                Shop::DB()->delete('tartikelpict', 'kArtikelPict', (int)$oArtikelPict->kMainArtikelBild);
            }
        }
        // Bildverknüpfung aus DB löschen
        Shop::DB()->delete('tartikelpict', 'kArtikelPict', (int)$oArtikelPict->kArtikelPict);
    } elseif (isset($oArtikelPict->kMainArtikelBild) && $oArtikelPict->kMainArtikelBild == 0) {
        // Das Bild ist ein Hauptbild
        // Gibt es Artikel die auf Bilder des zu löschenden Artikel verknüpfen?
        $oVerknuepfteArtikel_arr = Shop::DB()->query(
            "SELECT kArtikelPict
                FROM tartikelpict
                WHERE kMainArtikelBild = " . (int)$oArtikelPict->kArtikelPict, 2
        );
        if (count($oVerknuepfteArtikel_arr) === 0) {
            // Gibt ein neue Artikel die noch auf den physikalischen Pfad zeigen?
            $oObj = Shop::DB()->query(
                "SELECT count(*) AS nCount
                    FROM tartikelpict
                    WHERE cPfad = '{$oArtikelPict->cPfad}'", 1
            );
            if (isset($oObj->nCount) && $oObj->nCount < 2) {
                // Bild von der Platte löschen
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_MINI . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_KLEIN . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_NORMAL . $oArtikelPict->cPfad);
                @unlink(PFAD_ROOT . PFAD_PRODUKTBILDER_GROSS . $oArtikelPict->cPfad);
            }
        } else {
            //Reorder linked images because master imagelink will be deleted
            $kArtikelPictNext = $oVerknuepfteArtikel_arr[0]->kArtikelPict;
            //this will be the next masterimage
            Shop::DB()->update(
                'tartikelpict',
                'kArtikelPict',
                (int)$kArtikelPictNext,
                (object)['kMainArtikelBild' => 0]
            );
            //now link other images to the new masterimage
            Shop::DB()->update(
                'tartikelpict',
                'kMainArtikelBild',
                (int)$oArtikelPict->kArtikelPict,
                (object)['kMainArtikelBild' => (int)$kArtikelPictNext]
            );
        }
        // Bild aus DB löschen
        Shop::DB()->delete('tartikelpict', 'kArtikelPict', (int)$oArtikelPict->kArtikelPict);
    }
    // Clear Artikel Cache
    $cache = Shop::Cache();
    $cache->flushTags([CACHING_GROUP_ARTICLE . '_' . (int)$kArtikel]);
}

/**
 * @param object $oObject
 */
function extractStreet(&$oObject)
{
    $cData_arr = explode(' ', $oObject->cStrasse);
    if (count($cData_arr) > 1) {
        $oObject->cHausnummer = $cData_arr[count($cData_arr) - 1];
        unset($cData_arr[count($cData_arr) - 1]);
        $oObject->cStrasse = implode(' ', $cData_arr);
    }
}

/**
 * @param string $cSeoOld
 * @param string $cSeoNew
 * @return bool
 */
function checkDbeSXmlRedirect($cSeoOld, $cSeoNew)
{
    // Insert into tredirect weil sich das SEO von der Kategorie geändert hat
    if ($cSeoOld !== $cSeoNew && strlen($cSeoOld) > 0 && strlen($cSeoNew) > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Redirect.php';
        $oRedirect = new Redirect();
        $xPath_arr = parse_url(Shop::getURL());
        if (isset($xPath_arr['path'])) {
            $cSource = "{$xPath_arr['path']}/{$cSeoOld}";
        } else {
            $cSource = '/' . $cSeoOld;
        }

        return $oRedirect->saveExt($cSource, $cSeoNew, true);
    }

    return false;
}

/**
 * @param int         $kKey
 * @param string      $cKey
 * @param int|null    $kSprache
 * @param string|null $cAssoc
 * @return array|null|stdClass
 */
function getSeoFromDB($kKey, $cKey, $kSprache = null, $cAssoc = null)
{
    $kKey = (int)$kKey;
    if ($kKey > 0 && strlen($cKey) > 0) {
        if ($kSprache !== null && (int)$kSprache > 0) {
            $kSprache = (int)$kSprache;
            $oSeo     = Shop::DB()->select('tseo', 'kKey', $kKey, 'cKey', $cKey, 'kSprache', $kSprache);
            if (isset($oSeo->kKey) && (int)$oSeo->kKey > 0) {
                return $oSeo;
            }
        } else {
            $oSeo_arr = Shop::DB()->selectAll('tseo', ['kKey', 'cKey'], [$kKey, $cKey]);
            if (is_array($oSeo_arr) && count($oSeo_arr) > 0) {
                if ($cAssoc !== null && strlen($cAssoc) > 0) {
                    $oAssoc_arr = [];
                    foreach ($oSeo_arr as $oSeo) {
                        if (isset($oSeo->{$cAssoc})) {
                            $oAssoc_arr[$oSeo->{$cAssoc}] = $oSeo;
                        }
                    }
                    if (count($oAssoc_arr) > 0) {
                        $oSeo_arr = $oAssoc_arr;
                    }
                }

                return $oSeo_arr;
            }
        }
    }

    return null;
}

/**
 * @param int      $kArtikel
 * @param int      $kKundengruppe
 * @param int|null $kKunde
 * @return mixed
 */
function handlePriceFormat($kArtikel, $kKundengruppe, $kKunde = null)
{
    // tpreis
    $o                = new stdClass();
    $o->kArtikel      = (int)$kArtikel;
    $o->kKundengruppe = (int)$kKundengruppe;

    if ($kKunde !== null && (int)$kKunde > 0) {
        $o->kKunde = (int)$kKunde;
        flushCustomerPriceCache($o->kKunde);
    }

    return Shop::DB()->insert('tpreis', $o);
}

/**
 * Handle new PriceFormat (Wawi >= v.1.00):
 *
 * Sample XML:
 *  <tpreis kPreis="8" kArtikel="15678" kKundenGruppe="1" kKunde="0">
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>100</nAnzahlAb>
 *          <fNettoPreis>0.756303</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>250</nAnzahlAb>
 *          <fNettoPreis>0.714286</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>500</nAnzahlAb>
 *          <fNettoPreis>0.672269</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>750</nAnzahlAb>
 *          <fNettoPreis>0.630252</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>1000</nAnzahlAb>
 *          <fNettoPreis>0.588235</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>2000</nAnzahlAb>
 *          <fNettoPreis>0.420168</fNettoPreis>
 *      </tpreisdetail>
 *      <tpreisdetail kPreis="8">
 *          <nAnzahlAb>0</nAnzahlAb>
 *          <fNettoPreis>0.798319</fNettoPreis>
 *      </tpreisdetail>
 *  </tpreis>
 *
 * @param array $xml
 */
function handleNewPriceFormat($xml)
{
    if (is_array($xml) && isset($xml['tpreis'])) {
        $preise = mapArray($xml, 'tpreis', $GLOBALS['mPreis']);
        if (is_array($preise) && count($preise) > 0) {
            $kArtikel  = (int)$preise[0]->kArtikel;
            $Kunde_arr = Shop::DB()->selectAll('tpreis', ['kArtikel', 'kKundengruppe'], [$kArtikel, 0], 'kKunde');
            if (!empty($Kunde_arr)) {
                foreach ($Kunde_arr as $Kunde) {
                    $kKunde = (int)$Kunde->kKunde;
                    if ($kKunde > 0) {
                        flushCustomerPriceCache($kKunde);
                    }
                }
            }
            Shop::DB()->query(
                "DELETE p, d
                    FROM tpreis AS p
                    LEFT JOIN tpreisdetail AS d 
                        ON d.kPreis = p.kPreis
                    WHERE p.kArtikel = {$kArtikel}", 3
            );
            $customerGroupHandled = [];
            foreach ($preise as $i => $preis) {
                $kPreis = handlePriceFormat($preis->kArtikel, $preis->kKundenGruppe, $preis->kKunde);
                if (!empty($xml['tpreis'][$i])) {
                    $preisdetails = mapArray($xml['tpreis'][$i], 'tpreisdetail', $GLOBALS['mPreisDetail']);
                } else {
                    $preisdetails = mapArray($xml['tpreis'], 'tpreisdetail', $GLOBALS['mPreisDetail']);
                }
                $hasDefaultPrice = false;
                foreach ($preisdetails as $preisdetail) {
                    $o = (object)[
                        'kPreis'    => $kPreis,
                        'nAnzahlAb' => $preisdetail->nAnzahlAb,
                        'fVKNetto'  => $preisdetail->fNettoPreis
                    ];
                    Shop::DB()->insert('tpreisdetail', $o);
                    if ($o->nAnzahlAb == 0) {
                        $hasDefaultPrice = true;
                    }
                }
                // default price for customergroup set?
                if (!$hasDefaultPrice && isset($xml['fStandardpreisNetto'])) {
                    $o = (object)[
                        'kPreis'    => $kPreis,
                        'nAnzahlAb' => 0,
                        'fVKNetto'  => $xml['fStandardpreisNetto']
                    ];
                    Shop::DB()->insert('tpreisdetail', $o);
                }
                $customerGroupHandled[] = (int)$preis->kKundenGruppe;
            }
            //any customergroups with missing tpreis node left?
            $kKundengruppen_arr = Kundengruppe::getGroups();
            /** @var Kundengruppe $customergroup */
            foreach ($kKundengruppen_arr as $customergroup) {
                $kKundengruppe = $customergroup->getKundengruppe();
                if (isset($xml['fStandardpreisNetto']) && !in_array($kKundengruppe, $customerGroupHandled, true)) {
                    $kPreis = handlePriceFormat($kArtikel, $kKundengruppe);
                    $o      = (object)[
                        'kPreis'    => $kPreis,
                        'nAnzahlAb' => 0,
                        'fVKNetto'  => $xml['fStandardpreisNetto']
                    ];
                    Shop::DB()->insert('tpreisdetail', $o);
                }
            }
        }
    }
}

/**
 * @param array $objs
 */
function handleOldPriceFormat($objs)
{
    if (is_array($objs) && count($objs) > 0) {
        $kArtikel  = (int)$objs[0]->kArtikel;
        $Kunde_arr = Shop::DB()->selectAll('tpreis', ['kArtikel', 'kKundengruppe'], [$kArtikel, 0], 'kKunde');
        if (!empty($Kunde_arr)) {
            foreach ($Kunde_arr as $Kunde) {
                $kKunde = (int)$Kunde->kKunde;
                if ($kKunde > 0) {
                    flushCustomerPriceCache($kKunde);
                }
            }
        }
        Shop::DB()->query(
            "DELETE p, d
                FROM tpreis AS p
                LEFT JOIN tpreisdetail AS d 
                    ON d.kPreis = p.kPreis
                WHERE p.kArtikel = {$kArtikel}", 3
        );
        foreach ($objs as $obj) {
            $kPreis = handlePriceFormat($obj->kArtikel, $obj->kKundengruppe);
            // tpreisdetail
            insertPriceDetail($obj, 0, $kPreis);
            for ($i = 1; $i <= 5; $i++) {
                insertPriceDetail($obj, $i, $kPreis);
            }
        }
    }
}

/**
 * @param object $obj
 * @param int    $index
 * @param int    $priceId
 */
function insertPriceDetail($obj, $index, $priceId)
{
    $count = "nAnzahl{$index}";
    $price = "fPreis{$index}";

    if ((isset($obj->{$count}) && (int)$obj->{$count} > 0) || $index === 0) {
        $o            = new stdClass();
        $o->kPreis    = $priceId;
        $o->nAnzahlAb = ($index === 0) ? 0 : $obj->{$count};
        $o->fVKNetto  = ($index === 0) ? $obj->fVKNetto : $obj->{$price};

        Shop::DB()->insert('tpreisdetail', $o);
    }
}

/**
 * @param string $cAnrede
 * @return string
 */
function mappeWawiAnrede2ShopAnrede($cAnrede)
{
    $cAnrede = strtolower($cAnrede);
    if ($cAnrede === 'w' || $cAnrede === 'm') {
        return $cAnrede;
    }
    if ($cAnrede === 'frau' || $cAnrede === 'mrs' || $cAnrede === 'mrs.') {
        return 'w';
    }

    return 'm';
}

/**
 * prints fatal sync exception and exits with die()
 *
 * wawi codes:
 * 0: HTTP_NOERROR
 * 1: HTTP_DBERROR
 * 2: AUTH OK, ZIP CORRUPT
 * 3: HTTP_LOGIN
 * 4: HTTP_AUTH
 * 5: HTTP_BADINPUT
 * 6: HTTP_AUTHINVALID
 * 7: HTTP_AUTHCLOSED
 * 8: HTTP_CUSTOMERR
 * 9: HTTP_EBAYERROR
 *
 * @param string $msg Exception Message
 * @param int $wawiExceptionCode int code (0-9)
 */
function syncException($msg, $wawiExceptionCode = null)
{
    $output = '';
    if ($wawiExceptionCode !== null) {
        $output .= $wawiExceptionCode . '\n';
    }
    $output .= $msg;
    die(mb_convert_encoding($output, 'ISO-8859-1', 'auto'));
}

/**
 * flush object cache for category tree
 *
 * @return int
 */
function flushCategoryTreeCache()
{
    return Shop::Cache()->flushTags(['jtl_category_tree']);
}

/**
 * @param int $kKunde
 * @return bool|int
 */
function flushCustomerPriceCache($kKunde)
{
    return Shop::Cache()->flush('custprice_' . (int)$kKunde);
}

ob_start('handleError');
