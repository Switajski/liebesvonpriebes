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

require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Manufacturerurl.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Manufacturername.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Manufacturerkeyword.php');
require_once(JTLSEARCH_PFAD_ADMINMENU_VERWALTUNG_EXPORT_CLASSES.'class.Manufacturerdescription.php');
/**
 * Manufacturer Class
 * @access public
 * @author Andre Vermeulen
 * @copyright
 */
class Manufacturer extends Document implements IDocument
{
    /**
     * @access protected
     * @var integer
     */
    protected $kManufacturer;
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
     * Sets the kManufacturer
     * @access public
     * @var integer
     */
    public function setId($nId)
    {
        $this->kManufacturer = intval($nId);
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
            $oManufacturerName = new Manufacturername();
            $oManufacturerName->setLanguageIso($cLanguageIso);
            $oManufacturerName->setName($cName);
            $oManufacturerName->setManufacturer($this->getId());

            $this->oName_arr[] = $oManufacturerName;
            unset($oManufacturerName);
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
            $oManufacturerDescription = new Manufacturerdescription();
            $oManufacturerDescription->setLanguageIso($cLanguageIso);
            $oManufacturerDescription->setDescription($cDescription);
            $oManufacturerDescription->setManufacturer($this->getId());

            $this->oDescription_arr[] = $oManufacturerDescription;
            unset($oManufacturerDescription);
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
            $oManufacturerKeyword = new Manufacturerkeyword();
            $oManufacturerKeyword->setLanguageIso($cLanguageIso);
            $oManufacturerKeyword->setKeywords($cKeywords);
            $oManufacturerKeyword->setManufacturer($this->getId());

            $this->oKeywords_arr[] = $oManufacturerKeyword;
            unset($oManufacturerKeyword);
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
            $oManufacturerURL = new Manufacturerurl();
            $oManufacturerURL->setLanguageIso($cLanguageIso);
            $oManufacturerURL->setUrl($cURL);
            $oManufacturerURL->setManufacturer($this->getId());

            $this->oURL_arr[] = $oManufacturerURL;
            unset($oManufacturerURL);
        }
    }

    /**
     * Gets the kManufacturer
     * @access public
     * @return integer
     */
    public function getId()
    {
        return $this->kManufacturer;
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

    public function isValid()
    {
        return true;
    }

    public function getClassName()
    {
        return get_class();
    }
}
