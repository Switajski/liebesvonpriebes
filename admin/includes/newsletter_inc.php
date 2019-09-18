<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * @param array $Einstellungen
 * @return JTLSmarty
 */
function bereiteNewsletterVor($Einstellungen)
{
    //Smarty Objekt bauen
    $mailSmarty = new JTLSmarty(true, false, false, 'newsletter');
    $mailSmarty->setCaching(0)
               ->setDebugging(0)
               ->setCompileDir(PFAD_ROOT . PFAD_COMPILEDIR)
               ->registerResource('db', new SmartyResourceNiceDB('newsletter'))
               ->assign('Firma', Shop::DB()->query("SELECT * FROM tfirma", 1))
               ->assign('URL_SHOP', Shop::getURL())
               ->assign('Einstellungen', $Einstellungen);

    return $mailSmarty;
}

/**
 * @param JTLSmarty $mailSmarty
 * @param object    $oNewsletter
 * @param array     $Einstellungen
 * @param string    $oEmailempfaenger
 * @param array     $oArtikel_arr
 * @param array     $oHersteller_arr
 * @param array     $oKategorie_arr
 * @param string    $oKampagne
 * @param string    $oKunde
 * @return string|bool
 */
function versendeNewsletter(
    $mailSmarty,
    $oNewsletter,
    $Einstellungen,
    $oEmailempfaenger = '',
    $oArtikel_arr = [],
    $oHersteller_arr = [],
    $oKategorie_arr = [],
    $oKampagne = '',
    $oKunde = '')
{
    $mailSmarty->assign('oNewsletter', $oNewsletter)
               ->assign('Emailempfaenger', $oEmailempfaenger)
               ->assign('Kunde', $oKunde)
               ->assign('Artikelliste', $oArtikel_arr)
               ->assign('Herstellerliste', $oHersteller_arr)
               ->assign('Kategorieliste', $oKategorie_arr)
               ->assign('Kampagne', $oKampagne)
               ->assign('cNewsletterURL', Shop::getURL() .
                   '/newsletter.php?show=' .
                   (isset($oNewsletter->kNewsletter) ? $oNewsletter->kNewsletter : '0')
               );

    // Nettopreise?
    $NettoPreise = 0;
    $bodyHtml    = '';
    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        $oKundengruppe = Shop::DB()->query(
            "SELECT tkundengruppe.nNettoPreise
                FROM tkunde
                JOIN tkundengruppe 
                    ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
                WHERE tkunde.kKunde = " . (int)$oKunde->kKunde, 1
        );
        if (isset($oKundengruppe->nNettoPreise)) {
            $NettoPreise = $oKundengruppe->nNettoPreise;
        }
    }

    $mailSmarty->assign('NettoPreise', $NettoPreise);

    $cPixel = '';
    if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
        $cPixel = '<br /><img src="' . Shop::getURL() . '/' . PFAD_INCLUDES .
            'newslettertracker.php?kK=' . $oKampagne->kKampagne .
            '&kN=' . (isset($oNewsletter->kNewsletter) ? $oNewsletter->kNewsletter : 0) . '&kNE=' .
            (isset($oEmailempfaenger->kNewsletterEmpfaenger)
                ? $oEmailempfaenger->kNewsletterEmpfaenger
                : 0
            ) . '" alt="Newsletter" />';
    }

    $cTyp = 'VL';
    $nKey = isset($oNewsletter->kNewsletterVorlage) ? $oNewsletter->kNewsletterVorlage : 0;
    if (isset($oNewsletter->kNewsletter) && $oNewsletter->kNewsletter > 0) {
        $cTyp = 'NL';
        $nKey = $oNewsletter->kNewsletter;
    }
    //fetch
    if ($oNewsletter->cArt === 'text/html' || $oNewsletter->cArt === 'html') {
        try {
            $bodyHtml = $mailSmarty->fetch('db:' . $cTyp . '_' . $nKey . '_html') . $cPixel;
        } catch (Exception $e) {
            $GLOBALS['smarty']->assign('oSmartyError', $e->getMessage());

            return $e->getMessage();
        }
    }
    try {
        $bodyText = $mailSmarty->fetch('db:' . $cTyp . '_' . $nKey . '_text');
    } catch (Exception $e) {
        $GLOBALS['smarty']->assign('oSmartyError', $e->getMessage());

        return $e->getMessage();
    }
    //mail vorbereiten
    if (!isset($mail)) {
        $mail = new stdClass();
    }
    $mail->toEmail = $oEmailempfaenger->cEmail;
    $mail->toName  = (isset($oEmailempfaenger->cVorname)
            ? $oEmailempfaenger->cVorname
            : ''
        ) . ' ' .
        (isset($oEmailempfaenger->cNachname)
            ? $oEmailempfaenger->cNachname
            : ''
        );
    if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
        $mail->toName = (isset($oKunde->cVorname)
                ? $oKunde->cVorname
                : '') . ' ' . (
            isset($oKunde->cNachname)
                ? $oKunde->cNachname
                : ''
            );
    }

    $oSpracheTMP = Shop::DB()->select('tsprache', 'kSprache', (int)$oNewsletter->kSprache);

    $mail->fromEmail     = $Einstellungen['newsletter']['newsletter_emailadresse'];
    $mail->fromName      = $Einstellungen['newsletter']['newsletter_emailabsender'];
    $mail->replyToEmail  = $Einstellungen['newsletter']['newsletter_emailadresse'];
    $mail->replyToName   = $Einstellungen['newsletter']['newsletter_emailabsender'];
    $mail->subject       = $oNewsletter->cBetreff;
    $mail->bodyText      = $bodyText;
    $mail->bodyHtml      = $bodyHtml;
    $mail->lang          = $oSpracheTMP->cISO;
    $mail->methode       = $Einstellungen['newsletter']['newsletter_emailmethode'];
    $mail->sendmail_pfad = $Einstellungen['newsletter']['newsletter_sendmailpfad'];
    $mail->smtp_hostname = $Einstellungen['newsletter']['newsletter_smtp_host'];
    $mail->smtp_port     = $Einstellungen['newsletter']['newsletter_smtp_port'];
    $mail->smtp_auth     = $Einstellungen['newsletter']['newsletter_smtp_authnutzen'];
    $mail->smtp_user     = $Einstellungen['newsletter']['newsletter_smtp_benutzer'];
    $mail->smtp_pass     = $Einstellungen['newsletter']['newsletter_smtp_pass'];
    $mail->SMTPSecure    = $Einstellungen['newsletter']['newsletter_smtp_verschluesselung'];
    verschickeMail($mail);

    return true;
}

/**
 * @param JTLSmarty $mailSmarty
 * @param object    $oNewsletter
 * @param array     $oArtikel_arr
 * @param array     $oHersteller_arr
 * @param array     $oKategorie_arr
 * @param string    $oKampagne
 * @param string    $oEmailempfaenger
 * @param string    $oKunde
 * @return mixed
 */
function gibStaticHtml(
    $mailSmarty,
    $oNewsletter,
    $oArtikel_arr = [],
    $oHersteller_arr = [],
    $oKategorie_arr = [],
    $oKampagne = '',
    $oEmailempfaenger = '',
    $oKunde = ''
)
{
    $mailSmarty->assign('Emailempfaenger', $oEmailempfaenger)
               ->assign('Kunde', $oKunde)
               ->assign('Artikelliste', $oArtikel_arr)
               ->assign('Herstellerliste', $oHersteller_arr)
               ->assign('Kategorieliste', $oKategorie_arr)
               ->assign('Kampagne', $oKampagne);

    $cTyp = 'VL';
    $nKey = isset($oNewsletter->kNewsletterVorlage)
        ? $oNewsletter->kNewsletterVorlage
        : null;
    if ($oNewsletter->kNewsletter > 0) {
        $cTyp = 'NL';
        $nKey = $oNewsletter->kNewsletter;
    }

    return $mailSmarty->fetch('db:' . $cTyp . '_' . $nKey . '_html');
}

