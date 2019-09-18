<?php
/**
 * Add available column to redirect table
 *
 * @author Danny Raufeisen
 * @created Tue, 09 May 2017 17:00:00 +0200
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
class Migration_20170509165900 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add available column to redirect table';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tredirect
                ADD COLUMN cAvailable CHAR(1) DEFAULT 'u'"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE tredirect
                DROP COLUMN bAvailable"
        );
    }
}