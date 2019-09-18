<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.SessionHandler.php';

/**
 * Class Session
 */
class Session
{
    /**
     * @var string
     */
    const DefaultSession = 'JTLSHOP';

    /**
     * @var string
     */
    protected static $_sessionName = self::DefaultSession;

    /**
     * @var Session
     */
    private static $_instance;

    /**
     * @var SessionHandlerInterface
     */
    protected static $_handler;

    /**
     * @var SessionStorage
     */
    protected static $_storage;

    /**
     * @param bool   $start - call session_start()?
     * @param bool   $force - force new instance?
     * @param string $sessionName - if null, then default to current session name
     * @return Session
     */
    public static function getInstance($start = true, $force = false, $sessionName = self::DefaultSession)
    {
        if (self::$_sessionName !== $sessionName) {
            $force = true;
        }
        if ($force === true) {
            return new self($start, $sessionName);
        }

        return (self::$_instance === null)
            ? new self($start, $sessionName)
            : self::$_instance;
    }

    /**
     * @param bool   $start - call session_start()?
     * @param string $sessionName
     */
    public function __construct($start = true, $sessionName = self::DefaultSession)
    {
        self::$_instance    = $this;
        self::$_sessionName = $sessionName;
        $bot                = false;
        $saveBotSession     = 0;
        if (defined('SAVE_BOT_SESSION') && isset($_SERVER['HTTP_USER_AGENT'])) {
            $saveBotSession = (int)SAVE_BOT_SESSION;
            $bot            = self::getIsCrawler($_SERVER['HTTP_USER_AGENT']);
        }
        session_name(self::$_sessionName);
        if ($bot === false || $saveBotSession === 0) {
            if (ES_SESSIONS === 1) { // Sessions in DB speichern
                self::$_handler = new SessionHandlerDB();
            } else {
                self::$_handler = new \JTL\core\SessionHandler();
            }
            self::$_storage = new SessionStorage(self::$_handler, [], $start);
            $this->setStandardSessionVars();
        } else {
            if ($saveBotSession === 1 || $saveBotSession === 2) {
                session_id('jtl-bot');
            }
            if ($saveBotSession === 2 || $saveBotSession === 3) {
                $save = false;
                if ($saveBotSession === 2 && (Shop::Cache()->isAvailable() && Shop::Cache()->isActive())) {
                    $save = true;
                }
                self::$_handler = new SessionHandlerBot($save);
                self::$_storage = new SessionStorage(self::$_handler);
                $this->setStandardSessionVars();
            } else {
                self::$_handler = new \JTL\core\SessionHandler();
                self::$_storage = new SessionStorage(self::$_handler);
                $this->setStandardSessionVars();
            }
        }
        defined('SID') || define('SID', '');
        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);

