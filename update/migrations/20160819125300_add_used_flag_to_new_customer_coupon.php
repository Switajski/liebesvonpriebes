<?php
/**
 * used flag for new customer coupons
 *
 * @author ms
 * @created Fri, 19 Aug 2016 12:53:00 +0200
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
class Migration_20160819125300 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE `tkuponneukunde` ADD COLUMN `cVerwendet` VARCHAR(1) NOT NULL DEFAULT 'N';");
    }

    public function down()
    {
        $this->dropColumn("tkuponneukunde", "cVerwendet");
    }
}
