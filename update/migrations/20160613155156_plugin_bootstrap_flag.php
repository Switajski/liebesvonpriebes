<?php
/**
 * Plugin bootstrap flag
 *
 * @author andy
 * @created Mon, 13 Jun 2016 15:51:56 +0200
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
class Migration_20160613155156 extends Migration implements IMigration
{
    protected $author = 'andy';

    public function up()
    {
        $this->execute("ALTER TABLE `tplugin` ADD `bBootstrap` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
    }

    public function down()
    {
        $this->dropColumn('tplugin', 'bBootstrap');
    }
}
