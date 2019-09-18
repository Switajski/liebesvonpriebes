<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * HOOK_LETZTERINCLUDE_INC.
 */
if (class_exists('PayPalHelper')) {
    if (($message = PayPalHelper::getFlashMessage())) {
        Shop::Smarty()->assign('hinweis', $message);
        PayPalHelper::clearFlashMessage();
    }
}
