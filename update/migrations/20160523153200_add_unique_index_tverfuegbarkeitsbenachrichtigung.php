<?php
/**
 * add unique index to tverfuegbarkeitsbenachrichtigung
 *
 * @author ms
 * @created Mon, 23 May 2016 15:32:00 +0200
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
class Migration_20160523153200 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("DELETE data1 FROM `tverfuegbarkeitsbenachrichtigung` data1, `tverfuegbarkeitsbenachrichtigung` data2 
                           WHERE  data1.`cMail` = data2.`cMail` 
                             AND data1.`kArtikel` = data2.`kArtikel` 
                             AND data1.`kVerfuegbarkeitsbenachrichtigung` < data2.`kVerfuegbarkeitsbenachrichtigung`");
        $this->execute("CREATE UNIQUE INDEX `idx_cMail_kArtikel`  ON `tverfuegbarkeitsbenachrichtigung` (cMail, kArtikel)");
    }

    public function down()
    {
        $this->execute("DROP INDEX `idx_cMail_kArtikel` ON `tverfuegbarkeitsbenachrichtigung`");
    }
}
