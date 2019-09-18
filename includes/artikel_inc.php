<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int       $kArtikel
 * @param bool|null $isParent
 * @return stdClass|null
 */
function gibArtikelXSelling($kArtikel, $isParent = null)
{
    $kArtikel = (int)$kArtikel;
    if ($kArtikel <= 0) {
        return null;
    }
    $xSelling = new stdClass();
    $config   = Shop::getSettings([CONF_ARTIKELDETAILS]);
    $config   = $config['artikeldetails'];
    if ($config['artikeldetails_xselling_standard_anzeigen'] === 'Y') {
        $xSelling->Standard = new stdClass();
        $cSQLLager          = gibLagerfilter();
        $xsell              = Shop::DB()->query(
            "SELECT txsell.* FROM txsell, tartikel
                WHERE txsell.kXSellArtikel = tartikel.kArtikel {$cSQLLager}
                    AND txsell.kArtikel = {$kArtikel}
                ORDER BY tartikel.cName", 2
        );
        if (count($xsell) > 0) {
            $xsellgruppen = [];
            foreach ($xsell as $xs) {
                $xs->kXSellGruppe = (int)$xs->kXSellGruppe;
                if (!in_array($xs->kXSellGruppe, $xsellgruppen, true)) {
                    $xsellgruppen[] = (int)$xs->kXSellGruppe;
                }
            }
            $xSelling->Standard->XSellGruppen = [];
            $xsCount                          = count($xsellgruppen);
            $defaultOptions                   = Artikel::getDefaultOptions();
            for ($i = 0; $i < $xsCount; ++$i) {
                if (Shop::getLanguage() > 0) {
                    //lokalisieren
                    $objSprache = Shop::DB()->select(
                        'txsellgruppe',
                        'kXSellGruppe',
                        (int)$xsellgruppen[$i],
                        'kSprache',
                        Shop::getLanguage()
                    );
                    if (!isset($objSprache->cName)) {
                        continue;
                    }
                    $xSelling->Standard->XSellGruppen[$i]               = new stdClass();
                    $xSelling->Standard->XSellGruppen[$i]->Name         = $objSprache->cName;
                    $xSelling->Standard->XSellGruppen[$i]->Beschreibung = $objSprache->cBeschreibung;
                }
                $xSelling->Standard->XSellGruppen[$i]->Artikel = [];
                foreach ($xsell as $xs) {
                    if ($xs->kXSellGruppe === $xsellgruppen[$i]) {
                        $artikel = new Artikel();
                        $artikel->fuelleArtikel($xs->kXSellArtikel, $defaultOptions);
                        if ($artikel->kArtikel > 0 && $artikel->aufLagerSichtbarkeit()) {
                            $xSelling->Standard->XSellGruppen[$i]->Artikel[] = $artikel;
                        }
                    }
                }
            }
        }
    }
    if (isset($config['artikeldetails_xselling_kauf_anzeigen']) && $config['artikeldetails_xselling_kauf_anzeigen'] === 'Y') {
        $anzahl = (int)$config['artikeldetails_xselling_kauf_anzahl'];
        if ($isParent === null) {
            $isParent = ArtikelHelper::isParent($kArtikel);
        }
        if ($isParent === true) {
            if (isset($config['artikeldetails_xselling_kauf_parent']) && $config['artikeldetails_xselling_kauf_parent'] === 'Y') {
                $selectorXSellArtikel     = 'IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel)';
                $filterXSellParentArtikel = 'IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel)';
            } else {
                $selectorXSellArtikel     = 'txsellkauf.kXSellArtikel';
                $filterXSellParentArtikel = 'tartikel.kVaterArtikel';
            }
            $xsell = Shop::DB()->query(
                "SELECT {$kArtikel} AS kArtikel,
                        {$selectorXSellArtikel} AS kXSellArtikel,
                        SUM(txsellkauf.nAnzahl) nAnzahl
                    FROM txsellkauf
                    JOIN tartikel ON tartikel.kArtikel = txsellkauf.kXSellArtikel
                    WHERE (txsellkauf.kArtikel IN (
                            SELECT tartikel.kArtikel
                            FROM tartikel
                            WHERE tartikel.kVaterArtikel = {$kArtikel}
                        ) OR txsellkauf.kArtikel = {$kArtikel})
                        AND {$filterXSellParentArtikel} != {$kArtikel}
                    GROUP BY 1, 2
                    ORDER BY SUM(txsellkauf.nAnzahl) DESC
                    LIMIT {$anzahl}", 2
            );
        } else {
            if (isset($config['artikeldetails_xselling_kauf_parent']) && $config['artikeldetails_xselling_kauf_parent'] === 'Y') {
                $xsell = Shop::DB()->query(
                    "SELECT txsellkauf.kArtikel,
                        IF(tartikel.kVaterArtikel = 0, txsellkauf.kXSellArtikel, tartikel.kVaterArtikel) AS kXSellArtikel,
                        SUM(txsellkauf.nAnzahl) nAnzahl
                        FROM txsellkauf
                        JOIN tartikel 
                            ON tartikel.kArtikel = txsellkauf.kXSellArtikel
                        WHERE txsellkauf.kArtikel = {$kArtikel}
                            AND (tartikel.kVaterArtikel != (
                                SELECT tartikel.kVaterArtikel
                                FROM tartikel
                                WHERE tartikel.kArtikel = {$kArtikel}
                            ) OR tartikel.kVaterArtikel = 0)
                        GROUP BY 1, 2
                        ORDER BY SUM(txsellkauf.nAnzahl) DESC
                        LIMIT {$anzahl}", 2
                );
            } else {
                $xsell = Shop::DB()->selectAll(
                    'txsellkauf',
                    'kArtikel',
                    $kArtikel,
                    '*',
                    'nAnzahl DESC',
                    $anzahl
                );
            }
        }
        $xsellCount2 = is_array($xsell) ? count($xsell) : 0;
        if ($xsellCount2 > 0) {
            if (!isset($xSelling->Kauf)) {
                $xSelling->Kauf = new stdClass();
            }
            $xSelling->Kauf->Artikel = [];
            $defaultOptions          = Artikel::getDefaultOptions();
            foreach ($xsell as $xs) {
                $artikel = new Artikel();
                $artikel->fuelleArtikel($xs->kXSellArtikel, $defaultOptions);
                if ($artikel->kArtikel > 0 && $artikel->aufLagerSichtbarkeit()) {
                    $xSelling->Kauf->Artikel[] = $artikel;
                }
            }
        }
    }
    executeHook(HOOK_ARTIKEL_INC_XSELLING, [
        'kArtikel' => $kArtikel,
        'xSelling' => &$xSelling
    ]);

    return $xSelling;
}

/**
 *
 */
function bearbeiteFrageZumProdukt()
{
    $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
    if ($conf['artikeldetails']['artikeldetails_fragezumprodukt_anzeigen'] !== 'N') {
        $fehlendeAngaben = gibFehlendeEingabenProduktanfrageformular();
        Shop::Smarty()->assign('fehlendeAngaben_fragezumprodukt', $fehlendeAngaben);
        $nReturnValue = eingabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT_PLAUSI);

        if ($nReturnValue) {
            if (!floodSchutzProduktanfrage((int)$conf['artikeldetails']['produktfrage_sperre_minuten'])) {
                $oCheckBox     = new CheckBox();
                $kKundengruppe = Kundengruppe::getCurrent();
                $oAnfrage      = baueProduktanfrageFormularVorgaben();

                executeHook(HOOK_ARTIKEL_INC_FRAGEZUMPRODUKT);

                // Set empty string if it not exists
                if (empty($oAnfrage->cNachname)) {
                    $oAnfrage->cNachname = '';
                }
                // Set empty string if it not exists
                if (empty($oAnfrage->cVorname)) {
                    $oAnfrage->cVorname = '';
                }
                // CheckBox Spezialfunktion ausfuehren
                $oCheckBox->triggerSpecialFunction(
                    CHECKBOX_ORT_FRAGE_ZUM_PRODUKT,
                    $kKundengruppe,
                    true,
                    $_POST,
                    ['oKunde' => $oAnfrage, 'oNachricht' => $oAnfrage]
                )->checkLogging(CHECKBOX_ORT_FRAGE_ZUM_PRODUKT, $kKundengruppe, $_POST, true);
                sendeProduktanfrage();
            } else {
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('questionNotPossible', 'messages');
            }
        } else {
            if (isset($fehlendeAngaben['email']) && $fehlendeAngaben['email'] === 3) {
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('blockedEmail', 'global');
            } else {
                Shop::Smarty()->assign('Anfrage', baueProduktanfrageFormularVorgaben());
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('fillOutQuestion', 'messages');
            }
        }
    } else {
        $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('productquestionPleaseLogin', 'errorMessages');
    }
}

