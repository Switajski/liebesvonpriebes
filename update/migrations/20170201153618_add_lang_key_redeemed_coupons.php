<?php
/**
 * add lang key redeemed coupons
 *
 * @author Mirko Schmidt
 * @created Wed, 01 Feb 2017 15:36:18 +0100
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
class Migration_20170201153618 extends Migration implements IMigration
{
    protected $author = 'Mirko Schmidt';
    protected $description = 'add lang key redeemed coupons';

    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'currentCoupon', 'Bereits eingelöster Kupon: ');
        $this->setLocalization('eng', 'checkout', 'currentCoupon', 'Redeemed coupon: ');
        $this->setLocalization('ger', 'checkout', 'discountForArticle', 'gültig für: ');
        $this->setLocalization('eng', 'checkout', 'discountForArticle', 'applied to: ');
    }

    public function down()
    {
        $this->removeLocalization('currentCoupon');
        $this->removeLocalization('discountForArticle');
    }
}