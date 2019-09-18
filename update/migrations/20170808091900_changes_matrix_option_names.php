<?php
/**
 * changes matrix option names in configuration
 *
 * @author ms
 * @created Tue, 08 Aug 2017 09:19:00 +0200
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
class Migration_20170808091900 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'changes matrix option names in configuration';

    public function up()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Hochformat (nur bis zu 2 Variationen möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'H'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Querformat (nur bis zu 2 Variationen möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'Q'");
    }

    public function down()
    {
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Hochformat (nur bei 1 Variation möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'H'");
        $this->execute("UPDATE teinstellungenconfwerte SET cName='Querformat (nur bei 1 Variation möglich)' WHERE kEinstellungenConf = 1330 AND cWert = 'Q'");
    }
}
