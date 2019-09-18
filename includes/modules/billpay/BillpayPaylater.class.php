<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include_once __DIR__ . '/Billpay.class.php';

/**
 * Billpay implementation
 */
class BillpayPaylater extends Billpay
{
    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->name         = 'BillPay PayLater Ratenkauf';
        $this->caption      = 'BillPay PayLater Ratenkauf';
        $this->nPaymentType = IPL_CORE_PAYMENT_TYPE_PAY_LATER;

        return $this;
    }

    /**
     * @param Bestellung $oOrder
     */
    public function preparePaymentProcess($oOrder)
    {
        $oPaymentEx = isset($_SESSION['za_billpay_jtl']['oOrderEx'])
            ? $_SESSION['za_billpay_jtl']['oOrderEx']
            : null;

        if ($oPaymentEx === null) {
            BPHelper::log("canceled capture, invalid session information");
            header("location: bestellvorgang.php?editZahlungsart=1");
        }

        $oCustomer = $_SESSION['Kunde'];
        $oShipAddr = $_SESSION['Lieferadresse'];
        $oBasket   = $_SESSION['Warenkorb'];
        $oShipment = $_SESSION['Versandart'];

        BPHelper::removeHTML($oCustomer, $oShipAddr);

        $cOrderNumber = baueBestellnummer();
        $oBasketInfo  = $oPaymentEx->oBasketInfo;

        $nState = $this->preAuthorize($oCustomer, $oShipAddr, $oBasket, $oShipment, $cOrderNumber);

        if ($nState === 1) {
            $oOrder = finalisiereBestellung($cOrderNumber, true);

            // set order status to paid
            if ($this->getCoreSetting('aspaid') === 'Y') {
                $oIncomingPayment = new stdClass();
                $oIncomingPayment->fBetrag = $oBasketInfo->fTotal[AMT_GROSS] + $oBasket->gibGesamtsummeWarenExt([
                        C_WARENKORBPOS_TYP_ZINSAUFSCHLAG,
                        C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR
                    ], true);
                $oIncomingPayment->cISO = $oBasketInfo->cCurrency->cISO;
                $this->addIncomingPayment($oOrder, $oIncomingPayment);
                $this->setOrderStatusToPaid($oOrder);
            }

            unset($_SESSION['za_billpay_jtl']['oOrderEx']);

            $session = Session::getInstance();
            $session->cleanUp();
        }

        Shop::Smarty()->assign('oOrder', $oOrder)
            ->assign('oPaymentEx', $oPaymentEx)
            ->assign('nPaymentType', IPL_CORE_PAYMENT_TYPE_PAY_LATER)
            ->assign('nSSL', pruefeSSL())
            ->assign('nState', $nState);
    }

    /**
     * @return bool
     */
    public function preauthRequest()
    {
        $oPaymentEx = isset($_SESSION['za_billpay_jtl']['oOrderEx'])
            ? $_SESSION['za_billpay_jtl']['oOrderEx']
            : null;

        if ($oPaymentEx !== null) {
            $cName['ger'] = 'Bearbeitungsgeb&uuml;hr';
            $cName['eng'] = 'Processing fee';
            $this->addSpecialPosition(
                $cName,
                1,
                BPHelper::fmtAmountX($oPaymentEx->nFeeTotal),
                C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR,
                true,
                true,
                ''
            );
        }

        return true;
    }
}
