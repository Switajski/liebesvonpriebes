<?php
/**
 * sets nofollow for special pages
 *
 * @author ms
 * @created Tue, 28 Feb 2017 16:31:00 +0100
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
class Migration_20170228163100 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'sets nofollow for special pages';

    public function up()
    {
        $this->execute(
            "UPDATE `tlink` SET `cNoFollow` = 'Y' WHERE `nLinkart`= '11' OR `nLinkart`= '12' OR `nLinkart`= '24';"
        );
    }

    public function down()
    {
        $this->execute(
            "UPDATE `tlink` SET `cNoFollow` = 'N' WHERE `nLinkart`= '11' OR `nLinkart`= '12' OR `nLinkart`= '24';"
        );
    }
}
