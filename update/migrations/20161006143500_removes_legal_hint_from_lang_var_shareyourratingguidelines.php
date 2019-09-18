<?php
/**
 * removes legal hint from language variable shareYourRatingGuidelines
 *
 * @author ms
 * @created Thu, 06 Oct 2016 14:35:00 +0200
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
class Migration_20161006143500 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'shareYourRatingGuidelines', 'Teilen Sie uns Ihre Meinung mit');
        $this->setLocalization('eng', 'product rating', 'shareYourRatingGuidelines', 'Share your experience');
    }

    public function down()
    {
        $this->setLocalization('ger', 'product rating', 'shareYourRatingGuidelines', 'Teilen Sie uns Ihre Meinung mit. Bitte beachten Sie dabei unsere Artikelbewertungs-Richtlinien');
        $this->setLocalization('eng', 'product rating', 'shareYourRatingGuidelines', 'Share your experience and please be aware about our post guidelines');
    }
}
