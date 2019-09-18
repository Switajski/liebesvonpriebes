<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

define('PARTNER_PACKAGE', 'JTL');
define('SHOP_SOFTWARE', 'JTL');

$oAccount->permission('ORDER_TRUSTEDSHOPS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'uebersicht';

setzeSpracheTrustedShops();

$Einstellungen = Shop::getSettings([CONF_TRUSTEDSHOPS]);

if (isset($_POST['kaeuferschutzeinstellungen']) && (int)$_POST['kaeuferschutzeinstellungen'] === 1 && validateToken()) {
    // Lpesche das Zertifikat
    if (isset($_POST['delZertifikat'])) {
        $oTrustedShops = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);

        if ($oTrustedShops->oZertifikat->kTrustedShopsZertifikat > 0) {
            if ($oTrustedShops->loescheTrustedShopsZertifikat($oTrustedShops->oZertifikat->kTrustedShopsZertifikat)) {
                $cHinweis = 'Ihr Zertifikat wurde erfolgreich f&uuml;r die aktuelle Sprache gel&ouml;scht.';

                Shop::DB()->query(
                    "DELETE FROM teinstellungen
                        WHERE kEinstellungenSektion = " . CONF_TRUSTEDSHOPS . "
                            AND cName = 'trustedshops_nutzen'", 4
                );
                $aktWert                        = new stdClass();
                $aktWert->cWert                 = 'N';
                $aktWert->cName                 = 'trustedshops_nutzen';
                $aktWert->kEinstellungenSektion = CONF_TRUSTEDSHOPS;
                Shop::DB()->insert('teinstellungen', $aktWert);
                Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
            } else {
                $cFehler .= 'Fehler: Es wurde kein Zertifikat f&uuml; die aktuelle Sprache gefunden.';
            }
        } else {
            $cFehler .= 'Fehler: Es wurde kein Zertifikat f&uuml; die aktuelle Sprache gefunden.';
        }
    } else { // Speicher die Einstellungen
        $cPreStatus  = $Einstellungen['trustedshops']['trustedshops_nutzen'];
        $oConfig_arr = Shop::DB()->query(
            "SELECT *
                FROM teinstellungenconf
                WHERE kEinstellungenSektion = " . CONF_TRUSTEDSHOPS . "
                    AND cConf = 'Y'
                    AND cWertName != 'trustedshops_kundenbewertung_anzeigen'
                ORDER BY nSort", 2
        );
        $configCount = count($oConfig_arr);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = $_POST[$oConfig_arr[$i]->cWertName];
            $aktWert->cName                 = $oConfig_arr[$i]->cWertName;
            $aktWert->kEinstellungenSektion = CONF_TRUSTEDSHOPS;
            switch ($oConfig_arr[$i]->cInputTyp) {
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
                    bearbeiteListBox($aktWert->cWert, $oConfig_arr[$i]->cWertName, CONF_TRUSTEDSHOPS);
                    break;
            }

            if ($oConfig_arr[$i]->cInputTyp !== 'listbox') {
                Shop::DB()->delete('teinstellungen', ['kEinstellungenSektion', 'cName'], [CONF_TRUSTEDSHOPS, $oConfig_arr[$i]->cWertName]);
                Shop::DB()->insert('teinstellungen', $aktWert);
            }
            $settings = Shopsetting::getInstance();
            $settings->reset();
        }

        if (strlen($_POST['tsId']) > 0 && (strlen($_POST['wsUser']) > 0 &&
                strlen($_POST['wsPassword']) > 0 || $_POST['eType'] === TS_BUYERPROT_CLASSIC)) {
            $oZertifikat              = new stdClass();
            $oZertifikat->cTSID       = StringHandler::htmlentities(StringHandler::filterXSS(trim($_POST['tsId'])));
            $oZertifikat->cWSUser     = StringHandler::htmlentities(StringHandler::filterXSS($_POST['wsUser']));
            $oZertifikat->cWSPasswort = StringHandler::htmlentities(StringHandler::filterXSS($_POST['wsPassword']));
            $oZertifikat->cISOSprache = $_SESSION['TrustedShops']->oSprache->cISOSprache;
            $oZertifikat->nAktiv      = 0;
            $oZertifikat->eType       = StringHandler::htmlentities(StringHandler::filterXSS($_POST['eType']));
            $oZertifikat->dErstellt   = 'now()';

            //$oTrustedShops = new TrustedShops($oZertifikat->cTSID, $_SESSION['TrustedShops']->oSprache->cISOSprache);
            $oTrustedShops = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);

            $nReturnValue = (strlen($oTrustedShops->kTrustedShopsZertifikat) > 0) ?
                $oTrustedShops->speicherTrustedShopsZertifikat($oZertifikat, $oTrustedShops->kTrustedShopsZertifikat) :
                $oTrustedShops->speicherTrustedShopsZertifikat($oZertifikat);

            mappeTSFehlerCode($cHinweis, $cFehler, $nReturnValue);
        } elseif ($cPreStatus === 'Y') {
            $cFehler .= 'Fehler: Bitte f&uuml;llen Sie alle Felder aus.';
        }

        $cHinweis .= 'Ihre Einstellungen wurden &uuml;bernommen.';
        Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
        unset($oConfig_arr);
    }
} elseif (isset($_POST['kaeuferschutzupdate']) && (int)$_POST['kaeuferschutzupdate'] === 1 && validateToken()) {
    // Kaeuferprodukte updaten
    $oTrustedShops = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
    //$oZertifikat = $oTrustedShops->gibTrustedShopsZertifikatISO($_SESSION['TrustedShops']->oSprache->cISOSprache);

    if ($oTrustedShops->oZertifikat->kTrustedShopsZertifikat > 0 && $oTrustedShops->oZertifikat->nAktiv == 1) {
        $oTrustedShops->holeKaeuferschutzProdukte($oTrustedShops->oZertifikat->kTrustedShopsZertifikat);
        $cHinweis .= 'Ihre K&auml;uferschutzprodukte wurden aktualisiert.';
    } else {
        $cFehler .= 'Fehler: Ihre K&auml;uferschutzprodukte konnten nicht aktualisiert werden.';
    }
} elseif (isset($_POST['kundenbewertungeinstellungen']) && (int)$_POST['kundenbewertungeinstellungen'] === 1 && validateToken()) {
    // Kundenbewertung Einstellungen
    $oTrustedShops = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
    $cPreStatus    = $Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'];

    $oConfig_arr = Shop::DB()->selectAll(
        'teinstellungenconf',
        ['kEinstellungenSektion', 'cConf', 'cWertName'],
        [CONF_TRUSTEDSHOPS, 'Y', 'trustedshops_kundenbewertung_anzeigen'],
        '*',
        'nSort'
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        $aktWert                        = new stdClass();
        $aktWert->cWert                 = $_POST[$oConfig_arr[$i]->cWertName];
        $aktWert->cName                 = $oConfig_arr[$i]->cWertName;
        $aktWert->kEinstellungenSektion = CONF_TRUSTEDSHOPS;
        switch ($oConfig_arr[$i]->cInputTyp) {
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
                bearbeiteListBox($aktWert->cWert, $oConfig_arr[$i]->cWertName, CONF_TRUSTEDSHOPS);
                break;
        }

        if ($oConfig_arr[$i]->cInputTyp !== 'listbox') {
            Shop::DB()->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_TRUSTEDSHOPS, $oConfig_arr[$i]->cWertName]
            );
            Shop::DB()->insert('teinstellungen', $aktWert);
        }
    }
    $settings = Shopsetting::getInstance();
    $settings->reset();
    $Einstellungen = Shop::getSettings([CONF_TRUSTEDSHOPS]);
    if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'N') {
        $oTrustedShops->aenderKundenbewertungsstatusDB(0, $_SESSION['TrustedShops']->oSprache->cISOSprache);
        $oTrustedShops->aenderKundenbewertungsstatus(0, $_SESSION['TrustedShops']->oSprache->cISOSprache);
    }

    if (strlen($_POST['kb-tsId']) > 0) {
        $oTrustedShops->aenderKundenbewertungtsIDDB(trim($_POST['kb-tsId']), $_SESSION['TrustedShops']->oSprache->cISOSprache);
        $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert.';
    } else {
        $cFehler .= 'Fehler: Bitte geben Sie eine tsID ein!<br>';
    }
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);
} elseif (isset($_POST['kundenbewertungupdate']) && (int)$_POST['kundenbewertungupdate'] === 1) { // Kundenbewertung update
    if (isset($_POST['tsKundenbewertungActive']) || isset($_POST['tsKundenbewertungDeActive'])) {
        $nStatus = 0;
        if (isset($_POST['tsKundenbewertungActive'])) {
            $nStatus = 1;
        }
        $oTrustedShops                = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);
        $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus($_SESSION['TrustedShops']->oSprache->cISOSprache);

        if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0) {
            $nReturnValue = $oTrustedShops->aenderKundenbewertungsstatus($oTrustedShopsKundenbewertung->cTSID, $nStatus, $_SESSION['TrustedShops']->oSprache->cISOSprache);
            if ($nReturnValue === 1) {
                $filename = $oTrustedShopsKundenbewertung->cTSID . '.gif';
                $oTrustedShops::ladeKundenbewertungsWidgetNeu($filename);
                $cHinweis = 'Ihr Status wurde erfolgreich ge&auml;ndert';
            } elseif ($nReturnValue === 2) {
                $cFehler = 'Fehler: Bei der Status&auml;nderung ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.';
            } elseif ($nReturnValue === 3) {
                // Wurde die TS-ID vielleicht schon in einer anderen Sprache benutzt?
                if ($oTrustedShops->pruefeKundenbewertungsstatusAndereSprache($oTrustedShopsKundenbewertung->cTSID, $_SESSION['TrustedShops']->oSprache->cISOSprache)) {
                    $cFehler = 'Fehler: Ihre Trusted Shops ID (tsId) wurde bereits f&uuml;r eine andere Sprache verwendet.';
                } else {
                    $cFehler = 'Fehler: Ihre Trusted Shops ID (tsId) ist falsch.';
                }
            } elseif ($nReturnValue === 4) {
                $cFehler = 'Fehler: Sie sind nicht registriert um die Kundenbewertung zu nutzen. Bitte nutzen Sie den Link zum Formular, oben auf dieser Seite.';
            } elseif ($nReturnValue === 5) {
                $cFehler = 'Fehler: Ihr Username und Passwort sind falsch.';
            } elseif ($nReturnValue === 6) {
                $cFehler = 'Fehler: Sie m&uuml;ssen Ihre Trusted Shops Kundenbewertung erst aktivieren.';
            }
        } else {
            $cFehler = 'Fehler: Kundenbewertung nicht gefunden.';
        }
    }
} elseif (isset($_GET['whatis']) && (int)$_GET['whatis'] === 1) { // Infoseite anzeigen
    $step = 'info';
} elseif (isset($_GET['whatisrating']) && (int)$_GET['whatisrating'] === 1) { // Infoseite Kundenbewertung anzeigen
    $step = 'info_kundenbewertung';
}

