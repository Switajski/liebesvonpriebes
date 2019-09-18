<?php
/**
 * Create a new table to hold the emergency-codes for the 2FA.
 *
 * @author Clemens Rudolph
 * @created Mon, 06 Mar 2017 13:08:02 +0100
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
class Migration_20170306130802 extends Migration implements IMigration
{
    protected $author = 'cr';
    protected $description = 'Create a new table to hold the emergency-codes for the 2FA.';

    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `tadmin2facodes`(`kAdminlogin` INT(11) NOT NULL DEFAULT 0, `cEmergencyCode` VARCHAR(64) NOT NULL DEFAULT '', KEY `kAdminlogin` (`kAdminlogin`), UNIQUE KEY `cEmergencyCode` (`cEmergencyCode`) )");
    }

    public function down()
    {
        $this->execute('DROP TABLE `tadmin2facodes`');
    }
}
