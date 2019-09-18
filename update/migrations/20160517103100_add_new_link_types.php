<?php
/**
 * add new link types
 *
 * @author fm
 * @created Mon, 17 May 2016 10:31:00 +0200
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
class Migration_20160517103100 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("INSERT INTO `tspezialseite` (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`) VALUES ('0', 'Bestellvorgang', 'bestellvorgang.php', '32', '32')");
        $this->execute("INSERT INTO `tspezialseite` (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`) VALUES ('0', 'Bestellabschluss', 'bestellabschluss.php', '33', '33')");
    }

    public function down()
    {
        $this->execute("DELETE FROM `tspezialseite` WHERE `nLinkart` = '32' OR `nLinkart` = '33'");
    }
}
