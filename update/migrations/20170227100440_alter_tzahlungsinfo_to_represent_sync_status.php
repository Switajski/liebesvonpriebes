<?php
/**
 * Alter tzahlungsinfo to represent sync status
 *
 * @author Falk Prüfer
 * @created Mon, 27 Feb 2017 10:04:40 +0100
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
class Migration_20170227100440 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Alter tzahlungsinfo to represent sync status';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tzahlungsinfo
                ADD COLUMN cAbgeholt VARCHAR(1) NOT NULL DEFAULT 'N'"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE tzahlungsinfo
                DROP COLUMN cAbgeholt"
        );
    }
}
