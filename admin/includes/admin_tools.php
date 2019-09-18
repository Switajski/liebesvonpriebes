<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int $kEinstellungenSektion
 * @return array
 */
function getAdminSectionSettings($kEinstellungenSektion)
{
    $kEinstellungenSektion = (int)$kEinstellungenSektion;
    $oConfig_arr           = [];
    if ($kEinstellungenSektion > 0) {
        $oConfig_arr = Shop::DB()->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            $kEinstellungenSektion,
            '*',
            'nSort'
        );
        if (is_array($oConfig_arr) && count($oConfig_arr) > 0) {
            foreach ($oConfig_arr as $conf) {
                if ($conf->cInputTyp === 'selectbox') {
                    $conf->ConfWerte = Shop::DB()->selectAll(
                        'teinstellungenconfwerte',
                        'kEinstellungenConf',
                        $conf->kEinstellungenConf,
                        '*',
                        'nSort'
                    );
                }
                $oSetValue = Shop::DB()->select(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$kEinstellungenSektion, $conf->cWertName]
                );
                $conf->gesetzterWert = isset($oSetValue->cWert)
                    ? $oSetValue->cWert
                    : null;
            }
        }
    }

    return $oConfig_arr;
}

/**
 * @param array $settingsIDs
 * @param array $cPost_arr
 * @param array $tags
 * @return string
 */
function saveAdminSettings($settingsIDs, &$cPost_arr, $tags = [CACHING_GROUP_OPTION])
{
    array_walk($settingsIDs, function (&$i) {
        $i = (int)$i;
    });
    $oConfig_arr = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenConf IN (" . implode(',', $settingsIDs) . ")
            ORDER BY nSort", 2
    );
    if (is_array($oConfig_arr) && count($oConfig_arr) > 0) {
        foreach ($oConfig_arr as $config) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = isset($cPost_arr[$config->cWertName])
                ? $cPost_arr[$config->cWertName]
                : null;
            $aktWert->cName                 = $config->cWertName;
            $aktWert->kEinstellungenSektion = (int)$config->kEinstellungenSektion;
            switch ($config->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)$aktWert->cWert;
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
                case 'listbox':
                    bearbeiteListBox($aktWert->cWert, $aktWert->cName, $aktWert->kEinstellungenSektion);
                    break;
            }
            if ($config->cInputTyp !== 'listbox') {
                Shop::DB()->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [(int)$config->kEinstellungenSektion, $config->cWertName]
                );
                Shop::DB()->insert('teinstellungen', $aktWert);
            }
        }
        Shop::Cache()->flushTags($tags);

        return 'Ihre Einstellungen wurden erfolgreich &uuml;bernommen.';
    }

    return 'Fehler beim Speichern Ihrer Einstellungen.';
}

/**
 * @param array $cListBox_arr
 * @param string $cWertName
 * @param int $kEinstellungenSektion
 */
function bearbeiteListBox($cListBox_arr, $cWertName, $kEinstellungenSektion)
{
    $kEinstellungenSektion = (int)$kEinstellungenSektion;
    if (is_array($cListBox_arr) && count($cListBox_arr) > 0) {
        Shop::DB()->delete('teinstellungen', ['kEinstellungenSektion', 'cName'], [$kEinstellungenSektion, $cWertName]);
        foreach ($cListBox_arr as $cListBox) {
            $oAktWert                        = new stdClass();
            $oAktWert->cWert                 = $cListBox;
            $oAktWert->cName                 = $cWertName;
            $oAktWert->kEinstellungenSektion = $kEinstellungenSektion;

            Shop::DB()->insert('teinstellungen', $oAktWert);
        }
    } else {
        // Leere Kundengruppen Work Around
        if ($cWertName === 'bewertungserinnerung_kundengruppen' || $cWertName === 'kwk_kundengruppen') {
            // Standard Kundengruppe aus DB holen
            $oKundengruppe = Shop::DB()->select('tkundengruppe', 'cStandard', 'Y');
            if ($oKundengruppe->kKundengruppe > 0) {
                Shop::DB()->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$kEinstellungenSektion, $cWertName]
                );
                $oAktWert                        = new stdClass();
                $oAktWert->cWert                 = $oKundengruppe->kKundengruppe;
                $oAktWert->cName                 = $cWertName;
                $oAktWert->kEinstellungenSektion = CONF_BEWERTUNG;

                Shop::DB()->insert('teinstellungen', $oAktWert);
            }
        }
    }
}