/**
 * @param array $cPost_arr
 * @return array|null|stdClass
 */
function speicherVorlage($cPost_arr)
{
    $oNewsletterVorlage = null;
    $cPlausiValue_arr   = pruefeVorlage(
        $cPost_arr['cName'],
        $cPost_arr['kKundengruppe'],
        $cPost_arr['cBetreff'],
        $cPost_arr['cArt'],
        $cPost_arr['cHtml'],
        $cPost_arr['cText']
    );

    if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) === 0) {
        $GLOBALS['step'] = 'uebersicht';
        // Zeit bauen
        $dTag    = $cPost_arr['dTag'];
        $dMonat  = $cPost_arr['dMonat'];
        $dJahr   = $cPost_arr['dJahr'];
        $dStunde = $cPost_arr['dStunde'];
        $dMinute = $cPost_arr['dMinute'];

        $dZeitDB = $dJahr . '-' . $dMonat . '-' . $dTag . ' ' . $dStunde . ':' . $dMinute . ':00';
        $oZeit   = baueZeitAusDB($dZeitDB);

        $kNewsletterVorlage = isset($cPost_arr['kNewsletterVorlage'])
            ? (int)$cPost_arr['kNewsletterVorlage']
            : null;
        $kKampagne          = (int)$cPost_arr['kKampagne'];
        //$cArtNr_arr = $cPost_arr['cArtNr'];
        $cArtikel          = $cPost_arr['cArtikel'];
        $cHersteller       = $cPost_arr['cHersteller'];
        $cKategorie        = $cPost_arr['cKategorie'];
        $kKundengruppe_arr = $cPost_arr['kKundengruppe'];
        // Kundengruppen in einen String bauen
        $cKundengruppe = ';' . implode(';', $kKundengruppe_arr) . ';';
        $cArtikel      = ';' . $cArtikel . ';';
        $cHersteller   = ';' . $cHersteller . ';';
        $cKategorie    = ';' . $cKategorie . ';';

        $oNewsletterVorlage                     = new stdClass();
        if ($kNewsletterVorlage !== null) {
            $oNewsletterVorlage->kNewsletterVorlage = $kNewsletterVorlage;
        }
        $oNewsletterVorlage->kSprache           = (int)$_SESSION['kSprache'];
        $oNewsletterVorlage->kKampagne          = $kKampagne;
        $oNewsletterVorlage->cName              = $cPost_arr['cName'];
        $oNewsletterVorlage->cBetreff           = $cPost_arr['cBetreff'];
        $oNewsletterVorlage->cArt               = $cPost_arr['cArt'];
        $oNewsletterVorlage->cArtikel           = $cArtikel;
        $oNewsletterVorlage->cHersteller        = $cHersteller;
        $oNewsletterVorlage->cKategorie         = $cKategorie;
        $oNewsletterVorlage->cKundengruppe      = $cKundengruppe;
        $oNewsletterVorlage->cInhaltHTML        = $cPost_arr['cHtml'];
        $oNewsletterVorlage->cInhaltText        = $cPost_arr['cText'];

        $dt                             = new DateTime($oZeit->dZeit);
        $now                            = new DateTime();
        $oNewsletterVorlage->dStartZeit = ($dt > $now)
            ? $dt->format('Y-m-d H:i:s')
            : $now->format('Y-m-d H:i:s');
        if (isset($cPost_arr['kNewsletterVorlage']) && (int)$cPost_arr['kNewsletterVorlage'] > 0) {
            $_upd                = new stdClass();
            $_upd->cName         = $oNewsletterVorlage->cName;
            $_upd->kKampagne     = $oNewsletterVorlage->kKampagne;
            $_upd->cBetreff      = $oNewsletterVorlage->cBetreff;
            $_upd->cArt          = $oNewsletterVorlage->cArt;
            $_upd->cArtikel      = $oNewsletterVorlage->cArtikel;
            $_upd->cHersteller   = $oNewsletterVorlage->cHersteller;
            $_upd->cKategorie    = $oNewsletterVorlage->cKategorie;
            $_upd->cKundengruppe = $oNewsletterVorlage->cKundengruppe;
            $_upd->cInhaltHTML   = $oNewsletterVorlage->cInhaltHTML;
            $_upd->cInhaltText   = $oNewsletterVorlage->cInhaltText;
            $_upd->dStartZeit    = $oNewsletterVorlage->dStartZeit;
            Shop::DB()->update('tnewslettervorlage', 'kNewsletterVorlage', $kNewsletterVorlage, $_upd);
            $GLOBALS['cHinweis'] .= 'Die Vorlage "' . $oNewsletterVorlage->cName .
                '" wurde erfolgreich editiert.<br />';
        } else {
            $kNewsletterVorlage = Shop::DB()->insert('tnewslettervorlage', $oNewsletterVorlage);
            $GLOBALS['cHinweis'] .= 'Die Vorlage "' . $oNewsletterVorlage->cName .
                '" wurde erfolgreich gespeichert.<br />';
        }
        $oNewsletterVorlage->kNewsletterVorlage = $kNewsletterVorlage;

        return $oNewsletterVorlage;
    }

    return $cPlausiValue_arr;
}

/**
 * @param object $oNewslettervorlageStd
 * @param int    $kNewslettervorlageStd
 * @param array  $cPost_arr
 * @param int    $kNewslettervorlage
 * @return array
 */
