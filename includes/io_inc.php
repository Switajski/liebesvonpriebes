<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_CLASSES  . 'class.JTL-Shop.Warenkorb.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';

$io = IO::getInstance();

$io->register('suggestions')
   ->register('pushToBasket')
   ->register('pushToComparelist')
   ->register('removeFromComparelist')
   ->register('checkDependencies')
   ->register('checkVarkombiDependencies')
   ->register('generateToken')
   ->register('buildConfiguration')
   ->register('getBasketItems')
   ->register('getCategoryMenu')
   ->register('getRegionsByCountry')
   ->register('checkDeliveryCountry')
   ->register('setSelectionWizardAnswers')
   ->register('getCitiesByZip');

/**
 * @param string $keyword
 * @return array
 */
function suggestions($keyword)
{
    global $Einstellungen, $smarty;

    $results    = [];
    $language   = Shop::getLanguage();
    $maxResults = ((int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl'] > 0)
        ? (int)$Einstellungen['artikeluebersicht']['suche_ajax_anzahl']
        : 10;
    if (strlen($keyword) >= 2) {
        $results = Shop::DB()->executeQueryPrepared("
            SELECT cSuche AS keyword, nAnzahlTreffer AS quantity
              FROM tsuchanfrage
              WHERE SOUNDEX(cSuche) LIKE CONCAT(TRIM(TRAILING '0' FROM SOUNDEX(:keyword)), '%')
                  AND nAktiv = 1
                  AND kSprache = :lang
            ORDER BY CASE
                WHEN cSuche = :keyword THEN 0
                WHEN cSuche LIKE CONCAT(:keyword, '%') THEN 1
                WHEN cSuche LIKE CONCAT('%', :keyword, '%') THEN 2
                ELSE 99
                END, nAnzahlGesuche DESC, cSuche
            LIMIT :maxres",
            [
                'keyword' => $keyword,
                'maxres'  => $maxResults,
                'lang'    => $language
            ],
            2
        );

        if (is_array($results) && count($results) > 0) {
            foreach ($results as &$result) {
                $result->suggestion = utf8_encode($smarty->assign('result', $result)->fetch('snippets/suggestion.tpl'));
                $result->keyword    = utf8_encode($result->keyword);
            }
        }
    }

    return $results;
}

/**
 * @param string $cityQuery
 * @param string $country
 * @param string $zip
 * @return array
 */
function getCitiesByZip($cityQuery, $country, $zip)
{
    $results    = [];
    if (!empty($country) && !empty($zip)) {
        $cityQuery = "%" . StringHandler::filterXSS($cityQuery) . "%";
        $country   = StringHandler::filterXSS($country);
        $zip       = StringHandler::filterXSS($zip);
        $cities = Shop::DB()->queryPrepared("
            SELECT cOrt
            FROM tplz
            WHERE cLandISO = :country
                AND cPLZ = :zip
                AND cOrt LIKE :cityQuery",
            ['country' => $country, 'zip' => $zip, 'cityQuery' => $cityQuery],
            2);
        foreach ($cities as $result) {
            $results[] = utf8_encode($result->cOrt);
        }
    }

    return $results;
}

/**
 * @param int          $kArtikel
 * @param int|float    $anzahl
 * @param string|array $oEigenschaftwerte_arr
 * @return IOResponse
 */
function pushToBasket($kArtikel, $anzahl, $oEigenschaftwerte_arr = '')
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    global $Einstellungen, $smarty;

    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Sprache.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'boxen.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $GLOBALS['oSprache'] = Sprache::getInstance();

    $kArtikel = (int)$kArtikel;
    if ($anzahl > 0 && $kArtikel > 0) {
        $Artikel                             = new Artikel();
        $oArtikelOptionen                    = new stdClass();
        $oArtikelOptionen->nMerkmale         = 1;
        $oArtikelOptionen->nAttribute        = 1;
        $oArtikelOptionen->nArtikelAttribute = 1;
        $oArtikelOptionen->nDownload         = 1;
        $Artikel->fuelleArtikel($kArtikel, $oArtikelOptionen);
        // Falls der Artikel ein Variationskombikind ist, hole direkt seine Eigenschaften
        if ($Artikel->kEigenschaftKombi > 0  || $Artikel->nIstVater === 1) {
            // Variationskombi-Artikel
            $_POST['eigenschaftwert'] = $oEigenschaftwerte_arr['eigenschaftwert'];
            $oEigenschaftwerte_arr    = ArtikelHelper::getSelectedPropertiesForVarCombiArticle($kArtikel);
        } elseif (isset($oEigenschaftwerte_arr['eigenschaftwert']) && is_array($oEigenschaftwerte_arr['eigenschaftwert'])) {
            // einfache Variation - keine Varkombi
            $_POST['eigenschaftwert'] = $oEigenschaftwerte_arr['eigenschaftwert'];
            $oEigenschaftwerte_arr    = ArtikelHelper::getSelectedPropertiesForArticle($kArtikel);
        }

        if ((int)$anzahl != $anzahl && $Artikel->cTeilbar !== 'Y') {
            $anzahl = max((int)$anzahl, 1);
        }
        // Prüfung
        $errors = pruefeFuegeEinInWarenkorb($Artikel, $anzahl, $oEigenschaftwerte_arr);

        if (count($errors) > 0) {
            $localizedErrors = baueArtikelhinweise($errors, true, $Artikel, $anzahl);

            $oResponse->nType  = 0;
            $oResponse->cLabel = Shop::Lang()->get('basket', 'global');
            $oResponse->cHints = utf8_convert_recursive($localizedErrors);
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
        /** @var array('Warenkorb') $_SESSION['Warenkorb'] */
        $cart = $_SESSION['Warenkorb'];
        WarenkorbHelper::addVariationPictures($cart);
        /** @var Warenkorb $cart */
        $cart->fuegeEin($kArtikel, $anzahl, $oEigenschaftwerte_arr)
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
        // Wenn Kupon vorhanden und prozentual auf ganzen Warenkorb,
        // dann verwerfen und neu anlegen
        altenKuponNeuBerechnen();
        setzeLinks();
        // Persistenter Warenkorb
        if (!isset($_POST['login'])) {
            fuegeEinInWarenkorbPers($kArtikel, $anzahl, $oEigenschaftwerte_arr);
        }
        $boxes         = Boxen::getInstance();
        $pageType      = (Shop::getPageType() !== null) ? Shop::getPageType() : PAGE_UNBEKANNT;
        $boxesToShow   = $boxes->build($pageType, true)->render();
        $warensumme[0] = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true));
        $warensumme[1] = gibPreisStringLocalized($cart->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], false));
        $smarty->assign('Boxen', $boxesToShow)
               ->assign('WarenkorbWarensumme', $warensumme);

        $kKundengruppe = (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0)
            ? $_SESSION['Kunde']->kKundengruppe
            : $_SESSION['Kundengruppe']->kKundengruppe;
        $oXSelling     = gibArtikelXSelling($kArtikel, $Artikel->nIstVater > 0);

        $smarty->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString(
            gibVersandkostenfreiAb($kKundengruppe),
            $cart->gibGesamtsummeWarenExt(
                [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_KUPON, C_WARENKORBPOS_TYP_NEUKUNDENKUPON],
                true
            )))
               ->assign('zuletztInWarenkorbGelegterArtikel', $cart->gibLetztenWKArtikel())
               ->assign('fAnzahl', $anzahl)
               ->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise)
               ->assign('Einstellungen', $Einstellungen)
               ->assign('Xselling', $oXSelling)
               ->assign('WarensummeLocalized', $cart->gibGesamtsummeWarenLocalized())
               ->assign('Steuerpositionen', $cart->gibSteuerpositionen());

        $oResponse->nType           = 2;
        $oResponse->cWarenkorbText  = utf8_encode(lang_warenkorb_warenkorbEnthaeltXArtikel($_SESSION['Warenkorb']));
        $oResponse->cWarenkorbLabel = utf8_encode(lang_warenkorb_warenkorbLabel($_SESSION['Warenkorb']));
        $oResponse->cPopup          = utf8_encode($smarty->fetch('productdetails/pushed.tpl'));
        $oResponse->cWarenkorbMini  = utf8_encode($smarty->fetch('basket/cart_dropdown.tpl'));
        $oResponse->oArtikel        = utf8_convert_recursive($Artikel, true);
        $oResponse->cNotification   = utf8_encode(Shop::Lang()->get('basketAllAdded', 'messages'));

        $objResponse->script('this.response = ' . json_encode($oResponse) . ';');
        // Kampagne
        if (isset($_SESSION['Kampagnenbesucher'])) {
            setzeKampagnenVorgang(KAMPAGNE_DEF_WARENKORB, $kArtikel, $anzahl); // Warenkorb
        }

        if ($GLOBALS['GlobaleEinstellungen']['global']['global_warenkorb_weiterleitung'] === 'Y') {
            $linkHelper           = LinkHelper::getInstance();
            $oResponse->nType     = 1;
            $oResponse->cLocation = $linkHelper->getStaticRoute('warenkorb.php');
            $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

            return $objResponse;
        }
    }

    return $objResponse;
}

