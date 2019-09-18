<?php
/**
 * Create index for tartikel.kStueckliste
 *
 * @author Falk Prüfer
 * @created Tue, 16 May 2017 15:30:34 +0200
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
class Migration_20170516153034 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for tartikel.kStueckliste';

    public function up()
    {
        $this->execute("CREATE INDEX idx_tartikel_kStueckliste ON tartikel (kStueckliste)");
    }

    public function down()
    {
        $this->execute("DROP INDEX idx_tartikel_kStueckliste ON tartikel");
    }
}
