<?php
/**
 * adds paymentNotNecessary language variable
 *
 * @author ms
 * @created Wed, 10 May 2017 14:53:00 +0200
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
class Migration_20170510145300 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'adds paymentNotNecessary language variable';

    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'paymentNotNecessary', 'Keine Zahlung notwendig');
        $this->setLocalization('eng', 'checkout', 'paymentNotNecessary', 'Payment not necessary');
    }

    public function down()
    {
        $this->removeLocalization('paymentNotNecessary');
    }
}