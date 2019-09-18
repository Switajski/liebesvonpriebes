<?php
/**
 * tell_a_friend
 *
 * @author wp
 * @created Wed, 06 Apr 2016 09:21:55 +0200
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
class Migration_20160406092155 extends Migration implements IMigration
{
    protected $author = 'wp';

    public function up()
    {
        $this->execute("DROP TABLE IF EXISTS `tartikelweiterempfehlenhistory`;");
    }

    public function down()
    {
        $this->execute("CREATE TABLE `tartikelweiterempfehlenhistory` (
                      `kArtikelWeiterempfehlenHistory` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `kArtikel` int(10) unsigned NOT NULL,
                      `kSprache` int(10) unsigned NOT NULL,
                      `kKunde` int(10) unsigned NOT NULL,
                      `cName` varchar(255) NOT NULL,
                      `cEmail` varchar(255) NOT NULL,
                      `cIP` varchar(255) NOT NULL,
                      `dErstellt` datetime NOT NULL,
                      PRIMARY KEY (`kArtikelWeiterempfehlenHistory`),
                      KEY `kArtikel` (`kArtikel`,`kSprache`,`cIP`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
    }
}
