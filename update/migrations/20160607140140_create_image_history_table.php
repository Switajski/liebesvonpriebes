<?php
/**
 * create image history table
 *
 * @author andy
 * @created Tue, 07 Jun 2016 14:01:40 +0200
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
class Migration_20160607140140 extends Migration implements IMigration
{
    protected $author = 'andy';

    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `tartikelpicthistory` (`kArtikel` int(10) unsigned NOT NULL, `cPfad` varchar(255) NOT NULL, `nNr` tinyint(3) unsigned NOT NULL DEFAULT \'1\', UNIQUE KEY `UNIQUE` (`kArtikel`,`nNr`,`cPfad`)) ENGINE=MyISAM DEFAULT CHARSET=latin1');
        $this->execute('REPLACE INTO `tartikelpicthistory` (SELECT `kArtikel`, `cPfad`, `nNr` FROM `tartikelpict` WHERE `kBild` = 0)');
    }

    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tartikelpicthistory`');
    }
}
