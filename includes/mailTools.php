<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceMail.php';
require_once PFAD_ROOT . PFAD_PHPMAILER . 'PHPMailer.php';

/**
 * @param array $params
 * @param JTLSmarty $smarty
 * @return string
 */
function includeMailTemplate($params, &$smarty)
{
    if (isset($params['template'], $params['type']) &&
        ($params['type'] === 'text' || $params['type'] === 'plain' || $params['type'] === 'html') &&
        $smarty->getTemplateVars('int_lang') !== null) {
        $res            = null;
        $currenLanguage = null;
        $vorlage        = Shop::DB()->select('temailvorlageoriginal', 'cDateiname', $params['template']);
        if (isset($vorlage->kEmailvorlage) && $vorlage->kEmailvorlage > 0) {
            $row            = 'cContentText';
            $currenLanguage = $smarty->getTemplateVars('int_lang');
            if ($params['type'] === 'html') {
                $row = 'cContentHtml';
            }
            $res = Shop::DB()->query(
                "SELECT " . $row . " AS content
                    FROM temailvorlagesprache
                    WHERE kSprache = " . (int)$currenLanguage->kSprache . "
                    AND kEmailvorlage = " . (int)$vorlage->kEmailvorlage, 1
            );
        }
        if (isset($res->content)) {
            if ($params['type'] === 'plain') {
                $params['type'] = 'text';
            }

            return $smarty->fetch('db:' . $params['type'] . '_' .
                $vorlage->kEmailvorlage . '_' . $currenLanguage->kSprache);
        }
    }

    return '';
}

/**
 * @param string        $ModulId
 * @param stdClass      $Object
 * @param null|stdClass $mail
 * @return null|bool|stdClass
 */
