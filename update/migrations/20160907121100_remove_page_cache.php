<?php
/**
 * Remove page cache options
 *
 * @author fm
 * @created Wed, 07 Sep 2016 12:11:00 +0200
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
class Migration_20160907121100 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("DELETE FROM `teinstellungenconf` WHERE kEinstellungenConf = 1562 OR kEinstellungenConf = 1563 OR kEinstellungenConf = 1564");
        $this->execute("DELETE FROM `teinstellungenconfwerte` WHERE kEinstellungenConf = 1562 OR kEinstellungenConf = 1563 OR kEinstellungenConf = 1564");
    }

    public function down()
    {
        $this->execute("INSERT INTO `teinstellungenconf` (`kEinstellungenConf`,`kEinstellungenSektion`,`cName`,`cBeschreibung`,`cWertName`,`cInputTyp`,`cModulId`,`nSort`,`nStandardAnzeigen`,`nModul`,`cConf`) VALUES (1562,124,'Seiten-Cache aktivieren?','Cachet ganze Seiten','caching_page_cache','selectbox',NULL,110,2,0,'Y')");
        $this->execute("INSERT INTO `teinstellungenconf` (`kEinstellungenConf`,`kEinstellungenSektion`,`cName`,`cBeschreibung`,`cWertName`,`cInputTyp`,`cModulId`,`nSort`,`nStandardAnzeigen`,`nModul`,`cConf`) VALUES (1563,124,'Seiten-Cache-Debugging aktivieren?','Zeigt via Header \"JTL - Cached\" an, ob Seiten im Cache sind oder nicht','page_cache_debugging','selectbox',NULL,120,2,0,'Y')");
        $this->execute("INSERT INTO `teinstellungenconf` (`kEinstellungenConf`,`kEinstellungenSektion`,`cName`,`cBeschreibung`,`cWertName`,`cInputTyp`,`cModulId`,`nSort`,`nStandardAnzeigen`,`nModul`,`cConf`) VALUES (1564,124,'Objekt-Cache-Methode auch für Seiten-Cache verwenden?','Nutzt den Objekt-Cache auch zum Speichern von HTML-Inhalten des Seiten-Caches','advanced_page_cache','selectbox',NULL,130,2,0,'Y')");

        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1562,'Nein','0',1)");
        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1562,'Ja, immer','1',2)");
        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1562,'Ja, nur bei Gästen','2',3)");
        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1562,'Ja, nur bei leerem Warenkorb','3',4)");

        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1563,'Nein','N',1)");
        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1563,'Ja','Y',2)");

        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1564,'Nein','N',1)");
        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1564,'Ja','Y',2)");
    }
}
