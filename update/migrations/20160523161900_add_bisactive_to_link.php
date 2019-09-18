<?php
/**
 * active status for link sites
 *
 * @author ms
 * @created Mon, 23 May 2016 16:19:00 +0200
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
 */
class Migration_20160523161900 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE `tlink` ADD COLUMN `bIsActive` TINYINT(1) NOT NULL DEFAULT 1;");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tlink` DROP COLUMN `bIsActive`;");
    }
}
