<?php
/**
 * Create unique index for tseo
 *
 * @author FP
 * @created Mon, 06 May 2019 15:10:38 +0200
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
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190506151038 extends Migration implements IMigration
{
    protected $author      = 'FP';
    protected $description = 'Create unique index for tseo';

    public function up()
    {
        $this->execute(
            'DELETE FROM tseo
                 WHERE cSeo IN (SELECT * FROM (
                    SELECT DISTINCT tseo1.cSeo
                    FROM tseo tseo1
                    LEFT JOIN tseo tseo2 ON tseo2.cKey = tseo1.cKey
                        AND tseo2.kKey = tseo1.kKey
                        AND tseo2.kSprache = tseo1.kSprache
                        AND tseo2.cSeo < tseo1.cSeo
                    WHERE tseo2.cSeo IS NOT NULL) AS i)'
        );
        if ($this->fetchOne("SHOW INDEX FROM tseo WHERE KEY_NAME = 'cKey'")) {
            $this->execute('DROP INDEX cKey ON tseo');
        }
        $this->execute('CREATE UNIQUE INDEX cKey ON tseo(cKey, kKey, kSprache)');
    }

    public function down()
    {
        if ($this->fetchOne("SHOW INDEX FROM tseo WHERE KEY_NAME = 'cKey'")) {
            $this->execute('DROP INDEX cKey ON tseo');
        }
        $this->execute('CREATE INDEX cKey ON tseo(cKey, kKey, kSprache)');
    }
}