function sendeMail($ModulId, $Object, $mail = null)
{
    $Emailvorlage = null;
    $bodyHtml     = '';
    if (!is_object($mail)) {
        $mail = new stdClass();
    }
    $Einstellungen = Shop::getSettings([
        CONF_EMAILS,
        CONF_ZAHLUNGSARTEN,
        CONF_GLOBAL,
        CONF_KAUFABWICKLUNG,
        CONF_KONTAKTFORMULAR,
        CONF_ARTIKELDETAILS,
        CONF_TRUSTEDSHOPS
    ]);
    $absender_name = $Einstellungen['emails']['email_master_absender_name'];
    $absender_mail = $Einstellungen['emails']['email_master_absender'];
    $kopie         = '';
    //Smarty Objekt bauen
    $mailSmarty = new JTLSmarty(true, false, false, 'mail');
    $mailSmarty->registerResource('db', new SmartyResourceNiceDB('mail'))
               ->registerPlugin('function', 'includeMailTemplate', 'includeMailTemplate')
               ->setCaching(0)
               ->setDebugging(0)
               ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR)
               ->setTemplateDir(PFAD_ROOT . PFAD_EMAILTEMPLATES);
    if (!isset($Object->tkunde)) {
        $Object->tkunde = new stdClass();
    }
    if (!isset($Object->tkunde->kKundengruppe) || !$Object->tkunde->kKundengruppe) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kundengruppe.php';
        $Object->tkunde->kKundengruppe = Kundengruppe::getDefaultGroupID();
    }
    $Object->tfirma        = Shop::DB()->query("SELECT * FROM tfirma", 1);
    $Object->tkundengruppe = Shop::DB()->select(
        'tkundengruppe',
        'kKundengruppe',
        (int)$Object->tkunde->kKundengruppe
    );
    if (isset($Object->tkunde->kSprache) && $Object->tkunde->kSprache > 0) {
        $kundengruppensprache = Shop::DB()->select(
            'tkundengruppensprache',
            'kKundengruppe',
            (int)$Object->tkunde->kKundengruppe,
            'kSprache',
            (int)$Object->tkunde->kSprache
        );
        if (isset($kundengruppensprache->cName) && $kundengruppensprache->cName !== $Object->tkundengruppe->cName) {
            $Object->tkundengruppe->cName = $kundengruppensprache->cName;
        }
    }
    if (isset($_SESSION['currentLanguage']->kSprache)) {
        $Sprache = $_SESSION['currentLanguage'];
    } else {
        if (isset($Object->tkunde->kSprache) && $Object->tkunde->kSprache > 0) {
            $Sprache = Shop::DB()->select('tsprache', 'kSprache', (int)$Object->tkunde->kSprache);
        }
        if (isset($Object->NewsletterEmpfaenger->kSprache) && $Object->NewsletterEmpfaenger->kSprache > 0) {
            $Sprache = Shop::DB()->select('tsprache', 'kSprache', $Object->NewsletterEmpfaenger->kSprache);
        }
        if (empty($Sprache)) {
            $Sprache = isset($_SESSION['kSprache'])
                ? Shop::DB()->select('tsprache', 'kSprache', $_SESSION['kSprache'])
                : Shop::DB()->select('tsprache', 'cShopStandard', 'Y');
        }
    }
    $oKunde = lokalisiereKunde($Sprache, $Object->tkunde);

    $mailSmarty->assign('int_lang', $Sprache)//assign the current language for includeMailTemplate()
               ->assign('Firma', $Object->tfirma)
               ->assign('Kunde', $oKunde)
               ->assign('Kundengruppe', $Object->tkundengruppe)
               ->assign('NettoPreise', $Object->tkundengruppe->nNettoPreise)
               ->assign('ShopLogoURL', Shop::getLogo(true))
               ->assign('ShopURL', Shop::getURL());

    $AGB     = new stdClass();
    $WRB     = new stdClass();
    $WRBForm = new stdClass();
    $oAGBWRB = Shop::DB()->select(
        'ttext',
        ['kSprache', 'kKundengruppe'],
        [(int)$Sprache->kSprache, (int)$Object->tkunde->kKundengruppe]
    );
    $AGB->cContentText     = isset($oAGBWRB->cAGBContentText) ? $oAGBWRB->cAGBContentText : '';
    $AGB->cContentHtml     = isset($oAGBWRB->cAGBContentHtml) ? $oAGBWRB->cAGBContentHtml : '';
    $WRB->cContentText     = isset($oAGBWRB->cWRBContentText) ? $oAGBWRB->cWRBContentText : '';
    $WRB->cContentHtml     = isset($oAGBWRB->cWRBContentHtml) ? $oAGBWRB->cWRBContentHtml : '';
    $WRBForm->cContentHtml = isset($oAGBWRB->cWRBFormContentHtml) ? $oAGBWRB->cWRBFormContentHtml : '';
    $WRBForm->cContentText = isset($oAGBWRB->cWRBFormContentText) ? $oAGBWRB->cWRBFormContentText : '';

    $mailSmarty->assign('AGB', $AGB)
               ->assign('WRB', $WRB)
               ->assign('WRBForm', $WRBForm)
               ->assign('IP', StringHandler::htmlentities(StringHandler::filterXSS(gibIP())));

    $Object = lokalisiereInhalt($Object);
    // ModulId von einer Plugin Emailvorlage vorhanden?
    $cTable        = 'temailvorlage';
    $cTableSprache = 'temailvorlagesprache';
    $cTableSetting = 'temailvorlageeinstellungen';
    $cSQLWhere     = " cModulId = '" . $ModulId . "'";
    if (strpos($ModulId, 'kPlugin') !== false) {
        list($cPlugin, $kPlugin, $cModulId) = explode('_', $ModulId);
        $cTable                             = 'tpluginemailvorlage';
        $cTableSprache                      = 'tpluginemailvorlagesprache';
        $cTableSetting                      = 'tpluginemailvorlageeinstellungen';
        $cSQLWhere                          = " kPlugin = " . $kPlugin . " AND cModulId = '" . $cModulId . "'";
        $mailSmarty->assign('oPluginMail', $Object);
    }

    $Emailvorlage = Shop::DB()->query("SELECT * FROM " . $cTable . " WHERE " . $cSQLWhere, 1);
    // Email aktiv?
    if (isset($Emailvorlage->cAktiv) && $Emailvorlage->cAktiv === 'N') {
        Jtllog::writeLog(
            'Emailvorlage mit der ModulId ' . $ModulId . ' ist deaktiviert!',
            JTLLOG_LEVEL_NOTICE,
            false,
            'kEmailvorlage'
        );

        return false;
    }
    // Emailvorlageneinstellungen laden
    if (isset($Emailvorlage->kEmailvorlage) && $Emailvorlage->kEmailvorlage > 0) {
        $Emailvorlage->oEinstellung_arr = Shop::DB()->selectAll(
            $cTableSetting,
            'kEmailvorlage',
            $Emailvorlage->kEmailvorlage
        );
        // Assoc bauen
        if (is_array($Emailvorlage->oEinstellung_arr) && count($Emailvorlage->oEinstellung_arr) > 0) {
            $Emailvorlage->oEinstellungAssoc_arr = [];
            foreach ($Emailvorlage->oEinstellung_arr as $oEinstellung) {
                $Emailvorlage->oEinstellungAssoc_arr[$oEinstellung->cKey] = $oEinstellung->cValue;
            }
        }
    }

    if (!isset($Emailvorlage->kEmailvorlage) || (int)$Emailvorlage->kEmailvorlage === 0) {
        Jtllog::writeLog(
            'Keine Emailvorlage mit der ModulId ' . $ModulId . ' vorhanden oder diese Emailvorlage ist nicht aktiv!',
            JTLLOG_LEVEL_ERROR,
            false,
            'kEmailvorlage'
        );

        return false;
    }
    $mail->kEmailvorlage = $Emailvorlage->kEmailvorlage;

    $Emailvorlagesprache    = Shop::DB()->select(
        $cTableSprache,
        ['kEmailvorlage', 'kSprache'],
        [(int)$Emailvorlage->kEmailvorlage, (int)$Sprache->kSprache]
    );
    $Emailvorlage->cBetreff = injectSubject(
        $Object, (isset($Emailvorlagesprache->cBetreff) ? $Emailvorlagesprache->cBetreff : null)
    );

    if (isset($Emailvorlage->oEinstellungAssoc_arr['cEmailSenderName'])) {
        $absender_name = $Emailvorlage->oEinstellungAssoc_arr['cEmailSenderName'];
    }
    if (isset($Emailvorlage->oEinstellungAssoc_arr['cEmailOut'])) {
        $absender_mail = $Emailvorlage->oEinstellungAssoc_arr['cEmailOut'];
    }
    if (isset($Emailvorlage->oEinstellungAssoc_arr['cEmailCopyTo'])) {
        $kopie = $Emailvorlage->oEinstellungAssoc_arr['cEmailCopyTo'];
    }
    switch ($ModulId) {
        case MAILTEMPLATE_GUTSCHEIN:
            $mailSmarty->assign('Gutschein', $Object->tgutschein);
            break;

        case MAILTEMPLATE_BESTELLBESTAETIGUNG:
            $mailSmarty->assign('Bestellung', $Object->tbestellung)
                       ->assign('Verfuegbarkeit_arr', isset($Object->cVerfuegbarkeit_arr)
                           ? $Object->cVerfuegbarkeit_arr
                           : null)
                       ->assign('oTrustedShopsBewertenButton', null);
            // Zahlungsart Einstellungen
            if (isset($Object->tbestellung->Zahlungsart->cModulId) &&
                strlen($Object->tbestellung->Zahlungsart->cModulId) > 0
            ) {
                $cModulId         = $Object->tbestellung->Zahlungsart->cModulId;
                $oZahlungsartConf = Shop::DB()->query(
                    "SELECT tzahlungsartsprache.*
                        FROM tzahlungsartsprache
                        JOIN tzahlungsart 
                            ON tzahlungsart.kZahlungsart = tzahlungsartsprache.kZahlungsart
                            AND tzahlungsart.cModulId = '" . $cModulId . "'
                        WHERE tzahlungsartsprache.cISOSprache = '" . $Sprache->cISO . "'", 1
                );
                if (isset($oZahlungsartConf->kZahlungsart) && $oZahlungsartConf->kZahlungsart > 0) {
                    $mailSmarty->assign('Zahlungsart', $oZahlungsartConf);
                }
            }
            // Trusted Shops
            if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                $langID = isset($_SESSION['cISOSprache'])
                    ? $_SESSION['cISOSprache']
                    : 'ger'; //workaround for testmails from backend
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';
                $oTrustedShops                = new TrustedShops(-1, StringHandler::convertISO2ISO639($langID));
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($langID)
                );
                if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0 && $oTrustedShopsKundenbewertung->nStatus == 1) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', gibTrustedShopsBewertenButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }

            break;

        case MAILTEMPLATE_BESTELLUNG_AKTUALISIERT:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            // Zahlungsart Einstellungen
            if (isset($Object->tbestellung->Zahlungsart->cModulId) &&
                strlen($Object->tbestellung->Zahlungsart->cModulId) > 0
            ) {
                $cModulId         = $Object->tbestellung->Zahlungsart->cModulId;
                $oZahlungsartConf = Shop::DB()->query(
                    "SELECT tzahlungsartsprache.*
                        FROM tzahlungsartsprache
                        JOIN tzahlungsart 
                            ON tzahlungsart.kZahlungsart = tzahlungsartsprache.kZahlungsart
                            AND tzahlungsart.cModulId = '" . $cModulId . "'
                        WHERE tzahlungsartsprache.cISOSprache = '" . $Sprache->cISO . "'", 1
                );

                if (isset($oZahlungsartConf->kZahlungsart) && $oZahlungsartConf->kZahlungsart > 0) {
                    $mailSmarty->assign('Zahlungsart', $oZahlungsartConf);
                }
            }
            // Trusted Shops
            if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';
                $oTrustedShops                = new TrustedShops(
                    -1,
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0 && $oTrustedShopsKundenbewertung->nStatus == 1) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', gibTrustedShopsBewertenButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }

            break;

        case MAILTEMPLATE_PASSWORT_VERGESSEN:
            $mailSmarty->assign('passwordResetLink', $Object->passwordResetLink)
                       ->assign('Neues_Passwort', $Object->neues_passwort);
            break;

        case MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN:
            $mailSmarty->assign('passwordResetLink', $Object->passwordResetLink);
            break;

        case MAILTEMPLATE_BESTELLUNG_BEZAHLT:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            break;

        case MAILTEMPLATE_BESTELLUNG_STORNO:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            break;

        case MAILTEMPLATE_BESTELLUNG_RESTORNO:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            break;

        case MAILTEMPLATE_BESTELLUNG_TEILVERSANDT:
        case MAILTEMPLATE_BESTELLUNG_VERSANDT:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);
            // Trusted Shops
            if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';
                $oTrustedShops                = new TrustedShops(
                    -1,
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0 && $oTrustedShopsKundenbewertung->nStatus == 1) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', gibTrustedShopsBewertenButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }

            break;

        case MAILTEMPLATE_NEUKUNDENREGISTRIERUNG:
            break;

        case MAILTEMPLATE_ACCOUNTERSTELLUNG_DURCH_BETREIBER:
            break;

        case MAILTEMPLATE_KUNDENACCOUNT_GELOESCHT:
            break;

        case MAILTEMPLATE_KUPON:
            $mailSmarty->assign('Kupon', $Object->tkupon);
            break;

        case MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN:
            break;

        case MAILTEMPLATE_KONTAKTFORMULAR:
            if (isset($Einstellungen['kontakt']['kontakt_absender_name'])) {
                $absender_name = $Einstellungen['kontakt']['kontakt_absender_name'];
            }
            if (isset($Einstellungen['kontakt']['kontakt_absender_mail'])) {
                $absender_mail = $Einstellungen['kontakt']['kontakt_absender_mail'];
            }
            $mailSmarty->assign('Nachricht', $Object->tnachricht);
            break;

        case MAILTEMPLATE_PRODUKTANFRAGE:
            if (isset($Einstellungen['artikeldetails']['produktfrage_absender_name'])) {
                $absender_name = $Einstellungen['artikeldetails']['produktfrage_absender_name'];
            }
            if (isset($Einstellungen['artikeldetails']['produktfrage_absender_mail'])) {
                $absender_mail = $Einstellungen['artikeldetails']['produktfrage_absender_mail'];
            }
            $mailSmarty->assign('Nachricht', $Object->tnachricht)
                       ->assign('Artikel', $Object->tartikel);
            break;

        case MAILTEMPLATE_PRODUKT_WIEDER_VERFUEGBAR:
            $mailSmarty->assign('Benachrichtigung', $Object->tverfuegbarkeitsbenachrichtigung)
                       ->assign('Artikel', $Object->tartikel);
            break;

        case MAILTEMPLATE_WUNSCHLISTE:
            $mailSmarty->assign('Wunschliste', $Object->twunschliste);
            break;

        case MAILTEMPLATE_BEWERTUNGERINNERUNG:
            $mailSmarty->assign('Bestellung', $Object->tbestellung);

            // Trusted Shops
            if ($Einstellungen['trustedshops']['trustedshops_kundenbewertung_anzeigen'] === 'Y') {
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.TrustedShops.php';
                $oTrustedShops                = new TrustedShops(
                    -1,
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                $oTrustedShopsKundenbewertung = $oTrustedShops->holeKundenbewertungsstatus(
                    StringHandler::convertISO2ISO639($_SESSION['cISOSprache'])
                );
                if (strlen($oTrustedShopsKundenbewertung->cTSID) > 0 && $oTrustedShopsKundenbewertung->nStatus == 1) {
                    $mailSmarty->assign('oTrustedShopsBewertenButton', gibTrustedShopsBewertenButton(
                        $Object->tbestellung->oRechnungsadresse->cMail,
                        $Object->tbestellung->cBestellNr
                    ));
                }
            }
            break;

        case MAILTEMPLATE_NEWSLETTERANMELDEN:
            $mailSmarty->assign('NewsletterEmpfaenger', $Object->NewsletterEmpfaenger);
            break;

        case MAILTEMPLATE_KUNDENWERBENKUNDEN:
            $mailSmarty->assign('Neukunde', $Object->oNeukunde)
                       ->assign('Bestandskunde', $Object->oBestandskunde);
            break;

        case MAILTEMPLATE_KUNDENWERBENKUNDENBONI:
            $mailSmarty->assign('BestandskundenBoni', $Object->BestandskundenBoni)
                       ->assign('Neukunde', $Object->oNeukunde)
                       ->assign('Bestandskunde', $Object->oBestandskunde);
            break;

        case MAILTEMPLATE_STATUSEMAIL:
            $Object->mail->toName   = $Object->tfirma->cName . ' ' . $Object->cIntervall;
            $Emailvorlage->cBetreff = $Object->tfirma->cName . ' ' . $Object->cIntervall;
            $mailSmarty->assign('oMailObjekt', $Object);
            break;

        case MAILTEMPLATE_CHECKBOX_SHOPBETREIBER:
            $mailSmarty->assign('oCheckBox', $Object->oCheckBox)
                       ->assign('oKunde', $Object->oKunde)
                       ->assign('cAnzeigeOrt', $Object->cAnzeigeOrt)
                       ->assign('oSprache', $Sprache);
            if (empty($Object->oKunde->cVorname) && empty($Object->oKunde->cNachname)) {
                $subjectLineCustomer = $Object->oKunde->cMail;
            } else {
                $subjectLineCustomer = $Object->oKunde->cVorname . ' ' . $Object->oKunde->cNachname;
            }
            $Emailvorlage->cBetreff = $Object->oCheckBox->cName .
                ' - ' . $subjectLineCustomer;
            break;

        case MAILTEMPLATE_RMA_ABGESENDET:
            if (method_exists($Object->oRMA, 'getRMANumber')) {
                $Emailvorlage->cBetreff = $Object->tfirma->cName . ' ' . $Object->oRMA->getRMANumber();
            }
            $mailSmarty->assign('oRMA', $Object->oRMA);
            break;
        case MAILTEMPLATE_BEWERTUNG_GUTHABEN:
            $waehrung                                                 = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
            $Object->oBewertungGuthabenBonus->fGuthabenBonusLocalized = gibPreisStringLocalized(
                $Object->oBewertungGuthabenBonus->fGuthabenBonus,
                $waehrung,
                0
            );
            $mailSmarty->assign('oKunde', $Object->tkunde)
                       ->assign('oBewertungGuthabenBonus', $Object->oBewertungGuthabenBonus);
            break;
    }

    executeHook(HOOK_MAILTOOLS_INC_SWITCH);

    $mailSmarty->assign('Einstellungen', $Einstellungen);

    $cPluginBody = '';
    if (isset($Emailvorlage->kPlugin) && $Emailvorlage->kPlugin > 0) {
        $cPluginBody = '_' . $Emailvorlage->kPlugin;
    }
    //fetch
    if ($Emailvorlage->cMailTyp === 'text/html' || $Emailvorlage->cMailTyp === 'html') {
        $bodyHtml = $mailSmarty->fetch('db:html_' . $Emailvorlage->kEmailvorlage . '_' . $Sprache->kSprache . $cPluginBody);
    }
    $bodyText = $mailSmarty->fetch('db:text_' . $Emailvorlage->kEmailvorlage . '_' . $Sprache->kSprache . $cPluginBody);
    // AKZ, AGB und WRB anhängen falls eingestellt
    if ($Emailvorlage->nAKZ == 1) {
        if (!isset($akzHtml)) {
            $akzHtml = '';
        }
        if (!isset($akzText)) {
            $akzText = '';
        }
        $akzHtml .= $mailSmarty->fetch('db:html_core_jtl_anbieterkennzeichnung_' . $Sprache->kSprache . $cPluginBody);
        $akzText .= $mailSmarty->fetch('db:text_core_jtl_anbieterkennzeichnung_' . $Sprache->kSprache . $cPluginBody);

        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= '<br /><br />' . $akzHtml;
        }
        $bodyText .= "\n\n" . $akzText;
    }
    if ($Emailvorlage->nWRB == 1) {
        $cUeberschrift = Shop::Lang()->get('wrb', 'global');
        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= "<br /><br /><h3>{$cUeberschrift}</h3>" . $WRB->cContentHtml;
        }
        $bodyText .= "\n\n" . $cUeberschrift . "\n\n" . $WRB->cContentText;
    }
    if ($Emailvorlage->nWRBForm == 1) {
        $cUeberschrift = Shop::Lang()->get('wrbform', 'global');
        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= "<br /><br /><h3>{$cUeberschrift}</h3>" . $WRBForm->cContentHtml;
        }
        $bodyText .= "\n\n" . $cUeberschrift . "\n\n" . $WRBForm->cContentText;
    }
    if ($Emailvorlage->nAGB == 1) {
        $cUeberschrift = Shop::Lang()->get('agb', 'global');
        if (strlen($bodyHtml) > 0) {
            $bodyHtml .= "<br /><br /><h3>{$cUeberschrift}</h3>" . $AGB->cContentHtml;
        }
        $bodyText .= "\n\n{$cUeberschrift}\n\n{$AGB->cContentText}";
    }
    //mail vorbereiten
    if (isset($Object->tkunde->cMail)) {
        $mail->toEmail = $Object->tkunde->cMail;
        $mail->toName  = $Object->tkunde->cVorname . ' ' . $Object->tkunde->cNachname;
    } elseif (isset($Object->NewsletterEmpfaenger->cEmail) && strlen($Object->NewsletterEmpfaenger->cEmail) > 0) {
        $mail->toEmail = $Object->NewsletterEmpfaenger->cEmail;
    }
    //some mail servers seem to have problems with very long lines - wordwrap() if necessary
    $hasLongLines = false;
    foreach (preg_split('/((\r?\n)|(\r\n?))/', $bodyHtml) as $line) {
        if (strlen($line) > 987) {
            $hasLongLines = true;
            break;
        }
    }
    if ($hasLongLines) {
        $bodyHtml = wordwrap($bodyHtml, 900);
    }
    $hasLongLines = false;
    foreach (preg_split('/((\r?\n)|(\r\n?))/', $bodyText) as $line) {
        if (strlen($line) > 987) {
            $hasLongLines = true;
            break;
        }
    }
    if ($hasLongLines) {
        $bodyText = wordwrap($bodyText, 900);
    }

    $mail->fromEmail     = $absender_mail;
    $mail->fromName      = $absender_name;
    $mail->replyToEmail  = $absender_mail;
    $mail->replyToName   = $absender_name;
    $mail->subject       = StringHandler::htmlentitydecode($Emailvorlage->cBetreff);
    $mail->bodyText      = $bodyText;
    $mail->bodyHtml      = $bodyHtml;
    $mail->lang          = $Sprache->cISO;
    $mail->methode       = $Einstellungen['emails']['email_methode'];
    $mail->sendmail_pfad = $Einstellungen['emails']['email_sendmail_pfad'];
    $mail->smtp_hostname = $Einstellungen['emails']['email_smtp_hostname'];
    $mail->smtp_port     = $Einstellungen['emails']['email_smtp_port'];
    $mail->smtp_auth     = $Einstellungen['emails']['email_smtp_auth'];
    $mail->smtp_user     = $Einstellungen['emails']['email_smtp_user'];
    $mail->smtp_pass     = $Einstellungen['emails']['email_smtp_pass'];
    $mail->SMTPSecure    = $Einstellungen['emails']['email_smtp_verschluesselung'];
    $mail->SMTPAutoTLS   = !empty($mail->SMTPSecure);

    $mailSmarty->assign('absender_name', $absender_name)
               ->assign('absender_mail', $absender_mail);
    //Ausnahmen
    if (isset($Object->mail->fromEmail)) {
        $mail->fromEmail = $Object->mail->fromEmail;
    }
    if (isset($Object->mail->fromName)) {
        $mail->fromName = $Object->mail->fromName;
    }
    if (isset($Object->mail->toEmail)) {
        $mail->toEmail = $Object->mail->toEmail;
    }
    if (isset($Object->mail->toName)) {
        $mail->toName = $Object->mail->toName;
    }
    if (isset($Object->mail->replyToEmail)) {
        $mail->replyToEmail = $Object->mail->replyToEmail;
    }
    if (isset($Object->mail->replyToName)) {
        $mail->replyToName = $Object->mail->replyToName;
    }
    if (isset($Emailvorlagesprache->cPDFS) && strlen($Emailvorlagesprache->cPDFS) > 0) {
        $mail->cPDFS_arr = getPDFAttachments($Emailvorlagesprache->cPDFS, $Emailvorlagesprache->cDateiname);
    }
    executeHook(HOOK_MAILTOOLS_SENDEMAIL_ENDE, [
        'mailsmarty'    => &$mailSmarty,
        'mail'          => &$mail,
        'kEmailvorlage' => $Emailvorlage->kEmailvorlage,
        'kSprache'      => $Sprache->kSprache,
        'cPluginBody'   => $cPluginBody,
        'Emailvorlage'  => $Emailvorlage
    ]);

    verschickeMail($mail);

    if ($kopie) {
        $mail->toEmail      = $kopie;
        $mail->toName       = $kopie;
        $mail->fromEmail    = $absender_mail;
        $mail->fromName     = $absender_name;
        $mail->replyToEmail = $Object->tkunde->cMail;
        $mail->replyToName  = $Object->tkunde->cVorname . ' ' . $Object->tkunde->cNachname;
        verschickeMail($mail);
    }
    // Kopie Plugin
    if (isset($Object->oKopie, $Object->oKopie->cToMail) && strlen($Object->oKopie->cToMail) > 0) {
        $mail->toEmail      = $Object->oKopie->cToMail;
        $mail->toName       = $Object->oKopie->cToMail;
        $mail->fromEmail    = $absender_mail;
        $mail->fromName     = $absender_name;
        $mail->replyToEmail = $Object->tkunde->cMail;
        $mail->replyToName  = $Object->tkunde->cVorname . ' ' . $Object->tkunde->cNachname;
        verschickeMail($mail);
    }

    return $mail;
}

