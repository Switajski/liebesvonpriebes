<?php

/*
 * Solution 360 GmbH
 */

if (!function_exists('logoutUnregisteredUser')) {
    function logoutUnregisteredUser() {
        /*
         * This function logs out a user as if he clicked on the logout button.
         * The code is directly taken from jtl.php, minus the redirect to the
         * logged out-message. Also, it does NOT unset the basket because that
         * would defeat the purpose of our checkout!
         */
        if (!empty($_SESSION['Kunde']->kKunde)) {
            // Sprache und Waehrung beibehalten
            $kSprache    = Shop::$kSprache;
            $cISOSprache = Shop::$cISO;
            $Waehrung    = $_SESSION['Waehrung'];
            // Kategoriecache loeschen
            unset($_SESSION['kKategorieVonUnterkategorien_arr']);
            unset($_SESSION['oKategorie_arr']);
            // unset($_SESSION['Warenkorb']);
            $oldWarenkorb = $_SESSION['Warenkorb'];

            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 7000000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            session_destroy();
            $session = new Session();
            session_regenerate_id(true);

            $_SESSION['kSprache']    = $kSprache;
            $_SESSION['cISOSprache'] = $cISOSprache;
            $_SESSION['Waehrung']    = $Waehrung;
            $_SESSION['Warenkorb'] = $oldWarenkorb;
            Shop::setLanguage($kSprache, $cISOSprache);
        }
    }
}

if (!function_exists('loginUserForKunde')) {

    function loginUserForKunde($Kunde) {
        /*
         * We will now try to log in the user.
         *
         * This basically replicates the JTL-inherent functionality.
         */
        $Einstellungen = Shop::getSettings(array(CONF_GLOBAL, CONF_RSS, CONF_KUNDEN, CONF_KAUFABWICKLUNG, CONF_KUNDENFELD, CONF_KUNDENWERBENKUNDEN, CONF_TRUSTEDSHOPS));
        $hinweis = '';
        //create new session id to prevent session hijacking
        session_regenerate_id(false);

        //in tbesucher kKunde setzen
        if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
            Shop::DB()->query("update tbesucher set kKunde=$Kunde->kKunde where kBesucher=" . $_SESSION['oBesucher']->kBesucher, 4);
        }
        if ($Kunde->cAktiv === "Y" &&
                !(isset($Kunde->cSperre) && $Kunde->cSperre === "Y")) {
            unset($_SESSION['Zahlungsart']);
            unset($_SESSION['Versandart']);
            unset($_SESSION['Lieferadresse']);
            unset($_SESSION['ks']);
            unset($_SESSION['VersandKupon']);
            unset($_SESSION['NeukundenKupon']);
            unset($_SESSION['Kupon']);
            // LÃ¶sche kompletten Kategorie Cache
            unset($_SESSION['kKategorieVonUnterkategorien_arr']);
            unset($_SESSION['oKategorie_arr']);
            // Kampagne
            if (isset($_SESSION['Kampagnenbesucher'])) {
                setzeKampagnenVorgang(KAMPAGNE_DEF_LOGIN, $Kunde->kKunde, 1.0); // Login
            }

            $session = Session::getInstance();
            $session->setCustomer($Kunde);

            // Setzt aktuelle Wunschliste (falls vorhanden) vom Kunden in die Session
            setzeWunschlisteInSession();


            // Lade WarenkorbPers
            $bPersWarenkorbGeladen = false;
            if ($Einstellungen['global']['warenkorbpers_nutzen'] === 'Y' && count($_SESSION['Warenkorb']->PositionenArr) == 0) {
                $oWarenkorbPers = new WarenkorbPers($Kunde->kKunde);
                $oWarenkorbPers->ueberpruefePositionen(true);
                if (count($oWarenkorbPers->oWarenkorbPersPos_arr) > 0) {
                    foreach ($oWarenkorbPers->oWarenkorbPersPos_arr as $oWarenkorbPersPos) {
                        if (!isset($oWarenkorbPersPos->Artikel->bHasKonfig) || !$oWarenkorbPersPos->Artikel->bHasKonfig) {
                            fuegeEinInWarenkorb(
                                    $oWarenkorbPersPos->kArtikel, $oWarenkorbPersPos->fAnzahl, $oWarenkorbPersPos->oWarenkorbPersPosEigenschaft_arr, 1, $oWarenkorbPersPos->cUnique, $oWarenkorbPersPos->kKonfigitem, null, false
                            ); 
                        }
                    }
                    $_SESSION['Warenkorb']->setzePositionsPreise();
                    $bPersWarenkorbGeladen = true;
                }
            }

            // Pruefe, ob Artikel im Warenkorb vorhanden sind, welche fÃ¼r den aktuellen Kunden nicht mehr sichtbar sein duerfen
            pruefeWarenkorbArtikelSichtbarkeit($_SESSION['Kunde']->kKundengruppe);

            // Existiert ein pers. Warenkorb? Wenn ja => mergen, falls so gesetzt im Backend. Sonst nicht.
            if ($Einstellungen['global']['warenkorbpers_nutzen'] === "Y" && $Einstellungen['kaufabwicklung']['warenkorb_warenkorb2pers_merge'] === "Y" && !$bPersWarenkorbGeladen) {
                setzeWarenkorbPersInWarenkorb($_SESSION['Kunde']->kKunde);
            }
        } elseif (isset($Kunde->cSperre) && $Kunde->cSperre === "Y") {
            $hinweis = Shop::Sprache()->gibWert('accountLocked', 'global');
            return $hinweis;
        } else {
            $hinweis = Shop::Sprache()->gibWert('loginNotActivated', 'global');
            return $hinweis;
        }
    }

}

