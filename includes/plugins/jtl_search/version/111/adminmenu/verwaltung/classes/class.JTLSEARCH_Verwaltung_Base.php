<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class JTLSEARCH_Verwaltung_Base
 *
 * @author Andre Vermeulen
 */
abstract class JTLSEARCH_Verwaltung_Base
{
    /**
     * @var
     */
    protected $oDebugger;

    /**
     * @var string
     */
    private $cCssFile = '';

    /**
     * @var string
     */
    private $cName = '';

    /**
     * @var int
     */
    private $nSort = 0;

    /**
     * @var string
     */
    private $cContentTemplate = '';

    /**
     * @var array
     */
    private $xContentVarAssoc = [];

    /**
     * @var bool
     */
    private $bIssetContent = false;

    /**
     * @param IDebugger $oDebugger
     */
    abstract public function __construct(IDebugger $oDebugger);

    /**
     * @param bool $bForce
     * @return $this
     */
    abstract public function generateContent($bForce = false);

    /**
     * @param $nSort
     * @return $this
     */
    protected function setSort($nSort)
    {
        $this->nSort = intval($nSort);

        return $this;
    }

    /**
     * @param $cName
     * @return $this
     */
    protected function setName($cName)
    {
        if (is_string($cName) && strlen($cName) > 0) {
            $this->cName = $cName;
        }

        return $this;
    }

    /**
     * @param $cTemplate
     * @return $this
     */
    protected function setContentTemplate($cTemplate)
    {
        if (is_string($cTemplate) && strlen($cTemplate) > 0) {
            $this->cContentTemplate = $cTemplate;
        }

        return $this;
    }

    /**
     * @param $cCssFile
     * @return $this
     */
    protected function setCssFile($cCssFile)
    {
        if (is_string($cCssFile) && strlen($cCssFile) > 0) {
            $this->cCssFile = $cCssFile;
        }

        return $this;
    }

    /**
     * @param $cKey
     * @param $xVar
     * @return $this
     */
    protected function setContentVar($cKey, $xVar)
    {
        if (is_string($cKey) && strlen($cKey) > 0 && isset($xVar)) {
            $this->xContentVarAssoc[$cKey] = $xVar;
        }

        return $this;
    }

    /**
     * @return int
     */
    final public function getSort()
    {
        return $this->nSort;
    }

    /**
     * @param string $cTemplateBasePath
     * @return null
     */
    final public function getContent($cTemplateBasePath = '')
    {
        //Content erstellen
        $this->generateContent();

        $cFile = $cTemplateBasePath . $this->cContentTemplate;
        if (strlen($this->cContentTemplate) > 0 && is_file($cFile)) {
            if (is_file($cFile)) {
                $xResult['cTemplate'] = $cFile;
            } elseif (is_file($this->cContentTemplate)) {
                $xResult['cTemplate'] = $this->cContentTemplate;
            } else {
                return null;
            }
            $xResult['xContentVarAssoc'] = $this->xContentVarAssoc;

            return $xResult;
        }

        return null;
    }

    /**
     * @param string $cBaseCssURL
     * @return null|string
     */
    final public function getCssURL($cBaseCssURL = '')
    {
        $cFile = $cBaseCssURL . $this->cCssFile;
        if (strlen($this->cCssFile) > 0) {
            if ($cBaseCssURL === '') {
                return $this->cCssFile;
            }

            return $cFile;
        }

        return null;
    }

    /**
     * @return null|string
     */
    final public function getName()
    {
        if (strlen($this->cName) > 0) {
            return $this->cName;
        }

        return null;
    }

    /**
     * @param $bIssetContent
     * @return $this
     */
    final public function setIssetContent($bIssetContent)
    {
        if ($bIssetContent) {
            $this->bIssetContent = true;
        } else {
            $this->bIssetContent = false;
        }

        return $this;
    }

    /**
     * @return bool
     */
    final public function getIssetContent()
    {
        return $this->bIssetContent;
    }

    /**
     * @return bool|stdClass
     */
    public static function getServerSettings()
    {
        $oServerSettings_arr = Shop::DB()->query('SELECT cKey, cValue FROM tjtlsearchserverdata', 2);
        if (count($oServerSettings_arr) > 0) {
            $oResult = new stdClass();
            foreach ($oServerSettings_arr as $oServerSetting) {
                $oResult->{$oServerSetting->cKey} = $oServerSetting->cValue;
            }

            return $oResult;
        }

        return false;
    }
}
