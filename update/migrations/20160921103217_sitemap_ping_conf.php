<?php
/**
 * Add option to switch sitemap ping to Google and Bing on or off
 *
 * @author dr
 * @created Wed, 21 Sep 2016 10:32:17 +0200
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
class Migration_20160921103217 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add option to switch sitemap ping to Google and Bing on or off';

    public function up()
    {
        $this->setConfig('sitemap_google_ping', 'N', CONF_SITEMAP, 'Sitemap an Google und Bing &uuml;bermitteln nach Export',
            'selectbox', 180, (object)[
                'cBeschreibung' => 'Soll nach dem erfolgreichen Export der sitemap.xml und der sitemap_index.xml ein ' .
                    'Ping an Google und Bing durchgef&uuml;hrt werden, so dass die Website schnellstm&ouml;glich ' .
                    'gecrawlt wird?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ]
            ]
        );
    }

    public function down()
    {
        $this->removeConfig('sitemap_google_ping');
    }
}