/**
 * @param int $kEinstellungenSektion
 * @param array $cPost_arr
 * @param array $tags
 * @return string
 */
function saveAdminSectionSettings($kEinstellungenSektion, &$cPost_arr, $tags = [CACHING_GROUP_OPTION])
{
    if (!validateToken()) {
        return 'Fehler: Cross site request forgery.';
    }
    $kEinstellungenSektion = (int)$kEinstellungenSektion;
    $oConfig_arr           = Shop::DB()->selectAll(
        'teinstellungenconf',
        ['kEinstellungenSektion', 'cConf'],
        [$kEinstellungenSektion, 'Y'],
        '*',
        'nSort'
    );

    if (is_array($oConfig_arr) && count($oConfig_arr) > 0) {
        foreach ($oConfig_arr as $config) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = isset($cPost_arr[$config->cWertName])
                ? $cPost_arr[$config->cWertName]
                : null;
            $aktWert->cName                 = $config->cWertName;
            $aktWert->kEinstellungenSektion = $kEinstellungenSektion;
            switch ($config->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
                case 'listbox':
                case 'selectkdngrp':
                    bearbeiteListBox($aktWert->cWert, $config->cWertName, $kEinstellungenSektion);
                    break;
            }

            if ($config->cInputTyp !== 'listbox' && $config->cInputTyp !== 'selectkdngrp') {
                Shop::DB()->delete(
                    'teinstellungen',
                    ['kEinstellungenSektion', 'cName'],
                    [$kEinstellungenSektion, $config->cWertName]
                );
                Shop::DB()->insert('teinstellungen', $aktWert);
            }
        }
        Shop::Cache()->flushTags($tags);

        return 'Ihre Einstellungen wurden erfolgreich &uuml;bernommen.';
    }

    return 'Fehler beim Speichern Ihrer Einstellungen.';
}

/**
 * Holt alle vorhandenen Kampagnen
 * Wenn $bInterneKampagne false ist, werden keine Interne Shop Kampagnen geholt
 * Wenn $bAktivAbfragen true ist, werden nur Aktive Kampagnen geholt
 *
 * @param bool $bInterneKampagne
 * @param bool $bAktivAbfragen
 * @return array
 */
function holeAlleKampagnen($bInterneKampagne = false, $bAktivAbfragen = true)
{
    $cAktivSQL  = $bAktivAbfragen ? " WHERE nAktiv = 1" : '';
    $cInternSQL = '';
    if (!$bInterneKampagne && $bAktivAbfragen) {
        $cInternSQL = " AND kKampagne >= 1000";
    } elseif (!$bInterneKampagne) {
        $cInternSQL = " WHERE kKampagne >= 1000";
    }
    $oKampagne_arr    = [];
    $oKampagneTMP_arr = Shop::DB()->query(
        "SELECT kKampagne
            FROM tkampagne
            " . $cAktivSQL . "
            " . $cInternSQL . "
            ORDER BY kKampagne", 2
    );

    if (is_array($oKampagneTMP_arr) && count($oKampagneTMP_arr) > 0) {
        foreach ($oKampagneTMP_arr as $oKampagneTMP) {
            $oKampagne = new Kampagne($oKampagneTMP->kKampagne);
            if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
                $oKampagne_arr[$oKampagne->kKampagne] = $oKampagne;
            }
        }
    }

    return $oKampagne_arr;
}

/**
 * @param array $oXML_arr
 * @param int   $nLevel
 * @return array
 */
