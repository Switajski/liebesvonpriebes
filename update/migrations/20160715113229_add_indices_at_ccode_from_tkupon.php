<?php
/**
 * add_indices_at_cCode_from_tkupon
 *
 * @author Mirko Schmidt
 * @created Fri, 15 Jul 2016 11:32:29 +0200
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
class Migration_20160715113229 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->execute("ALTER TABLE `tkupon` ADD INDEX(`cCode`)");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tkupon DROP INDEX cCode");
    }
}
