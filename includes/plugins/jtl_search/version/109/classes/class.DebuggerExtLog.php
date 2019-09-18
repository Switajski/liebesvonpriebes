<?php
/**
 * DebuggerExtLog Class
 *
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
require_once 'interface.IDebugger.php';

/**
 * Class DebuggerExtLog
 */
final class DebuggerExtLog implements IDebugger
{
    /**
     * @var
     */
    private $cLogUrl;

    /**
     * @param string $cLogUrl
     */
    public function __construct($cLogUrl = '')
    {
        $this->cLogUrl = JTLSEARCH_DEBUGGING_EXTLOG_URL;
        if (strlen($cLogUrl) > 0) {
            $this->cLogUrl = $cLogUrl;
        }
    }

    /**
     * @param $cMessage
     * @return $this
     */
    public function doDebug($cMessage)
    {
        if (strlen($cMessage) > 0) {
            Communication::postData(
                $this->cLogUrl,
                array(
                    'a'       => 'writelog',
                    'name'    => urlencode(JTLSEARCH_DEBUGGING_LOG_FILE),
                    'content' => urlencode($cMessage),
                    'p'       => JTLSEARCH_SECRET_KEY)
            );
        }

        return $this;
    }
}
