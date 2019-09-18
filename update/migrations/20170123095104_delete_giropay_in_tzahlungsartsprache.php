<?php
/**
 * delete giropay in tzahlungsartsprache
 *
 * @author Mirko Schmidt
 * @created Mon, 23 Jan 2017 09:51:04 +0100
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
class Migration_20170123095104 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->execute("DELETE FROM `tzahlungsartsprache` WHERE `kZahlungsart` = 0");
    }

    public function down()
    {
        // Not necessary
    }
}