<?php
/**
 * Changes free gift descriptions
 *
 * @author ms
 * @created Fri, 31 Mar 2017 10:05:00 +0200
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
class Migration_20170331100500 extends Migration implements IMigration
{
    protected $author = 'ms';
    protected $description = 'changes free gift descriptions to clarify free gifts are based on the value of goods';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'freeGiftFromOrderValue',
            'Im Warenkorb können Sie aus folgenden Gratisgeschenken wählen, sofern Ihr Warenkorb über den erforderlichen Warenwert kommt.');
        $this->setLocalization('ger', 'global', 'freeGiftFromOrderValueBasket',
            'Wählen Sie ein Gratisgeschenk');
        $this->setLocalization('ger', 'errorMessages', 'freegiftsMinimum',
            'Der Gratisartikel-Mindestwarenwert ist nicht erreicht.');

        $this->setLocalization('eng', 'errorMessages', 'freegiftsMinimum',
            'Minimum value of goods not reached for this free gift.');

        $this->execute('UPDATE teinstellungenconf SET cBeschreibung="Soll die Funktion der Gratisgeschenke genutzt werden?" WHERE cWertName="sonstiges_gratisgeschenk_nutzen";');
        $this->execute('UPDATE teinstellungenconf SET cName="Anzahl Gratisgeschenke in der Übersichtsseite", cBeschreibung="Wieviele Gratisgeschenke sollen in der Übersichtsseite angezeigt werden? 0 = Alle" WHERE cWertName="sonstiges_gratisgeschenk_anzahl";');
        $this->execute('UPDATE teinstellungenconf SET cName="Sortierung der Gratisgeschenke nach" WHERE cWertName="sonstiges_gratisgeschenk_sortierung";');
    }

    public function down()
    {
        $this->setLocalization('ger', 'global', 'freeGiftFromOrderValue',
            'Im Warenkorb können Sie aus folgenden Gratisgeschenken wählen, sofern Ihr Warenkorb über den erforderlichen Bestellwert kommt.');
        $this->setLocalization('ger', 'global', 'freeGiftFromOrderValueBasket',
            'Wählen Sie ein Gratis Geschenk');
        $this->setLocalization('ger', 'errorMessages', 'freegiftsMinimum',
            'Der Gratisartikel-Mindestbestellwert ist nicht erreicht.');

        $this->setLocalization('eng', 'errorMessages', 'freegiftsMinimum',
            'Minimum shopping cart value not reached for this free gift.');

        $this->execute('UPDATE teinstellungenconf SET cBeschreibung="Solle die Funktion der Gratisgeschenke genutzt werden?" WHERE cWertName="sonstiges_gratisgeschenk_nutzen";');
        $this->execute('UPDATE teinstellungenconf SET cName="Anzahl Gratis Geschenke in der Übersichtsseite", cBeschreibung="Wieviele Gratis Geschenke sollen in der Übersichtsseite angezeigt werden? 0 = Alle" WHERE cWertName="sonstiges_gratisgeschenk_anzahl";');
        $this->execute('UPDATE teinstellungenconf SET cName="Sortierung der Gratis Geschenk Artikel nach" WHERE cWertName="sonstiges_gratisgeschenk_sortierung";');
    }
}