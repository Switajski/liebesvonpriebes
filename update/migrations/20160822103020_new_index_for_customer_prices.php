<?php
/**
 * New index for customer prices
 *
 * @author root
 * @created Mon, 22 Aug 2016 10:30:20 +0200
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
 */
class Migration_20160822103020 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->execute(
            "CREATE INDEX idx_tpreis_kKunde ON tpreis (kKunde)"
        );
    }

    public function down()
    {
        $this->execute(
            "DROP INDEX idx_tpreis_kKunde ON tpreis"
        );
    }
}
