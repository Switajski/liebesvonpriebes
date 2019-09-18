<?php
/**
 * adds cIgnoreShippingProposal to tversandart
 *
 * @author ms
 * @created Tue, 18 Jul 2017 11:49:00 +0200
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
class Migration_20170718114900 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds cIgnoreShippingProposal to tversandart';

    public function up()
    {
        $this->execute("ALTER TABLE tversandart
            ADD COLUMN cIgnoreShippingProposal CHAR(1) NOT NULL DEFAULT 'N' AFTER cSendConfirmationMail;");

        $bar = $this->execute("UPDATE tversandartzahlungsart AS vz
                                        JOIN tzahlungsart AS z ON 
                                            vz.kZahlungsart = z.kZahlungsart
                                        JOIN tversandart AS v ON 
                                            vz.kVersandart = v.kVersandart SET v.cIgnoreShippingProposal='Y'
                                    WHERE v.cName LIKE'%Abholung%' 
                                        OR z.cTSCode = 'CASH_ON_PICKUP' 
                                            AND	(SELECT 
                                                COUNT(nvz.kZahlungsart) 
                                                FROM tversandartzahlungsart AS nvz 
                                                WHERE nvz.kVersandart = v.kVersandart) = 1;");



    }

    public function down()
    {
        $this->execute("ALTER TABLE tversandart DROP COLUMN cIgnoreShippingProposal");
    }
}
