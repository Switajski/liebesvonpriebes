<?php
/** add a manufacturer column to tkupon to enable manufacturer specific coupons*/

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
class Migration_20161108112500 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE tkupon ADD COLUMN cHersteller TEXT NOT NULL AFTER cArtikel;");

        $this->setLocalization('ger', 'global', 'couponErr12', 'Der Kupon ist für den aktuellen Warenkorb ungültig (gilt nur für bestimmte Hersteller).');
        $this->setLocalization('eng', 'global', 'couponErr12', 'This coupon is invalid for your cart (valid only for specific manufacturers).');
    }

    public function down()
    {
        $this->dropColumn('tkupon', 'cHersteller');
        $this->removeLocalization('couponErr12');
    }
}