function speicherVorlageStd($oNewslettervorlageStd, $kNewslettervorlageStd, $cPost_arr, $kNewslettervorlage)
{
    $kNewslettervorlageStd = (int)$kNewslettervorlageStd;
    $cPlausiValue_arr      = [];
    if ($kNewslettervorlageStd > 0) {
        if (!isset($cPost_arr['kKundengruppe'])) {
            $cPost_arr['kKundengruppe'] = null;
        }
        $cPlausiValue_arr = pruefeVorlageStd(
            $cPost_arr['cName'],
            $cPost_arr['kKundengruppe'],
            $cPost_arr['cBetreff'],
            $cPost_arr['cArt']
        );

        if (is_array($cPlausiValue_arr) && count($cPlausiValue_arr) === 0) {
            // Zeit bauen
            $dTag    = $cPost_arr['dTag'];
            $dMonat  = $cPost_arr['dMonat'];
            $dJahr   = $cPost_arr['dJahr'];
            $dStunde = $cPost_arr['dStunde'];
            $dMinute = $cPost_arr['dMinute'];

            $dZeitDB = $dJahr . '-' . $dMonat . '-' . $dTag . ' ' . $dStunde . ':' . $dMinute . ':00';
            $oZeit   = baueZeitAusDB($dZeitDB);

            $cArtikel    = ';' . $cPost_arr['cArtikel'] . ';';
            $cHersteller = ';' . $cPost_arr['cHersteller'] . ';';
            $cKategorie  = ';' . $cPost_arr['cKategorie'] . ';';

            $kKundengruppe_arr = $cPost_arr['kKundengruppe'];
            // Kundengruppen in einen String bauen
            $cKundengruppe = ';' . implode(';', $kKundengruppe_arr) . ';';
            // StdVar vorbereiten
            if (isset($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) &&
                is_array($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) &&
                count($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) > 0) {
                foreach ($oNewslettervorlageStd->oNewslettervorlageStdVar_arr as $i => $nlTplStdVar) {
                    if ($nlTplStdVar->cTyp === 'TEXT') {
                        $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$i]->cInhalt =
                            $cPost_arr['kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar];
                    }
                    if ($nlTplStdVar->cTyp === 'BILD') {
                        $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$i]->cLinkURL = $cPost_arr['cLinkURL'];
                        $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$i]->cAltTag  = $cPost_arr['cAltTag'];
                    }
                }
            }

            $oNewsletterVorlage                        = new stdClass();
            $oNewsletterVorlage->kNewslettervorlageStd = $kNewslettervorlageStd;
            $oNewsletterVorlage->kKampagne             = (int)$cPost_arr['kKampagne'];
            $oNewsletterVorlage->kSprache              = $_SESSION['kSprache'];
            $oNewsletterVorlage->cName                 = $cPost_arr['cName'];
            $oNewsletterVorlage->cBetreff              = $cPost_arr['cBetreff'];
            $oNewsletterVorlage->cArt                  = $cPost_arr['cArt'];
            $oNewsletterVorlage->cArtikel              = $cArtikel;
            $oNewsletterVorlage->cHersteller           = $cHersteller;
            $oNewsletterVorlage->cKategorie            = $cKategorie;
            $oNewsletterVorlage->cKundengruppe         = $cKundengruppe;
            $oNewsletterVorlage->cInhaltHTML           = mappeVorlageStdVar(
                $oNewslettervorlageStd->cInhaltHTML,
                $oNewslettervorlageStd->oNewslettervorlageStdVar_arr
            );
            $oNewsletterVorlage->cInhaltText           = mappeVorlageStdVar(
                $oNewslettervorlageStd->cInhaltText,
                $oNewslettervorlageStd->oNewslettervorlageStdVar_arr,
                true
            );
            $dt  = new DateTime($oZeit->dZeit);
            $now = new DateTime();

            $oNewsletterVorlage->dStartZeit = ($dt > $now)
                ? $dt->format('Y-m-d H:i:s')
                : $now->format('Y-m-d H:i:s');

            if ($kNewslettervorlage > 0) {
                $upd                = new stdClass();
                $upd->cName         = $oNewsletterVorlage->cName;
                $upd->cBetreff      = $oNewsletterVorlage->cBetreff;
                $upd->kKampagne     = (int)$oNewsletterVorlage->kKampagne;
                $upd->cArt          = $oNewsletterVorlage->cArt;
                $upd->cArtikel      = $oNewsletterVorlage->cArtikel;
                $upd->cHersteller   = $oNewsletterVorlage->cHersteller;
                $upd->cKategorie    = $oNewsletterVorlage->cKategorie;
                $upd->cKundengruppe = $oNewsletterVorlage->cKundengruppe;
                $upd->cInhaltHTML   = $oNewsletterVorlage->cInhaltHTML;
                $upd->cInhaltText   = $oNewsletterVorlage->cInhaltText;
                $upd->dStartZeit    = $oNewsletterVorlage->dStartZeit;
                Shop::DB()->update('tnewslettervorlage', 'kNewsletterVorlage', (int)$kNewslettervorlage, $upd);
            } else {
                $kNewslettervorlage = Shop::DB()->insert('tnewslettervorlage', $oNewsletterVorlage);
            }
            // NewslettervorlageStdVarInhalt
            if ($kNewslettervorlage > 0 && isset($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) &&
                is_array($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) &&
                count($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) > 0
            ) {
                Shop::DB()->delete('tnewslettervorlagestdvarinhalt', 'kNewslettervorlage', $kNewslettervorlage);
                foreach ($oNewslettervorlageStd->oNewslettervorlageStdVar_arr as $i => $nlTplStdVar) {
                    $bBildVorhanden = false;
                    if ($nlTplStdVar->cTyp === 'BILD') {
                        // Bilder hochladen
                        $cUploadVerzeichnis = PFAD_ROOT . PFAD_BILDER . PFAD_NEWSLETTERBILDER;

                        if (!is_dir($cUploadVerzeichnis . $kNewslettervorlage)) {
                            mkdir($cUploadVerzeichnis . $kNewslettervorlage);
                        }

                        if (isset($_FILES['kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar]['name']) &&
                            strlen($_FILES['kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar]['name']) > 0
                        ) {
                            $cUploadDatei = $cUploadVerzeichnis . $kNewslettervorlage .
                                '/kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar .
                                mappeFileTyp($_FILES['kNewslettervorlageStdVar_' .
                                    $nlTplStdVar->kNewslettervorlageStdVar]['type']);
                            if (file_exists($cUploadDatei)) {
                                unlink($cUploadDatei);
                            }
                            move_uploaded_file(
                                $_FILES['kNewslettervorlageStdVar_' .
                                    $nlTplStdVar->kNewslettervorlageStdVar]['tmp_name'],
                                $cUploadDatei
                            );
                            // Link URL
                            if (isset($cPost_arr['cLinkURL']) && strlen($cPost_arr['cLinkURL']) > 0) {
                                $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$i]->cLinkURL =
                                    $cPost_arr['cLinkURL'];
                            }
                            // Alt Tag
                            if (isset($cPost_arr['cAltTag']) && strlen($cPost_arr['cAltTag']) > 0) {
                                $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$i]->cAltTag =
                                    $cPost_arr['cAltTag'];
                            }

                            $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$i]->cInhalt =
                                Shop::getURL() . '/' . PFAD_BILDER . PFAD_NEWSLETTERBILDER . $kNewslettervorlage .
                                '/kNewslettervorlageStdVar_' . $nlTplStdVar->kNewslettervorlageStdVar .
                                mappeFileTyp($_FILES['kNewslettervorlageStdVar_' .
                                    $nlTplStdVar->kNewslettervorlageStdVar]['type']
                                );
                            $bBildVorhanden = true;
                        }
                    }

                    $nlTplContent                           = new stdClass();
                    $nlTplContent->kNewslettervorlageStdVar = $nlTplStdVar->kNewslettervorlageStdVar;
                    $nlTplContent->kNewslettervorlage       = $kNewslettervorlage;
                    if ($nlTplStdVar->cTyp === 'TEXT') {
                        $nlTplContent->cInhalt = $nlTplStdVar->cInhalt;
                    } elseif ($nlTplStdVar->cTyp === 'BILD') {
                        if ($bBildVorhanden) {
                            $nlTplContent->cInhalt = $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$i]->cInhalt;
                            // Link URL
                            if (isset($cPost_arr['cLinkURL']) && strlen($cPost_arr['cLinkURL']) > 0) {
                                $nlTplContent->cLinkURL = $cPost_arr['cLinkURL'];
                            }
                            // Alt Tag
                            if (isset($cPost_arr['cAltTag']) && strlen($cPost_arr['cAltTag']) > 0) {
                                $nlTplContent->cAltTag = $cPost_arr['cAltTag'];
                            }
                            $upd = new stdClass();
                            $upd->cInhaltHTML = mappeVorlageStdVar(
                                $oNewslettervorlageStd->cInhaltHTML,
                                $oNewslettervorlageStd->oNewslettervorlageStdVar_arr
                            );
                            $upd->cInhaltText = mappeVorlageStdVar(
                                $oNewslettervorlageStd->cInhaltText,
                                $oNewslettervorlageStd->oNewslettervorlageStdVar_arr,
                                true
                            );
                            Shop::DB()->update('tnewslettervorlage', 'kNewsletterVorlage', $kNewslettervorlage, $upd);
                        } else {
                            $nlTplContent->cInhalt = $nlTplStdVar->cInhalt;
                            // Link URL
                            if (isset($cPost_arr['cLinkURL']) && strlen($cPost_arr['cLinkURL']) > 0) {
                                $nlTplContent->cLinkURL = $cPost_arr['cLinkURL'];
                            }
                            // Alt Tag
                            if (isset($cPost_arr['cAltTag']) && strlen($cPost_arr['cAltTag']) > 0) {
                                $nlTplContent->cAltTag = $cPost_arr['cAltTag'];
                            }
                        }
                    }
                    Shop::DB()->insert('tnewslettervorlagestdvarinhalt', $nlTplContent);
                }
            }
        }
    }

    return $cPlausiValue_arr; // Keine kNewslettervorlageStd uebergeben
}

/**
 * @param string $cTyp
 * @return string
 */
function mappeFileTyp($cTyp)
{
    switch ($cTyp) {
        case 'image/jpeg':
            return '.jpg';
            break;
        case 'image/pjpeg':
            return '.jpg';
            break;
        case 'image/gif':
            return '.gif';
            break;
        case 'image/png':
            return '.png';
            break;
        case 'image/bmp':
            return '.bmp';
            break;
        default:
            return '.jpg';
            break;
    }
}

