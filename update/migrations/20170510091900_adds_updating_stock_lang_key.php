<?php
/**
 * adds updating stock lang key
 *
 * @author ms
 * @created Wed, 10 May 2017 09:19:00 +0200
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
class Migration_20170510091900 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'adds updating stock lang key';

    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'updatingStockInformation', 'Lagerinformationen für Variationen werden geladen');
        $this->setLocalization('eng', 'productDetails', 'updatingStockInformation', 'updating stock information');
    }

    public function down()
    {
        $this->removeLocalization('updatingStockInformation');
    }
}