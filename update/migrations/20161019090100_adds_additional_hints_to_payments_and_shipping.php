<?php
/**
 * adds additional hints to payments and shipping
 *
 * @author ms
 * @created Wed, 19 Oct 2016 09:01:00 +0200
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
class Migration_20161019090100 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute('ALTER TABLE tversandartsprache ADD COLUMN cHinweistextShop TEXT NULL DEFAULT NULL AFTER cHinweistext;');
        $this->execute('UPDATE tversandartsprache SET cHinweistextShop = cHinweistext;');

        $this->execute('ALTER TABLE tzahlungsartsprache ADD COLUMN cHinweisTextShop TEXT NULL DEFAULT NULL AFTER cHinweisText;');
        $this->execute('UPDATE tzahlungsartsprache SET cHinweisTextShop = cHinweisText;');
    }

    public function down()
    {
        $this->dropColumn('tversandartsprache', 'cHinweistextShop');

        $this->dropColumn('tzahlungsartsprache', 'cHinweistextShop');
    }
}
