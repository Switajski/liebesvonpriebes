<?php
/**
 * remove_clickandbuy
 *
 * @author wp
 * @created Tue, 12 Apr 2016 09:59:29 +0200
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
class Migration_20160412095929 extends Migration implements IMigration
{
    protected $author = 'wp';

    public function up()
    {
        $this->execute("UPDATE `tzahlungsart` SET `nActive` = 0 WHERE `cModulId` = 'za_clickandbuy_jtl'");
        $this->execute("DELETE FROM `tversandartzahlungsart` WHERE `kZahlungsart` IN (SELECT `kZahlungsart` FROM `tzahlungsart` WHERE `cModulId` = 'za_clickandbuy_jtl')");
        $this->execute("DELETE FROM `tadminmenu` WHERE `kAdminmenu` = 52");
    }

    public function down()
    {
        $this->execute("UPDATE `tzahlungsart` SET `nActive` = 1 WHERE `cModulId` = 'za_clickandbuy_jtl'");
        $this->execute("INSERT INTO `tadminmenu` (`kAdminmenu`, `kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`) VALUES (52, 18, 'core_jtl', 'ClickandBuy', 'clickandbuy.php', 'ORDER_CLICKANDBUY_VIEW', 150)");
    }
}
