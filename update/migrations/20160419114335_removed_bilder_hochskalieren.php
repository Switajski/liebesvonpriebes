<?php
/**
 * removed bilder_hochskalieren
 *
 * @author andy
 * @created Tue, 19 Apr 2016 11:43:35 +0200
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
class Migration_20160419114335 extends Migration implements IMigration
{
    protected $author = 'andy';

    public function up()
    {
        $this->removeConfig('bilder_hochskalieren');
    }

    public function down()
    {
    }
}
