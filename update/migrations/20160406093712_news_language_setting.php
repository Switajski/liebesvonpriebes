<?php
/**
 * news language setting
 *
 * @author ms
 * @created Wed, 06 Apr 2016 09:37:12 +0100
 */

/**
 * Class Migration_20160406093712
 */
class Migration_20160406093712 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'news', 'newsRestricted', 'Dieser Beitrag unterliegt Beschr&auml;nkungen.');
        $this->setLocalization('eng', 'news', 'newsRestricted', 'This post is subject to restrictions.');
    }

    public function down()
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 14 AND `cName` = 'newsRestricted';");
    }
}
