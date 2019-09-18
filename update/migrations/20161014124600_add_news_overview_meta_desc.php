<?php
/**
 * add language variables for news overview meta description
 *
 * @author ms
 * @created Fri, 14 Oct 2016 12:46:00 +0200
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
class Migration_20161014124600 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'news', 'newsMetaDesc', 'Neuigkeiten und Aktuelles zu unserem Sortiment und unserem Onlineshop');
        $this->setLocalization('eng', 'news', 'newsMetaDesc', 'News and updates to our range and our online shop');
    }

    public function down()
    {
        $this->removeLocalization('newsMetaDesc');
    }
}
