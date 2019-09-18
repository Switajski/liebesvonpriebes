<?php
/**
 * add new object cache method
 *
 * @author fm
 * @created Fri, 21 Oct 2016 18:00:00 +0200
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
class Migration_20161021180000 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort) VALUES (1551, 'Dateien (erweitert)', 'advancedfile', 9)");
    }

    public function down()
    {
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = 1551 AND cWert = 'advancedfile'");
    }
}
