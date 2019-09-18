<?php
/**
 * Add boolean mode for fulltext search
 *
 * @author fp
 * @created Wed, 14 Mar 2018 13:37:43 +0100
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
class Migration_20180314133743 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add boolean mode for fulltext search';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->execute(
            "INSERT INTO teinstellungenconfwerte (
	            SELECT teinstellungenconf.kEinstellungenConf, 'Volltextsuche (Boolean Mode)', 'B', 3
                FROM teinstellungenconf
                WHERE teinstellungenconf.cWertName = 'suche_fulltext'
            )"
        );
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->execute(
            "DELETE teinstellungenconfwerte 
                FROM teinstellungenconfwerte 
                INNER JOIN teinstellungenconf ON teinstellungenconf.kEinstellungenConf = teinstellungenconfwerte.kEinstellungenConf
                WHERE teinstellungenconf.cWertName = 'suche_fulltext'
	                AND teinstellungenconfwerte.cWert = 'B'"
        );
    }
}