function getArrangedArray($oXML_arr, $nLevel = 1)
{
    $nLevel = (int)$nLevel;
    if (is_array($oXML_arr)) {
        $cArrayKeys = array_keys($oXML_arr);
        $nCount     = count($oXML_arr);
        for ($i = 0; $i < $nCount; $i++) {
            if (strpos($cArrayKeys[$i], ' attr') !== false) {
                //attribut array -> nicht beachten -> weiter
                continue;
            } else {
                if ($nLevel === 0 || (int)$cArrayKeys[$i] > 0 || $cArrayKeys[$i] == '0') {
                    //int Arrayelement -> in die Tiefe gehen
                    $oXML_arr[$cArrayKeys[$i]] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]);
                } else {
                    if (isset($oXML_arr[$cArrayKeys[$i]][0])) {
                        $oXML_arr[$cArrayKeys[$i]] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]);
                    } else {
                        if ($oXML_arr[$cArrayKeys[$i]] === '') {
                            //empty node
                            continue;
                        }
                        //kein Attributzweig, kein numerischer Anfang
                        $tmp_arr           = [];
                        $tmp_arr['0 attr'] = isset($oXML_arr[$cArrayKeys[$i] . ' attr'])
                            ? $oXML_arr[$cArrayKeys[$i] . ' attr']
                            : null;
                        $tmp_arr['0']      = $oXML_arr[$cArrayKeys[$i]];
                        unset($oXML_arr[$cArrayKeys[$i]], $oXML_arr[$cArrayKeys[$i] . ' attr']);
                        $oXML_arr[$cArrayKeys[$i]] = $tmp_arr;
                        if (is_array($oXML_arr[$cArrayKeys[$i]]['0'])) {
                            $oXML_arr[$cArrayKeys[$i]]['0'] = getArrangedArray($oXML_arr[$cArrayKeys[$i]]['0']);
                        }
                    }
                }
            }
        }
    }

    return $oXML_arr;
}

/**
 * @return array
 */
function holeBewertungserinnerungSettings()
{
    $Einstellungen = [];
    // Einstellungen für die Bewertung holen
    $oEinstellungen_arr = Shop::DB()->selectAll('teinstellungen', 'kEinstellungenSektion', CONF_BEWERTUNG);
    if (is_array($oEinstellungen_arr) && count($oEinstellungen_arr) > 0) {
        $Einstellungen['bewertung']                                       = [];
        $Einstellungen['bewertung']['bewertungserinnerung_kundengruppen'] = [];

        foreach ($oEinstellungen_arr as $oEinstellungen) {
            if ($oEinstellungen->cName) {
                if ($oEinstellungen->cName === 'bewertungserinnerung_kundengruppen') {
                    $Einstellungen['bewertung'][$oEinstellungen->cName][] = $oEinstellungen->cWert;
                } else {
                    $Einstellungen['bewertung'][$oEinstellungen->cName] = $oEinstellungen->cWert;
                }
            }
        }

        return $Einstellungen['bewertung'];
    }

    return $Einstellungen;
}

/**
 *
 */
function setzeSprache()
{
    if (validateToken() && verifyGPCDataInteger('sprachwechsel') === 1) {
        // Wähle explizit gesetzte Sprache als aktuelle Sprache
        $oSprache = Shop::DB()->select('tsprache', 'kSprache', (int)$_POST['kSprache']);

        if ((int)$oSprache->kSprache > 0) {
            $_SESSION['kSprache']    = (int)$oSprache->kSprache;
            $_SESSION['cISOSprache'] = $oSprache->cISO;
        }
    }

    if (!isset($_SESSION['kSprache'])) {
        // Wähle Standardsprache als aktuelle Sprache
        $oSprache = Shop::DB()->select('tsprache', 'cShopStandard', 'Y');

        if ((int)$oSprache->kSprache > 0) {
            $_SESSION['kSprache']    = (int)$oSprache->kSprache;
            $_SESSION['cISOSprache'] = $oSprache->cISO;
        }
    }
    if (isset($_SESSION['kSprache']) && empty($_SESSION['cISOSprache'])) {
        // Fehlendes cISO ergänzen
        $oSprache = Shop::DB()->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);

        if ((int)$oSprache->kSprache > 0) {
            $_SESSION['cISOSprache'] = $oSprache->cISO;
        }
    }
}

