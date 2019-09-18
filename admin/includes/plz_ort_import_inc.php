<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

defined('PLZIMPORT_HOST') || define('PLZIMPORT_HOST', 'www.fa-technik.adfc.de');
defined('PLZIMPORT_URL') || define('PLZIMPORT_URL', 'http://' . PLZIMPORT_HOST . '/code/opengeodb/');
defined('PLZIMPORT_ISO_REGEX') || define('PLZIMPORT_ISO_REGEX', '/([A-Z]{2})\.tab/');
defined('PLZIMPORT_REGEX') || define('PLZIMPORT_REGEX',
    '/<td><a href="([A-Z]{2}\.tab)">([A-Z]{2})\.tab<\/a><\/td><td[^>]*>([0-9]{2}\-[A-Za-z]{3}\-[0-9]{4}[0-9: ]+?) *<\/td><td[^>]*> *([0-9MK\.]+)<\/td>/');

/**
 * @return array
 */
function plzimportGetPLZOrt()
{
    $plzOrt_arr = Shop::DB()->query(
        "SELECT tplz.cLandISO, tland.cDeutsch, tland.cKontinent, count(tplz.kPLZ) AS nPLZOrte, backup.nBackup
            FROM tplz
            INNER JOIN tland ON tland.cISO = tplz.cLandISO
            LEFT JOIN (
	            SELECT tplz_backup.cLandISO, count(tplz_backup.kPLZ) AS nBackup
                FROM tplz_backup
                GROUP BY tplz_backup.cLandISO
            ) AS backup ON backup.cLandISO = tplz.cLandISO
            GROUP BY tplz.cLandISO, tland.cDeutsch, tland.cKontinent
            ORDER BY tplz.cLandISO", 2
    );

    foreach ($plzOrt_arr as $key => $oPLZOrt) {
        $fName = PFAD_UPLOADS . $oPLZOrt->cLandISO . '.tab';

        if (is_file($fName)) {
            $plzOrt_arr[$key]->cImportFile = $oPLZOrt->cLandISO . '.tab';
        }
    }

    return $plzOrt_arr;
}

/**
 * @param string $target
 * @param array  $sessData
 * @param object $result
 * @return void
 */
