<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Shop
 * @method static NiceDB DB()
 * @method static JTLCache Cache()
 * @method static Sprache Lang()
 * @method static JTLSmarty Smarty(bool $fast_init = false, bool $isAdmin = false)
 * @method static Media Media()
 * @method static EventDispatcher Event()
 * @method static bool has(string $key)
 * @method static Shop set(string $key, mixed $value)
 * @method static null|mixed get($key)
 */
final class Shop
{
    /**
     * @var int
     */
    public static $kSprache;

    /**
     * @var string
     */
    public static $cISO;

    /**
     * @var int
     */
    public static $kKonfigPos;

    /**
     * @var int
     */
    public static $kKategorie;

    /**
     * @var int
     */
    public static $kArtikel;

    /**
     * @var int
     */
    public static $kVariKindArtikel;

    /**
     * @var int
     */
    public static $kSeite;

    /**
     * @var int
     */
    public static $kLink;

    /**
     * @var int
     */
    public static $kHersteller;

    /**
     * @var int
     */
    public static $kSuchanfrage;

    /**
     * @var int
     */
    public static $kMerkmalWert;

    /**
     * @var int
     */
    public static $kTag;

    /**
     * @var int
     */
    public static $kSuchspecial;

    /**
     * @var int
     */
    public static $kNews;

    /**
     * @var int
     */
    public static $kNewsMonatsUebersicht;

    /**
     * @var int
     */
    public static $kNewsKategorie;

    /**
     * @var int
     */
    public static $kUmfrage;

    /**
     * @var int
     */
    public static $nBewertungSterneFilter;

    /**
     * @var string
     */
    public static $cPreisspannenFilter;

    /**
     * @var int
     */
    public static $kHerstellerFilter;

    /**
     * @var int
     */
    public static $kKategorieFilter;

    /**
     * @var int
     */
    public static $kSuchspecialFilter;

    /**
     * @var int
     */
    public static $kSuchFilter;

    /**
     * @var int
     */
    public static $nDarstellung;

    /**
     * @var int
     */
    public static $nSortierung;

    /**
     * @var int
     */
    public static $nSort;

    /**
     * @var
     */
    public static $show;

    /**
     * @var
     */
    public static $vergleichsliste;

    /**
     * @var bool
     */
    public static $bFileNotFound;

    /**
     * @var string
     */
    public static $cCanonicalURL;

    /**
     * @var bool
     */
    public static $is404;

    /**
     * @var
     */
    public static $MerkmalFilter;

    /**
     * @var
     */
    public static $SuchFilter;

    /**
     * @var
     */
    public static $TagFilter;

    /**
     * @var int
     */
    public static $kWunschliste;

    /**
     * @var bool
     */
    public static $bSEOMerkmalNotFound;

    /**
     * @var bool
     */
    public static $bKatFilterNotFound;

    /**
     * @var bool
     */
    public static $bHerstellerFilterNotFound;

    /**
     * @var bool
     */
    public static $isSeoMainword;

    /**
     * @var null|Shop
     */
    private static $_instance;

    /**
     * @var object
     */
    public static $NaviFilter;

    /**
     * @var string
     */
    public static $fileName;

    /**
     * @var
     */
    public static $AktuelleSeite;

    /**
     * @var string
     */
    public static $pageType;

    /**
     * @var bool
     */
    public static $directEntry = true;

    /**
     * @var bool
     */
    public static $bSeo = false;

    /**
     * @var bool
     */
    public static $isInitialized = false;

    /**
     * @var int
     */
    public static $nArtikelProSeite;

    /**
     * @var string
     */
    public static $cSuche;

    /**
     * @var
     */
    public static $seite;

    /**
     * @var int
     */
    public static $nSterne;

    /**
     * @var int
     */
    public static $nNewsKat;

    /**
     * @var string
     */
    public static $cDatum;

    /**
     * @var int
     */
    public static $nAnzahl;

    /**
     * @var string
     */
    public static $uri;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var bool
     */
    private static $_logged;

    /**
     * @var Shopsetting
     */
    private static $_settings;

    /**
     *
     */
    private function __construct()
    {
        self::$_instance = $this;
        self::$_settings = Shopsetting::getInstance();
    }

    /**
     * @return Shop
     */
    public static function getInstance()
    {
        return (self::$_instance === null) ? new self() : self::$_instance;
    }

