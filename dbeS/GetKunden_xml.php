<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
$return  = 3;
$xml_obj = [];

if (auth()) {
    $return                      = 0;
    $xml_obj['kunden']['tkunde'] = Shop::DB()->query("
        SELECT kKunde, kKundengruppe, kSprache, cKundenNr, cPasswort, cAnrede, cTitel, cVorname,
            cNachname, cFirma, cStrasse, cHausnummer, cAdressZusatz, cPLZ, cOrt, cBundesland, cLand, cTel,
            cMobil, cFax, cMail, cUSTID, cWWW, fGuthaben, cNewsletter, dGeburtstag, fRabatt,
            cHerkunft, dErstellt, dVeraendert, cAktiv, cAbgeholt,
            date_format(dGeburtstag, '%d.%m.%Y') AS dGeburtstag_formatted, nRegistriert, cZusatz
            FROM tkunde
            WHERE cAbgeholt = 'N'
            ORDER BY kKunde LIMIT " . LIMIT_KUNDEN, 9
    );
    if (is_array($xml_obj['kunden']['tkunde']) && count($xml_obj['kunden']['tkunde']) > 0) {
        $xml_obj['kunden attr']['anzahl'] = count($xml_obj['kunden']['tkunde']);
        for ($i = 0; $i < $xml_obj['kunden attr']['anzahl']; ++$i) {
            $xml_obj['kunden']['tkunde'][$i]['cAnrede']   = mappeKundenanrede(
                $xml_obj['kunden']['tkunde'][$i]['cAnrede'],
                $xml_obj['kunden']['tkunde'][$i]['kSprache']
            );
            $xml_obj['kunden']['tkunde'][$i]['cNachname'] = trim(entschluesselXTEA($xml_obj['kunden']['tkunde'][$i]['cNachname']));
            $xml_obj['kunden']['tkunde'][$i]['cFirma']    = trim(entschluesselXTEA($xml_obj['kunden']['tkunde'][$i]['cFirma']));
            $xml_obj['kunden']['tkunde'][$i]['cStrasse']  = trim(entschluesselXTEA($xml_obj['kunden']['tkunde'][$i]['cStrasse']));

            //Strasse und Hausnummer zusammenfuehren
            $xml_obj['kunden']['tkunde'][$i]['cStrasse'] .= ' ' . trim($xml_obj['kunden']['tkunde'][$i]['cHausnummer']);
            unset($xml_obj['kunden']['tkunde'][$i]['cHausnummer'], $xml_obj['kunden']['tkunde'][$i]['cPasswort']);
            $xml_obj['kunden']['tkunde'][$i . ' attr'] = buildAttributes($xml_obj['kunden']['tkunde'][$i]);
            $cZusatz                                   = $xml_obj['kunden']['tkunde'][$i]['cZusatz'];
            unset($xml_obj['kunden']['tkunde'][$i]['cZusatz']);
            $xml_obj['kunden']['tkunde'][$i]['cZusatz']         = trim(entschluesselXTEA($cZusatz));
            $xml_obj['kunden']['tkunde'][$i]['tkundenattribut'] = Shop::DB()->query("
                SELECT * 
                    FROM tkundenattribut 
                    WHERE kKunde = " . (int)$xml_obj['kunden']['tkunde'][$i . ' attr']['kKunde'], 9
            );
            $kundenattribute_anz                                =
                count($xml_obj['kunden']['tkunde'][$i]['tkundenattribut']);
            for ($o = 0; $o < $kundenattribute_anz; $o++) {
                $xml_obj['kunden']['tkunde'][$i]['tkundenattribut'][$o . ' attr'] =
                    buildAttributes($xml_obj['kunden']['tkunde'][$i]['tkundenattribut'][$o]);
            }
        }
    }
}

if (isset($xml_obj['kunden attr']['anzahl']) && $xml_obj['kunden attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml_obj);
}
echo $return;
