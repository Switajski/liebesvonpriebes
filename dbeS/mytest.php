<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

//wawi mindestversion überprüfen
if (!isset($_POST['wawiversion']) || (int)$_POST['wawiversion'] < JTL_MIN_WAWI_VERSION) {
    syncException("Ihr JTL-Shop Version " .
        (JTL_VERSION / 100) . " benötigt für den Datenabgleich mindestens JTL-Wawi Version " .
        (JTL_MIN_WAWI_VERSION / 100000.0) .
        ". \nEine aktuelle Version erhalten Sie unter: https://jtl-url.de/wawidownload", 8);
}
$return = 3;
$cName  = $_POST['uID'];
$cPass  = $_POST['uPWD'];

$_POST['uID']  = '*';
$_POST['uPWD'] = '*';

$loginDaten = new Synclogin();
$version    = '';
$oVersion   = null;

Jtllog::writeLog(print_r($loginDaten, true), JTLLOG_LEVEL_DEBUG, false, 'Sync_xml');
Jtllog::writeLog("{$cName} - {$cPass}", JTLLOG_LEVEL_DEBUG, false, 'Sync_xml');

if ($cName && $cPass && $cName === $loginDaten->cName && $cPass === $loginDaten->cPass) {
    $return = 0;
    if (isset($_POST['kKunde']) && (int)$_POST['kKunde'] > 0) {
        $oStatus = Shop::DB()->query("SHOW TABLE STATUS LIKE 'tkunde'", 1);
        if ($oStatus->Auto_increment < (int)$_POST['kKunde']) {
            Shop::DB()->query("ALTER TABLE tkunde AUTO_INCREMENT = " . (int)$_POST['kKunde'], 4);
        }
    }
    if (isset($_POST['kBestellung']) && (int)$_POST['kBestellung'] > 0) {
        $oStatus = Shop::DB()->query("SHOW TABLE STATUS LIKE 'tbestellung'", 1);
        if ($oStatus->Auto_increment < (int)$_POST['kBestellung']) {
            Shop::DB()->query("ALTER TABLE tbestellung AUTO_INCREMENT = " . (int)$_POST['kBestellung'], 4);
        }
    }
    if (isset($_POST['kLieferadresse']) && (int)$_POST['kLieferadresse'] > 0) {
        $oStatus = Shop::DB()->query("SHOW TABLE STATUS LIKE 'tlieferadresse'", 1);
        if ($oStatus->Auto_increment < (int)$_POST['kLieferadresse']) {
            Shop::DB()->query("ALTER TABLE tlieferadresse AUTO_INCREMENT = " . (int)$_POST['kLieferadresse'], 4);
        }
    }
    if (isset($_POST['kZahlungseingang']) && (int)$_POST['kZahlungseingang'] > 0) {
        $oStatus = Shop::DB()->query("SHOW TABLE STATUS LIKE 'tzahlungseingang'", 1);
        if ($oStatus->Auto_increment < (int)$_POST['kZahlungseingang']) {
            Shop::DB()->query("ALTER TABLE tzahlungseingang AUTO_INCREMENT  = " . (int)$_POST['kZahlungseingang'], 4);
        }
    }
    $oVersion = Shop::DB()->query("SELECT nVersion FROM tversion", 1);
} else {
    Jtllog::writeLog("Result: {$return}", JTLLOG_LEVEL_DEBUG, false, 'Sync_xml');
    syncException("{$return}");
}
echo $return . ';JTL4;' . $oVersion->nVersion . ';';
Jtllog::writeLog("Result: {$return}", JTLLOG_LEVEL_DEBUG, false, 'Sync_xml');