/**
 * @param string $cText
 * @return mixed
 */
function br2nl($cText)
{
    return str_replace(['<br>', '<br />', '<br/>'], "\n", $cText);
}

/**
 * @param string $cText
 * @param array  $oNewsletterStdVar_arr
 * @param bool   $bNoHTML
 * @return mixed|string
 */
function mappeVorlageStdVar($cText, $oNewsletterStdVar_arr, $bNoHTML = false)
{
    if (is_array($oNewsletterStdVar_arr) && count($oNewsletterStdVar_arr) > 0) {
        foreach ($oNewsletterStdVar_arr as $oNewsletterStdVar) {
            if ($oNewsletterStdVar->cTyp === 'TEXT') {
                if ($bNoHTML) {
                    $cText = strip_tags(br2nl(str_replace(
                        '$#' . $oNewsletterStdVar->cName . '#$', 
                        $oNewsletterStdVar->cInhalt, 
                        $cText
                        )
                    ));
                } else {
                    $cText = str_replace('$#' . $oNewsletterStdVar->cName . '#$', $oNewsletterStdVar->cInhalt, $cText);
                }
            } elseif ($oNewsletterStdVar->cTyp === 'BILD') {
                // Bildervorlagen auf die URL SHOP umbiegen
                $oNewsletterStdVar->cInhalt = str_replace(
                    NEWSLETTER_STD_VORLAGE_URLSHOP, 
                    Shop::getURL() . '/', 
                    $oNewsletterStdVar->cInhalt
                );
                if ($bNoHTML) {
                    $cText = strip_tags(br2nl(
                        str_replace(
                            '$#' . $oNewsletterStdVar->cName . '#$', 
                            $oNewsletterStdVar->cInhalt, 
                            $cText
                        )
                    ));
                } else {
                    $cAltTag = '';
                    if (isset($oNewsletterStdVar->cAltTag) && strlen($oNewsletterStdVar->cAltTag) > 0) {
                        $cAltTag = $oNewsletterStdVar->cAltTag;
                    }

                    if (isset($oNewsletterStdVar->cLinkURL) && strlen($oNewsletterStdVar->cLinkURL) > 0) {
                        $cText = str_replace(
                            '$#' . $oNewsletterStdVar->cName . '#$', '<a href="' . 
                            $oNewsletterStdVar->cLinkURL . 
                            '"><img src="' .
                            $oNewsletterStdVar->cInhalt . '" alt="' . $cAltTag . '" title="' . 
                            $cAltTag . 
                            '" /></a>', $cText
                        );
                    } else {
                        $cText = str_replace(
                            '$#' . $oNewsletterStdVar->cName . '#$', '<img src="' . 
                            $oNewsletterStdVar->cInhalt . 
                            '" alt="' .
                            $cAltTag . '" title="' . $cAltTag . '" />', $cText
                        );
                    }
                }
            }
        }
    }

    return $cText;
}

/**
 * @param string $cName
 * @param array  $kKundengruppe_arr
 * @param string $cBetreff
 * @param string $cArt
 * @return array
 */
function pruefeVorlageStd($cName, $kKundengruppe_arr, $cBetreff, $cArt)
{
    $cPlausiValue_arr = [];
    // Vorlagennamen pruefen
    if (empty($cName)) {
        $cPlausiValue_arr['cName'] = 1;
    }
    // Kundengruppen pruefen
    if (!is_array($kKundengruppe_arr) || count($kKundengruppe_arr) === 0) {
        $cPlausiValue_arr['kKundengruppe_arr'] = 1;
    }
    // Betreff pruefen
    if (empty($cBetreff)) {
        $cPlausiValue_arr['cBetreff'] = 1;
    }
    // Art pruefen
    if (empty($cArt)) {
        $cPlausiValue_arr['cArt'] = 1;
    }

    return $cPlausiValue_arr;
}

/**
 * @param string $cName
 * @param array  $kKundengruppe_arr
 * @param string $cBetreff
 * @param string $cArt
 * @param string $cHtml
 * @param string $cText
 * @return array
 */
function pruefeVorlage($cName, $kKundengruppe_arr, $cBetreff, $cArt, $cHtml, $cText)
{
    $cPlausiValue_arr = [];
    // Vorlagennamen pruefen
    if (empty($cName)) {
        $cPlausiValue_arr['cName'] = 1;
    }
    // Kundengruppen pruefen
    if (!is_array($kKundengruppe_arr) || count($kKundengruppe_arr) === 0) {
        $cPlausiValue_arr['kKundengruppe_arr'] = 1;
    }
    // Betreff pruefen
    if (empty($cBetreff)) {
        $cPlausiValue_arr['cBetreff'] = 1;
    }
    // Art pruefen
    if (empty($cArt)) {
        $cPlausiValue_arr['cArt'] = 1;
    }
    // HTML pruefen
    if (empty($cHtml)) {
        $cPlausiValue_arr['cHtml'] = 1;
    }
    // Text pruefen
    if (empty($cText)) {
        $cPlausiValue_arr['cText'] = 1;
    }

    return $cPlausiValue_arr;
}

/**
 * Baut eine Vorlage zusammen
 * Falls kNewsletterVorlage angegeben wurde und kNewsletterVorlageStd = 0 ist
 * wurde eine Vorlage editiert, die von einer Std Vorlage stammt.
 *
 * @param int $kNewsletterVorlageStd
 * @param int $kNewsletterVorlage
 * @return null
 */
function holeNewslettervorlageStd($kNewsletterVorlageStd, $kNewsletterVorlage = 0)
{
    $kNewsletterVorlageStd = (int)$kNewsletterVorlageStd;
    $kNewsletterVorlage    = (int)$kNewsletterVorlage;
    if ($kNewsletterVorlageStd > 0 || $kNewsletterVorlage > 0) {
        $oNewslettervorlage = new stdClass();
        if ($kNewsletterVorlage > 0) {
            $oNewslettervorlage = Shop::DB()->select('tnewslettervorlage', 'kNewsletterVorlage', $kNewsletterVorlage);

            if (isset($oNewslettervorlage->kNewslettervorlageStd) && $oNewslettervorlage->kNewslettervorlageStd > 0) {
                $kNewsletterVorlageStd = $oNewslettervorlage->kNewslettervorlageStd;
            }
        }

        $oNewslettervorlageStd = Shop::DB()->select('tnewslettervorlagestd', 'kNewslettervorlageStd', $kNewsletterVorlageStd);
        if ($oNewslettervorlageStd->kNewslettervorlageStd > 0) {
            if (isset($oNewslettervorlage->kNewslettervorlageStd) && $oNewslettervorlage->kNewslettervorlageStd > 0) {
                $oNewslettervorlageStd->kNewsletterVorlage = $oNewslettervorlage->kNewsletterVorlage;
                $oNewslettervorlageStd->kKampagne          = $oNewslettervorlage->kKampagne;
                $oNewslettervorlageStd->cName              = $oNewslettervorlage->cName;
                $oNewslettervorlageStd->cBetreff           = $oNewslettervorlage->cBetreff;
                $oNewslettervorlageStd->cArt               = $oNewslettervorlage->cArt;
                $oNewslettervorlageStd->cArtikel           = substr(
                    substr($oNewslettervorlage->cArtikel, 1), 
                    0, 
                    strlen(substr($oNewslettervorlage->cArtikel, 1)) - 1
                );
                $oNewslettervorlageStd->cHersteller        = substr(
                    substr($oNewslettervorlage->cHersteller, 1), 
                    0, 
                    strlen(substr($oNewslettervorlage->cHersteller, 1)) - 1
                );
                $oNewslettervorlageStd->cKategorie         = substr(
                    substr($oNewslettervorlage->cKategorie, 1), 
                    0, 
                    strlen(substr($oNewslettervorlage->cKategorie, 1)) - 1
                );
                $oNewslettervorlageStd->cKundengruppe      = $oNewslettervorlage->cKundengruppe;
                $oNewslettervorlageStd->dStartZeit         = $oNewslettervorlage->dStartZeit;
            }

            $oNewslettervorlageStd->oNewslettervorlageStdVar_arr = Shop::DB()->selectAll(
                'tnewslettervorlagestdvar', 
                'kNewslettervorlageStd', 
                $kNewsletterVorlageStd
            );

            if (is_array($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) && 
                count($oNewslettervorlageStd->oNewslettervorlageStdVar_arr) > 0) {
                foreach ($oNewslettervorlageStd->oNewslettervorlageStdVar_arr as $j => $nlTplStdVar) {
                    $nlTplContent = new stdClass();
                    if (isset($nlTplStdVar->kNewslettervorlageStdVar) && $nlTplStdVar->kNewslettervorlageStdVar > 0) {
                        $cSQL = " AND kNewslettervorlage IS NULL";
                        if (isset($kNewsletterVorlage) && (int)$kNewsletterVorlage > 0) {
                            $cSQL = " AND kNewslettervorlage = " . $kNewsletterVorlage;
                        }

                        $nlTplContent = Shop::DB()->query(
                            "SELECT *
                                FROM tnewslettervorlagestdvarinhalt
                                WHERE kNewslettervorlageStdVar = " . (int)$nlTplStdVar->kNewslettervorlageStdVar .
                                $cSQL, 1
                        );
                    }

                    if (isset($nlTplContent->cInhalt) && strlen($nlTplContent->cInhalt) > 0) {
                        $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$j]->cInhalt = str_replace(
                            NEWSLETTER_STD_VORLAGE_URLSHOP,
                            Shop::getURL() . '/',
                            $nlTplContent->cInhalt
                        );
                        if (isset($nlTplContent->cLinkURL) && strlen($nlTplContent->cLinkURL) > 0) {
                            $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$j]->cLinkURL = $nlTplContent->cLinkURL;
                        }
                        if (isset($nlTplContent->cAltTag) && strlen($nlTplContent->cAltTag) > 0) {
                            $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$j]->cAltTag = $nlTplContent->cAltTag;
                        }
                    } else {
                        $oNewslettervorlageStd->oNewslettervorlageStdVar_arr[$j]->cInhalt = '';
                    }
                }
            }
        }

        return $oNewslettervorlageStd;
    }

    return null;
}

