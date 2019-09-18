<?php
/**
 * @author ms
 * @created Mon, 12 Sep 2016 15:53:00 +0200
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
class Migration_20160912155300 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE `twarenkorbperspos` ADD COLUMN `nPosTyp` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `twarenkorbperspos` DROP COLUMN `nPosTyp`");
    }
}
