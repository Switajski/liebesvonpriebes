<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.PlausiCMS.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Link.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'links_inc.php';
/** @global JTLSmarty $smarty */
$hinweis            = '';
$fehler             = '';
$step               = 'uebersicht';
$link               = null;
$cUploadVerzeichnis = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
$clearCache         = false;
$continue           = true;


if (isset($_POST['addlink']) && (int)$_POST['addlink'] > 0) {
    $step = 'neuer Link';
    if (!isset($link)) {
        $link = new stdClass();
    }
    $link->kLinkgruppe = (int)$_POST['addlink'];
}

if (isset($_POST['dellink']) && (int)$_POST['dellink'] > 0 && validateToken()) {
    $kLink       = (int)$_POST['dellink'];
    $kLinkgruppe = (int)$_POST['kLinkgruppe'];
    removeLink($kLink, $kLinkgruppe);
    $hinweis   .= 'Link erfolgreich gel&ouml;scht!';
    $clearCache = true;
    $step       = 'uebersicht';
    $_POST      = [];
}

if (isset($_POST['loesch_linkgruppe']) && (int)$_POST['loesch_linkgruppe'] === 1 && validateToken()) {
    if (isset($_POST['loeschConfirmJaSubmit'])) {
        $step = 'loesch_linkgruppe';
    } else {
        $step  = 'uebersicht';
        $_POST = [];
    }
}

if ((isset($_POST['dellinkgruppe']) && (int)$_POST['dellinkgruppe'] > 0 && validateToken()) || $step === 'loesch_linkgruppe') {
    $step = 'uebersicht';

    $kLinkgruppe = -1;
    if (isset($_POST['dellinkgruppe'])) {
        $kLinkgruppe = (int)$_POST['dellinkgruppe'];
    }
    if ((int)$_POST['kLinkgruppe'] > 0) {
        $kLinkgruppe = (int)$_POST['kLinkgruppe'];
    }

    Shop::DB()->delete('tlinkgruppe', 'kLinkgruppe', $kLinkgruppe);
    Shop::DB()->delete('tlinkgruppesprache', 'kLinkgruppe', $kLinkgruppe);
    $links = Shop::DB()->selectAll('tlink', 'kLinkgruppe', $kLinkgruppe);
    foreach ($links as $link) {
        $oLink = new Link(null, $link, true);
        $oLink->delete(false, $oLink->kLinkgruppe);
    }
    Shop::DB()->delete('tlink', 'kLinkgruppe', $kLinkgruppe);
    $hinweis   .= 'Linkgruppe erfolgreich gel&ouml;scht!';
    $clearCache = true;
    $step       = 'uebersicht';
    $_POST      = [];
}

if (isset($_POST['delconfirmlinkgruppe']) && (int)$_POST['delconfirmlinkgruppe'] > 0 && validateToken()) {
    $step = 'linkgruppe_loeschen_confirm';
    $smarty->assign('oLinkgruppe', holeLinkgruppe((int)$_POST['delconfirmlinkgruppe']));
}