// Uebersicht
if ($step === 'uebersicht') {
    // Config holen
    $oConfig_arr = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenSektion = " . CONF_TRUSTEDSHOPS . "
            ORDER BY nSort", 2
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
                "SELECT *
                    FROM teinstellungenconfwerte
                    WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
                    ORDER BY nSort", 2
            );
        } elseif ($oConfig_arr[$i]->cInputTyp === 'listbox') {
            $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
                "SELECT kKundengruppe, cName
                    FROM tkundengruppe
                    ORDER BY cStandard DESC", 2
            );
        }

        if ($oConfig_arr[$i]->cInputTyp === 'listbox') {
            $oSetValue = Shop::DB()->query(
                "SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = " . CONF_TRUSTEDSHOPS . "
                        AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 2
            );
            $oConfig_arr[$i]->gesetzterWert = $oSetValue;
        } else {
            $oSetValue = Shop::DB()->query(
                "SELECT cWert
                    FROM teinstellungen
                    WHERE kEinstellungenSektion = " . CONF_TRUSTEDSHOPS . "
                        AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
            );
            $oConfig_arr[$i]->gesetzterWert = isset($oSetValue->cWert) ? $oSetValue->cWert : null;
        }
    }

    // Config
    $smarty->assign('oConfig_arr', $oConfig_arr);

    $oTrustedShops = new TrustedShops(-1, $_SESSION['TrustedShops']->oSprache->cISOSprache);

    if (isset($_POST['kaeuferschutzupdate'], $_POST['tsupdate']) && (int)$_POST['kaeuferschutzupdate'] === 1 &&
        $Einstellungen['trustedshops']['trustedshops_nutzen'] === 'Y'
    ) {
        $smarty->assign('oKaeuferschutzProdukteDB', $oTrustedShops->oKaeuferschutzProdukteDB);
        $smarty->assign('oZertifikat', $oTrustedShops->gibTrustedShopsZertifikatISO($_SESSION['TrustedShops']->oSprache->cISOSprache));

        // Kundenbwertungsstatus
        $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus($_SESSION['TrustedShops']->oSprache->cISOSprache);
        if ($oTrustedShopsKundenbewertung) {
            $smarty->assign('oTrustedShopsKundenbewertung', $oTrustedShopsKundenbewertung);
        }

        // Kundenbewertungs URL zur Uebersicht
        $cURLKundenBewertung_arr = [
            'de' => 'https://www.trustedshops.de/shopbetreiber/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
            'en' => 'https://www.trustedshops.co.uk/merchants/partners/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
            'fr' => 'https://www.trustedshops.fr/marchands/partenaires/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
            'es' => 'https://www.trustedshops.es/comerciante/partner/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
            'nl' => '',
            'it' => '',
            'pl' => 'https://www.trustedshops.pl/handlowcy/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE
        ];
    }

    if ($Einstellungen['trustedshops']['trustedshops_nutzen'] === 'Y') {
        $smarty->assign('oKaeuferschutzProdukteDB', $oTrustedShops->oKaeuferschutzProdukteDB);
    }

    $smarty->assign('Einstellungen', $Einstellungen);
    $smarty->assign('oZertifikat', $oTrustedShops->oZertifikat);

    // Kundenbewertungsstatus
    $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus($_SESSION['TrustedShops']->oSprache->cISOSprache);
    if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
        $smarty->assign('oTrustedShopsKundenbewertung', $oTrustedShopsKundenbewertung);
    }

    // Kundenbewertungs URL zur Uebersicht
    $cURLKundenBewertung_arr = [
        'de' => 'https://www.trustedshops.de/shopbetreiber/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
        'en' => 'https://www.trustedshops.co.uk/merchants/partners/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
        'fr' => 'https://www.trustedshops.fr/marchands/partenaires/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
        'es' => 'https://www.trustedshops.es/comerciante/partner/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE,
        'nl' => '',
        'it' => '',
        'pl' => 'https://www.trustedshops.pl/handlowcy/?shopsw=' . SHOP_SOFTWARE . '&partnerPackage=' . PARTNER_PACKAGE
    ];

    $cURLKundenBewertungUebersicht_arr = [];
    if (isset($oTrustedShopsKundenbewertung->cTSID) && strlen($oTrustedShopsKundenbewertung->cTSID) > 0) {
        $cURLKundenBewertungUebersicht_arr = [
            'de' => 'https://www.trustedshops.com/bewertung/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
            'en' => 'https://www.trustedshops.com/buyerrating/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
            'fr' => 'https://www.trustedshops.com/evaluation/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
            'es' => 'https://www.trustedshops.com/evaluacion/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
            'pl' => 'https://www.trustedshops.pl/opinia/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
            'nl' => 'https://www.trustedshops.nl/verkopersbeoordeling/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html',
            'it' => 'https://www.trustedshops.it/valutazione-del-negozio/info_' . $oTrustedShopsKundenbewertung->cTSID . '.html'
        ];
    }

    $oSprach_arr = [
        'de' => 'Deutsch',
        'en' => 'Englisch',
        'fr' => 'Franz&ouml;sisch',
        'nl' => 'Niederl&auml;ndisch',
        'it' => 'Italienisch',
        'pl' => 'Polnisch',
        'es' => 'Spanisch'
    ];
    $cMember_arr   = array_keys($oSprach_arr);
    $oSprachen_arr = [];
    foreach ($oSprach_arr as $i => $oSprach) {
        $oSprachen_arr[$i]                      = new stdClass();
        $oSprachen_arr[$i]->cISOSprache         = $i;
        $oSprachen_arr[$i]->cNameSprache        = $oSprach_arr[$i];
        $oSprachen_arr[$i]->cURLKundenBewertung = $cURLKundenBewertung_arr[$i];
        if (count($cURLKundenBewertungUebersicht_arr) > 0) {
            $oSprachen_arr[$i]->cURLKundenBewertungUebersicht = $cURLKundenBewertungUebersicht_arr[$i];
        }
    }
    $smarty->assign('Sprachen', $oSprachen_arr);
} elseif ($step === 'info') {
    $smarty->assign('PFAD_GFX_TRUSTEDSHOPS', PFAD_GFX_TRUSTEDSHOPS);
} elseif ($step === 'info_kundenbewertung') {
    $smarty->assign('PFAD_GFX_TRUSTEDSHOPS', PFAD_GFX_TRUSTEDSHOPS);
}
$smarty->assign('TS_BUYERPROT_CLASSIC', TS_BUYERPROT_CLASSIC)
       ->assign('TS_BUYERPROT_EXCELLENCE', TS_BUYERPROT_EXCELLENCE)
       ->assign('bAllowfopen', pruefeALLOWFOPEN())
       ->assign('bSOAP', pruefeSOAP())
       ->assign('bCURL', pruefeCURL())
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('trustedshops.tpl');

