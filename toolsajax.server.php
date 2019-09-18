<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . 'toolsajax.common.php';
/** @global JTLSmarty $smarty */

$cAktuelleSeite = isset($_SERVER['HTTP_REFERER'])
    ? substr(strrchr($_SERVER['HTTP_REFERER'], '/'), 1, strlen(strrchr($_SERVER['HTTP_REFERER'], '/')))
    : '';
if ($cAktuelleSeite === 'warenkorb.php?') {
    $Einstellungen        = Shop::getSettings([
            CONF_GLOBAL,
            CONF_NAVIGATIONSFILTER,
            CONF_RSS,
            CONF_KUNDEN,
            CONF_KAUFABWICKLUNG,
            CONF_KUNDENFELD,
            CONF_KUNDENWERBENKUNDEN,
            CONF_TRUSTEDSHOPS,
            CONF_AUSWAHLASSISTENT,
            CONF_METAANGABEN
        ]
    );
    $GlobaleEinstellungen = $Einstellungen['global'];
}

/**
 * @param int   $kArtikel
 * @param float $fPreis
 * @return xajaxResponse
 * @deprecated since 4.02
 */
function gibFinanzierungInfo($kArtikel, $fPreis)
{
    $objResponse = new xajaxResponse();
    $objResponse->assign('commerz_financing', 'style.display', 'none');

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @return xajaxResponse
 */
function billpayRates()
{
    global $smarty;
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

    $oAjax            = new xajaxResponse();
    $oResponse        = new stdClass();
    $oResponse->nType = 0;

    $oBillpay = PaymentMethod::create('za_billpay_jtl');
    if ($oBillpay) {
        $oRates = $oBillpay->calculateRates($_SESSION['Warenkorb']);
        if (is_object($oRates)) {
            $oResponse->nType         = 2;
            $oResponse->cRateHTML_arr = [];
            $oResponse->nRates_arr    = $oRates->nAvailable_arr;
            // special link
            $cPortalHash      = md5($oBillpay->getSetting('pid'));
            $cBillpayTermsURL = BILLPAY_TERMS_RATE;
            $cBillpayTermsURL = str_replace('%pidhash%', $cPortalHash, $cBillpayTermsURL);
            foreach ($oRates->aRates_arr as $oRate) {
                // rate
                $smarty->assign('oRate', $oRate)
                       ->assign('nRate_arr', $oRates->nAvailable_arr)
                       ->assign('cBillpayTermsURL', $cBillpayTermsURL)
                       ->assign('cBillpayPrivacyURL', BILLPAY_PRIVACY)
                       ->assign('cBillpayTermsPaymentURL', BILLPAY_TERMS_PAYMENT);

                $oResponse->cRateHTML_arr[$oRate->nRate] =
                    utf8_encode($smarty->fetch('checkout/modules/billpay/raten.tpl'));
            }
        } else {
            $oResponse->nType = 1;
        }
    }
    $oAjax->script('this.response = ' . json_encode($oResponse) . ';');

    return $oAjax;
}

/**
 * @deprecated since 4.05
 * @param int  $nVLKeys
 * @param bool $bWarenkorb
 * @return xajaxResponse
 */
function gibVergleichsliste($nVLKeys = 0, $bWarenkorb = true)
{
    global $smarty;
    require_once PFAD_ROOT . PFAD_INCLUDES . 'vergleichsliste_inc.php';

    $objResponse                   = new xajaxResponse();
    $session                       = Session::getInstance();
    $Einstellungen_Vergleichsliste = Shop::getSettings([CONF_VERGLEICHSLISTE, CONF_ARTIKELDETAILS]);
    $oVergleichsliste              = new Vergleichsliste();
    // Falls $nVLKeys 1 ist, nimm die kArtikel von $_SESSION['nArtikelUebersichtVLKey_arr']
    // und baue eine neue TMP Vergleichsliste
    if ($nVLKeys == 1 && isset($_SESSION['nArtikelUebersichtVLKey_arr']) &&
        is_array($_SESSION['nArtikelUebersichtVLKey_arr']) &&
        count($_SESSION['nArtikelUebersichtVLKey_arr']) > 0
    ) {
        $oVergleichsliste->oArtikel_arr = [];
        foreach ($_SESSION['nArtikelUebersichtVLKey_arr'] as $nArtikelUebersichtVLKey) {
            $oVergleichsliste->fuegeEin($nArtikelUebersichtVLKey, false);
        }
    } elseif ($nVLKeys != 0 && strlen($nVLKeys) > 0) {
        $nVLKey_arr = explode(';', $nVLKeys);
        if (is_array($nVLKey_arr)) {
            $keyCount = count($nVLKey_arr);
            for ($i = 0; $i < $keyCount; $i += 2) {
                $oVergleichsliste->fuegeEin($nVLKey_arr[$i], false, $nVLKey_arr[$i + 1]);
            }
        }
    }
    $oVergleichslisteArr = [];
    if (isset($oVergleichsliste->oArtikel_arr)) {
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($oVergleichsliste->oArtikel_arr as $article) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($article->kArtikel, $defaultOptions);
            $oVergleichslisteArr[] = $oArtikel;
        }
        $oVergleichsliste->oArtikel_arr = $oVergleichslisteArr;
    }
    $oMerkVaria_arr = baueMerkmalundVariation($oVergleichsliste);
    // Füge den Vergleich für Statistikzwecke in die DB ein
    setzeVergleich($oVergleichsliste);

    $cExclude = [];
    for ($i = 0; $i < 8; $i++) {
        $cElement = gibMaxPrioSpalteV($cExclude, $Einstellungen_Vergleichsliste);
        if (strlen($cElement) > 1) {
            $cExclude[] = $cElement;
        }
    }
    // Spaltenbreite
    $nBreiteAttribut = ((int)$Einstellungen_Vergleichsliste['vergleichsliste']['vergleichsliste_spaltengroesseattribut'] > 0)
        ? (int)$Einstellungen_Vergleichsliste['vergleichsliste']['vergleichsliste_spaltengroesseattribut']
        : 100;
    $nBreiteArtikel  = ((int)$Einstellungen_Vergleichsliste['vergleichsliste']['vergleichsliste_spaltengroesse'] > 0)
        ? (int)$Einstellungen_Vergleichsliste['vergleichsliste']['vergleichsliste_spaltengroesse']
        : 200;
    $nBreiteTabelle  = $nBreiteArtikel * count($oVergleichsliste->oArtikel_arr) + $nBreiteAttribut;
    //specific assigns
    global $AktuelleSeite;
    if (!isset($AktuelleSeite)) {
        $AktuelleSeite = null;
    }
    $smarty->assign('nBreiteTabelle', $nBreiteTabelle)
           ->assign('cPrioSpalten_arr', $cExclude)
           ->assign('oMerkmale_arr', $oMerkVaria_arr[0])
           ->assign('oVariationen_arr', $oMerkVaria_arr[1])
           ->assign('oVergleichsliste', $oVergleichsliste)
           ->assign('Navigation', createNavigation($AktuelleSeite, 0, 0))
           ->assign('Einstellungen', $GLOBALS['GlobaleEinstellungen'])
           ->assign('Einstellungen_Vergleichsliste', $Einstellungen_Vergleichsliste)
           ->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise)
           ->assign('bAjax', true)
           ->assign('bWarenkorb', $bWarenkorb);

    executeHook(HOOK_VERGLEICHSLISTE_PAGE);
    $objResponse->script("this.compareHTML = " .
        json_encode(utf8_encode($smarty->fetch('comparelist/index.tpl'))) . ";");

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @return xajaxResponse
 */
