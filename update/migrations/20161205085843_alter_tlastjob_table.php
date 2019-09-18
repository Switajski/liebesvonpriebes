<?php
/**
 * Alter tlastjob table
 *
 * @author fp
 * @created Mon, 05 Dec 2016 08:58:43 +0100
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
class Migration_20161205085843 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tlastjob
                ADD COLUMN cType     ENUM('RPT', 'STD')  NOT NULL DEFAULT 'STD' AFTER kJob,
                ADD COLUMN nJob      INT(11)             NOT NULL               AFTER cType,
                ADD COLUMN cJobName  VARCHAR(128)            NULL               AFTER nJob,
                ADD COLUMN nCounter  INT(10)             NOT NULL DEFAULT 0     AFTER dErstellt,
                ADD COLUMN nFinished INT(1)              NOT NULL DEFAULT 0     AFTER nCounter,
                CHANGE COLUMN kJob kJob INT(10) UNSIGNED NOT NULL AUTO_INCREMENT"
        );
        $this->execute(
            "UPDATE tlastjob SET
                nJob      = kJob,
                cType     = 'RPT',
                nFinished = 1"
        );
        $this->execute(
            "ALTER TABLE tlastjob
                ADD UNIQUE KEY idx_uq_nJob (nJob)"
        );
    }

    public function down()
    {
        $this->execute(
            "DELETE FROM tlastjob WHERE cType = 'STD'"
        );
        $this->execute(
            "ALTER TABLE tlastjob
                CHANGE COLUMN kJob kJob INT(10) UNSIGNED NOT NULL,
                DROP COLUMN cType,
                DROP COLUMN nJob,
                DROP COLUMN cJobName,
                DROP COLUMN nCounter,
                DROP COLUMN nFinished,
                DROP KEY idx_uq_nJob"
        );
    }
}
