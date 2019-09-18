<?php
/**
 * add_language_variable_descriptionview
 *
 * @author Mirko Schmidt
 * @created Fri, 12 Aug 2016 11:42:10 +0200
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
class Migration_20160812114210 extends Migration implements IMigration
{
    protected $author = 'Mirko Schmidt';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'showDescription', 'Beschreibung anzeigen');
        $this->setLocalization('eng', 'global', 'showDescription', 'Show description');
    }

    public function down()
    {
        $this->removeLocalization('showDescription');
    }
}