/**
 * @param int $kArtikel
 * @return IOResponse
 */
function pushToComparelist($kArtikel)
{
    global $Einstellungen;
    $kArtikel = (int)$kArtikel;
    if (!isset($Einstellungen['vergleichsliste'])) {
        if (isset($Einstellungen)) {
            $Einstellungen = array_merge($Einstellungen, Shop::getSettings([CONF_VERGLEICHSLISTE]));
        } else {
            $Einstellungen = Shop::getSettings([CONF_VERGLEICHSLISTE]);
        }
    }

    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $_POST['Vergleichsliste'] = 1;
    $_POST['a']               = $kArtikel;

    checkeWarenkorbEingang();
    $error             = Shop::Smarty()->getTemplateVars('fehler');
    $notice            = Shop::Smarty()->getTemplateVars('hinweis');
    $oResponse->nType  = 2;
    $oResponse->nCount = count($_SESSION['Vergleichsliste']->oArtikel_arr);
    $oResponse->cTitle = utf8_encode(Shop::Lang()->get('compare', 'global'));
    $buttons           = [
        (object)[
            'href'    => '#',
            'fa'      => 'fa fa-arrow-circle-right',
            'title'   => Shop::Lang()->get('continueShopping', 'checkout'),
            'primary' => true,
            'dismiss' => 'modal'
        ]
    ];

    if ($oResponse->nCount > 1) {
        array_unshift($buttons, (object)[
            'href'  => 'vergleichsliste.php',
            'fa'    => 'fa-tasks',
            'title' => Shop::Lang()->get('compare', 'global')
        ]);
    }

    $oResponse->cNotification = utf8_encode(
        Shop::Smarty()
            ->assign('type', empty($error) ? 'info' : 'danger')
            ->assign('body', empty($error) ? $notice : $error)
            ->assign('buttons', $buttons)
            ->fetch('snippets/notification.tpl')
    );
    $oResponse->cNavBadge = '';
    if ($oResponse->nCount > 1) {
        $oResponse->cNavBadge = utf8_encode(
            Shop::Smarty()
                ->assign('Einstellungen', $Einstellungen)
                ->fetch('layout/header_shop_nav_compare.tpl')
        );
    }

    $boxes = Boxen::getInstance();
    $oBox  = $boxes->prepareBox(BOX_VERGLEICHSLISTE, new stdClass());
    $oResponse->cBoxContainer = utf8_encode(
        Shop::Smarty()
            ->assign('Einstellungen', $Einstellungen)
            ->assign('oBox', $oBox)
            ->fetch('boxes/box_comparelist.tpl')
    );

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param int $kArtikel
 * @return IOResponse
 */
function removeFromComparelist($kArtikel)
{
    global $Einstellungen;

    $kArtikel = (int)$kArtikel;
    if (!isset($Einstellungen['vergleichsliste'])) {
        if (isset($Einstellungen)) {
            $Einstellungen = array_merge($Einstellungen, Shop::getSettings([CONF_VERGLEICHSLISTE]));
        } else {
            $Einstellungen = Shop::getSettings([CONF_VERGLEICHSLISTE]);
        }
    }

    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $_GET['Vergleichsliste'] = 1;
    $_GET['vlplo']           = $kArtikel;

    Session::getInstance()->setStandardSessionVars();
    $oResponse->nType     = 2;
    $oResponse->nCount    = count($_SESSION['Vergleichsliste']->oArtikel_arr);
    $oResponse->cTitle    = utf8_encode(Shop::Lang()->get('compare', 'global'));
    $oResponse->cNavBadge = '';

    if ($oResponse->nCount > 1) {
        $oResponse->cNavBadge = utf8_encode(
            Shop::Smarty()
                ->assign('Einstellungen', $Einstellungen)
                ->fetch('layout/header_shop_nav_compare.tpl')
        );
    }

    $boxes = Boxen::getInstance();
    $oBox  = $boxes->prepareBox(BOX_VERGLEICHSLISTE, new stdClass());
    $oResponse->cBoxContainer = utf8_encode(
        Shop::Smarty()
            ->assign('Einstellungen', $Einstellungen)
            ->assign('oBox', $oBox)
            ->fetch('boxes/box_comparelist.tpl')
    );

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param int $nTyp - 0 = Template, 1 = Object
 * @return IOResponse
 */
function getBasketItems($nTyp)
{
    /** @var array('Warenkorb' => Warenkorb) $_SESSION */
    global $Einstellungen, $smarty;

    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

    $oResponse   = new stdClass();
    $objResponse = new IOResponse();

    $GLOBALS['oSprache'] = Sprache::getInstance();
    WarenkorbHelper::addVariationPictures($_SESSION['Warenkorb']);

    switch ((int)$nTyp) {
        default:
        case 0:
            $kKundengruppe = $_SESSION['Kundengruppe']->kKundengruppe;
            $nAnzahl       = $_SESSION['Warenkorb']->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_ARTIKEL]);
            $cLand         = isset($_SESSION['cLieferlandISO']) ? $_SESSION['cLieferlandISO'] : '';
            $cPLZ          = '*';

            if (isset($_SESSION['Kunde']->kKundengruppe) && $_SESSION['Kunde']->kKundengruppe > 0) {
                $kKundengruppe = $_SESSION['Kunde']->kKundengruppe;
                $cLand         = $_SESSION['Kunde']->cLand;
                $cPLZ          = $_SESSION['Kunde']->cPLZ;
            }

            $versandkostenfreiAb = gibVersandkostenfreiAb($kKundengruppe, $cLand);
            /** @var array('Warenkorb') $_SESSION['Warenkorb'] */
            $smarty->assign('WarensummeLocalized', $_SESSION['Warenkorb']->gibGesamtsummeWarenLocalized())
                   ->assign('Warensumme', $_SESSION['Warenkorb']->gibGesamtsummeWaren())
                   ->assign('Steuerpositionen', $_SESSION['Warenkorb']->gibSteuerpositionen())
                   ->assign('Einstellungen', $Einstellungen)
                   ->assign('WarenkorbArtikelPositionenanzahl', $nAnzahl)
                   ->assign('WarenkorbArtikelanzahl',
                       $_SESSION['Warenkorb']->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]))
                   ->assign('zuletztInWarenkorbGelegterArtikel', $_SESSION['Warenkorb']->gibLetztenWKArtikel())
                   ->assign('WarenkorbGesamtgewicht', $_SESSION['Warenkorb']->getWeight())
                   ->assign('Warenkorbtext', lang_warenkorb_warenkorbEnthaeltXArtikel($_SESSION['Warenkorb']))
                   ->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise)
                   ->assign('WarenkorbVersandkostenfreiHinweis', baueVersandkostenfreiString($versandkostenfreiAb,
                       $_SESSION['Warenkorb']->gibGesamtsummeWarenExt(
                           [C_WARENKORBPOS_TYP_ARTIKEL, C_WARENKORBPOS_TYP_KUPON, C_WARENKORBPOS_TYP_NEUKUNDENKUPON],
                           true)
                   ))
                   ->assign('oSpezialseiten_arr', LinkHelper::getInstance()->getSpecialPages());

            VersandartHelper::getShippingCosts($cLand, $cPLZ, $error);
            $oResponse->cTemplate = utf8_encode($smarty->fetch('basket/cart_dropdown_label.tpl'));
            break;

        case 1:
            $oResponse->cItems = utf8_convert_recursive($_SESSION['Warenkorb']->PositionenArr);
            break;
    }

    $objResponse->script('this.response = ' . json_encode($oResponse) . ';');

    return $objResponse;
}