/**
 * @param string $cHinweis
 * @param string $cFehler
 * @param int    $nReturnValue
 */
function mappeTSFehlerCode(&$cHinweis, &$cFehler, $nReturnValue)
{
    if ($nReturnValue == -1) {
        $cHinweis .= 'Das Trusted Shops Zertifikat wurde erfolgreich gespeichert.<br />
            Bitte Besuchen Sie unter dem Backend Men&uuml;punkt "Admin" die "Boxenverwaltung" und f&uuml;gen Sie die Trusted Shops Siegelbox hinzu.<br />';
    } elseif ($nReturnValue == 1) {
        // Fehlende Sprache + TSID
        $cFehler .= 'Fehler: Bitte f&uuml;llen Sie alle Felder aus.';
    } elseif ($nReturnValue == 2) {
        // Das Zertifikat existiert nich
        $cFehler .= 'Fehler: Das Zertifikat zu Ihrer Trusted Shops ID existiert nicht.';
    } elseif ($nReturnValue == 3) {
        // Das Zertifikat ist abgelaufen
        $cFehler .= 'Fehler: Das Zertifikat zu Ihrer Trusted Shops ID ist abgelaufen.';
    } elseif ($nReturnValue == 4) {
        // Das Zertifikat ist gesperrt
        $cFehler .= 'Fehler: Das Zertifikat zu Ihrer Trusted Shops ID ist gesperrt.';
    } elseif ($nReturnValue == 5) {
        // Shop befindet sich in der Zertifizierung
        $cFehler .= 'Fehler: Das Zertifikat befindet sich in der Zertifzierung.';
    } elseif ($nReturnValue == 6) {
        // Keine Excellence-Variante mit Kaeuferschutz im Checkout-Prozess
        $cFehler .= 'Fehler: Das Zertifikat hat keine Excellence-Variante mit K&auml;uferschutz im Checkout-Prozess.';
    } elseif ($nReturnValue == 7) {
        // Ungueltige Sprache fuer gewaehlte TS-ID
        $cFehler .= 'Fehler: Ihre gew&auml;hlte Sprache stimmt nicht mit Ihrer Trusted Shops ID &uuml;berein.';
    } elseif ($nReturnValue == 8) {
        // Benutzername & Passwort ungueltig
        $cFehler .= 'Fehler: Ihre WebService User ID (wsUser) und Ihr WebService Passwort (wsPassword) konnten nicht verifiziert werden.';
    } elseif ($nReturnValue == 9) {
        // Zertifikat konnte nicht gespeichert werden
        $cFehler .= 'Fehler: Das Zertifikat konnte nicht gespeichert werden.';
    } elseif ($nReturnValue == 10) {
        // Falsche Kaeuferschutzvariante
        $cFehler .= 'Fehler: Ihre Trusted Shops ID entspricht nicht dem ausgew&auml;hlten K&auml;uferschutz Typ.';
    } elseif ($nReturnValue == 11) {
        // SOAP Fehler
        $cFehler .= 'Fehler: Interner SOAP Fehler. Entweder ist das Netzwerkprotokoll SOAP nicht eingebunden oder der Trusted Shops Service ist momentan nicht erreichbar.';
    }
}