/**
 * @param string $cEmail
 * @return bool
 */
function pruefeGlobaleEmailBlacklist($cEmail)
{
    $oEmailBlacklist = Shop::DB()->select('temailblacklist', 'cEmail', Shop::DB()->escape($cEmail));

    if (isset($oEmailBlacklist->cEmail) && strlen($oEmailBlacklist->cEmail) > 0) {
        $oEmailBlacklistBlock                = new stdClass();
        $oEmailBlacklistBlock->cEmail        = $oEmailBlacklist->cEmail;
        $oEmailBlacklistBlock->dLetzterBlock = 'now()';

        Shop::DB()->insert('temailblacklistblock', $oEmailBlacklistBlock);

        return true;
    }

    return false;
}

/**
 * @param object $mail
 */
function verschickeMail($mail)
{
    $kEmailvorlage = null;
    if (isset($mail->kEmailvorlage)) {
        if ((int)$mail->kEmailvorlage > 0) {
            $kEmailvorlage = (int)$mail->kEmailvorlage;
        }
        unset($mail->kEmailvorlage);
    }

    // EmailBlacklist beachten
    $Emailconfig = Shop::getSettings([CONF_EMAILBLACKLIST]);
    if ($Emailconfig['emailblacklist']['blacklist_benutzen'] === 'Y') {
        if (pruefeGlobaleEmailBlacklist($mail->toEmail)) {
            return;
        }
    }
    // BodyText encoden
    $mail->bodyText  = StringHandler::htmlentitydecode(str_replace('&euro;', 'EUR', $mail->bodyText), ENT_NOQUOTES);
    $mail->cFehler   = '';
    $GLOBALS['mail'] = $mail; // Plugin Work Around

    $bSent = false;
    if (!$mail->methode) {
        SendNiceMailReply(
            $mail->fromName,
            $mail->fromEmail,
            $mail->fromEmail,
            $mail->toEmail,
            $mail->subject,
            $mail->bodyText,
            $mail->bodyHtml
        );
    } else {
        //phpmailer
        $phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
        $lang      = ($mail->lang === 'DE' || $mail->lang === 'ger') ? 'de' : 'end';
        $phpmailer->setLanguage($lang, PFAD_ROOT . PFAD_PHPMAILER . 'language/');
        $phpmailer->Timeout  = SOCKET_TIMEOUT;
        $phpmailer->From     = $mail->fromEmail;
        $phpmailer->Sender   = $mail->fromEmail;
        $phpmailer->FromName = $mail->fromName;
        $phpmailer->addAddress($mail->toEmail, (!empty($mail->toName) ? $mail->toName : ''));
        $phpmailer->addReplyTo($mail->replyToEmail, $mail->replyToName);
        $phpmailer->Subject = $mail->subject;

        switch ($mail->methode) {
            case 'mail':
                $phpmailer->isMail();
                break;
            case 'sendmail':
                $phpmailer->isSendmail();
                $phpmailer->Sendmail = $mail->sendmail_pfad;
                break;
            case 'qmail':
                $phpmailer->isQmail();
                break;
            case 'smtp':
                $phpmailer->isSMTP();
                $phpmailer->Host          = $mail->smtp_hostname;
                $phpmailer->Port          = $mail->smtp_port;
                $phpmailer->SMTPKeepAlive = true;
                $phpmailer->SMTPAuth      = $mail->smtp_auth;
                $phpmailer->Username      = $mail->smtp_user;
                $phpmailer->Password      = $mail->smtp_pass;
                $phpmailer->SMTPSecure    = $mail->SMTPSecure;
                $phpmailer->SMTPAutoTLS   = isset($mail->SMTPAutoTLS)
                    ? $mail->SMTPAutoTLS
                    : (empty($mail->SMTPSecure)
                        ? false
                        : true);
                break;
        }
        if ($mail->bodyHtml) {
            $phpmailer->isHTML(true);
            $phpmailer->Body    = $mail->bodyHtml;
            $phpmailer->AltBody = $mail->bodyText;
        } else {
            $phpmailer->isHTML(false);
            $phpmailer->Body = $mail->bodyText;
        }

        if (isset($mail->cPDFS_arr) && count($mail->cPDFS_arr) > 0) {
            $cUploadVerzeichnis = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;

            foreach ($mail->cPDFS_arr as $i => $cPDFS) {
                $phpmailer->addAttachment(
                    $cUploadVerzeichnis . $cPDFS->fileName, $cPDFS->publicName . '.pdf',
                    'base64',
                    'application/pdf'
                );
            }
        }
        if (isset($mail->oAttachment_arr) && count($mail->oAttachment_arr) > 0) {
            foreach ($mail->oAttachment_arr as $oAttachment) {
                if (empty($oAttachment->cEncoding)) {
                    $oAttachment->cEncoding = 'base64';
                }
                if (empty($oAttachment->cType)) {
                    $oAttachment->cType = 'application/octet-stream';
                }
                $phpmailer->addAttachment(
                    $oAttachment->cFilePath,
                    $oAttachment->cName,
                    $oAttachment->cEncoding,
                    $oAttachment->cType
                );
            }
        }

        $bSent         = $phpmailer->send();
        $mail->cFehler = $phpmailer->ErrorInfo;
    }
    // Emailhistory
    if ($bSent) {
        $oEmailhistory = new Emailhistory();
        $oEmailhistory->setEmailvorlage($kEmailvorlage)
                      ->setSubject($mail->subject)
                      ->setFromName($mail->fromName)
                      ->setFromEmail($mail->fromEmail)
                      ->setToName((isset($mail->toName) ? $mail->toName : ''))
                      ->setToEmail($mail->toEmail)
                      ->setSent('now()')
                      ->save();
    } else {
        Jtllog::writeLog(
            'Email konnte nicht versendet werden! Fehler: ' . $mail->cFehler,
            JTLLOG_LEVEL_ERROR,
            false,
            'kEmailvorlage'
        );
    }

    executeHook(HOOK_MAILTOOLS_VERSCHICKEMAIL_GESENDET);
}