/**
 * @param string $cArtikel
 * @return stdClass
 */
function explodecArtikel($cArtikel)
{
    // cArtikel exploden
    $cArtikelTMP_arr                = explode(';', $cArtikel);
    $oExplodedArtikel               = new stdClass();
    $oExplodedArtikel->kArtikel_arr = [];
    $oExplodedArtikel->cArtNr_arr   = [];
    if (is_array($cArtikelTMP_arr) && count($cArtikelTMP_arr) > 0) {
        foreach ($cArtikelTMP_arr as $cArtikelTMP) {
            if ($cArtikelTMP) {
                $oExplodedArtikel->kArtikel_arr[] = $cArtikelTMP;
            }
        }
        // hole zu den kArtikeln die passende cArtNr
        foreach ($oExplodedArtikel->kArtikel_arr as $kArtikel) {
            $cArtNr = holeArtikelnummer($kArtikel);
            if (strlen($cArtNr) > 0) {
                $oExplodedArtikel->cArtNr_arr[] = $cArtNr;
            }
        }
    }

    return $oExplodedArtikel;
}

/**
 * @param string $cKundengruppe
 * @return array
 */
function explodecKundengruppe($cKundengruppe)
{
    // cKundengruppe exploden
    $cKundengruppeTMP_arr = explode(';', $cKundengruppe);
    $kKundengruppe_arr    = [];
    if (is_array($cKundengruppeTMP_arr) && count($cKundengruppeTMP_arr) > 0) {
        foreach ($cKundengruppeTMP_arr as $cKundengruppeTMP) {
            if (strlen($cKundengruppeTMP) > 0) {
                $kKundengruppe_arr[] = $cKundengruppeTMP;
            }
        }
    }

    return $kKundengruppe_arr;
}

/**
 * @param array $cArtNr_arr
 * @return array
 */
function holeArtikel($cArtNr_arr)
{
    // Artikel holen
    $oArtikel_arr = [];
    if (is_array($cArtNr_arr) && count($cArtNr_arr) > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($cArtNr_arr as $cArtNr) {
            if ($cArtNr !== '') {
                $oArtikel_tmp = Shop::DB()->select('tartikel', 'cArtNr', $cArtNr);
                // Artikel mit cArtNr vorhanden?
                if (isset($oArtikel_tmp->kArtikel) && $oArtikel_tmp->kArtikel > 0) {
                    // Artikelsichtbarkeit pruefen
//                    $oSichtbarkeit_arr = Shop::DB()->query(
//                        "SELECT *
//                            FROM tartikelsichtbarkeit
//                            WHERE kArtikel=" . $oArtikel_tmp->kArtikel, 2
//                    );
                    $nSichtbar = 1;
//                    if (is_array($oSichtbarkeit_arr) && count($oSichtbarkeit_arr) > 0) {
//                        foreach ($oSichtbarkeit_arr as $oSichtbarkeit) {
                            //@todo: $kKundengruppe_arr undefined
//                            if (in_array($oSichtbarkeit->kKundengruppe, $kKundengruppe_arr)) {
//                                $nSichtbar = 0;
//                                break;
//                            }
//                        }
//                    }
                    // Wenn der Artikel fuer diese Kundengruppen sichtbar ist
                    if ($nSichtbar) {
                        $_SESSION['Kundengruppe']->darfPreiseSehen = 1;
                        $oArtikel                                  = new Artikel();
                        $oArtikel->fuelleArtikel($oArtikel_tmp->kArtikel, $defaultOptions);

                        $oArtikel_arr[] = $oArtikel;
                    } else {
                        $GLOBALS['step'] = 'versand_vorbereiten';
                        $GLOBALS['cFehler'] .= 'Fehler, der Artikel ' . $cArtNr .
                            ' ist f&uuml;r einige Kundengruppen nicht sichtbar.<br>';
                    }
                } else {
                    $GLOBALS['step'] = 'versand_vorbereiten';
                    $GLOBALS['cFehler'] .= 'Fehler, der Artikel ' . $cArtNr .
                        ' konnte nicht in der Datenbank gefunden werden.<br>';
                }
            }
        }
    }

    return $oArtikel_arr;
}

/**
 * @param int $kArtikel
 * @return string
 */
function holeArtikelnummer($kArtikel)
{
    $cArtNr   = '';
    $oArtikel = null;

    if ((int)$kArtikel > 0) {
        $oArtikel = Shop::DB()->select('tartikel', 'kArtikel', (int)$kArtikel);
    }

    return isset($oArtikel->cArtNr) ? $oArtikel->cArtNr : $cArtNr;
}

/**
 * @param int $kNewsletter
 * @return stdClass
 */
