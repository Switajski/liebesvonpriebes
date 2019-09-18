<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'interface.IDocument.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Document.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productattribut.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productcategory.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productdescription.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productkeyword.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productname.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productprice.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productshortdescription.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Producturl.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Productvariation.php';

/**
 * Product Class
 *
 * @access public
 * @author Andre
 * @copyright
 */
class Product extends Document implements IDocument
{
    /**
     * @access protected
     * @var int
     */
    protected $kProduct;

    /**
     * @access protected
     * @var int
     */
    protected $kMasterId;

    /**
     * @access protected
     * @var string
     */
    protected $cArticleNumber;

    /**
     * @access protected
     * @var string
     */
    protected $cPictureURL;

    /**
     * @access protected
     * @var string
     */
    protected $kManufacturer;

    /**
     * @access protected
     * @var int
     */
    protected $nSalesRank;

    /**
     * @access protected
     * @var int
     */
    protected $nAvailability;

    /**
     * @access protected
     * @var string
     */
    protected $cEAN;

    /**
     * @access protected
     * @var string
     */
    protected $cISBN;

    /**
     * @access protected
     * @var string
     */
    protected $cMPN;

    /**
     * @access protected
     * @var string
     */
    protected $cUPC;

    /**
     * @access protected
     * @var array
     */
    protected $oName_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oShortDescription_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oDescription_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oPrice_arr;

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
    protected $oCategory_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oAttribute_arr;

    /**
     * @access protected
     * @var array
     */
    protected $oVariation_arr;

    /**
     * @param $kId
     * @return $this
     */
    public function setId($kId)
    {
        $this->kProduct = intval($kId);

        return $this;
    }

    /**
     * @param $kMasterId
     * @return $this
     */
    public function setMasterId($kMasterId)
    {
        $this->kMasterId = intval($kMasterId);

        return $this;
    }

