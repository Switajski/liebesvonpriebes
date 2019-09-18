<?php
/**
 * Rename options for setting 192
 *
 * @author Falk Prüfer
 * @created Thu, 28 Sep 2017 16:24:40 +0200
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
class Migration_20170928162440 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Rename options for setting 192';

    public function up()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'Automatischer Wechsel zu https' WHERE kEinstellungenConf = 192 AND cWert = 'P'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'Kein automatischer Wechsel' WHERE kEinstellungenConf = 192 AND cWert = 'N'");
    }

    public function down()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'Permanentes SSL mit eigenem Zertifikat' WHERE kEinstellungenConf = 192 AND cWert = 'P'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName = 'SSL-Verschl&uuml;sselung deaktivieren' WHERE kEinstellungenConf = 192 AND cWert = 'N'");
    }
}
