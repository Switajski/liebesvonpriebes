<?php
/**
 * Add language variable one-off
 *
 * @author Mirko
 * @created Tue, 01 Aug 2017 13:10:13 +0200
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
class Migration_20170801131013 extends Migration implements IMigration
{
    protected $author = 'msc';
    protected $description = 'Add language variable one-off';

    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'one-off', 'Einmalig enthalten');
        $this->setLocalization('eng', 'checkout', 'one-off', 'Included one-time');
    }

    public function down()
    {
        $this->removeLocalization('one-off');
    }
}