<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SuchStrings
 */
class SuchStrings
{
    /**
     * @var int
     */
    public $kSuchStrings;

    /**
     * @var string
     */
    public $cSession;

    /**
     * @var string
     */
    public $cSuchString;

    /**
     * @var string - [yyyy.mm.dd]
     */
    public $dZeit;

    /**
     * Konstruktor
     *
     * @param int $kSuchStrings - Falls angegeben, wird der SuchStrings mit angegebenem kSuchStrings aus der DB geholt
     */
    public function __construct($kSuchStrings = 0)
    {
        if ((int)$kSuchStrings > 0) {
            $this->loadFromDB($kSuchStrings);
        }
    }

    /**
     * Setzt SuchStrings mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kSuchStrings
     * @return $this
     */
    public function loadFromDB($kSuchStrings)
    {
        $kSuchStrings = (int)$kSuchStrings;
        $obj          = Shop::DB()->select('tsuchstrings', 'kSuchStrings', $kSuchStrings);
        if ($obj->kSuchStrings > 0) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     * @access public
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->kSuchStrings);
        $this->kSuchStrings = Shop::DB()->insert('tsuchstrings', $obj);

        return $this->kSuchStrings;
    }
}
