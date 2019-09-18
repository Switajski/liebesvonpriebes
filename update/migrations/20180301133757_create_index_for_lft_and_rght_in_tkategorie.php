<?php
/**
 * Create index for lft and rght in tkategorie
 *
 * @author Falk Prüfer
 * @created Thu, 01 Mar 2018 13:37:57 +0100
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
class Migration_20180301133757 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = /** @lang text */
        'Create index for lft and rght in tkategorie';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        // Check if an index for lft or rght always exists
        $idxExists = $this->fetchAll(
            "SHOW INDEX FROM tkategorie WHERE Column_name IN ('lft', 'rght')"
        );

        if (count($idxExists) > 0) {
            // If so - delete it...
            $idxDelete = [];
            foreach ($idxExists as $idx) {
                $idxDelete[] = $idx->Key_name;
            }
            foreach (array_unique($idxDelete) as $idxName) {
                $this->execute(
                    "ALTER TABLE `tkategorie` 
                        DROP INDEX `$idxName`"
                );
            }
        }

        $this->execute(
            "ALTER TABLE `tkategorie` 
                ADD INDEX `idx_tkategorie_lft_rght` (`lft` ASC, `rght` ASC);"
        );
    }

    public function down()
    {
        $this->execute(
            "ALTER TABLE `tkategorie` 
                DROP INDEX `idx_tkategorie_lft_rght`"
        );
    }
}
