<?php
/**
 * Adds account language variables
 *
 * @author ms
 * @created Wed, 18 Jan 2017 15:24:00 +0100
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
class Migration_20170118152400 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Adds language variables account section';

    public function up()
    {
        $this->setLocalization('ger', 'account data', 'accountOverview', 'Übersicht');
        $this->setLocalization('eng', 'account data', 'accountOverview', 'Overview');

        $this->setLocalization('ger', 'account data', 'orders', 'Bestellungen');
        $this->setLocalization('eng', 'account data', 'orders', 'Orders');

        $this->setLocalization('ger', 'account data', 'addresses', 'Adressen');
        $this->setLocalization('eng', 'account data', 'addresses', 'Addresses');

        $this->setLocalization('ger', 'account data', 'wishlists', 'Wunschlisten');
        $this->setLocalization('eng', 'account data', 'wishlists', 'Wishlists');
    }

    public function down()
    {
        $this->removeLocalization('accountOverview');
        $this->removeLocalization('orders');
        $this->removeLocalization('addresses');
        $this->removeLocalization('wishlists');
    }
}
