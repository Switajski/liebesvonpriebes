<?php
/*
    File: document.inc.php

    HTML Control Library - Document Level Tags

    Title: xajax HTML control class library

    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: document.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
    @copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
    Section: Description
    
    This file contains the class declarations for the following HTML Controls:
    
    - document, doctype, html, head, body
    - meta, link, script, style
    - title, base
    - noscript
    - frameset, frame, iframe, noframes
    
    The following controls are deprecated as of HTML 4.01, so they will not be supported:

    - basefont
*/

class clsDocument extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        if (isset($aConfiguration['attributes'])) {
            trigger_error(
                'clsDocument objects cannot have attributes.'
                . $this->backtrace(),
                E_USER_ERROR);
        }
        
        parent::__construct('DOCUMENT', $aConfiguration);

        $this->sClass = '%block';
    }

    public function printHTML()
    {
        $tStart = microtime();
        $this->_printChildren();
        $tStop = microtime();
        echo '<' . '!--';
        echo ' page generation took ';
        $nTime = $tStop - $tStart;
        $nTime *= 1000;
        echo $nTime;
        echo ' --' . '>';
    }
}


class clsDoctype extends xajaxControlContainer
{
    public $sText;
    
    public $sFormat;
    public $sVersion;
    public $sValidation;
    public $sEncoding;
    
    public function __construct($sFormat=null, $sVersion=null, $sValidation=null, $sEncoding='UTF-8')
    {
        if (null === $sFormat && false == defined('XAJAX_HTML_CONTROL_DOCTYPE_FORMAT')) {
            trigger_error('You must specify a doctype format.', E_USER_ERROR);
        }
        if (null === $sVersion && false == defined('XAJAX_HTML_CONTROL_DOCTYPE_VERSION')) {
            trigger_error('You must specify a doctype version.', E_USER_ERROR);
        }
        if (null === $sValidation && false == defined('XAJAX_HTML_CONTROL_DOCTYPE_VALIDATION')) {
            trigger_error('You must specify a doctype validation.', E_USER_ERROR);
        }
            
        if (null === $sFormat) {
            $sFormat = XAJAX_HTML_CONTROL_DOCTYPE_FORMAT;
        }
        if (null === $sVersion) {
            $sVersion = XAJAX_HTML_CONTROL_DOCTYPE_VERSION;
        }
        if (null === $sValidation) {
            $sValidation = XAJAX_HTML_CONTROL_DOCTYPE_VALIDATION;
        }
            
        parent::__construct('DOCTYPE', array());
        
        $this->sText = '<'.'!DOCTYPE html PUBLIC "-//W3C//DTD ';
        $this->sText .= $sFormat;
        $this->sText .= ' ';
        $this->sText .= $sVersion;
        if ('TRANSITIONAL' == $sValidation) {
            $this->sText .= ' Transitional';
        } elseif ('FRAMESET' == $sValidation) {
            $this->sText .= ' Frameset';
        }
        $this->sText .= '//EN" ';
        
        if ('HTML' == $sFormat) {
            if ('4.0' == $sVersion) {
                if ('STRICT' == $sValidation) {
                    $this->sText .= '"http://www.w3.org/TR/html40/strict.dtd"';
                } elseif ('TRANSITIONAL' == $sValidation) {
                    $this->sText .= '"http://www.w3.org/TR/html40/loose.dtd"';
                }
            } elseif ('4.01' == $sVersion) {
                if ('STRICT' == $sValidation) {
                    $this->sText .= '"http://www.w3.org/TR/html401/strict.dtd"';
                } elseif ('TRANSITIONAL' == $sValidation) {
                    $this->sText .= '"http://www.w3.org/TR/html401/loose.dtd"';
                } elseif ('FRAMESET' == $sValidation) {
                    $this->sText .= '"http://www.w3.org/TR/html4/frameset.dtd"';
                }
            }
        } elseif ('XHTML' == $sFormat) {
            if ('1.0' == $sVersion) {
                if ('STRICT' == $sValidation) {
                    $this->sText .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"';
                } elseif ('TRANSITIONAL' == $sValidation) {
                    $this->sText .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"';
                }
            } elseif ('1.1' == $sVersion) {
                $this->sText .= '"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"';
            }
        } else {
            trigger_error('Unsupported DOCTYPE tag.'
                . $this->backtrace(),
                E_USER_ERROR
                );
        }
        
        $this->sText .= '>';
        
        $this->sFormat = $sFormat;
        $this->sVersion = $sVersion;
        $this->sValidation = $sValidation;
        $this->sEncoding = $sEncoding;
    }
    
    public function printHTML($sIndent='')
    {
        header('content-type: text/html; charset=' . $this->sEncoding);
        
        if ('XHTML' == $this->sFormat) {
            print '<' . '?' . 'xml version="1.0" encoding="' . $this->sEncoding . '" ' . '?' . ">\n";
        }
            
        print $this->sText;
        
        print "\n";

        parent::_printChildren($sIndent);
    }
}

class clsHtml extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('html', $aConfiguration);

        $this->sClass = '%block';
        $this->sEndTag = 'optional';
    }
}

class clsHead extends xajaxControlContainer
{
    public $objXajax;
    
    public function __construct($aConfiguration=array())
    {
        $this->objXajax = null;
        if (isset($aConfiguration['xajax'])) {
            $this->setXajax($aConfiguration['xajax']);
        }

        parent::__construct('head', $aConfiguration);

        $this->sClass = '%block';
        $this->sEndTag = 'optional';
    }
    
    public function setXajax(&$objXajax)
    {
        $this->objXajax =& $objXajax;
    }

    public function _printChildren($sIndent='')
    {
        if (null != $this->objXajax) {
            $this->objXajax->printJavascript();
        }

        parent::_printChildren($sIndent);
    }
}

class clsBody extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('body', $aConfiguration);
        
        $this->sClass = '%block';
        $this->sEndTag = 'optional';
    }
}

class clsScript extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('script', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsStyle extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('style', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsLink extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('link', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsMeta extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('meta', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsTitle extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('title', $aConfiguration);

        $this->sClass = '%block';
    }

    public function setEvent($sEvent, &$objRequest)
    {
        trigger_error(
            'clsTitle objects do not support events.'
            . $this->backtrace(),
            E_USER_ERROR);
    }
}

class clsBase extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('base', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsNoscript extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('noscript', $aConfiguration);

        $this->sClass = '%flow';
    }
}

class clsIframe extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('iframe', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsFrameset extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('frameset', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsFrame extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('frame', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsNoframes extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('noframes', $aConfiguration);

        $this->sClass = '%flow';
    }
}
