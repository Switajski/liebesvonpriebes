<?php
/**
 * remove_settings_for_product_recommendation
 *
 * @author wp
 * @created Fri, 18 Mar 2016 10:19:47 +0100
 */

/**
 * Class Migration_20160318101947
 */
class Migration_20160318101947 extends Migration implements IMigration
{
    protected $author = 'wp';

    public function up()
    {
        $this->execute("DELETE FROM `teinstellungen` WHERE `cName` IN ('artikeldetails_artikelweiterempfehlen_anzeigen','artikeldetails_artikelweiterempfehlen_sperreminuten','artikeldetails_artikelweiterempfehlen_captcha')");
        $this->execute("DELETE FROM `teinstellungenconf` WHERE `kEinstellungenConf` IN (609,610,611,1471)");
        $this->execute("DELETE FROM `teinstellungenconfwerte` WHERE `kEinstellungenConf` IN (609,610,611,1471)");
    }

    public function down()
    {
        $this->execute("INSERT INTO `teinstellungen` (`kEinstellungenSektion`, `cName`, `cWert`, `cModulId`) VALUES
                        (5, 'artikeldetails_artikelweiterempfehlen_captcha', 'N', NULL),
                        (5, 'artikeldetails_artikelweiterempfehlen_anzeigen', 'P', NULL),
                        (5, 'artikeldetails_artikelweiterempfehlen_sperreminuten', '2', NULL)");

        $this->execute("INSERT INTO `teinstellungenconf` (`kEinstellungenConf`, `kEinstellungenSektion`, `cName`, `cBeschreibung`, `cWertName`, `cInputTyp`, `cModulId`, `nSort`, `nStandardAnzeigen`, `nModul`, `cConf`) VALUES
                        (609, 5, 'Artikel weiterempfehlen', '', NULL, NULL, NULL, 650, 1, 0, 'N'),
                        (610, 5, 'Artikel weiterempfehlen Formular anzeigen', 'Zeigt ein Formular an, über das ein Artikel weiterempfohlen werden kann. Ein Bekannter erhält diesen Artikel dann als Email.', 'artikeldetails_artikelweiterempfehlen_anzeigen', 'selectbox', NULL, 660, 1, 0, 'Y'),
                        (611, 5, 'Weiterempfehlungsformular sperren für X Minuten', 'Sobald ein Kunde das Kontaktformular genutzt hat, kann er frühstens nach sovielen Minuten das Formular erneut absenden.', 'artikeldetails_artikelweiterempfehlen_sperreminuten', 'number', NULL, 670, 1, 0, 'Y'),
                        (1471, 5, 'Spamschutz aktivieren2', 'Soll ein Sicherheitscode abgefragt werden, damit das Formular akzeptiert und abgesendet wird?', 'artikeldetails_artikelweiterempfehlen_captcha', 'selectbox', NULL, 695, 1, 0, 'Y')");

        $this->execute("INSERT INTO `teinstellungenconfwerte` (`kEinstellungenConf`, `cName`, `cWert`, `nSort`) VALUES
                        (610, 'Ja', 'Y', 1),
                        (610, 'Nein', 'N', 2),
                        (610, 'Ja, als PopUp', 'P', 3),
                        (1471, 'Nein', 'N', 1),
                        (1471, 'Ja', 'Y', 0)");
    }
}
