<?php
/**
 * add option for xselling show parent
 *
 * @author Falk Prüfer
 * @created Tue, 14 Jun 2016 15:25:25 +0200
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
class Migration_20160614152525 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->setConfig('artikeldetails_xselling_kauf_parent', 'N', CONF_ARTIKELDETAILS, 'Immer Vaterartikel anzeigen', 'selectbox', 230, (object)[
            'cBeschreibung' => 'Es werden immer die zugeh&ouml;rigen Vaterartikel angezeigt, auch wenn tats&auml;chlich Kindartikel gekauft wurden.',
            'inputOptions'  => [
                'Y' => 'Ja',
                'N' => 'Nein',
            ],
        ]);
    }

    public function down()
    {
        $this->removeConfig('artikeldetails_xselling_kauf_parent');
    }
}
