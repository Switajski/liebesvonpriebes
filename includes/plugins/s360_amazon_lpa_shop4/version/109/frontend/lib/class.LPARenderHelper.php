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
        if ($this->counterLoginButtons > 0 || $this->counterPayButtons > 0 || $this->isPluginFrontendPage()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function cssCheckoutNeeded() {
        return $this->isPluginFrontendPage();
    }
    
    public function cssPayButtonsNeeded() {
        return $this->counterPayButtons > 0;
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

    /**
     * Checks if the current site is a plugin frontend page of the given type (or at all, if the type is not given).
     * @param type $type - the type of the page, i.e. create, login, merge, checkout or complete
     * @return boolean
     */
    private function isPluginFrontendPage($type = '') {
        if (Shop::getPageType() === PAGE_PLUGIN) {
            if (stripos(Shop::$uri, '/lpa' . $type) !== false) {
                return true;
            }
        }
        return false;
    }

}