function plzimportDoImport($target, array $sessData, $result)
{
    $sessData['status'] = 'Importiere Daten aus ' . $target;
    $runtime            = (int)ini_get('max_execution_time');
    $endTime            = time() + $runtime - 5; // 5 sek. Reserve
    $fHandle            = fopen(PFAD_UPLOADS . $target, 'r');
    $fLength            = filesize(PFAD_UPLOADS . $target);
    $read               = 0;
    $oPLZOrt            = (object)[
        'cPLZ'     => '',
        'cOrt'     => '',
        'cLandISO' => 'IMP',
    ];

    if ($fHandle === false) {
        $result->type    = 'danger';
        $result->message = 'Importdatei für ' . $target . ' kann nicht gelesen werden!';

        return;
    }

    plzimportWriteSession('Import', $sessData);

    if (preg_match(PLZIMPORT_ISO_REGEX, $target, $hits)) {
        $isoLand = $hits[1];

        if (isset($sessData['currentPos'])) {
            // Import wird partiell fortgesetzt
            $data = '';
            $read = $sessData['currentPos'];
            fseek($fHandle, $sessData['currentPos']);
        } else {
            Shop::DB()->delete('tplz', 'cLandISO', 'IMP');
            // Erste Zeile nur Headerinformationen
            $data = fgetcsv($fHandle, 0, "\t");
        }

        while (!feof($fHandle)) {
            $read += strlen(implode(',', $data));
            $data = fgetcsv($fHandle, 0, "\t");

            if (isset($data[13]) && in_array($data[13], [6, 8])) {
                $plz_arr       = explode(',', $data[7]);
                $oPLZOrt->cOrt = utf8_decode($data[3]);

                foreach ($plz_arr as $plz) {
                    $oPLZOrt->cPLZ = $plz;

                    if (!empty($oPLZOrt->cPLZ) && !empty($oPLZOrt->cOrt)) {
                        Shop::DB()->insert('tplz', $oPLZOrt);
                    }
                }

                if ($fLength != 0) {
                    $sessData['step'] = 50 + round(40 / $fLength * $read);
                    plzimportWriteSession('Import', $sessData);
                }

                if ($runtime > 0 && time() >= $endTime) {
                    // max_execution_time erreicht - restart
                    $sessData['currentPos'] = ftell($fHandle);
                    plzimportWriteSession('Import', $sessData);
                    fclose($fHandle);

                    $cRedirectUrl = Shop::getURL() . '/' . PFAD_ADMIN . 'io.php?io=' .
                        urlencode(
                            json_encode(
                                [
                                    'name'   => 'plzimportActionDoImport',
                                    'params' => [$target, 'import', $sessData['step']]
                                ]
                            )
                        ) . '&token=' . StringHandler::filterXSS($_REQUEST['jtl_token']);
                    header('Location: ' . $cRedirectUrl);
                    exit;
                }
            }
        }

        $sessData['step']   = 90;
        $sessData['status'] = 'Erstelle Backup von ' . $isoLand . '...';
        plzimportWriteSession('Import', $sessData);

        Shop::DB()->delete('tplz_backup', 'cLandISO', $isoLand);
        Shop::DB()->queryPrepared("INSERT INTO tplz_backup SELECT * FROM tplz WHERE cLandISO = :isoCode", ['isoCode' => $isoLand],
            3);
        Shop::DB()->delete('tplz', 'cLandISO', $isoLand);

        $sessData['step']   = 95;
        $sessData['status'] = 'Aktualisiere ' . $isoLand . ' in Datenbank...';
        plzimportWriteSession('Import', $sessData);

        Shop::DB()->update('tplz', 'cLandISO', 'IMP', (object)[
            'cLandISO' => $isoLand,
        ]);

        $result->type    = 'success';
        $result->message = 'Import erfolgreich!';
    } else {
        $result->type    = 'danger';
        $result->message = 'Falscher Parameter angegeben!';
    }

    fclose($fHandle);
    unlink(PFAD_UPLOADS . $target);

    $sessData['step'] = 100;
    plzimportWriteSession('Import', $sessData);
}

/**
 * @param string $target
 * @param array  $sessData
 * @param object $result
 * @return void
 */
