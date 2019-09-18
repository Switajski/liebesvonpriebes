<?php
/**
 * change input types to password
 *
 * @author fm
 * @created Wed, 10 Nov 2016 10:11:00 +0100
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
class Migration_20161110101100 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'pass' WHERE cWertName = 'newsletter_smtp_pass'");
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'pass' WHERE cWertName = 'caching_redis_pass'");
    }

    public function down()
    {
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'text' WHERE cWertName = 'newsletter_smtp_pass'");
        $this->execute("UPDATE teinstellungenconf SET cInputTyp = 'text' WHERE cWertName = 'caching_redis_pass'");
    }
}
