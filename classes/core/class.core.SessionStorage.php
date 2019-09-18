<?php

/**
 * Class SessionStorage
 */
class SessionStorage
{
    /**
     * @var SessionHandler
     */
    protected $_handler;

    /**
     * @var array
     */
    public $sessionData = [];

    /**
     * @param SessionHandler $handler
     * @param array $options
     * @param bool  $start - call session_start()?
     */
    public function __construct($handler = null, array $options = [], $start = true)
    {
        ini_set('session.use_cookies', 1);
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            session_register_shutdown();
        } else {
            register_shutdown_function('session_write_close');
        }

        $this->setHandler($handler, $start);
    }

    /**
     * @param SessionHandler $handler
     * @param bool $start - call session_start()?
     * @return $this
     */
    public function setHandler($handler = null, $start = true)
    {
        $this->_handler = $handler;
        if ($this->_handler === null) {
            $this->_handler = new SessionHandler();
            $res            = true;
        } elseif (get_class($this->_handler) === 'JTL\core\SessionHandler') {
            //native php session handler
            $res = true;
        } elseif ($this->_handler instanceof SessionHandlerInterface) {
            if (version_compare(PHP_VERSION, '7.0', '<')) {
                ini_set('session.save_handler', 'user');
            }
            if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
                $res = session_set_save_handler($this->_handler, true);
            } else {
                $res = session_set_save_handler(
                    [$this->_handler, 'open'],
                    [$this->_handler, 'close'],
                    [$this->_handler, 'read'],
                    [$this->_handler, 'write'],
                    [$this->_handler, 'destroy'],
                    [$this->_handler, 'gc']
                );
            }
        } else {
            throw new \InvalidArgumentException('Must implement \SessionHandlerInterface.');
        }

        if ($res === true) {
            $conf           = Shop::getSettings([CONF_GLOBAL]);
            $cookieDefaults = session_get_cookie_params();
            $set            = false;
            $lifetime       = isset($cookieDefaults['lifetime'])
                ? $cookieDefaults['lifetime']
                : 0;
            $path = isset($cookieDefaults['path'])
                ? $cookieDefaults['path']
                : '';
            $domain = isset($cookieDefaults['domain'])
                ? $cookieDefaults['domain']
                : '';
            $secure = isset($cookieDefaults['secure'])
                ? $cookieDefaults['secure']
                : false;
            $httpOnly = isset($cookieDefaults['httponly'])
                ? $cookieDefaults['httponly']
                : false;
            if (isset($conf['global']['global_cookie_secure']) && $conf['global']['global_cookie_secure'] !== 'S') {
                $set    = true;
                $secure = $conf['global']['global_cookie_secure'] === 'Y';
            }
            if (isset($conf['global']['global_cookie_httponly']) && $conf['global']['global_cookie_httponly'] !== 'S') {
                $set      = true;
                $httpOnly = $conf['global']['global_cookie_httponly'] === 'Y';
            }
            if (isset($conf['global']['global_cookie_domain']) && $conf['global']['global_cookie_domain'] !== '') {
                $set    = true;
                $domain = $conf['global']['global_cookie_domain'];
                //EXPERIMENTAL_MULTILANG_SHOP
                if (defined('EXPERIMENTAL_MULTILANG_SHOP')) {
                    $languages = gibAlleSprachen();
                    foreach ($languages as $Sprache) {
                        if (defined('URL_SHOP_' . strtoupper($Sprache->cISO))) {
                            $shopLangURL = constant('URL_SHOP_' . strtoupper($Sprache->cISO));
                            if (strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false) {
                                if (defined('COOKIE_DOMAIN_' . strtoupper($Sprache->cISO))) {
                                    $domain = constant('COOKIE_DOMAIN_' . strtoupper($Sprache->cISO));
                                    break;
                                }
                            }
                        }
                    }
                }
                //EXPERIMENTAL_MULTILANG_SHOP END
            }
            if (isset($conf['global']['global_cookie_lifetime']) &&
                is_numeric($conf['global']['global_cookie_lifetime']) &&
                (int)$conf['global']['global_cookie_lifetime'] > 0
            ) {
                $set      = true;
                $lifetime = (int)$conf['global']['global_cookie_lifetime'];
            }
            if (!empty($conf['global']['global_cookie_path'])) {
                $set  = true;
                $path = $conf['global']['global_cookie_path'];
            }
            // only set secure if SSL is enabled
            if ($set === true) {
                $secure = $secure &&
                    ($conf['global']['kaufabwicklung_ssl_nutzen'] === 'P' || strpos(URL_SHOP, 'https://') === 0);
            }
            if ($set === true) {
                session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
            }
            if ($start) {
                session_start();
            }
            if ($set === true) {
                $exp = ($lifetime === 0) ? 0 : time() + $lifetime;
                setcookie(session_name(), session_id(), $exp, $path, $domain, $secure, $httpOnly);
            }
            $this->_handler->sessionData = &$_SESSION;
        } else {
            throw new \RuntimeException('Failed to start session');
        }

        return $this;
    }

    /**
     * @return SessionHandler
     */
    public function getHandler()
    {
        return $this->_handler;
    }
}