/**
 * @param object $Object
 * @param string $Betreff
 * @return mixed
 */
function injectSubject($Object, $Betreff)
{
    $a     = [];
    $b     = [];
    $keys1 = array_keys(get_object_vars($Object));
    if (is_array($keys1)) {
        foreach ($keys1 as $obj) {
            if (is_object($Object->$obj) && is_array(get_object_vars($Object->$obj))) {
                $keys2 = array_keys(get_object_vars($Object->$obj));
                if (is_array($keys2)) {
                    foreach ($keys2 as $member) {
                        if ($member{0} !== 'k' &&
                            !is_array($Object->$obj->$member) &&
                            !is_object($Object->$obj->$member)
                        ) {
                            $a[] = '#' . strtolower(substr($obj, 1)) . '.' . strtolower(substr($member, 1)) . '#';
                            $b[] = $Object->$obj->$member;
                        }
                    }
                }
            }
        }
    }

    return str_replace($a, $b, $Betreff);
}

/**
 * @param object $Object
 * @return mixed
 */
function lokalisiereInhalt($Object)
{
    if (isset($Object->tgutschein->fWert) && $Object->tgutschein->fWert != 0) {
        $Object->tgutschein->cLocalizedWert = gibPreisStringLocalized($Object->tgutschein->fWert, 0, 0);
    }

    return $Object;
}

