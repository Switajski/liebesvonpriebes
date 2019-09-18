<?php
/**
 * Add language variable one-off
 *
 * @author mh
 * @created Tue, 01 Aug 2017 13:10:13 +0200
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
class Migration_20190225113620 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add language variable reply';

    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'reply', 'Antwort von');
        $this->setLocalization('eng', 'product rating', 'reply', 'Reply from');
    }

    public function down()
    {
        $this->removeLocalization('reply');
    }
}