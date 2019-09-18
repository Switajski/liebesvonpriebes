<?php
/**
 * Add news count config in news overview
 *
 * @author Danny Raufeisen
 * @created Tue, 21 Mar 2017 11:46:00 +0100
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
class Migration_20170321114600 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add news count config in news overview';

    public function up()
    {
        $this->setConfig(
            'news_anzahl_uebersicht', '10', 113, 'Anzahl News in der Übersicht', 'number', 30,
            (object)[
                'cBeschreibung' =>
                    'Wieviele News sollen standardmäßig in der Newsübersicht angezeigt werden? 0 = standard'
            ]
        );
    }

    public function down()
    {
        $this->removeConfig('news_anzahl_uebersicht');
    }
}