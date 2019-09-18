<?php
/**
 * Add cUserAgent to tBesucher
 *
 * @author Falk Prüfer
 * @created Tue, 04 Jul 2017 13:37:17 +0200
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
class Migration_20170704133717 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Add cUserAgent to tBesucher';

    public function up()
    {
        $this->execute("ALTER TABLE tbesucher ADD COLUMN cUserAgent VARCHAR(512) NULL AFTER cReferer");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tbesucher DROP COLUMN cUserAgent");
    }
}
