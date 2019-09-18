<?php
/**
 * Upgrade sessiondata to MEDIUMTEXT
 *
 * @author Falk Prüfer
 * @created Fri, 24 Feb 2017 13:37:10 +0100
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
class Migration_20170224133710 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Upgrade sessiondata to MEDIUMTEXT';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tsession
                CHANGE COLUMN cSessionData cSessionData MEDIUMTEXT NULL DEFAULT NULL"
        );
    }

    public function down()
    {
        // In case of downgrade all sessions will be deleted to prevent invalid session data by truncating.
        $this->execute(
            "DELETE FROM tsession"
        );
        $this->execute(
            "ALTER TABLE tsession
                CHANGE COLUMN cSessionData cSessionData TEXT NULL DEFAULT NULL"
        );
    }
}
