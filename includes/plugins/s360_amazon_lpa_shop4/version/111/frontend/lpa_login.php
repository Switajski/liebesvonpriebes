<?php

/*
 * This class is called as redirect callback on a login via Amazon.
 * Amazon calls it with an access token as parameter.
 */

/*
 * Received access token - get user profile information from Amazon.
 * Endpoint depends on region and environment!
 */
require_once(dirname(__FILE__) . '/lib/lpa_includes.php');
$oPlugin = Plugin::getPluginById('s360_amazon_lpa_shop4');
require_once($oPlugin->cFrontendPfad . 'lib/class.LPAController.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_defines.php');
require_once($oPlugin->cFrontendPfad . 'lib/lpa_utils.php');
require_once("includes/bestellvorgang_inc.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "jtl_inc.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Bestellung.php");
require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Kampagne.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "mailTools.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "wunschliste_inc.php");
require_once(PFAD_ROOT . PFAD_INCLUDES . "kundenwerbenkeunden_inc.php");

pruefeHttps();
$skipToEnd = false;
$access_token = "";
if (isset($_REQUEST['access_token'])) {
    $access_token = StringHandler::filterXSS($_REQUEST['access_token']);
}
$tokenQuelle = "GET";

if (empty($access_token)) {
    /*
     * If the token was not given via request parameter, we try to get it from the cookie!
     */
    $access_token = isset($_COOKIE[S360_LPA_ACCESS_TOKEN_COOKIE]) ? $_COOKIE[S360_LPA_ACCESS_TOKEN_COOKIE] : '';
    $tokenQuelle = "Cookie";
    if (empty($access_token)) {
        /*
         * We got here without a token available anywhere.
         * This is most likely due to a customer canceling the click.
         */
        Shop::Smarty()->assign('lpa_login_display_mode', "error");
        return;
    }
}

$controller = new LPAController();
$config = $controller->getConfig();
$profileURLs = $controller->getProfileAPIURLs($config);

$curl = curl_init($profileURLs[0] . urlencode($access_token));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
$curl_error = curl_error($curl);
if (!empty($curl_error)) {
    // error in the curl call
    Jtllog::writeLog("LPA: LPA-Login-Fehler: CURL-Fehler: " . $curl_error, JTLLOG_LEVEL_ERROR);
}
curl_close($curl);
$data = json_decode($response);
if (!isset($data->aud) || $data->aud !== $config['client_id']) {
    // the access token does not belong to us
    Jtllog::writeLog("LPA: LPA-Login-Fehler: Falsches Access Token empfangen: " . json_encode($data), JTLLOG_LEVEL_DEBUG);
    header('HTTP/1.1 404 Not Found');
    echo 'Page not found';
    exit;
}

// exchange the access token for user profile
$curl = curl_init($profileURLs[1]);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: bearer '
    . $access_token));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
$curl_error = curl_error($curl);
if (!empty($curl_error)) {
    // error in the curl call
    Jtllog::writeLog("LPA: LPA-Login-Fehler: CURL-Fehler: " . $curl_error, JTLLOG_LEVEL_ERROR);
}
curl_close($curl);
$data = json_decode($response);

if (empty($data) || empty($data->email)) {
    Jtllog::writeLog("LPA: LPA-Login-Fehler: Keine Daten oder Daten ohne E-Mail von Amazon zurückbekommen (Token aus {$tokenQuelle}): " . json_encode($data), JTLLOG_LEVEL_NOTICE);
    return;
}

if (!isset($_SESSION)) {
    session_start();
}

/*
 * Now we have information on the user. The following scenarios may occur:
 *
 * User is already logged in in shop:
 * - if email has mapped Amazon-ID and matches, do nothing special (cookie is set, user is now also logged in against amazon)
 * - if email has no mapped Amazon-ID, trigger account merge (user might be logged in now in both systems, but CANNOT login via Amazon ONLY as long as the merge is not verified!)
 * - if email has different Amazon-ID, trigger account merge (maybe user has two amazon accounts, that's ok with us, but we only remember the last Amazon Account merged)
 *
 * User is not logged in:
 * - See if email for user exists
 * - if email exists and has mapped Amazon-ID that matches, log user in normally
 * - if email exists and has no mapped Amazon-ID, trigger account merge, log in user only after verified merge!
 * - if email exists and has different Amazon-ID, error message and log error, do not log in user
 * - if email not exists, trigger account creation
 */

$amazonEmail = $data->email;
$amazonId = $data->user_id;
$amazonName = $data->name;