function getNewsletterEmpfaenger($kNewsletter)
{
    $kNewsletter           = (int)$kNewsletter;
    $oNewsletterEmpfaenger = new stdClass();
    if ($kNewsletter > 0) {
        // Kundengruppen holen um spaeter die maximal Anzahl Empfaenger gefiltert werden kann
        $oNewsletter = Shop::DB()->select('tnewsletter', 'kNewsletter', $kNewsletter);
        // Kundengruppe pruefen und spaeter in den Empfaenger SELECT einbauen
        $cKundengruppenTMP_arr = explode(';', $oNewsletter->cKundengruppe);
        $kKundengruppe_arr     = [];
        $cKundengruppe_arr     = [];
        $cSQL                  = '';
        if (is_array($cKundengruppenTMP_arr) && count($cKundengruppenTMP_arr) > 0) {
            foreach ($cKundengruppenTMP_arr as $cKundengruppe) {
                $kKundengruppe = (int)$cKundengruppe;
                if ($kKundengruppe > 0) {
                    $kKundengruppe_arr[] = $kKundengruppe;
                }
                if (strlen($cKundengruppe) > 0) {
                    $cKundengruppe_arr[] = $cKundengruppe;
                }
            }

            $cSQL = "AND (";
            foreach ($kKundengruppe_arr as $i => $kKundengruppe) {
                if ($i > 0) {
                    $cSQL .= " OR tkunde.kKundengruppe = " . (int)$kKundengruppe;
                } else {
                    $cSQL .= "tkunde.kKundengruppe = " . (int)$kKundengruppe;
                }
            }

            if (in_array('0', $cKundengruppenTMP_arr)) {
                if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
                    $cSQL .= " OR tkunde.kKundengruppe IS NULL";
                } else {
                    $cSQL .= "tkunde.kKundengruppe IS NULL";
                }
            }

            $cSQL .= ")";
        }

        $oNewsletterEmpfaenger = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tnewsletterempfaenger
                LEFT JOIN tsprache 
                    ON tsprache.kSprache = tnewsletterempfaenger.kSprache
                LEFT JOIN tkunde 
                    ON tkunde.kKunde = tnewsletterempfaenger.kKunde
                WHERE tnewsletterempfaenger.kSprache = " . (int)$oNewsletter->kSprache . "
                    AND tnewsletterempfaenger.nAktiv = 1 " . $cSQL, 1
        );

        $oNewsletterEmpfaenger->cKundengruppe_arr = $cKundengruppe_arr;
    }

    return $oNewsletterEmpfaenger;
}

/**
 * @param string $dZeitDB
 * @return stdClass
 */
function baueZeitAusDB($dZeitDB)
{
    $oZeit = new stdClass();

    if (strlen($dZeitDB) > 0) {
        list($dDatum, $dUhrzeit)            = explode(' ', $dZeitDB);
        list($dJahr, $dMonat, $dTag)        = explode('-', $dDatum);
        list($dStunde, $dMinute, $dSekunde) = explode(':', $dUhrzeit);

        $oZeit->dZeit     = $dTag . '.' . $dMonat . '.' . $dJahr . ' ' . $dStunde . ':' . $dMinute;
        $oZeit->cZeit_arr = [$dTag, $dMonat, $dJahr, $dStunde, $dMinute];
    }

    return $oZeit;
}

/**
 * @param stdClass $cAktiveSucheSQL
 * @return int
 */
function holeAbonnentenAnzahl($cAktiveSucheSQL)
{
    $oAbonnentenMaxAnzahl = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewsletterempfaenger
            WHERE kSprache = " . (int)$_SESSION['kSprache'] . $cAktiveSucheSQL->cWHERE, 1
    );

    return isset($oAbonnentenMaxAnzahl->nAnzahl)
        ? (int)$oAbonnentenMaxAnzahl->nAnzahl
        : 0;
}

/**
 * @param string   $cSQL
 * @param stdClass $cAktiveSucheSQL
 * @return mixed
 */
function holeAbonnenten($cSQL, $cAktiveSucheSQL)
{
    return Shop::DB()->query(
        "SELECT tnewsletterempfaenger.*, 
            DATE_FORMAT(tnewsletterempfaenger.dEingetragen, '%d.%m.%Y %H:%i') AS dEingetragen_de,
            DATE_FORMAT(tnewsletterempfaenger.dLetzterNewsletter, '%d.%m.%Y %H:%i') AS dLetzterNewsletter_de, 
            tkunde.kKundengruppe, tkundengruppe.cName
            FROM tnewsletterempfaenger
            LEFT JOIN tkunde 
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            LEFT JOIN tkundengruppe 
                ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
            WHERE tnewsletterempfaenger.kSprache = " . (int)$_SESSION['kSprache'] .
                $cAktiveSucheSQL->cWHERE . "
            ORDER BY tnewsletterempfaenger.dEingetragen DESC" . $cSQL, 2
    );
}

/**
 * @param array $kNewsletterEmpfaenger_arr
 * @return bool
 */
function loescheAbonnenten($kNewsletterEmpfaenger_arr)
{
    if (is_array($kNewsletterEmpfaenger_arr) && count($kNewsletterEmpfaenger_arr) > 0) {
        $cSQL = " IN (";
        foreach ($kNewsletterEmpfaenger_arr as $i => $kNewsletterEmpfaenger) {
            $kNewsletterEmpfaenger = (int)$kNewsletterEmpfaenger;
            if ($i > 0) {
                $cSQL .= ", " . $kNewsletterEmpfaenger;
            } else {
                $cSQL .= $kNewsletterEmpfaenger;
            }
        }
        $cSQL .= ")";

        $oNewsletterEmpfaenger_arr = Shop::DB()->query(
            "SELECT *
                FROM tnewsletterempfaenger
                WHERE kNewsletterEmpfaenger" .
                $cSQL, 2
        );

        if (count($oNewsletterEmpfaenger_arr) > 0) {
            Shop::DB()->query(
                "DELETE FROM tnewsletterempfaenger
                    WHERE kNewsletterEmpfaenger" . $cSQL, 3
            );
            // Protokollieren
            foreach ($oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger) {
                $oNewsletterEmpfaengerHistory               = new stdClass();
                $oNewsletterEmpfaengerHistory->kSprache     = $oNewsletterEmpfaenger->kSprache;
                $oNewsletterEmpfaengerHistory->kKunde       = $oNewsletterEmpfaenger->kKunde;
                $oNewsletterEmpfaengerHistory->cAnrede      = $oNewsletterEmpfaenger->cAnrede;
                $oNewsletterEmpfaengerHistory->cVorname     = $oNewsletterEmpfaenger->cVorname;
                $oNewsletterEmpfaengerHistory->cNachname    = $oNewsletterEmpfaenger->cNachname;
                $oNewsletterEmpfaengerHistory->cEmail       = $oNewsletterEmpfaenger->cEmail;
                $oNewsletterEmpfaengerHistory->cOptCode     = $oNewsletterEmpfaenger->cOptCode;
                $oNewsletterEmpfaengerHistory->cLoeschCode  = $oNewsletterEmpfaenger->cLoeschCode;
                $oNewsletterEmpfaengerHistory->cAktion      = 'Geloescht';
                $oNewsletterEmpfaengerHistory->dEingetragen = $oNewsletterEmpfaenger->dEingetragen;
                $oNewsletterEmpfaengerHistory->dAusgetragen = 'now()';
                $oNewsletterEmpfaengerHistory->dOptCode     = '0000-00-00';

                Shop::DB()->insert('tnewsletterempfaengerhistory', $oNewsletterEmpfaengerHistory);
            }

            return true;
        }
    }

    return false;
}

/**
 * @param array $kNewsletterEmpfaenger_arr
 * @return bool
 */
