<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'interface.IDocument.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Document.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Categoryvisibility.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Categoryurl.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Categoryname.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Categorykeyword.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Categorydescription.php';

/**
 * Category Class
 *
 * @access public
 * @author Andre Vermeulen
 */
class Category extends Document implements IDocument
{
    /**
     * @access protected
     * @var int
     */
    protected $kCategory;

    /**
     * @access protected
     * @var int
     */
    protected $kMasterCategory;

    /**
     * @access protected
     * @var string
     */
    protected $cPictureURL;

    /**
     * @access protected
     * @var int
     */
    protected $nPriority;

    /**
     * @access protected
     * @var array
     */
    protected $oName_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oDescription_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oKeywords_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oURL_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oVisibility_arr;

    /**
     * @param $nId
     * @return $this
     */
    public function setId($nId)
    {
        $this->kCategory = intval($nId);

        return $this;
    }

    /**
     * @param $kMasterCategory
     * @return $this
     */
    public function setMasterCategory($kMasterCategory)
    {
        $this->kMasterCategory = intval($kMasterCategory);

        return $this;
    }

    /**
     * @param $cPictureURL
     * @return $this
     */
    public function setPictureURL($cPictureURL)
    {
        $this->cPictureURL = $cPictureURL;

        return $this;
    }

    /**
     * @param $nPriority
     * @return $this
     */
    public function setPriority($nPriority)
    {
        $this->nPriority = intval($nPriority);

        return $this;
    }

    /**
     * @param string $cName
     * @param string $cLanguageIso
     * @return $this
     */
    public function setName($cName, $cLanguageIso)
    {
        if (isset($cName) && !empty($cName) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryName = new Categoryname();
            $oCategoryName->setLanguageIso($cLanguageIso)
                          ->setName($cName)
                          ->setCategory($this->getId());

            $this->oName_arr[] = $oCategoryName;
            unset($oCategoryName);
        }

        return $this;
    }

    /**
     * @param string $cLanguageIso
     * @param string $cDescription
     * @return $this
     */
    public function setDescription($cDescription, $cLanguageIso)
    {
        if (isset($cDescription) && !empty($cDescription) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryDescription = new Categorydescription();
            $oCategoryDescription->setLanguageIso($cLanguageIso)
                                 ->setDescription($cDescription)
                                 ->setCategory($this->getId());

            $this->oDescription_arr[] = $oCategoryDescription;
            unset($oCategoryDescription);
        }

        return $this;
    }

    /**
     * @param string $cLanguageIso
     * @param string $cKeywords
     * @return $this
     */
    public function setKeywords($cKeywords, $cLanguageIso)
    {
        if (isset($cKeywords) && !empty($cKeywords) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryKeyword = new Categorykeyword();
            $oCategoryKeyword->setLanguageIso($cLanguageIso)
                             ->setKeywords($cKeywords)
                             ->setCategory($this->getId());

            $this->oKeywords_arr[] = $oCategoryKeyword;
            unset($oCategoryKeyword);
        }

        return $this;
    }

    /**
     * Sets the product URL in one language
     *
     * @param string $cLanguageIso
     * @param string $cURL
     * @return $this
     */
    public function setURL($cURL, $cLanguageIso)
    {
        if (isset($cURL) && !empty($cURL) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryURL = new Categoryurl();
            $oCategoryURL->setLanguageIso($cLanguageIso)
                         ->setUrl($cURL)
                         ->setCategory($this->getId());

            $this->oURL_arr[] = $oCategoryURL;
            unset($oCategoryURL);
        }

        return $this;
    }

    /**
     * @param bool $bVisibility
     * @param int  $kUserGroup
     * @return $this
     */
    public function setVisibility($bVisibility, $kUserGroup)
    {
        if ($bVisibility) {
            $oCategoryVisibility = new Categoryvisibility();
            $oCategoryVisibility->setUserGroup($kUserGroup)
                                ->setCategory($this->getId());

            $this->oVisibility_arr[] = $oCategoryVisibility;
            unset($oCategoryVisibility);
        }

        return $this;
    }

    /**
     * Gets the kCategory
     *
     * @access public
     * @return int
     */
    public function getId()
    {
        return $this->kCategory;
    }

    /**
     * Gets the kMasterCategory
     *
     * @access public
     * @return int
     */
    public function getMasterCategory()
    {
        return $this->kMasterCategory;
    }

    /**
     * Gets the cPictureURL
     *
     * @access public
     * @return string
     */
    public function getPictureURL()
    {
        return $this->cPictureURL;
    }

    /**
     * Gets the nPriority
     *
     * @access public
     * @return int
     */
    public function getPriority()
    {
        return $this->nPriority;
    }

    /**
     * Gets the name
     *
     * @access public
     * @param string
     * @return object|array
     */
    public function getName($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oName_arr as $oName) {
                if (strtolower($oName->cLanguageIso) === strtolower($cLanguageIso)) {
                    return $oName;
                }
            }
        }

        return $this->oName_arr;
    }

    /**
     * Gets the description
     *
     * @access public
     * @param string
     * @return object|array
     */
    public function getDescription($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oDescription_arr as $oDescription) {
                if (strtolower($oDescription->cLanguageIso) === strtolower($cLanguageIso)) {
                    return $oDescription;
                }
            }
        }

        return $this->oDescription_arr;
    }

    /**
     * Gets the keywords
     *
     * @access public
     * @param string
     * @return object|array
     */
    public function getKeywords($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oKeywords_arr as $oKeywords) {
                if (strtolower($oKeywords->cLanguageIso) === strtolower($cLanguageIso)) {
                    return $oKeywords;
                }
            }
        }

        return $this->oKeywords_arr;
    }

    /**
     * Gets the URL
     *
     * @access public
     * @param string
     * @return object|array
     */
    public function getURL($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oURL_arr as $oURL) {
                if (strtolower($oURL->cLanguageIso) === strtolower($cLanguageIso)) {
                    return $oURL;
                }
            }
        }

        return $this->oURL_arr;
    }

    /**
     * Gets the URL
     *
     * @access public
     * @return array
     */
    public function getVisibility()
    {
        return $this->oVisibility_arr;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return get_class();
    }
}
