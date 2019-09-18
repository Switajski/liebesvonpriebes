<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Staat
 */
class Staat
{
    /**
     * @var int
     */
    public $kStaat;

    /**
     * @var string
     */
    public $cLandIso;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cCode;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
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
            if (in_array($method, $methods, true) && method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getStaat()
    {
        return $this->kStaat;
    }

    /**
     * @return string
     */
    public function getLandIso()
    {
        return $this->cLandIso;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->cCode;
    }

    /**
     * @param int $kStaat
     * @return $this
     */
    public function setStaat($kStaat)
    {
        $this->kStaat = (int)$kStaat;

        return $this;
    }

    /**
     * @param string $cLandIso
     * @return $this
     */
    public function setLandIso($cLandIso)
    {
        $this->cLandIso = $cLandIso;

        return $this;
    }

    /**
     * @param string $cName
     * @return $this
     */
    public function setName($cName)
    {
        $this->cName = $cName;

        return $this;
    }

    /**
     * @param string $cCode
     * @return $this
     */
    public function setCode($cCode)
    {
        $this->cCode = $cCode;

        return $this;
    }

    /**
     * @param string $cLandIso
     * @return array|null
     */
    public static function getRegions($cLandIso)
    {
        if (strlen($cLandIso) === 2) {
            $oObj_arr = Shop::DB()->selectAll('tstaat', 'cLandIso', $cLandIso);
            if (is_array($oObj_arr) && count($oObj_arr) > 0) {
                $oStaat_arr = [];
                foreach ($oObj_arr as $oObj) {
                    $options = [
                        'Staat'   => $oObj->kStaat,
                        'LandIso' => $oObj->cLandIso,
                        'Name'    => $oObj->cName,
                        'Code'    => $oObj->cCode,
                    ];

                    $oStaat_arr[] = new self($options);
                }

                return $oStaat_arr;
            }
        }

        return null;
    }

    /**
     * @param string $cCode
     * @param string $cLandISO
     * @return null|Staat
     */
    public static function getRegionByIso($cCode, $cLandISO = '')
    {
        if (strlen($cCode) > 0) {
            $key2 = null;
            $val2 = null;
            if (strlen($cLandISO) > 0) {
                $key2 = 'cLandIso';
                $val2 = $cLandISO;
            }
            $oObj = Shop::DB()->select('tstaat', 'cCode', $cCode, $key2, $val2);
            if (isset($oObj->kStaat) && $oObj->kStaat > 0) {
                $options = [
                    'Staat'   => $oObj->kStaat,
                    'LandIso' => $oObj->cLandIso,
                    'Name'    => $oObj->cName,
                    'Code'    => $oObj->cCode,
                ];

                return new self($options);
            }
        }

        return null;
    }

    /**
     * @param string $cName
     * @return null|Staat
     */
    public static function getRegionByName($cName)
    {
        if (strlen($cName) > 0) {
            $oObj = Shop::DB()->select('tstaat', 'cName', $cName);
            if (isset($oObj->kStaat) && $oObj->kStaat > 0) {
                $options = [
                    'Staat'   => $oObj->kStaat,
                    'LandIso' => $oObj->cLandIso,
                    'Name'    => $oObj->cName,
                    'Code'    => $oObj->cCode,
                ];

                return new self($options);
            }
        }

        return null;
    }
}