/**
 * @param array $aValues
 * @return IOResponse
 */
function buildConfiguration($aValues)
{
    global $smarty;

    $oResponse       = new IOResponse();
    $Artikel         = new Artikel();
    $articleId       = isset($aValues['VariKindArtikel']) ? (int)$aValues['VariKindArtikel'] : (int)$aValues['a'];
    $items           = isset($aValues['item']) ? $aValues['item'] : [];
    $quantities      = isset($aValues['quantity']) ? $aValues['quantity'] : [];
    $variationValues = isset($aValues['eigenschaftwert']) ? $aValues['eigenschaftwert'] : [];
    $oKonfig         = buildConfig($articleId, $aValues['anzahl'], $variationValues, $items, $quantities, []);
    $Artikel->fuelleArtikel($articleId, null);
    $Artikel->Preise->cVKLocalized[$_SESSION['Kundengruppe']->nNettoPreise]
        = gibPreisStringLocalized($Artikel->Preise->fVK[$_SESSION['Kundengruppe']->nNettoPreise] * $aValues['anzahl'], 0, true);


    $smarty->assign('oKonfig', $oKonfig)
        ->assign('NettoPreise', $_SESSION['Kundengruppe']->nNettoPreise)
        ->assign('Artikel', $Artikel);
    $oKonfig->cTemplate = utf8_encode(
        $smarty->fetch('productdetails/config_summary.tpl')
    );

    $oResponse->script('this.response = ' . json_encode($oKonfig) . ';');

    return $oResponse;
}

