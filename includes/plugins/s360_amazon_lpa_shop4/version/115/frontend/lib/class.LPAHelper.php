<?php

/**
 *  Helper class with multiple useful methods.
 *  Future intention: replace lpa_utils.php
 */
class LPAHelper {

    /**
     * Converts a billing address from Amazon Pay to a billing address in JTL.
     *
     * @param $billingaddress
     * @param $customerMail
     * @return null|stdClass
     */
    public static function convertBillingAddressFromAmazonToJtl($billingaddress, $customerMail = null) {
        if(empty($billingaddress)) {
            return null;
        }

        $rechnungsAdresse = new stdClass();
        $aName_arr = explode(" ", utf8_decode($billingaddress['Name']), 2);
        if (count($aName_arr) === 2) {
            $rechnungsAdresse->cVorname = $aName_arr[0];
            $rechnungsAdresse->cNachname = $aName_arr[1];
        } else {
            $rechnungsAdresse->cNachname = utf8_decode($billingaddress['Name']);
        }

        /*
         * This logic was recommended by Amazon Pay
         */
        $cStrasse_arr = array();
        if (isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) && strlen(trim($billingaddress['AddressLine1'])) > 0) {
            $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine1']);
        }
        if (isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) && strlen(trim($billingaddress['AddressLine2'])) > 0) {
            $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine2']);
        }
        if (isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) && strlen(trim($billingaddress['AddressLine3'])) > 0) {
            $cStrasse_arr[] = utf8_decode($billingaddress['AddressLine3']);
        }

        if (count($cStrasse_arr) === 1) {
            $rechnungsAdresse->cStrasse = $cStrasse_arr[0];
        } else {
            $rechnungsAdresse->cFirma = isset($billingaddress['AddressLine1']) && is_string($billingaddress['AddressLine1']) ? utf8_decode($billingaddress['AddressLine1']) : '';
            $rechnungsAdresse->cStrasse = isset($billingaddress['AddressLine2']) && is_string($billingaddress['AddressLine2']) ? utf8_decode($billingaddress['AddressLine2']) : '';
            $rechnungsAdresse->cAdressZusatz = isset($billingaddress['AddressLine3']) && is_string($billingaddress['AddressLine3']) ? utf8_decode($billingaddress['AddressLine3']) : '';
        }

        /*
         * heuristic correction for the street and streetnumber in the shop backend. same is done by wawi sync when
         * addresses come from the wawi (see function extractStreet in syncinclude.php)
         */
        $cData_arr = explode(' ', $rechnungsAdresse->cStrasse);
        if (count($cData_arr) > 1) {
            $rechnungsAdresse->cHausnummer = $cData_arr[count($cData_arr) - 1];
            unset($cData_arr[count($cData_arr) - 1]);
            $rechnungsAdresse->cStrasse = implode(' ', $cData_arr);
        }

        $rechnungsAdresse->cOrt = utf8_decode($billingaddress['City']);
        $rechnungsAdresse->cPLZ = utf8_decode($billingaddress['PostalCode']);
        $rechnungsAdresse->cLand = ISO2land($billingaddress['CountryCode']);
        if(!empty($customerMail)) {
            $rechnungsAdresse->cMail = $customerMail;
        }
        return $rechnungsAdresse;
    }
}