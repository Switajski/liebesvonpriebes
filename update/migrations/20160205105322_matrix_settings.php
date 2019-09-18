<?php
/**
 * matrix-settings
 *
 * @author dh
 * @created Fri, 05 Feb 2016 10:53:22 +0100
 */

/**
 * Class Migration_20160205105322
 */
class Migration_20160205105322 extends Migration implements IMigration
{
    protected $author = 'dh';

    public function up()
    {
        $this->execute("INSERT INTO `teinstellungenconfwerte` (kEinstellungenConf, cName, cWert, nSort) VALUES(1330, 'Liste (nur bei Varkombis möglich)', 'L', 3);");
        $this->execute("UPDATE `teinstellungenconfwerte` SET cName='Hochformat (nur bei 1 Variation möglich)' WHERE kEinstellungenConf=1330 AND cWert='H';");
        $this->execute("UPDATE `teinstellungenconfwerte` SET cName='Querformat (nur bei 1 Variation möglich)' WHERE kEinstellungenConf=1330 AND cWert='Q';");
    }

    public function down()
    {
        $this->execute("DELETE FROM `teinstellungenconfwerte` WHERE kEinstellungenConf=1330 AND cWert='L';");
        $this->execute("UPDATE `teinstellungenconfwerte` SET cName='Hochformat' WHERE kEinstellungenConf=1330 AND cWert='H';");
        $this->execute("UPDATE `teinstellungenconfwerte` SET cName='Querformat' WHERE kEinstellungenConf=1330 AND cWert='Q';");
    }
}
