<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPost_arr
 * @param array $oSprache_arr
 * @return array
 */
function plausiCheckBox($cPost_arr, $oSprache_arr)
{
    $cPlausi_arr = [];
    if (!is_array($oSprache_arr) || count($oSprache_arr) === 0) {
        $cPlausi_arr['oSprache_arr'] = 1;

        return $cPlausi_arr;
    }
    if (is_array($cPost_arr) && count($cPost_arr) > 0) {
        // cName
        if (!isset($cPost_arr['cName']) || strlen($cPost_arr['cName']) === 0) {
            $cPlausi_arr['cName'] = 1;
        }
        // cText
        $bText = false;
        foreach ($oSprache_arr as $oSprache) {
            if (strlen($cPost_arr['cText_' . $oSprache->cISO]) > 0) {
                $bText = true;
                break;
            }
        }
        if (!$bText) {
            $cPlausi_arr['cText'] = 1;
        }
        // nLink
        $bLink = true;
        if ((int)$cPost_arr['nLink'] === 1) {
            $bLink = false;
            if (isset($cPost_arr['kLink']) && (int)$cPost_arr['kLink'] > 0) {
                $bLink = true;
            }
        }
        if (!$bLink) {
            $cPlausi_arr['kLink'] = 1;
        }
        // cAnzeigeOrt
        if (!is_array($cPost_arr['cAnzeigeOrt']) || count($cPost_arr['cAnzeigeOrt']) === 0) {
            $cPlausi_arr['cAnzeigeOrt'] = 1;
        } else {
            foreach ($cPost_arr['cAnzeigeOrt'] as $cAnzeigeOrt) {
                if ((int)$cAnzeigeOrt === 3 && $cPost_arr['kCheckBoxFunktion'] == 1) {
                    $cPlausi_arr['cAnzeigeOrt'] = 2;
                }
            }
        }
        // nPflicht
        if (!isset($cPost_arr['nPflicht']) || strlen($cPost_arr['nPflicht']) === 0) {
            $cPlausi_arr['nPflicht'] = 1;
        }
        // nAktiv
        if (!isset($cPost_arr['nAktiv']) || strlen($cPost_arr['nAktiv']) === 0) {
            $cPlausi_arr['nAktiv'] = 1;
        }
        // nLogging
        if (!isset($cPost_arr['nLogging']) || strlen($cPost_arr['nLogging']) === 0) {
            $cPlausi_arr['nLogging'] = 1;
        }
        // nSort
        if (!isset($cPost_arr['nSort']) || (int)$cPost_arr['nSort'] === 0) {
            $cPlausi_arr['nSort'] = 1;
        }
        // kKundengruppe
        if (!is_array($cPost_arr['kKundengruppe']) || count($cPost_arr['kKundengruppe']) === 0) {
            $cPlausi_arr['kKundengruppe'] = 1;
        }
    }

    return $cPlausi_arr;
}

/**
 * @param array $cPost_arr
 * @param array $oSprache_arr
 * @return CheckBox
 */
function speicherCheckBox($cPost_arr, $oSprache_arr)
{
    if (isset($cPost_arr['kCheckBox']) && (int)$cPost_arr['kCheckBox'] > 0) {
        $oCheckBox = new CheckBox((int)$cPost_arr['kCheckBox']);
        $oCheckBox->deleteCheckBox([(int)$cPost_arr['kCheckBox']]);
    } else {
        $oCheckBox = new CheckBox();
    }
    $oCheckBox->kLink = 0;
    if ((int)$cPost_arr['nLink'] === 1) {
        $oCheckBox->kLink = (int)$cPost_arr['kLink'];
    }
    $oCheckBox->kCheckBoxFunktion = (int)$cPost_arr['kCheckBoxFunktion'];
    $oCheckBox->cName             = htmlspecialchars($cPost_arr['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
    $oCheckBox->cKundengruppe     = gibKeyStringFuerKeyArray($cPost_arr['kKundengruppe'], ';');
    $oCheckBox->cAnzeigeOrt       = gibKeyStringFuerKeyArray($cPost_arr['cAnzeigeOrt'], ';');
    $oCheckBox->nAktiv            = 0;
    if ($cPost_arr['nAktiv'] === 'Y') {
        $oCheckBox->nAktiv = 1;
    }
    $oCheckBox->nPflicht = 0;
    $oCheckBox->nLogging = 0;
    if ($cPost_arr['nLogging'] === 'Y') {
        $oCheckBox->nLogging = 1;
    }
    if ($cPost_arr['nPflicht'] === 'Y') {
        $oCheckBox->nPflicht = 1;
    }
    $oCheckBox->nSort       = (int)$cPost_arr['nSort'];
    $oCheckBox->dErstellt   = 'now()';
    $cTextAssoc_arr         = [];
    $cBeschreibungAssoc_arr = [];
    foreach ($oSprache_arr as $oSprache) {
        $cTextAssoc_arr[$oSprache->cISO]         = str_replace('"', '&quot;', $cPost_arr['cText_' . $oSprache->cISO]);
        $cBeschreibungAssoc_arr[$oSprache->cISO] = str_replace('"', '&quot;', $cPost_arr['cBeschreibung_' . $oSprache->cISO]);
    }

    $oCheckBox->insertDB($cTextAssoc_arr, $cBeschreibungAssoc_arr);

    return $oCheckBox;
}