/**
 *
 */
function setzeSpracheTrustedShops()
{
    $cISOSprache_arr = [
        'de' => 'Deutsch',
        'en' => 'Englisch',
        'fr' => 'Französisch',
        'pl' => 'Polnisch',
        'es' => 'Spanisch'
    ];
    //setze std Sprache als aktuelle Sprache
    if (!isset($_SESSION['TrustedShops']->oSprache->cISOSprache)) {
        if (!isset($_SESSION['TrustedShops'])) {
            $_SESSION['TrustedShops']           = new stdClass();
            $_SESSION['TrustedShops']->oSprache = new stdClass();
        }
        $_SESSION['TrustedShops']->oSprache->cISOSprache  = 'de';
        $_SESSION['TrustedShops']->oSprache->cNameSprache = $cISOSprache_arr['de'];
    }

    //setze explizit ausgewählte Sprache
    if (isset($_POST['sprachwechsel']) && (int)$_POST['sprachwechsel'] === 1) {
        if (strlen($_POST['cISOSprache']) > 0) {
            $_SESSION['TrustedShops']->oSprache->cISOSprache  =
                StringHandler::htmlentities(StringHandler::filterXSS($_POST['cISOSprache']));
            $_SESSION['TrustedShops']->oSprache->cNameSprache =
                $cISOSprache_arr[StringHandler::htmlentities(StringHandler::filterXSS($_POST['cISOSprache']))];
        }
    }
}

/**
 * @param int $nMonth
 * @param int $nYear
 * @return int
 */
function firstDayOfMonth($nMonth = -1, $nYear = -1)
{
    return mktime(
        0,
        0,
        0,
        $nMonth > -1 ? $nMonth : date('m'),
        1,
        $nYear > -1 ? $nYear : date('Y')
    );
}

/**
 * @param int $nMonth
 * @param int $nYear
 * @return int
 */
function lastDayOfMonth($nMonth = -1, $nYear = -1)
{
    return mktime(
        23,
        59,
        59,
        $nMonth > -1 ? $nMonth : date('m'),
        date('t', firstDayOfMonth($nMonth, $nYear)),
        $nYear > -1 ? $nYear : date('Y')
    );
}

/**
 * Ermittelt den Wochenstart und das Wochenende
 * eines Datums im Format YYYY-MM-DD
 * und gibt ein Array mit Start als Timestamp zurück
 * Array[0] = Start
 * Array[1] = Ende
 * @param string $cDatum
 * @return array
 */
function ermittleDatumWoche($cDatum)
{
    if (strlen($cDatum) > 0) {
        list($cJahr, $cMonat, $cTag) = explode('-', $cDatum);
        // So = 0, SA = 6
        $nWochentag = (int)date('w', mktime(0, 0, 0, (int)$cMonat, (int)$cTag, (int)$cJahr));
        // Woche soll Montag starten - also So = 6, Mo = 0
        if ($nWochentag === 0) {
            $nWochentag = 6;
        } else {
            $nWochentag--;
        }
        // Wochenstart ermitteln
        $nTagOld = (int)$cTag;
        $nTag    = (int)$cTag - $nWochentag;
        $nMonat  = (int)$cMonat;
        $nJahr   = (int)$cJahr;
        if ($nTag <= 0) {
            --$nMonat;
            if ($nMonat === 0) {
                $nMonat = 12;
                ++$nJahr;
            }

            $nAnzahlTageProMonat = date('t', mktime(0, 0, 0, $nMonat, 1, $nJahr));
            $nTag                = $nAnzahlTageProMonat - $nWochentag + $nTagOld;
        }
        $nStampStart = mktime(0, 0, 0, $nMonat, $nTag, $nJahr);
        // Wochenende ermitteln
        $nTage               = 6;
        $nAnzahlTageProMonat = date('t', mktime(0, 0, 0, $nMonat, 1, $nJahr));
        $nTag += $nTage;
        if ($nTag > $nAnzahlTageProMonat) {
            $nTag -= $nAnzahlTageProMonat;
            ++$nMonat;
            if ($nMonat > 12) {
                $nMonat = 1;
                ++$nJahr;
            }
        }

        $nStampEnde = mktime(23, 59, 59, $nMonat, $nTag, $nJahr);

        return [$nStampStart, $nStampEnde];
    }

    return [];
}

