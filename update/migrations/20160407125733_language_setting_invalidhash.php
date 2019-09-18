<?php
/**
 * language setting invalidHash
 *
 * @author ms
 * @created Thu, 07 Apr 2016 12:57:33 +0100
 */

/**
 * Class Migration_20160406093712
 */
class Migration_20160407125733 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'invalidHash', 'Ung&uuml;ltiger Hash &uuml;bergeben - Eventuell ist Ihr Link abgelaufen. Versuchen Sie bitte erneut, Ihr Passwort zurückzusetzen.');
    }

    public function down()
    {
    }
}
