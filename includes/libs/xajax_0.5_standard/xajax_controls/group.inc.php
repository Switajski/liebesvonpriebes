<?php
/*
    File: group.inc.php

    HTML Control Library - Group Level Tags

    Title: xajax HTML control class library

    Please see <copyright.inc.php> for a detailed description, copyright
    and license information.
*/

/*
    @package xajax
    @version $Id: group.inc.php 362 2007-05-29 15:32:24Z calltoconstruct $
    @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
    @copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
    @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
    Section: Description
    
    This file contains the class declarations for the following HTML Controls:
    
    - ol, ul, li
    - dl, dt, dd
    - table, caption, colgroup, col, thead, tfoot, tbody, tr, td, th
    
    The following tags are deprecated as of HTML 4.01, therefore, they will not
    be supported:
    
    - dir, menu
*/

class clsList extends xajaxControlContainer
{
    public function __construct($sTag, $aConfiguration=array())
    {
        $this->clearEvent_AddItem();

        parent::__construct($sTag, $aConfiguration);

        $this->sClass = '%block';
    }
    
    public function addItem($mItem, $mConfiguration=null)
    {
        if (null != $this->eventAddItem) {
            $objItem =& call_user_func($this->eventAddItem, $mItem, $mConfiguration);
            $this->addChild($objItem);
        } else {
            $objItem =& $this->_onAddItem($mItem, $mConfiguration);
            $this->addChild($objItem);
        }
    }
    
    public function addItems($aItems, $mConfiguration=null)
    {
        foreach ($aItems as $mItem) {
            $this->addItem($mItem, $mConfiguration);
        }
    }
    
    public function clearEvent_AddItem()
    {
        $this->eventAddItem = null;
    }
    
    public function setEvent_AddItem($mFunction)
    {
        $this->eventAddItem = $mFunction;
    }
    
    public function &_onAddItem($mItem, $mConfiguration)
    {
        $objItem =& new clsLI(array(
            'child' => new clsLiteral($mItem)
            ));
        return $objItem;
    }
}

class clsUL extends clsList
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('ul', $aConfiguration);
    }
}

class clsOL extends clsList
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('ol', $aConfiguration);
    }
}

class clsLI extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('li', $aConfiguration);

        $this->sClass = '%flow';
        $this->sEndTag = 'optional';
    }
}

class clsDl extends xajaxControlContainer
{
    public function clsDl($aConfiguration=array())
    {
        parent::xajaxControlContainer('dl', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsDt extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('dt', $aConfiguration);

        $this->sClass = '%block';
        $this->sEndTag = 'optional';
    }
}

class clsDd extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('dd', $aConfiguration);

        $this->sClass = '%flow';
        $this->sEndTag = 'optional';
    }
}

class clsTableRowContainer extends xajaxControlContainer
{
    public $eventAddRow;
    public $eventAddRowCell;
    
    public function __construct($sTag, $aConfiguration=array())
    {
        $this->clearEvent_AddRow();
        $this->clearEvent_AddRowCell();

        parent::__construct($sTag, $aConfiguration);

        $this->sClass = '%block';
    }

    public function addRow($aCells, $mConfiguration=null)
    {
        if (null != $this->eventAddRow) {
            $objRow =& call_user_func($this->eventAddRow, $aCells, $mConfiguration);
            $this->addChild($objRow);
        } else {
            $objRow =& $this->_onAddRow($aCells, $mConfiguration);
            $this->addChild($objRow);
        }
    }
        
    public function addRows($aRows, $mConfiguration=null)
    {
        foreach ($aRows as $aCells) {
            $this->addRow($aCells, $mConfiguration);
        }
    }
    
    public function clearEvent_AddRow()
    {
        $this->eventAddRow = null;
    }
    public function clearEvent_AddRowCell()
    {
        $this->eventAddRowCell = null;
    }
    
    public function setEvent_AddRow($mFunction)
    {
        $mPrevious = $this->eventAddRow;
        $this->eventAddRow = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddRowCell($mFunction)
    {
        $mPrevious = $this->eventAddRowCell;
        $this->eventAddRowCell = $mFunction;
        return $mPrevious;
    }
    
    public function &_onAddRow($aCells, $mConfiguration=null)
    {
        $objTableRow =& new clsTr();
        if (null != $this->eventAddRowCell) {
            $objTableRow->setEvent_AddCell($this->eventAddRowCell);
        }
        $objTableRow->addCells($aCells, $mConfiguration);
        return $objTableRow;
    }
}

/*
    Class: clsTable
    
    A <xajaxControlContainer> derived class that aids in the construction of HTML
    tables.  Inherently, <xajaxControl> and <xajaxControlContainer> derived classes
    support <xajaxRequest> based events using the <xajaxControl->setEvent> method.
*/
class clsTable extends xajaxControlContainer
{
    public $eventAddHeader;
    public $eventAddHeaderRow;
    public $eventAddHeaderRowCell;
    public $eventAddBody;
    public $eventAddBodyRow;
    public $eventAddBodyRowCell;
    public $eventAddFooter;
    public $eventAddFooterRow;
    public $eventAddFooterRowCell;
    
