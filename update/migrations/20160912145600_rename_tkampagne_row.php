<?php
/**
 * Rename row in tkampagne and tkampagnedef to not blow up the table-width in statistics overview in backend
 *
 * @author dr
 * @created Mo, 12 Sep 2016 14:56:00 +0200
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
 */
class Migration_20160912145600 extends Migration implements IMigration
{
    protected $author = 'dr';

    public function up()
    {
        $this->execute(
            "UPDATE tkampagne
                SET cName = 'Verfügbarkeits-Benachrichtigungen' WHERE kKampagne = 1"
        );
        $this->execute(
            "UPDATE tkampagnedef
                SET cName = 'Verfügbarkeits-Anfrage' WHERE kKampagneDef = 6"
        );
    }

    public function down()
    {
        $this->execute(
            "UPDATE tkampagne
                SET cName = 'Verfügbarkeitsbenachrichtigungen' WHERE kKampagne = 1"
        );
        $this->execute(
            "UPDATE tkampagnedef
                SET cName = 'Verfügbarkeitsanfrage' WHERE kKampagneDef = 6"
        );
    }
}
