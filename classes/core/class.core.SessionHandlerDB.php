<?php

/**
 * Class SessionHandlerDB
 */
class SessionHandlerDB extends \JTL\core\SessionHandler implements SessionHandlerInterface
{
    /**
     * @var int
     */
    protected $lifeTime;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param string $savePath
     * @param string $sessName
     * @return mixed
     */
    public function open($savePath, $sessName)
    {
        //get session lifetime
        $this->lifeTime = get_cfg_var('session.gc_maxlifetime');

        return Shop::DB()->isConnected();
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $sessID
     * @return string
     */
    public function read($sessID)
    {
        $res = Shop::DB()->executeQueryPrepared(
            "SELECT cSessionData FROM tsession
                WHERE cSessionId = :id
                AND nSessionExpires > :time",
            ['id' => $sessID, 'time' => time()],
            1
        );

        return ($res !== false && isset($res->cSessionData)) ? $res->cSessionData : '';
    }

    /**
     * @param string $sessID
     * @param string $sessData
     * @return bool
     */
    public function write($sessID, $sessData)
    {
        $sessID = Shop::DB()->escape($sessID);
        //set new session expiration
        $newExp = time() + $this->lifeTime;
        //is a session with this id already in the database?
        $res = Shop::DB()->select('tsession', 'cSessionId', $sessID);
        //if yes,
        if (!empty($res)) {
            //...update session data
            $update                  = new stdClass();
            $update->nSessionExpires = $newExp;
            $update->cSessionData    = $sessData;
            // if something happened, return true
            if (Shop::DB()->update('tsession', 'cSessionId', $sessID, $update) > 0) {
                return true;
            }
        } else { //if no session was found, create a new row
            $session                  = new stdClass();
            $session->cSessionId      = $sessID;
            $session->nSessionExpires = $newExp;
            $session->cSessionData    = $sessData;

            if (Shop::DB()->insert('tsession', $session) > 0) {
                return true;
            }
        }

        //an unknown error occured
        return false;
    }

    /**
     * delete session data
     *
     * @param string $sessID
     * @return bool
     */
    public function destroy($sessID)
    {
        //if session was deleted, return true,
        return Shop::DB()->delete('tsession', 'cSessionId', $sessID) > 0;
    }

    /**
     * delete old sessions
     *
     * @param int $sessMaxLifeTime
     * @return int
     */
    public function gc($sessMaxLifeTime)
    {
        // return affected rows
        return Shop::DB()->query("DELETE FROM tsession WHERE nSessionExpires < " . time(), 3);
    }
}
