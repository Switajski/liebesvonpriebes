<?php
/**
 * Add 'google two-factor-authentication'
 * Issue #276
 *
 * @author root
 * @created Wed, 27 Jul 2016 14:14:07 +0200
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
class Migration_20160727141407 extends Migration implements IMigration
{
    protected $author = 'cr';

    public function up()
    {
        $this->execute("ALTER TABLE tadminlogin ADD b2FAauth tinyint(1) default 0, ADD c2FAauthSecret varchar(100) default '';");
    }

    public function down()
    {
        $this->dropColumn("tadminlogin", "b2FAauth");
        $this->dropColumn("tadminlogin", "c2FAauthSecret");
    }
}
