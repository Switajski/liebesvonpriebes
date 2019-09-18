<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('IMPORT_NEWSLETTER_RECEIVER_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

//jtl2
$format  = ['cAnrede', 'cVorname', 'cNachname', 'cEmail'];
$hinweis = '';
$fehler  = '';

if ((int)$_POST['newsletterimport'] === 1 &&
    isset($_POST['newsletterimport'], $_FILES['csv']['tmp_name']) &&
    validateToken() &&
    strlen($_FILES['csv']['tmp_name']) > 0
) {
    $file = fopen($_FILES['csv']['tmp_name'], 'r');
    if ($file !== false) {
        $row      = 0;
        $formatId = -1;
        $fmt      = [];
        while ($data = fgetcsv($file, 2000, ';', '"')) {
            if ($row == 0) {
                $hinweis .= 'Checke Kopfzeile ...';
                $fmt = checkformat($data);
                if ($fmt === -1) {
                    $fehler = 'Format nicht erkannt!';
                    break;
                } else {
                    $hinweis .= '<br /><br />Importiere...<br />';
                }
            } else {
                $hinweis .= '<br />Zeile ' . $row . ': ' . processImport($fmt, $data);
            }

            $row++;
        }
        fclose($file);
    }
}

$smarty->assign('sprachen', gibAlleSprachen())
       ->assign('kundengruppen', Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2))
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('newsletterimport.tpl');

/**
 * Class NewsletterEmpfaenger
 */
class NewsletterEmpfaenger
{
    public $cAnrede;
    public $cEmail;
    public $cVorname;
    public $cNachname;
    public $kKunde = 0;
    public $kSprache;
    public $cOptCode;
    public $cLoeschCode;
    public $dEingetragen;
    public $nAktiv = 1;
}

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
 * @param $cMail
 * @return bool
 */
function pruefeNLEBlacklist($cMail)
{
    $oNEB = Shop::DB()->select(
        'tnewsletterempfaengerblacklist',
        'cMail',
        StringHandler::filterXSS(strip_tags($cMail))
    );

    return (!empty($oNEB->cMail));
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
        // jtl-shop/issues#296
        if (!empty($data[$i]) && in_array($data[$i], $GLOBALS['format'], true)) {
            $fmt[$i] = $data[$i];
        }
    }
    if (!in_array('cEmail', $fmt, true)) {
        return -1;
    }

    return $fmt;
}

/**
 * OptCode erstellen und ueberpruefen
 * Werte fuer $dbfeld 'cOptCode','cLoeschCode'
 *
 * @param $dbfeld
 * @param $email
 * @return string
 */
function create_NewsletterCode($dbfeld, $email)
{
    $CodeNeu = md5($email . time() . rand(123, 456));
    while (!unique_NewsletterCode($dbfeld, $CodeNeu)) {
        $CodeNeu = md5($email . time() . rand(123, 456));
    }

    return $CodeNeu;
}

/**
 * @param $dbfeld
 * @param $code
 * @return bool
 */
function unique_NewsletterCode($dbfeld, $code)
{
    $res = Shop::DB()->select('tnewsletterempfaenger', $dbfeld, $code);

    return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
}

/**
 * @param $fmt
 * @param $data
 * @return string
 */
