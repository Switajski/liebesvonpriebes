<?php
/**
 * change the column type of tuploadschemasprache.cBeschreibung to TEXT to hold longer descriptions
 *
 * @author dr
 * @created Fr, 07 Oct 2016 16:19:00 +0200
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
class Migration_20161007161900 extends Migration implements IMigration
{
    protected $author = 'dr';

    public function up()
    {
        $this->execute("ALTER TABLE tuploadschemasprache MODIFY cBeschreibung TEXT NOT NULL");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tuploadschemasprache MODIFY cBeschreibung VARCHAR(45) NOT NULL");
    }
}
