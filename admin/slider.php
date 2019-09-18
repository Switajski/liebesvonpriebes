<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . 'toolsajax.server.php';
$oAccount->permission('SLIDER_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'slider_inc.php';
$cFehler      = '';
$cHinweis     = '';
$_kSlider     = 0;
$cRedirectUrl = Shop::getURL() . '/' . PFAD_ADMIN . 'slider.php';
$cAction      = (isset($_REQUEST['action']) && validateToken())
    ? $_REQUEST['action']
    : 'view';
$kSlider      = isset($_REQUEST['id'])
    ? (int)$_REQUEST['id']
    : 0;

switch ($cAction) {
    case 'slide_set':
        $aSlideKey = array_keys((array)$_REQUEST['aSlide']);
        $count     = count($aSlideKey);
        for ($i = 0; $i < $count; $i++) {
            $oSlide               = new Slide();
            $aSlide               = $_REQUEST['aSlide'][$aSlideKey[$i]];
            $oSlide->kSlide       = (strpos($aSlideKey[$i], 'neu') === false)
                ? $aSlideKey[$i]
                : null;
            $oSlide->kSlider      = $kSlider;
            $oSlide->cTitel       = htmlspecialchars($aSlide['cTitel'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $oSlide->cBild        = $aSlide['cBild'];
            $oSlide->cText        = htmlspecialchars($aSlide['cText'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            $oSlide->cLink        = $aSlide['cLink'];
            $oSlide->nSort        = $aSlide['nSort'];
            if ($aSlide['delete'] == 1) {
                $oSlide->delete();
            } else {
                $oSlide->save();
            }
        }
        break;
    default:
        $smarty->assign('disabled', '');
        // Daten Speichern
        if (!empty($_POST) && validateToken()) {
            $oSlider  = new Slider();
            $_kSlider = $_POST['kSlider'];
            $oSlider->load($kSlider);
            $oSlider->set($_REQUEST);
            // extensionpoint
            $kSprache      = (int)$_POST['kSprache'];
            $kKundengruppe = $_POST['kKundengruppe'];
            $nSeite        = (int)$_POST['nSeitenTyp'];
            $cKey          = $_POST['cKey'];

            $cKeyValue = '';
            $cValue    = '';
            if ($nSeite === PAGE_ARTIKEL) {
                $cKey      = 'kArtikel';
                $cKeyValue = 'article_key';
                $cValue    = $_POST[$cKeyValue];
            } elseif ($nSeite === PAGE_ARTIKELLISTE) {
                // data mapping
                $aFilter_arr = [
                    'kTag'         => 'tag_key',
                    'kMerkmalWert' => 'attribute_key',
                    'kKategorie'   => 'categories_key',
                    'kHersteller'  => 'manufacturer_key',
                    'cSuche'       => 'keycSuche'
                ];

                $cKeyValue = $aFilter_arr[$cKey];
                $cValue    = $_POST[$cKeyValue];
            } elseif ($nSeite === PAGE_EIGENE) {
                $cKey      = 'kLink';
                $cKeyValue = 'link_key';
                $cValue    = $_POST[$cKeyValue];
            }

            if (empty($oSlider->cEffects)) {
                $oSlider->cEffects = 'random';
            }
            if ($oSlider->save() === true) {
                Shop::DB()->delete('textensionpoint', ['cClass', 'kInitial'], ['Slider', $oSlider->kSlider]);
                // save extensionpoint
                $oExtension                = new stdClass();
                $oExtension->kSprache      = $kSprache;
                $oExtension->kKundengruppe = $kKundengruppe;
                $oExtension->nSeite        = $nSeite;
                $oExtension->cKey          = $cKey;
                $oExtension->cValue        = $cValue;
                $oExtension->cClass        = 'Slider';
                $oExtension->kInitial      = $oSlider->kSlider;
                Shop::DB()->insert('textensionpoint', $oExtension);

                header('Location: ' . $cRedirectUrl);
                exit;
            } else {
                $cFehler .= 'Slider konnte nicht gespeichert werden.';
            }

            if (empty($cFehler)) {
                $cHinweis = '&Auml;nderungen erfolgreich gespeichert.';
            }
        }
        break;
}
// Daten anzeigen
switch ($cAction) {
    case 'slides' :
        $oSlider = new Slider();
        $oSlider->load($kSlider);
        $smarty->assign('oSlider', $oSlider);
        if (!is_object($oSlider)) {
            $cFehler = 'Slider wurde nicht gefunden.';
            $cAction = 'view';
        }
        break;

    case 'edit':
        if ($kSlider === 0 && $_kSlider > 0) {
            $kSlider = $_kSlider;
        }
        $oSlider = new Slider();
        $oSlider->load($kSlider);
        $oExtension    = holeExtension($kSlider);
        $oSprache      = Sprache::getInstance(false);
        $oSprachen_arr = $oSprache->gibInstallierteSprachen();

        $smarty->assign('oSprachen_arr', $oSprachen_arr)
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('oExtension', $oExtension);

        if ($oSlider->cEffects !== 'random') {
            $cEffects_arr = explode(';', $oSlider->cEffects);
            $cEffects     = '';
            foreach ($cEffects_arr as $cKey => $cValue) {
                $cEffects .= '<option value="' . $cValue . '">' . $cValue . '</option>';
            }
            $smarty->assign('cEffects', $cEffects);
        } else {
            $smarty->assign('checked', 'checked="checked"')
                   ->assign('disabled', 'disabled="true"');
        }

        $smarty->assign('oSlider', $oSlider);

        if (!is_object($oSlider)) {
            $cFehler = 'Slider wurde nicht gefunden.';
            $cAction = 'view';
            break;
        }
        break;

    case 'new':
        $oSlider       = new Slider();
        $oSprache      = Sprache::getInstance(false);
        $oSprachen_arr = $oSprache->gibInstallierteSprachen();

        $smarty->assign('checked', 'checked="checked"')
               ->assign('oSprachen_arr', $oSprachen_arr)
               ->assign('oKundengruppe_arr', Kundengruppe::getGroups())
               ->assign('oSlider', $oSlider);
        break;

    case 'delete':
        $oSlider  = new Slider();
        $bSuccess = $oSlider->delete($kSlider);
        if ($bSuccess === true) {
            header('Location: ' . $cRedirectUrl);
            exit;
        } else {
            $cFehler = 'Slider konnte nicht entfernt werden.';
        }
        break;

    default:
        break;
}

$smarty->assign('PFAD_MEDIAFILES', PFAD_MEDIAFILES)
       ->assign('cFehler', $cFehler)
       ->assign('cHinweis', $cHinweis)
       ->assign('cAction', $cAction)
       ->assign('kSlider', $kSlider)
       ->assign('oSlider_arr', Shop::DB()->query("SELECT * FROM tslider", 2))
       ->assign('xajax_javascript', $xajax->getJavascript(Shop::getURL() . '/' . PFAD_XAJAX))
       ->display('slider.tpl');
