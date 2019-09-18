<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'interface.IDocument.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Document.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Manufacturerurl.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Manufacturername.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Manufacturerkeyword.php';
require_once JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES . 'class.Manufacturerdescription.php';

/**
 * Manufacturer Class
 *
 * @access public
 * @author Andre Vermeulen
 * @copyright
 */
class Manufacturer extends Document implements IDocument
{
    /**
     * @access protected
     * @var int
     */
    protected $kManufacturer;
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
     * @param $nId
     * @return $this
     */
    public function setId($nId)
    {
        $this->kManufacturer = intval($nId);

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
     * Sets the name in one language
     *
     * @param string $cLanguageIso
     * @param string $cName
     * @return $this
     */
    public function setName($cName, $cLanguageIso)
    {
        if (isset($cName) && !empty($cName) && isset($cLanguageIso) && !empty($cLanguageIso)) {
            $oManufacturerName = new Manufacturername();
            $oManufacturerName->setLanguageIso($cLanguageIso)
                              ->setName($cName)
                              ->setManufacturer($this->getId());

            $this->oName_arr[] = $oManufacturerName;
            unset($oManufacturerName);
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
            $oManufacturerDescription = new Manufacturerdescription();
            $oManufacturerDescription->setLanguageIso($cLanguageIso)
                                     ->setDescription($cDescription)
                                     ->setManufacturer($this->getId());

            $this->oDescription_arr[] = $oManufacturerDescription;
            unset($oManufacturerDescription);
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
            $oManufacturerKeyword = new Manufacturerkeyword();
            $oManufacturerKeyword->setLanguageIso($cLanguageIso)
                                 ->setKeywords($cKeywords)
                                 ->setManufacturer($this->getId());

            $this->oKeywords_arr[] = $oManufacturerKeyword;
            unset($oManufacturerKeyword);
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
            $oManufacturerURL = new Manufacturerurl();
            $oManufacturerURL->setLanguageIso($cLanguageIso)
                             ->setUrl($cURL)
                             ->setManufacturer($this->getId());

            $this->oURL_arr[] = $oManufacturerURL;
            unset($oManufacturerURL);
        }

        return $this;
    }

    /**
     * Gets the kManufacturer
     *
     * @access public
     * @return int
     */
    public function getId()
    {
        return $this->kManufacturer;
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
