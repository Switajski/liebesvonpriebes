<?php

class LPARenderHelper {

    private $counterLoginButtons = 0;
    private $counterPayButtons = 0;

    public function loginJSNeeded() {
        return ($this->isPluginFrontendPage('create') || $this->isPluginFrontendPage('login'));
    }

    public function checkoutJSNeeded() {
        return $this->isPluginFrontendPage('checkout');
    }

    public function headRedirectSnippetNeeded() {
        return ($this->isPluginFrontendPage('login') || $this->isPluginFrontendPage('checkout'));
    }

    /*
     * We need the head snippet iff we show login or pay buttons, or are on one of our frontendpages or if we are on an article or category page (category: button might be useful in quickview)
     */
    public function headSnippetNeeded($oPlugin) {
        if ($this->counterLoginButtons > 0
            || $this->counterPayButtons > 0
            || $this->isPluginFrontendPage()
            || (Shop::getPageType() === PAGE_ARTIKEL && $this->isDetailExpressButtonEnabled($oPlugin))
            || (Shop::getPageType() === PAGE_ARTIKELLISTE && $this->isListingExpressButtonEnabled($oPlugin))) {
            return true;
        }
        return false;
    }

    private function isDetailExpressButtonEnabled($oPlugin) {
        if(isset($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_ENABLED]) && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_ENABLED] === 'Y') {
            return true;
        }
        return false;
    }

    private function isListingExpressButtonEnabled($oPlugin) {
        if(Shop::getVersion() >= 406
            && isset($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_LISTING_ENABLED])
            && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_LISTING_ENABLED] === 'Y') {
            return true;
        }
        return false;
    }

    public function cssCheckoutNeeded() {
        return $this->isPluginFrontendPage();
    }

    public function cssPayButtonsNeeded() {
        return $this->counterPayButtons > 0 || Shop::getPageType() === PAGE_ARTIKEL;
    }


    public function cssLoginButtonsNeeded() {
        return $this->counterLoginButtons > 0;
    }

    public function addLoginButton() {
        $this->counterLoginButtons = $this->counterLoginButtons + 1;
    }

    public function addPayButton() {
        $this->counterPayButtons = $this->counterPayButtons + 1;
    }

    public function isCheckoutPossible() {
        return (isset($_SESSION["Warenkorb"]) && $_SESSION["Warenkorb"]->istBestellungMoeglich() === 10);
    }

    public function isDetailExpressButtonPossible($oPlugin) {
        if(isset($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_ENABLED]) && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_ENABLED] === 'N') {
            return false;
        }
        if(Shop::getPageType() === PAGE_ARTIKEL && !$this->isAjaxRequestForListingExpress()) {
            /*
             * the express button is possible only iff:
             * - we are on the detail page
             * - the current article is not excluded from amazon pay
             * - there is not excluded article in the basket already
             * - the current article can be put into the basket
             * - putting the current article into the basket 1 time fulfills the minimum order value for the customer group
             */
            if(!$this->isExpressCheckoutPossible()) {
                return false;
            }
            $articleLoaded = Shop::Smarty()->getTemplateVars('Artikel');
            $basketSum = $this->getBasketSum();
            if(!$this->isExpressBuyableArticle($articleLoaded, $basketSum)) {
                return false;
            }
            // all checks passed
            return true;
        }
        return false;
    }

    /*
     * This method defines if *in general* listing express buttons are available
     */
    public function isListingExpressButtonPossible($oPlugin) {
        if(isset($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_LISTING_ENABLED]) && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_PAYBUTTON_EXPRESS_LISTING_ENABLED] === 'N') {
            // function is disabled by config
            return false;
        }

        if (Shop::getVersion() < 406) {
            // this works only in shop 4.06 upwards, due to missing template constraints and io php functions in 4.05 (same restrictions as quickshopping)
            return false;
        }

        if (Shop::getPageType() === PAGE_ARTIKELLISTE || $this->isAjaxRequestForListingExpress()) {
            return $this->isExpressCheckoutPossible();
        }
        // not on an article listing page
        return false;
    }

    /*
     * Returns the article ids for the articles which should get an express button
     */
    public function getListingExpressButtonArticles() {
        $result = array();
        if($this->isAjaxRequestForListingExpress()) {
            $articleLoaded = Shop::Smarty()->getTemplateVars('Artikel');
            $basketSum = $this->getBasketSum();
            if(!empty($articleLoaded)) {
                if($this->isExpressBuyableArticle($articleLoaded, $basketSum)) {
                    $result[] = (int) $articleLoaded->kArtikel;
                }
            }
        } else {
            $basketSum = $this->getBasketSum();
            $Suchergebnisse = Shop::Smarty()->getTemplateVars('Suchergebnisse');
            if (isset($Suchergebnisse, $Suchergebnisse->Artikel, $Suchergebnisse->Artikel->elemente) && is_array($Suchergebnisse->Artikel->elemente)) {
                foreach ($Suchergebnisse->Artikel->elemente as $articleLoaded) {
                    if ($this->isExpressBuyableArticle($articleLoaded, $basketSum) && (int)$articleLoaded->kArtikel > 0) {
                        $result[] = (int) $articleLoaded->kArtikel;
                    }
                }
            }
            // bestsellers might be shown atop before the actual results, they cannot be found in the Suchergebnisse array
            $oBestseller_arr = Shop::Smarty()->getTemplateVars('oBestseller_arr');
            if(isset($oBestseller_arr) && is_array($oBestseller_arr) && !empty($oBestseller_arr)) {
                foreach ($oBestseller_arr as $articleLoaded) {
                    if ($this->isExpressBuyableArticle($articleLoaded, $basketSum) && (int)$articleLoaded->kArtikel > 0) {
                        $result[] = (int) $articleLoaded->kArtikel;
                    }
                }
            }
        }
        return array_unique($result);
    }

    private function getBasketSum() {
        $basketSum = 0;
        if(isset($_SESSION['Warenkorb'])) {
            $basketSum = $_SESSION['Warenkorb']->gibGesamtsummeWaren(1, 0);
        }
        return $basketSum;
    }

    private function isAjaxRequestForListingExpress() {
        return (Shop::getPageType() === PAGE_ARTIKEL && isAjaxRequest() && isset($_GET['isListStyle']));
    }

    private function isExpressCheckoutPossible() {
        // if the basket already contains an excluded artcile, we dont show the buttons, at all
        if(isset($_SESSION['Warenkorb']) && count($_SESSION['Warenkorb']->PositionenArr) > 0) {
            foreach ($_SESSION['Warenkorb']->PositionenArr as $oPosition) {
                if ((int)$oPosition->nPosTyp === (int)C_WARENKORBPOS_TYP_ARTIKEL && is_object($oPosition->Artikel)) {
                    if (isset($oPosition->Artikel->FunktionsAttribute['exclude_amapay']) || isset($oPosition->Artikel->AttributeAssoc['exclude_amapay'])) {
                        // there is an excluded from amazon pay article in the basket already, refuse to show the buy button
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function isExpressBuyableArticle($articleLoaded, $basketSum) {
        if (isset($articleLoaded->FunktionsAttribute['exclude_amapay']) || isset($articleLoaded->AttributeAssoc['exclude_amapay'])) {
            // the current article itself can't be bought with amazon pay, refuse to show the button
            return false;
        }
        if($articleLoaded->nIstVater && (int) $articleLoaded->kVaterArtikel === 0) {
            // current article is a father article, this cant be bought
            return false;
        }
        if((int) $articleLoaded->inWarenkorbLegbar !== 1 || $articleLoaded->oKonfig_arr) {
            // article cant be put into the basket anyway or is a configurator article
            return false;
        }
        // note: we do not check min max order quantity in general as that depends on the input of the customer, we only check if putting the article with quantity 1 into the cart will fulfill the minimal order value
        // hence this is only an approximation which might give false positives (i.e. situations where the button is visible but the checkout is not possible - this must be caught by the JS part when trying to add the article to the basket!)
        $price = (float) $articleLoaded->Preise->fVKBrutto;
        if(empty($price)) {
            // price is 0 or null, refuse to show the express button
            return false;
        }
        $sumTotal = $price + $basketSum;
        // check if we reach the minimal order sum for the customer group, if it was set
        if(isset($_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]) &&
            $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT] > 0 &&
            $sumTotal < $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]
        ) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the current site is a plugin frontend page of the given type (or at all, if the type is not given).
     * @param type $type - the type of the page, i.e. create, login, merge, checkout or complete
     * @return boolean
     */
    private function isPluginFrontendPage($type = '') {
        if(Shop::has('lpa_plugin_page')) {
            return true;
        }
        if (Shop::getPageType() === PAGE_PLUGIN) {
            if (stripos(Shop::$uri, '/lpa' . $type) !== false) {
                return true;
            }

        }
        return false;
    }

}
