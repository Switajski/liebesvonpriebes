<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

define("JTL_STARTCATS_KATATTRIB", "show_on_frontpage");

/**
 * StartCats Class
 * @access public
 * @author Daniel Böhmer JTL-Software-GmbH
 * @copyright 2006-2011 JTL-Software-GmbH
 */
class StartCats
{
    /**
     * @access protected
     * @var object
     */
    protected $oKategorie_arr;
    
    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->oKategorie_arr = array();
        
        if (isset($GLOBALS['DB'])) {
            $oObj_arr = $GLOBALS['DB']->executeQuery("SELECT kKategorie
														FROM tkategorieattribut
														WHERE cName = '" . JTL_STARTCATS_KATATTRIB . "'
														ORDER BY cWert", 2);
            
            if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                require_once(PFAD_ROOT . PFAD_CLASSES . "class.JTL-Shop.Kategorie.php");

                foreach ($oObj_arr as $oObj) {
                    if (isset($oObj->kKategorie) && $oObj->kKategorie > 0) {
                        $this->oKategorie_arr[] = new Kategorie($oObj->kKategorie);
                    }
                }
            }
        }
    }
    
    /**
     * Get Kategorie Array
     * @access public
     * @return Array
     */
    public function getKategories()
    {
        return $this->oKategorie_arr;
    }
}