    /*
        Function: clsTable
        
        Constructs and initializes an instance of the class.
    */
    public function __construct($aConfiguration=array())
    {
        $this->clearEvent_AddHeader();
        $this->clearEvent_AddHeaderRow();
        $this->clearEvent_AddHeaderRowCell();
        $this->clearEvent_AddBody();
        $this->clearEvent_AddBodyRow();
        $this->clearEvent_AddBodyRowCell();
        $this->clearEvent_AddFooter();
        $this->clearEvent_AddFooterRow();
        $this->clearEvent_AddFooterRowCell();

        parent::__construct('table', $aConfiguration);

        $this->sClass = '%block';
    }

    public function addHeader($aRows, $mConfiguration=null)
    {
        if (null != $this->eventAddHeader) {
            $objHeader =& call_user_func($this->eventAddHeader, $aRows, $mConfiguration);
            $this->addChild($objHeader);
        } else {
            $objHeader =& $this->_onAddHeader($aRows, $mConfiguration);
            $this->addChild($objHeader);
        }
    }
    public function addBody($aRows, $mConfiguration=null)
    {
        if (null != $this->eventAddBody) {
            $objBody =& call_user_func($this->eventAddBody, $aRows, $mConfiguration);
            $this->addChild($objBody);
        } else {
            $objBody =& $this->_onAddBody($aRows, $mConfiguration);
            $this->addChild($objBody);
        }
    }
    public function addFooter($aRows, $mConfiguration=null)
    {
        if (null != $this->eventAddFooter) {
            $objFooter =& call_user_func($this->eventAddFooter, $aRows, $mConfiguration);
            $this->addChild($objFooter);
        } else {
            $objFooter =& $this->_onAddFooter($aRows, $mConfiguration);
            $this->addChild($objFooter);
        }
    }
        
    public function addBodies($aBodies, $mConfiguration=null)
    {
        foreach ($aBodies as $aRows) {
            $this->addBody($aRows, $mConfiguration);
        }
    }

    public function clearEvent_AddHeader()
    {
        $this->eventAddHeader = null;
    }
    public function clearEvent_AddHeaderRow()
    {
        $this->eventAddHeaderRow = null;
    }
    public function clearEvent_AddHeaderRowCell()
    {
        $this->eventAddHeaderRowCell = null;
    }
    public function clearEvent_AddBody()
    {
        $this->eventAddBody = null;
    }
    public function clearEvent_AddBodyRow()
    {
        $this->eventAddBodyRow = null;
    }
    public function clearEvent_AddBodyRowCell()
    {
        $this->eventAddBodyRowCell = null;
    }
    public function clearEvent_AddFooter()
    {
        $this->eventAddFooter = null;
    }
    public function clearEvent_AddFooterRow()
    {
        $this->eventAddFooterRow = null;
    }
    public function clearEvent_AddFooterRowCell()
    {
        $this->eventAddFooterRowCell = null;
    }
    
