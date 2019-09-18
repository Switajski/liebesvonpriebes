<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include_once __DIR__ . '/Billpay.class.php';

/**
 * Billpay implementation
 */
class BillpayRatePayment extends Billpay
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name         = 'BillPay Ratenkauf';
        $this->caption      = 'BillPay Ratenkauf';
        $this->nPaymentType = IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT;

        return $this;
    }
}