/**
 * @param object $sprache
 * @param Kunde   $kunde
 * @return mixed
 */
function lokalisiereKunde($sprache, $kunde)
{
    if (Shop::Lang()->gibISO() !== $sprache->cISO) {
        Shop::Lang()->setzeSprache($sprache->cISO);
    }
    // Anrede mappen
    if (isset($kunde->cAnrede)) {
        if ($kunde->cAnrede === 'w') {
            $kunde->cAnredeLocalized = Shop::Lang()->get('salutationW', 'global');
        } elseif ($kunde->cAnrede === 'm') {
            $kunde->cAnredeLocalized = Shop::Lang()->get('salutationM', 'global');
        } else {
            $kunde->cAnredeLocalized = Shop::Lang()->get('salutationGeneral', 'global');
        }
    }
    $kunde = deepCopy($kunde);
    if (isset($kunde->cLand)) {
        $cISOLand = $kunde->cLand;
        $sel_var  = 'cDeutsch';
        if (strtolower($sprache->cISO) !== 'ger') {
            $sel_var = 'cEnglisch';
        }
        $land = Shop::DB()->select(
            'tland',
            'cISO',
            $kunde->cLand,
            null,
            null,
            null,
            null,
            false,
            $sel_var . ' AS cName, cISO'
        );
        if (isset($land->cName)) {
            $kunde->cLand = $land->cName;
        }
    }
    if (isset($_SESSION['Kunde'], $cISOLand)) {
        $_SESSION['Kunde']->cLand = $cISOLand;
    }

    return $kunde;
}

