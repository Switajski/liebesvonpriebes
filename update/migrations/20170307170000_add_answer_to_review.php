<?php
/**
 * Add answer column to tbewertung
 *
 * @author Danny Raufeisen
 * @created Tue, 07 Mar 2017 17:00:00 +0100
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
class Migration_20170307170000 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add answer column to tbewertung';

    public function up()
    {
        $this->execute("ALTER TABLE tbewertung ADD COLUMN cAntwort TEXT AFTER dDatum");
        $this->execute("ALTER TABLE tbewertung ADD COLUMN dAntwortDatum DATE AFTER cAntwort");
    }

    public function down()
    {
        $this->dropColumn('tbewertung', 'dAntwortDatum');
        $this->dropColumn('tbewertung', 'cAntwort');
    }
}
