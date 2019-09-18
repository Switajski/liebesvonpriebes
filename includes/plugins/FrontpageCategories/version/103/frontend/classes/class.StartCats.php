<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

define('JTL_STARTCATS_KATATTRIB', 'show_on_frontpage');

/**
 * Class StartCats
 */
class StartCats
{
    /**
     * @access protected
     * @var array
     */
    protected $oKategorie_arr;

    /**
     * @access public
     */
    public function __construct()
    {
        $this->oKategorie_arr = [];
        $oObj_arr             = Shop::DB()->query(
            "SELECT kKategorie
                FROM tkategorieattribut
                WHERE cName = '" . JTL_STARTCATS_KATATTRIB . "'
                ORDER BY cWert", 2
        );

        if (is_array($oObj_arr) && count($oObj_arr) > 0) {
            foreach ($oObj_arr as $oObj) {
                if (isset($oObj->kKategorie) && $oObj->kKategorie > 0) {
                    $this->oKategorie_arr[] = new Kategorie($oObj->kKategorie);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->oKategorie_arr;
    }
}
