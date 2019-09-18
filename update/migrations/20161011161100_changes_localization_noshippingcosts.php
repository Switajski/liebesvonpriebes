<?php
/**
 * changes language variables noShippingCosts
 *
 * @author ms
 * @created Tue, 11 Oct 2016 16:11:00 +0200
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
class Migration_20161011161100 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'basket', 'noShippingCostsAt', 'Noch %s und wir versenden kostenfrei mit %s %s');
        $this->setLocalization('eng', 'basket', 'noShippingCostsAt', 'Buy for another %s and get no shipping costs with %s %s');

        $this->setLocalization('ger', 'basket', 'noShippingCostsAtExtended', 'innerhalb von %s');
        $this->setLocalization('eng', 'basket', 'noShippingCostsAtExtended', 'to: %s');

        $this->setLocalization('ger', 'basket', 'noShippingCostsReached', 'Ihre Bestellung ist ohne Versandkosten mit %s %s');
        $this->setLocalization('eng', 'basket', 'noShippingCostsReached', 'Your order has no shipping costs with %s %s');
    }

    public function down()
    {
        $this->setLocalization('ger', 'basket', 'noShippingCostsAt', 'Noch %s und wir versenden kostenfrei');
        $this->setLocalization('eng', 'basket', 'noShippingCostsAt', 'Buy for another %s and get no shipping costs');

        $this->setLocalization('ger', 'basket', 'noShippingCostsAtExtended', 'Innerhalb von %s');
        $this->setLocalization('eng', 'basket', 'noShippingCostsAtExtended', 'To: %s');

        $this->setLocalization('ger', 'basket', 'noShippingCostsReached', 'Ihre Bestellung ist ohne Versandkosten');
        $this->setLocalization('eng', 'basket', 'noShippingCostsReached', 'Your order has no shipping costs');
    }
}