/**
 * @return array
 */
function gibFehlendeEingabenProduktanfrageformular()
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_ARTIKELDETAILS, CONF_GLOBAL]);
    if (!$_POST['nachricht']) {
        $ret['nachricht'] = 1;
    }
    if (pruefeEmailblacklist($_POST['email'])) {
        $ret['email'] = 3;
    }
    if (!valid_email($_POST['email'])) {
        $ret['email'] = 2;
    }
    if (!$_POST['email']) {
        $ret['email'] = 1;
    }
    if ($conf['artikeldetails']['produktfrage_abfragen_vorname'] === 'Y' && !$_POST['vorname']) {
        $ret['vorname'] = 1;
    }
    if ($conf['artikeldetails']['produktfrage_abfragen_nachname'] === 'Y' && !$_POST['nachname']) {
        $ret['nachname'] = 1;
    }
    if ($conf['artikeldetails']['produktfrage_abfragen_firma'] === 'Y' && !$_POST['firma']) {
        $ret['firma'] = 1;
    }
    if ($conf['artikeldetails']['produktfrage_abfragen_fax'] === 'Y' && !$_POST['fax']) {
        $ret['fax'] = 1;
    }
    if ($conf['artikeldetails']['produktfrage_abfragen_tel'] === 'Y' && !$_POST['tel']) {
        $ret['tel'] = 1;
    }
    if ($conf['artikeldetails']['produktfrage_abfragen_mobil'] === 'Y' && !$_POST['mobil']) {
        $ret['mobil'] = 1;
    }
    if ($conf['artikeldetails']['produktfrage_abfragen_captcha'] !== 'N' && !validateCaptcha($_POST)) {
        $ret['captcha'] = 2;
    }
    // CheckBox Plausi
    $oCheckBox     = new CheckBox();
    $kKundengruppe = Kundengruppe::getCurrent();
    $ret           = array_merge(
        $ret,
        $oCheckBox->validateCheckBox(CHECKBOX_ORT_FRAGE_ZUM_PRODUKT, $kKundengruppe, $_POST, true)
    );

    return $ret;
}

/**
 * @return stdClass
 */
function baueProduktanfrageFormularVorgaben()
{
    $msg             = new stdClass();
    $msg->cNachricht = isset($_POST['nachricht']) ? StringHandler::filterXSS($_POST['nachricht']) : null;
    $msg->cAnrede    = isset($_POST['anrede']) ? StringHandler::filterXSS($_POST['anrede']) : null;
    $msg->cVorname   = isset($_POST['vorname']) ? StringHandler::filterXSS($_POST['vorname']) : null;
    $msg->cNachname  = isset($_POST['nachname']) ? StringHandler::filterXSS($_POST['nachname']) : null;
    $msg->cFirma     = isset($_POST['firma']) ? StringHandler::filterXSS($_POST['firma']) : null;
    $msg->cMail      = isset($_POST['email']) ? StringHandler::filterXSS($_POST['email']) : null;
    $msg->cFax       = isset($_POST['fax']) ? StringHandler::filterXSS($_POST['fax']) : null;
    $msg->cTel       = isset($_POST['tel']) ? StringHandler::filterXSS($_POST['tel']) : null;
    $msg->cMobil     = isset($_POST['mobil']) ? StringHandler::filterXSS($_POST['mobil']) : null;
    if (strlen($msg->cAnrede) === 1) {
        if ($msg->cAnrede === 'm') {
            $msg->cAnredeLocalized = Shop::Lang()->get('salutationM', 'global');
        } elseif ($msg->cAnrede === 'w') {
            $msg->cAnredeLocalized = Shop::Lang()->get('salutationW', 'global');
        }
    }

    return $msg;
}

/**
 * @param array $fehlendeAngaben
 * @return int
 */
function eingabenKorrekt($fehlendeAngaben)
{
    foreach ($fehlendeAngaben as $angabe) {
        if ($angabe > 0) {
            return 0;
        }
    }

    return 1;
}

/**
 *
 */
function sendeProduktanfrage()
{
    require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

    $conf               = Shop::getSettings([CONF_EMAILS, CONF_ARTIKELDETAILS, CONF_GLOBAL]);
    $Objekt             = new stdClass();
    $Objekt->tartikel   = $GLOBALS['AktuellerArtikel'];
    $Objekt->tnachricht = baueProduktanfrageFormularVorgaben();
    $empfaengerName     = '';
    if ($Objekt->tnachricht->cVorname) {
        $empfaengerName = $Objekt->tnachricht->cVorname . ' ';
    }
    if ($Objekt->tnachricht->cNachname) {
        $empfaengerName .= $Objekt->tnachricht->cNachname;
    }
    if ($Objekt->tnachricht->cFirma) {
        if ($Objekt->tnachricht->cNachname || $Objekt->tnachricht->cVorname) {
            $empfaengerName .= ' - ';
        }
        $empfaengerName .= $Objekt->tnachricht->cFirma;
    }
    $mail = new stdClass();
    if (isset($conf['artikeldetails']['artikeldetails_fragezumprodukt_email'])) {
        $mail->toEmail = $conf['artikeldetails']['artikeldetails_fragezumprodukt_email'];
    }
    if (empty($mail->toEmail)) {
        $mail->toEmail = $conf['emails']['email_master_absender'];
    }
    $mail->toName       = $conf['global']['global_shopname'];
    $mail->replyToEmail = $Objekt->tnachricht->cMail;
    $mail->replyToName  = $empfaengerName;
    $Objekt->mail       = $mail;

    sendeMail(MAILTEMPLATE_PRODUKTANFRAGE, $Objekt);

    if ($conf['artikeldetails']['produktfrage_kopiekunde'] === 'Y') {
        $mail->toEmail      = $Objekt->tnachricht->cMail;
        $mail->toName       = $empfaengerName;
        $mail->replyToEmail = $Objekt->tnachricht->cMail;
        $mail->replyToName  = $empfaengerName;
        $Objekt->mail       = $mail;
        sendeMail(MAILTEMPLATE_PRODUKTANFRAGE, $Objekt);
    }
    $ProduktanfrageHistory             = new stdClass();
    $ProduktanfrageHistory->kSprache   = Shop::$kSprache;
    $ProduktanfrageHistory->kArtikel   = Shop::$kArtikel;
    $ProduktanfrageHistory->cAnrede    = $Objekt->tnachricht->cAnrede;
    $ProduktanfrageHistory->cVorname   = $Objekt->tnachricht->cVorname;
    $ProduktanfrageHistory->cNachname  = $Objekt->tnachricht->cNachname;
    $ProduktanfrageHistory->cFirma     = $Objekt->tnachricht->cFirma;
    $ProduktanfrageHistory->cTel       = $Objekt->tnachricht->cTel;
    $ProduktanfrageHistory->cMobil     = $Objekt->tnachricht->cMobil;
    $ProduktanfrageHistory->cFax       = $Objekt->tnachricht->cFax;
    $ProduktanfrageHistory->cMail      = $Objekt->tnachricht->cMail;
    $ProduktanfrageHistory->cNachricht = $Objekt->tnachricht->cNachricht;
    $ProduktanfrageHistory->cIP        = gibIP();
    $ProduktanfrageHistory->dErstellt  = 'now()';

    $kProduktanfrageHistory        = Shop::DB()->insert('tproduktanfragehistory', $ProduktanfrageHistory);
    $GLOBALS['PositiveFeedback'][] = Shop::Lang()->get('thankYouForQuestion', 'messages');
    // campaign
    if (isset($_SESSION['Kampagnenbesucher'])) {
        setzeKampagnenVorgang(KAMPAGNE_DEF_FRAGEZUMPRODUKT, $kProduktanfrageHistory, 1.0);
    }
}

