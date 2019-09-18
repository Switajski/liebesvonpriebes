<?php
/**
 * Remove page cache options
 *
 * @author fm
 * @created Wed, 07 Sep 2016 12:11:00 +0200
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
class Migration_20160907142800 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute(
            "CREATE TABLE `trevisions` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `type` VARCHAR(255) NOT NULL,
              `reference_primary` INT(11) NOT NULL,
              `reference_secondary` INT(11) DEFAULT NULL,
              `content` TEXT NOT NULL DEFAULT '',              
              `author` TEXT NOT NULL DEFAULT '',              
              `custom_table` TEXT NOT NULL DEFAULT '',              
              `custom_primary_key` TEXT NOT NULL DEFAULT '',              
              `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            )"
        );
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS `trevisions`");
    }
}
