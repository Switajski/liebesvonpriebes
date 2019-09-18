<?php
/**
 * DebuggerLog Class
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
require_once("interface.IDebugger.php");

final class DebuggerLog implements IDebugger
{
    private $cLogFile;
    
    public function __construct($cLogFile = "")
    {
        $this->cLogFile = JTLSEARCH_DEBUGGING_LOG_FILE;
        if (strlen($cLogFile) > 0) {
            $this->cLogFile = $cLogFile;
        }
    }
    
    public function doDebug($cMessage)
    {
        if (strlen($cMessage) > 0) {
            $cTime = date("[Y-m-d H:i:s]");
            error_log("{$cTime} {$cMessage}\n", 3, $this->cLogFile);
        }
    }
}
