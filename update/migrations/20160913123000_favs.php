<?php
/**
 * Add language variables for the new pagination
 *
 * @author fm
 * @created Mon, 12 Sep 2016 17:30:00 +0200
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
class Migration_20160913123000 extends Migration implements IMigration
{
    protected $author = 'andy';
    protected $description = 'Create admin favorite table';

    public function up()
    {
        $this->execute("
            CREATE TABLE `tadminfavs` (
             `kAdminfav` int(10) unsigned NOT NULL AUTO_INCREMENT,
             `kAdminlogin` int(10) unsigned NOT NULL,
             `cTitel` varchar(255) NOT NULL,
             `cUrl` varchar(255) NOT NULL,
             `nSort` int(10) unsigned NOT NULL DEFAULT '0',
             PRIMARY KEY (`kAdminfav`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `tadminfavs`");
    }
}
