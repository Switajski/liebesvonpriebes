<?php

/**
 * Debugger Interface
 *
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
interface IDebugger
{
    /**
     * @param $cMessage
     * @return $this
     */
    public function doDebug($cMessage);
}