function processImport($fmt, $data)
{
    if (isset($oTMP) && is_object($oTMP)) {
        unset($oTMP);
    }
    unset($newsletterempfaenger);

    $newsletterempfaenger = new NewsletterEmpfaenger();
    $cnt                  = count($fmt); // only columns that have no empty header jtl-shop/issues#296
    for ($i = 0; $i < $cnt; $i++) {
        if (!empty($fmt[$i])) {
            $newsletterempfaenger->{$fmt[$i]} = $data[$i];
        }
    }

    if (!valid_email($newsletterempfaenger->cEmail)) {
        return "keine g&uuml;ltige Email ($newsletterempfaenger->cEmail)! &Uuml;bergehe diesen Datensatz.";
    }
    // NewsletterEmpfaengerBlacklist
    if (pruefeNLEBlacklist($newsletterempfaenger->cEmail)) {
        return "keine g&uuml;ltige Email ($newsletterempfaenger->cEmail)! " .
            "Kunde hat sich auf die Blacklist setzen lassen! &Uuml;bergehe diesen Datensatz.";
    }

    if (!$newsletterempfaenger->cNachname) {
        return 'kein Nachname! &Uuml;bergehe diesen Datensatz.';
    }

    $old_mail = Shop::DB()->select('tnewsletterempfaenger', 'cEmail', $newsletterempfaenger->cEmail);
    if (isset($old_mail->kNewsletterEmpfaenger) && $old_mail->kNewsletterEmpfaenger > 0) {
        return "Newsletterempf&auml;nger mit dieser Emailadresse bereits vorhanden: (" .
            $newsletterempfaenger->cEmail . ")! &Uuml;bergehe Datensatz.";
    }

    if ($newsletterempfaenger->cAnrede === 'f') {
        $newsletterempfaenger->cAnrede = 'Frau';
    }
    if ($newsletterempfaenger->cAnrede === 'm' || $newsletterempfaenger->cAnrede === 'h') {
        $newsletterempfaenger->cAnrede = 'Herr';
    }
    $newsletterempfaenger->cOptCode    = create_NewsletterCode('cOptCode', $newsletterempfaenger->cEmail);
    $newsletterempfaenger->cLoeschCode = create_NewsletterCode('cLoeschCode', $newsletterempfaenger->cEmail);
    // Datum  des Eintrags setzen
    $newsletterempfaenger->dEingetragen = 'now()';
    $newsletterempfaenger->kSprache     = $_POST['kSprache'];
    // Ist der Newsletterempfaenger registrierter Kunde?
    $newsletterempfaenger->kKunde = 0;
    $KundenDaten                  = Shop::DB()->select('tkunde', 'cMail', $newsletterempfaenger->cEmail);
    if ($KundenDaten->kKunde > 0) {
        $newsletterempfaenger->kKunde   = $KundenDaten->kKunde;
        $newsletterempfaenger->kSprache = $KundenDaten->kSprache;
    }
    $oTMP               = new stdClass();
    $oTMP->cAnrede      = $newsletterempfaenger->cAnrede;
    $oTMP->cVorname     = $newsletterempfaenger->cVorname;
    $oTMP->cNachname    = $newsletterempfaenger->cNachname;
    $oTMP->kKunde       = $newsletterempfaenger->kKunde;
    $oTMP->cEmail       = $newsletterempfaenger->cEmail;
    $oTMP->dEingetragen = $newsletterempfaenger->dEingetragen;
    $oTMP->kSprache     = $newsletterempfaenger->kSprache;
    $oTMP->cOptCode     = $newsletterempfaenger->cOptCode;
    $oTMP->cLoeschCode  = $newsletterempfaenger->cLoeschCode;
    $oTMP->nAktiv       = $newsletterempfaenger->nAktiv;
    // In DB schreiben
    if (Shop::DB()->insert('tnewsletterempfaenger', $oTMP)) {
        // NewsletterEmpfaengerHistory fuettern
        $oTMP               = new stdClass();
        $oTMP->cAnrede      = $newsletterempfaenger->cAnrede;
        $oTMP->cVorname     = $newsletterempfaenger->cVorname;
        $oTMP->cNachname    = $newsletterempfaenger->cNachname;
        $oTMP->kKunde       = $newsletterempfaenger->kKunde;
        $oTMP->cEmail       = $newsletterempfaenger->cEmail;
        $oTMP->dEingetragen = $newsletterempfaenger->dEingetragen;
        $oTMP->kSprache     = $newsletterempfaenger->kSprache;
        $oTMP->cOptCode     = $newsletterempfaenger->cOptCode;
        $oTMP->cLoeschCode  = $newsletterempfaenger->cLoeschCode;
        $oTMP->cAktion      = 'Daten-Import';
        $res                = Shop::DB()->insert('tnewsletterempfaengerhistory', $oTMP);
        if ($res) {
            return 'Datensatz OK. Importiere: ' .
                $newsletterempfaenger->cVorname . ' ' .
                $newsletterempfaenger->cNachname;
        }
    }

    return 'Fehler beim Import dieser Zeile!';
}