// Determine if this is a mobile template
$isMobileTemplate = false;
$template = new Template();
if ($template->isMobileTemplateActive()) {
    $isMobileTemplate = true;
}
Shop::Smarty()->assign('lpa_template_mobile', $isMobileTemplate);

// Use custom template if it exists.
if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_login_custom.tpl')) {
    Shop::Smarty()->assign('cPluginTemplate', $oPlugin->cFrontendPfad . 'template/lpa_login_custom.tpl');
}

$formFirstName = null;
$formLastName = null;
/*
 * Guess first/last name in case of account creation
 */
if ($amazonName) {
    if (count(explode(' ', $amazonName, 2)) === 2) {
        $nameparts = explode(' ', $amazonName, 2);
        $formFirstName = $nameparts[0];
        $formLastName = $nameparts[1];
    } else {
        $formFirstName = '';
        $formLastName = $amazonName;
    }
    Shop::Smarty()->assign('lpa_first_name', $formFirstName)
            ->assign('lpa_last_name', $formLastName);
}
Shop::Smarty()->assign('lpa_email', $amazonEmail)
        ->assign('lpa_shop3_compatibility', $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_SHOP3_COMPATIBILITY]);

// check if corresponding accountmapping exists
$result = Shop::DB()->select(S360_LPA_TABLE_ACCOUNTMAPPING, 'cAmazonId', $amazonId);

