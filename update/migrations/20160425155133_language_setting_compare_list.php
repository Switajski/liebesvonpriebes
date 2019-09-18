<?php
/**
 * language setting compare list
 *
 * @author ms
 * @created Mon, 25 Apr 2016 15:51:33 +0200
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
class Migration_20160425155133 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'compareListNoItems', 'Sie benötigen mindestens zwei Artikel, um vergleichen zu können.');
        $this->setLocalization('eng', 'global', 'compareListNoItems', 'You need at least two products in order to be able to compare.');
    }

    public function down()
    {
        $this->setLocalization('ger', 'global', 'compareListNoItems', 'Sie haben noch keine Artikel auf Ihrer Vergleichsliste.');
        $this->setLocalization('eng', 'global', 'compareListNoItems', 'There are no items on you compare list yet.');
    }
}
