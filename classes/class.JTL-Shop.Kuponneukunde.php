<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Kuponneukunde
 *
 * @access public
 * @author Daniel Böhmer
 */
class Kuponneukunde
{
    /**
     * @access public
     * @var int
     */
    public $kKuponNeukunde;

    /**
     * @access public
     * @var int
     */
    public $kKupon;

    /**
     * @access public
     * @var string
     */
    public $cEmail;

    /**
     * @access public
     * @var string
     */
    public $cDatenHash;

    /**
     * @access public
     * @var string
     */
    public $dErstellt;

    /**
     * @access public
     * @var string
     */
    public $cVerwendet;

    /**
     * Constructor
     *
     * @access public
     * @param object $oObj
     */
    public function __construct($oObj = null)
    {
        if (is_object($oObj)) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            if (is_array($cMember_arr) && count($cMember_arr) > 0) {
                foreach ($cMember_arr as $cMember) {
                    $cMethod = 'set' . substr($cMember, 1);
                    if (method_exists($this, $cMethod)) {
                        call_user_func([&$this, $cMethod], $oObj->$cMember);
                    }
                }
            }
        }
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods, true)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function Save()
    {
        if ($this->kKuponNeukunde > 0) {
            Shop::DB()->delete('tkuponneukunde', 'kKuponNeukunde', (int)$this->kKuponNeukunde);
        }
        $obj = kopiereMembers($this);
        unset($obj->kKuponNeukunde);

        return Shop::DB()->insert('tkuponneukunde', $obj) > 0;
    }

    /**
     * @return bool
     */
    public function Delete()
    {
        $effected = Shop::DB()->delete('tkuponneukunde', 'kKuponNeukunde', (int)$this->kKuponNeukunde);

        return $effected == 1;
    }

    /**
     * Sets the kKuponNeukunde
     *
     * @access public
     * @param int $kKuponNeukunde
     * @return $this
     */
    public function setKuponNeukunde($kKuponNeukunde)
    {
        $this->kKuponNeukunde = (int)$kKuponNeukunde;

        return $this;
    }

    /**
     * Sets the kKupon
     *
     * @access public
     * @param int $kKupon
     * @return $this
     */
    public function setKupon($kKupon)
    {
        $this->kKupon = (int)$kKupon;

        return $this;
    }

    /**
     * Sets the cEmail
     *
     * @access public
     * @param string $cEmail
     * @return $this
     */
    public function setEmail($cEmail)
    {
        $this->cEmail = $cEmail;

        return $this;
    }

    /**
     * Sets the cDatenHash
     *
     * @access public
     * @param string $cDatenHash
     * @return $this
     */
    public function setDatenHash($cDatenHash)
    {
        $this->cDatenHash = $cDatenHash;

        return $this;
    }

    /**
     * Sets the dErstellt
     *
     * @access public
     * @param string $dErstellt
     * @return $this
     */
    public function setErstellt($dErstellt)
    {
        $this->dErstellt = ($dErstellt === 'now()')
            ? date('Y-m-d H:i:s')
            : $dErstellt;

        return $this;
    }

    /**
     * Sets the cVerwendet
     *
     * @access public
     * @param string $cVerwendet
     * @return $this
     */
    public function setVerwendet($cVerwendet)
    {
        $this->cVerwendet = $cVerwendet;

        return $this;
    }

    /**
     * Gets the kKuponNeukunde
     *
     * @access public
     * @return int
     */
    public function getKuponNeukunde()
    {
        return $this->kKuponNeukunde;
    }

    /**
     * Gets the kKupon
     *
     * @access public
     * @return int
     */
    public function getKupon()
    {
        return $this->kKupon;
    }

    /**
     * Gets the cEmail
     *
     * @access public
     * @return string
     */
    public function getEmail()
    {
        return $this->cEmail;
    }

    /**
     * Gets the cDatenHash
     *
     * @access public
     * @return string
     */
    public function getDatenHash()
    {
        return $this->cDatenHash;
    }

    /**
     * Gets the dErstellt
     *
     * @access public
     * @return string
     */
    public function getErstellt()
    {
        return $this->dErstellt;
    }

    /**
     * @param string $email
     * @param string $hash
     * @return Kuponneukunde|null
     */
    public static function Load($email, $hash)
    {
        if (strlen($email) > 0 && strlen($hash) > 0) {
            $Obj = Shop::DB()->executeQueryPrepared("
                SELECT *
                    FROM tkuponneukunde
                    WHERE cEmail = :email
                    OR cDatenHash = :hash",
                [
                    'email' => StringHandler::filterXSS($email),
                    'hash'  => StringHandler::filterXSS($hash)
                ],
                1
            );

            if (isset($Obj->kKuponNeukunde) && $Obj->kKuponNeukunde > 0) {
                return new self($Obj);
            }
        }

        return null;
    }

    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $street
     * @param string|null $streetnumber
     * @param string|null $zipcode
     * @param string|null $town
     * @param string|null $country
     * @return string
     */
    public static function Hash($firstname = null, $lastname = null, $street = null, $streetnumber = null, $zipcode = null, $town = null, $country = null)
    {
        $Str = '';
        $Sep = ';';
        if ($firstname !== null) {
            $Str .= $firstname . $Sep;
        }
        if ($lastname !== null) {
            $Str .= $lastname . $Sep;
        }
        if ($street !== null) {
            $Str .= $street . $Sep;
        }
        if ($streetnumber !== null) {
            $Str .= $streetnumber . $Sep;
        }
        if ($zipcode !== null) {
            $Str .= $zipcode . $Sep;
        }
        if ($town !== null) {
            $Str .= $town . $Sep;
        }
        if ($country !== null) {
            $Str .= $country . $Sep;
        }

        return md5($Str);
    }
}
