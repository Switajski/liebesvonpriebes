<?php
/**
 * delete_serbia_and_montenegro_from_tland
 *
 * @author Mirko Schmidt
 * @created Mon, 08 Aug 2016 12:16:33 +0200
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
class Migration_20160808121633 extends Migration implements IMigration
{
    protected $author = 'Mirko Schmidt';

    public function up()
    {
        $this->execute("DELETE FROM `tland` WHERE `cISO` = 'YU'");
        $this->execute("UPDATE `tland` SET `cEnglisch` = 'Serbia' WHERE `cISO` = 'RS'");
    }

    public function down()
    {
        $this->execute("INSERT INTO `tland` (`cISO`, `cDeutsch`, `cEnglisch`, `nEU`, `cKontinent`) VALUES('YU', 'Serbien und Montenegro', 'Serbia and Montenegro', 0, 'Europa')");
        $this->execute("UPDATE `tland` SET `cEnglisch` = 'Serbien' WHERE `cISO` = 'RS'");
    }
}
