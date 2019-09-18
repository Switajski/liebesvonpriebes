<?php
/**
 * Create Menu for PLZ import
 *
 * @author fp
 * @created Fri, 28 Oct 2016 11:14:05 +0200
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
class Migration_20161028111405 extends Migration implements IMigration
{
    protected $author = 'fp';

    /**
     * @param int $kAdminmenueGruppe
     */
    protected function reorderMenu($kAdminmenueGruppe)
    {
        $this->execute("SET @SortStart = 0");
        $this->execute(
            "UPDATE tadminmenu SET nSort = @SortStart:=@SortStart + 10
                WHERE kAdminmenueGruppe = " . (int)$kAdminmenueGruppe . " 
                ORDER BY nSort;"
        );
    }

    public function up()
    {
        $this->reorderMenu(11);
        $this->execute(
            "INSERT INTO tadminrecht (cRecht, cBeschreibung, kAdminrechtemodul) 
                SELECT 'PLZ_ORT_IMPORT_VIEW', 'PLZ-Import', kAdminrechtemodul 
                FROM tadminrechtemodul 
                WHERE cName = 'Import / Export'"
        );
        $this->execute(
            "INSERT INTO tadminmenu (kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort) 
                SELECT kAdminmenueGruppe, cModulId, 'PLZ-Import', 'plz_ort_import.php', 'PLZ_ORT_IMPORT_VIEW', 55 
                FROM tadminmenugruppe 
                WHERE cName = 'Wartung'"
        );
        $this->reorderMenu(11);

        $this->execute(
            "DROP INDEX PLZ_ORT_UNIQUE ON tplz"
        );
        $this->execute(
            "CREATE INDEX PLZ_ORT_UNIQUE ON tplz (cLandISO, cPLZ, cOrt)"
        );
        $this->execute(
            "CREATE TABLE tplz_backup LIKE tplz"
        );
    }

    public function down()
    {
        $this->execute(
            "DROP TABLE IF EXISTS tplz_backup"
        );
        $this->execute(
            "DROP INDEX PLZ_ORT_UNIQUE ON tplz"
        );
        $this->execute(
            "CREATE INDEX PLZ_ORT_UNIQUE  ON tplz (cPLZ, cOrt)"
        );

        $this->execute(
            "DELETE FROM tadminmenu WHERE cURL = 'plz_ort_import.php'"
        );
        $this->execute(
            "DELETE FROM tadminrecht WHERE cRecht = 'PLZ_ORT_IMPORT_VIEW'"
        );
        $this->reorderMenu(11);
    }
}
