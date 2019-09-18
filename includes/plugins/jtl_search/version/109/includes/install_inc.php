<?php
Shop::DB()->query('CREATE TABLE IF NOT EXISTS `tjtlsearchexportqueue` (`kExportqueue` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`nLimitN` INT( 10 ) UNSIGNED NOT NULL ,`nLimitM` INT( 10 ) UNSIGNED NOT NULL ,`nExportMethod` INT( 10 ) UNSIGNED NOT NULL ,`bFinished` BOOLEAN NOT NULL ,`bLocked` BOOLEAN NOT NULL ,`dStartTime` TIMESTAMP NOT NULL ,`dLastRun` TIMESTAMP NOT NULL ) ENGINE = MYISAM DEFAULT CHARSET=latin1;', 3);
Shop::DB()->query('CREATE TABLE IF NOT EXISTS `tjtlsearchserverdata` (`kId` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,`cKey` VARCHAR( 255 ) NOT NULL ,`cValue` VARCHAR( 255 ) NOT NULL) ENGINE = MYISAM DEFAULT CHARSET=latin1;', 3);
Shop::DB()->query("CREATE TABLE IF NOT EXISTS `tjtlsearchdeltaexport` (`kId` int(10) NOT NULL, `eDocumentType` enum('product','manufacturer','category') NOT NULL, `bDelete` tinyint(4) DEFAULT '0', `dLastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`kId`,`eDocumentType`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;", 3);
Shop::DB()->query("CREATE TABLE IF NOT EXISTS `tjtlsearchexportlanguage` ( `kExportLanguage` int(10) unsigned NOT NULL AUTO_INCREMENT, `cISO` varchar(3) NOT NULL, PRIMARY KEY (`kExportLanguage`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;", 3);
Shop::DB()->query("INSERT INTO tplugineinstellungen (`kPlugin` , `cName` ,`cWert`) VALUES ({$oPlugin->kPlugin}, 'jtlsearch_installed_{$oPlugin->nVersion}', '1'), ({$oPlugin->kPlugin}, 'jtlsearch_suggest_align', 'left')", 3);
Shop::DB()->query("DELETE FROM tcron WHERE cJobArt = 'JTLSearchExport'", 3);

$oSearchLanguage_arr = Shop::DB()->query("SELECT count(*) AS nCount FROM tjtlsearchexportlanguage", 1);
if (!isset($oSearchLanguage_arr->nCount) || $oSearchLanguage_arr->nCount == 0) {
    $oLanguage_arr = Shop::DB()->query("SELECT cISO FROM tsprache ORDER BY cNameDeutsch", 2);
    if (is_array($oLanguage_arr)) {
        $bFirst = true;
        foreach ($oLanguage_arr as $oLanguage) {
            if ($bFirst === true) {
                $cLanguageQuery = "INSERT INTO tjtlsearchexportlanguage (`cISO`) VALUES ('{$oLanguage->cISO}')";
                $bFirst         = false;
            } else {
                $cLanguageQuery .= ", ('{$oLanguage->cISO}')";
            }
        }
        if ($bFirst === false) {
            Shop::DB()->query("TRUNCATE TABLE tjtlsearchexportlanguage", 3);
            Shop::DB()->query($cLanguageQuery, 3);
        }
    }
}
