<?php
/**
 * global_meta_title_anhaengen setting title
 *
 * @author ms
 * @created Tue, 17 Jan 2017 16:19:00 +0100
 */

/**
 * Class Migration_20160205105322
 */
class Migration_20170117161900 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET cName='Meta Title an Produktseiten anhängen' WHERE kEinstellungenConf='140';");
    }

    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET cName='Meta Title überall anhängen' WHERE kEinstellungenConf='140';");
    }
}
