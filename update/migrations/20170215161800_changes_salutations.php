<?php
/**
 * Changes salutions
 *
 * @author ms
 * @created Wed, 15 Feb 2017 16:18:00 +0100
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
class Migration_20170215161800 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'changes female salutation to ms and adds general salutation';

    public function up()
    {
        $this->setLocalization('eng', 'global', 'salutationW', 'Ms');

        $this->setLocalization('ger', 'global', 'salutationGeneral', 'Frau/Herr');
        $this->setLocalization('eng', 'global', 'salutationGeneral', 'Ms/Mr');
    }

    public function down()
    {
        $this->setLocalization('eng', 'global', 'salutationW', 'Mrs');

        $this->removeLocalization('salutationGeneral');
    }
}