/**
 * @param object        $oSprache
 * @param Lieferadresse $oLieferadresse
 * @return object
 */
function lokalisiereLieferadresse($oSprache, $oLieferadresse)
{
    $langRow = (strtolower($oSprache->cISO) === 'ger') ? 'cDeutsch' : 'cEnglisch';
    $land    = Shop::DB()->select(
        'tland',
        'cISO', $oLieferadresse->cLand,
        null, null,
        null, null,
        false,
        $langRow . ' AS cName, cISO'
    );
    if (!empty($land->cName)) {
        $oLieferadresse->cLand = $land->cName;
    }

    return $oLieferadresse;
}

/**
 * @deprecated since 4.05.2 - use getPDFAttachments instead
 * This function produces inconsistency between attachment and name if one or more attachment doesnt exist!
 * @param string $cPDF
 * @return array
 */
function bauePDFArrayZumVeschicken($cPDF)
{
    $cPDFTMP_arr        = explode(';', $cPDF);
    $cPDF_arr           = [];
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;
    if (count($cPDFTMP_arr) > 0) {
        foreach ($cPDFTMP_arr as $cPDFTMP) {
            if (strlen($cPDFTMP) > 0 && file_exists($cUploadVerzeichnis . $cPDFTMP)) {
                $cPDF_arr[] = $cPDFTMP;
            }
        }
    }

    return $cPDF_arr;
}

