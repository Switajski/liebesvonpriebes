<?php
/**
 * adds options for short description
 *
 * @author ms
 * @created Fri, 07 Oct 2016 14:31:00 +0200
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
class Migration_20161007143100 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setConfig('artikeldetails_kurzbeschreibung_anzeigen', 'Y', 5, 'Kurzbeschreibung anzeigen', 'selectbox', 365, (object)[
            'cBeschreibung' => 'Soll die Kurzbeschreibung des Artikels auf der Detailseite angezeigt werden?',
            'inputOptions'  => [
                'Y' => 'Ja',
                'N' => 'Nein'
            ]]);
        $this->setConfig('artikeluebersicht_kurzbeschreibung_anzeigen', 'N', 4, 'Kurzbeschreibung anzeigen', 'selectbox', 315, (object)[
            'cBeschreibung' => 'Soll die Kurzbeschreibung des Artikels auf &Uuml;bersichtsseiten angezeigt werden?',
            'inputOptions'  => [
                'Y' => 'Ja',
                'N' => 'Nein'
            ]]);
    }

    public function down()
    {
        $this->removeConfig('artikeldetails_kurzbeschreibung_anzeigen');
        $this->removeConfig('artikeluebersicht_kurzbeschreibung_anzeigen');
    }
}
