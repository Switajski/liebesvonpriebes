<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JTLSEARCH_Status_Base
 *
 * @author Andre Vermeulen
 */
abstract class JTLSEARCH_Verwaltung_Base
{
    protected $oDB;
    
    protected $oDebugger;

    private $cCssFile = '';
    
    private $cName = '';
    
    private $nSort = 0;
    
    private $cContentTemplate = '';
    
    private $xContentVarAssoc = array();
    
    private $bIssetContent = false;
    
    abstract public function __construct(JTLSearchDB $oDB, IDebugger $oDebugger);
    abstract public function generateContent($bForce = false);
    
    protected function setSort($nSort)
    {
        $this->nSort = intval($nSort);
    }

    protected function setName($cName)
    {
        if (is_string($cName) && strlen($cName) > 0) {
            $this->cName = $cName;
        }
    }
    
    protected function setContentTemplate($cTemplate)
    {
        if (is_string($cTemplate) && strlen($cTemplate) > 0) {
            $this->cContentTemplate = $cTemplate;
        }
    }
    
    protected function setCssFile($cCssFile)
    {
        if (is_string($cCssFile) && strlen($cCssFile) > 0) {
            $this->cCssFile = $cCssFile;
        }
    }
    
    protected function setContentVar($cKey, $xVar)
    {
        if (is_string($cKey) && strlen($cKey) > 0 && isset($xVar)) {
            $this->xContentVarAssoc[$cKey] = $xVar;
        }
    }
    
    final public function getSort()
    {
        return $this->nSort;
    }

    final public function getContent($cTemplateBasePath = '')
    {
        //Content erstellen
        $this->generateContent();
        
        $cFile = $cTemplateBasePath.$this->cContentTemplate;
        if (strlen($this->cContentTemplate) > 0 && is_file($cFile)) {
            if (is_file($cFile)) {
                $xResult['cTemplate']           = $cFile;
            } elseif (is_file($this->cContentTemplate)) {
                $xResult['cTemplate']           = $this->cContentTemplate;
            } else {
                return null;
            }
            $xResult['xContentVarAssoc']    = $this->xContentVarAssoc;
            return $xResult;
        }
        return null;
    }
    
    final public function getCssURL($cBaseCssURL = '')
    {
        $cFile = $cBaseCssURL.$this->cCssFile;
        if (strlen($this->cCssFile) > 0) {
            if ($cBaseCssURL ==  '') {
                return $this->cCssFile;
            } else {
                return $cFile;
            }
        }
        return null;
    }

    final public function getName()
    {
        if (strlen($this->cName) > 0) {
            return $this->cName;
        } else {
            return null;
        }
    }
    
    final public function setIssetContent($bIssetContent)
    {
        if ($bIssetContent) {
            $this->bIssetContent = true;
        } else {
            $this->bIssetContent = false;
        }
    }
    
    final public function getIssetContent()
    {
        return $this->bIssetContent;
    }
}
