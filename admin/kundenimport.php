<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('IMPORT_CUSTOMER_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';

//jtl2
$format = [
    'cPasswort', 'cAnrede', 'cTitel', 'cVorname', 'cNachname', 'cFirma',
    'cStrasse', 'cHausnummer', 'cAdressZusatz', 'cPLZ', 'cOrt', 'cBundesland',
    'cLand', 'cTel', 'cMobil', 'cFax', 'cMail', 'cUSTID', 'cWWW', 'fGuthaben',
    'cNewsletter', 'dGeburtstag', 'fRabatt', 'cHerkunft', 'dErstellt', 'cAktiv'
];

if (isset($_POST['kundenimport'], $_FILES['csv']['tmp_name']) &&
    $_POST['kundenimport'] == 1 &&
    $_FILES['csv'] &&
    validateToken() &&
    strlen($_FILES['csv']['tmp_name']) > 0
) {
    $delimiter = guessCsvDelimiter($_FILES['csv']['tmp_name']);
    $file = fopen($_FILES['csv']['tmp_name'], 'r');
    if ($file !== false) {
        $row      = 0;
        $fmt      = [];
        $formatId = -1;
        $hinweis  = '';
        while ($data = fgetcsv($file, 2000, $delimiter, '"')) {
            if ($row === 0) {
                $hinweis .= 'Checke Kopfzeile ...';
                $fmt = checkformat($data);
                if ($fmt === -1) {
                    $hinweis .= ' - Format nicht erkannt!';
                    break;
                } else {
                    $hinweis .= '<br><br>Importiere...<br>';
                }
            } else {
                $hinweis .= '<br>Zeile ' . $row . ': ' . processImport($fmt, $data);
            }

            $row++;
        }
        fclose($file);
    }
}

$smarty->assign('sprachen', gibAlleSprachen())
       ->assign('kundengruppen', Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2))
       ->assign('step', (isset($step) ? $step : null))
       ->assign('hinweis', (isset($hinweis) ? $hinweis : null))
       ->display('kundenimport.tpl');


/**
 * @param int $length
 * @param int $myseed
 * @return string
 */
function generatePW($length = 8, $myseed = 1)
{
    $dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
    mt_srand((double) microtime() * 1000000 * $myseed);
    for ($i = 1; $i <= (count($dummy) * 2); $i++) {
        $swap         = mt_rand(0, count($dummy) - 1);
        $tmp          = $dummy[$swap];
        $dummy[$swap] = $dummy[0];
        $dummy[0]     = $tmp;
    }

    return substr(implode('', $dummy), 0, $length);
}

/**
 * @param array $data
 * @return array|int
 */
function checkformat($data)
{
    $fmt = [];
    $cnt = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        if (in_array($data[$i], $GLOBALS['format'], true)) {
            $fmt[$i] = $data[$i];
        } else {
            $fmt[$i] = '';
        }
    }

    if ((int)$_POST['PasswortGenerieren'] !== 1) {
        if (!in_array('cPasswort', $fmt, true) || !in_array('cMail', $fmt, true)) {
            return -1;
        }
    } else {
        if (!in_array('cMail', $fmt, true)) {
            return -1;
        }
    }

    return $fmt;
}

/**
 * @param array $fmt
 * @param array $data
 * @return string
 */
function processImport($fmt, $data)
{
    $kunde                = new Kunde();
    $kunde->kKundengruppe = (int)$_POST['kKundengruppe'];
    $kunde->kSprache      = (int)$_POST['kSprache'];
    $kunde->cAbgeholt     = 'Y';
    $kunde->cSperre       = 'N';
    $kunde->cAktiv        = 'Y';
    $kunde->nRegistriert  = 1;
    $kunde->dErstellt     = 'now()';
    $cnt                  = count($data);
    for ($i = 0; $i < $cnt; $i++) {
        if (!empty($fmt[$i])) {
            $kunde->{$fmt[$i]} = $data[$i];
        }
    }
    if (!valid_email($kunde->cMail)) {
        return 'keine g&uuml;ltige Email ($kunde->cMail) ! &Uuml;bergehe diesen Datensatz.';
    }
    if ((int)$_POST['PasswortGenerieren'] !== 1) {
        if (!$kunde->cPasswort || $kunde->cPasswort === 'd41d8cd98f00b204e9800998ecf8427e') {
            return 'kein Passwort! &Uuml;bergehe diesen Datensatz. (Kann unregstrierter JTL Shop Kunde sein)';
        }
    }
    if (!$kunde->cNachname) {
        return 'kein Nachname! &Uuml;bergehe diesen Datensatz.';
    }

    $old_mail = Shop::DB()->select('tkunde', 'cMail', $kunde->cMail);
    if (isset($old_mail->kKunde) && $old_mail->kKunde > 0) {
        return "Kunde mit dieser Emailadresse bereits vorhanden: ($kunde->cMail)! &Uuml;bergehe Datensatz.";
    }
    if ($kunde->cAnrede === 'f' || strtolower($kunde->cAnrede) === 'frau') {
        $kunde->cAnrede = 'w';
    }
    if ($kunde->cAnrede === 'h' || strtolower($kunde->cAnrede) === 'herr') {
        $kunde->cAnrede = 'm';
    }
    if ($kunde->cNewsletter == 0 || $kunde->cNewsletter == 'NULL') {
        $kunde->cNewsletter = 'N';
    }
    if ($kunde->cNewsletter == 1) {
        $kunde->cNewsletter = 'Y';
    }

    if (empty($kunde->cLand)) {
        if (isset($_SESSION['kundenimport']['cLand']) && strlen($_SESSION['kundenimport']['cLand']) > 0) {
            $kunde->cLand = $_SESSION['kundenimport']['cLand'];
        } else {
            $oRes = Shop::DB()->query("
                SELECT cWert AS cLand 
                    FROM teinstellungen 
                    WHERE cName = 'kundenregistrierung_standardland'", 1
            );
            if (is_object($oRes) && isset($oRes->cLand) && strlen($oRes->cLand) > 0) {
                $_SESSION['kundenimport']['cLand'] = $oRes->cLand;
                $kunde->cLand                      = $oRes->cLand;
            }
        }
    }
    $cPasswortKlartext = '';
    if ((int)$_POST['PasswortGenerieren'] === 1) {
        $cPasswortKlartext = $kunde->generatePassword(12);
        $kunde->cPasswort  = $kunde->generatePasswordHash($cPasswortKlartext);
    }
    $oTMP              = new stdClass();
    $oTMP->cNachname   = $kunde->cNachname;
    $oTMP->cFirma      = $kunde->cFirma;
    $oTMP->cStrasse    = $kunde->cStrasse;
    $oTMP->cHausnummer = $kunde->cHausnummer;
    if ($kunde->insertInDB()) {
        if ((int)$_POST['PasswortGenerieren'] === 1) {
            $kunde->cPasswortKlartext = $cPasswortKlartext;
            $kunde->cNachname         = $oTMP->cNachname;
            $kunde->cFirma            = $oTMP->cFirma;
            $kunde->cStrasse          = $oTMP->cStrasse;
            $kunde->cHausnummer       = $oTMP->cHausnummer;
            $obj                      = new stdClass();
            $obj->tkunde              = $kunde;
            sendeMail(MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER, $obj);
        }

        return 'Datensatz OK. Importiere: ' . $kunde->cVorname . ' ' . $kunde->cNachname;
    }

    return 'Fehler beim Import dieser Zeile! Bitte in ShopRoot/logs/ nachschauen!';
}
