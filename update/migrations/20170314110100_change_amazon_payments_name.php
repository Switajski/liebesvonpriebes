<?php
/**
 * Change "Amazon Payments" to "Amazon Pay"
 *
 * @author Danny Raufeisen
 * @created Tue, 14 Mar 2017 11:01:00 +0100
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
class Migration_20170314110100 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Change "Amazon Payments" to "Amazon Pay"';

    public function up()
    {
        $this->execute("UPDATE tadminmenu SET cLinkname = 'Amazon Pay' WHERE cLinkname = 'Amazon Payments'");
    }

    public function down()
    {
        $this->execute("UPDATE tadminmenu SET cLinkname = 'Amazon Payments' WHERE cLinkname = 'Amazon Pay'");
    }
}
