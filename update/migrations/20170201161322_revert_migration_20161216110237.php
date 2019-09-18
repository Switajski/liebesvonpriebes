<?php
/**
 * Revert Migration_20161216110237
 *
 * @author Falk Prüfer
 * @created Wed, 01 Feb 2017 16:13:22 +0100
 */

/**
 * Migration
 *
 * This migration will undo the changes from Migration_20161216110237 if it was installed trough an Beta release
 * Migration_20161216110237 has been removed in final release of 4.05.
 * The migration file 20161216110237_change_delivery_workdays_to_days.php can be deleted if exists in /update/migrations!
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
class Migration_20170201161322 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Revert Migration_20161216110237';

    public function up()
    {
        // The up-function will only be executed if Migration_20161216110237 is installed.
        $oMigration = $this->fetchOne("SELECT kMigration FROM tmigration WHERE kMigration = 20161216110237");

        if (isset($oMigration) && (int)$oMigration->kMigration === 20161216110237) {
            $this->removeConfig('addDeliveryDayOnSaturday');

            $this->setLocalization('ger', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# Werktage');
            $this->setLocalization('eng', 'global', 'deliverytimeEstimation', '#MINDELIVERYDAYS# - #MAXDELIVERYDAYS# workdays');
            $this->setLocalization('ger', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# Werktage');
            $this->setLocalization('eng', 'global', 'deliverytimeEstimationSimple', '#DELIVERYDAYS# workdays');
        }
    }

    public function down()
    {
        // This migration will undo the changes from Migration_20161216110237 in beta installations.
        // There is absolutly no reason to downgrade because Migration_20161216110237 has been removed in final release of 4.05
    }
}
