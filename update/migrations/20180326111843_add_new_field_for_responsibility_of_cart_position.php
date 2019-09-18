<?php
/**
 * Add new field for responsibilty of cart position.
 *
 * @author fp
 * @created Mon, 26 Mar 2018 11:18:43 +0200
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
class Migration_20180326111843 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add new field for responsibility of cart position.';

    public function up()
    {
        $this->execute(
            "ALTER TABLE twarenkorbpos ADD COLUMN cResponsibility VARCHAR(255) NOT NULL DEFAULT 'core' AFTER cUnique"
        );
        $this->execute(
            "ALTER TABLE twarenkorbperspos ADD COLUMN cResponsibility VARCHAR(255) NOT NULL DEFAULT 'core' AFTER cUnique"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE twarenkorbpos DROP COLUMN cResponsibility"
        );
        $this->execute(
            "ALTER TABLE twarenkorbperspos DROP COLUMN cResponsibility"
        );
    }
}
