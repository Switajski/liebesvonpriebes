<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class BillpayData
 */
class BillpayData
{
    /**
     * @var bool
     */
    public $bB2B = false;

    /**
     * @var bool
     */
    public $bToc = false;

    /**
     * @var object
     */
    public $oBasketInfo;

    /**
     * @var string
     */
    public $cTel;

    /**
     * @var string
     */
    public $cKundengruppe = '';

    /**
     * @var string
     */
    public $cAnrede;

    /**
     * @var string
     */
    public $dGeburtstag;

    /**
     * @var string
     */
    public $cFirma;

    /**
     * @var string
     */
    public $cInhaber;

    /**
     * @var string
     */
    public $cRechtsform;

    /**
     * @var string
     */
    public $cHrn;

    /**
     * @var string
     */
    public $cUSTID;

    /**
     * @var int
     */
    public $nInstalments = 0;

    /**
     * @var int
     */
    public $nDuration = 0;

    /**
     * @var int
     */
    public $nFeeTotal = 0;

    /**
     * @var int
     */
    public $nTotalAmount = 0;

    /**
     * @var int
     */
    public $nRate = 0;

    /**
     * @var string
     */
    public $cAccountholder;

    /**
     * @var string
     */
    public $cAccountnumber;

    /**
     * @var string
     */
    public $cSortcode;

    /**
     * BillpayData constructor.
     * @param null|array|object $object
     */
    public function __construct($object = null)
    {
        $this->cast($object);
    }

    /**
     * @param array|object $object
     */
    public function cast($object)
    {
        if (is_array($object) || is_object($object)) {
            foreach ($object as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
