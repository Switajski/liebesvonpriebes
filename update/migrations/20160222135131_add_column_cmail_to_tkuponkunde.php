<?php
/**
 * add column cMail to tkuponkunde
 *
 * @author sh
 * @created Mon, 22 Feb 2016 13:51:31 +0100
 */

/**
 * Class Migration_20160222135131
 */
class Migration_20160222135131 extends Migration implements IMigration
{
    protected $author = 'sh';

    public function up()
    {
        $this->execute("ALTER TABLE tkuponkunde ADD `cMail` VARCHAR(255) AFTER `kKunde`");
    }

    public function down()
    {
        $this->dropColumn('tkuponkunde', 'cMail');
    }
}
