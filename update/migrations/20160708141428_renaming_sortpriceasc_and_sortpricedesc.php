<?php
/**
 * renaming_sortPriceAsc_and_sortPriceDesc
 *
 * @author Mirko Schmidt
 * @created Fri, 08 Jul 2016 14:14:28 +0200
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
class Migration_20160708141428 extends Migration implements IMigration
{
    protected $author = 'Mirko Schmidt';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'sortPriceAsc', 'Preis aufsteigend');
        $this->setLocalization('eng', 'global', 'sortPriceAsc', 'Price ascending');
        $this->setLocalization('ger', 'global', 'sortPriceDesc', 'Preis absteigend');
        $this->setLocalization('eng', 'global', 'sortPriceDesc', 'Price descending');
    }

    public function down()
    {
        $this->setLocalization('ger', 'global', 'sortPriceAsc', 'Preis 1..9');
        $this->setLocalization('eng', 'global', 'sortPriceAsc', 'Price 1..9');
        $this->setLocalization('ger', 'global', 'sortPriceDesc', 'Preis 9..1');
        $this->setLocalization('eng', 'global', 'sortPriceDesc', 'Price 9..1');
    }
}
