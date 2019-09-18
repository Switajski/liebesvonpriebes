<?php
/**
 * add new special page type for compare list
 *
 * @author fm
 * @created Thu, 15 Sep 2016 17:52:00 +0200
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
class Migration_20160915175200 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("INSERT INTO `tspezialseite` (`kPlugin`, `cName`, `cDateiname`, `nLinkart`, `nSort`) VALUES ('0', 'Vergleichsliste', 'vergleichsliste.php', '35', '35')");
    }

    public function down()
    {
        $this->execute("DELETE FROM `tspezialseite` WHERE `nLinkart` = '35'");
    }
}
