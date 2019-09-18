<?php
/**
 * add lang key choose filter
 *
 * @author ms
 * @created Tue, 11 Apr 2017 08:50:00 +0200
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
class Migration_20170411085000 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'add lang key select filter';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'selectFilter', 'Beliebig');
        $this->setLocalization('eng', 'global', 'selectFilter', 'Any');
    }

    public function down()
    {
        $this->removeLocalization('selectFilter');
    }
}