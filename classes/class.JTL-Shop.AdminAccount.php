<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES_LIBS . 'password_compat/password.php';

/**
 * Class AdminAccount
 */
class AdminAccount
{
    /**
     * @var bool
     */
    private $_bLogged = false;

    /**
     * @var bool
     */
    private $twoFaAuthenticated = false;

    /**
     * @param bool $bInitialize
     */
    public function __construct($bInitialize = true)
    {
        if ($bInitialize) {
            AdminSession::getInstance();
            $this->_validateSession();
        }
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * checks user submitted hash against the ones saved in db
     *
     * @param string $hash - the hash received via email
     * @param string $mail - the admin account's email address
     * @return bool - true if successfully verified
     */
    public function verifyResetPasswordHash($hash, $mail)
    {
        $user = Shop::DB()->select('tadminlogin', 'cMail', $mail);
        if ($user !== null) {
            //there should be a string <created_timestamp>:<hash> in the DB
            $timestampAndHash = explode(':', $user->cResetPasswordHash);
            if (count($timestampAndHash) === 2) {
                $timeStamp    = $timestampAndHash[0];
                $originalHash = $timestampAndHash[1];
                //check if the link is not expired (=24 hours valid)
                $createdAt = new DateTime();
                $createdAt->setTimestamp((int)$timeStamp);
                $now  = new DateTime();
                $diff = $now->diff($createdAt);
                $secs = ($diff->format('%a') * (60 * 60 * 24)); //total days
                $secs += (int)$diff->format('%h') * (60 * 60); //hours
                $secs += (int)$diff->format('%i') * 60; //minutes
                $secs += (int)$diff->format('%s'); //seconds
                if ($secs > (60 * 60 * 24)) {
                    return false;
                }
                //check the submitted hash against the saved one
                return password_verify($hash, $originalHash);
            }
        }

        return false;
    }

    /**
     * creates hashes and sends mails for forgotten admin passwords
     *
     * @param string $mail - the admin account's email address
     * @return bool - true if valid admin account
     */
    public function prepareResetPassword($mail)
    {
        $now                      = new DateTime();
        $timestamp                = $now->format('U');
        $stringToSend             = md5($mail . microtime(true));
        $_upd                     = new stdClass();
        $_upd->cResetPasswordHash = $timestamp . ':' . password_hash($stringToSend, PASSWORD_DEFAULT);
        $res                      = Shop::DB()->update('tadminlogin', 'cMail', $mail, $_upd);
        if ($res > 0) {
            $user = Shop::DB()->select('tadminlogin', 'cMail', $mail);
            require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
            $obj                    = new stdClass();
            $obj->passwordResetLink = Shop::getAdminURL() . '/pass.php?fpwh=' . $stringToSend . '&mail=' . $mail;
            $obj->cHash             = $stringToSend;
            $obj->mail              = new stdClass();
            $obj->mail->toEmail     = $mail;
            $obj->mail->toName      = $user->cLogin;
            sendeMail(MAILTEMPLATE_ADMINLOGIN_PASSWORT_VERGESSEN, $obj);

            return true;
        }

        return false;
    }

    /**
     * @param string $cLogin
     * @param string $cPass
     * @return int
     */
    public function login($cLogin, $cPass)
    {
        $oAdmin = Shop::DB()->select(
            'tadminlogin',
            'cLogin',
            $cLogin,
            null,
            null,
            null,
            null,
            false,
            '*, UNIX_TIMESTAMP(dGueltigBis) AS dGueltigTS'
        );
        if (!is_object($oAdmin)) {
            return -3;
        }
        if (!$oAdmin->bAktiv && $oAdmin->kAdminlogingruppe != ADMINGROUP) {
            return -4;
        }
        if ($oAdmin->dGueltigTS && $oAdmin->kAdminlogingruppe != ADMINGROUP) {
            if ($oAdmin->dGueltigTS < time()) {
                return -5;
            }
        }
        $verified     = false;
        $cPassCrypted = null;
        if (strlen($oAdmin->cPass) === 32) {
            // old md5 hash support
            $oAdminTmp = Shop::DB()->select('tadminlogin', 'cLogin', $cLogin, 'cPass', md5($cPass));
            if (!isset($oAdminTmp->cLogin)) {
                //login failed
                $this->_setRetryCount($oAdmin->cLogin);

                return (($oAdmin->nLoginVersuch + 1) >= 3) ? -2 : -1;
            }
            if (!isset($_SESSION['AdminAccount'])) {
                $_SESSION['AdminAccount'] = new stdClass();
            }
            //login successful - update password hash
            $_SESSION['AdminAccount']->cPass  = md5($cPass);
            $_SESSION['AdminAccount']->cLogin = $cLogin;
            $verified                         = true;
            if ($this->checkAndUpdateHash($cPass) === true) {
                $oAdmin = Shop::DB()->select(
                    'tadminlogin',
                    'cLogin',
                    $cLogin,
                    null,
                    null,
                    null,
                    null,
                    false,
                    '*, UNIX_TIMESTAMP(dGueltigBis) AS dGueltigTS'
                );
            }
        } elseif (strlen($oAdmin->cPass) === 40) {
            //default login until Shop4
            $cPassCrypted = cryptPasswort($cPass, $oAdmin->cPass);
        } else {
            //new default login from 4.0 on
            $verified = password_verify($cPass, $oAdmin->cPass);
        }
        if ($verified === true || ($cPassCrypted !== null && $oAdmin->cPass === $cPassCrypted)) {
            // Wartungsmodus aktiv? Nein => loesche Session
            $settings = Shop::getSettings(CONF_GLOBAL);
            if ($settings['global']['wartungsmodus_aktiviert'] === 'N') {
                if (is_array($_SESSION) && count($_SESSION) > 0) {
                    foreach ($_SESSION as $i => $xSession) {
                        unset($_SESSION[$i]);
                    }
                }
            }

            $this->_toSession($oAdmin);
            //check password hash and update if necessary
            $this->checkAndUpdateHash($cPass);
            if (!$this->getIsTwoFaAuthenticated()) {
                return -6;
            }

            return $this->logged() ? 1 : 0;
        }
        $this->_setRetryCount($oAdmin->cLogin);

        return (($oAdmin->nLoginVersuch + 1) >= 3) ? -2 : -1;
    }

    /**
     * @return $this
     */
    public function logout()
    {
        $this->_bLogged = false;
        session_destroy();

        return $this;
    }

    /**
     * @return $this
     */
    public function lock()
    {
        $this->_bLogged = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function logged()
    {
        return $this->getIsTwoFaAuthenticated() && $this->getIsAuthenticated();
    }

    /**
     * @return bool
     */
    public function getIsAuthenticated()
    {
        return $this->_bLogged;
    }

    /**
     * @return bool
     */
    public function getIsTwoFaAuthenticated()
    {
        return $this->twoFaAuthenticated;
    }

    /**
     *
     */
    public function redirectOnFailure()
    {
        if (!$this->logged()) {
            $url = (strpos(basename($_SERVER['REQUEST_URI']), 'logout.php') === false)
                ? '?uri=' . base64_encode(basename($_SERVER['REQUEST_URI']))
                : '';
            header('Location: index.php' . $url);
            exit();
        }
    }

    /**
     * @return bool|stdClass
     */
    public function account()
    {
        return $this->getIsAuthenticated() ? $_SESSION['AdminAccount'] : false;
    }

    /**
     * @param string $cRecht
     * @param bool   $bRedirectToLogin
     * @param bool   $bShowNoAccessPage
     * @return bool
     */
    public function permission($cRecht, $bRedirectToLogin = false, $bShowNoAccessPage = false)
    {
        if ($bRedirectToLogin) {
            $this->redirectOnFailure();
        }
        // grant full access to admin
        if ($this->account() !== false && $this->account()->oGroup->kAdminlogingruppe == ADMINGROUP) {
            return true;
        }
        $bAccess = (isset($_SESSION['AdminAccount']->oGroup) && is_object($_SESSION['AdminAccount']->oGroup) &&
            is_array($_SESSION['AdminAccount']->oGroup->oPermission_arr) &&
            in_array($cRecht, $_SESSION['AdminAccount']->oGroup->oPermission_arr));
        if ($bShowNoAccessPage && !$bAccess) {
            Shop::Smarty()->display('tpl_inc/berechtigung.tpl');
            exit;
        }

        return $bAccess;
    }

    /**
     *
     */
    public function redirectOnUrl()
    {
        $cUrl       = Shop::getURL() . '/' . PFAD_ADMIN . 'index.php';
        $xParse_arr = parse_url($cUrl);
        $cHost      = $xParse_arr['host'];

        if (!empty($xParse_arr['port']) && (int)$xParse_arr['port'] > 0) {
            $cHost .= ':' . $xParse_arr['port'];
        }

        if (isset($_SERVER['HTTP_HOST']) && $cHost !== $_SERVER['HTTP_HOST'] && strlen($_SERVER['HTTP_HOST']) > 0) {
            header("Location: {$cUrl}");
            exit;
        }
    }

    /**
     * @return $this
     */
    private function _validateSession()
    {
        $this->_bLogged = false;
        if (isset($_SESSION['AdminAccount']->cLogin, $_SESSION['AdminAccount']->cPass, $_SESSION['AdminAccount']->cURL) &&
            $_SESSION['AdminAccount']->cURL === Shop::getURL()
        ) {
            $oAccount = Shop::DB()->select(
                'tadminlogin',
                'cLogin', $_SESSION['AdminAccount']->cLogin,
                'cPass', $_SESSION['AdminAccount']->cPass
            );
            $this->twoFaAuthenticated = (isset($oAccount->b2FAauth) && $oAccount->b2FAauth === '1')
                ? (isset($_SESSION['AdminAccount']->TwoFA_valid) && true === $_SESSION['AdminAccount']->TwoFA_valid)
                : true;
            $this->_bLogged = isset($oAccount->cLogin);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function doTwoFA()
    {
        if (isset($_SESSION['AdminAccount']->cLogin, $_POST['TwoFA_code'])) {
            $oTwoFA = new TwoFA();
            $oTwoFA->setUserByName($_SESSION['AdminAccount']->cLogin);
            // check the 2fa-code here really
            $this->twoFaAuthenticated = $_SESSION['AdminAccount']->TwoFA_valid = $oTwoFA->isCodeValid($_POST['TwoFA_code']);

            return $this->twoFaAuthenticated;
        }

        return false;
    }

    /**
     * @return array|int
     */
    public function favorites()
    {
        if (!$this->logged()) {
            return [];
        }

        return AdminFavorite::fetchAll($_SESSION['AdminAccount']->kAdminlogin);
    }

    /**
     * @param stdClass $oAdmin
     * @return $this
     */
    private function _toSession($oAdmin)
    {
        $oGroup = $this->_getPermissionsByGroup($oAdmin->kAdminlogingruppe);
        if (is_object($oGroup) || $oAdmin->kAdminlogingruppe == ADMINGROUP) {
            $_SESSION['AdminAccount']              = new stdClass();
            $_SESSION['AdminAccount']->cURL        = Shop::getURL();
            $_SESSION['AdminAccount']->kAdminlogin = $oAdmin->kAdminlogin;
            $_SESSION['AdminAccount']->cLogin      = $oAdmin->cLogin;
            $_SESSION['AdminAccount']->cMail       = $oAdmin->cMail;
            $_SESSION['AdminAccount']->cPass       = $oAdmin->cPass;

            if (!is_object($oGroup)) {
                $oGroup                    = new stdClass();
                $oGroup->kAdminlogingruppe = ADMINGROUP;
            }

            $_SESSION['AdminAccount']->oGroup = $oGroup;

            $this->_setLastLogin($oAdmin->cLogin)
                 ->_setRetryCount($oAdmin->cLogin, true)
                 ->_validateSession();
        }

        return $this;
    }

    /**
     * @param string $cLogin
     * @return $this
     */
    private function _setLastLogin($cLogin)
    {
        $_upd                = new stdClass();
        $_upd->dLetzterLogin = 'now()';
        Shop::DB()->update('tadminlogin', 'cLogin', $cLogin, $_upd);

        return $this;
    }

    /**
     * @param string $cLogin
     * @param bool   $bReset
     * @return $this
     */
    private function _setRetryCount($cLogin, $bReset = false)
    {
        if ($bReset) {
            $_upd                = new stdClass();
            $_upd->nLoginVersuch = 0;
            Shop::DB()->update('tadminlogin', 'cLogin', $cLogin, $_upd);
        } else {
            Shop::DB()->executeQueryPrepared("
                UPDATE tadminlogin
                    SET nLoginVersuch = nLoginVersuch+1
                    WHERE cLogin = :login", ['login' => $cLogin], 3
            );
        }

        return $this;
    }

    /**
     * @param int $kAdminlogingruppe
     * @return bool|object
     */
    private function _getPermissionsByGroup($kAdminlogingruppe)
    {
        $kAdminlogingruppe = (int)$kAdminlogingruppe;
        $oGroup            = Shop::DB()->select('tadminlogingruppe', 'kAdminlogingruppe', $kAdminlogingruppe);
        if (isset($oGroup->kAdminlogingruppe)) {
            $oPermission_arr = Shop::DB()->selectAll(
                'tadminrechtegruppe',
                'kAdminlogingruppe',
                $kAdminlogingruppe,
                'cRecht'
            );
            if (is_array($oPermission_arr)) {
                $oGroup->oPermission_arr = [];
                foreach ($oPermission_arr as $oPermission) {
                    $oGroup->oPermission_arr[] = $oPermission->cRecht;
                }

                return $oGroup;
            }
        }

        return false;
    }

    /**
     * @param string $password
     * @return false|string
     */
    public static function generatePasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * update password hash if necessary
     *
     * @param string $password
     * @return bool - true when hash was updated
     */
    private function checkAndUpdateHash($password)
    {
        //only update hash if the db update to 4.00+ was already executed
        if (isset($_SESSION['AdminAccount']->cPass, $_SESSION['AdminAccount']->cLogin) &&
            password_needs_rehash($_SESSION['AdminAccount']->cPass, PASSWORD_DEFAULT) &&
            version_compare(Shop::getShopVersion(), 400, '>=') === true
        ) {
            $_upd        = new stdClass();
            $_upd->cPass = password_hash($password, PASSWORD_DEFAULT);
            Shop::DB()->update('tadminlogin', 'cLogin', $_SESSION['AdminAccount']->cLogin, $_upd);

            return true;
        }

        return false;
    }
}
