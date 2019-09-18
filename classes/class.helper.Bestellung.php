<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class BestellungHelper
 */
class BestellungHelper extends WarenkorbHelper
{
    /**
     * @var Bestellung
     */
    protected $object;

    /**
     * @param Bestellung $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * @param int $decimals
     * @return object
     */
    public function getTotal($decimals = 0)
    {
        $order = $this->getObject();

        $info            = new stdClass();
        $info->type      = self::GROSS;
        $info->currency  = null;
        $info->article   = [0, 0];
        $info->shipping  = [0, 0];
        $info->discount  = [0, 0];
        $info->surcharge = [0, 0];
        $info->total     = [0, 0];
        $info->items     = [];

        $info->currency = $order->Waehrung;

        foreach ($order->Positionen as $oPosition) {
            $amountItem  = $oPosition->fPreisEinzelNetto;
            $amount      = $amountItem; /* $order->fWaehrungsFaktor;*/
            $amountGross = $amount + ($amount * $oPosition->fMwSt / 100);
            // floating-point precission bug
            $amountGross = (float)(string)$amountGross;

            switch ($oPosition->nPosTyp) {
                case C_WARENKORBPOS_TYP_ARTIKEL: {
                    $item = (object)[
                        'name'     => '',
                        'quantity' => 1,
                        'amount'   => []
                    ];

                    $item->name = html_entity_decode($oPosition->cName);

                    $item->amount = [
                        self::NET   => $amount,
                        self::GROSS => $amountGross
                    ];

                    if ((int)$oPosition->nAnzahl != $oPosition->nAnzahl) {
                        $item->amount[self::NET] *= $oPosition->nAnzahl;
                        $item->amount[self::GROSS] *= $oPosition->nAnzahl;

                        $item->name = sprintf('%g %s %s',
                            (float)$oPosition->nAnzahl,
                            $oPosition->Artikel->cEinheit ?: 'x',
                            $item->name
                        );
                    } else {
                        $item->quantity = (int)$oPosition->nAnzahl;
                    }

                    $info->article[self::NET] += $item->amount[self::NET] * $item->quantity;
                    $info->article[self::GROSS] += $item->amount[self::GROSS] * $item->quantity;

                    $info->items[] = $item;
                    break;
                }

                case C_WARENKORBPOS_TYP_VERSANDPOS:
                case C_WARENKORBPOS_TYP_VERSANDZUSCHLAG:
                case C_WARENKORBPOS_TYP_VERPACKUNG:
                case C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG: {
                    $info->shipping[self::NET] += $amount * $oPosition->nAnzahl;
                    $info->shipping[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;
                }

                case C_WARENKORBPOS_TYP_KUPON:
                case C_WARENKORBPOS_TYP_GUTSCHEIN:
                case C_WARENKORBPOS_TYP_NEUKUNDENKUPON: {
                    $info->discount[self::NET] += $amount * $oPosition->nAnzahl;
                    $info->discount[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;
                }

                case C_WARENKORBPOS_TYP_ZAHLUNGSART:
                case C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR: {
                    $info->surcharge[self::NET] += $amount * $oPosition->nAnzahl;
                    $info->surcharge[self::GROSS] += $amountGross * $oPosition->nAnzahl;
                    break;
                }
            }
        }

        if ($order->fGuthaben != 0) {
            $amountGross = $order->fGuthaben;
            $amount      = $amountGross;

            $info->discount[self::NET] += $amount;
            $info->discount[self::GROSS] += $amountGross;
        }

        // positive discount
        $info->discount[self::NET] *= -1;
        $info->discount[self::GROSS] *= -1;

        // total
        // $info->total[self::NET]   = $info->article[self::NET] + $info->shipping[self::NET] - $info->discount[self::NET] + $info->surcharge[self::NET];
        // $info->total[self::GROSS] = $info->article[self::GROSS] + $info->shipping[self::GROSS] - $info->discount[self::GROSS] + $info->surcharge[self::GROSS];

        $info->total[self::NET] = $order->fGesamtsummeNetto;
        $info->total[self::GROSS] = $order->fGesamtsumme;

        $formatter = function ($prop) use ($decimals) {
            return [
                self::NET   => number_format($prop[self::NET], $decimals, '.', ''),
                self::GROSS => number_format($prop[self::GROSS], $decimals, '.', ''),
            ];
        };

        if ($decimals > 0) {
            $info->article   = $formatter($info->article);
            $info->shipping  = $formatter($info->shipping);
            $info->discount  = $formatter($info->discount);
            $info->surcharge = $formatter($info->surcharge);
            $info->total     = $formatter($info->total);

            foreach ($info->items as &$item) {
                $item->amount = $formatter($item->amount);
            }
        }

        return $info;
    }

    /**
     * @return Bestellung
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return Lieferadresse|Rechnungsadresse
     */
    public function getShippingAddress()
    {
        if ((int)$this->object->kLieferadresse > 0 && is_object($this->object->Lieferadresse)) {
            return $this->object->Lieferadresse;
        }

        return $this->getBillingAddress();
    }

    /**
     * @return Rechnungsadresse
     */
    public function getBillingAddress()
    {
        return $this->object->oRechnungsadresse;
    }

    /**
     * @return Kunde
     */
    public function getCustomer()
    {
        return $this->object->oKunde;
    }

    /**
     * @return object
     */
    public function getCurrency()
    {
        return $this->object->Waehrung;
    }

    /**
     * @return string iso
     */
    public function getLanguage()
    {
        return Shop::Lang()->getIsoFromLangID($this->object->kSprache);
    }

    /**
     * @return string
     */
    public function getInvoiceID()
    {
        return $this->object->cBestellNr;
    }

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return (int)$this->object->kBestellung;
    }
}