if (!function_exists('deleteLPAAccountMapping')) {

    function deleteLPAAccountMapping($amazonId) {
        Shop::DB()->delete(S360_LPA_TABLE_ACCOUNTMAPPING, 'cAmazonId', $amazonId);
    }

}


if (!function_exists('createLPAAccountMapping')) {

    function createLPAAccountMapping($kKunde, $amazonId, $verified = 0) {
        $verificationCode = '';
        if ($verified == 0) {
            $verificationCode = 'V' . md5(time()); // simple unique string for comparison purposes on verification
        }
        $obj = new stdClass();
        $obj->kKunde = intval($kKunde);
        $obj->cAmazonId = $amazonId;
        $obj->nVerifiziert = intval($verified);
        $obj->cVerifizierungsCode = $verificationCode;
        /*
         * safety check if the EXACT SAME entry already exists to avoid duplicates (note that this is never true if the account is not verified)
         */
        $sql = "SELECT * FROM " . S360_LPA_TABLE_ACCOUNTMAPPING . " WHERE kKunde = {$obj->kKunde} "
                . "AND cAmazonId LIKE '{$obj->cAmazonId}' "
                . "AND nVerifiziert = {$obj->nVerifiziert} "
                . "AND cVerifizierungscode LIKE '{$obj->cVerifizierungsCode}'";
        $test = Shop::DB()->query($sql, 1);
        if (!$test) {
            $res = Shop::DB()->insert(S360_LPA_TABLE_ACCOUNTMAPPING, $obj);
            if (!isset($res) || $res == 0) {
                Jtllog::writeLog('LPA: LPA-Login-Fehler: Konnte das Account-Mapping für ' . $amazonId . ' nicht erzeugen.');
            }
        }
        return $verificationCode;
    }

}

if (!function_exists('updateLPAAccountMappingForKunde')) {

    function updateLPAAccountMappingForKunde($kKunde, $amazonId, $verified = 0) {
        $result = Shop::DB()->select(S360_LPA_TABLE_ACCOUNTMAPPING, 'kKunde', (int)$kKunde);
        
        if (empty($result)) {
            // There is no entry for that user, just create a new entry.
            createLPAAccountMapping($kKunde, $amazonId, $verified);
        } else {
            $obj = new stdClass();
            $obj->cAmazonId = $amazonId;
            $obj->nVerifiziert = intval($verified);
            $obj->kKunde = intval($kKunde);
            $obj->cVerifizierungsCode = '';
            $res = Shop::DB()->update(S360_LPA_TABLE_ACCOUNTMAPPING, 'kKunde', $kKunde, $obj);
            if (!isset($res) || $res == 0) {
                Jtllog::writeLog('LPA: LPA-Login-Fehler: Konnte das Account-Mapping für ' . $amazonId . ' nicht updaten.');
            }
        }
    }
}


if (!function_exists('verifyLPAAccountMapping')) {

    function verifyLPAAccountMapping($amazonId, $kKunde, $strictMode = false) {
        $result = Shop::DB()->select(S360_LPA_TABLE_ACCOUNTMAPPING, 'cAmazonId', $amazonId);
        if (empty($result)) {
            if ($strictMode) {
                // we did not find that amazonId - this is not allowed
                return false;
            }
            // There is no entry for that user, just create a new entry.
            createLPAAccountMapping($kKunde, $amazonId, 1);
        } else {
            if ($strictMode && (intval($result->kKunde) !== intval($kKunde))) {
                // we found an entry for the amazon Id, but the user Id does not match what was given to us... not allowed in strict mode.
                return false;
            }
            $obj = new stdClass();
            $obj->cAmazonId = $amazonId;
            $obj->nVerifiziert = 1;
            $obj->kKunde = intval($kKunde);
            $obj->cVerifizierungsCode = '';
            $res = Shop::DB()->update(S360_LPA_TABLE_ACCOUNTMAPPING, 'cAmazonId', $amazonId, $obj);
            if (!isset($res) || $res == 0) {
                Jtllog::writeLog('LPA: LPA-Login-Fehler: Konnte das Account-Mapping für ' . $amazonId . ' nicht updaten.');
            }
        }
        return true;
    }

}

