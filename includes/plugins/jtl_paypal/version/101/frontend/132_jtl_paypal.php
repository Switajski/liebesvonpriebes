<?php
/**
 * HOOK_INDEX_NAVI_HEAD_POSTGET.
 *
 * tracks campaigns
 */

//redirect to paypal express when button on article details page was clicked
if ($oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_express_article'] === 'Y' &&
    isset($_POST['jtl_paypal_redirect']) && $_POST['jtl_paypal_redirect'] === '2' &&
    $oPlugin->oPluginEinstellungAssoc_arr['jtl_paypal_shipping_pre'] === 'N'
) {
    $_SESSION['jtl_paypal_redirect_top'] = 1;
}
//track campaign
if ((isset($_POST['jtl_paypal_redirect']) && $_POST['jtl_paypal_redirect'] === '2') ||
    (isset($_GET['jtl_paypal_checkout_cart']) && $_GET['jtl_paypal_checkout_cart'] === '1' && !isset($_SESSION['jtl_paypal_set_campaign']))
) {
    $oKampagne = false;
    //article details page button
    if (isset($_POST['jtl_paypal_redirect']) && $_POST['jtl_paypal_redirect'] === '2') {
        $sql       = "SELECT * FROM tkampagne WHERE cParameter LIKE 'jtl_paypal_redirect'";
        $oKampagne = (class_exists('Shop')) ? Shop::DB()->query($sql, 1) : $GLOBALS['DB']->executeQuery($sql, 1);
    }
    //cart button
    if (isset($_GET['jtl_paypal_checkout_cart']) && $_GET['jtl_paypal_checkout_cart'] === '1') {
        $sql       = "SELECT * FROM tkampagne WHERE cParameter LIKE 'jtl_paypal_checkout_cart'";
        $oKampagne = (class_exists('Shop')) ? Shop::DB()->query($sql, 1) : $GLOBALS['DB']->executeQuery($sql, 1);
    }
    if ($oKampagne !== false && $oKampagne !== null) {
        $oKampagnenVorgang               = new stdClass();
        $oKampagnenVorgang->kKampagne    = $oKampagne->kKampagne;
        $oKampagnenVorgang->kKampagneDef = 1;
        $oKampagnenVorgang->kKey         = $_SESSION['oBesucher']->kBesucher;
        $oKampagnenVorgang->fWert        = 1.0;
        $oKampagnenVorgang->cParamWert   = $oKampagne->cWert;
        $oKampagnenVorgang->dErstellt    = 'now()';
        if (!function_exists('gibReferer')) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'besucher.php';
        }
        $oKampagnenVorgang->cCustomData = $_SERVER['REQUEST_URI'] . ';' . gibReferer();

        if (class_exists('Shop')) {
            Shop::DB()->insert('tkampagnevorgang', $oKampagnenVorgang);
        } else {
            $GLOBALS['DB']->insertRow('tkampagnevorgang', $oKampagnenVorgang);
        }
        $_SESSION['jtl_paypal_set_campaign'] = 1;
    }
}

if (isset($_POST['jtl_paypal_redirect'])) {
    header('Location: warenkorb.php?jtl_paypal_redirect=1');
}
