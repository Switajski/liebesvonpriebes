<?php
/**
 * Add plugin hook priority
 *
 * @author fm
 * @created Wed, 29 Jun 2016 11:42:00 +0200
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
class Migration_20160629114200 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("ALTER TABLE `tpluginhook` ADD COLUMN `nPriority` INT(10) NULL DEFAULT 5");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tpluginhook` DROP COLUMN `nPriority`");
    }
}
