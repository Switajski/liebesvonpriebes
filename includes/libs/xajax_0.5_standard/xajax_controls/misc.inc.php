<?php
/*
    File: misc.inc.php

    HTML Control Library - Miscellaneous Tags

    Title: xajax HTML control class library

    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: misc.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
    @copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
    Section: Description
    
    This file contains the class declarations for the following HTML Controls:
    
    (whatever does not fit elsewhere)
    
    - object, param
    - a, img, button
    - area, map
    
    The following tags are deprecated as of HTML 4.01, therefore, they will not be
    supported:
    
    - applet, embed
*/

class clsObject extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('object', $aConfiguration);

        $this->sClass = '%block';
        $this->sEndTag = 'required';
    }
}

class clsParam extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('param', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsAnchor extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        if (false == isset($aConfiguration['attributes'])) {
            $aConfiguration['attributes'] = array();
        }
        if (false == isset($aConfiguration['attributes']['href'])) {
            $aConfiguration['attributes']['href'] = '#';
        }

        parent::__construct('a', $aConfiguration);

        $this->sClass = '%inline';
        $this->sEndTag = 'required';
    }

    public function setEvent($sEvent, &$objRequest, $aParameters=array(), $sBeforeRequest='if (false == ', $sAfterRequest=') return false; ')
    {
        xajaxControl::setEvent($sEvent, $objRequest, $aParameters, $sBeforeRequest, $sAfterRequest);
    }
}

class clsImg extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('img', $aConfiguration);

        $this->sClass = '%inline';
    }
}

class clsButton extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('button', $aConfiguration);

        $this->sClass = '%inline';
    }
}

class clsArea extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('area', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsMap extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('map', $aConfiguration);

        $this->sClass = '%block';
    }
}