/**
 * @param int        $productID
 * @param array|null $selectedVariationValues
 * @return object
 */
function getArticleStockInfo($productID, $selectedVariationValues = null)
{
    $result = (object)[
        'stock'  => false,
        'status' => 0,
        'text'   => '',
    ];

    if ($selectedVariationValues !== null) {
        $products = getArticleByVariations($productID, $selectedVariationValues);

        if (count($products) === 1) {
            $productID = $products[0]->kArtikel;
        } else {
            return $result;
        }
    }

    if ($productID > 0) {
        $product = new Artikel();
        $options = (object)[
            'nMain'                     => 0,
            'nWarenlager'               => 0,
            'nVariationKombi'           => 0,
            'nVariationen'              => 0,
            'nKeinLagerbestandBeachten' => 1,
        ];

        $product->fuelleArtikel(
            $productID,
            $options,
            Kundengruppe::getCurrent(),
            $_SESSION['kSprache']
        );

        $stockInfo = $product->getStockInfo();

        if ($stockInfo->notExists || !$stockInfo->inStock) {
            $result->stock = false;
            $result->text  = utf8_encode($stockInfo->notExists ? Shop::Lang()->get('notAvailableInSelection') : Shop::Lang()->get('ampelRot'));
        } else {
            $result->stock = true;
            $result->text  = '';
        }

        $result->status = $product->Lageranzeige->nStatus;
    }

    return $result;
}

/**
 * @param array $aValues
 * @return IOResponse
 */