/**
 * @deprecated deprecated since version 4.3
 */
function sendeArtikelWeiterempfehlen()
{
}

/**
 * @param int $min
 * @return bool
 */
function floodSchutzProduktanfrage($min = 0)
{
    $min = (int)$min;
    if ($min <= 0) {
        return false;
    }
    $history = Shop::DB()->executeQueryPrepared(
        "SELECT kProduktanfrageHistory
            FROM tproduktanfragehistory
            WHERE cIP = :ip
                AND date_sub(now(), INTERVAL :min MINUTE) < dErstellt",
        ['ip'  => gibIP(), 'min' => $min],
        1
    );

    return (isset($history->kProduktanfrageHistory) && $history->kProduktanfrageHistory > 0);
}

/**
 * @deprecated deprecated since version 4.3
 * @param int $min
 * @return bool
 */
function floodSchutzArtikelWeiterempfehlen($min = 0)
{
    return false;
}

/**
 *
 */
function bearbeiteBenachrichtigung()
{
    $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
    if ((int)$_POST['a'] > 0 &&
        isset($conf['artikeldetails']['benachrichtigung_nutzen']) &&
        $conf['artikeldetails']['benachrichtigung_nutzen'] !== 'N'
    ) {
        $fehlendeAngaben = gibFehlendeEingabenBenachrichtigungsformular();
        Shop::Smarty()->assign('fehlendeAngaben_benachrichtigung', $fehlendeAngaben);
        $nReturnValue = eingabenKorrekt($fehlendeAngaben);

        executeHook(HOOK_ARTIKEL_INC_BENACHRICHTIGUNG_PLAUSI);
        if ($nReturnValue) {
            if (!floodSchutzBenachrichtigung($conf['artikeldetails']['benachrichtigung_sperre_minuten'])) {
                $Benachrichtigung            = baueFormularVorgabenBenachrichtigung();
                $Benachrichtigung->kSprache  = Shop::getLanguage();
                $Benachrichtigung->kArtikel  = (int)$_POST['a'];
                $Benachrichtigung->cIP       = gibIP();
                $Benachrichtigung->dErstellt = 'now()';
                $Benachrichtigung->nStatus   = 0;
                $oCheckBox                   = new CheckBox();
                $kKundengruppe               = Kundengruppe::getCurrent();

                executeHook(HOOK_ARTIKEL_INC_BENACHRICHTIGUNG);

                // Set empty string if not exists
                if (empty($Benachrichtigung->cNachname)) {
                    $Benachrichtigung->cNachname = '';
                }
                // Set empty string if it not exists
                if (empty($Benachrichtigung->cVorname)) {
                    $Benachrichtigung->cVorname = '';
                }
                // CheckBox Spezialfunktion ausfuehren
                $oCheckBox->triggerSpecialFunction(
                    CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT,
                    $kKundengruppe,
                    true,
                    $_POST,
                    ['oKunde' => $Benachrichtigung, 'oNachricht' => $Benachrichtigung]
                )->checkLogging(CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT, $kKundengruppe, $_POST, true);

                $kVerfuegbarkeitsbenachrichtigung = Shop::DB()->queryPrepared('
                    INSERT INTO tverfuegbarkeitsbenachrichtigung 
                        (cVorname, cNachname, cMail, kSprache, kArtikel, cIP, dErstellt, nStatus) 
                        VALUES 
                        (:cVorname, :cNachname, :cMail, :kSprache, :kArtikel, :cIP, now(), :nStatus)
                        ON DUPLICATE KEY UPDATE 
                            cVorname = :cVorname, cNachname = :cNachname, ksprache = :kSprache, 
                            cIP = :cIP, dErstellt = now(), nStatus = :nStatus', get_object_vars($Benachrichtigung), 7
                );
                // Kampagne
                if (isset($_SESSION['Kampagnenbesucher'])) {
                    // Verfügbarkeitsbenachrichtigung
                    setzeKampagnenVorgang(KAMPAGNE_DEF_VERFUEGBARKEITSANFRAGE, $kVerfuegbarkeitsbenachrichtigung, 1.0);
                }
                $GLOBALS['PositiveFeedback'][] = Shop::Lang()->get('thankYouForNotificationSubscription', 'messages');
            } else {
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('notificationNotPossible', 'messages');
            }
        } else {
            if (isset($fehlendeAngaben['email']) && $fehlendeAngaben['email'] === 3) {
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('blockedEmail', 'global');
            } else {
                Shop::Smarty()->assign('Benachrichtigung', baueFormularVorgabenBenachrichtigung());
                $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('fillOutNotification', 'messages');
            }
        }
    }
}

/**
 * @return array
 */
function gibFehlendeEingabenBenachrichtigungsformular()
{
    $ret  = [];
    $conf = Shop::getSettings([CONF_ARTIKELDETAILS, CONF_GLOBAL]);
    if (!$_POST['email']) {
        $ret['email'] = 1;
    } elseif (!valid_email($_POST['email'])) {
        $ret['email'] = 2;
    }
    if (pruefeEmailblacklist($_POST['email'])) {
        $ret['email'] = 3;
    }
    if (empty($_POST['vorname']) && $conf['artikeldetails']['benachrichtigung_abfragen_vorname'] === 'Y') {
        $ret['vorname'] = 1;
    }
    if (empty($_POST['nachname']) && $conf['artikeldetails']['benachrichtigung_abfragen_nachname'] === 'Y') {
        $ret['nachname'] = 1;
    }
    if ($conf['artikeldetails']['benachrichtigung_abfragen_captcha'] !== 'N' && !validateCaptcha($_POST)) {
        $ret['captcha'] = 2;
    }
    // CheckBox Plausi
    $oCheckBox     = new CheckBox();
    $kKundengruppe = Kundengruppe::getCurrent();
    $ret           = array_merge(
        $ret,
        $oCheckBox->validateCheckBox(CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT, $kKundengruppe, $_POST, true)
    );

    return $ret;
}

/**
 * @return stdClass
 */
function baueFormularVorgabenBenachrichtigung()
{
    $msg  = new stdClass();
    $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
    if (!empty($_POST['vorname']) && $conf['artikeldetails']['benachrichtigung_abfragen_vorname'] !== 'N') {
        $msg->cVorname = StringHandler::filterXSS($_POST['vorname']);
    }
    if (!empty($_POST['nachname']) && $conf['artikeldetails']['benachrichtigung_abfragen_nachname'] !== 'N') {
        $msg->cNachname = StringHandler::filterXSS($_POST['nachname']);
    }
    if (!empty($_POST['email'])) {
        $msg->cMail = StringHandler::filterXSS($_POST['email']);
    }

    return $msg;
}

/**
 * @param int $min
 * @return bool
 */
function floodSchutzBenachrichtigung($min)
{
    $min = (int)$min;
    if (!$min) {
        return false;
    }
    $history = Shop::DB()->executeQueryPrepared(
        'SELECT kVerfuegbarkeitsbenachrichtigung
            FROM tverfuegbarkeitsbenachrichtigung
            WHERE cIP = :ip
            AND date_sub(now(), INTERVAL :min MINUTE) < dErstellt',
        ['ip' => gibIP(), 'min' => $min],
        1
    );

    return isset($history->kVerfuegbarkeitsbenachrichtigung) && $history->kVerfuegbarkeitsbenachrichtigung > 0;
}

/**
 * @param int $kArtikel
 * @param int $kKategorie
 * @return stdClass
 */
