<?php

// Override for the pushToBasket function in shops < 406 - the old pushToBasket was unable to push VariationsArtikel (pure Variations) via AJAX
try {
    $io = $args_arr['io'];

    // this is the original pushToBasket function from the 4.06.3 version (includes/io_inc.php)
    function lpa_fallback_pushToBasket($kArtikel, $anzahl, $oEigenschaftwerte_arr = '') {

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
            if ($Artikel->kEigenschaftKombi > 0) {
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

    $io->register('lpa_fallback_pushToBasket');
} catch(Exception $ex) {
    Jtllog::writeLog("LPA: Exception bei IO Input-Registrierung (Hook 213): " . $ex->getMessage() . "(" . $ex->getCode() . ")", JTLLOG_LEVEL_ERROR);
}