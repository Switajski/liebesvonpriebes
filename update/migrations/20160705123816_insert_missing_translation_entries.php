<?php
/**
 * Insert missing translation entries
 *
 * @author root
 * @created Tue, 05 Jul 2016 12:38:16 +0200
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
class Migration_20160705123816 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->setLocalization('eng', 'global', 'invalidToken', 'Invalid securitycode');
        $this->setLocalization('eng', 'global', 'showAllProductsTaggedWith', 'View all products tagged with');
        $this->setLocalization('eng', 'messages', 'notificationNotPossible', 'Too many requests. Please wait a moment and send your notification request again.');
        $this->setLocalization('eng', 'paymentMethods', 'iclearError', 'Error communicating with iClear server');

        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 1 AND `cName` = 'payWithUos';");
    }

    public function down()
    {
        $this->setLocalization('ger', 'global', 'payWithUos', 'jetzt mit United Online Services bezahlen');

        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 1 AND `cName` = 'invalidToken' AND kSprachISO IN (SELECT kSprachISO FROM tsprachiso WHERE cISO = 'eng')");
        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 1 AND `cName` = 'showAllProductsTaggedWith' AND kSprachISO IN (SELECT kSprachISO FROM tsprachiso WHERE cISO = 'eng')");
        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 25 AND `cName` = 'notificationNotPossible' AND kSprachISO IN (SELECT kSprachISO FROM tsprachiso WHERE cISO = 'eng')");
        $this->execute("DELETE FROM `tsprachwerte` WHERE `kSprachsektion` = 29 AND `cName` = 'iclearError' AND kSprachISO IN (SELECT kSprachISO FROM tsprachiso WHERE cISO = 'eng')");
    }
}
