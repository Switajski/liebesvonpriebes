<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param array $oDatei_arr
 * @param array $nStat_arr
 * @return int
 */
function getAllFiles(&$oDatei_arr, &$nStat_arr)
{
    $cDateiPfad  = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_SHOPMD5;
    $cDateiListe = JTL_VERSION . '.csv';
    if (!is_array($oDatei_arr)) {
        return 4;
    }
    if (file_exists($cDateiPfad . $cDateiListe)) {
        $cShopFileAll = file_get_contents($cDateiPfad . $cDateiListe);  // kompletter String der md5sum.csv
        if (strlen($cShopFileAll) > 0) {
            $cShopFile_arr = explode("\n", $cShopFileAll);
            if (is_array($cShopFile_arr) && count($cShopFile_arr) > 0) {
                $nStat_arr['nAnzahl'] = 0;
                $nStat_arr['nFehler'] = 0;

                array_multisort($cShopFile_arr);
                foreach ($cShopFile_arr as $i => $cShopFile) {
                    if (strlen($cShopFile) === 0) {
                        continue;
                    }

                    list($cDatei, $cDateiMD5) = explode(';', $cShopFile);

                    $cMD5Akt   = '';
                    $bFehler   = true;
                    $cFilePath = PFAD_ROOT . $cDatei;

                    if (file_exists($cFilePath)) {
                        $cMD5Akt = md5_file($cFilePath);
                    }

                    if ($cMD5Akt === $cDateiMD5) {
                        $bFehler = false;
                    } else {
                        $nStat_arr['nFehler']++;
                    }

                    $oDatei           = new stdClass();
                    $oDatei->cName    = $cDatei;
                    $oDatei->cMD5Orig = $cDateiMD5;
                    $oDatei->cMD5Akt  = $cMD5Akt;
                    $oDatei->bFehler  = $bFehler;

                    $oDatei_arr[] = $oDatei;
                    $nStat_arr['nAnzahl']++;
                }
            }

            return 1;
        }

        return 3;
    }

    return 2;
}
