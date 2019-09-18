<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Synclogin
 */
class Synclogin
{
    /**
     * @var string
     */
    public $cMail;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cPass;

    /**
     * Synclogin constructor.
     * get wawi sync user/pass from db
     */
    public function __construct()
    {
        $obj = Shop::DB()->select('tsynclogin', 1, 1);
        if ($obj !== null) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        } else {
            Jtllog::writeLog('Kein Sync-Login gefunden.', JTLLOG_LEVEL_ERROR);
        }
    }
}
