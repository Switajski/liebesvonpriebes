<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

@ini_set('max_execution_time', 0);

$oAccount->permission('EXPORT_YATEGO_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

define('DELIMITER', ';');
define('ESC', '"');
define('CRLF', "\n");
define('PATH', PFAD_ROOT . PFAD_EXPORT_YATEGO);
define('DESCRIPTION_TAGS', '<a><b><i><u><p><br><hr><h1><h2><h3><h4><h5><h6><ul><ol><li><span><font><table><colgroup>');

$exportformat = Shop::DB()->query(
    "SELECT texportformat.*, tkampagne.cParameter AS tkampagne_cParameter, 
        tkampagne.cWert AS tkampagne_cWert
        FROM texportformat
        LEFT JOIN tkampagne 
            ON tkampagne.kKampagne = texportformat.kKampagne
            AND tkampagne.nAktiv = 1
        WHERE texportformat.nSpecial = 1", 1
);

$queue = Shop::DB()->select('texportqueue', 'kExportformat', (int)$exportformat->kExportformat);
if (!$queue->kExportformat || !$queue->nLimit_m) {
    die('1');
}

if (!pruefeYategoExportPfad()) {
    Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt = now() WHERE nSpecial = 1", 4);
    Shop::DB()->delete('texportqueue', 'kExportqueue', (int)$queue->kExportqueue);

    die('2');
}

//falls dateien existieren, loeschen
if ($queue->nLimit_n == 0 && file_exists(PATH . 'varianten.csv')) {
    unlink(PATH . 'varianten.csv');
}
if ($queue->nLimit_n == 0 && file_exists(PATH . 'artikel.csv')) {
    unlink(PATH . 'artikel.csv');
}
if ($queue->nLimit_n == 0 && file_exists(PATH . 'shopkategorien.csv')) {
    unlink(PATH . 'shopkategorien.csv');
}
if ($queue->nLimit_n == 0 && file_exists(PATH . 'lager.csv')) {
    unlink(PATH . 'lager.csv');
}

$ExportEinstellungen = getEinstellungenExport($exportformat->kExportformat);

// Global Array
$oGlobal_arr          = [];
$oGlobal_arr['lager'] = [];
$KategorieListe       = [];

setzeSteuersaetze();
$_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
$_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
$_SESSION['kSprache']                                 = $exportformat->kSprache;
$_SESSION['kKundengruppe']                            = $exportformat->kKundengruppe;
$_SESSION['Kundengruppe']->kKundengruppe              = $exportformat->kKundengruppe;
// Nur Vaeterartikel holen
$res = Shop::DB()->query(
    "SELECT tartikel.kArtikel
        FROM tartikel
        JOIN tartikelattribut 
            ON tartikelattribut.kArtikel = tartikel.kArtikel
        WHERE tartikelattribut.cName = 'yategokat'
            AND tartikel.kVaterArtikel = 0
        ORDER BY tartikel.kArtikel
        LIMIT " . $queue->nLimit_n . ", " . $queue->nLimit_m, 2
);
$defaultOptions = Artikel::getDefaultOptions();
foreach ($res as $tartikel) {
    $Artikel = new Artikel();
    $Artikel->fuelleArtikel(
        $tartikel->kArtikel,
        $defaultOptions,
        $exportformat->kKundengruppe,
        $exportformat->kSprache,
        true
    );
    verarbeiteYategoExport($Artikel, $exportformat, $ExportEinstellungen, $KategorieListe, $oGlobal_arr);
}

$KategorieListe                = array_keys($KategorieListe);
$oGlobal_arr['shopkategorien'] = getCats($KategorieListe);

if ($exportformat->cKodierung === 'UTF-8') {
    $cHeader = "\xEF\xBB\xBF";
    writeFile(PATH . 'varianten.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['varianten']) .
            CRLF . makecsv($oGlobal_arr['variantenwerte'])));
    writeFile(PATH . 'artikel.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['artikel'])));
    writeFile(PATH . 'shopkategorien.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['shopkategorien'])));
    writeFile(PATH . 'lager.csv', $cHeader . utf8_encode(makecsv($oGlobal_arr['lager'])));
} else {
    writeFile(PATH . 'varianten.csv', makecsv($oGlobal_arr['varianten']) .
        CRLF . makecsv($oGlobal_arr['variantenwerte']));
    writeFile(PATH . 'artikel.csv', makecsv($oGlobal_arr['artikel']));
    writeFile(PATH . 'shopkategorien.csv', makecsv($oGlobal_arr['shopkategorien']));
    writeFile(PATH . 'lager.csv', makecsv($oGlobal_arr['lager']));
}

$max_artikel = Shop::DB()->query(
    "SELECT count(*) AS cnt
        FROM tartikel
        JOIN tartikelattribut 
            ON tartikelattribut.kArtikel = tartikel.kArtikel
        WHERE tartikelattribut.cName = 'yategokat'", 1
);

if ($max_artikel->cnt > $queue->nLimit_n + $queue->nLimit_m) {
    Shop::DB()->query("
        UPDATE texportqueue 
            SET nLimit_n = nLimit_n+" . $queue->nLimit_m . " 
            WHERE kExportqueue = " . (int)$queue->kExportqueue, 4
    );
    header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] .
        '?back=admin&token=' . $_SESSION['jtl_token']);
    exit;
} else {
    Shop::DB()->query("UPDATE texportformat SET dZuletztErstellt = now() WHERE nSpecial = 1", 4);
    Shop::DB()->delete('texportqueue', 'kExportqueue', (int)$queue->kExportqueue);
    if ($_GET['back'] === 'admin') {
        header('Location: yatego.export.php?token=' . $_SESSION['jtl_token'] .
            '&rdy=' . base64_encode((int)$max_artikel->cnt));
        exit;
    }
}

