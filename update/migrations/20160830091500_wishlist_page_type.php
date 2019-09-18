<?php
/**
 * add new link type for wishlist
 *
 * @author fm
 * @created Tue, 30 Oct 2016 09:15:00 +0200
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
class Migration_20160830091500 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("INSERT INTO `tspezialseite` (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`) VALUES ('0', 'Wunschliste', 'wunschliste.php', '34', '34')");
    }

    public function down()
    {
        $this->execute("DELETE FROM `tspezialseite` WHERE `nLinkart` = '34'");
    }
}
