<?php
/**
 * fix some language variable problems
 *
 * @author msc
 * @created Thu, 22 Sep 2016 14:28:00 +0200
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
class Migration_20160922142800 extends Migration implements IMigration
{
    protected $author = 'msc';

    public function up()
    {
        $this->setLocalization('ger','checkout','noShippingMethodsAvailable','Es steht keine Versandart für Ihre Bestellung zur Verfügung. Bitte kontaktieren Sie uns direkt, um diese Bestellung abzuwickeln.');
        $this->setLocalization('ger','messages','wishlistDelAll','Alle Artikel auf Ihrem Wunschzettel wurden gelöscht.');
        $this->setLocalization('ger','errorMessages','newsletterNoactive','Fehler: Ihr Freischaltcode wurde nicht gefunden.');
        $this->setLocalization('ger','global','incorrectEmailPlz','Es existiert kein Kunde mit angegebener E-Mail-Adresse und PLZ. Bitte versuchen Sie es noch einmal.');
        $this->setLocalization('ger','global','incorrectEmail','Es existiert kein Kunde mit der angegebenen E-Mail-Adresse. Bitte versuchen Sie es noch einmal.');
    }

    public function down()
    {
        $this->setLocalization('ger','checkout','noShippingMethodsAvailable','Es steht keine Versandart für Ihre Bestellung zur Verfügung. Bitte kontakieren Sie uns direkt, um diese Bestellung abzuwickeln.');
        $this->setLocalization('ger','messages','wishlistDelAll','Alle Artikel auf Ihrer Wunschzettel wurden gelöscht.');
        $this->setLocalization('ger','errorMessages','newsletterNoactive','Fehler: Ihre Freischaltcode wurde nicht gefunden.');
        $this->setLocalization('ger','global','incorrectEmailPlz','Es existiert kein Kunde mit angegebener E-Mail-Adresse und PLZ. Bitte versuchen Sie es nocheinmal.');
        $this->setLocalization('ger','global','incorrectEmail','Es existiert kein Kunde mit der angegebenen E-Mail-Adresse. Bitte versuchen Sie es nocheinmal.');
    }
}
