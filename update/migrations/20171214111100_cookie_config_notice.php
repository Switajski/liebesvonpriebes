<?php
/**
 * @author fm
 * @created Thu, 11 Dec 2017 11:11:00 +0100
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
class Migration_20171214111100 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add cookie config notice';

    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET cName = 'Cookie-Einstellungen (Achtung: nur ändern, wenn Sie genau wissen, was Sie tun!)' WHERE cName = 'Cookie-Einstellungen'");
    }

    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET cName = 'Cookie-Einstellungen' WHERE cName = 'Cookie-Einstellungen (Achtung: nur ändern, wenn Sie genau wissen, was Sie tun!)'");
    }
}
