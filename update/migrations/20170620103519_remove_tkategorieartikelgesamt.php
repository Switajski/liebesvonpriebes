<?php
/**
 * Remove tkategorieartikelgesamt
 *
 * @author fp
 * @created Tue, 20 Jun 2017 10:35:19 +0200
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
class Migration_20170620103519 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Remove tkategorieartikelgesamt';

    public function up()
    {
        $this->execute("DROP TABLE tkategorieartikelgesamt");
    }

    public function down()
    {
        $this->execute("CREATE TABLE tkategorieartikelgesamt (
            kArtikel       int(10) unsigned NOT NULL,
            kOberKategorie int(10) unsigned NOT NULL,
            kKategorie     int(10) unsigned NOT NULL,
            nLevel         int(10) unsigned NOT NULL,
            KEY kArtikel (kArtikel,kOberKategorie)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1");
        $this->execute("INSERT INTO tkategorieartikelgesamt (kArtikel, kOberKategorie, kKategorie, nLevel) (
            SELECT DISTINCT tkategorieartikel.kArtikel, oberkategorie.kOberKategorie, oberkategorie.kKategorie, oberkategorie.nLevel - 1
                FROM tkategorieartikel
                INNER JOIN tkategorie ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                INNER JOIN tkategorie oberkategorie ON tkategorie.lft BETWEEN oberkategorie.lft AND oberkategorie.rght
        )");
    }
}
