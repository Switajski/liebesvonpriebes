<?php
/**
 * moves the 404 page into the hidden linkgroup
 *
 * @author ms
 * @created Tue, 17 May 2016 13:23:00 +0200
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
class Migration_20160517132300 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("UPDATE `tlink` SET `kLinkgruppe` = (SELECT `kLinkgruppe` FROM `tlinkgruppe` WHERE `cName` = 'hidden') WHERE `nLinkart`= '29';");
    }

    public function down()
    {
        $this->execute("UPDATE `tlink` SET `kLinkgruppe`='0' WHERE `nLinkart`= '29';");
    }
}