function plzimportDoDownload($target, array $sessData, $result)
{
    $sessData['status'] = 'Download von ' . $target;
    $runtime            = (int)ini_get('max_execution_time');
    $endTime            = time() + $runtime - 5; // 5 sek. Reserve
    $partSize           = 8 * 1024; // 8 KBytes
    $ioFile             = PLZIMPORT_URL . $target;
    $ioHandle           = fsockopen(PLZIMPORT_HOST, 80, $errNo, $errStr);
    $fHandle            = fopen(PFAD_UPLOADS . $target, 'w');
    $ioLength           = 0;

    if ($ioHandle === false) {
        $result->type    = 'danger';
        $result->message = $target . ' kann nicht heruntergeladen werden!';

        if (!empty($errStr)) {
            $result->message .= ' ' . $errStr;
        }

        return;
    }

    if ($fHandle === false) {
        $result->type    = 'danger';
        $result->message = 'Downloaddatei für ' . $target . ' kann nicht erstellt werden!';

        return;
    }

    fwrite($ioHandle, "GET {$ioFile} HTTP/1.1\r\n" .
        "Host: " . PLZIMPORT_HOST . "\r\n" .
        "User-Agent: Mozilla/5.0\r\n" .
        "Keep-Alive: 115\r\n" .
        "Connection: keep-alive\r\n" .
        "\r\n");

    $line = '';
    while (!feof($ioHandle) && $line !== "\r\n") {
        $line = fgets($ioHandle);
        if (preg_match('/Content-Length: ([0-9\.]+)/', $line, $hits)) {
            $ioLength = (int)$hits[1];
        }
    }

    $buf     = fread($ioHandle, $partSize);
    $written = 0;
    while (!feof($ioHandle) && $buf !== false) {
        $written += fwrite($fHandle, $buf);
        $buf     = fread($ioHandle, $partSize);

        if ($buf === false) {
            fclose($fHandle);
            fclose($ioHandle);

            $result->type    = 'danger';
            $result->message = $target . ' kann nicht heruntergeladen werden!';

            return;
        }

        if ($ioLength !== 0) {
            $sessData['step'] = round(50 / $ioLength * $written);
            plzimportWriteSession('Import', $sessData);
        }

        if ($runtime > 0 && time() >= $endTime) {
            // max_execution_time erreicht - restart
            $result->type    = 'danger';
            $result->message = 'Der Download von ' . $target . ' dauert zu lange!';

            return;
        }
    }

    if ($buf !== false) {
        fwrite($fHandle, $buf);
    }

    fclose($fHandle);
    fclose($ioHandle);

    $sessData['step'] = 50;
    plzimportWriteSession('Import', $sessData);

    $result->type    = 'success';
    $result->message = $target . ' wurde erfolgreich heruntergeladen!';

    // Download fertig - weiter mit dem Import
    $cRedirectUrl = Shop::getURL() . '/' . PFAD_ADMIN . 'io.php?io=' .
        urlencode(
            json_encode(
                [
                    'name'   => 'plzimportActionDoImport',
                    'params' => [$target, 'import', $sessData['step']]
                ]
            )
        ) . '&token=' . StringHandler::filterXSS($_REQUEST['jtl_token']);
    header('Location: ' . $cRedirectUrl);
    exit;
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return void
 */
function plzimportActionIndex(JTLSmarty $smarty, array &$messages)
{
    $status = plzimportActionCheckStatus();

    if (isset($status) && $status->running) {
        $messages['notice'] = 'Es l&auml;uft bereits ein Import. Bitte warten Sie bis dieser abgeschlossen ist!';
    }

    $smarty->assign('oPlzOrt_arr', plzimportGetPLZOrt());
}

/**
 * @return array
 */
function plzimportActionUpdateIndex()
{
    Shop::Smarty()->assign('oPlzOrt_arr', plzimportGetPLZOrt());

    return [
        'listHTML' => Shop::Smarty()->fetch('tpl_inc/plz_ort_import_index_list.tpl')
    ];
}

/**
 * @param string $target
 * @param string $part
 * @param int    $step
 * @return object
 */
function plzimportActionDoImport($target = '', $part = '', $step = 0)
{
    $target = StringHandler::filterXSS($target);
    $part   = StringHandler::filterXSS($part);
    $step   = (int)$step;

    session_write_close();
    ini_set('max_execution_time', 30);

    if (empty($part)) {
        $part = 'download';
    }

    $step   = (int)$step;
    $result = (object)[
        'type'    => 'danger',
        'message' => 'Import kann nicht gestartet werden!',
    ];

    if (!empty($target) && (plzimportOpenSession('Import') || $step > 0)) {
        if ($step === 0) {
            $sessData = [
                'running' => true,
                'start'   => time(),
                'step'    => 0,
                'status'  => 'Importiere ' . $target . '...',
            ];
        } else {
            $sessData         = plzimportReadSession('Import');
            $sessData['step'] = $step;
        }

        plzimportWriteSession('Import', $sessData);

        switch ($part) {
            case 'import':
                plzimportDoImport($target, $sessData, $result);
                break;
            case 'download':
            default:
                plzimportDoDownload($target, $sessData, $result);
                break;
        }

        plzimportCloseSession('Import');
    }

    return $result;
}

/**
 * @param string $type
 * @param string $message
 * @return object
 */
function plzimportActionResetImport($type = 'success', $message = 'Import wurde abgebrochen!')
{
    session_write_close();

    $step   = 100;
    $result = (object)[
        'type'    => StringHandler::filterXSS($type),
        'message' => StringHandler::filterXSS($message),
    ];

    $sessData         = plzimportReadSession('Import');
    $sessData['step'] = $step;

    plzimportWriteSession('Import', $sessData);
    plzimportCloseSession('Import');

    return $result;
}

/**
 * @return object
 */
function plzimportActionCallStatus()
{
    session_write_close();
    $sessData = plzimportReadSession('Import');

    if (isset($sessData)) {
        $result = (object)$sessData;
    } else {
        $result = (object)[
            'running' => false,
            'start'   => time(),
            'step'    => 0,
            'status'  => '',
        ];
    }

    return $result;
}

/**
 * @return object
 */
function plzimportActionCheckStatus()
{
    session_write_close();

    if (plzimportOpenSession('Import')) {
        plzimportCloseSession('Import');

        $impData = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tplz
                WHERE cLandISO ='IMP'", 1
        );

        $result = (object)[
            'running' => false,
            'start'   => time(),
            'tmp'     => $impData->nAnzahl,
        ];
    } else {
        $sessData = plzimportReadSession('Import');
        $result   = (object)[
            'running' => $sessData['running'],
            'start'   => $sessData['start'] * 1000,
            'tmp'     => 0,
        ];
    }

    return $result;
}