    public function setEvent_AddHeader($mFunction)
    {
        $mPrevious = $this->eventAddHeader;
        $this->eventAddHeader = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddHeaderRow($mFunction)
    {
        $mPrevious = $this->eventAddHeaderRow;
        $this->eventAddHeaderRow = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddHeaderRowCell($mFunction)
    {
        $mPrevious = $this->eventAddHeaderRowCell;
        $this->eventAddHeaderRowCell = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddBody($mFunction)
    {
        $mPrevious = $this->eventAddBody;
        $this->eventAddBody = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddBodyRow($mFunction)
    {
        $mPrevious = $this->eventAddBodyRow;
        $this->eventAddBodyRow = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddBodyRowCell($mFunction)
    {
        $mPrevious = $this->eventAddBodyRowCell;
        $this->eventAddBodyRowCell = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddFooter($mFunction)
    {
        $mPrevious = $this->eventAddFooter;
        $this->eventAddFooter = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddFooterRow($mFunction)
    {
        $mPrevious = $this->eventAddFooterRow;
        $this->eventAddFooterRow = $mFunction;
        return $mPrevious;
    }
    public function setEvent_AddFooterRowCell($mFunction)
    {
        $mPrevious = $this->eventAddFooterRowCell;
        $this->eventAddFooterRowCell = $mFunction;
        return $mPrevious;
    }
    
    public function &_onAddHeader($aRows, $mConfiguration)
    {
        $objTableHeader =& new clsThead();
        if (null != $this->eventAddHeaderRow) {
            $objTableHeader->setEvent_AddRow($this->eventAddHeaderRow);
        }
        if (null != $this->eventAddHeaderRowCell) {
            $objTableHeader->setEvent_AddRowCell($this->eventAddHeaderRowCell);
        }
        $objTableHeader->addRows($aRows, $mConfiguration);
        return $objTableHeader;
    }
    public function &_onAddBody($aRows, $mConfiguration)
    {
        $objTableBody =& new clsTbody();
        if (null != $this->eventAddBodyRow) {
            $objTableBody->setEvent_AddRow($this->eventAddBodyRow);
        }
        if (null != $this->eventAddBodyRowCell) {
            $objTableBody->setEvent_AddRowCell($this->eventAddBodyRowCell);
        }
        $objTableBody->addRows($aRows, $mConfiguration);
        return $objTableBody;
    }
    public function &_onAddFooter($aRows, $mConfiguration)
    {
        $objTableFooter =& new clsTfoot();
        if (null != $this->eventAddFooterRow) {
            $objTableFooter->setEvent_AddRow($this->eventAddFooterRow);
        }
        if (null != $this->eventAddFooterRowCell) {
            $objTableFooter->setEvent_AddRowCell($this->eventAddFooterRowCell);
        }
        $objTableFooter->addRows($aRows, $mConfiguration);
        return $objTableFooter;
    }
}

class clsCaption extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('caption', $aConfiguration);

        $this->sClass = '%block';
    }
}

class clsColgroup extends xajaxControlContainer
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('colgroup', $aConfiguration);

        $this->sClass = '%block';
        $this->sEndTag = 'optional';
    }
}

class clsCol extends xajaxControl
{
    public function __construct($aConfiguration=array())
    {
        parent::__construct('col');

        $this->sClass = '%block';
    }
}

/*
    Class: clsThead
*/
class clsThead extends clsTableRowContainer
{
    /*
        Function: clsThead
        
        Constructs and initializes an instance of the class.
    */
    public function __construct($aConfiguration=array())
    {
        parent::__construct('thead', $aConfiguration);
    }
}

/*
    Class: clsTbody
*/
class clsTbody extends clsTableRowContainer
{
    /*
        Function: clsTbody
        
        Constructs and initializes an instance of the class.
    */
    public function __construct($aConfiguration=array())
    {
        parent::__construct('tbody', $aConfiguration);
    }
}

/*
    Class: clsTfoot
*/
class clsTfoot extends clsTableRowContainer
{
    /*
        Function: clsTfoot
        
        Constructs and initializes an instance of the class.
    */
    public function __construct($aConfiguration=array())
    {
        parent::__construct('tfoot', $aConfiguration);
    }
}

/*
    Class: clsTr
*/
class clsTr extends xajaxControlContainer
{
    public $eventAddCell;
    
    /*
        Function: clsTr
        
        Constructs and initializes an instance of the class.
    */
    public function __construct($aConfiguration=array())
    {
        $this->clearEvent_AddCell();

        parent::__construct('tr', $aConfiguration);

        $this->sClass = '%block';
    }
    
    public function addCell($mCell, $mConfiguration=null)
    {
        if (null != $this->eventAddCell) {
            $objCell =& call_user_func($this->eventAddCell, $mCell, $mConfiguration);
            $this->addChild($objCell);
        } else {
            $objCell =& $this->_onAddCell($mCell, $mConfiguration);
            $this->addChild($objCell);
        }
    }
    
    public function addCells($aCells, $mConfiguration=null)
    {
        foreach ($aCells as $mCell) {
            $this->addCell($mCell, $mConfiguration);
        }
    }
    
    public function clearEvent_AddCell()
    {
        $this->eventAddCell = null;
    }
    
    public function setEvent_AddCell($mFunction)
    {
        $mPrevious = $this->eventAddCell;
        $this->eventAddCell = $mFunction;
        return $mPrevious;
    }
    
    public function &_onAddCell($mCell, $mConfiguration=null)
    {
        return new clsTd(array(
            'child' => new clsLiteral($mCell)
            ));
    }
}

/*
    Class: clsTd
*/
class clsTd extends xajaxControlContainer
{
    /*
        Function: clsTd
        
        Constructs and initializes an instance of the class.
    */
    public function __construct($aConfiguration=array())
    {
        parent::__construct('td', $aConfiguration);

        $this->sClass = '%flow';
    }
}

/*
    Class: clsTh
*/
class clsTh extends xajaxControlContainer
{
    /*
        Function: clsTh
        
        Constructs and initializes an instance of the class.
    */
    public function __construct($aConfiguration=array())
    {
        parent::__construct('th', $aConfiguration);

        $this->sClass = '%flow';
    }
}
