<?php
/**
 * Increase migration content length
 *
 * @author Martin Schophaus
 * @created Wed, 14 Feb 2018 13:24:51 +0100
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
class Migration_20180214132451 extends Migration implements IMigration
{
    protected $author = 'Martin Schophaus';
    protected $description = 'Increase revisions content length';

    public function up()
    {
        $this->execute("ALTER TABLE trevisions MODIFY content LONGTEXT");
    }

    public function down()
    {
        $this->execute("ALTER TABLE trevisions MODIFY content TEXT");
    }
}