<?php
/**
 * add shipping language variable
 *
 * @author msc
 * @created Thu, 10 Oct 2016 16:17:00 +0200
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
class Migration_20161010161700 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'productShippingDesc', 'Gesonderte Versandkosten');
        $this->setLocalization('eng', 'checkout', 'productShippingDesc', 'Separate shipping costs');
        $this->setLocalization('ger', 'global', 'shippingMethods', 'Versandarten');
        $this->setLocalization('eng', 'global', 'shippingMethods', 'Shipping methods');
    }

    public function down()
    {
        $this->setLocalization('ger', 'checkout', 'productShippingDesc', 'Für folgende Artikel gelten folgende Versandkosten');
        $this->setLocalization('eng', 'checkout', 'productShippingDesc', 'Shipping costs for the following products');
        $this->removeLocalization('shippingMethods');
    }
}
