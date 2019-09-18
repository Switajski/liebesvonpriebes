<?php
/**
 * Change text to mediumtext for tnewsletter
 *
 * @author Falk Prüfer
 * @created Thu, 09 Mar 2017 15:12:22 +0100
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
class Migration_20170309151222 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Change text to mediumtext for tnewsletter';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tnewsletter
                CHANGE COLUMN cInhaltHTML cInhaltHTML MEDIUMTEXT NOT NULL,
                CHANGE COLUMN cInhaltText cInhaltText MEDIUMTEXT NOT NULL"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE tnewsletter
                CHANGE COLUMN cInhaltHTML cInhaltHTML TEXT NOT NULL,
                CHANGE COLUMN cInhaltText cInhaltText TEXT NOT NULL"
        );
    }
}