function gibNaviBlaettern($kArtikel, $kKategorie)
{
    $kArtikel   = (int)$kArtikel;
    $kKategorie = (int)$kKategorie;
    $navi       = new stdClass();
    // Wurde der Artikel von der Artikelübersicht aus angeklickt?
    if ($kArtikel > 0 &&
        isset($_SESSION['oArtikelUebersichtKey_arr']) &&
        is_array($_SESSION['oArtikelUebersichtKey_arr']) &&
        count($_SESSION['oArtikelUebersichtKey_arr']) > 0
    ) {
        // Such die Position des aktuellen Artikels im Array der Artikelübersicht
        $nArrayPos          = -1;
        $kArtikelVorheriger = 0;
        $kArtikelNaechster  = 0;
        foreach ($_SESSION['oArtikelUebersichtKey_arr'] as $i => $oArtikelUebersichtKey) {
            if (isset($oArtikelUebersichtKey->kArtikel) && (int)$oArtikelUebersichtKey->kArtikel === $kArtikel) {
                $nArrayPos = $i;
                break;
            }
        }
        if ($nArrayPos === 0) {
            // Artikel ist an der ersten Position => es gibt nur einen nächsten Artikel (oder keinen :))
            $kArtikelNaechster = isset($_SESSION['oArtikelUebersichtKey_arr'][$nArrayPos + 1]->kArtikel)
                ? $_SESSION['oArtikelUebersichtKey_arr'][$nArrayPos + 1]->kArtikel
                : null;
        } elseif ($nArrayPos === (count($_SESSION['oArtikelUebersichtKey_arr']) - 1)) {
            // Artikel ist an der letzten Position => es gibt nur einen voherigen Artikel
            $kArtikelVorheriger = $_SESSION['oArtikelUebersichtKey_arr'][$nArrayPos - 1]->kArtikel;
        } elseif ($nArrayPos !== -1) {
            $kArtikelNaechster  = $_SESSION['oArtikelUebersichtKey_arr'][$nArrayPos + 1]->kArtikel;
            $kArtikelVorheriger = $_SESSION['oArtikelUebersichtKey_arr'][$nArrayPos - 1]->kArtikel;
        }
        // Nächster Artikel
        if ($kArtikelNaechster > 0) {
            $navi->naechsterArtikel = new Artikel();
            $navi->naechsterArtikel->fuelleArtikel($kArtikelNaechster, Artikel::getDefaultOptions());

            if (!$navi->naechsterArtikel->kArtikel > 0) {
                unset($navi->naechsterArtikel);
            }
        }
        // Vorheriger Artikel
        if ($kArtikelVorheriger > 0) {
            $navi->vorherigerArtikel = new Artikel();
            $navi->vorherigerArtikel->fuelleArtikel($kArtikelVorheriger, Artikel::getDefaultOptions());

            if (!$navi->vorherigerArtikel->kArtikel > 0) {
                unset($navi->vorherigerArtikel);
            }
        }
    }
    // Ist der Besucher nicht von der Artikelübersicht gekommen?
    if ($kKategorie > 0 && (!isset($navi->vorherigerArtikel) && !isset($navi->naechsterArtikel))) {
        $objArr_pre = Shop::DB()->query(
            "SELECT tartikel.kArtikel
                FROM tkategorieartikel, tpreise, tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = tkategorieartikel.kArtikel
                    AND tkategorieartikel.kKategorie = $kKategorie
                    AND tpreise.kArtikel = tartikel.kArtikel
                    AND tartikel.kArtikel < $kArtikel
                    AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    " . gibLagerfilter() . "
                ORDER BY tartikel.kArtikel DESC
                LIMIT 1", 1
        );
        $objArr_next = Shop::DB()->query(
            "SELECT tartikel.kArtikel
                FROM tkategorieartikel, tpreise, tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.kArtikel = tkategorieartikel.kArtikel
                    AND tkategorieartikel.kKategorie = $kKategorie
                    AND tpreise.kArtikel = tartikel.kArtikel
                    AND tartikel.kArtikel > $kArtikel
                    AND tpreise.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    " . gibLagerfilter() . "
                ORDER BY tartikel.kArtikel
                LIMIT 1", 1
        );

        if (isset($objArr_pre->kArtikel) && $objArr_pre->kArtikel) {
            $navi->vorherigerArtikel = new Artikel();
            $navi->vorherigerArtikel->fuelleArtikel($objArr_pre->kArtikel, Artikel::getDefaultOptions());
        }
        if (isset($objArr_next->kArtikel) && $objArr_next->kArtikel) {
            $navi->naechsterArtikel = new Artikel();
            $navi->naechsterArtikel->fuelleArtikel($objArr_next->kArtikel, Artikel::getDefaultOptions());
        }
    }

    return $navi;
}

/**
 * @param $nEigenschaftWert
 * @return array
 */
function gibNichtErlaubteEigenschaftswerte($nEigenschaftWert)
{
    $nEigenschaftWert = (int)$nEigenschaftWert;
    if ($nEigenschaftWert) {
        $arNichtErlaubteEigenschaftswerte  = Shop::DB()->selectAll(
            'teigenschaftwertabhaengigkeit',
            'kEigenschaftWert',
            $nEigenschaftWert,
            'kEigenschaftWertZiel AS EigenschaftWert'
        );
        $arNichtErlaubteEigenschaftswerte2 = Shop::DB()->selectAll(
            'teigenschaftwertabhaengigkeit',
            'kEigenschaftWertZiel',
            $nEigenschaftWert,
            'kEigenschaftWert AS EigenschaftWert'
        );

        return array_merge(
            $arNichtErlaubteEigenschaftswerte,
            $arNichtErlaubteEigenschaftswerte2
        );
    }

    return [];
}

/**
 * @param null|string|array $cRedirectParam
 * @param bool              $bRenew
 * @param null|Artikel      $oArtikel
 * @param null|float        $fAnzahl
 * @param int               $kKonfigitem
 * @return array
 */
function baueArtikelhinweise($cRedirectParam = null, $bRenew = false, $oArtikel = null, $fAnzahl = null, $kKonfigitem = 0)
{
    if ($cRedirectParam === null && isset($_GET['r'])) {
        $cRedirectParam = $_GET['r'];
    }
    if ($bRenew || !isset($GLOBALS['Artikelhinweise']) || !is_array($GLOBALS['Artikelhinweise'])) {
        $GLOBALS['Artikelhinweise'] = [];
    }
    if ($bRenew || !isset($GLOBALS['PositiveFeedback']) || !is_array($GLOBALS['PositiveFeedback'])) {
        $GLOBALS['PositiveFeedback'] = [];
    }
    if ($cRedirectParam) {
        $hin_arr = is_array($cRedirectParam) ? $cRedirectParam : explode(',', $cRedirectParam);
        $hin_arr = array_unique($hin_arr);

        foreach ($hin_arr as $hin) {
            switch ($hin) {
                case R_LAGERVAR:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('quantityNotAvailableVar', 'messages');
                    break;
                case R_VARWAEHLEN:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('chooseVariations', 'messages');
                    break;
                case R_VORBESTELLUNG:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('preorderNotPossible', 'messages');
                    break;
                case R_LOGIN:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('pleaseLogin', 'messages');
                    break;
                case R_LAGER:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('quantityNotAvailable', 'messages');
                    break;
                case R_MINDESTMENGE:
                    if ($oArtikel === null) {
                        $oArtikel = $GLOBALS['AktuellerArtikel'];
                    }
                    if ($fAnzahl === null) {
                        $fAnzahl = $_GET['n'];
                    }
                    $GLOBALS['Artikelhinweise'][] = lang_mindestbestellmenge($oArtikel, $fAnzahl, $kKonfigitem);
                    break;
                case R_LOGIN_WUNSCHLISTE:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('loginWishlist', 'messages');
                    break;
                case R_MAXBESTELLMENGE:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkMaxorderlimit', 'messages');
                    break;
                case R_ARTIKELABNAHMEINTERVALL:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkPurchaseintervall', 'messages');
                    break;
                case R_UNVERKAEUFLICH:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkUnsalable', 'messages');
                    break;
                case R_AUFANFRAGE:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('wkOnrequest', 'messages');
                    break;
                case R_EMPTY_TAG:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('tagArtikelEmpty', 'messages');
                    break;
                case R_EMPTY_VARIBOX:
                    $GLOBALS['Artikelhinweise'][] = Shop::Lang()->get('artikelVariBoxEmpty', 'messages');
                    break;
                default:
                    break;
            }
            executeHook(HOOK_ARTIKEL_INC_ARTIKELHINWEISSWITCH);
        }
    }

    return $GLOBALS['Artikelhinweise'];
}