    /**
     * @param $cArticleNumber
     * @return $this
     */
    public function setArticleNumber($cArticleNumber)
    {
        $this->cArticleNumber = $cArticleNumber;

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
     * @param $kManufacturer
     * @return $this
     */
    public function setManufacturer($kManufacturer)
    {
        $this->kManufacturer = $kManufacturer;

        return $this;
    }

    /**
     * @param $nSalesRank
     * @return $this
     */
    public function setSalesRank($nSalesRank)
    {
        $this->nSalesRank = intval($nSalesRank);

        return $this;
    }

    /**
     * @param $nAvailability
     * @return $this
     */
    public function setAvailability($nAvailability)
    {
        $this->nAvailability = intval(ceil($nAvailability));

        return $this;
    }

    /**
     * @param $cEAN
     * @return $this
     */
    public function setEAN($cEAN)
    {
        $this->cEAN = $cEAN;

        return $this;
    }

    /**
     * @param $cISBN
     * @return $this
     */
    public function setISBN($cISBN)
    {
        $this->cISBN = $cISBN;

        return $this;
    }

    /**
     * @param $cMPN
     * @return $this
     */
    public function setMPN($cMPN)
    {
        $this->cMPN = $cMPN;

        return $this;
    }

    /**
     * @param $cUPC
     * @return $this
     */
    public function setUPC($cUPC)
    {
        $this->cUPC = $cUPC;

        return $this;
    }

    /**
     * Sets the name in one language
     *
     * @param string $cLanguageIso
     * @param string $cName
     * @return $this
     */
    public function setName($cName, $cLanguageIso)
    {
        if (isset($cName) && !empty($cName) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oProductName = new Productname();
            $oProductName->setLanguageIso($cLanguageIso)
                         ->setName($cName)
                         ->setProduct($this->getId());

            $this->oName_arr[] = $oProductName;
            unset($oProductName);
        }

        return $this;
    }

    /**
     * Sets the short description in one language
     *
     * @param string $cLanguageIso
     * @param string $cShortDescription
     * @return $this
     */
    public function setShortDescription($cShortDescription, $cLanguageIso)
    {
        if (isset($cShortDescription) && !empty($cShortDescription) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oProductShortDescription = new Productshortdescription();
            $oProductShortDescription->setLanguageIso($cLanguageIso)
                                     ->setShortDescription($cShortDescription)
                                     ->setProduct($this->getId());

            $this->oShortDescription_arr[] = $oProductShortDescription;
            unset($oProductShortDescription);
        }

        return $this;
    }

    /**
     * Sets the description in one language
     *
     * @param string $cLanguageIso
     * @param string $cDescription
     * @return $this
     */
    public function setDescription($cDescription, $cLanguageIso)
    {
        if (isset($cDescription) && !empty($cDescription) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oProductDescription = new Productdescription();
            $oProductDescription->setDescription($cDescription)
                                ->setLanguageIso($cLanguageIso)
                                ->setProduct($this->getId());

            $this->oDescription_arr[] = $oProductDescription;
            unset($oProductDescription);
        }

        return $this;
    }

    /**
     * Sets the price in one currency for one usergroup
     *
     * @param string      $cCurrencyIso
     * @param int         $kUserGroup
     * @param float       $fPrice
     * @param null|string $cBasePrice
     * @return $this
     */
    public function setPrice($cCurrencyIso, $kUserGroup, $fPrice, $cBasePrice = null)
    {
        if (isset($cCurrencyIso) && !empty($cCurrencyIso) && isset($kUserGroup) && !empty($kUserGroup)) {
            $oProductPrice = new Productprice();
            $oProductPrice->setCurrencyIso($cCurrencyIso)
                          ->setPrice($fPrice)
                          ->setUserGroup($kUserGroup)
                          ->setProduct($this->getId());
            if ($cBasePrice !== null && strlen($cBasePrice) > 0) {
                $oProductPrice->setBasePrice($cBasePrice);
            }
            $this->oPrice_arr[] = $oProductPrice;
            unset($oProductPrice);
        }

        return $this;
    }

    /**
     * Sets the keywords in one language
     *
     * @param string $cLanguageIso
     * @param string $cKeywords
     * @return $this
     */
    public function setKeywords($cKeywords, $cLanguageIso)
    {
        if (isset($cKeywords) && !empty($cKeywords) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oProductKeyword = new Productkeyword();
            $oProductKeyword->setKeywords($cKeywords)
                            ->setLanguageIso($cLanguageIso)
                            ->setProduct($this->getId());

            $this->oKeywords_arr[] = $oProductKeyword;
            unset($oProductKeyword);
        }

        return $this;
    }

    /**
     * Sets the product URL in one language
     *
     * @param string $cLanguageIso
     * @param string $cUrl
     * @return $this
     */
    public function setURL($cUrl, $cLanguageIso)
    {
        if (isset($cUrl) && !empty($cUrl) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oProductUrl = new Producturl();
            $oProductUrl->setLanguageIso($cLanguageIso)
                        ->setUrl($cUrl)
                        ->setProduct($this->getId());

            $this->oURL_arr[] = $oProductUrl;
            unset($oProductUrl);
        }

        return $this;
    }

    /**
     * Sets one attribute
     *
     * @param string $cAttribute
     * @param string $cValue
     * @param string $cLanguageIso
     * @return $this
     */
    public function setAttribute($cAttribute, $cValue, $cLanguageIso)
    {
        if (isset($cAttribute) && !empty($cAttribute) && isset($cValue) && !empty($cValue) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oProductAttribute = new Productattribut();
            $oProductAttribute->setKey($cAttribute)
                              ->setValue($cValue)
                              ->setLanguageIso($cLanguageIso)
                              ->setProduct($this->getId());

            $this->oAttribute_arr[] = $oProductAttribute;
            unset($oProductAttribute);
        }

        return $this;
    }

    /**
     * Sets one Variation
     *
     * @param string $cVariation
     * @param string $cValue
     * @param string $cLanguageIso
     * @return $this
     */
    public function setVariation($cVariation, $cValue, $cLanguageIso)
    {
        if (isset($cVariation) && !empty($cVariation) && isset($cValue) && !empty($cValue) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oProductVariation = new Productvariation();
            $oProductVariation->setKey($cVariation)
                              ->setValue($cValue)
                              ->setLanguageIso($cLanguageIso)
                              ->setProduct($this->getId());

            $this->oVariation_arr[] = $oProductVariation;
            unset($oProductVariation);
        }

        return $this;
    }

    /**
     * @param $kCategory
     * @return $this
     */
    public function setCategory($kCategory)
    {
        if (isset($kCategory) && $kCategory > 0) {
            $oProductCategory = new Productcategory();
            $oProductCategory->setCategory($kCategory)
                             ->setProduct($this->getId());

            $this->oCategory_arr[] = $oProductCategory;
            unset($oProductCategory);
        }

        return $this;
    }

    /**
     * Gets the kProduct
     *
     * @access public
     * @return int
     */
    public function getId()
    {
        return $this->kProduct;
    }

    /**
     * Gets the kMasterId
     *
     * @access public
     * @return int
     */
    public function getMasterId()
    {
        return $this->kMasterId;
    }

    /**
     * Gets the cArticleNumber
     *
     * @access public
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->cArticleNumber;
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
     * Gets the kManufacturer
     *
     * @access public
     * @return string
     */
    public function getManufacturer()
    {
        return $this->kManufacturer;
    }

    /**
     * Gets the nSalesRank
     *
     * @access public
     * @return int
     */
    public function getSalesRank()
    {
        return $this->nSalesRank;
    }

    /**
     * Gets the nAvailability
     *
     * @access public
     * @return int
     */
    public function getAvailability()
    {
        return $this->nAvailability;
    }

    /**
     * Gets the cEAN
     *
     * @access public
     * @return string
     */
    public function getEAN()
    {
        return $this->cEAN;
    }

    /**
     * Gets the cISBN
     *
     * @access public
     * @return string
     */
    public function getISBN()
    {
        return $this->cISBN;
    }

    /**
     * Gets the cMPN
     *
     * @access public
     * @return string
     */
    public function getMPN()
    {
        return $this->cMPN;
    }

    /**
     * Gets the cUPC
     *
     * @access public
     * @return string
     */
    public function getUPC()
    {
        return $this->cUPC;
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
     * Gets the short description
     *
     * @access public
     * @param string
     * @return object|array
     */
    public function getShortDescription($cLanguageIso = null)
    {
        if ($cLanguageIso !== null) {
            foreach ($this->oShortDescription_arr as $oShortDescription) {
                if (strtolower($oShortDescription->cLanguageIso) === strtolower($cLanguageIso)) {
                    return $oShortDescription;
                }
            }
        }

        return $this->oShortDescription_arr;
    }

    /**
     * Gets the price
     *
     * @access public
     * @param int
     * @return object|array
     */
    public function getPrice($kUserGroup = null)
    {
        if ($kUserGroup !== null) {
            foreach ($this->oPrice_arr as $oPrice) {
                if ($oPrice->kUserGroup == $kUserGroup) {
                    return $oPrice;
                }
            }
        }

        return $this->oPrice_arr;
    }

    /**
     * Gets the keywords
     *
     * @access public
     * @param string
     * @return string|array
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
     * @return string|array
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
     * Gets the attributes
     *
     * @access public
     * @return array
     */
    public function getAttributes()
    {
        return $this->oAttribute_arr;
    }

    /**
     * Gets the Variations
     *
     * @access public
     * @return array
     */
    public function getVariation()
    {
        return $this->oVariation_arr;
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
