<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Nice
 */
class Nice
{
    /**
     * @var null|Nice
     */
    private static $instance;

    /**
     * @var string
     */
    private $cBrocken = '';

    /**
     * @var string
     */
    private $cAPIKey = '';

    /**
     * @var string
     */
    private $cDomain = '';

    /**
     * @var array
     */
    private $kShopModul_arr = [];

    /**
     * @return Nice
     */
    public static function getInstance()
    {
        return (self::$instance !== null) ? self::$instance : new self();
    }

    /**
     * Konstruktor
     * Zum Erstellen eines Nice-Objects die static function getInstance() nutzen
     */
    protected function __construct()
    {
        if (($this->cBrocken = Shop::Cache()->get('cbrocken')) === false) {
            // Hole Brocken
            $oBrocken = Shop::DB()->query("SELECT cBrocken FROM tbrocken LIMIT 1", 1);

            if (!empty($oBrocken->cBrocken)) {
                // Brocken encrypten
                $cPassA         = substr(base64_decode($oBrocken->cBrocken), 0, 9);
                $cPassE         = substr(
                    base64_decode($oBrocken->cBrocken),
                    strlen(base64_decode($oBrocken->cBrocken)) - 11
                );
                $cBlowfishKey   = $cPassA . $cPassE;
                $oXTEA          = new XTEA($cBlowfishKey);
                $this->cBrocken = $oXTEA->decrypt(str_replace(
                        [$cPassA, $cPassE],
                        ['', ''],
                        base64_decode($oBrocken->cBrocken))
                );
                Shop::Cache()->set('cbrocken', $this->cBrocken, [CACHING_GROUP_CORE]);
            }
        }
        // Brocken zerlegen
        if (is_string($this->cBrocken) && strlen($this->cBrocken) > 0) {
            $cBrocken_arr = explode(';', $this->cBrocken);
            if (is_array($cBrocken_arr)) {
                if (!empty($cBrocken_arr[0])) {
                    $this->cAPIKey = $cBrocken_arr[0];
                }
                if (!empty($cBrocken_arr[1])) {
                    $this->cDomain = trim($cBrocken_arr[1]);
                }
                $bCount = count($cBrocken_arr);
                if ($bCount > 2) {
                    for ($i = 2; $i < $bCount; $i++) {
                        $this->kShopModul_arr[] = (int)$cBrocken_arr[$i];
                    }
                }
            }
        }

        $this->ladeDefines();
        self::$instance = $this;
    }

    /**
     * @param int $kShopModulCheck
     * @return bool
     */
    public function checkErweiterung($kShopModulCheck)
    {
        return ($this->cAPIKey !== '' &&
            strlen($this->cAPIKey) > 0 &&
            !empty($this->cDomain) &&
            count($this->kShopModul_arr) > 0
        )
            ? in_array((int)$kShopModulCheck, $this->kShopModul_arr, true)
            : false;
    }

    /**
     * @return $this
     */
    private function ladeDefines()
    {
        // SEO Modul - Suchmaschinenoptimierung
        defined('SHOP_ERWEITERUNG_SEO') || define('SHOP_ERWEITERUNG_SEO', 8001);
        // Umfragen Modul
        defined('SHOP_ERWEITERUNG_UMFRAGE') || define('SHOP_ERWEITERUNG_UMFRAGE', 8021);
        // Auswahlassistent Modul
        defined('SHOP_ERWEITERUNG_AUSWAHLASSISTENT') || define('SHOP_ERWEITERUNG_AUSWAHLASSISTENT', 8031);
        // Upload Modul
        defined('SHOP_ERWEITERUNG_UPLOADS') || define('SHOP_ERWEITERUNG_UPLOADS', 8041);
        // Download Modul
        defined('SHOP_ERWEITERUNG_DOWNLOADS') || define('SHOP_ERWEITERUNG_DOWNLOADS', 8051);
        // Konfigurator Modul
        defined('SHOP_ERWEITERUNG_KONFIGURATOR') || define('SHOP_ERWEITERUNG_KONFIGURATOR', 8061);
        // Warenrücksendung Modul
        defined('SHOP_ERWEITERUNG_WARENRUECKSENDUNG') || define('SHOP_ERWEITERUNG_WARENRUECKSENDUNG', 8071);
        // Brandfree Option
        defined('SHOP_ERWEITERUNG_BRANDFREE') || define('SHOP_ERWEITERUNG_BRANDFREE', 8081);

        return $this;
    }

    /**
     * @return array
     */
    public function gibAlleMoeglichenModule()
    {
        $oModul_arr = [];
        if (!defined(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
            $this->ladeDefines();
        }
        // Umfragen Modul
        $oModul           = new stdClass();
        $oModul->kModulId = SHOP_ERWEITERUNG_UMFRAGE;
        $oModul->cName    = 'Umfragen Modul';
        $oModul->cDefine  = 'SHOP_ERWEITERUNG_UMFRAGE';
        $oModul->cURL     = 'https://jtl-url.de/ykepb';
        $oModul_arr[]     = $oModul;
        // Auswahlassistent Modul
        $oModul           = new stdClass();
        $oModul->kModulId = SHOP_ERWEITERUNG_AUSWAHLASSISTENT;
        $oModul->cName    = 'Auswahlassistent Modul';
        $oModul->cDefine  = 'SHOP_ERWEITERUNG_AUSWAHLASSISTENT';
        $oModul->cURL     = 'https://jtl-url.de/q6tox';
        $oModul_arr[]     = $oModul;
        // Upload Modul
        $oModul           = new stdClass();
        $oModul->kModulId = SHOP_ERWEITERUNG_UPLOADS;
        $oModul->cName    = 'Upload Modul';
        $oModul->cDefine  = 'SHOP_ERWEITERUNG_UPLOADS';
        $oModul->cURL     = 'https://jtl-url.de/7-cop';
        $oModul_arr[]     = $oModul;
        // Upload Modul
        $oModul           = new stdClass();
        $oModul->kModulId = SHOP_ERWEITERUNG_DOWNLOADS;
        $oModul->cName    = 'Download Modul';
        $oModul->cDefine  = 'SHOP_ERWEITERUNG_DOWNLOADS';
        $oModul->cURL     = 'https://jtl-url.de/i0zvj';
        $oModul_arr[]     = $oModul;
        // Konfigurator Modul
        $oModul           = new stdClass();
        $oModul->kModulId = SHOP_ERWEITERUNG_KONFIGURATOR;
        $oModul->cName    = 'Konfigurator Modul';
        $oModul->cDefine  = 'SHOP_ERWEITERUNG_KONFIGURATOR';
        $oModul->cURL     = 'https://jtl-url.de/ni9f5';
        $oModul_arr[]     = $oModul;
        // Brandfree Option
        $oModul           = new stdClass();
        $oModul->kModulId = SHOP_ERWEITERUNG_BRANDFREE;
        $oModul->cName    = 'Brandfree Option';
        $oModul->cDefine  = 'SHOP_ERWEITERUNG_BRANDFREE';
        $oModul->cURL     = 'https://jtl-url.de/t4egb';
        $oModul_arr[]     = $oModul;
        
        return $oModul_arr;
    }

    /**
     * @return string
     */
    public function getBrocken()
    {
        return $this->cBrocken;
    }

    /**
     * @return string
     */
    public function getAPIKey()
    {
        return $this->cAPIKey;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->cDomain;
    }

    /**
     * @return array
     */
    public function getShopModul()
    {
        return $this->kShopModul_arr;
    }
}
