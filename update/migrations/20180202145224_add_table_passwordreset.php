<?php
/**
 * add_table_passwordreset
 *
 * @author mschop
 * @created Fri, 02 Feb 2018 14:52:24 +0100
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
class Migration_20180202145224 extends Migration implements IMigration
{
    protected $author = 'Martin Schophaus';
    protected $description = 'Add Table tpasswordreset';

    public function up()
    {
        $this->execute(
            "CREATE TABLE tpasswordreset(
            kKunde INT PRIMARY KEY ,
            cKey VARCHAR(255) UNIQUE,
            dExpires DATETIME
          ) ENGINE=InnoDB COLLATE utf8_unicode_ci;
          CREATE INDEX tpasswordreset_cKey ON tpasswordreset(cKey);
          ALTER TABLE tkunde DROP COLUMN cResetPasswordHash;
        ");
    }

    public function down()
    {
        $this->execute('DROP TABLE tpasswordreset');
        $this->execute('ALTER TABLE tkunde ADD COLUMN cResetPasswordHash VARCHAR(255)');
    }
}
