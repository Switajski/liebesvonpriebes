<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class TemplateHelper
 */
class TemplateHelper
{
    /**
     * @var string
     */
    public $templateDir;

    /**
     * @var bool
     */
    public $isAdmin = false;

    /**
     * @var []TemplateHelper
     */
    public static $instances = [];

    /**
     * @var bool
     */
    private $cachingEnabled = true;

    /**
     * @param bool $isAdmin
     */
    public function __construct($isAdmin = false)
    {
        $this->isAdmin         = $isAdmin;
        $idx                   = $isAdmin ? 'admin' : 'frontend';
        self::$instances[$idx] = $this;
    }

    /**
     * @param bool $isAdmin
     * @return TemplateHelper
     */
    public static function getInstance($isAdmin = false)
    {
        $idx = $isAdmin ? 'admin' : 'frontend';

        return (!empty(self::$instances[$idx])) ? self::$instances[$idx] : new self($isAdmin);
    }

    /**
     * @return $this
     */
    public function disableCaching()
    {
        $this->cachingEnabled = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function enableCaching()
    {
        $this->cachingEnabled = true;

        return $this;
    }

    /**
     * @param string $dir
     * @return $this
     */
    public function setTemplateDir($dir)
    {
        $this->templateDir = $dir;

        return $this;
    }

    /**
     * get list of all backend templates
     *
     * @return array
     */
    public function getAdminTemplates()
    {
        $templates = [];
        $folders   = $this->getAdminTemplateFolders();
        foreach ($folders as $folder) {
            $oTemplate = $this->getData($folder, true);
            if ($oTemplate) {
                $templates[] = $oTemplate;
            }
        }

        return $templates;
    }

    /**
     * todo
     *
     * @return array
     */
    public function getStoredTemplates()
    {
        $storedTemplates = [];

        $subTemplateDir   = 'original' . DIRECTORY_SEPARATOR;
        $storeTemplateDir = PFAD_ROOT . PFAD_TEMPLATES . $subTemplateDir;

        $folders      = $this->getFrontendTemplateFolders();
        $childFolders = $this->getFolders($storeTemplateDir, 2);

        foreach ($childFolders as $version => $dirs) {
            $intersect = array_intersect(
                array_values($folders),
                array_keys($dirs)
            );
            foreach ($intersect as $dir) {
                $d = $subTemplateDir . $version . DIRECTORY_SEPARATOR . $dir;
                if ($data = $this->getData($d, false)) {
                    $storedTemplates[$dir][] = $data;
                }
            }
        }

        return $storedTemplates;
    }

    /**
     * get list of all frontend templates
     *
     * @return array
     */
    public function getFrontendTemplates()
    {
        $templates = [];
        $folders   = $this->getFrontendTemplateFolders();
        foreach ($folders as $folder) {
            $oTemplate = $this->getData($folder, false);
            if ($oTemplate) {
                $templates[] = $oTemplate;
            }
        }

        foreach ($templates as $template) {
            //check if given parent template is available
            if ($template->bChild === true) {
                $template->bHasError = true;
                foreach ($templates as $_template) {
                    if ($_template->cOrdner === $template->cParent) {
                        $template->bHasError = false;
                        break;
                    }
                }
            }
        }

        return $templates;
    }

    /**
     * @param string $path
     * @param int    $depht
     * @return array
     */
    public function getFolders($path, $depht = 0)
    {
        $result = [];

        if (!is_dir($path)) {
            return $result;
        }

        foreach (scandir($path) as $key => $value) {
            if (!in_array($value, ['.', '..'], true) && is_dir($path . DIRECTORY_SEPARATOR . $value)) {
                $result[$value] = ($depht > 1)
                    ? $this->getFolders($path . DIRECTORY_SEPARATOR . $value, $depht - 1)
                    : [];
            }
        }

        return $result;
    }

    /**
     * get all potential template folder names
     *
     * @param bool $path
     * @return array
     */
    public function getFrontendTemplateFolders($path = false)
    {
        $cOrdner_arr = [];
        if ($nHandle = opendir(PFAD_ROOT . PFAD_TEMPLATES)) {
            while (false !== ($cFile = readdir($nHandle))) {
                if ($cFile !== '.' && $cFile !== '..' && $cFile[0] !== '.') {
                    $cOrdner_arr[] = $path ? (PFAD_ROOT . PFAD_TEMPLATES . $cFile) : $cFile;
                }
            }
            closedir($nHandle);
        }

        return $cOrdner_arr;
    }

    /**
     * get all potential admin template folder names
     *
     * @param bool $bPfad
     * @return array
     */
    public function getAdminTemplateFolders($bPfad = false)
    {
        $cOrdner_arr = [];
        if (($nHandle = opendir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES)) !== false) {
            while (false !== ($cFile = readdir($nHandle))) {
                if (
                    $cFile !== '.' &&
                    $cFile !== '..' &&
                    $cFile[0] !== '.' &&
                    is_dir(PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cFile)
                ) {
                    $cOrdner_arr[] = $bPfad
                        ? (PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cFile)
                        : $cFile;
                }
            }
            closedir($nHandle);
        }

        return $cOrdner_arr;
    }

