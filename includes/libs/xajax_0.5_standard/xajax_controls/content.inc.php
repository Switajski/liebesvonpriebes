<?php
/*
    File: content.inc.php

    HTML Control Library - Content Level Tags

    Title: xajax HTML control class library

    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: content.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
    @copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
    Section: Description
    
    This file contains class declarations for the following HTML tags:
    
    - literal
    - br, hr
    - sub, sup, q, em, strong, cite, dfn, code, samp, kbd, var, abbr, acronym, tt, i, b, big, small, ins, del
    - h1 ... h6, address, p, blockquote, pre
    
    The following tags are deprecated as of HTML 4.01 and therefore they are not supported.
    
    - font, strike, s, u, center
*/

class clsLiteral extends xajaxControl
{
    public function __construct($sText)
    {
        parent::__construct('CDATA');

        $this->sClass = '%inline';
        $this->sText = $sText;
    }

    public function printHTML($sIndent='')
    {
        echo $this->sText;
    }
}

class clsBr extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('br', $aConfiguration);
        
        $this->sClass = '%inline';
        $this->sEndTag = 'optional';
    }
}

class clsHr extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('hr', $aConfiguration);
        
        $this->sClass = '%inline';
        $this->sEndTag = 'optional';
    }
}

class clsInlineContainer extends xajaxControlContainer
{
    public function __construct($sTag, $aConfiguration)
    {
        parent::__construct($sTag, $aConfiguration);
        
        $this->sClass = '%inline';
        $this->sEndTag = 'required';
    }
}

class clsSub extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('sub', $aConfiguration);
    }
}

class clsSup extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('sup', $aConfiguration);
    }
}

class clsEm extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('em', $aConfiguration);
    }
}

class clsStrong extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('strong', $aConfiguration);
    }
}

class clsCite extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('cite', $aConfiguration);
    }
}

class clsDfn extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('dfn', $aConfiguration);
    }
}

class clsCode extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('code', $aConfiguration);
    }
}

class clsSamp extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('samp', $aConfiguration);
    }
}

class clsKbd extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('kbd', $aConfiguration);
    }
}

class clsVar extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('var', $aConfiguration);
    }
}

class clsAbbr extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('abbr', $aConfiguration);
    }
}

class clsAcronym extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('acronym', $aConfiguration);
    }
}

class clsTt extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('tt', $aConfiguration);
    }
}

class clsItalic extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('i', $aConfiguration);
    }
}

class clsBold extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('b', $aConfiguration);
    }
}

class clsBig extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('big', $aConfiguration);
    }
}


class clsSmall extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('small', $aConfiguration);
    }
}

class clsIns extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('ins', $aConfiguration);
    }
}

class clsDel extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('del', $aConfiguration);
    }
}

class clsHeadline extends xajaxControlContainer
{
    public function __construct($sType, $aConfiguration=array())
    {
        if (0 < strpos($sType, '123456')) {
            trigger_error('Invalid type for headline control; should be 1,2,3,4,5 or 6.'
                . $this->backtrace(),
                E_USER_ERROR
                );
        }

        parent::__construct('h' . $sType, $aConfiguration);

        $this->sClass = '%inline';
    }
}

class clsAddress extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('address', $aConfiguration);
    }
}

class clsParagraph extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('p', $aConfiguration);
    }
}

class clsBlockquote extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('blockquote', $aConfiguration);
    }
}

class clsPre extends clsInlineContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('pre', $aConfiguration);
    }
}
