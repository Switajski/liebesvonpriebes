<?php

include_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

/**
 * Class DresdnerCetelem
 */
class DresdnerCetelem extends PaymentMethod
{
    /**
     * @var string
     */
    public $Haendlernummer = '';

    /**
     * @param int $nAgainCheckout
     * @return $this
     */
    public function init($nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);
        $this->name    = 'DresdnerCetelem';
        $this->caption = 'DresdnerCetelem';

        return $this;
    }

    /**
     * @param Bestellung $order
     */
    public function preparePaymentProcess($order)
    {
        $customer             = $_SESSION['Kunde'];
        $this->Haendlernummer = $this->getHaendlernummer();

        if ($order->fGesamtsummeKundenwaehrung > 0) {
            $paymentHash = $this->generateHash($order);
            $cAnrede     = ($customer->cAnrede === 'm') ? 'HERR' : 'FRAU';
            $cAnzahlung  = '';
            if ($this->gibAnzahlungMoeglich() === 'Y') {
                $cAnzahlung = '&/CreditCalculator/firstPayment=0';
            }
            $fGesamtsummeKundenWaehrung = str_replace('.', ',', round($order->fGesamtsummeKundenwaehrung, 2));

            $cURL = 'https://finanzierung.commerzfinanz.com/ecommerce/entry?vendorid=' . $this->Haendlernummer  .
                '&order_amount=' . $fGesamtsummeKundenWaehrung . $cAnzahlung . '&order_id=' .
                urlencode(utf8_encode($order->cBestellNr)) . '&firstname=' .
                urlencode(utf8_encode($customer->cVorname)) . '&lastname=' .
                urlencode(utf8_encode($customer->cNachname)) . '&email=' .
                $customer->cMail . '&street=' . urlencode(utf8_encode($customer->cStrasse . ' ' .
                    $customer->cHausnummer)) . '&zip=' . $customer->cPLZ . '&city=' .
                urlencode(utf8_encode($customer->cOrt)) . '&successURL=' . $this->getNotificationURL($paymentHash);

            $strReturn = '<a href="#" class="submit" onClick="open_window(\'' .
                $cURL . '\'); changeButton(this); return false;">' .
                Shop::Lang()->get('payWithDresdnercetelem', 'global') . '</a>';

            Shop::Smarty()->assign('dresdnercetelemform', $strReturn);
        }
    }

    /**
     * @return string
     */
    public function getHaendlernummer()
    {
        global $Einstellungen;

        return $Einstellungen['zahlungsarten']['zahlungsart_dresdnercetelem_haendlernummer'];
    }

    /**
     * @return string
     */
    public function gibAnzahlungMoeglich()
    {
        global $Einstellungen;

        return $Einstellungen['zahlungsarten']['zahlungsart_dresdnercetelem_anzahlung'];
    }

    /**
     * @return int|null
     */
    public function gibMindestArtikelwert()
    {
        global $Einstellungen;
        $fMinWert = isset($Einstellungen['zahlungsarten']['zahlungsart_dresdnercetelem_min'])
            ? (int)$Einstellungen['zahlungsarten']['zahlungsart_dresdnercetelem_min']
            : null;
        if ($fMinWert < 100) {
            $fMinWert = 100;
        }

        return $fMinWert;
    }

    /**
     * @param array $args_arr
     * @return bool
     */
    public function isValidIntern($args_arr = [])
    {
        if (strlen($this->getHaendlernummer()) === 0) {
            ZahlungsLog::add($this->moduleID, 'Pflichtparameter "Haendlernummer" ist nicht gesetzt!', null, LOGLEVEL_ERROR);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function canPayAgain()
    {
        return true;
    }
}
