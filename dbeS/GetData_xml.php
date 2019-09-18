<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$xml_obj = [];
if (auth()) {
    $return = 0;
    // verfuegbarkeitsbenachrichtigungen
    $xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'] = Shop::DB()->query(
        "SELECT *
            FROM tverfuegbarkeitsbenachrichtigung
            WHERE cAbgeholt = 'N'
            LIMIT " . LIMIT_VERFUEGBARKEITSBENACHRICHTIGUNGEN, 9
    );
    $xml_obj['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] =
        count($xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung']);
    for ($i = 0; $i < $xml_obj['tverfuegbarkeitsbenachrichtigung attr']['anzahl']; $i++) {
        $xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'][$i . ' attr'] =
            buildAttributes($xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'][$i]);
        Shop::DB()->query("UPDATE tverfuegbarkeitsbenachrichtigung
            SET cAbgeholt = 'Y'
            WHERE kVerfuegbarkeitsbenachrichtigung = " . (int)$xml_obj['queueddata']['verfuegbarkeitsbenachrichtigungen']['tverfuegbarkeitsbenachrichtigung'][$i . ' attr']['kVerfuegbarkeitsbenachrichtigung'], 4);
    }

    // uploadqueue
    $xml_obj['queueddata']['uploadqueue']['tuploadqueue'] = Shop::DB()->query(
        "SELECT *
            FROM tuploadqueue
            LIMIT " . LIMIT_UPLOADQUEUE, 9
    );

    $xml_obj['tuploadqueue attr']['anzahl'] = count($xml_obj['queueddata']['uploadqueue']['tuploadqueue']);
    for ($i = 0; $i < $xml_obj['tuploadqueue attr']['anzahl']; $i++) {
        $xml_obj['queueddata']['uploadqueue']['tuploadqueue'][$i . ' attr'] = buildAttributes($xml_obj['queueddata']['uploadqueue']['tuploadqueue'][$i]);
    }
}

if ($xml_obj['tverfuegbarkeitsbenachrichtigung attr']['anzahl'] > 0 || $xml_obj['tuploadqueue attr']['anzahl'] > 0) {
    zipRedirect(time() . '.jtl', $xml_obj);
}

echo $return;
