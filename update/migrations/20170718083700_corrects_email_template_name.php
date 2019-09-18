<?php
/**
 * corrects email template name
 *
 * @author ms
 * @created Tue, 18 Jul 2017 08:37:00 +0200
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
class Migration_20170718083700 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'corrects email template name';

    public function up()
    {
        $this->execute("UPDATE temailvorlage SET cName='Warenrücksendung abgeschickt' WHERE cModulId='core_jtl_rma_submitted' AND cDateiname ='rma'");
    }

    public function down()
    {
        $this->execute("UPDATE temailvorlage SET cName='Warenrücksendung abegeschickt' WHERE cModulId='core_jtl_rma_submitted' AND cDateiname ='rma'");
    }
}