        executeHook(HOOK_CORE_SESSION_CONSTRUCTOR);
    }

    /**
     * @param string $userAgent
     * @return bool
     */
    public static function getIsCrawler($userAgent)
    {
        return preg_match(
            '/Google|ApacheBench|sqlmap|loader.io|bot|Rambler|Yahoo|AbachoBOT|accoona' .
            '|spider|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot' .
            '|Gigabot|Lycos|alexa|AltaVista|IDBot|Scrubby/', $userAgent
        ) > 0;
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::$_handler->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public static function set($key, $value)
    {
        return self::$_handler->set($key, $value);
    }

    /**
     * setzt Sessionvariablen beim ersten Sessionaufbau oder wenn globale Daten aktualisiert werden müssen
     *
     * @return $this
     */
    public function setStandardSessionVars()
    {
        $globalsAktualisieren = true;
        $updateLanguage       = false;
        Shop::Lang()->autoload();
        $_SESSION['FremdParameter'] = [];

        if (!isset($_SESSION['Warenkorb'])) {
            $_SESSION['Warenkorb'] = new Warenkorb();
        }
        if (isset($_SESSION['Globals_TS'])) {
            $globalsAktualisieren = false;
            $ts                   = Shop::DB()->executeQueryPrepared("
                  SELECT dLetzteAenderung 
                      FROM tglobals 
                      WHERE dLetzteAenderung > :ts",
                ['ts' => $_SESSION['Globals_TS']],
                1
            );
            if (isset($ts->dLetzteAenderung)) {
                $_SESSION['Globals_TS'] = $ts->dLetzteAenderung;
                $globalsAktualisieren   = true;
            }
        } else {
            $ts                     = Shop::DB()->query("SELECT dLetzteAenderung FROM tglobals", 1);
            $_SESSION['Globals_TS'] = $ts->dLetzteAenderung;
        }
        if (isset($_GET['lang']) && (!isset($_SESSION['cISOSprache']) || $_GET['lang'] !== $_SESSION['cISOSprache'])) {
            $globalsAktualisieren = true;
            $updateLanguage       = true;
        }
        $lang    = isset($_GET['lang']) ? $_GET['lang'] : '';
        $checked = false;
        if (isset($_SESSION['kSprache'])) {
            checkeSpracheWaehrung($lang);
            $checked = true;
        }
        if ($globalsAktualisieren ||
            !isset($_SESSION['cISOSprache'], $_SESSION['kSprache'], $_SESSION['Kundengruppe'])
        ) {
            //Kategorie
            unset($_SESSION['cTemplate'], $_SESSION['template'], $_SESSION['oKategorie_arr_new']);
            $_SESSION['oKategorie_arr']                   = [];
            $_SESSION['kKategorieVonUnterkategorien_arr'] = [];
            $_SESSION['ks']                               = [];
            $_SESSION['Waehrungen']                       = Shop::DB()->query("SELECT * FROM twaehrung", 2);
            $_SESSION['Sprachen']                         = Sprache::getInstance(false)->gibInstallierteSprachen();
            if (!isset($_SESSION['jtl_token'])) {
                $_SESSION['jtl_token'] = generateCSRFToken();
            }
            array_map(function ($lang) {
                $lang->kSprache = (int)$lang->kSprache;

                return $lang;
            }, $_SESSION['Sprachen']);
            array_map(function ($currency) {
                $currency->kWaehrung = (int)$currency->kWaehrung;

                return $currency;
            }, $_SESSION['Waehrungen']);
            // Sprache anhand der Browsereinstellung ermitteln
            $cLangDefault = '';
            $cAllowed_arr = [];
            foreach ($_SESSION['Sprachen'] as $oSprache) {
                $cISO              = StringHandler::convertISO2ISO639($oSprache->cISO);
                $oSprache->cISO639 = $cISO;
                $cAllowed_arr[]    = $cISO;
                if ($oSprache->cShopStandard === 'Y') {
                    $cLangDefault = $cISO;
                }
            }
            $cDefaultLanguage = $this->getBrowserLanguage($cAllowed_arr, $cLangDefault);
            $cDefaultLanguage = StringHandler::convertISO6392ISO($cDefaultLanguage);

            if (!isset($_SESSION['kSprache'])) {
                foreach ($_SESSION['Sprachen'] as $Sprache) {
                    if ($Sprache->cISO === $cDefaultLanguage || (empty($cDefaultLanguage) && $Sprache->cShopStandard === 'Y')) {
                        $_SESSION['kSprache']    = $Sprache->kSprache;
                        $_SESSION['cISOSprache'] = trim($Sprache->cISO);
                        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                        $_SESSION['currentLanguage'] = clone $Sprache;
                        break;
                    }
                }
            }
            if (!isset($_SESSION['Waehrung'])) {
                foreach ($_SESSION['Waehrungen'] as $Waehrung) {
                    if ($Waehrung->cStandard === 'Y') {
                        memberCopy($Waehrung, $_SESSION['Waehrung']);
                        $_SESSION['cWaehrungName'] = $Waehrung->cName;
                    }
                }
            } else {
                foreach ($_SESSION['Waehrungen'] as $Waehrung) {
                    if ($Waehrung->cISO === $_SESSION['Waehrung']->cISO) {
                        memberCopy($Waehrung, $_SESSION['Waehrung']);
                        $_SESSION['cWaehrungName'] = $Waehrung->cName;
                    }
                }
            }
            //EXPERIMENTAL_MULTILANG_SHOP
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                if (defined('URL_SHOP_' . strtoupper($Sprache->cISO))) {
                    $shopLangURL = constant('URL_SHOP_' . strtoupper($Sprache->cISO));
                    if (strpos($shopLangURL, $_SERVER['HTTP_HOST']) !== false) {
                        $_SESSION['kSprache']    = $Sprache->kSprache;
                        $_SESSION['cISOSprache'] = trim($Sprache->cISO);
                        Shop::setLanguage($_SESSION['kSprache'], $_SESSION['cISOSprache']);
                        break;
                    }
                }
            }
            //EXPERIMENTAL_MULTILANG_SHOP END

            if (!isset($_SESSION['Kunde']->kKunde)) {
                $_SESSION['Kundengruppe']                             = Kundengruppe::getDefault();
                $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
                $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
                $conf                                                 = Shop::getSettings([CONF_GLOBAL]);
                if ($_SESSION['Kundengruppe']->cStandard === 'Y' && (int)$conf['global']['global_sichtbarkeit'] === 2) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen = 0;
                }
                if ($_SESSION['Kundengruppe']->cStandard === 'Y' && (int)$conf['global']['global_sichtbarkeit'] === 3) {
                    $_SESSION['Kundengruppe']->darfPreiseSehen            = 0;
                    $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 0;
                }
                if (isset($_SESSION['Kundengruppe']->kKundengruppe, $_SESSION['kSprache']) &&
                    $_SESSION['Kundengruppe']->kKundengruppe &&
                    $_SESSION['kSprache'] > 0
                ) {
                    $oKundengruppeSprache = Shop::DB()->select(
                        'tkundengruppensprache',
                        'kKundengruppe',
                        (int)$_SESSION['Kundengruppe']->kKundengruppe,
                        'kSprache',
                        (int)$_SESSION['kSprache']
                    );
                    if (isset($oKundengruppeSprache->cName)) {
                        $_SESSION['Kundengruppe']->cNameLocalized = $oKundengruppeSprache->cName;
                    }
                }
            } elseif ($globalsAktualisieren && $updateLanguage) {
                // Kundensprache ändern, wenn im eingeloggten Zustand die Sprache geändert wird
                /** @var array('Kunde' => Kunde) $_SESSION */
                $_SESSION['Kunde']->kSprache = $_SESSION['kSprache'];
                $_SESSION['Kunde']->updateInDB();
            }
            $_SESSION['Kundengruppe']->Attribute = Kundengruppe::getAttributes($_SESSION['Kundengruppe']->kKundengruppe);
            $linkHelper                          = LinkHelper::getInstance();
            $linkGroups                          = $linkHelper->getLinkGroups();
            if (TEMPLATE_COMPATIBILITY === true || Shop::Cache()->isCacheGroupActive(CACHING_GROUP_CORE) === false) {
                $_SESSION['Linkgruppen'] = $linkGroups;
                $manufacturerHelper      = HerstellerHelper::getInstance();
                $manufacturers           = $manufacturerHelper->getManufacturers();
                $_SESSION['Hersteller']  = $manufacturers;
            }
            if (TEMPLATE_COMPATIBILITY === true) {
                /**
                 * Zahlungsarten Ticket #6042
                 * @depcrecated since 4.05
                 */
                $_SESSION['Zahlungsarten'] = Zahlungsart::loadAll();
                /**
                 * Lieferlaender Ticket #6042
                 * @depcrecated since 4.05
                 */
                $_SESSION['Lieferlaender'] = Shop::DB()->query(
                    "SELECT l.* 
                        FROM tland AS l
                        JOIN tversandart AS v 
                            ON v.cLaender LIKE CONCAT('%', l.cISO, '%')
                        GROUP BY l.cISO", 2
                );
            }
            $_SESSION['Warenkorb']->loescheDeaktiviertePositionen();
            setzeSteuersaetze();
            // sprache neu laden
            Shop::Lang()->reset();
        }
        if (!$checked) {
            checkeSpracheWaehrung($lang);
        }
        getFsession();
        $this->checkWishlistDeletes()->checkComparelistDeletes();
        // Kampagnen in die Session laden
        Kampagne::getAvailable();
        if (!isset($_SESSION['cISOSprache'])) {
            session_destroy();
            die(utf8_decode('<h1>Ihr Shop wurde installiert. Lesen Sie in unserem Guide ' .
                '<a href="https://guide.jtl-software.de/jtl/JTL-Shop:Installation:Erste_Schritte#Einrichtung_und_Grundkonfiguration">' .
                'mehr zu ersten Schritten mit JTL-Shop, der Grundkonfiguration und dem erstem Abgleich mit JTL-Wawi</a>.</h1>'));
        }

        //wurde kunde über wawi aktualisiert?
        if (isset($_SESSION['Kunde']->kKunde) &&
            $_SESSION['Kunde']->kKunde > 0 &&
            !isset($_SESSION['kundendaten_aktualisiert'])
        ) {
            $Kunde = Shop::DB()->query(
                "SELECT kKunde
                    FROM tkunde
                    WHERE kKunde = " . (int)$_SESSION['Kunde']->kKunde . "
                        AND date_sub(now(), INTERVAL 3 HOUR) < dVeraendert", 1
            );
            if (isset($Kunde->kKunde) && $Kunde->kKunde > 0) {
                $oKunde = new Kunde($_SESSION['Kunde']->kKunde);
                $this->setCustomer($oKunde);
                $_SESSION['kundendaten_aktualisiert'] = 1;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkWishlistDeletes()
    {
        $kWunschlistePos = verifyGPCDataInteger('wlplo');
        if ($kWunschlistePos !== 0) {
            $CWunschliste = new Wunschliste();
            $CWunschliste->entfernePos($kWunschlistePos);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function checkComparelistDeletes()
    {
        $kVergleichlistePos = verifyGPCDataInteger('vlplo');
        if ($kVergleichlistePos !== 0) {
            if (isset($_SESSION['Vergleichsliste']->oArtikel_arr) &&
                is_array($_SESSION['Vergleichsliste']->oArtikel_arr) &&
                count($_SESSION['Vergleichsliste']->oArtikel_arr) > 0
            ) {
                // Wunschliste Position aus der Session löschen
                foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $oArtikel) {
                    if ((int)$oArtikel->kArtikel === $kVergleichlistePos) {
                        unset($_SESSION['Vergleichsliste']->oArtikel_arr[$i]);
                    }
                }
                // Ist nach dem Löschen des Artikels aus der Vergleichslite kein weiterer Artikel vorhanden?
                if (count($_SESSION['Vergleichsliste']->oArtikel_arr) === 0) {
                    unset($_SESSION['Vergleichsliste']);
                } else {
                    // Positionen Array in der Wunschliste neu nummerieren
                    $_SESSION['Vergleichsliste']->oArtikel_arr = array_merge($_SESSION['Vergleichsliste']->oArtikel_arr);
                }
                if (!isset($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], 'index.php') !== false) {
                    http_response_code(301);
                    header('Location: ' . Shop::getURL() . '/');
                    exit;
                }
            }
        }

        return $this;
    }

    /**
     * @param array  $cAllowed_arr
     * @param string $cDefault
     * @return string
     */
    public function getBrowserLanguage($cAllowed_arr, $cDefault)
    {
        $cLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;

        if (empty($cLanguage)) {
            return $cDefault;
        }

        $cAccepted_arr   = preg_split('/,\s*/', $cLanguage);
        $cCurrentLang    = $cDefault;
        $nCurrentQuality = 0;

        foreach ($cAccepted_arr as $cAccepted) {
            $res = preg_match(
                '/^([a-z]{1,8}(?:-[a-z]{1,8})*)' .
                '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $cAccepted, $cMatch_arr
            );
            if (!$res) {
                continue;
            }
            $cLangeCode   = explode('-', $cMatch_arr[1]);
            $nLangQuality = isset($cMatch_arr[2])
                ? (float)$cMatch_arr[2]
                : 1.0;
            while (count($cLangeCode)) {
                if (in_array(strtolower(implode('-', $cLangeCode)), $cAllowed_arr, true)) {
                    if ($nLangQuality > $nCurrentQuality) {
                        $cCurrentLang    = strtolower(implode('-', $cLangeCode));
                        $nCurrentQuality = $nLangQuality;
                        break;
                    }
                }
                array_pop($cLangeCode);
            }
        }

        return $cCurrentLang;
    }

    /**
     * @return $this
     */
    public function cleanUp()
    {
        // Unregistrierten Benutzer löschen
        if (isset($_SESSION['Kunde']->nRegistriert) && $_SESSION['Kunde']->nRegistriert == 0) {
            unset($_SESSION['Kunde']);
        }

        unset(
            $_SESSION['Zahlungsart'],
            $_SESSION['Warenkorb'],
            $_SESSION['Versandart'],
            $_SESSION['Lieferadresse'],
            $_SESSION['VersandKupon'],
            $_SESSION['NeukundenKupon'],
            $_SESSION['Kupon'],
            $_SESSION['GuthabenLocalized'],
            $_SESSION['Bestellung'],
            $_SESSION['Warenkorb'],
            $_SESSION['IP'],
            $_SESSION['TrustedShops'],
            $_SESSION['kommentar']
        );
        $_SESSION['Warenkorb'] = new Warenkorb();
        // WarenkorbPers loeschen
        $oWarenkorbPers = new WarenkorbPers((isset($_SESSION['Kunde']->kKunde) ? $_SESSION['Kunde']->kKunde : 0));
        $oWarenkorbPers->entferneAlles();

        return $this;
    }

    /**
     * @param Kunde $Kunde
     * @return $this
     */
    public function setCustomer($Kunde)
    {
        /** @var array('Warenkorb' => Warenkorb) $_SESSION */
        $Kunde->angezeigtesLand                               = ISO2land($Kunde->cLand);
        $_SESSION['Kunde']                                    = $Kunde;
        $_SESSION['Kundengruppe']                             = Shop::DB()->select(
            'tkundengruppe',
            'kKundengruppe',
            (int)$Kunde->kKundengruppe
        );
        $_SESSION['Kundengruppe']->darfPreiseSehen            = 1;
        $_SESSION['Kundengruppe']->darfArtikelKategorienSehen = 1;
        $_SESSION['Kundengruppe']->Attribute                  =
            Kundengruppe::getAttributes($_SESSION['Kundengruppe']->kKundengruppe);
        $_SESSION['Warenkorb']->setzePositionsPreise();
        setzeSteuersaetze();
        setzeLinks();

        return $this;
    }

    /**
     * @return Kunde
     */
    public function Customer()
    {
        return $_SESSION['Kunde'];
    }

    /**
     * @return stdClass
     */
    public function CustomerGroup()
    {
        return $_SESSION['Kundengruppe'];
    }

    /**
     * @return Sprache
     */
    public function Language()
    {
        $o              = Sprache::getInstance(false);
        $o->kSprache    = $_SESSION['kSprache'];
        $o->kSprachISO  = $_SESSION['kSprache'];
        $o->cISOSprache = $_SESSION['cISOSprache'];

        return $o;
    }

    /**
     * @return array
     */
    public function Languages()
    {
        return $_SESSION['Sprachen'];
    }

    /**
     * @return array
     */
    public function Payments()
    {
        return $_SESSION['Zahlungsarten'];
    }

    /**
     * @return stdClass
     */
    public function DeliveryCountries()
    {
        return $_SESSION['Lieferlaender'];
    }

    /**
     * @return stdClass
     */
    public function Currency()
    {
        return $_SESSION['Waehrung'];
    }

    /**
     * @return mixed
     */
    public function Currencies()
    {
        return $_SESSION['Waehrungen'];
    }

    /**
     * @return Warenkorb
     */
    public function Basket()
    {
        return $_SESSION['Warenkorb'];
    }

    /**
     * @return array
     * @deprecated since 4.00
     */
    public function Manufacturers()
    {
        return $_SESSION['Hersteller'];
    }

    /**
     * @return array
     * @deprecated since 4.00
     */
    public function LinkGroups()
    {
        return $_SESSION['Linkgruppen'];
    }

    /**
     * @return array
     * @deprecated since 4.00
     */
    public function Categories()
    {
        return $_SESSION['oKategorie_arr'];
    }
}
