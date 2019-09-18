<?php
/** missing migration for manufacturer filter. sets coupon manufacturer filter if empty*/

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
class Migration_20171211131600 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("UPDATE tkupon SET cHersteller = '-1' WHERE cHersteller = '';");
    }

    public function down()
    {

    }
}
