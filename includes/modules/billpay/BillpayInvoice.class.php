<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include_once __DIR__ . '/Billpay.class.php';

/**
 * Billpay implementation
 */
class BillpayInvoice extends Billpay
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name         = 'BillPay Rechnung';
        $this->caption      = 'BillPay Rechnung';
        $this->nPaymentType = IPL_CORE_PAYMENT_TYPE_INVOICE;

        return $this;
    }
}
