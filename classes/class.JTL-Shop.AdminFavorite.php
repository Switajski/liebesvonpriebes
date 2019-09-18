<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AdminFavorite
 */
class AdminFavorite
{
    /**
     * @var int
     */
    public $kAdminfav;

    /**
     * @var int
     */
    public $kAdminlogin;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cUrl;

    /**
     * @var int
     */
    public $nSort;

    /**
     * Konstruktor
     *
     * @param int $kAdminfav
     */
    public function __construct($kAdminfav = 0)
    {
        $kAdminfav = (int)$kAdminfav;
        if ($kAdminfav > 0) {
            $this->loadFromDB($kAdminfav);
        }
    }

    /**
     * Setzt AdminFavorite mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kAdminfav Primary Key
     * @return $this
     */
    public function loadFromDB($kAdminfav)
    {
        $obj = Shop::DB()->select('tadminfavs', 'kAdminfav', (int)$kAdminfav);
        foreach (get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        executeHook(HOOK_ATTRIBUT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return mixed
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->kAdminfav);

        return Shop::DB()->insert('tadminfavs', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);

        return Shop::DB()->update('tadminfavs', 'kAdminfav', $obj->kAdminfav, $obj);
    }

    /**
     * @param $kAdminlogin
     * @return array|int
     */
    public static function fetchAll($kAdminlogin)
    {
        $favs = Shop::DB()->selectAll(
            'tadminfavs',
            'kAdminlogin',
            $kAdminlogin,
            'kAdminfav, cTitel, cUrl',
            'nSort ASC'
        );

        $favs = is_array($favs) ? $favs : [];

        foreach ($favs as &$fav) {
            $fav->bExtern = true;
            $fav->cAbsUrl = $fav->cUrl;
            if (strpos($fav->cUrl, 'http') !== 0) {
                $fav->bExtern = false;
                $fav->cAbsUrl = Shop::getURL() . '/' . $fav->cUrl;
            }
        }

        return $favs;
    }

    /**
     * @param $kAdminlogin
     * @param $title
     * @param $url
     * @param int $sort
     * @return bool
     */
    public static function add($kAdminlogin, $title, $url, $sort = -1)
    {
        $urlHelper = new UrlHelper($url);

        $id   = (int)$kAdminlogin;
        $sort = (int)$sort;

        $url = str_replace(
            [Shop::getURL(), Shop::getURL(true)],
            '',
            $urlHelper->normalize()
        );

        $url = strip_tags($url);
        $url = ltrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if ($sort < 0) {
            $sort = count(static::fetchAll($id));
        }

        $item = (object)[
            'kAdminlogin' => $id,
            'cTitel'      => $title,
            'cUrl'        => $url,
            'nSort'       => $sort
        ];

        if ($id > 0 && strlen($item->cTitel) > 0 && strlen($item->cUrl) > 0) {
            Shop::DB()->insertRow('tadminfavs', $item);

            return true;
        }

        return false;
    }

    /**
     * @param $kAdminlogin
     * @param int $kAdminfav
     */
    public static function remove($kAdminlogin, $kAdminfav = 0)
    {
        $kAdminfav   = (int)$kAdminfav;
        $kAdminlogin = (int)$kAdminlogin;

        if ($kAdminfav > 0) {
            Shop::DB()->delete('tadminfavs', ['kAdminfav', 'kAdminlogin'], [$kAdminfav, $kAdminlogin]);
        } else {
            Shop::DB()->delete('tadminfavs', 'kAdminlogin', $kAdminlogin);
        }
    }
}
