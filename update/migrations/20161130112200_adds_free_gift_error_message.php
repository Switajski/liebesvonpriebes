<?php
/**
 * adds free gift error message
 *
 * @author ms
 * @created Wed, 30 Nov 2016 11:22:00 +0100
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
class Migration_20161130112200 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'errorMessages', 'freegiftsMinimum', 'Der Gratisartikel-Mindestbestellwert ist nicht erreicht.');
        $this->setLocalization('eng', 'errorMessages', 'freegiftsMinimum', 'Minimum shopping cart value not reached for this free gift.');
    }

    public function down()
    {
        $this->removeLocalization('freegiftsMinimum');
    }
}