$displayMode = 'default';
if (empty($result)) {
    // the amazon id is new to us
    if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde && $_SESSION['Kunde']->nRegistriert > 0) {
        // ... and a registered user is logged in
        /*
         * We have to merge the accounts, however, as the user is logged in,
         * we can simply remember that this amazonID belongs to the current account and it is automatically valid.
         *
         * If the account had a different amazonID before, we overwrite it.
         */
        updateLPAAccountMappingForKunde($_SESSION['Kunde']->kKunde, $amazonId, 1);
    } else {
        if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde) {
            // ... and an unregistered user is logged in
            /*
             * We have to log out the unregistered user and then proceed as if he was not logged in before.
             */
            logoutUnregisteredUser();
        }

        // ... and no user is logged in
        /*
         * We have to create a completely new account OR merge an existing account with the same e-Mail address, if it exists.
         *
         * Creating is done by:
         * - IF required user data should be asked:
         *      present a form to the user with the required additional data, on submit, create the user and save the mapping
         * - ELSE
         *      create a user with the other required data set to default values, also generate a password for the user which he can retrieve later with the
         *      password recovery, if necessary and save the mapping
         *
         * Merging is done by:
         * - inserting the mapping in the database but not marking it verified yet
         * - asking the user password for the shop from the user
         * - if he enters it correctly, set the mapping verified
         */
        $sql = 'SELECT * FROM tkunde WHERE cMail LIKE :amazonEmail AND nRegistriert > 0';
        $kunde = Shop::DB()->executeQueryPrepared($sql, ['amazonEmail' => $amazonEmail], 2);
        if (empty($kunde)) {
            /*
             * No customer known with that email, create a new account.
             * This can be challenging as the kKunde is not known yet! We create an entry for the amazon id only and with dummy kKunde '-1'.
             * Together with the verification code, the amazonId can be used to map the (via form/automatically) newly created user to the account.
             */
            $verificationCode = createLPAAccountMapping(-1, $amazonId);
            Shop::Smarty()->assign('lpa_login_verification_code', $verificationCode)
                    ->assign('lpa_login_amazon_id', $amazonId);
            $displayMode = 'create';
        } else {
            /*
             * SAFETY CHECK: if we have multiple results (i.e. an error in the database with multiple users with the same mail),
             * we cannot determine which user is the correct one.
             */
            if (count($kunde) > 1) {
                Jtllog::writeLog('LPA: LPA-Login-Fehler: Es wurde mehr als ein registrierter Kunde mit der E-Mail-Adresse "' . $amazonEmail . '" im Shop gefunden. Es kann keine Account-Zuordnung durchgeführt werden! Prüfen Sie bitte den Datenbestand!', JTLLOG_LEVEL_ERROR);
                $displayMode = 'error';
            } else {
                $kunde = $kunde[0];

                if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTOMERGE] === "Y") {
                    /*
                     * The customer is known in the shop but not for the plugin.
                     * But we should do an auto-merge; i.e. we create a verified mapping and then login the user.
                     */
                    updateLPAAccountMappingForKunde($kunde->kKunde, $amazonId, 1);
                    $kunde = new Kunde($kunde->kKunde); // load kunde object
                    loginUserForKunde($kunde);
                } else {
                    /*
                     * A customer exists, we have to trigger a merge.
                     */
                    // insert not-yet-verified mapping
                    $verificationCode = createLPAAccountMapping($kunde->kKunde, $amazonId);
                    Shop::Smarty()->assign('lpa_login_verification_code', $verificationCode)
                            ->assign('lpa_login_amazon_id', $amazonId);
                    $displayMode = 'merge';
                }
            }
        }
    }
} else {
    // we know that amazon id already
    if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde && $_SESSION['Kunde']->nRegistriert > 0) {
        // ... and there is a registered user logged in
        /*
         * Lets verify that amazon user and the session user match up in mail and amazon id. Else we log an error.
         * Then we should log out the session user and try to log in the "real" user belonging to the amazonId.
         */
        if ((int) $_SESSION['Kunde']->kKunde === (int) $result->kKunde) {
            // email and kKunde match, this is the correct user and he was logged in via Amazon
            // check if we can "auto-verify" the account match
            if ($result->nVerifiziert == 0) {
                // the logged in user did not verify his account yet. However, since he logged in here and in amazon, we can assume that both accounts are legit.
                updateLPAAccountMappingForKunde($result->kKunde, $amazonId, 1);
            }
        } else {
            /*
             * This is a problem - the user currently logged in in the shop is not the user we expected from our matching table.
             * This might be the case if the user has multiple shop accounts and only one amazon account which is mapped to another shop account.
             *
             * We remap the account mapping such that it fits. (The amazon-login can be considered valid and the current shop user should be considered valid as
             * well.) Logging in the other account instead would potentially be a security breach.
             */
            Jtllog::writeLog('LPA: LPA-Login-Fehler: Der angemeldete Kunde (' . $_SESSION['Kunde']->kKunde . ') ist nicht der Kunde, den das Plugin erwartet hätte (' . $result->kKunde . '). Ggf. hat der Kunde zwei Accounts im Shop.', JTLLOG_LEVEL_NOTICE);
            deleteLPAAccountMapping($amazonId);
            createLPAAccountMapping($_SESSION['Kunde']->kKunde, $amazonId, 1);
        }
    } else {
        if (isset($_SESSION['Kunde']) && $_SESSION['Kunde']->kKunde) {
            // ... and an unregistered user is logged in
            /*
             * We have to log out the unregistered user and then proceed as if he was not logged in before.
             */
            logoutUnregisteredUser();
        }
        // ... and no user is logged in
        /*
         * So we log in the user that belongs to that amazonId.
         * Attention: The matching information may be outdated if the user account does not exist anymore, in that case
         * we have to handle this like a new account creation.
         */
        $kunde = null;
        // load registered kunde from database
        $oKundeTMP = Shop::DB()->select('tkunde', 'cMail', $amazonEmail, 'nRegistriert', 1, null, null, false, 'kKunde');
        if (isset($oKundeTMP->kKunde) && $oKundeTMP->kKunde > 0) {
            $kunde = new Kunde($oKundeTMP->kKunde);
        }
        if (isset($kunde) && (int) $kunde->kKunde === (int) $result->kKunde) {
            /*
             * This seems ok, so far. Check if the user is already verified, too.
             */
            if ($result->nVerifiziert == 0 && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTOMERGE] !== "Y") {
                // the logged in user did not verify his account yet. Since he isn't logged in, either, we still need him to verify that he is the right
                // person
                $verificationCode = $result->cVerifizierungsCode;
                Shop::Smarty()->assign('lpa_login_verification_code', $verificationCode)
                        ->assign('lpa_login_amazon_id', $amazonId);
                $displayMode = 'merge';
            } elseif ($result->nVerifiziert == 0 && $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTOMERGE] === "Y") {
                /*
                 * The account was not verified yet, BUT we are set to automerge now.
                 */
                updateLPAAccountMappingForKunde($kunde->kKunde, $amazonId, 1); // set verified
                loginUserForKunde($kunde);
            } else {
                /*
                 * The match up between amazon account and user was already verified, log in the user.
                 */
                loginUserForKunde($kunde);
            }
        } else {
            // the user referenced in our mapping does not exist in the database, at least not with the given kKunde or he is not registered, therefore the mapping must be considered
            // invalid and should be deleted
            deleteLPAAccountMapping($amazonId);

            // now the amazon id is actually unknown to us... this is the same case as if it wasnt known before.
            // we handle this like a normal create or merge
            // first, find the user in the db that has the correct email address
            $kunde = Shop::DB()->select('tkunde', 'cMail', $amazonEmail, 'nRegistriert', 1, null, null, false);
            if (empty($kunde)) {
                // no registered user exists with that mail address, we have to create it
                $verificationCode = createLPAAccountMapping(-1, $amazonId);
                Shop::Smarty()->assign('lpa_login_verification_code', $verificationCode)
                        ->assign('lpa_login_amazon_id', $amazonId);
                $displayMode = 'create';
            } else {
                // a registered user exists, we trigger an account merge
                if ($oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_ADVANCED_AUTOMERGE] === "Y") {
                    // automerge enabled, create new verified mapping and login the user
                    createLPAAccountMapping($kunde->kKunde, $amazonId, 1);
                    $kunde = new Kunde($kunde->kKunde); // load customer object
                    loginUserForKunde($kunde);
                } else {
                    $verificationCode = createLPAAccountMapping($kunde->kKunde, $amazonId);
                    Shop::Smarty()->assign('lpa_login_verification_code', $verificationCode)
                            ->assign('lpa_login_amazon_id', $amazonId);
                    $displayMode = 'merge';
                }
            }
        }
    }
}