    /**
     * read xml config file
     *
     * @param string    $cOrdner
     * @param bool|null $isAdmin
     * @return null|SimpleXMLElement|SimpleXMLObject
     */
    public function getXML($cOrdner, $isAdmin = null)
    {
        $isAdmin  = ($isAdmin !== null) ? $isAdmin : $this->isAdmin;
        $cXMLFile = ($isAdmin === false)
            ? PFAD_ROOT . PFAD_TEMPLATES . $cOrdner . DIRECTORY_SEPARATOR . TEMPLATE_XML
            : PFAD_ROOT . PFAD_ADMIN . PFAD_TEMPLATES . $cOrdner . DIRECTORY_SEPARATOR . TEMPLATE_XML;
        if (file_exists($cXMLFile)) {
            if (defined('LIBXML_NOWARNING')) {
                //try to suppress warning if opening fails
                $oXML = simplexml_load_file($cXMLFile, 'SimpleXMLElement', LIBXML_NOWARNING);
            } else {
                $oXML = simplexml_load_file($cXMLFile);
            }
            if ($oXML === false) {
                $oXML = simplexml_load_string(file_get_contents($cXMLFile));
            }

            if (is_a($oXML, 'SimpleXMLElement')) {
                $oXML->Ordner = $cOrdner;
            } else {
                $oXML = null;
            }

            return $oXML;
        }

        return null;
    }

    /**
     * @param string $cOrdner
     * @return array|bool
     */
    public function getConfig($cOrdner)
    {
        $oSetting_arr = Shop::DB()->selectAll('ttemplateeinstellungen', 'cTemplate', $cOrdner);
        if (is_array($oSetting_arr) && count($oSetting_arr) > 0) {
            $oFMTSettings_arr = [];
            foreach ($oSetting_arr as $oSetting) {
                if (isset($oFMTSettings_arr[$oSetting->cSektion]) && !is_array($oFMTSettings_arr[$oSetting->cSektion])) {
                    $oFMTSettings_arr[$oSetting->cSektion] = [];
                }
                $oFMTSettings_arr[$oSetting->cSektion][$oSetting->cName] = $oSetting->cWert;
            }

            return $oFMTSettings_arr;
        }

        return false;
    }

    /**
     * @param string    $cOrdner
     * @param bool|null $isAdmin
     * @return mixed|stdClass
     */
    public function getData($cOrdner, $isAdmin = null)
    {
        $isAdmin = ($isAdmin !== null) ? $isAdmin : $this->isAdmin;
        $cacheID = 'tpl_' . $cOrdner . ($isAdmin ? '_admin' : '');
        if ($this->cachingEnabled === true && ($oTemplate = Shop::Cache()->get($cacheID)) !== false) {
            return $oTemplate;
        }

        $oTemplate    = new stdClass();
        $oXMLTemplate = $this->getXML($cOrdner, $isAdmin);
        if (!$oXMLTemplate) {
            return false;
        }

        $oTemplate->cName        = (string)trim($oXMLTemplate->Name);
        $oTemplate->cOrdner      = (string)$cOrdner;
        $oTemplate->cAuthor      = (string)trim($oXMLTemplate->Author);
        $oTemplate->cURL         = (string)trim($oXMLTemplate->URL);
        $oTemplate->cVersion     = (string)trim($oXMLTemplate->Version);
        $oTemplate->cShopVersion = (string)trim($oXMLTemplate->ShopVersion);
        $oTemplate->cPreview     = (string)trim($oXMLTemplate->Preview);
        $oTemplate->cDokuURL     = (string)trim($oXMLTemplate->DokuURL);
        $oTemplate->bChild       = !empty($oXMLTemplate->Parent);
        $oTemplate->cParent      = !empty($oXMLTemplate->Parent) ? (string)trim($oXMLTemplate->Parent) : '';
        $oTemplate->bResponsive  = empty($oXMLTemplate['isFullResponsive'])
            ? false
            : (strtolower((string)$oXMLTemplate['isFullResponsive']) === 'true');
        $oTemplate->bHasError    = false;
        $oTemplate->eTyp         = '';
        $oTemplate->cDescription = (!empty($oXMLTemplate->Description)) ? (string)trim($oXMLTemplate->Description) : '';
        if (StringHandler::is_utf8($oTemplate->cDescription)) {
            $oTemplate->cDescription = utf8_decode($oTemplate->cDescription);
        }

        if (!empty($oXMLTemplate->Parent)) {
            $parentConfig = $this->getData($oXMLTemplate->Parent, $isAdmin);

            if ($parentConfig !== false && empty($oTemplate->cVersion)) {
                $oTemplate->cVersion = $parentConfig->cVersion;
            }
            if ($parentConfig !== false && empty($oTemplate->cShopVersion)) {
                $oTemplate->cShopVersion = $parentConfig->cShopVersion;
            }
        }

        $oTemplate_arr = Shop::DB()->query("SELECT * FROM ttemplate", 2);
        foreach ($oTemplate_arr as $oTpl) {
            if (!isset($oTemplate->bAktiv) || !$oTemplate->bAktiv) {
                $oTemplate->bAktiv = (strcasecmp($oTemplate->cOrdner, $oTpl->cTemplate) === 0);
                if ($oTemplate->bAktiv) {
                    $oTemplate->eTyp = $oTpl->eTyp;
                }
            }
        }
        $oTemplate->bEinstellungen = isset($oXMLTemplate->Settings->Section) || $oTemplate->bChild;
        if (strlen($oTemplate->cName) === 0) {
            $oTemplate->cName = $oTemplate->cOrdner;
        }
        if ($this->cachingEnabled === true) {
            Shop::Cache()->set($cacheID, $oTemplate, [CACHING_GROUP_TEMPLATE]);
        }

        return $oTemplate;
    }
}
