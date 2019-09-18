<?php
/**
 * Alter table tkuponbestellung
 *
 * @author Mirko Schmidt
 * @created Thue, 16 Jan 2017 11:28:00 +0100
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
class Migration_20170116112800 extends Migration implements IMigration
{
    protected $author = 'msc';
    protected $description = 'Alter table tkuponbestellung';

    public function up()
    {
        $this->execute("ALTER TABLE `tkuponbestellung` ADD `kKunde` INT, ADD `cBestellNr` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci, ADD `fGesamtsummeBrutto` DOUBLE NOT NULL DEFAULT 0, ADD `fKuponwertBrutto` DOUBLE NOT NULL DEFAULT 0, ADD `cKuponTyp` ENUM('prozent','festpreis','versand','neukunden') CHARACTER SET latin1 COLLATE latin1_swedish_ci, ADD `dErstellt` DATETIME, ADD INDEX (`cKuponTyp`, `dErstellt`)");
        // Standard- und Versandkupons
        $this->execute("UPDATE `tkuponbestellung` AS `kbg`
                        INNER JOIN (SELECT `kpb`.`kKupon`, `bsk`.`kBestellung`, `bsk`.`kKunde`, `bsk`.`cBestellNr`, ROUND(`bsk`.`fGesamtsumme`, 2) AS `fGesamtsummeBrutto`,
                                    IF(
                                        (ROUND(`wkp`.`fPreisEinzelNetto`*(1+`wkp`.`fMwSt`/100), 2)*(-1)) > 0,
                                        (ROUND(`wkp`.`fPreisEinzelNetto`*(1+`wkp`.`fMwSt`/100), 2)*(-1)),
                                        (SELECT IFNULL(min(`va`.`fPreis`), 0) AS `fPreis`
                                            FROM `twarenkorbpos` AS `wpv`
                                            INNER JOIN `tversandartsprache` AS `vs` ON `vs`.`cName` = `wpv`.`cName`
                                            INNER JOIN `tversandart` AS `va` ON `va`.`kVersandart` = `vs`.`kVersandart` OR `va`.cName = `wpv`.`cName`
                                            WHERE `wpv`.`nPosTyp` = 2
                                                AND `wkp`.`kWarenkorb` = `wpv`.`kWarenkorb`
                                                AND `wpv`.`kWarenkorb` IN
                                                (SELECT `kWarenkorb` 
	
                                                    FROM `twarenkorbpos` 
                                                    WHERE `nPosTyp` = 3
                                                    AND `fPreisEinzelNetto` = 0))
                                        ) AS `fKuponwertBrutto`,
                                    IF(`kp`.`cKuponTyp` = 'neukundenkupon', 'neukunden', IF(IFNULL(`kp`.`cWertTyp`,'festpreis') != '', IFNULL(`kp`.`cWertTyp`,'festpreis'), 'versand')) AS `cKuponTyp`,
                                    `bsk`.`dErstellt`
                                    FROM `tbestellung` AS `bsk`
                                    INNER JOIN `twarenkorbpos` AS `wkp` ON `bsk`.`kWarenkorb` = `wkp`.`kWarenkorb`
                                    INNER JOIN `tkuponbestellung` AS `kpb` ON `kpb`.`kBestellung` = `bsk`.`kBestellung`
                                    INNER JOIN `tkupon` AS `kp` ON `kpb`.`kKupon` = `kp`.`kKupon`
                                    WHERE  `wkp`.`nPosTyp` = 3) AS `mergetable` ON `mergetable`.`kBestellung` = `kbg`.`kBestellung` AND  `mergetable`.`kKupon` = `kbg`.`kKupon`
                        SET
                            `kbg`.`kKunde` = `mergetable`.`kKunde`,
                            `kbg`.`cBestellNr` = `mergetable`.`cBestellNr`,
                            `kbg`.`fGesamtsummeBrutto` = `mergetable`.`fGesamtsummeBrutto`,
                            `kbg`.`fKuponwertBrutto` = `mergetable`.`fKuponwertBrutto`,
                            `kbg`.`cKuponTyp` = `mergetable`.`cKuponTyp`,
                            `kbg`.`dErstellt` = `mergetable`.`dErstellt`");
        // Neukundenkupons
        $this->execute("UPDATE `tkuponbestellung` AS `kbg`
                        INNER JOIN (SELECT `kpb`.`kKupon`, `bsk`.`kBestellung`, `bsk`.`kKunde`, `bsk`.`cBestellNr`, ROUND(`bsk`.`fGesamtsumme`, 2) AS `fGesamtsummeBrutto`,
                                    (ROUND(`wkp`.`fPreisEinzelNetto`*(1+`wkp`.`fMwSt`/100), 2)*(-1)) AS `fKuponwertBrutto`,
                                    IF(`kp`.`cKuponTyp` = 'neukundenkupon', 'neukunden', IF(IFNULL(`kp`.`cWertTyp`,'festpreis') != '', IFNULL(`kp`.`cWertTyp`,'festpreis'), 'versand')) AS `cKuponTyp`,
                                    `bsk`.`dErstellt`
                                    FROM `tbestellung` AS `bsk`
                                    INNER JOIN `twarenkorbpos` AS `wkp` ON `bsk`.`kWarenkorb` = `wkp`.`kWarenkorb`
                                    INNER JOIN `tkuponbestellung` AS `kpb` ON `kpb`.`kBestellung` = `bsk`.`kBestellung`
                                    INNER JOIN `tkupon` AS `kp` ON `kpb`.`kKupon` = `kp`.`kKupon`
                                    WHERE  `wkp`.`nPosTyp` = 7) AS `mergetable` ON `mergetable`.`kBestellung` = `kbg`.`kBestellung` AND  `mergetable`.`kKupon` = `kbg`.`kKupon`
                        SET
                            `kbg`.`kKunde` = `mergetable`.`kKunde`,
                            `kbg`.`cBestellNr` = `mergetable`.`cBestellNr`,
                            `kbg`.`fGesamtsummeBrutto` = `mergetable`.`fGesamtsummeBrutto`,
                            `kbg`.`fKuponwertBrutto` = `mergetable`.`fKuponwertBrutto`,
                            `kbg`.`cKuponTyp` = `mergetable`.`cKuponTyp`,
                            `kbg`.`dErstellt` = `mergetable`.`dErstellt`");
        // Restlichen Kupons die bisher noch nicht gefüllt wurden
        $this->execute("UPDATE `tkuponbestellung` AS `kbg`
                        INNER JOIN (SELECT DISTINCT `kpb`.`kKupon`, `bsk`.`kBestellung`, `bsk`.`kKunde`, `bsk`.`cBestellNr`, ROUND(`bsk`.`fGesamtsumme`, 2) AS `fGesamtsummeBrutto`,
                                    (SELECT `fWert`
                                        FROM `tkupon`
                                        WHERE `kKupon` =
                                            (SELECT `kKupon`
                                            FROM `tkuponbestellung`
                                            WHERE `tkuponbestellung`.`kBestellung` = `bsk`.`kBestellung`
                                                AND `kKupon` IN
                                                    (SELECT `kKupon`
                                                    FROM `tkupon`
                                                    WHERE `kKupon` = `tkuponbestellung`.`kKupon`
                                                        AND `cWertTyp` = 'prozent'))) AS `fWert`,
                                    IFNULL(ROUND((SELECT SUM(((`twarenkorbpos`.`fPreis`+(`twarenkorbpos`.`fPreis`/100*`twarenkorbpos`.`fMwSt`))/(100-`fWert`)*`fWert`)*(-`twarenkorbpos`.`nAnzahl`))
                                        FROM `twarenkorbpos`
                                        WHERE `twarenkorbpos`.`kWarenkorb` = `bsk`.`kWarenkorb`
                                            AND IF(LOCATE(';'+`twarenkorbpos`.`cArtNr`+';',
                                                (SELECT `cArtikel`
                                                    FROM `tkupon`
                                                    WHERE `kKupon` =
                                                        (SELECT `kKupon`
                                                            FROM `tkuponbestellung`
                                                            WHERE `tkuponbestellung`.`kBestellung` = `bsk`.`kBestellung`)))>0,1,0)=1
                                            AND `twarenkorbpos`.`kArtikel` != 0), 2)*(-1), 0) AS `fKuponwertBrutto`, `kp`.`cWertTyp` AS `cKuponTyp`, `bsk`.`dErstellt`
                                    FROM `tbestellung` AS `bsk`
                                    INNER JOIN `twarenkorbpos` AS `wkp` ON `bsk`.`kWarenkorb` = `wkp`.`kWarenkorb`
                                    INNER JOIN `tkuponbestellung` AS `kpb` ON `kpb`.`kKunde` IS NULL AND `kpb`.`kBestellung` = `bsk`.`kBestellung`
                                    INNER JOIN `tkupon` AS `kp` ON `kpb`.`kKupon` = `kp`.`kKupon`) AS `mergetable` ON `mergetable`.`kBestellung` = `kbg`.`kBestellung` AND  `mergetable`.`kKupon` = `kbg`.`kKupon`
                        SET
                            `kbg`.`kKunde` = `mergetable`.`kKunde`,
                            `kbg`.`cBestellNr` = `mergetable`.`cBestellNr`,
                            `kbg`.`fGesamtsummeBrutto` = `mergetable`.`fGesamtsummeBrutto`,
                            `kbg`.`fKuponwertBrutto` = `mergetable`.`fKuponwertBrutto`,
                            `kbg`.`cKuponTyp` = `mergetable`.`cKuponTyp`,
                            `kbg`.`dErstellt` = `mergetable`.`dErstellt`");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tkuponbestellung` DROP `kKunde`, DROP `cBestellNr`, DROP `fGesamtsummeBrutto`, DROP `fKuponwertBrutto`, DROP `cKuponTyp`, DROP `dErstellt`");
    }
}