/**
 * @param Artikel $AktuellerArtikel
 * @return mixed
 */
function bearbeiteProdukttags($AktuellerArtikel)
{
    // Wurde etwas von der Tag Form gepostet?
    if (verifyGPCDataInteger('produktTag') === 1) {
        $tag = StringHandler::filterXSS(verifyGPDataString('tag'));
        // Wurde ein Tag gepostet?
        if (strlen($tag) > 0) {
            $conf = Shop::getSettings([CONF_ARTIKELDETAILS]);
            // Prüfe ob Kunde eingeloggt
            if (empty($_SESSION['Kunde']->kKunde) && $conf['artikeldetails']['tagging_freischaltung'] === 'Y') {
                $linkHelper = LinkHelper::getInstance();
                header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) .
                    '?a=' . (int)$_POST['a'] . '&tag=' .
                    StringHandler::htmlentities(StringHandler::filterXSS($_POST['tag'])) .
                    '&r=' . R_LOGIN_TAG . '&produktTag=1', true, 303);
                exit();
            }
            // Posts die älter als 24 Stunden sind löschen
            Shop::DB()->query("DELETE FROM ttagkunde WHERE dZeit < DATE_SUB(now(),INTERVAL 1 MONTH)", 4);
            // Admin Einstellungen prüfen
            if (($conf['artikeldetails']['tagging_freischaltung'] === 'Y' &&
                    isset($_SESSION['Kunde']->kKunde) &&
                    $_SESSION['Kunde']->kKunde > 0) ||
                $conf['artikeldetails']['tagging_freischaltung'] === 'O'
            ) {
                $ip = gibIP();
                // Ist eine Kunde eingeloggt?
                if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                    $count_tag_postings = Shop::DB()->query(
                        "SELECT count(kTagKunde) AS Anzahl
                            FROM ttagkunde
                            WHERE dZeit > DATE_SUB(now(),INTERVAL 1 DAY)
                                AND kKunde = " . (int)$_SESSION['Kunde']->kKunde, 1
                    );
                    $kKunde = (int)$_SESSION['Kunde']->kKunde;
                } else { // Wenn nicht, dann hat ein anonymer Besucher ein Tag gepostet
                    $count_tag_postings = Shop::DB()->executeQueryPrepared(
                        "SELECT count(kTagKunde) AS Anzahl FROM ttagkunde
                            WHERE dZeit > DATE_SUB(now(), INTERVAL 1 DAY)
                                AND cIP = :ip
                                AND kKunde = 0",
                        ['ip' => $ip],
                        1
                    );
                    $kKunde = 0;
                }
                // Wenn die max. eingestellte Anzahl der Posts pro Tag nicht überschritten wurde
                if ($count_tag_postings->Anzahl < (int)$conf['artikeldetails']['tagging_max_ip_count']) {
                    if ($kKunde === 0 && $conf['artikeldetails']['tagging_freischaltung'] === 'Y') {
                        return Shop::Lang()->get('pleaseLoginToAddTags', 'messages');
                    }
                    // Prüfe, ob der Tag bereits gemappt wurde
                    $tagmapping_objTMP = Shop::DB()->select(
                        'ttagmapping',
                        'kSprache',
                        Shop::getLanguage(),
                        'cName',
                        Shop::DB()->escape($tag)
                    );
                    $tagmapping_obj    = $tagmapping_objTMP;
                    if (isset($tagmapping_obj->cNameNeu) && strlen($tagmapping_obj->cNameNeu) > 0) {
                        $tag = $tagmapping_obj->cNameNeu;
                    }
                    // Prüfe ob der Tag bereits vorhanden ist
                    $tag_obj = Shop::DB()->select('ttag', 'kSprache', Shop::getLanguage(), 'cName', $tag);
                    $kTag    = isset($tag_obj->kTag) ? (int)$tag_obj->kTag : null;
                    if ($kTag > 0) {
                        $count = Shop::DB()->query(
                            "UPDATE ttagartikel
                                SET nAnzahlTagging = nAnzahlTagging+1
                                WHERE kTag = " . $kTag . "
                                    AND kArtikel = " . (int)$AktuellerArtikel->kArtikel, 3
                        );
                        if (!$count) {
                            $neuerTag                 = new stdClass();
                            $neuerTag->kTag           = $kTag;
                            $neuerTag->kArtikel       = (int)$AktuellerArtikel->kArtikel;
                            $neuerTag->nAnzahlTagging = 1;
                            Shop::DB()->insert('ttagartikel', $neuerTag);
                        }
                    } else {
                        require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
                        $neuerTag           = new stdClass();
                        $neuerTag->kSprache = Shop::getLanguage();
                        $neuerTag->cName    = $tag;
                        $neuerTag->cSeo     = getSeo($tag);
                        $neuerTag->cSeo     = checkSeo($neuerTag->cSeo);
                        $neuerTag->nAktiv   = 0;
                        $kTag               = Shop::DB()->insert('ttag', $neuerTag);

                        if ($kTag > 0) {
                            $neuerTag                 = new stdClass();
                            $neuerTag->kTag           = $kTag;
                            $neuerTag->kArtikel       = $AktuellerArtikel->kArtikel;
                            $neuerTag->nAnzahlTagging = 1;
                            Shop::DB()->insert('ttagartikel', $neuerTag);
                        }
                    }
                    $neuerTagKunde         = new stdClass();
                    $neuerTagKunde->kTag   = $kTag;
                    $neuerTagKunde->kKunde = $kKunde;
                    $neuerTagKunde->cIP    = $ip;
                    $neuerTagKunde->dZeit  = 'now()';
                    Shop::DB()->insert('ttagkunde', $neuerTagKunde);

                    if (isset($tag_obj->nAktiv) && (int)$tag_obj->nAktiv === 0) {
                        return Shop::Lang()->get('tagAcceptedWaitCheck', 'messages');
                    }

                    return Shop::Lang()->get('tagAccepted', 'messages');
                }

                return Shop::Lang()->get('maxTagsExceeded', 'messages');
            }
        } elseif (isset($_POST['einloggen'])) {
            $linkHelper = LinkHelper::getInstance();
            header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) .
                '?a=' . (int)$_POST['a'] . '&r=' . R_LOGIN_TAG, true, 303);
            exit();
        } else {
            $url = (!empty($AktuellerArtikel->cURLFull))
                ? ($AktuellerArtikel->cURLFull . '?')
                : (Shop::getURL() . '/?a=' . (int)$_POST['a'] . '&');
            header('Location: ' . $url . 'r=' . R_EMPTY_TAG, true, 303);
            exit();
        }
    }

    return null;
}

/**
 * Baue Blätter Navi - Dient für die Blätternavigation unter Bewertungen in der Artikelübersicht
 *
 * @param int $bewertung_seite
 * @param int $bewertung_sterne
 * @param int $nAnzahlBewertungen
 * @param int $nAnzahlSeiten
 * @return stdClass
 */
