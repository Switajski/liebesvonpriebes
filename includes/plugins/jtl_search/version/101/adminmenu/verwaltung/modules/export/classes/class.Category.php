<?php

/**
 * copyright (c) 2006-2011 JTL-Software-GmbH, all rights reserved
 *
 * this file may not be redistributed in whole or significant part
 * and is subject to the JTL-Software-GmbH license.
 *
 * license: http://jtl-software.de/jtlshop3license.html
 */
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'interface.IDocument.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Document.php');

require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Categoryvisibility.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Categoryurl.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Categoryname.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Categorykeyword.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Categorydescription.php');

/**
 * Category Class
 * @access public
 * @author Andre Vermeulen
 * @copyright
 */
class Category extends Document implements IDocument
{
    /**
     * @access protected
     * @var integer
     */
    protected $kCategory;
    
    /**
     * @access protected
     * @var integer
     */
    protected $kMasterCategory;

    /**
     * @access protected
     * @var string
     */
    protected $cPictureURL;
    
    /**
     * @access protected
     * @var integer
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
     * Sets the kCategory
     * @access public
     * @var integer
     */
    public function setId($nId)
    {
        $this->kCategory = intval($nId);
    }

    /**
     * Sets the kMasterCategory
     * @access public
     * @var integer
     */
    public function setMasterCategory($kMasterCategory)
    {
        $this->kMasterCategory = intval($kMasterCategory);
    }

    /**
     * Sets the cPictureURL
     * @access public
     * @var string
     */
    public function setPictureURL($cPictureURL)
    {
        $this->cPictureURL = $cPictureURL;
    }

    /**
     * Sets the nPriority
     * @access public
     * @var integer
     */
    public function setPriority($nPriority)
    {
        $this->nPriority = intval($nPriority);
    }

    /**
     * Sets the name in one language
     * @param string $cLanguageISO
     * @param string $cName
     */
    public function setName($cName, $cLanguageIso)
    {
        if (isset($cName) && !empty($cName) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryName = new Categoryname();
            $oCategoryName->setLanguageIso($cLanguageIso);
            $oCategoryName->setName($cName);
            $oCategoryName->setCategory($this->getId());

            $this->oName_arr[] = $oCategoryName;
            unset($oCategoryName);
        }
    }

    /**
     * Sets the description in one language
     * @param string $cLanguageISO
     * @param string $cDescription
     */
    public function setDescription($cDescription, $cLanguageIso)
    {
        if (isset($cDescription) && !empty($cDescription) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryDescription = new Categorydescription();
            $oCategoryDescription->setLanguageIso($cLanguageIso);
            $oCategoryDescription->setDescription($cDescription);
            $oCategoryDescription->setCategory($this->getId());

            $this->oDescription_arr[] = $oCategoryDescription;
            unset($oCategoryDescription);
        }
    }

    /**
     * Sets the keywords in one language
     * @param string $cLanguageISO
     * @param string $cKeywords
     */
    public function setKeywords($cKeywords, $cLanguageIso)
    {
        if (isset($cKeywords) && !empty($cKeywords) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryKeyword = new Categorykeyword();
            $oCategoryKeyword->setLanguageIso($cLanguageIso);
            $oCategoryKeyword->setKeywords($cKeywords);
            $oCategoryKeyword->setCategory($this->getId());

            $this->oKeywords_arr[] = $oCategoryKeyword;
            unset($oCategoryKeyword);
        }
    }

    /**
     * Sets the product URL in one language
     * @param string $cLanguageISO
     * @param string $cURL
     */
    public function setURL($cURL, $cLanguageIso)
    {
        if (isset($cURL) && !empty($cURL) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oCategoryURL = new Categoryurl();
            $oCategoryURL->setLanguageIso($cLanguageIso);
            $oCategoryURL->setUrl($cURL);
            $oCategoryURL->setCategory($this->getId());

            $this->oURL_arr[] = $oCategoryURL;
            unset($oCategoryURL);
        }
    }

    /**
     * Sets the product URL in one language
     * @param string $cLanguageISO
     * @param string $cURL
     */
    public function setVisibility($bVisibility, $kUserGroup)
    {
        if ($bVisibility) {
            $oCategoryVisibility = new Categoryvisibility();
            $oCategoryVisibility->setUserGroup($kUserGroup);
            $oCategoryVisibility->setCategory($this->getId());

            $this->oVisibility_arr[] = $oCategoryVisibility;
            unset($oCategoryVisibility);
        }
    }

    /**
     * Gets the kCategory
     * @access public
     * @return integer
     */
    public function getId()
    {
        return $this->kCategory;
    }

    /**
     * Gets the kMasterCategory
     * @access public
     * @return integer
     */
    public function getMasterCategory()
    {
        return $this->kMasterCategory;
    }

    /**
     * Gets the cPictureURL
     * @access public
     * @return string
     */
    public function getPictureURL()
    {
        return $this->cPictureURL;
    }

    /**
     * Gets the nPriority
     * @access public
     * @return integer
     */
    public function getPriority()
    {
        return $this->nPriority;
    }

    /**
     * Gets the name
     * @access public
     * @param string
     * @return object|array
     */
    public function getName($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oName_arr as $oName) {
                if (strtolower($oName->cLanguageIso) == strtolower($cLanguageIso)) {
                    return $oName;
                }
            }
        }
        return $this->oName_arr;
    }

    /**
     * Gets the description
     * @access public
     * @param string
     * @return object|array
     */
    public function getDescription($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oDescription_arr as $oDescription) {
                if (strtolower($oDescription->cLanguageIso) == strtolower($cLanguageIso)) {
                    return $oDescription;
                }
            }
        }
        return $this->oDescription_arr;
    }

    /**
     * Gets the keywords
     * @access public
     * @param string
     * @return object|array
     */
    public function getKeywords($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oKeywords_arr as $oKeywords) {
                if (strtolower($oKeywords->cLanguageIso) == strtolower($cLanguageIso)) {
                    return $oKeywords;
                }
            }
        }
        return $this->oKeywords_arr;
    }

    /**
     * Gets the URL
     * @access public
     * @param string
     * @return object|array
     */
    public function getURL($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oURL_arr as $oURL) {
                if (strtolower($oURL->cLanguageIso) == strtolower($cLanguageIso)) {
                    return $oURL;
                }
            }
        }
        return $this->oURL_arr;
    }

    /**
     * Gets the URL
     * @access public
     * @return array
     */
    public function getVisibility()
    {
        return $this->oVisibility_arr;
    }

    public function isValid()
    {
        return true;
    }

    public function getClassName()
    {
        return get_class();
    }
}
