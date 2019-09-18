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

    public function headSnippetNeeded() {
        if ($this->counterLoginButtons > 0 || $this->counterPayButtons > 0 || $this->isPluginFrontendPage() || Shop::getPageType() === PAGE_ARTIKEL) {
            return true;
        } else {
            return false;
        }
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
        if(Shop::getPageType() === PAGE_ARTIKEL) {
            /*
             * the express button is possible only iff:
             * - we are on the detail page
             * - the current article is not excluded from amazon pay
             * - there is not excluded article in the basket already
             * - the current article can be put into the basket
             * - putting the current article into the basket 1 time fulfills the minimum order value for the customer group
             */
            $articleLoaded = Shop::Smarty()->getTemplateVars('Artikel');

            if (isset($articleLoaded->FunktionsAttribute['exclude_amapay']) || isset($articleLoaded->AttributeAssoc['exclude_amapay'])) {
                // the current article itself can't be bought with amazon pay, refuse to show the button
                return false;
            }

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
            $basketSum = 0;
            if(isset($_SESSION['Warenkorb'])) {
                $basketSum = $_SESSION['Warenkorb']->gibGesamtsummeWaren(1, 0);
            }
            $sumTotal = $price + $basketSum;
            // check if we reach the minimal order sum for the customer group, if it was set
            if(isset($_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]) &&
                $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT] > 0 &&
                $sumTotal < $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]
            ) {
                return false;
            }
            // all checks passed
            return true;
        }
        return false;
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