/**
 * @return array
 */
function plzimportActionDelTempImport()
{
    Shop::DB()->delete('tplz', 'cLandISO', 'IMP');
    return [
        'type'    => 'success',
        'message' => 'Tempor&auml;rer Import wurde gel&ouml;scht!',
    ];
}

/**
 * @return array
 */
function plzimportActionLoadAvailableDownloads()
{
    $oLand_arr = isset($_SESSION['plzimport.oLand_arr']) ? $_SESSION['plzimport.oLand_arr'] : Shop::Cache()->get('plzimport.oLand_arr');

    if ($oLand_arr === false) {
        $ch = curl_init();
        @curl_setopt($ch, CURLOPT_URL, PLZIMPORT_URL);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        @curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $cContent = @curl_exec($ch);
        curl_close($ch);

        if (preg_match_all(PLZIMPORT_REGEX, $cContent, $hits, PREG_PATTERN_ORDER)) {
            $quotedHits = array_map(
                function ($hit) {
                    return Shop::DB()->getPDO()->quote($hit);
                },
                $hits[2]
            );
            $oLand_arr  = Shop::DB()->query(
                "SELECT cISO, cDeutsch
                    FROM tland
                    WHERE cISO IN (" . implode(", ", $quotedHits) . ")
                    ORDER BY cISO", 2
            );

            foreach ($oLand_arr as $key => $oLand) {
                $idx = array_search($oLand->cISO, $hits[2], true);
                if ($idx !== false) {
                    $date         = date_create_from_format('d-M-Y H:i', $hits[3][$idx]);
                    $oLand->cURL  = urlencode($hits[1][$idx]);
                    $oLand->cDate = $date !== false ? $date->format('d.m.Y') : $hits[3][$idx];
                    $oLand->cSize = $hits[4][$idx];
                }
            }

            Shop::Cache()->set('plzimport.oLand_arr', $oLand_arr);
            $_SESSION['plzimport.oLand_arr'] = $oLand_arr;
        } else {
            $oLand_arr = [];
        }
    }

    Shop::Smarty()->assign('oLand_arr', countriesPreventXss($oLand_arr));

    return [
        'dialogHTML' => Shop::Smarty()->fetch('tpl_inc/plz_ort_import_auswahl.tpl')
    ];
}

/**
 * @param stdClass $country
 * @return stdClass
 */
function countryPreventXss($country)
{
    if (Shop::Smarty()->escape_html) {
        return $country;
    }

    return (object)[
        'cISO'     => htmlspecialchars($country->cISO, ENT_QUOTES, JTL_CHARSET, false),
        'cDeutsch' => htmlspecialchars($country->cDeutsch, ENT_QUOTES, JTL_CHARSET, false),
        'cDate'    => htmlspecialchars($country->cDate, ENT_QUOTES, JTL_CHARSET, false),
        'cSize'    => htmlspecialchars($country->cSize, ENT_QUOTES, JTL_CHARSET, false),
        'cURL'     => htmlspecialchars($country->cURL, ENT_QUOTES, JTL_CHARSET, false),
    ];
}

