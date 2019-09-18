<?php
/**
 * remove-shopinfo-menu-point
 *
 * @author Martin Schophaus
 * @created Thu, 01 Mar 2018 13:52:00 +0100
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
class Migration_20180301135200 extends Migration implements IMigration
{
    protected $author = 'Martin Schophaus';
    protected $description = 'remove-shopinfo-menu-point';

    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cLinkname = 'Shopinfo (elm@ar)'");
    }

    public function down()
    {
        $this->execute("
          INSERT INTO tadminmenu (kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort)
          VALUES (12, 'core_jtl', 'Shopinfo (elm@ar)', 'shopinfoexport.php', 'EXPORT_SHOPINFO_VIEW', 40)          
        ");
    }
}
