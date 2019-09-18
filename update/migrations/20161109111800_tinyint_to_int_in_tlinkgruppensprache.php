<?php
/**
 * change the column type of tlinkgruppensprache.kLinkgruppe to INT
 *
 * @author ms
 * @created Tue, 09 Nov 2016 11:18:00 +0100
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
class Migration_20161109111800 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE tlinkgruppesprache CHANGE COLUMN kLinkgruppe kLinkgruppe INT UNSIGNED NOT NULL DEFAULT '0';");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tlinkgruppesprache CHANGE COLUMN kLinkgruppe kLinkgruppe TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';");
    }
}