$createUrl = str_replace("http://", "https://", Shop::getURL()) . '/lpacreate';
$mergeUrl = str_replace("http://", "https://", Shop::getURL()) . '/lpamerge';
if (Shop::getLanguage(true) === "eng") {
    $createUrl .= '-en';
    $mergeUrl .= '-en';
}
Shop::Smarty()->assign('lpa_create_url_localized', $createUrl)
        ->assign('lpa_merge_url_localized', $mergeUrl)
        ->assign('lpa_create_form_target', $oPlugin->cFrontendPfadURLSSL . 'lpa_create.php')
        ->assign('lpa_merge_form_target', $oPlugin->cFrontendPfadURLSSL . 'lpa_merge.php')
        ->assign('lpa_seller_id', $config['merchant_id']);

if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_create_account_form_custom.tpl')) {
    Shop::Smarty()->assign('lpa_template_path_create_form', $oPlugin->cFrontendPfad . 'template/lpa_create_account_form_custom.tpl');
} else {
    Shop::Smarty()->assign('lpa_template_path_create_form', $oPlugin->cFrontendPfad . 'template/lpa_create_account_form.tpl');
}
if (file_exists($oPlugin->cFrontendPfad . 'template/lpa_merge_account_form_custom.tpl')) {
    Shop::Smarty()->assign('lpa_template_path_merge_form', $oPlugin->cFrontendPfad . 'template/lpa_merge_account_form_custom.tpl');
} else {
    Shop::Smarty()->assign('lpa_template_path_merge_form', $oPlugin->cFrontendPfad . 'template/lpa_merge_account_form.tpl');
}

Shop::Smarty()->assign('laender', gibBelieferbareLaender())
        ->assign('lpa_login_for_checkout', (int) isset($_SESSION['lpa-from-checkout']))
        ->assign('lpa_login_display_mode', $displayMode);

if ($displayMode === 'default') {
    // The login succeeded normally. We can now either redirect to where we want the user to go, or we simply let smarty show the correct template
    redirectToLPACookieLocation();
} else if ($displayMode === 'create') {
    $lpaCreateEinstellungen = Shop::getSettings(array(
                CONF_GLOBAL,
                CONF_RSS,
                CONF_KUNDEN,
                CONF_KUNDENFELD,
                CONF_SONSTIGES,
                CONF_NEWS,
                CONF_SITEMAP,
                CONF_ARTIKELUEBERSICHT,
                CONF_AUSWAHLASSISTENT,
                CONF_CACHING
    ));

    $createMode = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_CHECKOUT_CREATE_MODE];
    $createPassMode = $oPlugin->oPluginEinstellungAssoc_arr[S360_LPA_CONFKEY_CHECKOUT_CREATE_PASSWORD_MODE];
    $createFromCheckout = (int) isset($_SESSION['lpa-from-checkout']);
    if (!$createFromCheckout) {
        // in this case the user chose login directly... createMode is forced to create an account in this case.
        $createMode = "Y";
    }

    Shop::Smarty()->assign('oKundenfeld_arr', gibSelbstdefKundenfelder())
            ->assign('lpaEinstellungen', $lpaCreateEinstellungen)
            ->assign('lpa_create_mode', $createMode)
            ->assign('lpa_create_password_mode', $createPassMode)
            ->assign('nAnzeigeOrt', CHECKBOX_ORT_REGISTRIERUNG);
}