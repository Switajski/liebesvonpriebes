<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_SKRILL_VIEW', true, true);

ini_set('allow_url_fopen', 1);

// Each platform has its own login data for communications with MB.
define('MONEYBOOKERS_JTL_CUSTOMER_ID', '22884241');
define('MONEYBOOKERS_JTL_SECRET_WORD_MD5', md5('jtl123'));

// URLs and Emails to communicate with MB
define('MONEYBOOKERS_EMAIL_VALIDATAION_URL', 'https://www.skrill.com/app/email_check.pl?email=%s&cust_id=%s&password=%s');
define('MONEYBOOKERS_SECRET_WORD_VALIDATION_URL', 'https://www.skrill.com/app/secret_word_check.pl?email=%s&secret=%s&cust_id=%s');
define('MONEYBOOKERS_ACTIVATION_EMAIL_ADDRESS', 'ecommerce@moneybookers.com');
define('MONEYBOOKERS_ACTIVATION_EMAIL_SUBJECT', 'Quick Checkout Activation Request');

// Handle Post Requests
$actionError = null;

if (!pruefeALLOWFOPEN()) {
    $actionError = 99;
}

// Valide Email
if (isset($_POST['actionValidateEmail']) && validateToken()) {
    if (pruefeALLOWFOPEN()) {
        $customerId = MONEYBOOKERS_JTL_CUSTOMER_ID;
        $password   = MONEYBOOKERS_JTL_SECRET_WORD_MD5;
        $email      = urlencode($_POST['email']);
        $url        = sprintf(MONEYBOOKERS_EMAIL_VALIDATAION_URL, $email, $customerId, $password);
        $arr        = @file($url);
        $answer     = is_array($arr) ? implode('', $arr) : 'NOK';

        // Answer is NOK or OK,customerId
        $arr = explode(',', $answer);
        if ((count($arr) === 2) && ($arr[0] === 'OK')) {
            $email      = Shop::DB()->escape($_POST['email']);
            $customerId = Shop::DB()->escape($arr[1]);
            $sql        = "INSERT INTO tskrill SET cEmail = '$email', cCustomerId = '$customerId'";
            Shop::DB()->query($sql, 10);

            Jtllog::writeLog('Validierung der Skrill-Freischaltung erfolgreich => URL: ' .
                $url . ' - Return: ' . print_r($arr, true), JTLLOG_LEVEL_DEBUG, false, 'Skrill');
        } else {
            Jtllog::writeLog('Validierung der Skrill-Freischaltung fehlgeschlagen => URL: ' .
                $url . ' - Return: ' . print_r($arr, true), JTLLOG_LEVEL_ERROR, false, 'Skrill');
            $actionError = 1;
        }
    } else {
        Jtllog::writeLog('fopen Wrapper bei der Skrill-Freischaltung fehlgeschlagen.', JTLLOG_LEVEL_ERROR, false, 'Skrill');
        $actionError = 99;
    }
}

// Action Delete
if (isset($_POST['actionDelete']) && validateToken()) {
    $sql = "DELETE FROM tskrill";
    Shop::DB()->query($sql, 10);

    $sql = "UPDATE tzahlungsart SET nActive = 0 WHERE cModulId LIKE 'za_mbqc_%_jtl'";
    Shop::DB()->query($sql, 10);
}