function baueBewertungNavi($bewertung_seite, $bewertung_sterne, $nAnzahlBewertungen, $nAnzahlSeiten = 0)
{
    $oBlaetterNavi         = new stdClass();
    $oBlaetterNavi->nAktiv = 0;
    if (!$nAnzahlSeiten) {
        $nAnzahlSeiten = 10;
    }
    // Ist die Anzahl der Bewertungen für einen bestimmten Artikel, in einer bestimmten Sprache größer als
    // die im Backend eingestellte maximale Anzahl an Bewertungen für eine Seite?
    if ((int)$nAnzahlBewertungen > (int)$nAnzahlSeiten) {
        $nBlaetterAnzahl_arr = [];
        // Anzahl an Seiten
        $nSeiten     = ceil((int)$nAnzahlBewertungen / (int)$nAnzahlSeiten);
        $nMaxAnzeige = 5; // Zeige in der Navigation nur maximal X Seiten an
        $nAnfang     = 0; // Wenn die aktuelle Seite - $nMaxAnzeige größer 0 ist, wird nAnfang gesetzt
        $nEnde       = 0; // Wenn die aktuelle Seite + $nMaxAnzeige <= $nSeitenist, wird nEnde gesetzt
        $nVoherige   = $bewertung_seite - 1; // Zum zurück blättern in der Navigation
        if ($nVoherige === 0) {
            $nVoherige = 1;
        }
        $nNaechste = $bewertung_seite + 1; // Zum vorwärts blättern in der Navigation
        if ($nNaechste >= $nSeiten) {
            $nNaechste = $nSeiten;
        }
        // Ist die maximale Anzahl an Seiten > als die Anzahl erlaubter Seiten in der Navigation?
        if ($nSeiten > $nMaxAnzeige) {
            // Diese Variablen ermitteln die aktuellen Seiten in der Navigation, die angezeigt werden sollen.
            // Begrenzt durch $nMaxAnzeige.
            // Ist die aktuelle Seite nach dem abzug der Begrenzung größer oder gleich 1?
            if (($bewertung_seite - $nMaxAnzeige) >= 1) {
                $nAnfang = 1;
                $nVon    = ($bewertung_seite - $nMaxAnzeige) + 1;
            } else {
                $nAnfang = 0;
                $nVon    = 1;
            }
            // Ist die aktuelle Seite nach dem addieren der Begrenzung kleiner als die maximale Anzahl der Seiten
            if (($bewertung_seite + $nMaxAnzeige) < $nSeiten) {
                $nEnde = $nSeiten;
                $nBis  = ($bewertung_seite + $nMaxAnzeige) - 1;
            } else {
                $nEnde = 0;
                $nBis  = $nSeiten;
            }
            // Baue die Seiten für die Navigation
            for ($i = $nVon; $i <= $nBis; $i++) {
                $nBlaetterAnzahl_arr[] = $i;
            }
        } else {
            // Baue die Seiten für die Navigation
            for ($i = 1; $i <= $nSeiten; $i++) {
                $nBlaetterAnzahl_arr[] = $i;
            }
        }
        // Blaetter Objekt um später in Smarty damit zu arbeiten
        $oBlaetterNavi->nSeiten             = $nSeiten;
        $oBlaetterNavi->nVoherige           = $nVoherige;
        $oBlaetterNavi->nNaechste           = $nNaechste;
        $oBlaetterNavi->nAnfang             = $nAnfang;
        $oBlaetterNavi->nEnde               = $nEnde;
        $oBlaetterNavi->nBlaetterAnzahl_arr = $nBlaetterAnzahl_arr;
        $oBlaetterNavi->nAktiv              = 1;
    }

    $oBlaetterNavi->nSterne        = $bewertung_sterne;
    $oBlaetterNavi->nAktuelleSeite = $bewertung_seite;
    $oBlaetterNavi->nVon           = (($oBlaetterNavi->nAktuelleSeite - 1) * $nAnzahlSeiten) + 1;
    $oBlaetterNavi->nBis           = $oBlaetterNavi->nAktuelleSeite * $nAnzahlSeiten;

    if ($oBlaetterNavi->nBis > $nAnzahlBewertungen) {
        --$oBlaetterNavi->nBis;
    }

    return $oBlaetterNavi;
}

/**
 * Mappt den Fehlercode für Bewertungen
 *
 * @param string $cCode
 * @param float  $fGuthaben
 * @return string
 */
function mappingFehlerCode($cCode, $fGuthaben = 0.0)
{
    switch ($cCode) {
        // Fehler
        case 'f01':
            $error = Shop::Lang()->get('bewertungWrongdata', 'errorMessages');
            break;
        case 'f02':
            $error = Shop::Lang()->get('bewertungBewexist', 'errorMessages');
            break;
        case 'f03':
            $error = Shop::Lang()->get('bewertungBewnotbought', 'errorMessages');
            break;
        // Hinweise
        case 'h01':
            $error = Shop::Lang()->get('bewertungBewadd', 'messages');
            break;
        case 'h02':
            $error = Shop::Lang()->get('bewertungHilfadd', 'messages');
            break;
        case 'h03':
            $error = Shop::Lang()->get('bewertungHilfchange', 'messages');
            break;
        case 'h04':
            $error = sprintf(Shop::Lang()->get('bewertungBewaddCredits', 'messages'), (string)$fGuthaben);
            break;
        case 'h05':
            $error = Shop::Lang()->get('bewertungBewaddacitvate', 'messages');
            break;
        default:
            $error = '';
    }
    executeHook(HOOK_ARTIKEL_INC_BEWERTUNGHINWEISSWITCH, ['error' => $error]);

    return $error;
}

/**
 * @param Artikel $oVaterArtikel
 * @param Artikel $oKindArtikel
 * @return mixed
 */
function fasseVariVaterUndKindZusammen($oVaterArtikel, $oKindArtikel)
{
    $oArtikel                                   = $oKindArtikel;
    $kVariKindArtikel                           = (int)$oKindArtikel->kArtikel;
    $oArtikel->kArtikel                         = (int)$oVaterArtikel->kArtikel;
    $oArtikel->kVariKindArtikel                 = (int)$kVariKindArtikel;
    $oArtikel->nIstVater                        = 1;
    $oArtikel->kVaterArtikel                    = (int)$oVaterArtikel->kArtikel;
    $oArtikel->kEigenschaftKombi                = $oVaterArtikel->kEigenschaftKombi;
    $oArtikel->kEigenschaftKombi_arr            = $oVaterArtikel->kEigenschaftKombi_arr;
    $oArtikel->fDurchschnittsBewertung          = $oVaterArtikel->fDurchschnittsBewertung;
    $oArtikel->Bewertungen                      = isset($oVaterArtikel->Bewertungen)
        ? $oVaterArtikel->Bewertungen
        : null;
    $oArtikel->HilfreichsteBewertung            = isset($oVaterArtikel->HilfreichsteBewertung)
        ? $oVaterArtikel->HilfreichsteBewertung
        : null;
    $oArtikel->oVariationKombiVorschau_arr      = isset($oVaterArtikel->oVariationKombiVorschau_arr)
        ? $oVaterArtikel->oVariationKombiVorschau_arr
        : [];
    $oArtikel->oVariationDetailPreis_arr        = $oVaterArtikel->oVariationDetailPreis_arr;
    $oArtikel->nVariationKombiNichtMoeglich_arr = $oVaterArtikel->nVariationKombiNichtMoeglich_arr;
    $oArtikel->oVariationKombiVorschauText      = isset($oVaterArtikel->oVariationKombiVorschauText)
        ? $oVaterArtikel->oVariationKombiVorschauText
        : null;
    $oArtikel->cVaterURL                        = $oVaterArtikel->cURL;
    $oArtikel->VaterFunktionsAttribute          = $oVaterArtikel->FunktionsAttribute;

    executeHook(HOOK_ARTIKEL_INC_FASSEVARIVATERUNDKINDZUSAMMEN, ['article' => $oArtikel]);

    return $oArtikel;
}

/**
 * @param int $kArtikel
 * @return array
 */
