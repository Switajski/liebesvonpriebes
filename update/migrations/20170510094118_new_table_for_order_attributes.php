<?php
/**
 * New Table for order attributes
 *
 * @author Falk Prüfer
 * @created Wed, 10 May 2017 09:41:18 +0200
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
class Migration_20170510094118 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'New Table for order attributes';

    public function up()
    {
        $this->execute(
            "CREATE TABLE tbestellattribut (
                kBestellattribut INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                kBestellung      INT(10) UNSIGNED NOT NULL,
                cName            VARCHAR(255)     NOT NULL,
                cValue           TEXT                 NULL,
                PRIMARY KEY (kBestellattribut),
                UNIQUE KEY idx_kBestellung_cName_uq (kBestellung, cName)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE tbestellattribut");
    }
}
