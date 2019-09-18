<?php
/**
 * added_option_for_dimension_of_articles
 *
 * @author Mirko Schmidt
 * @created Fri, 13 May 2016 16:23:57 +0200
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
class Migration_20160513162357 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->execute("INSERT INTO `teinstellungenconf` (`kEinstellungenConf`,`kEinstellungenSektion`,`cName`,`cBeschreibung`,`cWertName`,`cInputTyp`,`cModulId`,`nSort`,`nStandardAnzeigen`,`nModul`,`cConf`) VALUES (1651,5,'Abmessungen anzeigen?','Maße des Artikels in LxBxH','artikeldetails_abmessungen_anzeigen','selectbox',NULL,1490,1,0,'Y')");
        $this->execute("INSERT INTO `teinstellungen` (`kEinstellungenSektion`,`cName`,`cWert`) VALUES (5,'artikeldetails_abmessungen_anzeigen','N')");
        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1651,'Nein','N',1)");
        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES (1651,'Ja','Y',2)");
    }

    public function down()
    {
        $this->removeConfig('artikeldetails_abmessungen_anzeigen');
    }
}
