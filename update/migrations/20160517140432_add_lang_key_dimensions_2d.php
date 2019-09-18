<?php
/**
 * add_lang_key_dimensions_2d
 *
 * @author Mirko Schmidt
 * @created Tue, 17 May 2016 14:04:32 +0200
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
class Migration_20160517140432 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'dimensions2d', 'Abmessungen (L&times;H)');
        $this->setLocalization('eng', 'productDetails', 'dimensions2d', 'Dimensions (L&times;H)');
    }

    public function down()
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'dimensions2d'");
    }
}
