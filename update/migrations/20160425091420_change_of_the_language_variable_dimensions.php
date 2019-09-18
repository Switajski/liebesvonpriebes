<?php
/**
 * change_of_the_language_variable_dimensions
 *
 * @author mirko
 * @created Mon, 25 Apr 2016 09:14:20 +0200
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
class Migration_20160425091420 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'dimensions', 'Abmessungen(LxBxH)');
        $this->setLocalization('eng', 'productDetails', 'dimensions', 'Dimensions(LxWxH)');
    }

    public function down()
    {
        $this->setLocalization('ger', 'productDetails', 'dimensions', 'Abmessungen');
        $this->setLocalization('eng', 'productDetails', 'dimensions', 'Dimensions');
    }
}
