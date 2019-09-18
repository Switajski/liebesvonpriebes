<?php
/**
 * create_order_info_about_downloadeable_products
 *
 * @author msc
 * @created Thu, 31 Aug 2017 16:40:30 +0200
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
class Migration_20170831164030 extends Migration implements IMigration
{
    protected $author = 'msc';
    protected $description = 'create_order_info_about_downloadeable_products';

    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'digitalProductsRegisterInfo', 'Nur angemeldete Kunden können Download-Artikel bestellen. Bitte erstellen Sie ein Kundenkonto oder melden Sie sich mit Ihren Zugangsdaten an, um mit dem Kauf fortzufahren.');
        $this->setLocalization('eng', 'checkout', 'digitalProductsRegisterInfo', 'Only registered customers can order downloadable products. Please register or log in to your account in order to continue your purchase.');
    }

    public function down()
    {
        $this->removeLocalization('digitalProductsRegisterInfo');
    }
}