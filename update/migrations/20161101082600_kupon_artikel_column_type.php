<?php
/**
 * change the column type of tkupon.cArtikel to MEDIUMTEXT to store more product numbers than just about 5000
 *
 * @author dr
 * @created Mon, 01 Nov 2016 08:26:00 +0200
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
class Migration_20161101082600 extends Migration implements IMigration
{
    protected $author = 'dr';

    public function up()
    {
        $this->execute("ALTER TABLE tkupon MODIFY cArtikel MEDIUMTEXT NOT NULL");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tkupon MODIFY cArtikel TEXT NOT NULL");
    }
}
