<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $cPost_arr
 * @return bool
 */
function pruefeEingabe($cPost_arr)
{
    $cVorname  = StringHandler::filterXSS($cPost_arr['cVorname']);
    $cNachname = StringHandler::filterXSS($cPost_arr['cNachname']);
    $cEmail    = StringHandler::filterXSS($cPost_arr['cEmail']);

    return (strlen($cVorname) > 0 && strlen($cNachname) > 0 && valid_email($cEmail));
}

/**
 * @param array $cPost_arr
 * @param array $Einstellungen
 * @return bool
 */
function setzeKwKinDB($cPost_arr, $Einstellungen)
{
    if ($Einstellungen['kundenwerbenkunden']['kwk_nutzen'] === 'Y') {
        $cVorname  = StringHandler::filterXSS($cPost_arr['cVorname']);
        $cNachname = StringHandler::filterXSS($cPost_arr['cNachname']);
        $cEmail    = StringHandler::filterXSS($cPost_arr['cEmail']);
        // Prüfe ob Email nicht schon bei einem Kunden vorhanden ist
        $oKunde = Shop::DB()->select('tkunde', 'cMail', $cEmail);

        if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
            return false;
        }
        $oKwK = new KundenwerbenKunden($cEmail);
        if ((int)$oKwK->kKundenWerbenKunden > 0) {
            return false;
        }
        // Setze in tkundenwerbenkunden
        $oKwK->kKunde       = $_SESSION['Kunde']->kKunde;
        $oKwK->cVorname     = $cVorname;
        $oKwK->cNachname    = $cNachname;
        $oKwK->cEmail       = $cEmail;
        $oKwK->nRegistriert = 0;
        $oKwK->fGuthaben    = (float)$Einstellungen['kundenwerbenkunden']['kwk_neukundenguthaben'];
        $oKwK->dErstellt    = 'now()';
        $oKwK->insertDB();
        $oKwK->sendeEmailanNeukunde();

        return true;
    }

    return false;
}

/**
 * @param int   $kKunde
 * @param float $fGuthaben
 * @return bool
 */
function gibBestandskundeGutbaben($kKunde, $fGuthaben)
{
    $kKunde = (int)$kKunde;
    if ($kKunde > 0) {
        Shop::DB()->query("
            UPDATE tkunde 
                SET fGuthaben = fGuthaben + " . (float)$fGuthaben . " 
                WHERE kKunde = " . $kKunde, 3
        );

        return true;
    }

    return false;
}
