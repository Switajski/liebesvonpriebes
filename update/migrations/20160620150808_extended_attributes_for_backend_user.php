<?php
/**
 * Extended attributes for backend user
 *
 * @author JTL-Software-GmbH
 * @created Mon, 20 Jun 2016 15:08:08 +0200
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
class Migration_20160620150808 extends Migration implements IMigration
{
    protected $author = 'fp';

    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tadminloginattribut` (
                `kAttribut`    INT          NOT NULL AUTO_INCREMENT,
                `kAdminlogin`  INT          NOT NULL,
                `cName`        VARCHAR(45)  NOT NULL,
                `cAttribValue` VARCHAR(512) NOT NULL DEFAULT '',
                `cAttribText`  TEXT             NULL,
                PRIMARY KEY (`kAttribut`),
                UNIQUE INDEX `cName_UNIQUE` (`kAdminlogin`, `cName`)) 
                ENGINE = MyISAM  DEFAULT CHARSET=latin1"
        );

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tcontentauthor` (
                `kContentAuthor`  INT          NOT NULL AUTO_INCREMENT,
                `cRealm`          VARCHAR(45)  NOT NULL,
                `kAdminlogin`     INT          NOT NULL,
                `kContentId`      INT          NOT NULL,
                PRIMARY KEY (`kContentAuthor`),
                UNIQUE INDEX `cRealm_UNIQUE` (`cRealm`, `kContentId`)) 
                ENGINE = MyISAM  DEFAULT CHARSET=latin1"
        );
    }

    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tcontentauthor`');
        $this->execute('DROP TABLE IF EXISTS `tadminloginattribut`');
    }
}
