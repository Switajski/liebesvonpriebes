<?php
/**
 * add news category image row
 *
 * @author dr
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
class Migration_20160510150906 extends Migration implements IMigration
{
    protected $author = 'dr';

    public function up()
    {
        $this->execute("ALTER TABLE tnewskategorie ADD `cPreviewImage` VARCHAR(255)");
    }

    public function down()
    {
        $this->dropColumn('tnewskategorie', 'cPreviewImage');
    }
}
