<?php
/**
 * Refactor category nested set level
 *
 * @author Falk Prüfer
 * @created Tue, 20 Dec 2016 10:52:42 +0100
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
class Migration_20161220105242 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Refactor category nested set level';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tkategorie
                ADD COLUMN nLevel int(10) unsigned NOT NULL DEFAULT 0 AFTER rght"
        );

        $this->execute(
            "UPDATE tkategorie
                SET nLevel = (
                    SELECT nLevel 
                    FROM tkategorielevel 
                    WHERE tkategorielevel.kKategorie = tkategorie.kKategorie)"
        );

        $this->execute(
            "DROP TABLE tkategorielevel"
        );
    }

    public function down()
    {
        $this->execute(
            "CREATE TABLE tkategorielevel (
                kKategorieLevel     int(10) unsigned NOT NULL AUTO_INCREMENT,
                kKategorie          int(10) unsigned NOT NULL,
                nLevel              int(10) unsigned NOT NULL,
                PRIMARY KEY (kKategorieLevel),
                UNIQUE KEY kKategorie (kKategorie))"
        );

        $this->execute(
            "INSERT INTO tkategorielevel (kKategorie, nLevel)
                SELECT kKategorie, nLevel FROM tkategorie"
        );

        $this->execute(
            "ALTER TABLE tkategorie
                DROP COLUMN nLevel"
        );
    }
}
