<?php
/**
 * create columns for dynamic options sources
 *
 * @author fm
 * @created Fri, 20 May 2016 14:21:00 +0200
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
class Migration_20160520142100 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("ALTER TABLE `tplugineinstellungenconf` ADD COLUMN `cSourceFile` VARCHAR(255) NULL DEFAULT NULL");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tplugineinstellungenconf` DROP COLUMN `cSourceFile`");
    }
}