/**
 * Return version of files
 *
 * @param bool $bDate
 * @return mixed
 */
function getJTLVersionDB($bDate = false)
{
    $nRet     = 0;
    $nVersion = Shop::DB()->query("SELECT nVersion, dAktualisiert FROM tversion", 1);
    if (isset($nVersion->nVersion) && is_numeric($nVersion->nVersion)) {
        $nRet = (int)$nVersion->nVersion;
    }
    if ($bDate) {
        $nRet = $nVersion->dAktualisiert;
    }

    return $nRet;
}

/**
 * Return version of files
 *
 * @return mixed
 */
function getJTLVersion()
{
    $majorMinor = Shop::getVersion();
    $major      = substr($majorMinor, 0, 1);
    $minor      = substr($majorMinor, 1);
    $patch      = is_int(JTL_MINOR_VERSION) ? JTL_MINOR_VERSION : 0;
    $version    = $major.'.'.$minor.'.'.$patch;

    return $version;
}

/**
 * @param string $size_str
 * @return mixed
 */
function getMaxFileSize($size_str)
{
    switch (substr($size_str, -1)) {
        case 'M':
        case 'm':
            return (int)$size_str * 1048576;
        case 'K':
        case 'k':
            return (int)$size_str * 1024;
        case 'G':
        case 'g':
            return (int)$size_str * 1073741824;
        default:
            return $size_str;
    }
}

/**
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cTargetID
 * @return IOResponse
 */
function getCurrencyConversionIO($fPreisNetto, $fPreisBrutto, $cTargetID)
{
    $response = new IOResponse();
    $cString  = getCurrencyConversion($fPreisNetto, $fPreisBrutto);
    $response->assign($cTargetID, 'innerHTML', $cString);

    return $response;
}

/**
 * @param float  $fPreisNetto
 * @param float  $fPreisBrutto
 * @param string $cTooltipID
 * @return IOResponse
 */
function setCurrencyConversionTooltipIO($fPreisNetto, $fPreisBrutto, $cTooltipID)
{
    $response = new IOResponse();
    $cString  = getCurrencyConversion($fPreisNetto, $fPreisBrutto);
    $response->assign($cTooltipID, 'dataset.originalTitle', $cString);

    return $response;
}

/**
 * @param $title
 * @param $utl
 */
function addFav($title, $url)
{
    $success     = false;
    $title       = utf8_decode($title);
    $url         = utf8_decode($url);
    $kAdminlogin = (int)$_SESSION['AdminAccount']->kAdminlogin;

    if (!empty($title) && !empty($url)) {
        $success = AdminFavorite::add($kAdminlogin, $title, $url);
    }

    if ($success) {
        $result = [
            'title' => $title,
            'url'   => $url
        ];
    } else {
        $result = new IOError('Unauthorized', 401);
    }

    return $result;
}

/**
 * @return array
 */
function reloadFavs()
{
    global $smarty, $oAccount;

    $smarty->assign('favorites', $oAccount->favorites());
    $tpl = $smarty->fetch('tpl_inc/favs_drop.tpl');

    return [ 'tpl' => $tpl ];
}

/**
 * @return array
 */
function getNotifyDropIO()
{
    Shop::Smarty()->assign('notifications', Notification::getInstance());
    return [
        'tpl' => Shop::Smarty()->fetch('tpl_inc/notify_drop.tpl'),
        'type' => 'notify'
    ];
}