/**
 * @param string $n
 * @return string
 */
function getNum($n)
{
    return str_replace('.', ',', $n);
}

/**
 * @param string $img
 * @return string
 */
function getURL($img)
{
    return $img ? Shop::getURL() . '/' . $img : '';
}

/**
 * @param string $file
 * @param string $data
 */
function writeFile($file, $data)
{
    $handle = fopen($file, 'a');
    fwrite($handle, $data);
    fclose($handle);
}

/**
 * @param array $cGlobalAssoc_arr
 * @return string
 */
function makecsv($cGlobalAssoc_arr)
{
    global $queue;
    $out = '';

    if (is_array($cGlobalAssoc_arr) && count($cGlobalAssoc_arr) > 0) {
        if ($queue->nLimit_n == 0) {
            $fieldnames = array_keys($cGlobalAssoc_arr[0]);
            $out        = ESC . implode(ESC . DELIMITER . ESC, $fieldnames) . ESC . CRLF;
        }

        foreach ($cGlobalAssoc_arr as $cGlobalAssoc) {
            $out .= ESC . implode(ESC . DELIMITER . ESC, $cGlobalAssoc) . ESC . CRLF;
        }
    }

    return $out;
}

/**
 * @param array $catlist
 * @return array
 */
function getCats($catlist)
{
    $shopcats = [];
    if (is_array($catlist) && count($catlist)) {
        // fetch all categories in $cats with index kKategorie
        $cats = [];
        $res  = Shop::DB()->query("SELECT kKategorie, cName, kOberKategorie, nSort FROM tkategorie", 10);
        while ($row = $res->fetch(PDO::FETCH_OBJ)) {
            $cats[$row->kKategorie] = $row;
        }
        foreach ($catlist as $cat_id) {
            $this_cat = &$cats[$cat_id];
            $catdir   = [];
            // create category path
            while (isset($this_cat)) {
                array_unshift($catdir, new stdClass());
                $catdir[0]->cId   = $this_cat->kKategorie;
                $catdir[0]->cName = $this_cat->cName;

                $this_cat = &$cats[$this_cat->kOberKategorie];
            }

            $shopcats[] = [
                'foreign_id_h' => isset($catdir[0]) ? $catdir[0]->cId : null,
                'foreign_id_m' => isset($catdir[1]) ? $catdir[1]->cId : null,
                'foreign_id_l' => isset($catdir[2]) ? $catdir[2]->cId : null,
                'title_h'      => isset($catdir[0]) ? $catdir[0]->cName : null,
                'title_m'      => isset($catdir[1]) ? $catdir[1]->cName : null,
                'title_l'      => isset($catdir[2]) ? $catdir[2]->cName : null,
                'sorting'      => $cats[$cat_id]->nSort
            ];
        }
    }

    return $shopcats;
}
