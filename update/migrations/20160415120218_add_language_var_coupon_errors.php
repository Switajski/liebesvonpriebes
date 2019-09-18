<?php
/**
 * add_language_var_coupon_errors
 *
 * @author mirko
 * @created Fri, 15 Apr 2016 12:02:18 +0200
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
class Migration_20160415120218 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'couponErr1', 'Der Kupon ist nicht aktiv.');
        $this->setLocalization('eng', 'global', 'couponErr1', 'This coupon is not actived.');
        $this->setLocalization('ger', 'global', 'couponErr2', 'Der Kupon ist nicht mehr gültig.');
        $this->setLocalization('eng', 'global', 'couponErr2', 'This coupon is not valid anymore.');
        $this->setLocalization('ger', 'global', 'couponErr3', 'Der Kupon ist zur Zeit nicht gültig.');
        $this->setLocalization('eng', 'global', 'couponErr3', 'This coupon is not valid currently.');
        $this->setLocalization('ger', 'global', 'couponErr4', 'Der Kupon-Mindestbestellwert ist nicht erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr4', 'Minimum shopping cart value not reached for this coupon.');
        $this->setLocalization('ger', 'global', 'couponErr5', 'Der Kupon ist für die aktuelle Kundengruppe ungültig.');
        $this->setLocalization('eng', 'global', 'couponErr5', 'This coupon is invalid for this customer group.');
        $this->setLocalization('ger', 'global', 'couponErr6', 'Der Kupon hat die maximal erlaubte Anzahl an Verwendungen überschritten.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'This coupon has reached the maximum usage limit.');
        $this->setLocalization('ger', 'global', 'couponErr7', 'Der Kupon ist für den aktuellen Warenkorb ungültig (gilt nur für bestimmte Artikel).');
        $this->setLocalization('eng', 'global', 'couponErr7', 'This coupon is invalid for your cart (valid only for specific article).');
        $this->setLocalization('ger', 'global', 'couponErr8', 'Der Kupon ist für den aktuellen Warenkorb ungültig (gilt nur für bestimmte Kategorien).');
        $this->setLocalization('eng', 'global', 'couponErr8', 'This coupon is invalid for your cart (valid only for specific categories).');
        $this->setLocalization('ger', 'global', 'couponErr9', 'Der Kupon ist ungültig für Ihr Kundenkonto.');
        $this->setLocalization('eng', 'global', 'couponErr9', 'This coupon is invalid for your account.');
        $this->setLocalization('ger', 'global', 'couponErr10', 'Der Kupon ist aufgrund der Lieferadresse ungültig.');
        $this->setLocalization('eng', 'global', 'couponErr10', 'This coupon is invalid for the delivery address.');
        $this->setLocalization('ger', 'global', 'couponErr99', 'Leider sind die Voraussetzungen für den Kupon nicht erfüllt.');
        $this->setLocalization('eng', 'global', 'couponErr99', 'Unfortunately, the conditions for the coupon are not met.');
    }

    public function down()
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'couponErr5';");
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'couponErr7';");
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'couponErr8';");
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'couponErr9';");
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'couponErr10';");
        $this->setLocalization('ger', 'global', 'couponErr1', 'Fehler: Kupon ist nicht aktiv.');
        $this->setLocalization('eng', 'global', 'couponErr1', 'Error: Coupon not active.');
        $this->setLocalization('ger', 'global', 'couponErr2', 'Fehler: Kupon ist nicht mehr gültig (Datum abgelaufen).');
        $this->setLocalization('eng', 'global', 'couponErr2', 'Error: Coupon is not valid anymore, "Date expired".');
        $this->setLocalization('ger', 'global', 'couponErr3', 'Fehler: Kupon ist nicht mehr gültig.');
        $this->setLocalization('eng', 'global', 'couponErr3', 'Error: Coupon is not valid anymore.');
        $this->setLocalization('ger', 'global', 'couponErr4', 'Fehler: Mindestbestellwert noch nicht erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr4', 'Error: Minimum order value not reached.');
        $this->setLocalization('ger', 'global', 'couponErr6', 'Fehler: Maximale Verwendungen erreicht.');
        $this->setLocalization('eng', 'global', 'couponErr6', 'Error: Maximum usage reached');
        $this->setLocalization('ger', 'global', 'couponErr99', 'Fehler: Unbekannter Kupon Fehler.');
        $this->setLocalization('eng', 'global', 'couponErr99', 'Error: Unspecified coupon error.');
    }
}