function holeAehnlicheArtikel($kArtikel)
{
    // Aktueller Artikel
    $kArtikel     = (int)$kArtikel;
    $oArtikel_arr = [];
    $cLimit       = ' LIMIT 3';
    $conf         = Shop::getSettings([CONF_ARTIKELDETAILS]);
    // Gibt es X-Seller? Aus der Artikelmenge der änhlichen Artikel, dann alle X-Seller rausfiltern
    $oXSeller               = gibArtikelXSelling($kArtikel);
    $kArtikelXSellerKey_arr = [];
    if (isset($oXSeller->Standard->XSellGruppen) &&
        is_array($oXSeller->Standard->XSellGruppen) &&
        count($oXSeller->Standard->XSellGruppen) > 0
    ) {
        foreach ($oXSeller->Standard->XSellGruppen as $oXSeller) {
            if (is_array($oXSeller->Artikel) && count($oXSeller->Artikel) > 0) {
                foreach ($oXSeller->Artikel as $oArtikel) {
                    $oArtikel->kArtikel = (int)$oArtikel->kArtikel;
                    if (!in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr, true)) {
                        $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                    }
                }
            }
        }
    }
    if (isset($oXSeller->Kauf->XSellGruppen) &&
        is_array($oXSeller->Kauf->XSellGruppen) &&
        count($oXSeller->Kauf->XSellGruppen) > 0
    ) {
        foreach ($oXSeller->Kauf->XSellGruppen as $oXSeller) {
            if (is_array($oXSeller->Artikel) && count($oXSeller->Artikel) > 0) {
                foreach ($oXSeller->Artikel as $oArtikel) {
                    $oArtikel->kArtikel = (int)$oArtikel->kArtikel;
                    if (!in_array($oArtikel->kArtikel, $kArtikelXSellerKey_arr, true)) {
                        $kArtikelXSellerKey_arr[] = $oArtikel->kArtikel;
                    }
                }
            }
        }
    }

    $cSQLXSeller = '';
    if (count($kArtikelXSellerKey_arr) > 0) {
        $cSQLXSeller = " AND tartikel.kArtikel NOT IN (" . implode(',', $kArtikelXSellerKey_arr) . ") ";
    }

    if ($kArtikel > 0) {
        if ((int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'] > 0) {
            $cLimit = " LIMIT " . (int)$conf['artikeldetails']['artikeldetails_aehnlicheartikel_anzahl'];
        }
        $lagerFilter         = gibLagerfilter();
        $kundenGruppe        = (int)$_SESSION['Kundengruppe']->kKundengruppe;
        $oArtikelMerkmal_arr = Shop::DB()->query(
            "SELECT tartikelmerkmal.kArtikel, tartikel.kVaterArtikel
                 FROM tartikelmerkmal
                 JOIN tartikel ON tartikel.kArtikel = tartikelmerkmal.kArtikel
                    AND tartikel.kVaterArtikel != {$kArtikel}
                    AND (tartikel.nIstVater = 1 OR tartikel.kEigenschaftKombi = 0)
                 JOIN tartikelmerkmal similarMerkmal ON similarMerkmal.kArtikel = {$kArtikel}
                    AND similarMerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                    AND similarMerkmal.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                 LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = {$kundenGruppe}
                 WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikelmerkmal.kArtikel != {$kArtikel}
                    {$lagerFilter}
                    {$cSQLXSeller}
                 GROUP BY tartikelmerkmal.kArtikel
                 ORDER BY COUNT(tartikelmerkmal.kMerkmal) DESC
                " . $cLimit, 2
        );
        if (is_array($oArtikelMerkmal_arr) && count($oArtikelMerkmal_arr) > 0) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($oArtikelMerkmal_arr as $oArtikelMerkmal) {
                $oArtikel = new Artikel();
                $id = ($oArtikelMerkmal->kVaterArtikel > 0)
                    ? $oArtikelMerkmal->kVaterArtikel
                    : $oArtikelMerkmal->kArtikel;
                $oArtikel->fuelleArtikel($id, $defaultOptions);
                if ($oArtikel->kArtikel > 0) {
                    $oArtikel_arr[] = $oArtikel;
                }
            }
        } else { // Falls es keine Merkmale gibt, in tsuchcachetreffer und ttagartikel suchen
            $oArtikelSuchcacheTreffer_arr = Shop::DB()->query(
                "SELECT tsuchcachetreffer.kArtikel, tartikel.kVaterArtikel
                    FROM
                    (
                        SELECT kSuchCache
                        FROM tsuchcachetreffer
                        WHERE kArtikel = " . $kArtikel . "
                        AND nSort <= 10
                    ) AS ssSuchCache
                    JOIN tsuchcachetreffer 
                        ON tsuchcachetreffer.kSuchCache = ssSuchCache.kSuchCache
                        AND tsuchcachetreffer.kArtikel != " . $kArtikel . "
                    LEFT JOIN tartikelsichtbarkeit 
                        ON tsuchcachetreffer.kArtikel = tartikelsichtbarkeit.kArtikel
                        AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                    JOIN tartikel 
                        ON tartikel.kArtikel = tsuchcachetreffer.kArtikel
                        AND tartikel.kVaterArtikel != " . $kArtikel . "
                    WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        {$lagerFilter}
                        {$cSQLXSeller}
                    GROUP BY tsuchcachetreffer.kArtikel
                    ORDER BY COUNT(*) DESC
                    " . $cLimit, 2
            );

            if (is_array($oArtikelSuchcacheTreffer_arr) && count($oArtikelSuchcacheTreffer_arr) > 0) {
                $defaultOptions = Artikel::getDefaultOptions();
                foreach ($oArtikelSuchcacheTreffer_arr as $oArtikelSuchcacheTreffer) {
                    $oArtikel = new Artikel();
                    $id       = ($oArtikelSuchcacheTreffer->kVaterArtikel > 0)
                        ? $oArtikelSuchcacheTreffer->kVaterArtikel
                        : $oArtikelSuchcacheTreffer->kArtikel;
                    $oArtikel->fuelleArtikel($id, $defaultOptions);
                    if ($oArtikel->kArtikel > 0) {
                        $oArtikel_arr[] = $oArtikel;
                    }
                }
            } else {
                $oArtikelTags_arr = Shop::DB()->query(
                    "SELECT ttagartikel.kArtikel, tartikel.kVaterArtikel
                        FROM
                        (
                            SELECT kTag
                                FROM ttagartikel
                                WHERE kArtikel = " . $kArtikel . "
                        ) AS ssTag
                        JOIN ttagartikel 
                            ON ttagartikel.kTag = ssTag.kTag
                            AND ttagartikel.kArtikel != " . $kArtikel . "
                        LEFT JOIN tartikelsichtbarkeit 
                            ON ttagartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        JOIN tartikel 
                            ON tartikel.kArtikel = ttagartikel.kArtikel
                            AND tartikel.kVaterArtikel != " . $kArtikel . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            {$lagerFilter}
                            {$cSQLXSeller}
                        GROUP BY ttagartikel.kArtikel
                        ORDER BY COUNT(*) DESC
                        " . $cLimit, 2
                );
                if (is_array($oArtikelTags_arr) && count($oArtikelTags_arr) > 0) {
                    $defaultOptions = Artikel::getDefaultOptions();
                    foreach ($oArtikelTags_arr as $oArtikelTags) {
                        $oArtikel = new Artikel();
                        $id       = ($oArtikelTags->kVaterArtikel > 0)
                            ? $oArtikelTags->kVaterArtikel
                            : $oArtikelTags->kArtikel;
                        $oArtikel->fuelleArtikel($id, $defaultOptions);
                        if ($oArtikel->kArtikel > 0) {
                            $oArtikel_arr[] = $oArtikel;
                        }
                    }
                }
            }
        }
    }
    executeHook(HOOK_ARTIKEL_INC_AEHNLICHEARTIKEL, ['oArtikel_arr' => &$oArtikel_arr]);

    if (is_array($oArtikel_arr) &&
        is_array($kArtikelXSellerKey_arr) &&
        count($oArtikel_arr) > 0 &&
        count($kArtikelXSellerKey_arr) > 0
    ) {
        foreach ($oArtikel_arr as $i => $oArtikel) {
            foreach ($kArtikelXSellerKey_arr as $kArtikelXSellerKey) {
                if ($oArtikel->kArtikel === $kArtikelXSellerKey) {
                    unset($oArtikel_arr[$i]);
                }
            }
        }
    }

    return $oArtikel_arr;
}

/**
 * @param int $Productkey
 * @return bool
 */
function ProductBundleWK($Productkey)
{
    $Productkey = (int)$Productkey;
    if ($Productkey > 0) {
        $oOption                             = new stdClass();
        $oOption->nMerkmale                  = 1;
        $oOption->nAttribute                 = 1;
        $oOption->nArtikelAttribute          = 1;
        $oOption->nKeineSichtbarkeitBeachten = 1;

        return fuegeEinInWarenkorb($Productkey, 1, [], 0, false, 0, $oOption);
    }

    return false;
}

/**
 * @param int       $kArtikel
 * @param float|int $fAnzahl
 * @param array     $nVariation_arr
 * @param array     $nKonfiggruppe_arr
 * @param array     $nKonfiggruppeAnzahl_arr
 * @param array     $nKonfigitemAnzahl_arr
 * @return stdClass|null
 */
