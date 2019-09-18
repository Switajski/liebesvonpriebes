<?php
/**
 * Add localized message for mutating basket
 *
 * @author root
 * @created Tue, 23 Aug 2016 14:59:25 +0200
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
class Migration_20160823145925 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'yourbasketismutating', 'Ihr Warenkorb wurde aufgrund von Preis- oder Lagerbestandsänderungen aktualisiert. Bitte prüfen Sie die Warenkorbpositionen.');
        $this->setLocalization('eng', 'checkout', 'yourbasketismutating', 'Your shopping cart has been updated due to price or stock changes. Please check your order items.');
    }

    public function down()
    {
        $this->execute("DELETE FROM tsprachwerte WHERE cName = 'yourbasketismutating'");
    }
}
