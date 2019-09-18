<?php
/**
 * Add language var "show all reviews" to reset review filter
 *
 * @author Danny Raufeisen
 * @created Fri, 27 Jan 2017 16:59:00 +0100
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
class Migration_20170127165900 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add language var "show all reviews" to reset review filter';

    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'allReviews', 'Alle Bewertungen');
        $this->setLocalization('eng', 'product rating', 'allReviews', 'All reviews');
    }

    public function down()
    {
        $this->removeLocalization('allReviews');
    }
}