<?php
/**
 * livesearch language setting
 *
 * @author fp
 * @created Fri, 11 Mar 2016 14:41:22 +0100
 */

/**
 * Class Migration_20160311144122
 */
class Migration_20160311144122 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'noDataAvailable', 'Keine Daten verf&uuml;gbar!');
        $this->setLocalization('eng', 'global', 'noDataAvailable', 'No data available!');
    }

    public function down()
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 1 AND `cName` = 'noDataAvailable';");
    }
}
