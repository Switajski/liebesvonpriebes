<?php
/**
 * remove_saferpay
 *
 * @author wp
 * @created Thu, 28 Apr 2016 16:27:06 +0200
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
class Migration_20160428162706 extends Migration implements IMigration
{
    protected $author = 'wp';

    public function up()
    {
        $this->execute("UPDATE `tzahlungsart` SET `nActive` = 0, `nNutzbar` = 0 WHERE `cModulId` = 'za_saferpay_jtl'");
        $this->execute("DELETE FROM `tversandartzahlungsart` WHERE `kZahlungsart` IN (SELECT `kZahlungsart` FROM `tzahlungsart` WHERE `cModulId` = 'za_saferpay_jtl')");
    }

    public function down()
    {
        $this->execute("UPDATE `tzahlungsart` SET `nActive` = 1, `nNutzbar` = 1 WHERE `cModulId` = 'za_saferpay_jtl'");
    }
}
