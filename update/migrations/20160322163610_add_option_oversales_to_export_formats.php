<?php
/**
 * add_option_oversales_to_export_formats
 *
 * @author sh
 * @created Tue, 22 Mar 2016 16:36:10 +0100
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
class Migration_20160322163610 extends Migration implements IMigration
{
    protected $author = 'sh';

    public function up()
    {
        $this->execute("INSERT INTO teinstellungenconfwerte (`kEinstellungenConf`,`cName`,`cWert`,`nSort`) VALUES ((SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='exportformate_lager_ueber_null' LIMIT 1),'Ja (mit Überverkäufen)','O',3)");
    }

    public function down()
    {
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf=(SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='exportformate_lager_ueber_null' LIMIT 1) AND cWert='O'");
    }
}
