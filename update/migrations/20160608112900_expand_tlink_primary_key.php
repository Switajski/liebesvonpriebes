<?php
/**
 * add new link types
 *
 * @author ms
 * @created Wed, 08 Jun 2016 11:29:00 +0200
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
class Migration_20160608112900 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE `tlink` DROP PRIMARY KEY, ADD PRIMARY KEY (`kLink`, `kLinkgruppe`);");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tlink` DROP PRIMARY KEY, ADD PRIMARY KEY (`kLink`);");
    }
}
