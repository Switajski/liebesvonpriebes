<?php
/**
 * Create index for tkategorie.nLevel
 *
 * @author Falk Prüfer
 * @created Thu, 20 Apr 2017 09:49:22 +0200
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
class Migration_20170420094922 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for tkategorie.nLevel';

    public function up()
    {
        $this->execute(
            "CREATE INDEX idx_tkategorie_nLevel ON tkategorie (nLevel)"
        );
    }

    public function down()
    {
        $this->execute(
            "DROP INDEX idx_tkategorie_nLevel ON tkategorie"
        );
    }
}