function buildConfig($kArtikel, $fAnzahl, $nVariation_arr, $nKonfiggruppe_arr, $nKonfiggruppeAnzahl_arr, $nKonfigitemAnzahl_arr)
{
    $oKonfig                  = new stdClass;
    $oKonfig->fAnzahl         = $fAnzahl;
    $oKonfig->fGesamtpreis    = [0.0, 0.0];
    $oKonfig->cPreisLocalized = [];
    $oKonfig->cPreisString    = Shop::Lang()->get('priceAsConfigured', 'productDetails');

    if (!class_exists('Konfigurator') || !Konfigurator::validateKonfig($kArtikel)) {
        return null;
    }
    foreach ($nVariation_arr as $i => $nVariation) {
        $_POST['eigenschaftwert_' . $i] = $nVariation;
    }
    if (ArtikelHelper::isParent($kArtikel)) {
        $kArtikel              = ArtikelHelper::getArticleForParent($kArtikel);
        $oEigenschaftwerte_arr = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($kArtikel);
    } else {
        $oEigenschaftwerte_arr = ArtikelHelper::getSelectedPropertiesForArticle($kArtikel, false);
    }

    $oArtikel                                = new Artikel();
    $oArtikelOptionen                        = new stdClass();
    $oArtikelOptionen->nKonfig               = 1;
    $oArtikelOptionen->nAttribute            = 1;
    $oArtikelOptionen->nArtikelAttribute     = 1;
    $oArtikelOptionen->nVariationKombi       = 1;
    $oArtikelOptionen->nVariationKombiKinder = 1;
    $oArtikel->fuelleArtikel($kArtikel, $oArtikelOptionen);

    $oKonfig->nMinDeliveryDays      = $oArtikel->nMinDeliveryDays;
    $oKonfig->nMaxDeliveryDays      = $oArtikel->nMaxDeliveryDays;
    $oKonfig->cEstimatedDelivery    = $oArtikel->cEstimatedDelivery;
    $oKonfig->Lageranzeige          = new stdClass();
    $oKonfig->Lageranzeige->nStatus = $oArtikel->Lageranzeige->nStatus;

    $fAnzahl = max($fAnzahl, 1);
    if ($oArtikel->cTeilbar !== 'Y' && (int)$fAnzahl != $fAnzahl) {
        $fAnzahl = (int)$fAnzahl;
    }

    $oKonfig->fGesamtpreis = [
        berechneBrutto(
            $oArtikel->gibPreis($fAnzahl, $oEigenschaftwerte_arr),
            gibUst($oArtikel->kSteuerklasse)
        ) * $fAnzahl,
        $oArtikel->gibPreis($fAnzahl, $oEigenschaftwerte_arr) * $fAnzahl
    ];
    $oKonfig->oKonfig_arr = $oArtikel->oKonfig_arr;

    foreach ($nKonfiggruppe_arr as $i => $nKonfiggruppe) {
        $nKonfiggruppe_arr[$i] = (array)$nKonfiggruppe;
    }
    /** @var Konfiggruppe $oKonfiggruppe */
    foreach ($oKonfig->oKonfig_arr as $i => &$oKonfiggruppe) {
        $oKonfiggruppe->bAktiv = false;
        $kKonfiggruppe         = $oKonfiggruppe->getKonfiggruppe();
        $nKonfigitem_arr       = isset($nKonfiggruppe_arr[$kKonfiggruppe]) ? $nKonfiggruppe_arr[$kKonfiggruppe] : [];
        foreach ($oKonfiggruppe->oItem_arr as $j => &$oKonfigitem) {
            /** @var Konfigitem $oKonfigitem */
            $kKonfigitem          = $oKonfigitem->getKonfigitem();
            $oKonfigitem->fAnzahl = (float)(
                isset($nKonfiggruppeAnzahl_arr[$oKonfigitem->getKonfiggruppe()])
                    ? $nKonfiggruppeAnzahl_arr[$oKonfigitem->getKonfiggruppe()]
                    : $oKonfigitem->getInitial()
            );
            if ($oKonfigitem->fAnzahl > $oKonfigitem->getMax() || $oKonfigitem->fAnzahl < $oKonfigitem->getMin()) {
                $oKonfigitem->fAnzahl = $oKonfigitem->getInitial();
            }
            if ($nKonfigitemAnzahl_arr && isset($nKonfigitemAnzahl_arr[$oKonfigitem->getKonfigitem()])) {
                $oKonfigitem->fAnzahl = (float)$nKonfigitemAnzahl_arr[$oKonfigitem->getKonfigitem()];
            }
            if ($oKonfigitem->fAnzahl <= 0) {
                $oKonfigitem->fAnzahl = 1;
            }
            $oKonfigitem->fAnzahlWK = $oKonfigitem->fAnzahl;
            if (!$oKonfigitem->ignoreMultiplier()) {
                $oKonfigitem->fAnzahlWK *= $fAnzahl;
            }
            $oKonfigitem->bAktiv = in_array($kKonfigitem, $nKonfigitem_arr);

            if ($oKonfigitem->bAktiv) {
                $oKonfig->fGesamtpreis[0] += $oKonfigitem->getPreis() * $oKonfigitem->fAnzahlWK;
                $oKonfig->fGesamtpreis[1] += $oKonfigitem->getPreis(true) * $oKonfigitem->fAnzahlWK;
                $oKonfiggruppe->bAktiv = true;
                //Konfigitem mit Lagerinfos
                if ($oKonfigitem->getArtikel()->cLagerBeachten === 'Y'
                    && $oKonfig->nMinDeliveryDays < $oKonfigitem->getArtikel()->nMinDeliveryDays
                ) {
                    $oKonfig->nMinDeliveryDays      = $oKonfigitem->getArtikel()->nMinDeliveryDays;
                    $oKonfig->nMaxDeliveryDays      = $oKonfigitem->getArtikel()->nMaxDeliveryDays;
                    $oKonfig->cEstimatedDelivery    = $oKonfigitem->getArtikel()->cEstimatedDelivery;
                    $oKonfig->Lageranzeige->nStatus = $oKonfigitem->getArtikel()->Lageranzeige->nStatus;
                }
            }
        }
        unset($oKonfigitem);
        $oKonfiggruppe->oItem_arr = array_values($oKonfiggruppe->oItem_arr);
    }
    unset($oKonfiggruppe);
    if ($_SESSION['Kundengruppe']->darfPreiseSehen) {
        $oKonfig->cPreisLocalized = [
            gibPreisStringLocalized($oKonfig->fGesamtpreis[0]),
            gibPreisStringLocalized($oKonfig->fGesamtpreis[1])
        ];
    } else {
        $oKonfig->cPreisLocalized = [Shop::Lang()->get('priceHidden', 'global')];
    }
    $oKonfig->nNettoPreise = $_SESSION['Kundengruppe']->nNettoPreise;

    return $oKonfig;
}

/**
 * @deprecated since 4.0
 * @param Artikel $Artikel
 * @return string
 */
function gibMetaTitle($Artikel)
{
    return $Artikel->getMetaTitle();
}

/**
 * @deprecated since 4.0
 * @param Artikel $Artikel
 * @param object  $KategorieListe
 * @return string
 */
function gibMetaDescription($Artikel, $KategorieListe)
{
    return $Artikel->getMetaDescription($KategorieListe);
}

/**
 * @deprecated since 4.0
 * @param Artikel $Artikel
 * @return mixed
 */
function gibMetaKeywords($Artikel)
{
    return $Artikel->getMetaKeywords();
}

/**
 * @deprecated since 4.0
 * @param Artikel $AktuellerArtikel
 * @return mixed
 */
function holeProduktTagging($AktuellerArtikel)
{
    return $AktuellerArtikel->tags;
}

/**
 * @deprecated since version 4.3
 */
function bearbeiteArtikelWeiterempfehlen()
{
}

/**
 * @deprecated since version 4.3
 * @return array
 */
function gibFehlendeEingabenArtikelWeiterempfehlenFormular()
{
    return [];
}

if (!function_exists('baueFormularVorgaben')) {
    /**
     * @return stdClass
     */
    function baueFormularVorgaben()
    {
        return baueProduktanfrageFormularVorgaben();
    }
}
