<?php
/**
 * Persistent deliverytime in orders
 *
 * @author root
 * @created Wed, 10 Aug 2016 10:20:32 +0200
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
class Migration_20160810102032 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->execute(
            "ALTER TABLE tbestellung 
                ADD COLUMN nLongestMinDelivery INT NOT NULL DEFAULT 0 AFTER cVersandInfo,
                ADD COLUMN nLongestMaxDelivery INT NOT NULL DEFAULT 0 AFTER nLongestMinDelivery"
        );
        $this->execute(
            "ALTER TABLE twarenkorbpos 
                ADD COLUMN nLongestMinDelivery INT NOT NULL DEFAULT 0,
                ADD COLUMN nLongestMaxDelivery INT NOT NULL DEFAULT 0 AFTER nLongestMinDelivery"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE tbestellung 
                DROP COLUMN nLongestMinDelivery,
                DROP COLUMN nLongestMaxDelivery"
        );
        $this->execute(
            "ALTER TABLE twarenkorbpos 
                DROP COLUMN nLongestMinDelivery,
                DROP COLUMN nLongestMaxDelivery"
        );
    }
}
