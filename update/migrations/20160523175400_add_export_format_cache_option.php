<?php
/**
 * @author fm
 * @created Mon, 23 May 2016 17:54:00 +0200
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
class Migration_20160523175400 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("ALTER TABLE `texportformat` ADD COLUMN `nUseCache` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `texportformat` DROP COLUMN `nUseCache`");
    }
}