/**
 * @param string $cPDFs
 * @param string $cNames
 *
 * @return stdClass[]
 */
function getPDFAttachments($cPDFs, $cNames)
{
    $result      = [];
    $cPDFs_arr   = StringHandler::parseSSK(trim($cPDFs, ";\t\n\r\0"));
    $cNames_arr  = StringHandler::parseSSK(trim($cNames, ";\t\n\r\0"));
    $cUploadPath = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_EMAILPDFS;

    if (is_array($cPDFs_arr)) {
        foreach ($cPDFs_arr as $key => $pdfFile) {
            if (!empty($pdfFile) && file_exists($cUploadPath . $pdfFile)) {
                $result[] = (object)[
                    'fileName'   => $pdfFile,
                    'publicName' => isset($cNames_arr[$key]) ? $cNames_arr[$key] : $pdfFile,
                ];
            }
        }
    }

    return $result;
}

/**
 * @param string $cDateiname
 * @return array
 * @deprecated since 4.05 - use getPDFAttachments instead
 */
function baueDateinameArrayZumVeschicken($cDateiname)
{
    $cDateinameTMP_arr = explode(';', $cDateiname);
    $cDateiname_arr    = [];
    if (count($cDateinameTMP_arr) > 0) {
        foreach ($cDateinameTMP_arr as $cDateinameTMP) {
            if (strlen($cDateinameTMP) > 0) {
                $cDateiname_arr[] = $cDateinameTMP;
            }
        }
    }

    return $cDateiname_arr;
}
