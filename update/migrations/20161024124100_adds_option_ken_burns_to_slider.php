<?php
/**
 * adds option for ken burns effect to sliders
 *
 * @author ms
 * @created Mon, 24 Oct 2016 12:41:00 +0200
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
class Migration_20161024124100 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE tslider ADD COLUMN bUseKB TINYINT(1) NOT NULL AFTER bRandomStart;");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tslider DROP COLUMN bUseKB");
    }
}