function checkDependencies($aValues)
{
    $objResponse   = new IOResponse();
    $kVaterArtikel = (int)$aValues['a'];
    $fAnzahl       = (float)$aValues['anzahl'];
    $valueID_arr   = array_filter((array)$aValues['eigenschaftwert']);
    $wrapper       = isset($aValues['wrapper']) ? StringHandler::filterXSS($aValues['wrapper']) : '';

    if ($kVaterArtikel > 0) {
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
        $oArtikel                                    = new Artikel();
        $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen, Kundengruppe::getCurrent(), $_SESSION['kSprache']);
        $weightDiff   = 0;
        $newProductNr = '';
        foreach ($valueID_arr as $valueID) {
            $currentValue = new EigenschaftWert($valueID);
            $weightDiff  += $currentValue->fGewichtDiff;
            $newProductNr = (!empty($currentValue->cArtNr) && $oArtikel->cArtNr !== $currentValue->cArtNr)
                ? $currentValue->cArtNr
                : $oArtikel->cArtNr;
        }
        $weightTotal        = Trennzeichen::getUnit(JTLSEPARATER_WEIGHT, $_SESSION['kSprache'], $oArtikel->fGewicht + $weightDiff);
        $weightArticleTotal = Trennzeichen::getUnit(JTLSEPARATER_WEIGHT, $_SESSION['kSprache'], $oArtikel->fArtikelgewicht + $weightDiff);
        $cUnitWeightLabel   = Shop::Lang()->get('weightUnit', 'global');

        // Alle Variationen ohne Freifeld
        $nKeyValueVariation_arr = $oArtikel->keyValueVariations($oArtikel->VariationenOhneFreifeld);

        // Freifeldpositionen gesondert zwischenspeichern
        foreach ($valueID_arr as $kKey => $cVal) {
            if (!isset($nKeyValueVariation_arr[$kKey])) {
                unset($valueID_arr[$kKey]);
                $kFreifeldEigeschaftWert_arr[$kKey] = $cVal;
            }
        }

        $nNettoPreise = $_SESSION['Kundengruppe']->nNettoPreise;
        $fVKNetto     = $oArtikel->gibPreis($fAnzahl, $valueID_arr, Kundengruppe::getCurrent());

        $fVK = [
            berechneBrutto($fVKNetto, $_SESSION['Steuersatz'][$oArtikel->kSteuerklasse]),
            $fVKNetto
        ];

        $cVKLocalized = [
            0 => gibPreisStringLocalized($fVK[0]),
            1 => gibPreisStringLocalized($fVK[1])
        ];

        $cPriceLabel = $oArtikel->nVariationOhneFreifeldAnzahl === count($valueID_arr)
            ? Shop::Lang()->get('priceAsConfigured', 'productDetails')
            : Shop::Lang()->get('priceStarting', 'global');

        $objResponse->jsfunc(
            '$.evo.article().setPrice',
            $fVK[$nNettoPreise],
            $cVKLocalized[$nNettoPreise],
            $cPriceLabel,
            $wrapper
        );
        $objResponse->jsfunc('$.evo.article().setArticleWeight', [
            [$oArtikel->fGewicht, $weightTotal . ' ' . $cUnitWeightLabel],
            [$oArtikel->fArtikelgewicht, $weightArticleTotal . ' ' . $cUnitWeightLabel],
        ], $wrapper);

        if (!empty($oArtikel->staffelPreis_arr)) {
            $fStaffelVK = [0 => [], 1 => []];
            $cStaffelVK = [0 => [], 1 => []];
            foreach ($oArtikel->staffelPreis_arr as $staffelPreis) {
                $nAnzahl                 = &$staffelPreis['nAnzahl'];
                $fStaffelVKNetto         = $oArtikel->gibPreis($nAnzahl, $valueID_arr, Kundengruppe::getCurrent());
                $fStaffelVK[0][$nAnzahl] = berechneBrutto(
                    $fStaffelVKNetto,
                    $_SESSION['Steuersatz'][$oArtikel->kSteuerklasse]
                );
                $fStaffelVK[1][$nAnzahl] = $fStaffelVKNetto;
                $cStaffelVK[0][$nAnzahl] = gibPreisStringLocalized($fStaffelVK[0][$nAnzahl]);
                $cStaffelVK[1][$nAnzahl] = gibPreisStringLocalized($fStaffelVK[1][$nAnzahl]);
            }

            $objResponse->jsfunc(
                '$.evo.article().setStaffelPrice',
                $fStaffelVK[$nNettoPreise],
                $cStaffelVK[$nNettoPreise],
                $wrapper
            );
        }

        if ($oArtikel->cVPE === 'Y' &&
            $oArtikel->fVPEWert > 0 &&
            $oArtikel->cVPEEinheit &&
            !empty($oArtikel->Preise)
        ) {
            $oArtikel->baueVPE($fVKNetto);
            $fStaffelVPE = [0 => [], 1 => []];
            $cStaffelVPE = [0 => [], 1 => []];
            foreach ($oArtikel->staffelPreis_arr as $key => $staffelPreis) {
                $nAnzahl                  = &$staffelPreis['nAnzahl'];
                $fStaffelVPE[0][$nAnzahl] = $oArtikel->fStaffelpreisVPE_arr[$key][0];
                $fStaffelVPE[1][$nAnzahl] = $oArtikel->fStaffelpreisVPE_arr[$key][1];
                $cStaffelVPE[0][$nAnzahl] = $staffelPreis['cBasePriceLocalized'][0];
                $cStaffelVPE[1][$nAnzahl] = $staffelPreis['cBasePriceLocalized'][1];

            }

            $objResponse->jsfunc(
                '$.evo.article().setVPEPrice',
                $oArtikel->cLocalizedVPE[$nNettoPreise],
                $fStaffelVPE[$nNettoPreise],
                $cStaffelVPE[$nNettoPreise],
                $wrapper
            );
        }

        if (!empty($newProductNr)) {
            $objResponse->jsfunc('$.evo.article().setProductNumber', $newProductNr, $wrapper);
        }
    }

    return $objResponse;
}