    /**
     * object wrapper - this allows to call NiceDB->query() etc.
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return (($mapping = self::map($method))!== null)
            ? call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper - this allows to call Shop::DB()->query() etc.
     *
     * @param string $method
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return (($mapping = self::map($method)) !== null)
            ? call_user_func_array([self::getInstance(), $mapping], $arguments)
            : null;
    }

    /**
     * @param $key
     * @return null|mixed
     */
    public function _get($key)
    {
        return isset($this->registry[$key])
            ? $this->registry[$key]
            : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function _set($key, $value)
    {
        $this->registry[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function _has($key)
    {
        return isset($this->registry[$key]);
    }

    /**
     * map function calls to real functions
     *
     * @param string $method
     * @return string|null
     */
    private static function map($method)
    {
        $mapping = [
            'DB'       => '_DB',
            'Cache'    => '_Cache',
            'Lang'     => '_Language',
            'Smarty'   => '_Smarty',
            'Media'    => '_Media',
            'Event'    => '_Event',
            'has'      => '_has',
            'set'      => '_set',
            'get'      => '_get'
        ];

        return isset($mapping[$method])
            ? $mapping[$method]
            : null;
    }

    /**
     * get remote service instance
     *
     * @return RemoteService
     */
    public function RS()
    {
        return RemoteService::getInstance();
    }

    /**
     * get session instance
     *
     * @return Session
     */
    public function Session()
    {
        return Session::getInstance();
    }

    /**
     * get db adapter instance
     *
     * @return NiceDB
     */
    public function _DB()
    {
        return NiceDB::getInstance();
    }

    /**
     * get language instance
     *
     * @return Sprache
     */
    public function _Language()
    {
        return Sprache::getInstance();
    }

    /**
     * get config
     *
     * @return Shopsetting
     */
    public function Config()
    {
        return self::$_settings;
    }

    /**
     * get garbage collector
     *
     * @return GarbageCollector
     */
    public function Gc()
    {
        return new GarbageCollector();
    }

    /**
     * get logger
     *
     * @return Jtllog
     */
    public function Logger()
    {
        return new Jtllog();
    }
    
    /**
     * @return PHPSettingsHelper
     */
    public function PHPSettingsHelper()
    {
        return PHPSettingsHelper::getInstance();
    }

    /**
     * get cache instance
     *
     * @return JTLCache
     */
    public function _Cache()
    {
        return JTLCache::getInstance();
    }

    /**
     * get template engine instance
     *
     * @param bool $fast_init
     * @param bool $isAdmin
     * @return JTLSmarty
     */
    public function _Smarty($fast_init = false, $isAdmin = false)
    {
        return JTLSmarty::getInstance($fast_init, $isAdmin);
    }

    /**
     * get media instance
     *
     * @return Media
     */
    public function _Media()
    {
        return Media::getInstance();
    }

    /**
     * get event instance
     *
     * @return EventDispatcher
     */
    public function _Event()
    {
        return EventDispatcher::getInstance();
    }

    /**
     * @param string       $eventName
     * @param array|object $arguments
     */
    public static function fire($eventName, $arguments = [])
    {
        self::Event()->fire($eventName, $arguments);
    }

    /**
     * quick&dirty debugging
     *
     * @param mixed       $var - the variable to debug
     * @param bool        $die - set true to die() afterwards
     * @param null|string $beforeString - a prefix string
     * @param int         $backtrace - backtrace depth
     */
    public static function dbg($var, $die = false, $beforeString = null, $backtrace = 0)
    {
        if ($beforeString !== null) {
            echo $beforeString . '<br />';
        }
        echo '<pre>';
        var_dump($var);
        if ($backtrace > 0) {
            echo '<br />Backtrace:<br />';
            var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace));
        }
        echo '</pre>';
        if ($die === true) {
            die();
        }
    }

    /**
     * get current language/language ISO
     *
     * @var bool $iso
     * @return int|string
     */
    public static function getLanguage($iso = false)
    {
        return ($iso === false) ? (int)self::$kSprache : self::$cISO;
    }

    /**
     * set language/language ISO
     *
     * @param int $languageID
     * @param string $cISO
     */
    public static function setLanguage($languageID, $cISO = null)
    {
        self::$kSprache = (int)$languageID;
        if ($cISO !== null) {
            self::$cISO = $cISO;
        }
    }

    /**
     * @param array $config
     * @return array
     */
    public static function getConfig($config)
    {
        return self::getSettings($config);
    }

    /**
     * @param array|int $config
     * @return array
     */
    public static function getSettings($config)
    {
        return self::$_settings->getSettings($config);
    }

    /**
     * @param string $section
     * @param string $option
     * @return string|array|int
     */
    public static function getSettingValue($section, $option)
    {
        return self::getConfigValue($section, $option);
    }

    /**
     * @param string $section
     * @param string $option
     * @return string|array|int
     */
    public static function getConfigValue($section, $option)
    {
        return self::$_settings->getValue($section, $option);
    }

    /**
     * Load plugin event driven system
     */
    public static function bootstrap()
    {
        $cacheID = 'plgnbtsrp';
        if (($plugins = self::Cache()->get($cacheID)) === false) {
            $plugins = self::DB()->executeQuery("
                SELECT kPlugin 
                  FROM tplugin 
                  WHERE nStatus = 2 
                    AND bBootstrap = 1 
                  ORDER BY nPrio ASC", 2) ?: [];
            self::Cache()->set($cacheID, $plugins, [CACHING_GROUP_PLUGIN]);
        }

        foreach ($plugins as $plugin) {
            if (($p = Plugin::bootstrapper($plugin->kPlugin)) !== null) {
                $p->boot(EventDispatcher::getInstance());
            }
        }
    }

    /**
     *
     */
    public static function run()
    {
        self::$kKonfigPos             = verifyGPCDataInteger('ek');
        self::$kKategorie             = verifyGPCDataInteger('k');
        self::$kArtikel               = verifyGPCDataInteger('a');
        self::$kVariKindArtikel       = verifyGPCDataInteger('a2');
        self::$kSeite                 = verifyGPCDataInteger('s');
        self::$kLink                  = verifyGPCDataInteger('s');
        self::$kHersteller            = verifyGPCDataInteger('h');
        self::$kSuchanfrage           = verifyGPCDataInteger('l');
        self::$kMerkmalWert           = verifyGPCDataInteger('m');
        self::$kTag                   = verifyGPCDataInteger('t');
        self::$kSuchspecial           = verifyGPCDataInteger('q');
        self::$kNews                  = verifyGPCDataInteger('n');
        self::$kNewsMonatsUebersicht  = verifyGPCDataInteger('nm');
        self::$kNewsKategorie         = verifyGPCDataInteger('nk');
        self::$kUmfrage               = verifyGPCDataInteger('u');
        self::$nBewertungSterneFilter = verifyGPCDataInteger('bf');
        self::$cPreisspannenFilter    = verifyGPDataString('pf');
        self::$kHerstellerFilter      = verifyGPCDataInteger('hf');
        self::$kKategorieFilter       = verifyGPCDataInteger('kf');
        self::$kSuchspecialFilter     = verifyGPCDataInteger('qf');
        self::$kSuchFilter            = verifyGPCDataInteger('sf');

        self::$nDarstellung = verifyGPCDataInteger('ed');
        self::$nSortierung  = verifyGPCDataInteger('sortierreihenfolge');
        self::$nSort        = verifyGPCDataInteger('Sortierung');

        self::$show            = verifyGPCDataInteger('show');
        self::$vergleichsliste = verifyGPCDataInteger('vla');
        self::$bFileNotFound   = false;
        self::$cCanonicalURL   = '';
        self::$is404           = false;

        self::$nSterne = verifyGPCDataInteger('nSterne');

        self::$isSeoMainword = !(!isset($oSeo) || !is_object($oSeo) || !isset($oSeo->cSeo) || trim($oSeo->cSeo) === '');

        self::$kWunschliste = checkeWunschlisteParameter();

        self::$nNewsKat = verifyGPCDataInteger('nNewsKat');
        self::$cDatum   = verifyGPDataString('cDatum');
        self::$nAnzahl  = verifyGPCDataInteger('nAnzahl');

        if (strlen(verifyGPDataString('qs')) > 0) {
            self::$cSuche = StringHandler::xssClean(verifyGPDataString('qs'));
        } elseif (strlen(verifyGPDataString('suchausdruck')) > 0) {
            self::$cSuche = StringHandler::xssClean(verifyGPDataString('suchausdruck'));
        } else {
            self::$cSuche = StringHandler::xssClean(verifyGPDataString('suche'));
        }
        //avoid redirect loops for surveys that require logged in customers
        if (self::$kUmfrage > 0 && empty($_SESSION['Kunde']->kKunde) && verifyGPCDataInteger('r') !== 0) {
            self::$kUmfrage = 0;
        }

        self::$nArtikelProSeite = verifyGPCDataInteger('af');
        if (self::$nArtikelProSeite > 0) {
            $_SESSION['ArtikelProSeite'] = self::$nArtikelProSeite;
        }

        self::$isInitialized = true;
        $redirect = verifyGPDataString('r');
        if (self::$kArtikel > 0
            && !empty($redirect)
            && (self::$kNews > 0 // get param "n" was used a article amount
                || (isset($_GET['n']) && (float)$_GET['n'] > 0)) // article amount was a float >0 and <1
        ) {
            //GET param "n" is often misused as "amount of article"
            self::$kNews = 0;
            if ((int)$redirect === R_LOGIN_WUNSCHLISTE) {
                //login redirect on wishlist add when not logged in uses get param "n" as amount and "a" for the article ID
                //but we wont to go to the login page, not to the article page
                self::$kArtikel = 0;
            }
        } elseif (self::$kArtikel > 0 &&
            ((int)$redirect === R_LOGIN_BEWERTUNG || (int)$redirect === R_LOGIN_TAG) &&
            empty($_SESSION['Kunde']->kKunde)
        ) {
            //avoid redirect to article page for ratings that require logged in customers
            self::$kArtikel = 0;
        }

        $_SESSION['cTemplate'] = Template::$cTemplate;

        self::Event()->fire('shop.run');
    }

    /**
     * get page parameters
     *
     * @return array
     */
    public static function getParameters()
    {
        self::seoCheck();
        if (self::$kKategorie > 0 && !Kategorie::isVisible(self::$kKategorie, $_SESSION['Kundengruppe']->kKundengruppe)) {
            self::$kKategorie = 0;
        }
        //check variation combination
        if (ArtikelHelper::isVariChild(self::$kArtikel)) {
            self::$kVariKindArtikel = self::$kArtikel;
            self::$kArtikel         = ArtikelHelper::getParent(self::$kArtikel);
        }

        return [
            'kKategorie'             => self::$kKategorie,
            'kKonfigPos'             => self::$kKonfigPos,
            'kHersteller'            => self::$kHersteller,
            'kArtikel'               => self::$kArtikel,
            'kVariKindArtikel'       => self::$kVariKindArtikel,
            'kSeite'                 => self::$kSeite,
            'kLink'                  => self::$kSeite > 0 ? self::$kSeite : self::$kLink,
            'kSuchanfrage'           => self::$kSuchanfrage,
            'kMerkmalWert'           => self::$kMerkmalWert,
            'kTag'                   => self::$kTag,
            'kSuchspecial'           => self::$kSuchspecial,
            'kNews'                  => self::$kNews,
            'kNewsMonatsUebersicht'  => self::$kNewsMonatsUebersicht,
            'kNewsKategorie'         => self::$kNewsKategorie,
            'kUmfrage'               => self::$kUmfrage,
            'kKategorieFilter'       => self::$kKategorieFilter,
            'kHerstellerFilter'      => self::$kHerstellerFilter,
            'nBewertungSterneFilter' => self::$nBewertungSterneFilter,
            'cPreisspannenFilter'    => self::$cPreisspannenFilter,
            'kSuchspecialFilter'     => self::$kSuchspecialFilter,
            'nSortierung'            => self::$nSortierung,
            'nSort'                  => self::$nSort,
            'MerkmalFilter_arr'      => self::$MerkmalFilter,
            'TagFilter_arr'          => self::$TagFilter !== null ? self::$TagFilter : [],
            'SuchFilter_arr'         => self::$SuchFilter !== null ? self::$SuchFilter : [],
            'nArtikelProSeite'       => self::$nArtikelProSeite !== null ? self::$nArtikelProSeite : null,
            'cSuche'                 => self::$cSuche !== null ? self::$cSuche : null,
            'seite'                  => self::$seite !== null ? self::$seite : null,
            'show'                   => self::$show,
            'is404'                  => self::$is404,
            'kSuchFilter'            => self::$kSuchFilter,
            'kWunschliste'           => self::$kWunschliste,
            'MerkmalFilter'          => self::$MerkmalFilter,
            'SuchFilter'             => self::$SuchFilter,
            'TagFilter'              => self::$TagFilter,
            'vergleichsliste'        => self::$vergleichsliste,
            'nDarstellung'           => self::$nDarstellung,
            'isSeoMainword'          => self::$isSeoMainword,
            'nNewsKat'               => self::$nNewsKat,
            'cDatum'                 => self::$cDatum,
            'nAnzahl'                => self::$nAnzahl,
            'nSterne'                => self::$nSterne,
        ];
    }

    /**
     * check for seo url
     */
    public static function seoCheck()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        self::$uri                       = $uri;
        self::$bSEOMerkmalNotFound       = false;
        self::$bKatFilterNotFound        = false;
        self::$bHerstellerFilterNotFound = false;

        if (strpos($uri, 'index.php') === false) {
            executeHook(HOOK_SEOCHECK_ANFANG, ['uri' => &$uri]);
            $seite        = 0;
            $hstseo       = '';
            $katseo       = '';
            $xShopurl_arr = parse_url(self::getURL());
            $xBaseurl_arr = parse_url($uri);
            $seo          = isset($xBaseurl_arr['path'])
                ? substr($xBaseurl_arr['path'], isset($xShopurl_arr['path'])
                    ? (strlen($xShopurl_arr['path']) + 1)
                    : 1)
                : false;
            //Fremdparameter
            $seo = extFremdeParameter($seo);
            if ($seo) {
                //change Opera Fix
                if (substr($seo, strlen($seo) - 1, 1) === '?') {
                    $seo = substr($seo, 0, strlen($seo) - 1);
                }
                $nMatch = preg_match('/[^_](' . SEP_SEITE . '([0-9]+))/', $seo, $cMatch_arr, PREG_OFFSET_CAPTURE);
                if ($nMatch === 1) {
                    $seite = (int)$cMatch_arr[2][0];
                    $seo   = substr($seo, 0, $cMatch_arr[1][1]);
                }
                //double content work around
                if ($seite === 1 && strlen($seo) > 0) {
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $seo);
                    exit();
                }
                $cSEOMerkmal_arr = explode(SEP_MERKMAL, $seo);
                $seo             = $cSEOMerkmal_arr[0];
                $oHersteller_arr = explode(SEP_HST, $seo);
                $nMerkmalZaehler = 0;
                if (is_array($oHersteller_arr) && count($oHersteller_arr) > 1) {
                    $seo    = $oHersteller_arr[0];
                    $hstseo = $oHersteller_arr[1];
                } else {
                    $seo = $oHersteller_arr[0];
                }
                $oKategorie_arr = explode(SEP_KAT, $seo);
                if (is_array($oKategorie_arr) && count($oKategorie_arr) > 1) {
                    $seo    = $oKategorie_arr[0];
                    $katseo = $oKategorie_arr[1];
                } else {
                    $seo = $oKategorie_arr[0];
                }
                if ((int)$seite > 0) {
                    $_GET['seite'] = (int)$seite;
                }
                //split attribute/attribute value
                $oMerkmal_arr = explode(SEP_MM_MMW, $seo);
                if (is_array($oMerkmal_arr) && count($oMerkmal_arr) > 1) {
                    $seo = $oMerkmal_arr[1];
                    //$mmseo = $oMerkmal_arr[0];
                }
                //category filter
                if (strlen($katseo) > 0) {
                    $oSeo = self::DB()->select('tseo', 'cKey', 'kKategorie', 'cSeo', $katseo);
                    if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $katseo) === 0) {
                        self::$kKategorieFilter = $oSeo->kKey;
                    } else {
                        self::$bKatFilterNotFound = true;
                    }
                }
                //manufacturer filter
                if (strlen($hstseo) > 0) {
                    $oSeo = self::DB()->select('tseo', 'cKey', 'kHersteller', 'cSeo', $hstseo);
                    if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $hstseo) === 0) {
                        self::$kHerstellerFilter = $oSeo->kKey;
                    } else {
                        self::$bHerstellerFilterNotFound = true;
                    }
                }
                //attribute filter
                if (count($cSEOMerkmal_arr) > 1) {
                    $nMerkmalZaehler = 1;
                    foreach ($cSEOMerkmal_arr as $i => $cSEOMerkmal) {
                        if ($i > 0 && strlen($cSEOMerkmal) > 0) {
                            $oSeo = self::DB()->select('tseo', 'cKey', 'kMerkmalWert', 'cSeo', $cSEOMerkmal);
                            if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $cSEOMerkmal) === 0) {
                                //hänge an GET, damit baueMerkmalFilter die Merkmalfilter setzen kann im NAvifilter.
                                $_GET['mf' . $nMerkmalZaehler] = $oSeo->kKey;
                                ++$nMerkmalZaehler;
                                self::$bSEOMerkmalNotFound = false;
                            } else {
                                self::$bSEOMerkmalNotFound = true;
                            }
                        }
                    }
                }
                $oSeo = self::DB()->select('tseo', 'cSeo', $seo);
                //EXPERIMENTAL_MULTILANG_SHOP
                if (isset($oSeo->kSprache) && self::$kSprache !== $oSeo->kSprache &&
                    defined('EXPERIMENTAL_MULTILANG_SHOP') && EXPERIMENTAL_MULTILANG_SHOP === true) {
                    $oSeo->kSprache = self::$kSprache;
                }
                //EXPERIMENTAL_MULTILANG_SHOP END
                //Link active?
                if (isset($oSeo->cKey) && $oSeo->cKey === 'kLink') {
                    $bIsActive = self::DB()->select('tlink', 'kLink', (int)$oSeo->kKey);
                    if ($bIsActive->bIsActive === '0') {
                        $oSeo = false;
                    }
                }
                //mainwords
                if (isset($oSeo->kKey) && strcasecmp($oSeo->cSeo, $seo) === 0) {
                    //canonical
                    self::$cCanonicalURL = self::getURL() . '/' . $oSeo->cSeo;

                    switch ($oSeo->cKey) {
                        case 'kKategorie':
                            self::$kKategorie = (int)$oSeo->kKey;
                            break;

                        case 'kHersteller':
                            self::$kHersteller = (int)$oSeo->kKey;
                            break;

                        case 'kArtikel':
                            self::$kArtikel = (int)$oSeo->kKey;
                            break;

                        case 'kLink':
                            self::$kLink = (int)$oSeo->kKey;
                            break;

                        case 'kSuchanfrage':
                            self::$kSuchanfrage = (int)$oSeo->kKey;
                            break;

                        case 'kMerkmalWert':
                            self::$kMerkmalWert = (int)$oSeo->kKey;
                            break;

                        case 'kTag':
                            self::$kTag = (int)$oSeo->kKey;
                            break;

                        case 'suchspecial':
                            self::$kSuchspecial = (int)$oSeo->kKey;
                            break;

                        case 'kNews':
                            self::$kNews = (int)$oSeo->kKey;
                            break;

                        case 'kNewsMonatsUebersicht':
                            self::$kNewsMonatsUebersicht = (int)$oSeo->kKey;
                            break;

                        case 'kNewsKategorie':
                            self::$kNewsKategorie = (int)$oSeo->kKey;
                            break;

                        case 'kUmfrage':
                            self::$kUmfrage = (int)$oSeo->kKey;
                            break;

                    }
                }
                if (isset($oSeo->kSprache) && $oSeo->kSprache > 0) {
                    $kSprache = (int)$oSeo->kSprache;
                    $spr      = class_exists('Sprache')
                        ? self::Lang()->getIsoFromLangID($kSprache)
                        : self::DB()->select('tsprache', 'kSprache', $kSprache);
                    $cLang = isset($spr->cISO) ? $spr->cISO : null;
                    if ($cLang !== $_SESSION['cISOSprache']) {
                        checkeSpracheWaehrung($cLang);
                        setzeSteuersaetze();
                    }
                }
            }
            self::$MerkmalFilter = setzeMerkmalFilter();
            self::$SuchFilter    = setzeSuchFilter();
            self::$TagFilter     = setzeTagFilter();

            executeHook(HOOK_SEOCHECK_ENDE);
        }
    }

    /**
     * decide which page to load
     */
    public static function getEntryPoint()
    {
        self::setPageType(PAGE_UNBEKANNT);
        if ((self::$kArtikel > 0 && !self::$kKategorie) ||
            (self::$kArtikel > 0 && self::$kKategorie > 0 && self::$show === 1)
        ) {
            $kVaterArtikel = ArtikelHelper::getParent(self::$kArtikel);
            if ($kVaterArtikel > 0) {
                $kArtikel = $kVaterArtikel;
                //save data from child article POST and add to redirect
                $cRP = '';
                if (is_array($_POST) && count($_POST) > 0) {
                    $cMember_arr = array_keys($_POST);
                    foreach ($cMember_arr as $cMember) {
                        $cRP .= '&' . $cMember . '=' . $_POST[$cMember];
                    }
                    // Redirect POST
                    $cRP = '&cRP=' . base64_encode($cRP);
                }
                http_response_code(301);
                header('Location: ' . self::getURL() . '/navi.php?a=' . $kArtikel . $cRP);
                exit();
            }

            self::setPageType(PAGE_ARTIKEL);
            self::$fileName = 'artikel.php';
        } elseif ((!isset(self::$bSEOMerkmalNotFound) || self::$bSEOMerkmalNotFound === false) &&
            (!isset(self::$bKatFilterNotFound) || self::$bKatFilterNotFound === false) &&
            (!isset(self::$bHerstellerFilterNotFound) || self::$bHerstellerFilterNotFound === false) &&
            ((self::$isSeoMainword || self::$NaviFilter->nAnzahlFilter == 0) || !self::$bSeo) &&
            (self::$kHersteller > 0 || self::$kSuchanfrage > 0 || self::$kMerkmalWert > 0 || self::$kTag > 0 || self::$kKategorie > 0 ||
                (isset(self::$cPreisspannenFilter) && self::$cPreisspannenFilter > 0) ||
                (isset(self::$nBewertungSterneFilter) && self::$nBewertungSterneFilter > 0) || self::$kHerstellerFilter > 0 ||
                self::$kKategorieFilter > 0 || self::$kSuchspecial > 0 || self::$kSuchFilter > 0)
        ) {
            //these are some serious changes! - create 404 if attribute or filtered category is empty
            self::$fileName      = 'filter.php';
            self::$AktuelleSeite = 'ARTIKEL';
            self::setPageType(PAGE_ARTIKELLISTE);
        } elseif (self::$kWunschliste > 0) {
            self::$fileName      = 'wunschliste.php';
            self::$AktuelleSeite = 'WUNSCHLISTE';
            self::setPageType(PAGE_WUNSCHLISTE);
        } elseif (self::$vergleichsliste > 0) {
            self::$fileName      = 'vergleichsliste.php';
            self::$AktuelleSeite = 'VERGLEICHSLISTE';
            self::setPageType(PAGE_VERGLEICHSLISTE);
        } elseif (self::$kNews > 0 || self::$kNewsMonatsUebersicht > 0 || self::$kNewsKategorie > 0) {
            self::$fileName      = 'news.php';
            self::$AktuelleSeite = 'NEWS';
            self::setPageType(PAGE_NEWS);
        } elseif (self::$kUmfrage > 0) {
            self::$fileName      = 'umfrage.php';
            self::$AktuelleSeite = 'UMFRAGE';
            self::setPageType(PAGE_UMFRAGE);
        } elseif (!self::$kLink) {
            //check path
            $cPath        = self::getRequestUri();
            $cRequestFile = '/' . ltrim($cPath, '/');
            if ($cRequestFile === '/index.php') {
                // special case: /index.php shall be redirected to Shop-URL
                header('Location: ' . Shop::getURL(), true, 301);
                exit;
            }
            if ($cRequestFile === '/') {
                //special case: home page is accessible without seo url
                $link        = null;
                $linkHelper  = LinkHelper::getInstance();
                if (!empty($_SESSION['Kundengruppe']->kKundengruppe)) {
                    $cKundengruppenSQL = " AND (FIND_IN_SET('" . (int)$_SESSION['Kundengruppe']->kKundengruppe
                        . "', REPLACE(cKundengruppen, ';', ',')) > 0
                        OR cKundengruppen IS NULL 
                        OR cKundengruppen = 'NULL' 
                        OR tlink.cKundengruppen = '')";
                    $link = Shop::DB()->query("
                        SELECT kLink 
                            FROM tlink
                            WHERE nLinkart = " . LINKTYP_STARTSEITE . $cKundengruppenSQL, 1
                    );
                }
                self::$kLink = isset($link->kLink)
                    ? (int)$link->kLink
                    : $linkHelper->getSpecialPageLinkKey(LINKTYP_STARTSEITE);
            } elseif (self::Media()->isValidRequest($cPath)) {
                self::Media()->handleRequest($cPath);
            } else {
                self::$is404         = true;
                self::$AktuelleSeite = '404';
                self::setPageType(PAGE_404);
            }
        } else {
            if (!empty(self::$kLink)) {
                $linkHelper = LinkHelper::getInstance();
                $link       = $linkHelper->getPageLink(self::$kLink);
                $oSeite     = null;
                if (isset($link->nLinkart)) {
                    $oSeite = self::DB()->select('tspezialseite', 'nLinkart', (int)$link->nLinkart);
                }
                if (!empty($oSeite->cDateiname)) {
                    self::$fileName = $oSeite->cDateiname;
                    switch ($oSeite->cDateiname) {
                        case 'news.php' :
                            self::$AktuelleSeite = 'NEWS';
                            self::setPageType(PAGE_NEWS);
                            break;
                        case 'jtl.php' :
                            self::$AktuelleSeite = 'MEIN KONTO';
                            self::setPageType(PAGE_MEINKONTO);
                            break;
                        case 'kontakt.php' :
                            self::$AktuelleSeite = 'KONTAKT';
                            self::setPageType(PAGE_KONTAKT);
                            break;
                        case 'newsletter.php' :
                            self::$AktuelleSeite = 'NEWSLETTER';
                            self::setPageType(PAGE_NEWSLETTER);
                            break;
                        case 'pass.php' :
                            self::$AktuelleSeite = 'PASSWORT VERGESSEN';
                            self::setPageType(PAGE_PASSWORTVERGESSEN);
                            break;
                        case 'registrieren.php' :
                            self::$AktuelleSeite = 'REGISTRIEREN';
                            self::setPageType(PAGE_REGISTRIERUNG);
                            break;
                        case 'umfrage.php' :
                            self::$AktuelleSeite = 'UMFRAGE';
                            self::setPageType(PAGE_UMFRAGE);
                            break;
                        case 'warenkorb.php' :
                            self::$AktuelleSeite = 'WARENKORB';
                            self::setPageType(PAGE_WARENKORB);
                            break;
                        case 'wunschliste.php' :
                            self::$AktuelleSeite = 'WUNSCHLISTE';
                            self::setPageType(PAGE_WUNSCHLISTE);
                            break;
                        default :
                            break;
                    }
                }
            }
            if (self::$fileName === null) {
                self::$fileName      = 'seite.php';
                self::$AktuelleSeite = 'SEITE';
                self::setPageType(PAGE_EIGENE);
            }
        }
    }

    /**
     * build navigation filter object from parameters
     *
     * @param array         $cParameter_arr
     * @param stdClass|null $NaviFilter
     *
     * @return mixed
     */
    public static function buildNaviFilter($cParameter_arr, $NaviFilter = null)
    {
        if ($NaviFilter === null) {
            $NaviFilter = new stdClass();
        }
        $NaviFilter->oSprache_arr = self::Lang()->getLangArray();
        $oSeo                     = null;
        //get active languages
        $oSprache_arr = $NaviFilter->oSprache_arr;
        $bSprache     = (is_array($oSprache_arr) && count($oSprache_arr) > 0);
        //mainwords
        if (isset($cParameter_arr['kKategorie']) && $cParameter_arr['kKategorie'] > 0) {
            if (!isset($NaviFilter->Kategorie)) {
                $NaviFilter->Kategorie = new stdClass();
            }
            $NaviFilter->Kategorie->kKategorie = (int)$cParameter_arr['kKategorie'];
            $oSeo_arr                          = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tkategorie.cName AS cKatName, tkategoriesprache.cName
                    FROM tseo
                        LEFT JOIN tkategorie
                            ON tkategorie.kKategorie = tseo.kKey
                        LEFT JOIN tkategoriesprache
                            ON tkategoriesprache.kKategorie = tkategorie.kKategorie
                            AND tkategoriesprache.kSprache = tseo.kSprache
                    WHERE cKey = 'kKategorie' 
                        AND kKey = " . $NaviFilter->Kategorie->kKategorie . "
                    ORDER BY tseo.kSprache", 2
            );
            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Kategorie->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr)) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Kategorie->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            foreach ($oSeo_arr as $item) {
                if ((int)$item->kSprache === (int)self::$kSprache) {
                    if (!empty($item->cName)) {
                        $NaviFilter->Kategorie->cName = $item->cName;
                    } elseif (!empty($item->cKatName)) {
                        $NaviFilter->Kategorie->cName = $item->cKatName;
                    }
                    break;
                }
            }
        }

        if (isset($cParameter_arr['kHersteller']) && $cParameter_arr['kHersteller'] > 0) {
            if (!isset($NaviFilter->Hersteller)) {
                $NaviFilter->Hersteller = new stdClass();
            }
            $NaviFilter->Hersteller->kHersteller = (int)$cParameter_arr['kHersteller'];
            $oSeo_arr                            = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                        LEFT JOIN thersteller
                        ON thersteller.kHersteller = tseo.kKey
                    WHERE cKey = 'kHersteller' 
                        AND kKey = " . (int)$NaviFilter->Hersteller->kHersteller . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                $NaviFilter->Hersteller->cSeo = [];
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Hersteller->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr)) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Hersteller->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            if (isset($oSeo_arr[0]->cName)) {
                $NaviFilter->Hersteller->cName = $oSeo_arr[0]->cName;
            } else {
                //invalid manufacturer ID
                self::$kHersteller = 0;
                unset($NaviFilter->Hersteller);
                self::$is404 = true;
            }
        }

        if (isset($cParameter_arr['kSuchanfrage']) && $cParameter_arr['kSuchanfrage'] > 0) {
            if (!isset($NaviFilter->Suchanfrage)) {
                $NaviFilter->Suchanfrage = new stdClass();
            }
            $NaviFilter->Suchanfrage->kSuchanfrage = $cParameter_arr['kSuchanfrage'];
            $oSeo_obj                              = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                    FROM tseo
                    LEFT JOIN tsuchanfrage
                        ON tsuchanfrage.kSuchanfrage = tseo.kKey
                        AND tsuchanfrage.kSprache = tseo.kSprache
                    WHERE cKey = 'kSuchanfrage' 
                        AND kKey = " . (int)$NaviFilter->Suchanfrage->kSuchanfrage, 1
            );
            if ($bSprache) {
                $NaviFilter->Suchanfrage->cSeo = [];
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Suchanfrage->cSeo[$oSprache->kSprache] = '';
                    if ($oSprache->kSprache == $oSeo_obj->kSprache) {
                        $NaviFilter->Suchanfrage->cSeo[$oSprache->kSprache] = $oSeo_obj->cSeo;
                    }
                }
            }
            if (!empty($oSeo_obj->cSuche)) {
                $NaviFilter->Suchanfrage->cName = $oSeo_obj->cSuche;
            }
        }

        if (isset($cParameter_arr['kMerkmalWert']) && $cParameter_arr['kMerkmalWert'] > 0) {
            if (!isset($NaviFilter->MerkmalWert)) {
                $NaviFilter->MerkmalWert = new stdClass();
            }
            $NaviFilter->MerkmalWert->kMerkmalWert = (int)$cParameter_arr['kMerkmalWert'];
            $oSeo_arr                              = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kMerkmalWert' 
                        AND kKey = " . $NaviFilter->MerkmalWert->kMerkmalWert . "
                    ORDER BY kSprache", 2
            );
            if ($bSprache) {
                $NaviFilter->MerkmalWert->cSeo = [];
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->MerkmalWert->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr)) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->MerkmalWert->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $oSQL            = new stdClass();
            $oSQL->cMMSelect = "tmerkmal.cName";
            $oSQL->cMMJOIN   = '';
            $oSQL->cMMWhere  = '';
            if (self::$kSprache > 0 && !standardspracheAktiv()) {
                $oSQL->cMMSelect = "tmerkmalsprache.cName, tmerkmal.cName AS cMMName";
                $oSQL->cMMJOIN   = " JOIN tmerkmalsprache 
                                        ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                                        AND tmerkmalsprache.kSprache = " . (int)self::$kSprache;
            }
            $oSQL->cMMWhere = "tmerkmalwert.kMerkmalWert = '" . $NaviFilter->MerkmalWert->kMerkmalWert . "'";
            if (isset($cParameter_arr['MerkmalFilter_arr']) && is_array($cParameter_arr['MerkmalFilter_arr']) && count($cParameter_arr['MerkmalFilter_arr']) > 0) {
                foreach ($cParameter_arr['MerkmalFilter_arr'] as $kMerkmalWert) {
                    $oSQL->cMMWhere .= " OR tmerkmalwert.kMerkmalWert = " . (int)$kMerkmalWert . " ";
                }
            }
            $oMerkmalWert_arr = self::DB()->query(
                "SELECT tmerkmalwertsprache.cWert, " . $oSQL->cMMSelect . "
                FROM tmerkmalwert
                JOIN tmerkmalwertsprache 
                    ON tmerkmalwertsprache.kMerkmalWert = tmerkmalwert.kMerkmalWert
                    AND kSprache = " . self::$kSprache . "
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tmerkmalwert.kMerkmal
                " . $oSQL->cMMJOIN . "
                WHERE " . $oSQL->cMMWhere, 2
            );
            if (is_array($oMerkmalWert_arr) && count($oMerkmalWert_arr) > 0) {
                $oMerkmalWert = $oMerkmalWert_arr[0];
                unset($oMerkmalWert_arr[0]);
                if (isset($oMerkmalWert->cWert) && strlen($oMerkmalWert->cWert) > 0) {
                    if (!isset($NaviFilter->MerkmalWert)) {
                        $NaviFilter->MerkmalWert = new stdClass();
                    }
                    if (isset($oMerkmalWert->cName) && strlen($oMerkmalWert->cName) > 0) {
                        $NaviFilter->MerkmalWert->cName = $oMerkmalWert->cName . ': ' . $oMerkmalWert->cWert;
                    } elseif (isset($oMerkmalWert->cMMName) && strlen($oMerkmalWert->cMMName) > 0) {
                        $NaviFilter->MerkmalWert->cName = $oMerkmalWert->cMMName . ': ' . $oMerkmalWert->cWert;
                    }
                    if (count($oMerkmalWert_arr) > 0) {
                        foreach ($oMerkmalWert_arr as $oTmpMerkmal) {
                            if (isset($oTmpMerkmal->cName) && strlen($oTmpMerkmal->cName) > 0) {
                                $NaviFilter->MerkmalWert->cName .= ', ' . $oTmpMerkmal->cName . ': ' . $oTmpMerkmal->cWert;
                            } elseif (isset($oTmpMerkmal->cMMName) && strlen($oTmpMerkmal->cMMName) > 0) {
                                $NaviFilter->MerkmalWert->cName .= ', ' . $oTmpMerkmal->cMMName . ': ' . $oTmpMerkmal->cWert;
                            }
                        }
                    }
                }
            }
        }

        if (isset($cParameter_arr['kTag']) && $cParameter_arr['kTag'] > 0) {
            if (!isset($NaviFilter->Tag)) {
                $NaviFilter->Tag = new stdClass();
            }
            $NaviFilter->Tag->kTag = (int)$cParameter_arr['kTag'];
            $oSeo_obj              = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, ttag.cName
                    FROM tseo
                    LEFT JOIN ttag
                        ON tseo.kKey = ttag.kTag
                    WHERE tseo.cKey = 'kTag' 
                        AND tseo.kKey = " . $NaviFilter->Tag->kTag, 1
            );
            if (isset($bSprache)) {
                $NaviFilter->Tag->cSeo = [];
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Tag->cSeo[$oSprache->kSprache] = '';
                    if ($oSprache->kSprache == $oSeo_obj->kSprache) {
                        $NaviFilter->Tag->cSeo[$oSprache->kSprache] = $oSeo_obj->cSeo;
                    }
                }
            }
            if (!empty($oSeo_obj->cName)) {
                $NaviFilter->Tag->cName = $oSeo_obj->cName;
            }
        }

        if (isset($cParameter_arr['kNews']) && $cParameter_arr['kNews'] > 0) {
            if (!isset($NaviFilter->News)) {
                $NaviFilter->News = new stdClass();
            }
            $NaviFilter->News->kNews = (int)$cParameter_arr['kNews'];
            $oSeo_obj                = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tnews.cBetreff
                    FROM tseo
                    LEFT JOIN tnews
                        ON tnews.kNews = tseo.kKey                        
                    WHERE cKey = 'kNews'
                        AND kKey = " . $NaviFilter->News->kNews . "
                    ORDER BY kSprache", 1
            );
            if ($bSprache && $oSeo_obj !== null) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->News->cSeo[$oSprache->kSprache] = '';
                    if ($oSprache->kSprache == $oSeo_obj->kSprache) {
                        $NaviFilter->News->cSeo[$oSprache->kSprache] = $oSeo_obj->cSeo;
                    }
                }
            }
            if (!empty($oSeo_obj->cBetreff)) {
                $NaviFilter->News->cName = $oSeo_obj->cBetreff;
            }
        }

        if (isset($cParameter_arr['kNewsMonatsUebersicht']) && $cParameter_arr['kNewsMonatsUebersicht'] > 0) {
            if (!isset($NaviFilter->NewsMonat)) {
                $NaviFilter->NewsMonat = new stdClass();
            }
            $NaviFilter->NewsMonat->kNewsMonatsUebersicht = (int)$cParameter_arr['kNewsMonatsUebersicht'];
            $oSeo_obj                                     = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tnewsmonatsuebersicht.cName
                    FROM tseo
                    LEFT JOIN tnewsmonatsuebersicht
                        ON tnewsmonatsuebersicht.kNewsMonatsUebersicht = tseo.kKey
                    WHERE tseo.cKey = 'kNewsMonatsUebersicht'
                        AND tseo.kKey = " . $NaviFilter->NewsMonat->kNewsMonatsUebersicht, 1
            );
            if ($bSprache) {
                $NaviFilter->NewsMonat->cSeo = [];
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->NewsMonat->cSeo[$oSprache->kSprache] = '';
                    if ($oSprache->kSprache == $oSeo_obj->kSprache) {
                        $NaviFilter->NewsMonat->cSeo[$oSprache->kSprache] = $oSeo_obj->cSeo;
                    }
                }
            }
            if (!empty($oSeo_obj->cName)) {
                $NaviFilter->NewsMonat->cName = $oSeo_obj->cName;
            }
        }

        if (isset($cParameter_arr['kNewsKategorie']) && $cParameter_arr['kNewsKategorie'] > 0) {
            if (!isset($NaviFilter->NewsKategorie)) {
                $NaviFilter->NewsKategorie = new stdClass();
            }
            $NaviFilter->NewsKategorie->kNewsKategorie = (int)$cParameter_arr['kNewsKategorie'];
            $oSeo_obj                                  = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tnewskategorie.cName
                    FROM tseo
                    LEFT JOIN tnewskategorie
                        ON tnewskategorie.kNewsKategorie = tseo.kKey
                    WHERE cKey = 'kNewsKategorie'
                        AND kKey = " . $NaviFilter->NewsKategorie->kNewsKategorie, 1
            );
            if ($bSprache && $oSeo_obj !== null) {
                $NaviFilter->NewsKategorie->cSeo = [];
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->NewsKategorie->cSeo[$oSprache->kSprache] = '';
                    if ($oSprache->kSprache == $oSeo_obj->kSprache) {
                        $NaviFilter->NewsKategorie->cSeo[$oSprache->kSprache] = $oSeo_obj->cSeo;
                    }
                }
            }
            if (!empty($oSeo_obj->cName)) {
                $NaviFilter->NewsKategorie->cName = $oSeo_obj->cName;
            }
        }
        //search specials
        if (isset($cParameter_arr['kSuchspecial']) && $cParameter_arr['kSuchspecial'] > 0) {
            if (!isset($NaviFilter->Suchspecial)) {
                $NaviFilter->Suchspecial = new stdClass();
            }
            $NaviFilter->Suchspecial->kKey = (int)$cParameter_arr['kSuchspecial'];
            $oSeo_arr                      = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial'
                        AND kKey = " . $NaviFilter->Suchspecial->kKey . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->Suchspecial->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr)) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->Suchspecial->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            switch ($NaviFilter->Suchspecial->kKey) {
                case SEARCHSPECIALS_BESTSELLER:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('bestsellers', 'global');
                    break;
                case SEARCHSPECIALS_SPECIALOFFERS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('specialOffers', 'global');
                    break;
                case SEARCHSPECIALS_NEWPRODUCTS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('newProducts', 'global');
                    break;
                case SEARCHSPECIALS_TOPOFFERS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('topOffers', 'global');
                    break;
                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('upcomingProducts', 'global');
                    break;
                case SEARCHSPECIALS_TOPREVIEWS:
                    $NaviFilter->Suchspecial->cName = self::Lang()->get('topReviews', 'global');
                    break;
                default:
                    //invalid search special ID
                    self::$is404               = true;
                    self::$kSuchspecial        = 0;
                    $NaviFilter->nAnzahlFilter = 0;
                    unset($NaviFilter->Suchspecial);
                    break;
            }
        }
        //filter
        if (isset($cParameter_arr['kKategorieFilter']) && $cParameter_arr['kKategorieFilter'] > 0 &&
            (!isset($NaviFilter->Kategorie->kKategorie) || $cParameter_arr['kKategorieFilter'] != $NaviFilter->Kategorie->kKategorie)
        ) {
            if (!isset($NaviFilter->KategorieFilter)) {
                $NaviFilter->KategorieFilter = new stdClass();
            }
            $NaviFilter->KategorieFilter->kKategorie = $cParameter_arr['kKategorieFilter'];
            $oSeo_arr                                = self::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'kKategorie' 
                        AND kKey = " . (int)$NaviFilter->KategorieFilter->kKategorie . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->KategorieFilter->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr)) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->KategorieFilter->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $seo_obj = (self::$kSprache > 0 && !standardspracheAktiv())
                ? self::DB()->select(
                    'tkategoriesprache',
                    'kKategorie', $NaviFilter->KategorieFilter->kKategorie,
                    'kSprache', self::$kSprache,
                    null, null,
                    false,
                    'cName'
                )
                : self::DB()->select(
                    'tkategorie',
                    'kKategorie', $NaviFilter->KategorieFilter->kKategorie,
                    null, null,
                    null, null,
                    false,
                    'cName'
                );
            if (isset($seo_obj->cName) && strlen($seo_obj->cName) > 0) {
                $NaviFilter->KategorieFilter->cName = $seo_obj->cName;
            }
        }

        if ((isset($cParameter_arr['kHerstellerFilter']) && $cParameter_arr['kHerstellerFilter'] > 0) &&
            (!isset($NaviFilter->Hersteller->kHersteller) ||
                $cParameter_arr['kHerstellerFilter'] != $NaviFilter->Hersteller->kHersteller)
        ) {
            if (!isset($NaviFilter->HerstellerFilter)) {
                $NaviFilter->HerstellerFilter = new stdClass();
            }
            $NaviFilter->HerstellerFilter->kHersteller = (int)$cParameter_arr['kHerstellerFilter'];
            $oSeo_arr                                  = self::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                        LEFT JOIN thersteller
                        ON thersteller.kHersteller = tseo.kKey
                    WHERE cKey = 'kHersteller' 
                        AND kKey = " . (int)$NaviFilter->HerstellerFilter->kHersteller . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                $NaviFilter->HerstellerFilter->cSeo = [];
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->HerstellerFilter->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr)) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->HerstellerFilter->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            if (isset($oSeo_arr[0]->cName)) {
                $NaviFilter->HerstellerFilter->cName = $oSeo_arr[0]->cName;
            }
        }
        $NaviFilter->MerkmalFilter = [];
        if (isset($cParameter_arr['MerkmalFilter_arr']) && is_array($cParameter_arr['MerkmalFilter_arr']) &&
            (($paramCount = count($cParameter_arr['MerkmalFilter_arr'])) > 0)) {
            for ($i = 0; $i < $paramCount; ++$i) {
                $oMerkmalWert               = new stdClass();
                $oMerkmalWert->kMerkmalWert = (int)$cParameter_arr['MerkmalFilter_arr'][$i];
                $oMerkmalWert->cSeo         = [];
                if ($oMerkmalWert->kMerkmalWert > 0) {
                    $oSeo_arr = self::DB()->query("
                        SELECT cSeo, kSprache
                            FROM tseo
                            WHERE cKey = 'kMerkmalWert' 
                                AND kKey = " . $oMerkmalWert->kMerkmalWert . "
                            ORDER BY kSprache", 2
                    );

                    if ($bSprache) {
                        foreach ($oSprache_arr as $oSprache) {
                            $oMerkmalWert->cSeo[$oSprache->kSprache] = '';
                            if (is_array($oSeo_arr)) {
                                foreach ($oSeo_arr as $oSeo) {
                                    if ($oSprache->kSprache == $oSeo->kSprache) {
                                        $oMerkmalWert->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                                    }
                                }
                            }
                        }
                    }
                    $seo_obj = self::DB()->query("
                        SELECT tmerkmalwertsprache.cWert, tmerkmalwert.kMerkmal
                            FROM tmerkmalwertsprache
                            JOIN tmerkmalwert 
                                ON tmerkmalwert.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                            WHERE tmerkmalwertsprache.kSprache = " . self::$kSprache . "
                               AND tmerkmalwertsprache.kMerkmalWert = " . $oMerkmalWert->kMerkmalWert, 1
                    );
                    if (!empty($seo_obj->kMerkmal)) {
                        $oMerkmalWert->kMerkmal = $seo_obj->kMerkmal;
                        $oMerkmalWert->cWert    = $seo_obj->cWert;
                        $oMerkmalWert->cName    = $seo_obj->cWert;
                    }
                    $NaviFilter->MerkmalFilter[] = $oMerkmalWert;
                }
            }
        }
        //tag filter
        $tagCount = isset($cParameter_arr['TagFilter_arr']) ? count($cParameter_arr['TagFilter_arr']) : 0;
        if ($tagCount > 0 && isset($cParameter_arr['TagFilter_arr']) && is_array($cParameter_arr['TagFilter_arr'])) {
            $NaviFilter->TagFilter = [];
            for ($i = 0; $i < $tagCount; ++$i) {
                $oTag       = new stdClass();
                $oTag->kTag = (int)$cParameter_arr['TagFilter_arr'][$i];
                $seo_obj    = new stdClass();
                if (self::$kSprache > 0 && $oTag->kTag > 0) {
                    $seo_obj = self::DB()->select('ttag', 'nAktiv', 1, 'kSprache', self::$kSprache, 'kTag', $oTag->kTag, false, 'cName');
                }
                if (!empty($seo_obj->cName)) {
                    $oTag->cName = $seo_obj->cName;
                }
                $NaviFilter->TagFilter[] = $oTag;
            }
        }
        //search filter
        $NaviFilter->SuchFilter = [];
        $sfCount                = isset($cParameter_arr['SuchFilter_arr']) ? count($cParameter_arr['SuchFilter_arr']) : 0;
        if ($sfCount > 0 && isset($cParameter_arr['SuchFilter_arr']) && is_array($cParameter_arr['SuchFilter_arr'])) {
            for ($i = 0; $i < $sfCount; $i++) {
                if (!isset($NaviFilter->SuchFilter[$i])) {
                    if (!isset($NaviFilter->SuchFilter)) {
                        $NaviFilter->SuchFilter = [];
                    }
                    $NaviFilter->SuchFilter[$i] = new stdClass();
                }
                $NaviFilter->SuchFilter[$i]->kSuchanfrage = (int)$cParameter_arr['SuchFilter_arr'][$i];
                // Namen holen
                $oSuchanfrage = self::DB()->select(
                    'tsuchanfrage',
                    'kSuchanfrage', $NaviFilter->SuchFilter[$i]->kSuchanfrage,
                    'kSprache', self::$kSprache,
                    null, null,
                    false,
                    'cSuche'
                );
                if (!empty($oSuchanfrage->cSuche)) {
                    $NaviFilter->SuchFilter[$i]->cName = $oSuchanfrage->cSuche;
                }
            }
        }
        //rating stars filter
        if (isset($cParameter_arr['nBewertungSterneFilter']) && $cParameter_arr['nBewertungSterneFilter'] > 0) {
            if (!isset($NaviFilter->BewertungFilter)) {
                $NaviFilter->BewertungFilter = new stdClass();
            }
            $NaviFilter->BewertungFilter->nSterne = (int)$cParameter_arr['nBewertungSterneFilter'];
        }
        //price span filter
        if (isset($cParameter_arr['cPreisspannenFilter']) && strlen($cParameter_arr['cPreisspannenFilter']) > 0) {
            if (!isset($NaviFilter->PreisspannenFilter)) {
                $NaviFilter->PreisspannenFilter = new stdClass();
            }
            list($fVon, $fBis) = explode('_', $cParameter_arr['cPreisspannenFilter']);
            if (!isset($NaviFilter->PreisspannenFilter)) {
                $NaviFilter->PreisspannenFilter = new stdClass();
            }
            $NaviFilter->PreisspannenFilter->fVon  = (float)$fVon;
            $NaviFilter->PreisspannenFilter->fBis  = (float)$fBis;
            $NaviFilter->PreisspannenFilter->cWert = $NaviFilter->PreisspannenFilter->fVon . '_' . $NaviFilter->PreisspannenFilter->fBis;
            //localize prices
            $NaviFilter->PreisspannenFilter->cVonLocalized = gibPreisLocalizedOhneFaktor($NaviFilter->PreisspannenFilter->fVon);
            $NaviFilter->PreisspannenFilter->cBisLocalized = gibPreisLocalizedOhneFaktor($NaviFilter->PreisspannenFilter->fBis);
        }
        //search special filter
        if (!empty($cParameter_arr['kSuchspecialFilter'])) {
            if (!isset($NaviFilter->SuchspecialFilter)) {
                $NaviFilter->SuchspecialFilter = new stdClass();
            }
            $oSeo_arr = self::DB()->query(
                "SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial'
                        AND kKey = " . (int)$cParameter_arr['kSuchspecialFilter'] . "
                    ORDER BY kSprache", 2
            );

            if ($bSprache) {
                foreach ($oSprache_arr as $oSprache) {
                    $NaviFilter->SuchspecialFilter->cSeo[$oSprache->kSprache] = '';
                    if (is_array($oSeo_arr)) {
                        foreach ($oSeo_arr as $oSeo) {
                            if ($oSprache->kSprache == $oSeo->kSprache) {
                                $NaviFilter->SuchspecialFilter->cSeo[$oSprache->kSprache] = $oSeo->cSeo;
                            }
                        }
                    }
                }
            }
            $NaviFilter->SuchspecialFilter->kKey = $cParameter_arr['kSuchspecialFilter'];
            //get names
            switch ($NaviFilter->SuchspecialFilter->kKey) {
                case SEARCHSPECIALS_BESTSELLER:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('bestsellers', 'global');
                    break;

                case SEARCHSPECIALS_SPECIALOFFERS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('specialOffers', 'global');
                    break;

                case SEARCHSPECIALS_NEWPRODUCTS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('newProducts', 'global');
                    break;

                case SEARCHSPECIALS_TOPOFFERS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('topOffers', 'global');
                    break;

                case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                    $NaviFilter->SuchspecialFilter->cName = self::Lang()->get('upcomingProducts', 'global');
                    break;

                default:
                    $NaviFilter->SuchspecialFilter->cName = '';
                    break;

            }
        }
        //sorting
        if (isset($cParameter_arr['nSortierung']) && $cParameter_arr['nSortierung'] > 0) {
            $NaviFilter->nSortierung = (int)$cParameter_arr['nSortierung'];
        }
        //number of products per page
        if (isset($cParameter_arr['nArtikelProSeite']) && $cParameter_arr['nArtikelProSeite'] > 0) {
            $NaviFilter->nAnzahlProSeite = (int)$cParameter_arr['nArtikelProSeite'];
        }
        //search
        if (isset($cParameter_arr['cSuche']) && strlen($cParameter_arr['cSuche']) > 0) {
            $cParameter_arr['cSuche'] = StringHandler::filterXSS($cParameter_arr['cSuche']);
            if (!isset($NaviFilter->Suche)) {
                $NaviFilter->Suche = new stdClass();
            }
            $NaviFilter->Suche->cSuche = $cParameter_arr['cSuche'];
            if (!isset($NaviFilter->EchteSuche)) {
                $NaviFilter->EchteSuche = new stdClass();
            }
            $NaviFilter->EchteSuche->cSuche = $cParameter_arr['cSuche'];
        }
        //page
        $NaviFilter->nSeite = verifyGPCDataInteger('seite');
        if (!isset($NaviFilter->nSeite) || !$NaviFilter->nSeite) {
            $NaviFilter->nSeite = 1;
        }
        if (!function_exists('gibAnzahlFilter')) {
            require_once PFAD_ROOT . PFAD_INCLUDES . 'filter_inc.php';
        }
        $NaviFilter->nAnzahlFilter = gibAnzahlFilter($NaviFilter);
        self::$NaviFilter = $NaviFilter;

        return $NaviFilter;
    }

    /**
     * @param stdClass $NaviFilter
     */
    public static function checkNaviFilter($NaviFilter)
    {
        if ($NaviFilter->nAnzahlFilter > 0) {
            if (empty($NaviFilter->Hersteller->kHersteller) && empty($NaviFilter->Kategorie->kKategorie) &&
                empty($NaviFilter->Tag->kTag) && empty($NaviFilter->Suchanfrage->kSuchanfrage) &&
                empty($NaviFilter->News->kNews) && empty($NaviFilter->Newsmonat->kNewsMonatsUebersicht) &&
                empty($NaviFilter->NewsKategorie->kNewsKategorie) &&
                !isset($NaviFilter->Suche->cSuche) && empty($NaviFilter->MerkmalWert->kMerkmalWert) &&
                empty($NaviFilter->Suchspecial->kKey)
            ) {
                //we have a manufacturer filter that doesn't filter anything
                if (!empty($NaviFilter->HerstellerFilter->cSeo[self::$kSprache])) {
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $NaviFilter->HerstellerFilter->cSeo[self::$kSprache]);
                    exit();
                }
                //we have a category filter that doesn't filter anything
                if (!empty($NaviFilter->KategorieFilter->cSeo[self::$kSprache])) {
                    http_response_code(301);
                    header('Location: ' . self::getURL() . '/' . $NaviFilter->KategorieFilter->cSeo[self::$kSprache]);
                    exit();
                }
            } elseif (!empty($NaviFilter->Hersteller->kHersteller) && !empty($NaviFilter->HerstellerFilter->kHersteller) &&
                !empty($NaviFilter->Hersteller->cSeo[self::$kSprache])) {
                //we have a manufacturer page with some manufacturer filter
                http_response_code(301);
                header('Location: ' . self::getURL() . '/' . $NaviFilter->Hersteller->cSeo[self::$kSprache]);
                exit();
            } elseif (!empty($NaviFilter->Kategorie->kKategorie) && !empty($NaviFilter->KategorieFilter->kKategorie) &&
                !empty($NaviFilter->Kategorie->cSeo[self::$kSprache])) {
                //we have a category page with some category filter
                http_response_code(301);
                header('Location: ' . self::getURL() . '/' . $NaviFilter->Kategorie->cSeo[self::$kSprache]);
                exit();
            }
        }
    }

    /**
     * @return int
     */
    public static function getShopVersion()
    {
        $oVersion = self::DB()->query("SELECT nVersion FROM tversion", 1);

        return (isset($oVersion->nVersion) && (int)$oVersion->nVersion > 0)
            ? (int)$oVersion->nVersion
            : 0;
    }

    /**
     * Return version of files
     *
     * @return int
     */
    public static function getVersion()
    {
        return JTL_VERSION;
    }

    /**
     * get logo from db, fallback to first file in logo dir
     *
     * @var bool $fullURL - prepend shop url if set to true
     * @return string|null - image path/null if no logo was found
     */
    public static function getLogo($fullUrl = false)
    {
        $ret  = null;
        $conf = self::getSettings([CONF_LOGO]);
        $file = isset($conf['logo']['shop_logo']) ? $conf['logo']['shop_logo'] : null;
        if ($file !== null && $file !== '') {
            $ret = PFAD_SHOPLOGO . $file;
        } elseif (is_dir(PFAD_ROOT . PFAD_SHOPLOGO)) {
            $dir = opendir(PFAD_ROOT . PFAD_SHOPLOGO);
            if (!$dir) {
                return '';
            }
            while (($cDatei = readdir($dir)) !== false) {
                if ($cDatei !== '.' && $cDatei !== '..' && strpos($cDatei, SHOPLOGO_NAME) !== false) {
                    $ret = PFAD_SHOPLOGO . $cDatei;
                    break;
                }
            }
        }

        return ($ret === null) ? null : (($fullUrl === true) ? self::getURL() . '/' : '') . $ret;
    }

    /**
     * @param bool $bForceSSL
     * @param bool $bMultilang
     * @return string - the shop URL without trailing slash
     */
    public static function getURL($bForceSSL = false, $bMultilang = true)
    {
        $cShopURL = URL_SHOP;
        //EXPERIMENTAL_MULTILANG_SHOP
        if ($bMultilang === true && isset($_SESSION['cISOSprache']) &&
            defined('URL_SHOP_' . strtoupper($_SESSION['cISOSprache']))
        ) {
            $cShopURL = constant('URL_SHOP_' . strtoupper($_SESSION['cISOSprache']));
        }
        $sslStatus = pruefeSSL();
        if ($sslStatus === 2) {
            $cShopURL = str_replace('http://', 'https://', $cShopURL);
        } elseif ($sslStatus === 4 || ($sslStatus === 3 && $bForceSSL)) {
            $cShopURL = str_replace('http://', 'https://', $cShopURL);
        }

        return rtrim($cShopURL, '/');
    }

    /**
     * @param bool $bForceSSL
     * @return string - the shop Admin URL without trailing slash
     */
    public static function getAdminURL($bForceSSL = false)
    {
        $cShopURL = static::getURL($bForceSSL, false) . '/' . PFAD_ADMIN;

        return rtrim($cShopURL, '/');
    }

    /**
     * @param int $pageType
     */
    public static function setPageType($pageType)
    {
        self::$pageType        = $pageType;
        $GLOBALS['nSeitenTyp'] = $pageType;
        executeHook(HOOK_SHOP_SET_PAGE_TYPE, ['pageType' => $pageType]);
    }

    /**
     * @return int
     */
    public static function getPageType()
    {
        return (self::$pageType !== null) ? self::$pageType : PAGE_UNBEKANNT;
    }

    /**
     * @return string
     */
    public static function getRequestUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }

        $xShopurl_arr = parse_url(self::getURL());
        $xBaseurl_arr = parse_url($uri);

        if (empty($xShopurl_arr['path'])) {
            $xShopurl_arr['path'] = '/';
        }

        $cPath = isset($xBaseurl_arr['path'])
            ? substr($xBaseurl_arr['path'], strlen($xShopurl_arr['path']))
            : '';

        return $cPath;
    }

    /**
     * @return bool
     */
    public static function isAdmin()
    {
        if (is_bool(self::$_logged)) {
            return self::$_logged;
        }
        $result   = false;
        $isLogged = function () {
            $oAccount = new AdminAccount(true);

            return $oAccount->logged();
        };
        $sessionName = session_name();
        if ($sessionName !== 'eSIdAdm' && $sessionName !== 'JTLSHOP') {
            Session::getInstance();
        }

        if (isset($_COOKIE['eSIdAdm'])) {
            if ($sessionName !== 'eSIdAdm') {
                $oldID = session_id();
                session_write_close();
                session_id($_COOKIE['eSIdAdm']);
                $result = $isLogged();
                session_write_close();
                session_id($oldID);
                Session::getInstance(true, true);
            } else {
                $result = $isLogged();
            }
        }
        self::$_logged = $result;

        return $result;
    }

    /**
     * @return bool
     */
    public static function isBrandfree()
    {
        return Nice::getInstance()->checkErweiterung(SHOP_ERWEITERUNG_BRANDFREE);
    }
}