function aktiviereAbonnenten($kNewsletterEmpfaenger_arr)
{
    if (is_array($kNewsletterEmpfaenger_arr) && count($kNewsletterEmpfaenger_arr) > 0) {
        $cSQL = " IN (";
        foreach ($kNewsletterEmpfaenger_arr as $i => $kNewsletterEmpfaenger) {
            $kNewsletterEmpfaenger = (int)$kNewsletterEmpfaenger;
            if ($i > 0) {
                $cSQL .= ", " . $kNewsletterEmpfaenger;
            } else {
                $cSQL .= $kNewsletterEmpfaenger;
            }
        }
        $cSQL .= ")";

        $oNewsletterEmpfaenger_arr = Shop::DB()->query(
            "SELECT *
                FROM tnewsletterempfaenger
                WHERE kNewsletterEmpfaenger" .
                $cSQL, 2
        );

        if (count($oNewsletterEmpfaenger_arr) > 0) {
            Shop::DB()->query(
                "UPDATE tnewsletterempfaenger
                    SET nAktiv = 1
                    WHERE kNewsletterEmpfaenger" . $cSQL, 3
            );
            // Protokollieren
            foreach ($oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger) {
                $oNewsletterEmpfaengerHistory               = new stdClass();
                $oNewsletterEmpfaengerHistory->kSprache     = $oNewsletterEmpfaenger->kSprache;
                $oNewsletterEmpfaengerHistory->kKunde       = $oNewsletterEmpfaenger->kKunde;
                $oNewsletterEmpfaengerHistory->cAnrede      = $oNewsletterEmpfaenger->cAnrede;
                $oNewsletterEmpfaengerHistory->cVorname     = $oNewsletterEmpfaenger->cVorname;
                $oNewsletterEmpfaengerHistory->cNachname    = $oNewsletterEmpfaenger->cNachname;
                $oNewsletterEmpfaengerHistory->cEmail       = $oNewsletterEmpfaenger->cEmail;
                $oNewsletterEmpfaengerHistory->cOptCode     = $oNewsletterEmpfaenger->cOptCode;
                $oNewsletterEmpfaengerHistory->cLoeschCode  = $oNewsletterEmpfaenger->cLoeschCode;
                $oNewsletterEmpfaengerHistory->cAktion      = 'Aktiviert';
                $oNewsletterEmpfaengerHistory->dEingetragen = $oNewsletterEmpfaenger->dEingetragen;
                $oNewsletterEmpfaengerHistory->dAusgetragen = 'now()';
                $oNewsletterEmpfaengerHistory->dOptCode     = '0000-00-00';

                Shop::DB()->insert('tnewsletterempfaengerhistory', $oNewsletterEmpfaengerHistory);
            }

            return true;
        }
    }

    return false;
}

/**
 * @param array $cPost_arr
 * @return int
 */
