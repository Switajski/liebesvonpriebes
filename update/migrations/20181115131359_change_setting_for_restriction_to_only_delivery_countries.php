<?php
/**
 * Change setting for restriction to only delivery countries
 *
 * @author fp
 * @created Thu, 15 Nov 2018 13:13:59 +0100
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
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20181115131359 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Change setting for restriction to only delivery countries';

    public function up()
    {
        $this->execute(
            "UPDATE teinstellungenconf
                SET cBeschreibung = 'Damit gibt es bei der Lieferadresse nur Länder zur Auswahl, für die min. eine Versandart definiert ist.'
                WHERE cWertName = 'kundenregistrierung_nur_lieferlaender'"
        );
    }

    public function down()
    {
        $this->execute(
            "UPDATE teinstellungenconf
                SET cBeschreibung = 'Damit gibt es bei der Rechnungsadresse und Lieferadresse nur Länder zur Auswahl, für die min. eine Versandart definiert ist.'
                WHERE cWertName = 'kundenregistrierung_nur_lieferlaender'"
        );
    }
}