/**
 * @param array      $aValues
 * @param int        $kEigenschaft
 * @param int        $kEigenschaftWert
 * @return IOResponse
 */
function checkVarkombiDependencies($aValues, $kEigenschaft = 0, $kEigenschaftWert = 0)
{
    $kEigenschaft                = (int)$kEigenschaft;
    $kEigenschaftWert            = (int)$kEigenschaftWert;
    $oArtikel                    = null;
    $objResponse                 = new IOResponse();
    $kVaterArtikel               = (int)$aValues['a'];
    $kArtikelKind                = isset($aValues['VariKindArtikel']) ? (int)$aValues['VariKindArtikel'] : 0;
    $idx                         = isset($aValues['eigenschaftwert']) ? (array)$aValues['eigenschaftwert'] : [];
    $kFreifeldEigeschaftWert_arr = [];
    $kGesetzteEigeschaftWert_arr = array_filter($idx);
    $wrapper                     = isset($aValues['wrapper']) ? StringHandler::filterXSS($aValues['wrapper']) : '';

    if ($kVaterArtikel > 0) {
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
        $oArtikel                                    = new Artikel();
        $oArtikel->fuelleArtikel($kVaterArtikel, $oArtikelOptionen, Kundengruppe::getCurrent(), $_SESSION['kSprache']);

        // Alle Variationen ohne Freifeld
        $nKeyValueVariation_arr = $oArtikel->keyValueVariations($oArtikel->VariationenOhneFreifeld);

        // Freifeldpositionen gesondert zwischenspeichern
        foreach ($kGesetzteEigeschaftWert_arr as $kKey => $cVal) {
            if (!isset($nKeyValueVariation_arr[$kKey])) {
                unset($kGesetzteEigeschaftWert_arr[$kKey]);
                $kFreifeldEigeschaftWert_arr[$kKey] = $cVal;
            }
        }

        $bHasInvalidSelection = false;
        $nInvalidVariations   = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, true);

        foreach ($kGesetzteEigeschaftWert_arr as $kKey => $kValue) {
            if (isset($nInvalidVariations[$kKey]) && in_array($kValue, $nInvalidVariations[$kKey])) {
                $bHasInvalidSelection = true;
                break;
            }
        }

        // Auswahl zurücksetzen sobald eine nicht vorhandene Variation ausgewählt wurde.
        if ($bHasInvalidSelection) {
            $objResponse->jsfunc('$.evo.article().variationResetAll', $wrapper);

            $kGesetzteEigeschaftWert_arr = [$kEigenschaft => $kEigenschaftWert];
            $nInvalidVariations          = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, true);

            // Auswählter EigenschaftWert ist ebenfalls nicht vorhanden
            if (in_array($kEigenschaftWert, $nInvalidVariations[$kEigenschaft])) {
                $kGesetzteEigeschaftWert_arr = [];

                // Wir befinden uns im Kind-Artikel -> Weiterleitung auf Vater-Artikel
                if ($kArtikelKind > 0) {
                    $objResponse->jsfunc(
                        '$.evo.article().setArticleContent',
                        $oArtikel->kArtikel,
                        0,
                        $oArtikel->cURL,
                        [],
                        $wrapper
                    );

                    return $objResponse;
                }
            }
        }

        // Alle EigenschaftWerte vorhanden, Kind-Artikel ermitteln
        if (count($kGesetzteEigeschaftWert_arr) >= $oArtikel->nVariationOhneFreifeldAnzahl) {
            $products = getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWert_arr);

            if (count($products) === 1 && $kArtikelKind !== (int)$products[0]->kArtikel) {
                $oArtikelTMP                  = $products[0];
                $oGesetzteEigeschaftWerte_arr = [];
                foreach ($kFreifeldEigeschaftWert_arr as $cKey => $cValue) {
                    $oGesetzteEigeschaftWerte_arr[] = (object)[
                        'key'   => $cKey,
                        'value' => $cValue
                    ];
                }
                $cUrl = baueURL($oArtikelTMP, URLART_ARTIKEL, 0, empty($oArtikelTMP->kSeoKey) ? true : false, true);
                $objResponse->jsfunc(
                    '$.evo.article().setArticleContent',
                    $kVaterArtikel,
                    $oArtikelTMP->kArtikel,
                    $cUrl,
                    $oGesetzteEigeschaftWerte_arr,
                    $wrapper
                );

                executeHook(HOOK_TOOLSAJAXSERVER_PAGE_TAUSCHEVARIATIONKOMBI, [
                    'objResponse' => &$objResponse,
                    'oArtikel'    => &$oArtikel,
                    'bIO'         => true
                ]);

                return $objResponse;
            }
        }

        $objResponse->jsfunc('$.evo.article().variationDisableAll', $wrapper);
        $nPossibleVariations = $oArtikel->getVariationsBySelection($kGesetzteEigeschaftWert_arr, false);
        $checkStockInfo      = count($kGesetzteEigeschaftWert_arr) > 0 && (count($kGesetzteEigeschaftWert_arr) === count($nPossibleVariations) - 1);
        $stockInfo           = (object)[
            'stock'  => true,
            'status' => 2,
            'text'   => '',
        ];

        foreach ($oArtikel->Variationen as $variation) {
            if (in_array($variation->cTyp, ['FREITEXT', 'PFLICHTFREITEXT'])) {
                $objResponse->jsfunc('$.evo.article().variationEnable', $variation->kEigenschaft, 0, $wrapper);
            } else {
                foreach ($variation->Werte as $value) {
                    $stockInfo->stock = true;
                    $stockInfo->text = '';

                    if (isset($nPossibleVariations[$value->kEigenschaft])
                        && in_array($value->kEigenschaftWert, $nPossibleVariations[$value->kEigenschaft])) {
                        $objResponse->jsfunc('$.evo.article().variationEnable', $value->kEigenschaft, $value->kEigenschaftWert, $wrapper);

                        if ($checkStockInfo && !array_key_exists($value->kEigenschaft, $kGesetzteEigeschaftWert_arr)) {
                            $kGesetzteEigeschaftWert_arr[$value->kEigenschaft] = $value->kEigenschaftWert;

                            $products = getArticleByVariations($kVaterArtikel, $kGesetzteEigeschaftWert_arr);
                            if (count($products) === 1) {
                                $stockInfo = getArticleStockInfo((int)$products[0]->kArtikel);
                            }
                            unset($kGesetzteEigeschaftWert_arr[$value->kEigenschaft]);
                        }
                    } else {
                        $stockInfo->stock  = false;
                        $stockInfo->status = 0;
                        $stockInfo->text   = utf8_encode(Shop::Lang()->get('notAvailableInSelection'));
                    }
                    if ($value->notExists || !$value->inStock) {
                        $stockInfo->stock  = false;
                        $stockInfo->status = 0;
                        $stockInfo->text   = utf8_encode($value->notExists ? Shop::Lang()->get('notAvailableInSelection')
                            : Shop::Lang()->get('ampelRot'));
                    }
                    if (!$stockInfo->stock) {
                        $objResponse->jsfunc('$.evo.article().variationInfo', $value->kEigenschaftWert, $stockInfo->status,
                            $stockInfo->text, $wrapper);
                    }
                }

                if (isset($kGesetzteEigeschaftWert_arr[$variation->kEigenschaft])) {
                    $objResponse->jsfunc(
                        '$.evo.article().variationActive',
                        $variation->kEigenschaft,
                        addslashes($kGesetzteEigeschaftWert_arr[$variation->kEigenschaft]),
                        null,
                        $wrapper
                    );
                }
            }
        }
    } else {
        $objResponse->jsfunc('$.evo.error', 'Article not found', $kVaterArtikel);
    }
    $objResponse->jsfunc("$.evo.article().variationRefreshAll", $wrapper);

    return $objResponse;
}