// Activate: send Mail
if (isset($_POST['actionActivate']) && validateToken()) {
    // Load Settings
    global $Einstellungen;

    if (!is_array($Einstellungen)) {
        $Einstellungen = [];
    }
    $Einstellungen = array_merge($Einstellungen, Shop::getSettings([CONF_EMAILS]));

    $sql  = 'SELECT * FROM tskrill';
    $data = Shop::DB()->query($sql, 1);

    $sql     = 'SELECT cName, cUnternehmer FROM tfirma';
    $company = Shop::DB()->query($sql, 1);

    // Create Message
    $body = "Platform Name: JTL-Shop2 \n"
        . "First and last Name of the merchant : " . $company->cName . " " . $company->cUnternehmer . "\n"
        . "Email Address of the merchant : " . $data->cEmail . "\n"
        . "Customer ID of the merchant : " . $data->cCustomerId . "\n"
        . "Shop URL : " . Shop::getURL() . "\n";

    // Content
    $mail               = new stdClass();
    $mail->lang         = 'ger';
    $mail->toEmail      = MONEYBOOKERS_ACTIVATION_EMAIL_ADDRESS;
    $mail->toName       = MONEYBOOKERS_ACTIVATION_EMAIL_ADDRESS;
    $mail->fromEmail    = $Einstellungen['emails']['email_master_absender'];
    $mail->replyToEmail = $Einstellungen['emails']['email_master_absender'];
    $mail->fromName     = $Einstellungen['emails']['email_master_absender_name'];
    $mail->replyToName  = $Einstellungen['emails']['email_master_absender_name'];
    $mail->subject      = MONEYBOOKERS_ACTIVATION_EMAIL_SUBJECT;
    $mail->bodyText     = $body;

    // Method
    $mail->methode       = $Einstellungen['emails']['email_methode'];
    $mail->sendMail_pfad = $Einstellungen['emails']['email_sendmail_pfad'];
    $mail->smtp_hostname = $Einstellungen['emails']['email_smtp_hostname'];
    $mail->smtp_port     = $Einstellungen['emails']['email_smtp_port'];
    $mail->smtp_auth     = $Einstellungen['emails']['email_smtp_auth'];
    $mail->smtp_user     = $Einstellungen['emails']['email_smtp_user'];
    $mail->smtp_pass     = $Einstellungen['emails']['email_smtp_pass'];
    $mail->SMTPSecure    = $Einstellungen['emails']['email_smtp_verschluesselung'];
    // Send
    include_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
    verschickeMail($mail);
    $sql = 'UPDATE tskrill SET dActivationRequest = NOW()';
    Shop::DB()->query($sql, 10);
}

// Validate Secret Word
if (isset($_POST['actionValidateSecretWord']) && validateToken()) {
    if (pruefeALLOWFOPEN()) {
        $sql    = 'SELECT * FROM tskrill';
        $data   = Shop::DB()->query($sql, 1);
        $email  = urlencode($data->cEmail);
        $secret = urlencode(md5(md5($_POST['secretWord']) . MONEYBOOKERS_JTL_SECRET_WORD_MD5));
        $url    = sprintf(MONEYBOOKERS_SECRET_WORD_VALIDATION_URL, $email, $secret, MONEYBOOKERS_JTL_CUSTOMER_ID);
        $arr    = @file($url);
        $answer = is_array($arr) ? implode('', $arr) : 'NOK';

        // Answer is NOK or OK,customerId
        if ($answer === 'OK') {
            $secretWord = Shop::DB()->escape($_POST['secretWord']);
            $sql        = "UPDATE tskrill SET cSecretWord = '$secretWord'";
            Shop::DB()->query($sql, 10);

            $sql = "UPDATE tzahlungsart SET nActive = 1 WHERE cModulId LIKE 'za_mbqc_%_jtl'";
            Shop::DB()->query($sql, 10);
        } elseif ($answer === 'VELOCITY_CHECK_EXCEEDED') {
            $actionError = 2;
            Jtllog::writeLog('Validierung des Skrill-Geheimworts fehlgeschlagen => URL: ' .
                $url . ' - Answer: ' . $answer, JTLLOG_LEVEL_ERROR, false, 'Skrill');
        } else {
            $actionError = 3;
            Jtllog::writeLog('Validierung des Skrill-Geheimworts fehlgeschlagen => URL: ' .
                $url . ' - Answer: ' . $answer, JTLLOG_LEVEL_ERROR, false, 'Skrill');
        }
    } else {
        $actionError = 99;
    }
}

// Action Delete Secret Word
if (isset($_POST['actionDeleteSecretWord']) && validateToken()) {
    $sql = "UPDATE tskrill SET cSecretWord = ''";
    Shop::DB()->query($sql, 10);

    $sql = "UPDATE tzahlungsart SET nActive = 0 WHERE cModulId LIKE 'za_mbqc_%_jtl'";
    Shop::DB()->query($sql, 10);
}

// Load Data from Database
$sql  = "SELECT * FROM tskrill";
$data = Shop::DB()->query($sql, 1);

global $smarty;
if ($data === false || $data === null) {
    $smarty->assign('showEmailInput', true);
} else {
    $smarty->assign('showEmailInput', false)
           ->assign('email', $data->cEmail)
           ->assign('customerId', $data->cCustomerId);

    if ($data->dActivationRequest === '0000-00-00 00:00:00') {
        $smarty->assign('showActivationButton', true);
    } else {
        $smarty->assign('showActivationButton', false)
               ->assign('activationRequest', $data->dActivationRequest);
    }

    if ($data->cSecretWord === '') {
        $smarty->assign('showSecretWordValidation', true);
    } else {
        $smarty->assign('showSecretWordValidation', false)
               ->assign('secretWord', $data->cSecretWord);
    }
}
$smarty->assign('actionError', $actionError)
       ->display('skrill.tpl');