function generateToken()
{
    $objResponse             = new xajaxResponse();
    $cToken                  = gibToken();
    $cName                   = gibTokenName();
    $token_arr               = ['name' => $cName, 'token' => $cToken];
    $_SESSION['xcrsf_token'] = json_encode($token_arr);
    $objResponse->script("doXcsrfToken('" . $cName . "', '" . $cToken . "');");

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param array $oArtikel_arr
 * @return xajaxResponse
 */
function ermittleVersandkostenAjax($oArtikel_arr)
{
    $objResponse      = new xajaxResponse();
    $oResponse        = new stdClass();
    $oResponse->cText = utf8_encode(VersandartHelper::getShippingCostsExt($oArtikel_arr));
    $oResponse->cText .= ' (' . $_SESSION['shipping_count']++ . ')';
    $objResponse->script("this.response = " . json_encode($oResponse) . ";");

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param int $nPos
 * @return xajaxResponse
 */
function loescheWarenkorbPosAjax($nPos)
{
    /** @var array('Warenkorb') $_SESSION['Warenkorb'] */
    $objResponse = new xajaxResponse();
    //wurden Positionen gelöscht?
    if ($_SESSION['Warenkorb']->PositionenArr[(int)$nPos]->nPosTyp == 1) {
        unset($_SESSION['Warenkorb']->PositionenArr[(int)$nPos]);
        $_SESSION['Warenkorb']->PositionenArr = array_merge($_SESSION['Warenkorb']->PositionenArr);
        loescheAlleSpezialPos();
        if (!$_SESSION['Warenkorb']->enthaltenSpezialPos(C_WARENKORBPOS_TYP_ARTIKEL)) {
            $_SESSION['Warenkorb'] = new Warenkorb();
        }
        // Lösche Position aus dem WarenkorbPersPos
        if ($_SESSION['Kunde']->kKunde > 0) {
            $oWarenkorbPers = new WarenkorbPers($_SESSION['Kunde']->kKunde);
            $oWarenkorbPers->entferneAlles()
                           ->bauePersVonSession();
        }
    }

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param int       $kArtikel
 * @param int|float $anzahl
 * @param string    $oEigenschaftwerte_arr
 * @return xajaxResponse
 */
function fuegeEinInWarenkorbAjax($kArtikel, $anzahl, $oEigenschaftwerte_arr = '')
{
    global $Einstellungen, $smarty;

    require_once PFAD_ROOT . PFAD_INCLUDES . 'boxen.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    $oResponse           = new stdClass();
    $objResponse         = new xajaxResponse();
    $GLOBALS['oSprache'] = Sprache::getInstance();
    $kArtikel            = (int)$kArtikel;
    if ($anzahl > 0 && $kArtikel > 0) {
        $Artikel                     = new Artikel();
        $Artikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
        // Falls der Artikel ein Variationskombikind ist, hole direkt seine Eigenschaften
        if (isset($Artikel->kEigenschaftKombi) && $Artikel->kEigenschaftKombi > 0) {
            $oEigenschaftwerte_arr = gibVarKombiEigenschaftsWerte($Artikel->kArtikel);
        }
        if ((int)$anzahl != $anzahl && $Artikel->cTeilbar !== 'Y') {
            $anzahl = max((int)$anzahl, 1);
        }
        // Prüfung
        $redirectParam = pruefeFuegeEinInWarenkorb($Artikel, $anzahl, $oEigenschaftwerte_arr);

        if (count($redirectParam) > 0) {
            $cRedirectParam = implode(',', $redirectParam);
            baueArtikelhinweise($cRedirectParam);

            $smarty->assign('cHinweis_arr', $GLOBALS['Artikelhinweise']);
            $oResponse->cPopup = utf8_encode($smarty->fetch('productdetails/redirect.tpl'));
            //redirekt zum artikel, um variation/en zu wählen / MBM beachten
            if ($Artikel->nIstVater === 1) {
                $location = 'navi.php?a=' . $Artikel->kArtikel .
                    '&n=' . $anzahl . '&r=' . implode(',', $redirectParam);
            } elseif ($Artikel->kEigenschaftKombi > 0) {
                $location = 'navi.php?a=' . $Artikel->kVaterArtikel . '&a2=' .
                    $Artikel->kArtikel . '&n=' . $anzahl . '&r=' . implode(',', $redirectParam);
            } else {
                $location = 'index.php?a=' . $Artikel->kArtikel . '&n=' .
                    $anzahl . '&r=' . implode(',', $redirectParam);
            }

            $oResponse->nType     = 1;
            $oResponse->cLocation = $location;
            $oResponse->oArtikel  = $Artikel;
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
        $_SESSION['Warenkorb']->fuegeEin($kArtikel, $anzahl, $oEigenschaftwerte_arr)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
                              ->loescheSpezialPos(C_WARENKORBPOS_TYP_TRUSTEDSHOPS);

        unset(
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Versandart'],
            $_SESSION['Zahlungsart'],
            $_SESSION['TrustedShops']
        );
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb, dann verwerfen und neu anlegen
        altenKuponNeuBerechnen();
        setzeLinks();
        // Persistenter Warenkorb
        if (!isset($_POST['login'])) {
            fuegeEinInWarenkorbPers($kArtikel, $anzahl, $oEigenschaftwerte_arr);
        }
        $boxes       = Boxen::getInstance();
        $pageType    = (Shop::getPageType() !== null) ? Shop::getPageType() : PAGE_UNBEKANNT;
        $boxesToShow = $boxes->build($pageType, true)->render();
        $smarty->assign('Boxen', $boxesToShow);
        $warensumme[0] = gibPreisStringLocalized($_SESSION['Warenkorb']->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true));
        $warensumme[1] = gibPreisStringLocalized($_SESSION['Warenkorb']->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false));
        $smarty->assign('WarenkorbWarensumme', $warensumme);

        $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
        if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
            $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
        }
        $oXSelling = gibArtikelXSelling($kArtikel);

        $smarty->assign('WarenkorbVersandkostenfreiHinweis',
            baueVersandkostenfreiString(gibVersandkostenfreiAb($kKundengruppe),
                $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(
                    [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_KUPON, C_WARENKORBPOS_TYP_NEUKUNDENKUPON],
                    true)
            ))
               ->assign('oArtikel', $Artikel)// deprecated 3.12
               ->assign('zuletztInWarenkorbGelegterArtikel', $Artikel)
               ->assign('fAnzahl', $anzahl)
               ->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise)
               ->assign('Einstellungen', $Einstellungen)
               ->assign('Xselling', $oXSelling);

        $oResponse->nType           = 2;
        $oResponse->cWarenkorbText  = utf8_encode(lang_warenkorb_warenkorbEnthaeltXArtikel($_SESSION['Warenkorb']));
        $oResponse->cWarenkorbLabel = utf8_encode(lang_warenkorb_warenkorbLabel($_SESSION['Warenkorb']));
        $oResponse->cPopup          = utf8_encode($smarty->fetch('productdetails/pushed.tpl'));
        $oResponse->cWarenkorbMini  = utf8_encode($smarty->fetch('basket/cart_dropdown.tpl'));
        $oResponse->oArtikel        = utf8_convert_recursive($Artikel, true);

        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');
        // Kampagne
        if (isset($_SESSION['Kampagnenbesucher'])) {
            setzeKampagnenVorgang(KAMPAGNE_DEF_WARENKORB, $kArtikel, $anzahl); // Warenkorb
        }
        if ($GLOBALS['GlobaleEinstellungen']['global']['global_warenkorb_weiterleitung'] === 'Y') {
            $oResponse->nType     = 1;
            $oResponse->cLocation = 'warenkorb.php';
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
    }

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param $nED
 * @return xajaxResponse
 */
function setzeErweiterteDarstellung($nED)
{
    global $Einstellungen;
    $objResponse = new xajaxResponse();

    require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';

    unset($NaviFilter);
    $NaviFilter = Shop::buildNaviFilter([]);
    gibErweiterteDarstellung($Einstellungen, $NaviFilter, $nED);

    return $objResponse;
}

/**
 * Kundenformular Ajax PLZ
 *
 * @deprecated since 4.05
 * @param $cFormValue - Textfeld Wert vom Kundenfomular input Feldes
 * @param $cLandISO - Ausgewähltes Land in der DropDown Box
 * @return xajaxResponse
 */
function gibPLZInfo($cFormValue, $cLandISO)
{
    $objResponse = new xajaxResponse();
    $oPlz_arr    = [];
    if (strlen($cFormValue) >= 4) {
        $oPlz_arr = Shop::DB()->selectAll(
            'tplz',
            ['cPLZ', 'cLandISO'],
            [StringHandler::htmlentities(StringHandler::filterXSS($cFormValue)),
             StringHandler::htmlentities(StringHandler::filterXSS($cLandISO))],
            'cOrt'
        );
    }
    foreach ($oPlz_arr as $i => $oPlz) {
        $oPlz_arr[$i]->cOrt = utf8_encode($oPlz->cOrt);
    }

    $objResponse->script('this.plz_data = ' . json_encode($oPlz_arr) . ';');

    executeHook(HOOK_TOOLSAJAXSERVER_PAGE_KUNDENFORMULARPLZ);

    return $objResponse;
}

/**
 * Kundenformular Ajax PLZ
 *
 * @deprecated since 4.05
 * @param string $cFormValue - Textfeld Wert vom Kundenfomular
 * @param string $cLandISO - Ausgewähltes Land in der DropDown Box
 * @return xajaxResponse
 */
function aenderKundenformularPLZ($cFormValue, $cLandISO)
{
    $objResponse = new xajaxResponse();
    if (strlen($cFormValue) >= 4) {
        $oPlz = Shop::DB()->select(
            'tplz',
            'cPLZ',
            StringHandler::htmlentities(StringHandler::filterXSS($cFormValue)),
            'cLandISO',
            StringHandler::htmlentities(StringHandler::filterXSS($cLandISO)),
            null,
            null,
            false,
            'cOrt'
        );
        if (!empty($oPlz->cOrt) && strlen($oPlz->cOrt) > 0) {
            $objResponse->assign('kundenformular_ort', 'value', $oPlz->cOrt);
        }
    }

    executeHook(HOOK_TOOLSAJAXSERVER_PAGE_KUNDENFORMULARPLZ);

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param string $cLandIso
 * @return xajaxResponse
 */
function gibRegionzuLand($cLandIso)
{
    $objResponse = new xajaxResponse();
    if (strlen($cLandIso) === 2) {
        $cRegion_arr = Staat::getRegions($cLandIso);
        $cRegion_arr = utf8_convert_recursive($cRegion_arr);
        $objResponse->script('this.response = ' . json_encode($cRegion_arr) . ';');
    }

    return $objResponse;
}

/**
 * Textfeld Ajax Suche
 *
 * @deprecated since 4.05
 * @param string     $cValue - Textfeld Wert (input) des Suchfeldes
 * @param string|int $nkeyCode - Geklickter Tastenwert
 * @param string     $cElemSearchID - Suchfeld (input)
 * @param string     $cElemSuggestID - DIV an dem die Suchvorschläge angegeben werden
 * @param string     $cElemSubmitID - Form die abgeschickt werden soll
 * @return xajaxResponse
 */
function suchVorschlag($cValue, $nkeyCode, $cElemSearchID, $cElemSuggestID, $cElemSubmitID)
{
    $nkeyCode       = (int)$nkeyCode;
    $cValue         = StringHandler::htmlentities(StringHandler::filterXSS($cValue));
    $cElemSearchID  = StringHandler::htmlentities(StringHandler::filterXSS($cElemSearchID));
    $cElemSuggestID = StringHandler::htmlentities(StringHandler::filterXSS($cElemSuggestID));
    $cElemSubmitID  = StringHandler::htmlentities(StringHandler::filterXSS($cElemSubmitID));

    global $Einstellungen;
    // Maximale Suchvorschläge
    $nMaxAnzahl = ((int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl'] > 0)
        ? (int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl']
        : 10;

    $objResponse = new xajaxResponse();
    $objResponse->assign($cElemSuggestID, 'innerHTML', '');

    if (strlen($cValue) >= 3) {
        $oSuchanfrage_arr = Shop::DB()->executeQueryPrepared("
            SELECT cSuche, nAnzahlTreffer
                FROM tsuchanfrage
                WHERE cSuche LIKE :search
                    AND nAktiv = 1
                    AND kSprache = :lang
                ORDER BY nAnzahlGesuche DESC, cSuche
                LIMIT :lim",
            ['search' => $cValue . '%', 'lang' => Shop::getLanguage(), 'lim' => $nMaxAnzahl],
            2
        );

        if (is_array($oSuchanfrage_arr) && count($oSuchanfrage_arr) > 0) {
            $cSuche = '';
            foreach ($oSuchanfrage_arr as $i => $oSuchanfrage) {
                $onClick   = 'document.getElementById("' . $cElemSearchID . '").value = "' .
                    $oSuchanfrage->cSuche . '"; document.' . $cElemSubmitID . '.submit();';
                $cSuchwort = str_replace($cValue, '<b>' . $cValue . '</b>', $oSuchanfrage->cSuche);
                $cSuche .= '<div class="suggestions" id="' . $cElemSuggestID . $i . '" onclick="' . $onClick . '">' .
                    $cSuchwort . ' <span class="suggestion_count">(' . $oSuchanfrage->nAnzahlTreffer . ')</span></div>';
                $cSuche .= '<input id="' . $cElemSuggestID . 'value' . $i . '" name="' . $cElemSuggestID . 'value' .
                    $i . '" type="hidden" value="' . $oSuchanfrage->cSuche . '">';
            }

            $objResponse->assign($cElemSuggestID, 'innerHTML', $cSuche);
            $objResponse->script(
                "resizeContainer('" . $cElemSearchID . "', '" . $cElemSuggestID . "');
               nAnzahlSuggests = " . (count($oSuchanfrage_arr) - 1) . ";"
            );
        }
    }

    executeHook(HOOK_TOOLSAJAXSERVER_PAGE_SUCHVORSCHLAG, [
        'cValue'         => &$cValue,
        'nkeyCode'       => &$nkeyCode,
        'cElemSearchID'  => &$cElemSearchID,
        'cElemSuggestID' => &$cElemSuggestID,
        'cElemSubmitID'  => &$cElemSubmitID,
        'objResponse'    => &$objResponse
    ]);

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param string $cValue
 * @return xajaxResponse
 */
function suggestions($cValue)
{
    global $Einstellungen;

    $cSuch_arr   = [];
    $cValue      = Shop::DB()->escape(StringHandler::filterXSS($cValue));
    $objResponse = new xajaxResponse();
    $nMaxAnzahl  = ((int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl'] > 0)
        ? (int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl']
        : 10;
    if (strlen($cValue) >= 3) {
        $oSuchanfrage_arr = Shop::DB()->executeQueryPrepared("
            SELECT cSuche, nAnzahlTreffer
                FROM tsuchanfrage
                WHERE cSuche LIKE :search
                    AND nAktiv = 1
                    AND kSprache = :lang
                ORDER BY nAnzahlGesuche DESC, cSuche
                LIMIT :lim",
            ['search' => $cValue . '%', 'lang' => Shop::getLanguage(), 'lim' => $nMaxAnzahl],
            2
        );
        if (is_array($oSuchanfrage_arr) && count($oSuchanfrage_arr) > 0) {
            foreach ($oSuchanfrage_arr as $i => $oSuchanfrage) {
                $cSuche                 = utf8_encode($oSuchanfrage->cSuche);
                $i                      = count($cSuch_arr);
                $cSuch_arr[$i]['value'] = $cSuche . ' <span class="ac_resultcount">' . $oSuchanfrage->nAnzahlTreffer .
                    ' ' . StringHandler::htmlentities(Shop::Lang()->get('matches', 'global')) . ' </span>';
                $cSuch_arr[$i]['result'] = $cSuche;
            }
        }
    }

    executeHook(HOOK_TOOLSAJAXSERVER_PAGE_SUCHVORSCHLAG, [
        'cValue'      => &$cValue,
        'objResponse' => &$objResponse,
        'cSuch_arr'   => &$cSuch_arr
    ]);

    $objResponse->script('this.ac_data = ' . json_encode($cSuch_arr) . ';');

    return $objResponse;
}

/**
 * @param array $aFormValues
 * @param int   $nVater
 * @param int   $kEigenschaft
 * @param int   $kEigenschaftWert
 * @param bool  $bSpeichern
 * @return xajaxResponse
 */
function tauscheVariationKombi($aFormValues, $nVater = 0, $kEigenschaft = 0, $kEigenschaftWert = 0, $bSpeichern = false)
{
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    global $smarty;

    Shop::setPageType(PAGE_ARTIKEL);
    Shop::$AktuelleSeite = 'ARTIKEL';
    $objResponse         = new xajaxResponse();
    $cVariationKombiKind = '';
    $Einstellungen       = Shop::getSettings([
        CONF_GLOBAL,
        CONF_NAVIGATIONSFILTER,
        CONF_RSS,
        CONF_ARTIKELUEBERSICHT,
        CONF_ARTIKELDETAILS,
        CONF_PREISVERLAUF,
        CONF_BEWERTUNG,
        CONF_BOXEN,
        CONF_PREISVERLAUF,
        CONF_METAANGABEN
    ]);
    //hole aktuellen Vater Artikel
    if (isset($aFormValues['a']) && $aFormValues['a'] > 0) {
        $oVaterArtikel    = new Artikel();
        $oArtikelOptionen = new stdClass();
        if ($nVater) {
            $oArtikelOptionen->nMerkmale         = 1;
            $oArtikelOptionen->nAttribute        = 1;
            $oArtikelOptionen->nArtikelAttribute = 1;
            $oArtikelOptionen->nMedienDatei      = 1;
        }
        $oArtikelOptionen->nVariationKombi       = 1;
        $oArtikelOptionen->nVariationKombiKinder = 1;
        $oArtikelOptionen->nDownload             = 1;
        $oArtikelOptionen->nKonfig               = 1;
        $oArtikelOptionen->nMain                 = 1;
        $oArtikelOptionen->nWarenlager           = 1;
        $oArtikelOptionen->nVariationDetailPreis = 1;
        // Warenkorbmatrix nötig? => Varikinder mit Preisen holen
        $oArtikelOptionen->nWarenkorbmatrix = (int)($Einstellungen['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] === 'Y');
        // Stückliste nötig? => Stücklistenkomponenten  holen
        $oArtikelOptionen->nStueckliste   = (int)($Einstellungen['artikeldetails']['artikeldetails_stueckliste_anzeigen'] === 'Y');
        $oArtikelOptionen->nProductBundle = (int)($Einstellungen['artikeldetails']['artikeldetails_produktbundle_nutzen'] === 'Y');
        $oVaterArtikel->fuelleArtikel((int)$aFormValues['a'], $oArtikelOptionen, (int)$aFormValues['kKundengruppe'], (int)$aFormValues['kSprache']);

        $bKindVorhanden = false;
        if (!$nVater && is_array($oVaterArtikel->Variationen) && count($oVaterArtikel->Variationen) > 0) {
            $kVariationKombi_arr = [];
            foreach ($oVaterArtikel->Variationen as $oVariation) {
                if ($oVariation->cTyp !== 'FREIFELD' &&
                    $oVariation->cTyp !== 'PFLICHT-FREIFELD' &&
                    isset($aFormValues['eigenschaftwert_' . $oVariation->kEigenschaft]) &&
                    (int)$aFormValues['eigenschaftwert_' . $oVariation->kEigenschaft] > 0
                ) {
                    $kVariationKombi_arr[$oVariation->kEigenschaft] =
                        (int)$aFormValues['eigenschaftwert_' . $oVariation->kEigenschaft];
                }
            }
            if ($bSpeichern) {
                if (!isset($_SESSION['oVarkombiAuswahl'])) {
                    $_SESSION['oVarkombiAuswahl'] = new stdClass();
                }
                $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr = $kVariationKombi_arr;
            }
            $oArtikelTMP = gibArtikelByVariationen($oVaterArtikel->kArtikel, $kVariationKombi_arr);
            if (isset($oArtikelTMP->kArtikel) && $oArtikelTMP->kArtikel > 0) {
                require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
                require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceMail.php';
                $bKindVorhanden = true;
                // Bewertungsguthaben
                $fBelohnung = 0.0;
                if ($Einstellungen['bewertung']['bewertung_guthaben_nutzen'] === 'Y') {
                    $fBelohnung = (isset($_GET['strl']) && $Einstellungen['bewertung']['bewertung_stufe2_anzahlzeichen'] <= $_GET['strl'])
                        ? $Einstellungen['bewertung']['bewertung_stufe2_guthaben']
                        : $Einstellungen['bewertung']['bewertung_stufe1_guthaben'];
                }

                // Hinweise und Fehler sammeln
                $cHinweis                                    = mappingFehlerCode(verifyGPDataString('cHinweis'), $fBelohnung);
                $cFehler                                     = mappingFehlerCode(verifyGPDataString('cFehler'));
                $oArtikel                                    = new Artikel();
                $oArtikelOptionen                            = new stdClass();
                $oArtikelOptionen->nMerkmale                 = 1;
                $oArtikelOptionen->nAttribute                = 1;
                $oArtikelOptionen->nArtikelAttribute         = 1;
                $oArtikelOptionen->nMedienDatei              = 1;
                $oArtikelOptionen->nVariationKombi           = 1;
                $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
                $oArtikelOptionen->nKonfig                   = 1;
                $oArtikelOptionen->nDownload                 = 1;
                $oArtikelOptionen->nMain                     = 1;
                $oArtikelOptionen->nWarenlager               = 1;
                // Warenkorbmatrix nötig? => Varikinder mit Preisen holen
                $oArtikelOptionen->nWarenkorbmatrix = (int)($Einstellungen['artikeldetails']['artikeldetails_warenkorbmatrix_anzeige'] === 'Y');
                // Stückliste nötig? => Stücklistenkomponenten  holen
                $oArtikelOptionen->nStueckliste   = (int)($Einstellungen['artikeldetails']['artikeldetails_stueckliste_anzeigen'] === 'Y');
                $oArtikelOptionen->nProductBundle = (int)($Einstellungen['artikeldetails']['artikeldetails_produktbundle_nutzen'] === 'Y');
                $oArtikel->fuelleArtikel($oArtikelTMP->kArtikel, $oArtikelOptionen, $aFormValues['kKundengruppe'], $aFormValues['kSprache']);
                $oArtikel->kArtikelVariKombi = $oArtikel->kArtikel;

                // Hole EigenschaftWerte zur gewählten VariationKombi
                $oVariationKombiKind_arr = Shop::DB()->query("
                    SELECT teigenschaftkombiwert.kEigenschaftWert, teigenschaftkombiwert.kEigenschaft
                        FROM teigenschaftkombiwert
                        JOIN tartikel 
                            ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                            AND tartikel.kVaterArtikel = " . (int)$oVaterArtikel->kArtikel . "
                            AND tartikel.kArtikel = " . (int)$oArtikel->kArtikelVariKombi . "
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                        ORDER BY tartikel.kArtikel", 2
                );

                if (is_array($oVariationKombiKind_arr) && count($oVariationKombiKind_arr) > 0) {
                    foreach ($oVariationKombiKind_arr as $f => $oVariationKombiKind) {
                        if ($f > 0) {
                            $cVariationKombiKind .= ';' . $oVariationKombiKind->kEigenschaft . '_' . $oVariationKombiKind->kEigenschaftWert;
                        } else {
                            $cVariationKombiKind .= $oVariationKombiKind->kEigenschaft . '_' . $oVariationKombiKind->kEigenschaftWert;
                        }
                    }
                }

                $oArtikel->kArtikel                         = $oVaterArtikel->kArtikel;
                $oArtikel->cVaterVKLocalized                = $oVaterArtikel->Preise->cVKLocalized;
                $oArtikel->nIstVater                        = $oVaterArtikel->nIstVater;
                $oArtikel->fDurchschnittsBewertung          = $oVaterArtikel->fDurchschnittsBewertung;
                $oArtikel->Bewertungen                      = isset($oVaterArtikel->Bewertungen)
                    ? $oVaterArtikel->Bewertungen
                    : null;
                $oArtikel->HilfreichsteBewertung            = isset($oVaterArtikel->HilfreichsteBewertung)
                    ? $oVaterArtikel->HilfreichsteBewertung
                    : null;
                $oArtikel->oVariationKombiVorschau_arr      = isset($oVaterArtikel->oVariationKombiVorschau_arr)
                    ? $oVaterArtikel->oVariationKombiVorschau_arr
                    : null;
                $oArtikel->oVariationDetailPreis_arr        = $oVaterArtikel->oVariationDetailPreis_arr;
                $oArtikel->nVariationKombiNichtMoeglich_arr = $oVaterArtikel->nVariationKombiNichtMoeglich_arr;
                $oArtikel->oVariationKombiVorschauText      = isset($oVaterArtikel->oVariationKombiVorschauText)
                    ? $oVaterArtikel->oVariationKombiVorschauText
                    : null;
                $oArtikel->cVaterURL                        = $oVaterArtikel->cURL;
                $oArtikel->VaterFunktionsAttribute          = $oVaterArtikel->FunktionsAttribute;
                // Kind mit uebergeben
                $oArtikel->kVariKindArtikel = $oArtikel->kArtikelVariKombi;

                $xPost_arr = $_POST;
                baueArtikelDetail($oArtikel, $xPost_arr);

                if (isset($aFormValues['kEditKonfig'])) {
                    $smarty->assign('kEditKonfig', $aFormValues['kEditKonfig']);
                }
                $cArtikelTemplate = 'productdetails/details.tpl';
                if (isset($oArtikel->FunktionsAttribute[FKT_ATTRIBUT_ARTIKELDETAILS_TPL])) {
                    $cArtikelTemplate = $oArtikel->FunktionsAttribute[FKT_ATTRIBUT_ARTIKELDETAILS_TPL];
                }
                if (isset($aFormValues['ek'])) {
                    holeKonfigBearbeitenModus($aFormValues['ek'], $smarty);
                }
                $objResponse->assign('popUps', 'innerHTML', $smarty->fetch('productdetails/popups.tpl'));
                // force running the outputfilter - we have to use display() for this
                ob_start();
                $smarty->display($cArtikelTemplate);
                $articleHtml = ob_get_contents();
                ob_end_clean();
                $objResponse->assign('contentmid', 'innerHTML', $articleHtml);
                if (isset($_SESSION['oVarkombiAuswahl'])) {
                    $objResponse->script("setzeEigenschaftWerte('" . $cVariationKombiKind . "');");
                }
                // Hole alle Eigenschaften des Artikels
                $oEigenschaft_arr = Shop::DB()->query("
                    SELECT *
                        FROM teigenschaft
                        WHERE kArtikel = " . (int)$oVaterArtikel->kArtikel . "
                            AND (cTyp = 'RADIO' OR cTyp = 'SELECTBOX')
                        ORDER BY nSort ASC, cName ASC", 2
                );
                // Durchlaufe alle Eigenschaften
                $oEigenschaftWert_arr = [];
                foreach ($oEigenschaft_arr as $i => $oEigenschaft) {
                    $oEigenschaftWert_arr[$i] = Shop::DB()->selectAll(
                        'teigenschaftwert',
                        'kEigenschaft',
                        (int)$oEigenschaft->kEigenschaft
                    );
                }
                // Baue mögliche Kindartikel
                $oKombiFilter_arr = gibMoeglicheVariationen(
                    $oVaterArtikel->kArtikel,
                    $oEigenschaftWert_arr,
                    $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr
                );
                if (is_array($oKombiFilter_arr) && count($oKombiFilter_arr) > 0) {
                    $objResponse->script('schliesseAlleEigenschaftFelder();');
                    foreach ($oKombiFilter_arr as $oKombiFilter) {
                        $objResponse->script('aVC(' . $oKombiFilter->kEigenschaftWert . ');');
                    }
                }
                $objResponse->script('setBindingsArtikel(1);');
                if ($oArtikel->bHasKonfig && count($oArtikel->oKonfig_arr) > 0) {
                    $cArtikelJSTemplate = 'artikel_konfigurator_js.tpl';
                    if (isset($oArtikel->FunktionsAttribute[FKT_ATTRIBUT_ARTIKELKONFIG_TPL_JS])) {
                        $cArtikelJSTemplate = $oArtikel->FunktionsAttribute[FKT_ATTRIBUT_ARTIKELKONFIG_TPL_JS];
                    }
                    $cKonfigJS = $smarty->fetch('tpl_inc/' . $cArtikelJSTemplate);
                    // remove script header
                    $cKonfigJS = str_replace(['<script type="text/javascript">', '</script>'], '', $cKonfigJS);

                    $objResponse->script($cKonfigJS);
                }

                foreach ($oVariationKombiKind_arr as $f => $oVariationKombiKind) {
                    $kNichtGesetzteEigenschaft = $oVariationKombiKind->kEigenschaft;
                    // hole eigenschaftswerte
                    $kBereitsGesetzt      = [];
                    $oEigenschaftWert_arr = Shop::DB()->selectAll(
                        'teigenschaftwert',
                        'kEigenschaft',
                        (int)$kNichtGesetzteEigenschaft
                    );
                    foreach ($oEigenschaftWert_arr as $oEigenschaftWert) {
                        $kMoeglicheEigenschaftWert_arr                             = $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr;
                        $kMoeglicheEigenschaftWert_arr[$kNichtGesetzteEigenschaft] = $oEigenschaftWert->kEigenschaftWert;

                        $oTMPArtikel = gibArtikelByVariationen(
                            $oVaterArtikel->kArtikel,
                            $kMoeglicheEigenschaftWert_arr
                        );

                        if ($oTMPArtikel && $oTMPArtikel->kArtikel > 0) {
                            if (in_array($oTMPArtikel->kArtikel, $kBereitsGesetzt)) {
                                continue;
                            }
                            $kBereitsGesetzt[] = $oTMPArtikel->kArtikel;

                            $oTestArtikel                                = new Artikel();
                            $oArtikelOptionen->nMerkmale                 = 0;
                            $oArtikelOptionen->nAttribute                = 0;
                            $oArtikelOptionen->nArtikelAttribute         = 0;
                            $oArtikelOptionen->nMedienDatei              = 0;
                            $oArtikelOptionen->nVariationKombi           = 0;
                            $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
                            $oArtikelOptionen->nKonfig                   = 0;
                            $oArtikelOptionen->nDownload                 = 0;
                            $oArtikelOptionen->nMain                     = 0;
                            $oArtikelOptionen->nWarenlager               = 0;

                            $oTestArtikel->fuelleArtikel(
                                $oTMPArtikel->kArtikel,
                                $oArtikelOptionen,
                                Kundengruppe::getCurrent(),
                                Shop::getLanguage()
                            );

                            if ($oTestArtikel->cLagerBeachten === 'Y' &&
                                $oTestArtikel->cLagerKleinerNull !== 'Y' &&
                                $oTestArtikel->fLagerbestand == 0
                            ) {
                                $objResponse->script(
                                    "setzeVarInfo({$oEigenschaftWert->kEigenschaftWert}, 
                                    '{$oTestArtikel->Lageranzeige->AmpelText}', 
                                    '{$oTestArtikel->Lageranzeige->nStatus}');"
                                );
                            }
                        }
                    }
                }
            }
        }
        if (!$bKindVorhanden) {
            $xPost_arr = $_POST;
            baueArtikelDetail($oVaterArtikel, $xPost_arr);
            // force running the outputfilter - we have to use display() for this
            ob_start();
            $smarty->display('productdetails/details.tpl');
            $articleHtml = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('contentmid', 'innerHTML', $articleHtml);
            $objResponse->script('setBindingsArtikel(1);');
            $cVariationKombiKind = "{$kEigenschaft}_{$kEigenschaftWert}";
            $objResponse->script("setzeEigenschaftWerte('" . $cVariationKombiKind . "');");
            // Kein Kind vorhanden, gesetzte Werte zurücksetzen
            $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr = [$kEigenschaft => $kEigenschaftWert];
            $kArtikel                                                  = (int)$aFormValues['a'];
            checkVarkombiDependencies($kArtikel, '', $kEigenschaft, $kEigenschaftWert, ['objResponse' => $objResponse]);
            // Nachricht an den Benutzer
            $cMessage = Shop::Lang()->get('selectionNotAvailable', 'productDetails');
            $objResponse->script("setBuyfieldMessage('{$cMessage}');");
        }
    }

    executeHook(HOOK_TOOLSAJAXSERVER_PAGE_TAUSCHEVARIATIONKOMBI, ['objResponse' => &$objResponse, 'oArtikel' => &$oArtikel]);

    return $objResponse;
}

/**
 * @param Artikel $oArtikel
 * @param array   $xPost_arr
 */
function baueArtikelDetail($oArtikel, $xPost_arr)
{
    global $kKategorie, $AktuelleKategorie, $smarty;

    $conf = Shop::getSettings([
        CONF_BOXEN,
        CONF_GLOBAL,
        CONF_ARTIKELDETAILS,
        CONF_PREISVERLAUF,
        CONF_BEWERTUNG
    ]);
    Shop::setPageType(PAGE_ARTIKEL);
    // Letzten angesehenden Artikel hinzufügen
    if ($conf['boxen']['box_zuletztangesehen_anzeigen'] === 'Y') {
        $boxes = new Boxen();
        $boxes->addRecentlyViewed($oArtikel->kArtikel, $conf['boxen']['box_zuletztangesehen_anzahl']);
    }
    $oArtikel->berechneSieSparenX($conf['artikeldetails']['sie_sparen_x_anzeigen']);
    $Artikelhinweise = [];
    baueArtikelhinweise();

    if (isset($xPost_arr['fragezumprodukt']) && (int)$xPost_arr['fragezumprodukt'] === 1) {
        bearbeiteFrageZumProdukt();
    } elseif (isset($xPost_arr['benachrichtigung_verfuegbarkeit']) && (int)$xPost_arr['benachrichtigung_verfuegbarkeit'] === 1) {
        bearbeiteBenachrichtigung();
    }
    //url
    $requestURL = baueURL($oArtikel, URLART_ARTIKEL);
    $sprachURL  = ($oArtikel->cSprachURL_arr !== null) ? $oArtikel->cSprachURL_arr : baueSprachURLS($oArtikel, URLART_ARTIKEL);
    //hole aktuelle Kategorie, falls eine gesetzt
    $kKategorie             = $oArtikel->gibKategorie();
    $AktuelleKategorie      = new Kategorie($kKategorie);
    $AufgeklappteKategorien = new KategorieListe();
    $AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);
    $startKat             = new Kategorie();
    $startKat->kKategorie = 0;
    // Bewertungen holen
    $bewertung_seite  = verifyGPCDataInteger('btgseite');
    $bewertung_sterne = verifyGPCDataInteger('btgsterne');
    // Hat Artikel einen Preisverlauf?
    $smarty->assign('bPreisverlauf', true);
    if ($conf['preisverlauf']['preisverlauf_anzeigen'] === 'Y') {
        $kArtikel      = (isset($oArtikel->kVariKindArtikel) && $oArtikel->kVariKindArtikel > 0)
            ? $oArtikel->kVariKindArtikel
            : $oArtikel->kArtikel;
        $oPreisverlauf = new Preisverlauf();
        $oPreisverlauf = $oPreisverlauf->gibPreisverlauf(
            $kArtikel,
            $oArtikel->Preise->kKundengruppe,
            (int)$conf['preisverlauf']['preisverlauf_anzahl_monate']
        );
        if (count($oPreisverlauf) < 2) {
            $smarty->assign('bPreisverlauf', false);
        }
    }
    // Sortierung der Bewertungen
    $nSortierung = verifyGPCDataInteger('sortierreihenfolge');
    // Dient zum aufklappen des Tabmenüs
    $bewertung_anzeigen    = verifyGPCDataInteger('bewertung_anzeigen');
    $BewertungsTabAnzeigen = 0;
    if ($bewertung_seite || $bewertung_sterne || $bewertung_anzeigen) {
        $BewertungsTabAnzeigen = 1;
    }
    if ($bewertung_seite == 0) {
        $bewertung_seite = 1;
    }
    // Bewertungen holen
    if (!isset($oArtikel->Bewertungen)) {
        $oArtikel->holeBewertung(
            $_SESSION['kSprache'],
            $conf['bewertung']['bewertung_anzahlseite'],
            $bewertung_seite,
            $bewertung_sterne,
            $conf['bewertung']['bewertung_freischalten'],
            $nSortierung
        );
        $oArtikel->holehilfreichsteBewertung(Shop::getLanguage());
    }
    $oArtikel->Bewertungen->Sortierung = $nSortierung;
    //$nAnzahlBewertungen = $oArtikel->Bewertungen->oBewertungGesamt->nAnzahl;
    $nAnzahlBewertungen = ($bewertung_sterne == 0) ?
        $oArtikel->Bewertungen->nAnzahlSprache :
        $oArtikel->Bewertungen->nSterne_arr[5 - $bewertung_sterne];
    // Baue Blätter Navigation
    $oBlaetterNavi = baueBewertungNavi(
        $bewertung_seite,
        $bewertung_sterne,
        $nAnzahlBewertungen,
        $conf['bewertung']['bewertung_anzahlseite']
    );
    // Baue Gewichte für Smarty
    $oTrennzeichen = Trennzeichen::getUnit(JTLSEPARATER_WEIGHT, Shop::getLanguage());
    $shopURL       = Shop::getURL() . '/';
    baueGewicht([$oArtikel], $oTrennzeichen->getDezimalstellen(), $oTrennzeichen->getDezimalstellen());

    $smarty->assign('Navigation', createNavigation('ARTIKEL', $AufgeklappteKategorien, $oArtikel))
           ->assign('Ueberschrift', $oArtikel->cName)
           ->assign('UeberschriftKlein', $oArtikel->cKurzBeschreibung)
           ->assign('UVPlocalized', gibPreisStringLocalized($oArtikel->fUVP))
           ->assign('UVPBruttolocalized', gibPreisStringLocalized($oArtikel->fUVPBrutto))
           ->assign('Einstellungen', $conf)
           ->assign('Artikel', $oArtikel)
           ->assign('Xselling', isset($oArtikel->kVariKindArtikel)
               ? gibArtikelXSelling($oArtikel->kVariKindArtikel)
               : null)
           ->assign('requestURL', $requestURL)
           ->assign('sprachURL', $sprachURL)
           ->assign('Artikelhinweise', $Artikelhinweise)
           ->assign('verfuegbarkeitsBenachrichtigung', gibVerfuegbarkeitsformularAnzeigen($oArtikel, $conf['artikeldetails']['benachrichtigung_nutzen']))
           ->assign('code_fragezumprodukt', generiereCaptchaCode($conf['artikeldetails']['produktfrage_abfragen_captcha']))
           ->assign('code_benachrichtigung_verfuegbarkeit', generiereCaptchaCode($conf['artikeldetails']['benachrichtigung_abfragen_captcha']))
           ->assign('ProdukttagHinweis', bearbeiteProdukttags($oArtikel))
           ->assign('ProduktTagging', $oArtikel->tags)
           ->assign('BlaetterNavi', $oBlaetterNavi)
           ->assign('BewertungsTabAnzeigen', $BewertungsTabAnzeigen)
           ->assign('hinweis', isset($cHinweis) ? $cHinweis : null)
           ->assign('fehler', isset($cFehler) ? $cFehler : null)
           ->assign('PFAD_IMAGESLIDER', $shopURL . PFAD_IMAGESLIDER)
           ->assign('PFAD_MEDIAFILES', $shopURL . PFAD_MEDIAFILES)
           ->assign('PFAD_FLASHPLAYER', $shopURL . PFAD_FLASHPLAYER)
           ->assign('PFAD_BILDER', $shopURL . PFAD_BILDER)
           ->assign('KONFIG_ITEM_TYP_ARTIKEL', KONFIG_ITEM_TYP_ARTIKEL)
           ->assign('KONFIG_ITEM_TYP_SPEZIAL', KONFIG_ITEM_TYP_SPEZIAL)
           ->assign('KONFIG_ANZEIGE_TYP_CHECKBOX', KONFIG_ANZEIGE_TYP_CHECKBOX)
           ->assign('KONFIG_ANZEIGE_TYP_RADIO', KONFIG_ANZEIGE_TYP_RADIO)
           ->assign('KONFIG_ANZEIGE_TYP_DROPDOWN', KONFIG_ANZEIGE_TYP_DROPDOWN)
           ->assign('KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI', KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI)
           ->assign('FKT_ATTRIBUT_ARTIKELDETAILS_TPL', FKT_ATTRIBUT_ARTIKELDETAILS_TPL)
           ->assign('FKT_ATTRIBUT_ARTIKELKONFIG_TPL', FKT_ATTRIBUT_ARTIKELKONFIG_TPL)
           ->assign('FKT_ATTRIBUT_ARTIKELKONFIG_TPL_JS', FKT_ATTRIBUT_ARTIKELKONFIG_TPL_JS);

    require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    //Meta
    $smarty->assign('meta_title', $oArtikel->getMetaTitle())
           ->assign('meta_description', $oArtikel->getMetaDescription($AufgeklappteKategorien))
           ->assign('meta_keywords', $oArtikel->getMetaKeywords());

    executeHook(HOOK_TOOLSAJAXSERVER_PAGE_ARTIKELDETAIL);
}

/**
 * @deprecated since 4.05
 * @param int $kMerkmalWert
 * @param int $kAuswahlAssistentFrage
 * @param int $nFrage
 * @param int $kKategorie
 * @return xajaxResponse
 */
function setSelectionWizardAnswerAjax($kMerkmalWert, $kAuswahlAssistentFrage, $nFrage, $kKategorie)
{
    global $smarty;

    require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'auswahlassistent_ext_inc.php';

    $Einstellungen = Shop::getSettings([
        CONF_GLOBAL,
        CONF_RSS,
        CONF_NAVIGATIONSFILTER,
        CONF_ARTIKELUEBERSICHT,
        CONF_AUSWAHLASSISTENT
    ]);
    $objResponse             = new xajaxResponse();
    $bMerkmalFilterVorhanden = false;
    $bFragenEnde             = false;
    processSelectionWizard(
        $kMerkmalWert,
        $nFrage,
        $kKategorie,
        $bFragenEnde,
        $oSuchergebnisse,
        $NaviFilter,
        $bMerkmalFilterVorhanden
    );
    if (!$bFragenEnde && $bMerkmalFilterVorhanden && $oSuchergebnisse->GesamtanzahlArtikel > 1) {
        $smarty->assign('Einstellungen', $Einstellungen)
               ->assign('NaviFilter', $NaviFilter)
               ->assign('oAuswahlAssistent', $_SESSION['AuswahlAssistent']->oAuswahlAssistent);
        $objResponse->assign('selection_wizard', 'innerHTML', $smarty->fetch('productwizard/form.tpl'));
        $objResponse->script("aaDeleteSelectBTN();");
        foreach ($_SESSION['AuswahlAssistent']->oAuswahl_arr as $i => $oAuswahl) {
            $cAusgabe = $oAuswahl->cWert;
            if ($_SESSION['AuswahlAssistent']->nFrage > $i) {
                $cAusgabe .= " <span class='edit fa fa-edit list-group-item-text' title='" . Shop::Lang()->get('edit', 'global') .
                    "' onClick='return resetSelectionWizardAnswer(" . $i . ", " . $kKategorie . ");'></span>";
            }
            if ($i != $_SESSION['AuswahlAssistent']->nFrage) {
                $objResponse->assign('answer_' . $_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$i]->kAuswahlAssistentFrage, 'innerHTML', $cAusgabe);
            }
        }
    } elseif (!$bFragenEnde || $oSuchergebnisse->GesamtanzahlArtikel == 1 || !$bMerkmalFilterVorhanden) { // Abbruch
        if (!$kKategorie) {
            unset($_POST['mf1']);
        }
        $cParameter_arr['MerkmalFilter_arr'] = setzeMerkmalFilter();
        $NaviFilter                          = Shop::buildNaviFilter($cParameter_arr, $NaviFilter);
        $objResponse->script("window.location.href='" . StringHandler::htmlentitydecode(gibNaviURL($NaviFilter, true, null)) . "';");

        unset($_SESSION['AuswahlAssistent']);
    }

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param int $nFrage
 * @param int $kKategorie
 * @return xajaxResponse
 */
function resetSelectionWizardAnswerAjax($nFrage, $kKategorie)
{
    global $smarty;

    require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'auswahlassistent_ext_inc.php';

    $Einstellungen = Shop::getSettings([
        CONF_GLOBAL,
        CONF_RSS,
        CONF_NAVIGATIONSFILTER,
        CONF_ARTIKELUEBERSICHT,
        CONF_AUSWAHLASSISTENT
    ]);
    $objResponse   = new xajaxResponse();

    $_SESSION['AuswahlAssistent']->nFrage            = $nFrage;
    $_SESSION['AuswahlAssistent']->oAuswahlAssistent = AuswahlAssistent::getGroupsByLocation(
        $_SESSION['AuswahlAssistent']->oAuswahlAssistentOrt->cKey,
        $_SESSION['AuswahlAssistent']->oAuswahlAssistentOrt->kKey,
        Shop::getLanguage()
    );
    // Bereits ausgewaehlte Antworten loeschen
    foreach ($_SESSION['AuswahlAssistent']->oAuswahl_arr as $i => $oAuswahl) {
        if ($i >= $nFrage) {
            unset($_SESSION['AuswahlAssistent']->oAuswahl_arr[$i], $_SESSION['AuswahlAssistent']->kMerkmalGesetzt_arr[$i]);
        }
    }
    // Filter
    //@todo: undefined vars..:
    baueFilterSelectionWizard($kKategorie, $NaviFilter, $FilterSQL, $oSuchergebnisse, $nArtikelProSeite, $nLimitN);
    filterSelectionWizard($oSuchergebnisse->MerkmalFilter, $bMerkmalFilterVorhanden);
    $smarty->assign('Einstellungen', $Einstellungen)
           ->assign('NaviFilter', $NaviFilter)
           ->assign('oAuswahlAssistent', $_SESSION['AuswahlAssistent']->oAuswahlAssistent);
    $objResponse->assign('selection_wizard', 'innerHTML', $smarty->fetch('productwizard/form.tpl'));
    $objResponse->script('aaDeleteSelectBTN();');
    foreach ($_SESSION['AuswahlAssistent']->oAuswahl_arr as $i => $oAuswahl) {
        if ($i < $_SESSION['AuswahlAssistent']->nFrage) {
            $objResponse->assign(
                'answer_' . $_SESSION['AuswahlAssistent']->oAuswahlAssistent->oAuswahlAssistentFrage_arr[$i]->kAuswahlAssistentFrage,
                'innerHTML', $oAuswahl->cWert . " <div class='edit' onClick='return resetSelectionWizardAnswer(" . $i . ", " . $kKategorie . ");'></div>"
            );
        }
    }

    return $objResponse;
}

/**
 * @deprecated since 4.05
 * @param int   $kVaterArtikel
 * @param array $kGesetzteEigeschaftWert_arr
 * @return mixed
 */
function getValidVarkombis($kVaterArtikel, $kGesetzteEigeschaftWert_arr)
{
    $kGesetzteEigeschaftWert_arr = array_map('intval', $kGesetzteEigeschaftWert_arr);
    $oKombiFilter_arr            = Shop::DB()->query("
        SELECT DISTINCT(teigenschaftkombiwert.kEigenschaftWert) AS kEigenschaftWert
            FROM
            (
                SELECT teigenschaftkombiwert.kEigenschaftKombi
                FROM tartikel
                JOIN teigenschaftkombiwert 
                    ON teigenschaftkombiwert.kEigenschaftKombi = tartikel.kEigenschaftKombi
                WHERE tartikel.kVaterartikel = " . (int)$kVaterArtikel . "
                AND teigenschaftkombiwert.kEigenschaftWert IN (" . implode(',', $kGesetzteEigeschaftWert_arr) . ")
            GROUP BY teigenschaftkombiwert.kEigenschaftKombi
            HAVING count(*) = " . count($kGesetzteEigeschaftWert_arr) . "
            ) AS sub
            JOIN teigenschaftkombiwert 
                ON teigenschaftkombiwert.kEigenschaftKombi = sub.kEigenschaftKombi", 2
    );

    return $oKombiFilter_arr;
}

/**
 * @param int    $kVaterArtikel
 * @param string $cVaterURL
 * @param int    $kEigenschaft
 * @param int    $kEigenschaftWert
 * @param array  $oParam_arr
 * @return xajaxResponse
 */
function checkVarkombiDependencies($kVaterArtikel, $cVaterURL, $kEigenschaft = 0, $kEigenschaftWert = 0, $oParam_arr = [])
{
    $objResponse = isset($oParam_arr['objResponse'])
        ? $oParam_arr['objResponse']
        : new xajaxResponse();

    $kVaterArtikel    = (int)$kVaterArtikel;
    $kEigenschaft     = (int)$kEigenschaft;
    $kEigenschaftWert = (int)$kEigenschaftWert;

    $objResponse->script('loescheVarInfo();');
    $objResponse->script('hideBuyfieldMessage();');

    if ($kVaterArtikel > 0) {
        // Grad geklickter Eigenschaftswert in die Session aufnehmen
        if ($kEigenschaft > 0 && $kEigenschaftWert > 0) {
            $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr[$kEigenschaft] = $kEigenschaftWert;
        }
        // Hole alle Eigenschaften des Artikels
        $oEigenschaft_arr = Shop::DB()->query("
          SELECT * 
            FROM teigenschaft 
            WHERE kArtikel = " . (int)$kVaterArtikel . " 
                AND (cTyp = 'RADIO' OR cTyp = 'SELECTBOX') 
            ORDER BY nSort ASC, cName ASC", 2
        );
        // Durchlaufe alle Eigenschaften
        $oEigenschaftWert_arr = [];
        foreach ($oEigenschaft_arr as $i => $oEigenschaft) {
            $oEigenschaftWert_arr[$i] = Shop::DB()->selectAll(
                'teigenschaftwert',
                'kEigenschaft',
                (int)$oEigenschaft->kEigenschaft
            );
        }
        // Baue mögliche Kindartikel
        $oKombiFilter_arr = gibMoeglicheVariationen(
            $kVaterArtikel,
            $oEigenschaftWert_arr,
            $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr
        );
        if (is_array($oKombiFilter_arr) && count($oKombiFilter_arr) > 0) {
            $objResponse->script("schliesseAlleEigenschaftFelder();");
            foreach ($oKombiFilter_arr as $oKombiFilter) {
                $objResponse->script("aVC(" . $oKombiFilter->kEigenschaftWert . ");");
            }
        }
        // Wenn nur noch eine Variation fehlt
        if (isset($_SESSION['oVarkombiAuswahl']->nVariationOhneFreifeldAnzahl) &&
            $_SESSION['oVarkombiAuswahl']->nVariationOhneFreifeldAnzahl ==
            count($_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr) + 1
        ) {
            $oArtikel                                    = new Artikel();
            $oArtikelOptionen                            = new stdClass();
            $oArtikelOptionen->nMerkmale                 = 0;
            $oArtikelOptionen->nAttribute                = 0;
            $oArtikelOptionen->nArtikelAttribute         = 0;
            $oArtikelOptionen->nMedienDatei              = 0;
            $oArtikelOptionen->nVariationKombi           = 1;
            $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
            $oArtikelOptionen->nKonfig                   = 0;
            $oArtikelOptionen->nDownload                 = 0;
            $oArtikelOptionen->nMain                     = 1;
            $oArtikelOptionen->nWarenlager               = 1;

            $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen, Kundengruppe::getCurrent(), Shop::getLanguage());

            $kGesetzeEigenschaft_arr       = array_keys($_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr);
            $kNichtGesetzteEigenschaft_arr = array_values(array_diff($oArtikel->kEigenschaftKombi_arr, $kGesetzeEigenschaft_arr));
            $kNichtGesetzteEigenschaft     = (int)$kNichtGesetzteEigenschaft_arr[0];

            // hole eigenschaftswerte
            $oEigenschaftWert_arr = Shop::DB()->selectAll('teigenschaftwert', 'kEigenschaft', (int)$kNichtGesetzteEigenschaft);
            foreach ($oEigenschaftWert_arr as $oEigenschaftWert) {
                $kMoeglicheEigenschaftWert_arr                             = $_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr;
                $kMoeglicheEigenschaftWert_arr[$kNichtGesetzteEigenschaft] = $oEigenschaftWert->kEigenschaftWert;

                $oTMPArtikel = gibArtikelByVariationen($kVaterArtikel, $kMoeglicheEigenschaftWert_arr);

                if ($oTMPArtikel && $oTMPArtikel->kArtikel > 0) {
                    $oTestArtikel                                = new Artikel();
                    $oArtikelOptionen->nMerkmale                 = 0;
                    $oArtikelOptionen->nAttribute                = 0;
                    $oArtikelOptionen->nArtikelAttribute         = 0;
                    $oArtikelOptionen->nMedienDatei              = 0;
                    $oArtikelOptionen->nVariationKombi           = 0;
                    $oArtikelOptionen->nKeinLagerbestandBeachten = 1;
                    $oArtikelOptionen->nKonfig                   = 0;
                    $oArtikelOptionen->nDownload                 = 0;
                    $oArtikelOptionen->nMain                     = 0;
                    $oArtikelOptionen->nWarenlager               = 0;

                    $oTestArtikel->fuelleArtikel(
                        $oTMPArtikel->kArtikel,
                        $oArtikelOptionen,
                        Kundengruppe::getCurrent(),
                        Shop::getLanguage()
                    );

                    if ($oTestArtikel->cLagerBeachten === 'Y' &&
                        $oTestArtikel->cLagerKleinerNull !== 'Y' &&
                        $oTestArtikel->fLagerbestand == 0
                    ) {
                        $objResponse->script(
                            "setzeVarInfo({$oEigenschaftWert->kEigenschaftWert}, 
                            '{$oTestArtikel->Lageranzeige->AmpelText}', 
                            '{$oTestArtikel->Lageranzeige->nStatus}');"
                        );
                    }
                }
            }
        }
        // Alle Variationen ausgewaehlt? => Ajax Call und Kind laden
        if (count($oParam_arr) === 0 &&
            $_SESSION['oVarkombiAuswahl']->nVariationOhneFreifeldAnzahl ==
                count($_SESSION['oVarkombiAuswahl']->kGesetzteEigeschaftWert_arr)
        ) {
            $objResponse->script("doSwitchVarkombi('{$kEigenschaft}', '{$kEigenschaftWert}');");
        }
    }

    if (count($oParam_arr) === 0) {
        return $objResponse;
    }
}

/**
 * @param int   $kVaterArtikel
 * @param array $oEigenschaftWert_arr
 * @param array $kGesetzteEigeschaftWert_arr
 * @return array
 */
function gibMoeglicheVariationen($kVaterArtikel, $oEigenschaftWert_arr, $kGesetzteEigeschaftWert_arr)
{
    $oMoeglicheEigenschaften_arr = [];

    foreach ($oEigenschaftWert_arr as $group) {
        $i    = 2;
        $cSQL = [];
        foreach ($kGesetzteEigeschaftWert_arr as $kEigenschaft => $kEigenschaftWert) {
            if ($group[0]->kEigenschaft != $kEigenschaft) {
                $kEigenschaftWert = (int)$kEigenschaftWert;
                $cSQL[] = "INNER JOIN teigenschaftkombiwert e{$i} 
                    ON e1.kEigenschaftKombi = e{$i}.kEigenschaftKombi 
                    AND e{$i}.kEigenschaftWert = {$kEigenschaftWert}";
                $i++;
            }
        }
        $cSQLStr          = implode(' ', $cSQL);
        $oEigenschaft_arr = Shop::DB()->query("
            SELECT e1.* FROM teigenschaftkombiwert e1 " .
                $cSQLStr . "
                WHERE e1.kEigenschaft = " . (int)$group[0]->kEigenschaft . "
                GROUP BY e1.kEigenschaft, e1.kEigenschaftWert", 2
        );
        $oMoeglicheEigenschaften_arr = array_merge($oMoeglicheEigenschaften_arr, $oEigenschaft_arr);
    }

    return $oMoeglicheEigenschaften_arr;
}

/**
 * @param int   $kArtikel
 * @param array $kVariationKombi_arr
 * @return null|stdClass
 */
function gibArtikelByVariationen($kArtikel, $kVariationKombi_arr)
{
    $cSQL1 = '';
    $cSQL2 = '';
    if (is_array($kVariationKombi_arr) && count($kVariationKombi_arr) > 0) {
        $j = 0;
        foreach ($kVariationKombi_arr as $i => $kVariationKombi) {
            if ($j > 0) {
                $cSQL1 .= ',' . (int)$i;
                $cSQL2 .= ',' . (int)$kVariationKombi;
            } else {
                $cSQL1 .= (int)$i;
                $cSQL2 .= (int)$kVariationKombi;
            }
            $j++;
        }
    } else {
        return null;
    }

    return Shop::DB()->query("
        SELECT tartikel.kArtikel
            FROM teigenschaftkombiwert
            JOIN tartikel 
                ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe . "
            WHERE teigenschaftkombiwert.kEigenschaft IN (" . $cSQL1 . ")
                AND teigenschaftkombiwert.kEigenschaftWert IN (" . $cSQL2 . ")
                AND tartikelsichtbarkeit.kArtikel IS NULL
                AND tartikel.kVaterArtikel = " . (int)$kArtikel . "
            GROUP BY tartikel.kArtikel
            HAVING count(*) = " . count($kVariationKombi_arr), 1
    );
}
/** @global xajax $xajax */
$xajax->processRequest();
header('Content-Type:text/html;charset=' . JTL_CHARSET . ';');