/**
 * @param stdClass[] $countries
 * @return stdClass[]
 */
function countriesPreventXss($countries)
{
    if (Shop::Smarty()->escape_html) {
        return $countries;
    }

    return array_map('countryPreventXss', $countries);
}

/**
 * @param string $target
 */
function plzimportActionRestoreBackup($target = '')
{
    $target = StringHandler::filterXSS($target);

    if (!empty($target)) {
        Shop::DB()->delete('tplz', 'cLandISO', $target);
        Shop::DB()->queryPrepared("INSERT INTO tplz SELECT * FROM tplz_backup WHERE cLandISO = :target", ['target' => $target], 3);
        Shop::DB()->delete('tplz_backup', 'cLandISO', $target);

        $result = (object)[
            'result' => 'success',
        ];
    } else {
        $result = (object)[
            'result' => 'failure',
        ];
    }

    return $result;
}

/**
 * @param string    $step
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return void
 */
function plzimportFinalize($step, JTLSmarty $smarty, array &$messages)
{
    if (isset($_SESSION['plzimport.notice'])) {
        $messages['notice'] = $_SESSION['plzimport.notice'];
        unset($_SESSION['plzimport.notice']);
    }
    if (isset($_SESSION['plzimport.error'])) {
        $messages['error'] = $_SESSION['plzimport.error'];
        unset($_SESSION['plzimport.error']);
    }

    /*switch ($step) {

    }*/

    $smarty->assign('hinweis', $messages['notice'])
        ->assign('fehler', $messages['error'])
        ->display('plz_ort_import.tpl');
}

/**
 * @param string $sessID
 * @return bool
 */
function plzimportOpenSession($sessID)
{
    $dbSess = Shop::DB()->select('tadminsession', 'cSessionId', "plzimport.{$sessID}");

    if (!isset($dbSess->nSessionExpires) || $dbSess->nSessionExpires < time()) {
        Shop::DB()->query(
            "INSERT INTO tadminsession (cSessionId, nSessionExpires, cSessionData)
                VALUES ('plzimport." . $sessID . "', " . (time() + 2 * 60) . ", '')
                ON DUPLICATE KEY UPDATE
                nSessionExpires = " . (time() + 2 * 60) . ",
                cSessionData    = ''", 3
        );

        return true;
    }

    return false;
}

/**
 * @param string $sessID
 * @return void
 */
function plzimportCloseSession($sessID)
{
    Shop::DB()->delete('tadminsession', 'cSessionId', "plzimport.{$sessID}");
}

/**
 * @param string $sessID
 * @param array  $data
 * @return void
 */
function plzimportWriteSession($sessID, array $data)
{
    Shop::DB()->update('tadminsession', 'cSessionId', "plzimport.{$sessID}", (object)[
        'cSessionData'    => serialize($data),
        'nSessionExpires' => time() + 2 * 60
    ]);
}

/**
 * @param string $sessID
 * @return array
 */
function plzimportReadSession($sessID)
{
    $dbSess = Shop::DB()->select('tadminsession', 'cSessionId', "plzimport.{$sessID}");

    if (!empty($dbSess->cSessionData)) {
        return unserialize($dbSess->cSessionData);
    }

    return [];
}

/**
 * @param mixed       $data
 * @param string|null $error
 * @return void
 */
function plzimportMakeResponse($data, $error = null)
{
    ob_end_clean();

    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-type: application/json');

    if ($error !== null) {
        header(makeHTTPHeader(500), true, $error);
    }

    $result = (object)[
        'error' => $error,
        'data'  => utf8_convert_recursive($data)
    ];

    $json = json_encode($result);

    echo $json;
    exit;
}
