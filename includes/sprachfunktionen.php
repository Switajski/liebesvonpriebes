<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param Warenkorb $warenkorb
 * @return string
 */
function lang_warenkorb_warenkorbEnthaeltXArtikel($warenkorb)
{
    if ($warenkorb === null) {
        return '';
    }
    if ($warenkorb->hatTeilbareArtikel()) {
        $nPositionen = $warenkorb->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]);
        $ret         = Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' . $nPositionen . ' ';
        if ($nPositionen == 1) {
            $ret .= Shop::Lang()->get('position', 'global');
        } else {
            $ret .= Shop::Lang()->get('positions', 'global');
        }

        return $ret;
    } else {
        $nArtikel = (get_class($warenkorb) === 'Warenkorb')
            ? $warenkorb->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
            : 0;
        $nArtikel = str_replace('.', ',', $nArtikel);
        if ($nArtikel == 1) {
            return Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' .
                $nArtikel . ' ' . Shop::Lang()->get('product', 'global');
        }
        if ($nArtikel > 1) {
            return Shop::Lang()->get('yourbasketcontains', 'checkout') . ' ' .
                $nArtikel . ' ' . Shop::Lang()->get('products', 'global');
        }
        if ($nArtikel == 0) {
            return Shop::Lang()->get('emptybasket', 'checkout');
        }
    }

    return '';
}

/**
 * @param Warenkorb $warenkorb
 * @return string,
 */
function lang_warenkorb_warenkorbLabel($warenkorb)
{
    $cLabel = Shop::Lang()->get('basket', 'checkout');
    if ($warenkorb !== null) {
        $cLabel .= ' (' . gibPreisStringLocalized(
            $warenkorb->gibGesamtsummeWarenExt(
                [C_WARENKORBPOS_TYP_ARTIKEL],
                !$_SESSION['Kundengruppe']->nNettoPreise
            )) . ')';
    }

    return $cLabel;
}

/**
 * @param Warenkorb $warenkorb
 * @return string
 */
function lang_warenkorb_bestellungEnthaeltXArtikel($warenkorb)
{
    $ret = Shop::Lang()->get('yourordercontains', 'checkout') . ' ' . count($warenkorb->PositionenArr) . ' ';
    if (count($warenkorb->PositionenArr) === 1) {
        $ret .= Shop::Lang()->get('position', 'global');
    } else {
        $ret .= Shop::Lang()->get('positions', 'global');
    }
    $ret .= ' ' . Shop::Lang()->get('with', 'global') . ' ' .
        (isset($_SESSION['Warenkorb']->kWarenkorb)
            ? $warenkorb->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL])
            : 0
        ) . ' ';
    if (isset($anzahlArtikel) && $anzahlArtikel == 1) {
        $ret .= Shop::Lang()->get('product', 'global');
    } else {
        $ret .= Shop::Lang()->get('products', 'global');
    }

    return $ret;
}

/**
 * @param int $anzahlArtikel
 * @return string
 */
function lang_warenkorb_Artikelanzahl($anzahlArtikel)
{
    if ($anzahlArtikel == 1) {
        return $anzahlArtikel . ' ' . Shop::Lang()->get('product', 'global');
    }

    return $anzahlArtikel . ' ' . Shop::Lang()->get('products', 'global');
}

/**
 * @param int $laenge
 * @return string
 */
function lang_passwortlaenge($laenge)
{
    return $laenge . ' ' . Shop::Lang()->get('min', 'characters') . '!';
}

/**
 * @param int  $ust
 * @param bool $netto
 * @return string
 */
function lang_steuerposition($ust, $netto)
{
    if ($ust == (int)$ust) {
        $ust = (int)$ust;
    }
    return $netto
        ? Shop::Lang()->get('plus', 'productDetails') . ' ' . $ust . '% ' . Shop::Lang()->get('vat', 'productDetails')
        : Shop::Lang()->get('incl', 'productDetails') . ' ' . $ust . '% ' . Shop::Lang()->get('vat', 'productDetails');
}

/**
 * @param string $suchausdruck
 * @param int    $anzahl
 * @return string
 */
function lang_suche_mindestanzahl($suchausdruck, $anzahl)
{
    return Shop::Lang()->get('expressionHasTo', 'global') . ' ' .
        $anzahl . ' ' .
        Shop::Lang()->get('characters', 'global') . '<br />' .
        Shop::Lang()->get('yourSearch', 'global') . ': ' . $suchausdruck;
}

/**
 * @param int $status
 * @return mixed
 */
function lang_bestellstatus($status)
{
    switch ($status) {
        case BESTELLUNG_STATUS_OFFEN:
            return Shop::Lang()->get('statusPending', 'order');
            break;
        case BESTELLUNG_STATUS_IN_BEARBEITUNG:
            return Shop::Lang()->get('statusProcessing', 'order');
            break;
        case BESTELLUNG_STATUS_BEZAHLT:
            return Shop::Lang()->get('statusPaid', 'order');
            break;
        case BESTELLUNG_STATUS_VERSANDT:
            return Shop::Lang()->get('statusShipped', 'order');
            break;
        case BESTELLUNG_STATUS_STORNO:
            return Shop::Lang()->get('statusCancelled', 'order');
            break;
        case BESTELLUNG_STATUS_TEILVERSANDT:
            return Shop::Lang()->get('statusPartialShipped', 'order');
            break;
        default:
            return '';
    }
}

/**
 * @param Artikel   $Artikel
 * @param int|float $beabsichtigteKaufmenge
 * @param int       $kKonfigitem
 * @return string
 */
function lang_mindestbestellmenge($Artikel, $beabsichtigteKaufmenge, $kKonfigitem = 0)
{
    if ($Artikel->cEinheit) {
        $Artikel->cEinheit = ' ' . $Artikel->cEinheit;
    }
    $cName = $Artikel->cName;
    if (class_exists('Konfigitem') && (int)$kKonfigitem > 0) {
        $oKonfigitem = new Konfigitem($kKonfigitem);
        $cName       = $oKonfigitem->getName();
    }

    return Shop::Lang()->get('product', 'global') . ' &quot;' . $cName . '&quot; ' .
        Shop::Lang()->get('hasMbm', 'messages') . ' (' .
        $Artikel->fMindestbestellmenge . $Artikel->cEinheit . '). ' .
        Shop::Lang()->get('yourQuantity', 'messages') . ' ' .
        (float)$beabsichtigteKaufmenge . $Artikel->cEinheit . '.';
}