function gibAbonnent($cPost_arr)
{
    $cVorname  = strip_tags(Shop::DB()->escape($cPost_arr['cVorname']));
    $cNachname = strip_tags(Shop::DB()->escape($cPost_arr['cNachname']));
    $cEmail    = strip_tags(Shop::DB()->escape($cPost_arr['cEmail']));
    // Etwas muss gesetzt sein um zu suchen
    if (!$cVorname && !$cNachname && !$cEmail) {
        return 1;
    }
    // SQL bauen
    $cSQL = '';
    if (strlen($cVorname) > 0) {
        $cSQL .= "tnewsletterempfaenger.cVorname LIKE '%" . strip_tags(Shop::DB()->realEscape($cVorname)) . "%'";
    }
    if (strlen($cNachname) > 0 && strlen($cVorname) > 0) {
        $cSQL .= " AND tnewsletterempfaenger.cNachname LIKE '%" . strip_tags(Shop::DB()->realEscape($cNachname)) . "%'";
    } elseif (strlen($cNachname) > 0) {
        $cSQL .= "tnewsletterempfaenger.cNachname LIKE '%" . strip_tags(Shop::DB()->realEscape($cNachname)) . "%'";
    }
    if (strlen($cEmail) > 0 && (strlen($cVorname) > 0 || strlen($cNachname) > 0)) {
        $cSQL .= " AND tnewsletterempfaenger.cEmail LIKE '%" . strip_tags(Shop::DB()->realEscape($cEmail)) . "%'";
    } elseif (strlen($cEmail) > 0) {
        $cSQL .= "tnewsletterempfaenger.cEmail LIKE '%" . strip_tags(Shop::DB()->realEscape($cEmail)) . "%'";
    }
    $oAbonnent = Shop::DB()->query(
        "SELECT tnewsletterempfaenger.kNewsletterEmpfaenger, tnewsletterempfaenger.cVorname AS newsVorname, 
            tnewsletterempfaenger.cNachname AS newsNachname, tkunde.cVorname, tkunde.cNachname, 
            tnewsletterempfaenger.cEmail, tnewsletterempfaenger.nAktiv, tkunde.kKundengruppe, tkundengruppe.cName, 
            DATE_FORMAT(tnewsletterempfaenger.dEingetragen, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterempfaenger
            JOIN tkunde 
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            JOIN tkundengruppe 
                ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
            WHERE " . $cSQL . "
            ORDER BY Datum DESC", 1
    );
    if (isset($oAbonnent->kNewsletterEmpfaenger) && $oAbonnent->kNewsletterEmpfaenger > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';
        $oKunde               = new Kunde($oAbonnent->kKunde);
        $oAbonnent->cNachname = $oKunde->cNachname;

        return $oAbonnent;
    }

    return 0;
}

/**
 * @param int $kNewsletterEmpfaenger
 * @return bool
 */
function loescheAbonnent($kNewsletterEmpfaenger)
{
    $kNewsletterEmpfaenger = (int)$kNewsletterEmpfaenger;
    if ($kNewsletterEmpfaenger > 0) {
        Shop::DB()->delete('tnewsletterempfaenger', 'kNewsletterEmpfaenger', $kNewsletterEmpfaenger);

        return true;
    }

    return false;
}

/**
 * @param object $oNewsletterVorlage
 * @return string|bool
 */
function baueNewsletterVorschau(&$oNewsletterVorlage)
{
    $Einstellungen = Shop::getSettings([CONF_NEWSLETTER]);
    $mailSmarty    = bereiteNewsletterVor($Einstellungen);
    // Baue Arrays mit kKeys
    $kArtikel_arr    = gibAHKKeys($oNewsletterVorlage->cArtikel, true);
    $kHersteller_arr = gibAHKKeys($oNewsletterVorlage->cHersteller);
    $kKategorie_arr  = gibAHKKeys($oNewsletterVorlage->cKategorie);
    // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kampagne.php';
    $oKampagne = new Kampagne((int)$oNewsletterVorlage->kKampagne);
    // Baue Arrays von Objekten
    $oArtikel_arr    = gibArtikelObjekte($kArtikel_arr, $oKampagne);
    $oHersteller_arr = gibHerstellerObjekte($kHersteller_arr, $oKampagne);
    $oKategorie_arr  = gibKategorieObjekte($kKategorie_arr, $oKampagne);
    // Kunden Dummy bauen
    $oKunde            = new stdClass();
    $oKunde->cAnrede   = 'm';
    $oKunde->cVorname  = 'Max';
    $oKunde->cNachname = 'Mustermann';
    // Emailempfaenger dummy bauen
    $oEmailempfaenger              = new stdClass();
    $oEmailempfaenger->cEmail      = $Einstellungen['newsletter']['newsletter_emailtest'];
    $oEmailempfaenger->cLoeschCode = '78rev6gj8er6we87gw6er8';
    $oEmailempfaenger->cLoeschURL  = Shop::getURL() .
        '/newsletter.php?lang=ger' . '&lc=' . $oEmailempfaenger->cLoeschCode;

    $mailSmarty->assign('NewsletterEmpfaenger', $oEmailempfaenger)
               ->assign('oNewsletterVorlage', $oNewsletterVorlage)
               ->assign('Kunde', $oKunde)
               ->assign('Artikelliste', $oArtikel_arr)
               ->assign('Herstellerliste', $oHersteller_arr)
               ->assign('Kategorieliste', $oKategorie_arr)
               ->assign('Kampagne', $oKampagne);

    $cTyp = 'VL';
    //fetch
    try {
        $bodyHtml = $mailSmarty->fetch('db:' . $cTyp . '_' . $oNewsletterVorlage->kNewsletterVorlage . '_html');
        $bodyText = $mailSmarty->fetch('db:' . $cTyp . '_' . $oNewsletterVorlage->kNewsletterVorlage . '_text');
    } catch (Exception $e) {
        return $e->getMessage();
    }
    $oNewsletterVorlage->cInhaltHTML = $bodyHtml;
    $oNewsletterVorlage->cInhaltText = $bodyText;

    return true;
}

/**
 * Braucht ein String von Keys oder Nummern und gibt ein Array mit kKeys zurueck
 * Der String muss ';' separiert sein z.b. '1;2;3'
 *
 * @param string $cKey
 * @param bool   $bArtikelnummer
 * @return array
 */
function gibAHKKeys($cKey, $bArtikelnummer = false)
{
    $kKey_arr = [];
    $cKey_arr = explode(';', $cKey);
    if (is_array($cKey_arr) && count($cKey_arr) > 0) {
        foreach ($cKey_arr as $_cKey) {
            if (strlen($_cKey) > 0) {
                if ($bArtikelnummer) {
                    $kKey_arr[] = "'" . $_cKey . "'";
                } else {
                    $kKey_arr[] = (int)$_cKey;
                }
            }
        }
        // Ausnahme: Wurden Artikelnummern uebergebenn?
        // Wenn ja, dann hole fuer die Artikelnummern die entsprechenden kArtikel
        if ($bArtikelnummer && count($kKey_arr) > 0) {
            $kArtikel_arr       = [];
            $oArtikelNummer_arr = Shop::DB()->query(
                "SELECT kArtikel
                    FROM tartikel
                    WHERE cArtNr IN (" . implode(',', $kKey_arr) . ")
                        AND kEigenschaftKombi = 0", 2
            );
            // Existieren Artikel zu den entsprechenden Artikelnummern?
            if (is_array($oArtikelNummer_arr) && count($oArtikelNummer_arr) > 0) {
                foreach ($oArtikelNummer_arr as $oArtikelNummer) {
                    if (isset($oArtikelNummer->kArtikel) && (int)$oArtikelNummer->kArtikel) {
                        $kArtikel_arr[] = $oArtikelNummer->kArtikel;
                    }
                }

                if (count($kArtikel_arr) > 0) {
                    $kKey_arr = $kArtikel_arr;
                }
            }
        }
    }

    return $kKey_arr;
}

/**
 * Benoetigt ein Array von kArtikel und gibt ein Array mit Artikelobjekten zurueck
 *
 * @param array         $kArtikel_arr
 * @param string|object $oKampagne
 * @param int           $kKundengruppe
 * @param int           $kSprache
 * @return array
 */
function gibArtikelObjekte($kArtikel_arr, $oKampagne = '', $kKundengruppe = 0, $kSprache = 0)
{
    $oArtikel_arr = [];
    if (is_array($kArtikel_arr) && count($kArtikel_arr) > 0) {
        $shopURL = Shop::getURL() . '/';
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($kArtikel_arr as $kArtikel) {
            if ((int)$kArtikel > 0) {
                $_SESSION['Kundengruppe']->darfPreiseSehen = 1;
                $oArtikel                                  = new Artikel();
                $oArtikel->fuelleArtikel($kArtikel, $defaultOptions, $kKundengruppe, $kSprache);

                if (!$oArtikel->kArtikel > 0) {
                    Jtllog::writeLog(
                        "Newsletter Cron konnte den Artikel ({$kArtikel}) f&uuml;r Kundengruppe " . 
                        "({$kKundengruppe}) und Sprache ({$kSprache}) nicht laden (Sichtbarkeit?)",
                        JTLLOG_LEVEL_NOTICE, false, 'Newsletter Artikel', $kArtikel
                    );

                    continue;
                }
                $oArtikel->cURL = $shopURL . $oArtikel->cURL;
                // Kampagne URL
                if (isset($oKampagne->cParameter) && strlen($oKampagne->cParameter) > 0) {
                    $cSep = '?';
                    if (strpos($oArtikel->cURL, '.php') !== false) {
                        $cSep = '&';
                    }
                    $oArtikel->cURL = $oArtikel->cURL . $cSep . $oKampagne->cParameter . '=' . $oKampagne->cWert;
                }
                // Artikelbilder absolut machen
                $imageCount = count($oArtikel->Bilder);
                if (is_array($oArtikel->Bilder) && $imageCount > 0) {
                    for ($i = 0; $i < $imageCount; $i++) {
                        $oArtikel->Bilder[$i]->cPfadMini   = $shopURL . $oArtikel->Bilder[$i]->cPfadMini;
                        $oArtikel->Bilder[$i]->cPfadKlein  = $shopURL . $oArtikel->Bilder[$i]->cPfadKlein;
                        $oArtikel->Bilder[$i]->cPfadNormal = $shopURL . $oArtikel->Bilder[$i]->cPfadNormal;
                        $oArtikel->Bilder[$i]->cPfadGross  = $shopURL . $oArtikel->Bilder[$i]->cPfadGross;
                    }
                    $oArtikel->cVorschaubild = $shopURL . $oArtikel->cVorschaubild;
                }
                $oArtikel_arr[] = $oArtikel;
            }
        }
    }

    return $oArtikel_arr;
}

/**
 * Benoetigt ein Array von kHersteller und gibt ein Array mit Herstellerobjekten zurueck
 *
 * @param array      $kHersteller_arr
 * @param int|object $oKampagne
 * @param int|object $kSprache
 * @return array
 */
function gibHerstellerObjekte($kHersteller_arr, $oKampagne = 0, $kSprache = 0)
{
    $oHersteller_arr = [];
    $shopURL         = Shop::getURL() . '/';
    if (is_array($kHersteller_arr) && count($kHersteller_arr) > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Hersteller.php';
        foreach ($kHersteller_arr as $kHersteller) {
            $kHersteller = (int)$kHersteller;
            if ($kHersteller > 0) {
                $oHersteller = new Hersteller($kHersteller);
                if (strpos($oHersteller->cURL, $shopURL) === false) {
                    $oHersteller->cURL = $oHersteller->cURL = $shopURL . $oHersteller->cURL;
                }
                // Kampagne URL
                if (isset($oKampagne->cParameter) && strlen($oKampagne->cParameter) > 0) {
                    $cSep = '?';
                    if (strpos($oHersteller->cURL, '.php') !== false) {
                        $cSep = '&';
                    }
                    $oHersteller->cURL = $oHersteller->cURL . $cSep . $oKampagne->cParameter . '=' . $oKampagne->cWert;
                }
                // Herstellerbilder absolut machen
                $oHersteller->cBildpfadKlein  = $shopURL . $oHersteller->cBildpfadKlein;
                $oHersteller->cBildpfadNormal = $shopURL . $oHersteller->cBildpfadNormal;

                $oHersteller_arr[] = $oHersteller;
            }
        }
    }

    return $oHersteller_arr;
}

/**
 * Benoetigt ein Array von kKategorie und gibt ein Array mit Kategorieobjekten zurueck
 *
 * @param array      $kKategorie_arr
 * @param int|object $oKampagne
 * @return array
 */
function gibKategorieObjekte($kKategorie_arr, $oKampagne = 0)
{
    $oKategorie_arr = [];
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kategorie.php';

    if (is_array($kKategorie_arr) && count($kKategorie_arr) > 0) {
        $shopURL = Shop::getURL() . '/';
        foreach ($kKategorie_arr as $kKategorie) {
            $kKategorie = (int)$kKategorie;
            if ($kKategorie > 0) {
                $oKategorie = new Kategorie($kKategorie);
                if (strpos($oKategorie->cURL, $shopURL) === false) {
                    $oKategorie->cURL = $shopURL . $oKategorie->cURL;
                }
                // Kampagne URL
                if (isset($oKampagne->cParameter) && strlen($oKampagne->cParameter) > 0) {
                    $cSep = '?';
                    if (strpos($oKategorie->cURL, '.php') !== false) {
                        $cSep = '&';
                    }
                    $oKategorie->cURL = $oKategorie->cURL . $cSep . $oKampagne->cParameter . '=' . $oKampagne->cWert;
                }
                $oKategorie_arr[] = $oKategorie;
            }
        }
    }

    return $oKategorie_arr;
}

// OptCode erstellen und ueberpruefen - Werte fuer $dbfeld 'cOptCode','cLoeschCode'
if (!function_exists('create_NewsletterCode')) {
    /**
     * @param string $dbfeld
     * @param string $email
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
}

if (!function_exists('unique_NewsletterCode')) {
    /**
     * @param string $dbfeld
     * @param string $code
     * @return bool
     */
    function unique_NewsletterCode($dbfeld, $code)
    {
        $res = Shop::DB()->select('tnewsletterempfaenger', $dbfeld, $code);

        return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
    }
}
