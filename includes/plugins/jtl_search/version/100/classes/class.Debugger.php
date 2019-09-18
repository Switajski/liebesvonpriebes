<?php

/**
 * DebuggerEcho Class
 * @access public
 * @author Andre Vermeulen JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
require_once("interface.IDebugger.php");

final class Debugger implements IDebugger
{
    private $oDebugger_arr = array();

    public function __construct($oDebugger = null)
    {
        if ($oDebugger instanceof IDebugger) {
            $this->setDebugger($oDebugger);
        } elseif ($oDebugger === null) {
            $this->buildDebugger();
        }
    }

    public function setDebugger(IDebugger $oDebugger)
    {
        array_push($this->oDebugger_arr, $oDebugger);
    }
    
    private function buildDebugger()
    {
        if (JTLSEARCH_DEBUGGING) {
            switch (JTLSEARCH_DEBUGGING_MODE) {
                case 1:
                    require_once(JTLSEARCH_PFAD_CLASSES.'class.DebuggerLog.php');
                    $this->setDebugger(new DebuggerLog(JTLSEARCH_DEBUGGING_LOG_FILE));
                    break;
                case 2:
                    require_once(JTLSEARCH_PFAD_CLASSES.'class.DebuggerExtLog.php');
                    $this->setDebugger(new DebuggerExtLog(JTLSEARCH_DEBUGGING_EXTLOG_URL));
                    break;
                case 3:
                    require_once(JTLSEARCH_PFAD_CLASSES.'class.DebuggerEcho.php');
                    $this->setDebugger(new DebuggerEcho());
                    break;

                default:
                    require_once(JTLSEARCH_PFAD_CLASSES.'class.DebuggerLog.php');
                    $this->setDebugger(new DebuggerLog(JTLSEARCH_DEBUGGING_LOG_FILE));
                    break;
            }
        }
    }

    public function doDebug($cMessage, $nLogLevel = JTLSEARCH_DEBUGGING_LEVEL_NOTICE)
    {
        if ($nLogLevel <= JTLSEARCH_DEBUGGING_LEVEL) {
            $cMessage = str_replace(array("\r", "\r\n", "\n"),  '', $cMessage);
            foreach ($this->oDebugger_arr as $oDebugger) {
                $oDebugger->doDebug('[Level:'.$nLogLevel.']'.$cMessage);
            }
        }
    }
    
    public function doVarDebug($xVar)
    {
        if (is_string($xVar) || is_numeric($xVar) || is_bool($xVar) || is_null($xVar)) {
            $cResult = $xVar;
        } elseif (is_array($xVar)) {
            $cResult = $this->doArray2String($xVar);
        } elseif (is_object($xVar)) {
            $cResult = $this->doObject2String($xVar);
        }
        $this->doDebug($cResult);
    }
    
    private function doArray2String($aVar)
    {
        $bFirst = true;
        $cResult = 'array(';
        foreach ($aVar as $cKey => $xValue) {
            if (!$bFirst) {
                $cResult .= ', ';
            } else {
                $bFirst = false;
            }
            $cResult .= $cKey.'=>';
            if (is_string($xValue) || is_numeric($xValue) || is_bool($xValue) || is_null($xValue)) {
                $cResult .= $xValue;
            } elseif (is_array($xValue)) {
                $cResult .= $this->doArray2String($xValue);
            } elseif (is_object($xValue)) {
                $cResult .= $this->doObject2String($xValue);
            }
        }
        $cResult .= ')';
        return $cResult;
    }
    
    private function doObject2String($oVar)
    {
        $bFirst = true;
        $cResult = 'object{'.get_class($oVar).'}(';
        $aObjectVars = get_object_vars($oVar);
        foreach ($aVar as $cKey => $xValue) {
            if (!$bFirst) {
                $cResult .= ', ';
            } else {
                $bFirst = false;
            }
            $cResult .= $cKey.'=>';
            if (is_string($xValue) || is_numeric($xValue) || is_bool($xValue) || is_null($xValue)) {
                $cResult .= $xValue;
            } elseif (is_array($xValue)) {
                $cResult .= $this->doArray2String($xValue);
            } elseif (is_object($xValue)) {
                $cResult .= $this->doObject2String($xValue);
            }
        }
        $cResult .= ')';
        return $cResult;
    }
}
