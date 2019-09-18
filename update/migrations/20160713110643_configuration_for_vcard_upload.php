<?php
/**
 * Configuration for vCard upload
 *
 * @author root
 * @created Wed, 13 Jul 2016 11:06:43 +0200
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
class Migration_20160713110643 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->setConfig('kundenregistrierung_vcardupload', 'Y', 6, 'vCard Upload erlauben', 'selectbox', 240, (object)[
            'cBeschreibung' => 'Erlaubt dem Kunden bei der Registrierung das Hochladen einer elektronischen Visitenkarte (vCard) im vcf-Format.',
            'inputOptions'  => [
                'Y' => 'Ja',
                'N' => 'Nein',
            ]
        ]);

        $this->setLocalization('ger', 'account data', 'uploadVCard', 'vCard hochladen');
        $this->setLocalization('eng', 'account data', 'uploadVCard', 'Upload vCard');
    }

    public function down()
    {
        $this->removeConfig('kundenregistrierung_vcardupload');
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'uploadVCard'");
    }
}
