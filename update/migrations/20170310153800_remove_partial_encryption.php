<?php
/**
 * Remove option for partial https encryption
 *
 * @author Felix Moche
 * @created Fri, 10 Mar 2017 15:38:00 +0100
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
class Migration_20170310153800 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Remove partial https encryption option';

    public function up()
    {
        $this->execute(
            "UPDATE teinstellungen 
                SET cWert = 'P' 
                WHERE kEinstellungenSektion = 1 
                AND cName = 'kaufabwicklung_ssl_nutzen'
                AND cWert = 'Z'"
        );
        $this->execute(
            "DELETE 
                FROM teinstellungenconfwerte 
                WHERE kEinstellungenConf = 192 
                AND cWert = 'Z'"
        );
    }

    public function down()
    {
        $this->execute(
            "INSERT INTO 
                teinstellungenconfwerte (`kEinstellungenConf`, `cName`, `cWert`, `nSort`)
                VALUES (192, 'Teilverschlüsselung und automatischer Wechsel zwischen http und https', 'Z', 3)"
        );
    }
}