/**
 * @param int   $parentProductID
 * @param array $selectedVariationValues
 * @return array
 */
function getArticleByVariations($parentProductID, $selectedVariationValues)
{
    if (!is_array($selectedVariationValues) || count($selectedVariationValues) === 0) {
        return [];
    }

    $variationID    = 0;
    $variationValue = 0;

    if (count($selectedVariationValues) > 0) {
        $combinations = [];
        $i            = 0;
        foreach ($selectedVariationValues as $id => $value) {
            if (0 === $i++) {
                $variationID    = $id;
                $variationValue = $value;
            } else {
                $combinations[] = "($id, $value)";
            }
        }
    } else {
        $combinations = null;
    }

    $combinationSQL = ($combinations !== null && count($combinations) > 0)
        ? 'EXISTS (
                     SELECT 1
                     FROM teigenschaftkombiwert innerKombiwert
                     WHERE (innerKombiwert.kEigenschaft, innerKombiwert.kEigenschaftWert) IN (' . implode(', ', $combinations) . ')
                        AND innerKombiwert.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
                     GROUP BY innerKombiwert.kEigenschaftKombi
                     HAVING COUNT(innerKombiwert.kEigenschaftKombi) = ' . count($combinations) . '
                )
                AND '
        : '';

    $products = Shop::DB()->query(
        'SELECT tartikel.kArtikel,
                tseo.kKey AS kSeoKey, COALESCE(tseo.cSeo, \'\') AS cSeo,
                tartikel.fLagerbestand, tartikel.cLagerBeachten, tartikel.cLagerKleinerNull
            FROM teigenschaftkombiwert
            INNER JOIN tartikel ON tartikel.kEigenschaftKombi = teigenschaftkombiwert.kEigenschaftKombi
            LEFT JOIN tseo ON tseo.cKey = \'kArtikel\'
                            AND tseo.kKey = tartikel.kArtikel
                            AND tseo.kSprache = ' . Shop::getLanguage() . '
            LEFT JOIN tartikelsichtbarkeit ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                                            AND tartikelsichtbarkeit.kKundengruppe = ' . (int)$_SESSION['Kundengruppe']->kKundengruppe . '
            WHERE ' . $combinationSQL . 'tartikel.kVaterArtikel = ' . (int)$parentProductID . '
                AND teigenschaftkombiwert.kEigenschaft = ' . $variationID . '
                AND teigenschaftkombiwert.kEigenschaftWert = ' . $variationValue . '
                AND tartikelsichtbarkeit.kArtikel IS NULL'
        , 2
    );

    return $products;
}

