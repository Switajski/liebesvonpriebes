<?php
/**
 * Add language variables for birthday date
 *
 * @author Danny Raufeisen
 * @created Mon, 16 Jan 2017 14:56:38 +0100
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
class Migration_20170116145638 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add language variables for birthday date';

    public function up()
    {
        $this->setLocalization('ger', 'account data', 'birthdayFormat', 'TT.MM.JJJJ');
        $this->setLocalization('eng', 'account data', 'birthdayFormat', 'DD.MM.YYYY');
    }

    public function down()
    {
        $this->removeLocalization('birthdayFormat');
    }
}
