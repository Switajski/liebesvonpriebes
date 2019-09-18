<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
$return  = 3;
$xml_obj = [];
if (auth()) {
    $return                                           = 0;
    $xml_obj['zahlungseingaenge']['tzahlungseingang'] = Shop::DB()->query(
        "SELECT *, date_format(dZeit, '%d.%m.%Y') AS dZeit_formatted
            FROM tzahlungseingang
            WHERE cAbgeholt = 'N'
            ORDER BY kZahlungseingang", 9
    );
    $xml_obj['zahlungseingaenge attr']['anzahl']      = count($xml_obj['zahlungseingaenge']['tzahlungseingang']);
    for ($i = 0; $i < $xml_obj['zahlungseingaenge attr']['anzahl']; $i++) {
        $xml_obj['zahlungseingaenge']['tzahlungseingang'][$i . ' attr'] =
            buildAttributes($xml_obj['zahlungseingaenge']['tzahlungseingang'][$i]);
    }
}

if ($xml_obj['zahlungseingaenge attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml_obj);
}
echo $return;