/**
 * @return IOResponse
 */
function generateToken()
{
    $objResponse             = new IOResponse();
    $cToken                  = gibToken();
    $cName                   = gibTokenName();
    $token_arr               = ['name' => $cName, 'token' => $cToken];
    $_SESSION['xcrsf_token'] = json_encode($token_arr);
    $objResponse->script("doXcsrfToken('" . $cName . "', '" . $cToken . "');");

    return $objResponse;
}

/**
 * @param int $categoryId
 * @return IOResponse
 */
function getCategoryMenu($categoryId)
{
    global $smarty;

    $categoryId = (int)$categoryId;
    $auto       = $categoryId === 0;

    if ($auto) {
        $categoryId = Shop::$kKategorie;
    }

    $response   = new IOResponse();
    $list       = new KategorieListe();
    $category   = new Kategorie($categoryId);
    $categories = $list->holUnterkategorien($category->kKategorie, 0, 0);

    if ($auto && count($categories) === 0) {
        $category   = new Kategorie($category->kOberKategorie);
        $categories = $list->holUnterkategorien($category->kKategorie, 0, 0);
    }

    $result = (object)['current' => $category, 'items' => $categories];

    $smarty->assign('result', $result)
           ->assign('nSeitenTyp', 0);
    $template = utf8_encode($smarty->fetch('snippets/categories_offcanvas.tpl'));

    $response->script('this.response = ' . json_encode($template) . ';');

    return $response;
}

/**
 * @param string $country
 * @return IOResponse
 */
function getRegionsByCountry($country)
{
    $response = new IOResponse();

    if (strlen($country) === 2) {
        $regions = Staat::getRegions($country);
        $regions = utf8_convert_recursive($regions);
        $response->script("this.response = " . json_encode($regions) . ";");
    }

    return $response;
}

/**
 * @param string $country
 * @return IOResponse
 */
function checkDeliveryCountry($country)
{
    $response = new IOResponse();

    if (strlen($country) === 2) {
        $deliveryCountries = gibBelieferbareLaender($_SESSION['Kundengruppe']->kKundengruppe, false, false, [$country]);
        $response->script('this.response = ' . (count($deliveryCountries) === 1 ? 'true' : 'false') . ';');
    }

    return $response;
}

/**
 * @param string $cKey
 * @param int $kKey
 * @param int $kSprache
 * @param array $kSelection_arr
 * @return IOResponse
 */
function setSelectionWizardAnswers($cKey, $kKey, $kSprache, $kSelection_arr)
{
    global $smarty;

    $response = new IOResponse();
    $AWA      = AuswahlAssistent::startIfRequired($cKey, $kKey, $kSprache, $smarty, $kSelection_arr);

    if ($AWA !== null) {
        $oLastSelectedValue = $AWA->getLastSelectedValue();
        $NaviFilter         = $AWA->getNaviFilter();

        if (($oLastSelectedValue !== null && $oLastSelectedValue->nAnzahl === 1) ||
            $AWA->getCurQuestion() === $AWA->getQuestionCount() ||
            $AWA->getQuestion($AWA->getCurQuestion())->nTotalResultCount === 0)
        {
            $response->script("window.location.href='" .
                StringHandler::htmlentitydecode(gibNaviURL($NaviFilter, true, null)) . "';");
        } else {
            $response->assign('selectionwizard', 'innerHTML', utf8_encode($AWA->fetchForm($smarty)));
        }
    }

    return $response;
}