if (!function_exists('setLPARedirectionCookie')) {

    function setLPARedirectionCookie() {
        $link = "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if (Shop::getPageType() === PAGE_SITEMAP || Shop::getPageType() === PAGE_REGISTRIERUNG) {
            return; // do not set the cookie to redirect to the sitemap url
        }
        if (strpos($link, 'lpalogin') !== FALSE || strpos($link, 'lpacheckout') !== FALSE || strpos($link, 'lpamerge') !== FALSE || strpos($link, 'lpacreate') !== FALSE || strpos($link, 'lpacheckout') !== FALSE) {
            return; // do not manipulate the cookie if we are looking at one of our own frontend links
        }
        if (strpos($link, 'logout') !== FALSE || strpos($link, 'registrieren.php') !== FALSE) {
            $link = Shop::getURL(); // redirect to the start page, if we are looking at the logout site or the register-site
        }
        if (strpos($link, 'toolsajax') !== FALSE || strpos($link, "io.php") !== FALSE || isset($_REQUEST['isAjax'])) {
            return; // do not redirect to ajax endpoints or to URLs used for AJAX Requests
        }
        setcookie(S360_LPA_LOGIN_REDIRECT_COOKIE, $link, 0, '/');
    }

}


if (!function_exists('redirectToLPACookieLocation')) {
    /*
     * Redirects to the location given in the cookie or to the shop homepage if none is set.
     * 
     * IF the site initially came from the checkout (i.e. to have the user login before checking out),
     * we send the user back there.
     */

    function redirectToLPACookieLocation() {
        if (isset($_SESSION['lpa-from-checkout'])) {
            unset($_SESSION['lpa-from-checkout']);
            $language_suffix = '';
            if (Shop::getLanguage(true) === "eng") {
                $language_suffix = '-en';
            }
            header('Location: ' . Shop::getURL() . '/lpacheckout' . $language_suffix, 303);
            return;
        }

        if (isset($_COOKIE[S360_LPA_LOGIN_REDIRECT_COOKIE])) {
            $url = $_COOKIE[S360_LPA_LOGIN_REDIRECT_COOKIE];
            setcookie(S360_LPA_LOGIN_REDIRECT_COOKIE, "", time() - 86400);
            header('Location: ' . $url);
            return;
        } else {
            // otherwise we redirect to main shop site
            header('Location: ' . Shop::getURL(), true, 303);
            return;
        }
    }

}

if (!function_exists('lpaConvertAmount')) {
    /*
     * Converts the given amount from one currency to another.
     * If no source currency is given, it is assumed that the source currency is the standard shop currency and the factor therefore is 1.
     */

    function lpaConvertAmount($amount, $fromCurrencyISO, $toCurrencyISO) {
        /*
         * Currencies in the database have a conversion factor.
         * To get to the desired value, we need to divide the amount by the factor of the fromCurrency (the price is then normalized to the
         * standard shop currency).
         * Then we have to multiply the result with the toCurrency to get the value in the target currency.
         */
        $fromCurrency = new stdClass();
        if (!empty($fromCurrencyISO)) {
            $fromCurrency = Shop::DB()->select('twaehrung', 'cISO', $fromCurrencyISO);
        } else {
            $fromCurrency->fFaktor = 1;
        }
        $toCurrency = Shop::DB()->select('twaehrung', 'cISO', $toCurrencyISO);
        if (empty($fromCurrency) || empty($toCurrency)) {
            Jtllog::writeLog("LPA: Fehler bei der Währungskonvertierung. Ausgangswährung '{$fromCurrencyISO}' oder Amazon-Endpunktwährung '{$toCurrencyISO}' nicht gefunden.", JTLLOG_LEVEL_ERROR);
            return $amount;
        }
        $result = $amount;
        $result /= $fromCurrency->fFaktor;
        $result *= $toCurrency->fFaktor;
        return $result;
    }

}

if (!function_exists('lpaGetShopBasePath')) {
    /*
     * Determines the base path of the shop, i.e. if the shop is running on www.mydomain.de/ it returns "/".
     * If it is running on www.mydomain.de/myshop/, it returns "/myshop/"
     */

    function lpaGetShopBasePath() {
        return parse_url(Shop::getURL(), PHP_URL_PATH);
    }

}
