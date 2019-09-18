<?php
/**
 * Add index on tnewsletterempfaenger.kKunde
 *
 * @author Falk Prüfer
 * @created Thu, 22 Dec 2016 13:50:18 +0100
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
class Migration_20161222135018 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Add index on tnewsletterempfaenger.kKunde';

    public function up()
    {
        $this->execute("ALTER TABLE tnewsletterempfaenger ADD INDEX kKunde (kKunde)");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tnewsletterempfaenger DROP INDEX kKunde");
    }
}