if (isset($_POST['neu_link']) && (int)$_POST['neu_link'] === 1 && validateToken()) {
    $sprachen    = gibAlleSprachen();
    $hasHTML_arr = [];

    foreach ($sprachen as $sprache) {
        $hasHTML_arr[] = 'cContent_' . $sprache->cISO;
    }
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST, $hasHTML_arr, true);
    $oPlausiCMS->doPlausi('lnk');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        if (!isset($link)) {
            $link = new stdClass();
        }
        $link->kLink              = (int)$_POST['kLink'];
        $link->kLinkgruppe        = (int)$_POST['kLinkgruppe'];
        $link->kPlugin            = (int)$_POST['kPlugin'];
        $link->cName              = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $link->nLinkart           = (int)$_POST['nLinkart'];
        $link->cURL               = isset($_POST['cURL']) ? $_POST['cURL'] : null;
        $link->nSort              = !empty($_POST['nSort']) ? $_POST['nSort'] : 0;
        $link->bSSL               = (int)$_POST['bSSL'];
        $link->bIsActive          = 1;
        $link->cSichtbarNachLogin = 'N';
        $link->cNoFollow          = 'N';
        $link->cIdentifier        = $_POST['cIdentifier'];
        $link->bIsFluid           = (isset($_POST['bIsFluid']) && $_POST['bIsFluid'] === '1') ? 1 : 0;
        if (isset($_POST['cKundengruppen']) && is_array($_POST['cKundengruppen']) && count($_POST['cKundengruppen']) > 0) {
            $link->cKundengruppen = implode(';', $_POST['cKundengruppen']) . ';';
        }
        if (is_array($_POST['cKundengruppen']) && in_array('-1', $_POST['cKundengruppen'])) {
            $link->cKundengruppen = 'NULL';
        }
        if (isset($_POST['bIsActive']) && (int)$_POST['bIsActive'] !== 1) {
            $link->bIsActive = 0;
        }
        if (isset($_POST['cSichtbarNachLogin']) && $_POST['cSichtbarNachLogin'] === 'Y') {
            $link->cSichtbarNachLogin = 'Y';
        }
        if (isset($_POST['cNoFollow']) && $_POST['cNoFollow'] === 'Y') {
            $link->cNoFollow = 'Y';
        }
        if ($link->nLinkart > 2 && isset($_POST['nSpezialseite']) && (int)$_POST['nSpezialseite'] > 0) {
            $link->nLinkart = (int)$_POST['nSpezialseite'];
            $link->cURL     = '';
        }
        $clearCache = true;
        $kLink      = 0;
        if ((int)$_POST['kLink'] === 0) {
            //einfuegen
            $kLink    = Shop::DB()->insert('tlink', $link);
            $hinweis .= 'Link wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kLink       = (int)$_POST['kLink'];
            $kLinkgruppe = (int)$_POST['kLinkgruppe'];
            $revision    = new Revision();
            $revision->addRevision('link', (int)$_POST['kLink'], true);
            Shop::DB()->update('tlink', ['kLink', 'kLinkgruppe'], [$kLink, $kLinkgruppe], $link);
            $hinweis .= "Der Link <strong>$link->cName</strong> wurde erfolgreich ge&auml;ndert.";
            $step     = 'uebersicht';
            $continue = (isset($_POST['continue']) && $_POST['continue'] === '1');
        }
        // Bilder hochladen
        if (!is_dir($cUploadVerzeichnis . $kLink)) {
            mkdir($cUploadVerzeichnis . $kLink);
        }

        if (is_array($_FILES['Bilder']['name']) && count($_FILES['Bilder']['name']) > 0) {
            $nLetztesBild = gibLetzteBildNummer($kLink);
            $nZaehler     = 0;
            if ($nLetztesBild > 0) {
                $nZaehler = $nLetztesBild;
            }
            $imageCount = (count($_FILES['Bilder']['name']) + $nZaehler);
            for ($i = $nZaehler; $i < $imageCount; $i++) {
                if ($_FILES['Bilder']['size'][$i - $nZaehler] <= 2097152) {
                    $cUploadDatei = $cUploadVerzeichnis . $kLink . '/Bild' . ($i + 1) . '.' .
                        substr(
                            $_FILES['Bilder']['type'][$i - $nZaehler],
                            strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/') + 1,
                            strlen($_FILES['Bilder']['type'][$i - $nZaehler] - strpos($_FILES['Bilder']['type'][$i - $nZaehler], '/')) + 1
                        );
                    move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $cUploadDatei);
                }
            }
        }

        if (!isset($linkSprache)) {
            $linkSprache = new stdClass();
        }
        $linkSprache->kLink = $kLink;
        foreach ($sprachen as $sprache) {
            $linkSprache->cISOSprache = $sprache->cISO;
            $linkSprache->cName       = $link->cName;
            $linkSprache->cTitle      = '';
            $linkSprache->cContent    = '';
            if (!empty($_POST['cName_' . $sprache->cISO])) {
                $linkSprache->cName = htmlspecialchars($_POST['cName_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            }
            if (!empty($_POST['cTitle_' . $sprache->cISO])) {
                $linkSprache->cTitle = htmlspecialchars($_POST['cTitle_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            }
            if (!empty($_POST['cContent_' . $sprache->cISO])) {
                $linkSprache->cContent = parseText($_POST['cContent_' . $sprache->cISO], $kLink);
            }
            $linkSprache->cSeo = $linkSprache->cName;
            if (!empty($_POST['cSeo_' . $sprache->cISO])) {
                $linkSprache->cSeo = $_POST['cSeo_' . $sprache->cISO];
            }
            $linkSprache->cMetaTitle = $linkSprache->cTitle;
            if (isset($_POST['cMetaTitle_' . $sprache->cISO])) {
                $linkSprache->cMetaTitle = htmlspecialchars($_POST['cMetaTitle_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            }
            $linkSprache->cMetaKeywords    = htmlspecialchars($_POST['cMetaKeywords_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $linkSprache->cMetaDescription = htmlspecialchars($_POST['cMetaDescription_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            Shop::DB()->delete('tlinksprache', ['kLink', 'cISOSprache'], [$kLink, $sprache->cISO]);
            $linkSprache->cSeo = getSeo($linkSprache->cSeo);
            Shop::DB()->insert('tlinksprache', $linkSprache);
            $oSpracheTMP = Shop::DB()->select('tsprache', 'cISO ', $linkSprache->cISOSprache);
            if (isset($oSpracheTMP->kSprache) && $oSpracheTMP->kSprache > 0) {
                Shop::DB()->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', (int)$linkSprache->kLink, (int)$oSpracheTMP->kSprache]
                );
                $oSeo           = new stdClass();
                $oSeo->cSeo     = checkSeo($linkSprache->cSeo);
                $oSeo->kKey     = $linkSprache->kLink;
                $oSeo->cKey     = 'kLink';
                $oSeo->kSprache = $oSpracheTMP->kSprache;
                Shop::DB()->insert('tseo', $oSeo);
            }
        }
    } else {
        $step = 'neuer Link';
        if (!isset($link)) {
            $link = new stdClass();
        }
        $link->kLinkgruppe = (int)$_POST['kLinkgruppe'];
        $fehler            = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
} elseif (((isset($_POST['neuelinkgruppe']) && (int)$_POST['neuelinkgruppe'] === 1) ||
        (isset($_POST['kLinkgruppe']) && (int)$_POST['kLinkgruppe'] > 0)) && validateToken()) {
    $step = 'neue Linkgruppe';
    if (isset($_POST['kLinkgruppe']) && (int)$_POST['kLinkgruppe'] > 0) {
        $linkgruppe = Shop::DB()->select('tlinkgruppe', 'kLinkgruppe', (int)$_POST['kLinkgruppe']);
        $smarty->assign('Linkgruppe', $linkgruppe)
               ->assign('Linkgruppenname', getLinkgruppeNames($linkgruppe->kLinkgruppe));
    }
}

if ($continue && ((isset($_POST['kLink']) && (int)$_POST['kLink'] > 0) ||
        (isset($_GET['kLink']) && (int)$_GET['kLink'] && isset($_GET['delpic']))) && validateToken()) {
    $step = 'neuer Link';
    $link = Shop::DB()->select('tlink', 'kLink', verifyGPCDataInteger('kLink'));
    $smarty->assign('Link', $link)
           ->assign('Linkname', getLinkVar($link->kLink, 'cName'))
           ->assign('Linkseo', getLinkVar($link->kLink, 'cSeo'))
           ->assign('Linktitle', getLinkVar($link->kLink, 'cTitle'))
           ->assign('Linkcontent', getLinkVar($link->kLink, 'cContent'))
           ->assign('Linkmetatitle', getLinkVar($link->kLink, 'cMetaTitle'))
           ->assign('Linkmetakeys', getLinkVar($link->kLink, 'cMetaKeywords'))
           ->assign('Linkmetadesc', getLinkVar($link->kLink, 'cMetaDescription'));
    // Bild loeschen?
    if (verifyGPCDataInteger('delpic') === 1) {
        @unlink($cUploadVerzeichnis . $link->kLink . '/' . verifyGPDataString('cName'));
    }
    // Hohle Bilder
    $cDatei_arr = [];
    if (is_dir($cUploadVerzeichnis . $link->kLink)) {
        $DirHandle = opendir($cUploadVerzeichnis . $link->kLink);
        $shopURL   = Shop::getURL() . '/';
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $nImageGroesse_arr = calcRatio(PFAD_ROOT . '/' . PFAD_BILDER . PFAD_LINKBILDER . $link->kLink . '/' . $Datei, 160, 120);
                $oDatei            = new stdClass();
                $oDatei->cName     = substr($Datei, 0, strpos($Datei, '.'));
                $oDatei->cNameFull = $Datei;
                $oDatei->cURL      = '<img class="link_image" src="' . $shopURL . PFAD_BILDER . PFAD_LINKBILDER . $link->kLink . '/' . $Datei . '" />';
                $oDatei->nBild     = (int)substr(str_replace('Bild', '', $Datei), 0, strpos(str_replace('Bild', '', $Datei), '.'));
                $cDatei_arr[]      = $oDatei;
            }
        }
        usort($cDatei_arr, 'cmp_obj');
        $smarty->assign('cDatei_arr', $cDatei_arr);
    }
}

if (isset($_POST['neu_linkgruppe']) && (int)$_POST['neu_linkgruppe'] === 1 && validateToken()) {
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST);
    $oPlausiCMS->doPlausi('grp');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        if (!isset($linkgruppe)) {
            $linkgruppe = new stdClass();
        }
        $linkgruppe->kLinkgruppe   = (int)$_POST['kLinkgruppe'];
        $linkgruppe->cName         = htmlspecialchars($_POST['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $linkgruppe->cTemplatename = htmlspecialchars($_POST['cTemplatename'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);

        $kLinkgruppe = 0;
        if ((int)$_POST['kLinkgruppe'] === 0) {
            //einf?gen
            $kLinkgruppe = Shop::DB()->insert('tlinkgruppe', $linkgruppe);
            $hinweis    .= 'Linkgruppe wurde erfolgreich hinzugef&uuml;gt.';
        } else {
            //updaten
            $kLinkgruppe = (int)$_POST['kLinkgruppe'];
            Shop::DB()->update('tlinkgruppe', 'kLinkgruppe', $kLinkgruppe, $linkgruppe);
            $hinweis .= "Die Linkgruppe <strong>$linkgruppe->cName</strong> wurde erfolgreich ge&auml;ndert.";
            $step     = 'uebersicht';
        }
        $clearCache = true;
        $sprachen   = gibAlleSprachen();
        if (!isset($linkgruppeSprache)) {
            $linkgruppeSprache = new stdClass();
        }
        $linkgruppeSprache->kLinkgruppe = $kLinkgruppe;
        foreach ($sprachen as $sprache) {
            $linkgruppeSprache->cISOSprache = $sprache->cISO;
            $linkgruppeSprache->cName       = $linkgruppe->cName;
            if ($_POST['cName_' . $sprache->cISO]) {
                $linkgruppeSprache->cName = htmlspecialchars($_POST['cName_' . $sprache->cISO], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            }

            Shop::DB()->delete('tlinkgruppesprache', ['kLinkgruppe', 'cISOSprache'], [$kLinkgruppe, $sprache->cISO]);
            Shop::DB()->insert('tlinkgruppesprache', $linkgruppeSprache);
        }
    } else {
        $step   = 'neue Linkgruppe';
        $fehler = 'Fehler: Bitte f&uuml;llen Sie alle Pflichtangaben aus!';
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
}
// Verschiebt einen Link in eine andere Linkgruppe
if (isset($_POST['aender_linkgruppe']) && (int)$_POST['aender_linkgruppe'] === 1 && validateToken()) {
    if ((int)$_POST['kLink'] > 0 && (int)$_POST['kLinkgruppe'] > 0 && (int)$_POST['kLinkgruppeAlt'] > 0) {
        $oLink = new Link();
        $oLink->load((int)$_POST['kLink'], null, true, (int)$_POST['kLinkgruppeAlt']);
        if ($oLink->getLink() > 0) {
            $oLinkgruppe = Shop::DB()->select('tlinkgruppe', 'kLinkgruppe', (int)$_POST['kLinkgruppe']);
            if (isset($oLinkgruppe->kLinkgruppe) && $oLinkgruppe->kLinkgruppe > 0) {
                $exists = Shop::DB()->select('tlink', ['kLink', 'kLinkgruppe'],[(int)$oLink->kLink,  (int)$_POST['kLinkgruppe']]);
                if (empty($exists)) {
                    $oLink->setLinkgruppe((int)$_POST['kLinkgruppe'])
                        ->setVaterLink(0)
                        ->save();
                    $oLink->setLinkgruppe((int)$_POST['kLinkgruppeAlt'])
                        ->delete(false,(int)$_POST['kLinkgruppeAlt']);
                    // Kinder auch umziehen
                    if (isset($oLink->oSub_arr) && count($oLink->oSub_arr) > 0) {
                        aenderLinkgruppeRek($oLink->oSub_arr, (int)$_POST['kLinkgruppe'], (int)$_POST['kLinkgruppeAlt']);
                    }
                    $hinweis   .= 'Sie haben den Link "' . $oLink->cName . '" erfolgreich in die Linkgruppe "' .
                        $oLinkgruppe->cName . '" verschoben.';
                    $step       = 'uebersicht';
                    $clearCache = true;
                } else {
                    $fehler .= 'Fehler: Der Link konnte nicht verschoben werden. Er existiert bereits in der Zielgruppe.';
                }
            } else {
                $fehler .= 'Fehler: Es konnte keine Linkgruppe mit Ihrem Key gefunden werden.';
            }
        } else {
            $fehler .= 'Fehler: Es konnte kein Link mit Ihrem Key gefunden werden.';
        }
    }
    $step = 'uebersicht';
}
if (isset($_POST['kopiere_in_linkgruppe']) &&
    (int)$_POST['kopiere_in_linkgruppe'] === 1 &&
    (int)$_POST['kLink'] > 0 &&
    (int)$_POST['kLinkgruppe'] > 0 &&
    validateToken()
) {
    $oLink = new Link((int)$_POST['kLink'], null, true);

    if ($oLink->getLink() > 0) {
        $oLinkgruppe = Shop::DB()->select('tlinkgruppe', 'kLinkgruppe', (int)$_POST['kLinkgruppe']);
        if (isset($oLinkgruppe->kLinkgruppe) && $oLinkgruppe->kLinkgruppe > 0) {
            $exists = Shop::DB()->select('tlink', ['kLink', 'kLinkgruppe'],[(int)$oLink->kLink,  (int)$_POST['kLinkgruppe']]);
            if (empty($exists)) {
                $oLink->setLinkgruppe($_POST['kLinkgruppe'])
                    ->setVaterLink(0)
                    ->save();
                $hinweis .= 'Sie haben den Link "' . $oLink->cName . '" erfolgreich in die Linkgruppe "' .
                    $oLinkgruppe->cName . '" kopiert.';
            } else {
                $fehler .= 'Fehler: Der Link konnte nicht kopiert werden. Er existiert bereits in der Zielgruppe.';
            }
            $step       = 'uebersicht';
            $clearCache = true;
        } else {
            $fehler .= 'Fehler: Es konnte keine Linkgruppe mit Ihrem Key gefunden werden.';
        }
    } else {
        $fehler .= 'Fehler: Es konnte kein Link mit Ihrem Key gefunden werden.';
    }
}
// Ordnet einen Link neu an
if (isset($_POST['aender_linkvater']) && (int)$_POST['aender_linkvater'] === 1 && validateToken()) {
    $success = false;
    if ((int)$_POST['kLink'] > 0 && (int)$_POST['kVaterLink'] >= 0 && (int)$_POST['kLinkgruppe'] > 0) {
        $kLink       = (int)$_POST['kLink'];
        $kVaterLink  = (int)$_POST['kVaterLink'];
        $kLinkgruppe = (int)$_POST['kLinkgruppe'];
        $oLink       = Shop::DB()->select('tlink', ['kLink', 'kLinkgruppe'], [$kLink, $kLinkgruppe]);
        $oVaterLink  = Shop::DB()->select('tlink', ['kLink', 'kLinkgruppe'], [$kVaterLink, $kLinkgruppe]);

        if (isset($oLink->kLink) && $oLink->kLink > 0 &&
            ((isset($oVaterLink->kLink) && $oVaterLink->kLink > 0) || $kVaterLink == 0)) {
            $success         = true;
            $upd             = new stdClass();
            $upd->kVaterLink = $kVaterLink;
            Shop::DB()->update('tlink', ['kLink', 'kLinkgruppe'], [$kLink, $kLinkgruppe], $upd);
            $hinweis .= "Sie haben den Link '" . $oLink->cName . "' erfolgreich verschoben.";
            $step     = 'uebersicht';
        }
        $clearCache = true;
    }

    if (!$success) {
        $fehler .= 'Fehler: Link konnte nicht verschoben werden.';
    }
}

if ($step === 'uebersicht') {
    $linkgruppen = Shop::DB()->query("SELECT * FROM tlinkgruppe", 2);
    $lCount      = count($linkgruppen);

    foreach ($linkgruppen as $linkgruppe) {
        $linkgruppe->links_nh = Shop::DB()->selectAll(
            'tlink',
            'kLinkgruppe',
            (int)$linkgruppe->kLinkgruppe,
            '*',
            'nSort, cName'
        );
        $linkgruppe->links    = build_navigation_subs_admin($linkgruppe->links_nh);
    }

    $smarty->assign('kPlugin', verifyGPCDataInteger('kPlugin'))
           ->assign('linkgruppen', $linkgruppen);
}

if ($step === 'neue Linkgruppe') {
    $smarty->assign('sprachen', gibAlleSprachen());
}

if ($step === 'neuer Link') {
    $kundengruppen = Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2);
    $smarty->assign('Link', $link)
           ->assign('oSpezialseite_arr', holeSpezialseiten())
           ->assign('sprachen', gibAlleSprachen())
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($link));
}

//clear cache
if ($clearCache === true) {
    Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
    Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
}
$smarty->assign('step', $step)
       ->assign('hinweis', $hinweis)
       ->assign('fehler', $fehler)
       ->display('links.tpl');
