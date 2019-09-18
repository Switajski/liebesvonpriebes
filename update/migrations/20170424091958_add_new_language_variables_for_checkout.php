<?php
/**
 * Add new language variables for checkout
 *
 * @author Falk Prüfer
 * @created Mon, 24 Apr 2017 09:19:58 +0200
 */

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20170424091958 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add new language variables for checkout';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'addressData', 'Adressdaten');
        $this->setLocalization('eng', 'global', 'addressData', 'Address data');
        $this->setLocalization('ger', 'global', 'preferredDeliveryAddress', 'Bevorzugte Lieferadresse');
        $this->setLocalization('eng', 'global', 'preferredDeliveryAddress', 'Preferred delivery address');
        $this->setLocalization('ger', 'account data', 'deviatingDeliveryAddress', 'Abweichende Lieferadresse');
        $this->setLocalization('eng', 'account data', 'deviatingDeliveryAddress', 'Deviating delivery address');
        $this->setLocalization('ger', 'account data', 'billingAndDeliveryAddress', 'Rechnungs- und Lieferadresse');
        $this->setLocalization('eng', 'account data', 'billingAndDeliveryAddress', 'Billing and delivery address');
        $this->setLocalization('ger', 'account data', 'shippingAndPaymentOptions', 'Versand- und Zahlungsart');
        $this->setLocalization('eng', 'account data', 'shippingAndPaymentOptions', 'Shipping and payment options');
        $this->setLocalization('ger', 'global', 'alreadyCustomer', 'Ich bin bereits Kunde');
        $this->setLocalization('eng', 'global', 'alreadyCustomer', 'I am already a customer');
        $this->setLocalization('ger', 'account data', 'editAddressData', 'Adressdaten ändern');
        $this->setLocalization('eng', 'account data', 'editAddressData', 'Edit address data');
        $this->setLocalization('ger', 'checkout', 'additionalPackaging', 'Zusatzverpackungen');
        $this->setLocalization('eng', 'checkout', 'additionalPackaging', 'Additional packaging');
        $this->setLocalization('ger', 'checkout', 'proceedNewCustomer', 'Als Neukunde fortfahren');
        $this->setLocalization('eng', 'checkout', 'proceedNewCustomer', 'Proceed as new customer');

        $this->setLocalization('eng', 'global', 'paymentOptions', 'Payment options');
    }

    public function down()
    {
        $this->removeLocalization('addressData');
        $this->removeLocalization('preferredDeliveryAddress');
        $this->removeLocalization('deviatingDeliveryAddress');
        $this->removeLocalization('billingAndDeliveryAddress');
        $this->removeLocalization('shippingAndPaymentOptions');
        $this->removeLocalization('alreadyCustomer');
        $this->removeLocalization('editAddressData');
        $this->removeLocalization('additionalPackaging');
        $this->removeLocalization('proceedNewCustomer');

        $this->setLocalization('eng', 'global', 'paymentOptions', 'payment options');
    }
}
