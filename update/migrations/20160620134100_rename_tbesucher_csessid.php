<?php
/**
 * Rename tbesucher.cSessId to tbesucher.cSessID
 *
 * @author dr
 * @created Mon, 20 Jun 2016 13:41:00 +0200
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
class Migration_20160620134100 extends Migration implements IMigration
{
    protected $author = 'dr';

    public function up()
    {
        $this->execute("ALTER TABLE `tbesucher` CHANGE COLUMN `cSessId` `cSessID` VARCHAR(128)");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tbesucher` CHANGE COLUMN `cSessID` `cSessId` VARCHAR(128)");
    }
}
