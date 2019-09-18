<?php
/**
 * execute migration 20160415120218
 *
 * @author Mirko Schmidt
 * @created Wed, 18 Jan 2017 16:51:03 +0100
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
class Migration_20170118165103 extends Migration implements IMigration
{
    protected $author = 'msc';
    protected $description = 'Execute migration 20160415120218 a second time.';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'couponErr2', 'Der Kupon ist nicht mehr gültig.');
        $this->setLocalization('ger', 'global', 'couponErr3', 'Der Kupon ist zur Zeit nicht gültig.');
        $this->setLocalization('ger', 'global', 'couponErr5', 'Der Kupon ist für die aktuelle Kundengruppe ungültig.');
        $this->setLocalization('ger', 'global', 'couponErr6', 'Der Kupon hat die maximal erlaubte Anzahl an Verwendungen überschritten.');
        $this->setLocalization('ger', 'global', 'couponErr7', 'Der Kupon ist für den aktuellen Warenkorb ungültig (gilt nur für bestimmte Artikel).');
        $this->setLocalization('ger', 'global', 'couponErr8', 'Der Kupon ist für den aktuellen Warenkorb ungültig (gilt nur für bestimmte Kategorien).');
        $this->setLocalization('ger', 'global', 'couponErr9', 'Der Kupon ist ungültig für Ihr Kundenkonto.');
        $this->setLocalization('ger', 'global', 'couponErr10', 'Der Kupon ist aufgrund der Lieferadresse ungültig.');
        $this->setLocalization('ger', 'global', 'couponErr99', 'Leider sind die Voraussetzungen für den Kupon nicht erfüllt.');
    }

    public function down()
    {
        
    }
}