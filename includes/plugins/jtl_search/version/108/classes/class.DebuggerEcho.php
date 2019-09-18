<?php
/**
 * DebuggerEcho Class
 *
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
require_once 'interface.IDebugger.php';

/**
 * Class DebuggerEcho
 */
final class DebuggerEcho implements IDebugger
{
    /**
     * @param $cMessage
     * @return $this
     */
    public function doDebug($cMessage)
    {
        if (strlen($cMessage) > 0) {
            $cTime = date('[Y-m-d H:i:s]');
            echo "{$cTime} {$cMessage}\n";
        }

        return $this;
    }
}
