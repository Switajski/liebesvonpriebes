<?php
/**
 * adds poll error message
 *
 * @author ms
 * @created Mon, 19 Dec 2016 13:00:00 +0100
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
class Migration_20161219130000 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'messages', 'pollError', 'Bei der Auswertung ist ein Fehler aufgetreten.');
        $this->setLocalization('eng', 'messages', 'pollError', 'An error occured during validation.');
    }

    public function down()
    {
        $this->removeLocalization('pollError');
    }
}
