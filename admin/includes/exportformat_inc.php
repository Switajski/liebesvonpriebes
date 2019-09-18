<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @deprecated since 4.05
 * @param array $cDateinameSplit_arr
 * @param int   $nDateiZaehler
 * @return string
 */
function gibDateiname($cDateinameSplit_arr, $nDateiZaehler)
{
    if (is_array($cDateinameSplit_arr) && count($cDateinameSplit_arr) > 1) {
        return $cDateinameSplit_arr[0] . $nDateiZaehler . $cDateinameSplit_arr[1];
    }

    return $cDateinameSplit_arr[0] . $nDateiZaehler;
}

/**
 * @deprecated since 4.05
 * @param array $cDateinameSplit_arr
 * @param int   $nDateiZaehler
 * @return string
 */
function gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler)
{
    return PFAD_ROOT . PFAD_EXPORT . gibDateiname($cDateinameSplit_arr, $nDateiZaehler);
}

/**
 * @deprecated since 4.05
 * @return array
 */
function pruefeExportformat()
{
    $cPlausiValue_arr = [];
    // Name
    if (!isset($_POST['cName']) || strlen($_POST['cName']) === 0) {
        $cPlausiValue_arr['cName'] = 1;
    }
    // Dateiname
    if (!isset($_POST['cDateiname']) || strlen($_POST['cDateiname']) === 0) {
        $cPlausiValue_arr['cDateiname'] = 1;
    }
    // Dateiname Endung fehlt
    if (strpos($_POST['cDateiname'], '.') === false) {
        $cPlausiValue_arr['cDateiname'] = 2;
    }
    // Content
    if (!isset($_POST['cContent']) || strlen($_POST['cContent']) === 0) {
        $cPlausiValue_arr['cContent'] = 1;
    }
    // Sprache
    if (!isset($_POST['kSprache']) || (int)$_POST['kSprache'] === 0) {
        $cPlausiValue_arr['kSprache'] = 1;
    }
    // Sprache
    if (!isset($_POST['kWaehrung']) || (int)$_POST['kWaehrung'] === 0) {
        $cPlausiValue_arr['kWaehrung'] = 1;
    }
    // Kundengruppe
    if (!isset($_POST['kKundengruppe']) || (int)$_POST['kKundengruppe'] === 0) {
        $cPlausiValue_arr['kKundengruppe'] = 1;
    }

    return $cPlausiValue_arr;
}

/**
 * Falls eingestellt, wird die Exportdatei in mehrere Dateien gesplittet
 *
 * @deprecated since 4.05
 * @param object $oExportformat
 */
function splitteExportDatei($oExportformat)
{
    if (isset($oExportformat->nSplitgroesse) &&
        (int)$oExportformat->nSplitgroesse > 0 &&
        file_exists(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname)
    ) {
        $nDateiZaehler       = 1;
        $cDateinameSplit_arr = [];
        $nFileTypePos        = strrpos($oExportformat->cDateiname, '.');
        // Dateiname splitten nach Name + Typ
        if ($nFileTypePos === false) {
            $cDateinameSplit_arr[0] = $oExportformat->cDateiname;
        } else {
            $cDateinameSplit_arr[0] = substr($oExportformat->cDateiname, 0, $nFileTypePos);
            $cDateinameSplit_arr[1] = substr($oExportformat->cDateiname, $nFileTypePos);
        }
        // Ist die angelegte Datei größer als die Einstellung im Exportformat?
        clearstatcache();
        if (filesize(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname) >=
            ($oExportformat->nSplitgroesse * 1024 * 1024 - 102400)) {
            sleep(2);
            loescheExportDateien($oExportformat->cDateiname, $cDateinameSplit_arr[0]);
            $handle     = fopen(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname, 'r');
            $nZeile     = 1;
            $new_handle = fopen(gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler), 'w');
            $nSizeDatei = 0;
            while (($cContent = fgets($handle)) !== false) {
                if ($nZeile > 1) {
                    $nSizeZeile = strlen($cContent) + 2;
                    //Schwelle erreicht?
                    if ($nSizeDatei <= ($oExportformat->nSplitgroesse * 1024 * 1024 - 102400)) {
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei += $nSizeZeile;
                    } else {
                        //neue Datei
                        schreibeFusszeile($new_handle, $oExportformat->cFusszeile, $oExportformat->cKodierung);
                        fclose($new_handle);
                        $nDateiZaehler++;
                        $new_handle = fopen(gibDateiPfad($cDateinameSplit_arr, $nDateiZaehler), 'w');
                        schreibeKopfzeile($new_handle, $oExportformat->cKopfzeile, $oExportformat->cKodierung);
                        // Schreibe Content
                        fwrite($new_handle, $cContent);
                        $nSizeDatei = $nSizeZeile;
                    }
                } elseif ($nZeile === 1) {
                    schreibeKopfzeile($new_handle, $oExportformat->cKopfzeile, $oExportformat->cKodierung);
                }
                $nZeile++;
            }
            fclose($new_handle);
            fclose($handle);
            unlink(PFAD_ROOT . PFAD_EXPORT . $oExportformat->cDateiname);
        }
    }
}

/**
 * @deprecated since 4.05
 * @param resource $dateiHandle
 * @param string   $cKopfzeile
 * @param string   $cKodierung
 */
function schreibeKopfzeile($dateiHandle, $cKopfzeile, $cKodierung)
{
    //export begin
    if ($cKopfzeile) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            if ($cKodierung === 'UTF-8') {
                fwrite($dateiHandle, "\xEF\xBB\xBF");
            }
            fwrite($dateiHandle, utf8_encode($cKopfzeile . "\n"));
        } else {
            fwrite($dateiHandle, $cKopfzeile . "\n");
        }
    }
}

/**
 * @deprecated since 4.05
 * @param resource $dateiHandle
 * @param string   $cFusszeile
 * @param string   $cKodierung
 */
function schreibeFusszeile($dateiHandle, $cFusszeile, $cKodierung)
{
    if (strlen($cFusszeile) > 0) {
        if ($cKodierung === 'UTF-8' || $cKodierung === 'UTF-8noBOM') {
            fwrite($dateiHandle, utf8_encode($cFusszeile));
        } else {
            fwrite($dateiHandle, $cFusszeile);
        }
    }
}

/**
 * @deprecated since 4.05
 * @param string $cDateiname
 * @param string $cDateinameSplit
 */
function loescheExportDateien($cDateiname, $cDateinameSplit)
{
    if (is_dir(PFAD_ROOT . PFAD_EXPORT)) {
        $dir = opendir(PFAD_ROOT . PFAD_EXPORT);
        if ($dir !== false) {
            while (($cDatei = readdir($dir)) !== false) {
                if ($cDatei !== $cDateiname && strpos($cDatei, $cDateinameSplit) !== false) {
                    @unlink(PFAD_ROOT . PFAD_EXPORT . $cDatei);
                }
            }
            closedir($dir);
        }
    }
}

/**
 * @param Artikel $Artikel
 * @param object  $exportformat
 * @param array   $ExportEinstellungen
 * @param array   $KategorieListe
 * @param array   $oGlobal_arr
 */
function verarbeiteYategoExport(&$Artikel, $exportformat, $ExportEinstellungen, &$KategorieListe, &$oGlobal_arr)
{
    if ($Artikel->kArtikel > 0 &&
        !($ExportEinstellungen['exportformate_lager_ueber_null'] === 'Y' && $Artikel->fLagerbestand <= 0 ||
        $ExportEinstellungen['exportformate_lager_ueber_null'] === 'O' && $Artikel->fLagerbestand <= 0 &&
            $Artikel->cLagerKleinerNull === 'N' ||
        $ExportEinstellungen['exportformate_preis_ueber_null'] === 'Y' && $Artikel->Preise->fVKNetto <= 0 ||
        $ExportEinstellungen['exportformate_beschreibung'] === 'Y' && !$Artikel->cBeschreibung)
    ) {
        $Artikel->cBeschreibungHTML     = str_replace('"', '&quot;', $Artikel->cBeschreibung);
        $Artikel->cKurzBeschreibungHTML = str_replace('"', '&quot;', $Artikel->cKurzBeschreibung);
        $Artikel->cName                 = strip_tags($Artikel->cName);
        $Artikel->cBeschreibung         = strip_tags($Artikel->cBeschreibung, DESCRIPTION_TAGS);
        $Artikel->cBeschreibung         = str_replace('\"', '""', addslashes($Artikel->cBeschreibung));
        $Artikel->cKurzBeschreibung     = strip_tags($Artikel->cKurzBeschreibung);
        $Artikel->cKurzBeschreibung     = str_replace('\"', '""', addslashes($Artikel->cKurzBeschreibung));

        $find    = ["\r\n", "\r", "\n", "\x0B", "\x0"];
        $replace = [' ', ' ', ' ', ' ', ''];

        // Zeichenmaskierung
        if ($ExportEinstellungen['exportformate_quot'] !== 'N' && $ExportEinstellungen['exportformate_quot']) {
            $find[] = '"';
            if ($ExportEinstellungen['exportformate_quot'] === 'q' || $ExportEinstellungen['exportformate_quot'] === 'bq') {
                $replace[] = '\"';
            } elseif ($ExportEinstellungen['exportformate_quot'] === 'qq') {
                $replace[] = '""';
            } else {
                $replace[] = $ExportEinstellungen['exportformate_quot'];
            }
        }
        if ($ExportEinstellungen['exportformate_equot'] !== 'N' && $ExportEinstellungen['exportformate_equot']) {
            $find[] = "'";
            if ($ExportEinstellungen['exportformate_equot'] === 'q' || $ExportEinstellungen['exportformate_equot'] === 'bq') {
                $replace[] = '"';
            } else {
                $replace[] = $ExportEinstellungen['exportformate_equot'];
            }
        }
        if ($ExportEinstellungen['exportformate_semikolon'] !== 'N' && $ExportEinstellungen['exportformate_semikolon']) {
            $find[]    = ';';
            $replace[] = $ExportEinstellungen['exportformate_semikolon'];
        }
        $Artikel->cName                 = StringHandler::unhtmlentities($Artikel->cName);
        $Artikel->cBeschreibung         = StringHandler::unhtmlentities($Artikel->cBeschreibung);
        $Artikel->cKurzBeschreibung     = StringHandler::unhtmlentities($Artikel->cKurzBeschreibung);
        $Artikel->cName                 = StringHandler::removeWhitespace(
            str_replace($find, $replace, $Artikel->cName)
        );
        $Artikel->cBeschreibung         = StringHandler::removeWhitespace(
            str_replace($find, $replace, $Artikel->cBeschreibung)
        );
        $Artikel->cKurzBeschreibung     = StringHandler::removeWhitespace(
            str_replace($find, $replace, $Artikel->cKurzBeschreibung)
        );
        $Artikel->cBeschreibungHTML     = StringHandler::removeWhitespace(
            str_replace($find, $replace, $Artikel->cBeschreibungHTML)
        );
        $Artikel->cKurzBeschreibungHTML = StringHandler::removeWhitespace(
            str_replace($find, $replace, $Artikel->cKurzBeschreibungHTML)
        );
        $Artikel->Preise->fVKBrutto     = berechneBrutto($Artikel->Preise->fVKNetto, gibUst($Artikel->kSteuerklasse));

        //Kategoriepfad
        $Artikel->Kategorie     = new Kategorie(
            $Artikel->gibKategorie(),
            $exportformat->kSprache,
            $exportformat->kKundengruppe
        );
        $Artikel->Kategoriepfad = $Artikel->Kategorie->cKategoriePfad;
        $Artikel->Versandkosten = gibGuenstigsteVersandkosten(
            $ExportEinstellungen['exportformate_lieferland'],
            $Artikel,
            0,
            $exportformat->kKundengruppe
        );

        // Kampagne URL
        if (isset($exportformat->tkampagne_cParameter)) {
            $cSep = '?';
            if (strpos($Artikel->cURL, '.php') !== false) {
                $cSep = '&';
            }

            $Artikel->cURL .= $cSep . $exportformat->tkampagne_cParameter . '=' . $exportformat->tkampagne_cWert;
        }

        $Artikel->cDeeplink   = Shop::getURL() . '/' . $Artikel->cURL;
        $Artikel->Artikelbild = '';
        if (isset($Artikel->Bilder[0]->cPfadGross) && strlen($Artikel->Bilder[0]->cPfadGross) > 0) {
            $Artikel->Artikelbild = Shop::getURL() . '/' . $Artikel->Bilder[0]->cPfadGross;
        }
        $Artikel->Lieferbar = 'Y';
        if ($Artikel->fLagerbestand <= 0) {
            $Artikel->Lieferbar = 'N';
        }
        $Artikel->Lieferbar_01 = 1;
        if ($Artikel->fLagerbestand <= 0) {
            $Artikel->Lieferbar_01 = 0;
        }
        $Artikel->Verfuegbarkeit_kelkoo = '003';
        if ($Artikel->fLagerbestand > 0) {
            $Artikel->Verfuegbarkeit_kelkoo = '001';
        }

        // X-Selling
        $oXSellingTMP_arr = Shop::DB()->query(
            "SELECT kXSellArtikel
                FROM txsell
                WHERE kArtikel = " . (int)$Artikel->kArtikel, 9
        );
        $oXSelling_arr    = [];
        if (is_array($oXSellingTMP_arr) && count($oXSellingTMP_arr) > 0) {
            foreach ($oXSellingTMP_arr as $oXSellingTMP) {
                $oXSelling_arr[] = $oXSellingTMP['kXSellArtikel'];
            }
        }

        $cVarianten       = '';
        $kKategorie_arr   = []; // Alle Kategorien vom Artikel
        $kYatKategoie_arr = []; // Alle Yatego Kategorien vom Artikel
        $oKategorie_arr   = Shop::DB()->selectAll(
            'tkategorieartikel',
            'kArtikel',
            (int)$Artikel->kArtikel,
            'kKategorie'
        );
        if (is_array($oKategorie_arr) && count($oKategorie_arr) > 0) {
            foreach ($oKategorie_arr as $oKategorie) {
                $kKategorie_arr[] = $oKategorie->kKategorie;
            }
        }
        if ($Artikel->FunktionsAttribute['yategokat']) {
            $kYatKategoie_arr = explode(',', $Artikel->FunktionsAttribute['yategokat']);
        }
        $kKategorieListe_arr = array_merge($kYatKategoie_arr, $kKategorie_arr);

        // Varianten
        $oVariationsLager_arr = [];
        if (is_array($Artikel->Variationen)) {
            $defaultOptions       = Artikel::getDefaultOptions();
            $shopURL              = Shop::getURL();
            $combinations         = [];
            $oVariationsListe_arr = [];

            foreach ($Artikel->Variationen as $oVariation) {
                $oGlobal_arr['varianten'][] = [
                    'foreign_id'       => $oVariation->kEigenschaft,
                    'vs_title'         => $oVariation->cName,
                    'variant_set_name' => $oVariation->cName,
                    'set_sorting'      => 'man',
                    'sorting_number'   => $oVariation->nSort
                ];
                $oVariationsListe_arr[]     = $oVariation->kEigenschaft;
                $combinationsPass           = [];

                foreach ($oVariation->Werte as $oWert) {
                    $oVariationsBild = Shop::DB()->select(
                        'teigenschaftwertpict',
                        'kEigenschaftWert',
                        (int)$oWert->kEigenschaftWert,
                        null,
                        null,
                        null,
                        null,
                        false,
                        'cPfad'
                    );

                    $cBild = !empty($oVariationsBild->cPfad)
                        ? $shopURL . '/' . PFAD_VARIATIONSBILDER_GROSS . $oVariationsBild->cPfad
                        : '';

                    $oGlobal_arr['variantenwerte'][] = [
                        'variant_set_id' => $oWert->kEigenschaft,
                        'foreign_id'     => $oWert->kEigenschaftWert,
                        'title'          => $oWert->cName,
                        'picture'        => $cBild,
                        'price'          => getNum($oWert->fAufpreisNetto)
                    ];

                    if ((int)$Artikel->nIstVater === 0) {
                        // Varianten sukzessive kombinieren
                        if (count($combinations) === 0) {
                            $oVariantKombi                    = new stdClass();
                            $oVariantKombi->cName             = $oWert->cName;
                            $oVariantKombi->fAufpreisNetto    = $oWert->fAufpreisNetto;
                            $oVariantKombi->cEigenschaft      = (string)$oVariation->kEigenschaft;
                            $oVariantKombi->cEigenschaftWerte = (string)$oWert->kEigenschaftWert;
                            $oVariantKombi->foreignIdPostfix  = sprintf("%05s", $oWert->kEigenschaftWert);
                            $combinationsPass[]               = $oVariantKombi;
                        } else {
                            foreach ($combinations as $oVariantKombi) {
                                $oVariantKombiNew                     = clone $oVariantKombi;
                                $oVariantKombiNew->cName             .= '.' . $oWert->cName;
                                $oVariantKombiNew->fAufpreisNetto    += $oWert->fAufpreisNetto;
                                $oVariantKombiNew->cEigenschaftWerte .= ',' . $oWert->kEigenschaftWert;
                                $oVariantKombiNew->foreignIdPostfix  .= sprintf("%05s", $oWert->kEigenschaftWert);
                                $combinationsPass[]                   = $oVariantKombiNew;
                            }
                        }
                    }
                }

                $combinations = $combinationsPass;
            }

            if ((int)$Artikel->nIstVater > 0) {
                // Hole VarKombi-Kinder
                $combinations = Shop::DB()->query(
                    "SELECT a.kArtikel, ekw.kEigenschaftKombi,
                            GROUP_CONCAT(ekw.kEigenschaft ORDER BY ekw.kEigenschaft SEPARATOR ',') AS cEigenschaften,
                            GROUP_CONCAT(ekw.kEigenschaftWert ORDER BY ekw.kEigenschaft SEPARATOR ',') AS cEigenschaftWerte
                        FROM tartikel AS a
                            JOIN teigenschaftkombiwert AS ekw
                                ON ekw.kEigenschaftKombi = a.kEigenschaftKombi
                        WHERE a.kVaterArtikel = " . (int)$Artikel->kArtikel . "
                        GROUP BY ekw.kEigenschaftKombi",
                    2
                );
            }

            foreach ($combinations as $combination) {
                if ((int)$Artikel->nIstVater > 0) {
                    $childArticle = new Artikel();
                    $childArticle->fuelleArtikel(
                        $combination->kArtikel,
                        $defaultOptions,
                        $exportformat->kKundengruppe,
                        $exportformat->kSprache,
                        true
                    );
                    $oVariationsLager_arr[] = [
                        'foreign_id'    => $childArticle->kArtikel,
                        'article_id'    => $Artikel->kArtikel,
                        'variant_ids'   => $combination->cEigenschaftWerte,
                        'stock_value'   => ($childArticle->cLagerBeachten === 'Y' &&
                            $childArticle->cLagerKleinerNull === 'N')
                            ? $childArticle->fLagerbestand
                            : -1,
                        'delivery_date' => $childArticle->cLieferstatus,
                        'active'        => ($childArticle->cLagerBeachten === 'Y' &&
                            $childArticle->cLagerKleinerNull === 'N' &&
                            $childArticle->fLagerbestand <= 0)
                            ? 0
                            : 1,
                        'article_nr'    => $childArticle->cArtNr,
                        'price'         => $childArticle->Preise->fVKNetto,
                        'quantity_unit' => !empty($childArticle->cVPEEinheit)
                            ? $childArticle->cVPEEinheit
                            : utf8_decode('Stück'),
                        'package_size'  => !empty($childArticle->fVPEWert) && $childArticle->fVPEWert > 0
                            ? $childArticle->fVPEWert
                            : 1,
                        'info_v_title'  => '',
                        'info_vs_id'    => '',
                        'info_p_title'  => '',
                        'delitem'       => ''
                    ];
                } else {
                    $oVariationsLager_arr[] = [
                        'foreign_id'    => $Artikel->kArtikel . $combination->foreignIdPostfix,
                        'article_id'    => $Artikel->kArtikel,
                        'variant_ids'   => $combination->cEigenschaftWerte,
                        'stock_value'   => $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerVariation === 'N'
                            ? $Artikel->fLagerbestand
                            : -1,
                        'delivery_date' => $Artikel->cLieferstatus,
                        'active'        => ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N' &&
                            $Artikel->fLagerbestand <= 0)
                            ? 0
                            : 1,
                        'article_nr'    => $Artikel->cArtNr . '.' . $combination->cName,
                        'price'         => $Artikel->Preise->fVKNetto + $combination->fAufpreisNetto,
                        'quantity_unit' => !empty($Artikel->cVPEEinheit) ? $Artikel->cVPEEinheit : utf8_decode('Stück'),
                        'package_size'  => (!empty($Artikel->fVPEWert) && $Artikel->fVPEWert > 0)
                            ? $Artikel->fVPEWert
                            : 1,
                        'info_v_title'  => '',
                        'info_vs_id'    => '',
                        'info_p_title'  => '',
                        'delitem'       => ''
                    ];
                }
            }

            $cVarianten = implode(',', $oVariationsListe_arr);
        }

        //do not use double quotes here to keep csv column order (https://gitlab.jtl-software.de/jtlshop/shop4/issues/166)
        $cBacklink = "<a href='" . getURL($Artikel->cURL) . "' target='_blank'>{$Artikel->cName}</a>";

        $oGlobal_arr['artikel'][] = [
            'foreign_id'         => $Artikel->kArtikel,
            'article_nr'         => $Artikel->cArtNr,
            'title'              => $Artikel->cName,
            'tax'                => getNum($Artikel->fMwSt),
            'price'              => count($Artikel->Variationen) > 0 ? 0 : getNum($Artikel->Preise->fVKBrutto),
            'price_uvp'          => getNum($Artikel->fUVP),
            'delivery_surcharge' => 0,
            'delivery_calc_once' => 0,
            'short_desc'         => '<h2>' . $Artikel->cName . '</h2>' . ($Artikel->cKurzBeschreibung
                    ?: substr($Artikel->cBeschreibung, 0, 130)),
            'long_desc'          => '<h2>' . $Artikel->cName . '</h2>' . $Artikel->cBeschreibung . $cBacklink,
            'url'                => getURL($Artikel->cURL),
            'picture'            => isset($Artikel->Bilder[0]) ? getURL($Artikel->Bilder[0]->cPfadGross) : '',
            'picture2'           => isset($Artikel->Bilder[1]) ? getURL($Artikel->Bilder[1]->cPfadGross) : '',
            'picture3'           => isset($Artikel->Bilder[2]) ? getURL($Artikel->Bilder[2]->cPfadGross) : '',
            'picture4'           => isset($Artikel->Bilder[3]) ? getURL($Artikel->Bilder[3]->cPfadGross) : '',
            'picture5'           => isset($Artikel->Bilder[4]) ? getURL($Artikel->Bilder[4]->cPfadGross) : '',
            'categories'         => implode(',', $kKategorieListe_arr),
            'variants'           => $cVarianten,
            'delivery_date'      => $Artikel->cLieferstatus,
            'stock'              => $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N'
                ? $Artikel->fLagerbestand : -1,
            'cross_selling'      => implode(',', $oXSelling_arr)
        ];

        $KategorieListe[$Artikel->Kategorie->kKategorie] = 1;

        // Lager
        if (count($Artikel->Variationen) === 0) {
            $oGlobal_arr['lager'][] = [
                'foreign_id'    => $Artikel->kArtikel,
                'article_id'    => $Artikel->kArtikel,
                'variant_ids'   => $cVarianten,
                'stock_value'   => $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N'
                    ? $Artikel->fLagerbestand : -1,
                'delivery_date' => $Artikel->cLieferstatus,
                'active'        => ($Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N' &&
                    $Artikel->fLagerbestand <= 0) ? 0 : 1,
                'article_nr'    => $Artikel->cArtNr,
                'price'         => 0,
                'quantity_unit' => (isset($Artikel->cVPEEinheit) && strlen($Artikel->cVPEEinheit) > 0)
                    ? $Artikel->cVPEEinheit : utf8_decode('Stück'),
                'package_size'  => (isset($Artikel->fVPEWert) && $Artikel->fVPEWert > 0) ? $Artikel->fVPEWert : 1,
                'info_v_title'  => '',
                'info_vs_id'    => '',
                'info_p_title'  => '',
                'delitem'       => ''
            ];
        } else {
            $oGlobal_arr['lager'] = array_merge($oGlobal_arr['lager'], $oVariationsLager_arr);
        }
    }
}

/**
 * @return bool
 */
function pruefeYategoExportPfad()
{
    return (is_dir(PFAD_ROOT . PFAD_EXPORT_YATEGO) && is_writable(PFAD_ROOT . PFAD_EXPORT_YATEGO));
}

/**
 * @param int $kExportformat
 * @return array
 */
function getEinstellungenExport($kExportformat)
{
    $kExportformat = (int)$kExportformat;
    $ret           = [];
    if ($kExportformat > 0) {
        $einst = Shop::DB()->selectAll(
            'texportformateinstellungen',
            'kExportformat',
            $kExportformat,
            'cName, cWert'
        );
        foreach ($einst as $eins) {
            if ($eins->cName) {
                $ret[$eins->cName] = $eins->cWert;
            }
        }
    }

    return $ret;
}

/**
 * @deprecated since 4.05
 * @param object $oExportformat
 * @return array
 */
function baueArtikelExportSQL(&$oExportformat)
{
    $cSQL_arr          = [];
    $cSQL_arr['Where'] = '';
    $cSQL_arr['Join']  = '';

    if (empty($oExportformat->kExportformat)) {
        return $cSQL_arr;
    }
    $cExportEinstellungAssoc_arr = getEinstellungenExport($oExportformat->kExportformat);

    switch ($oExportformat->nVarKombiOption) {
        case 2:
            $cSQL_arr['Where'] = " AND kVaterArtikel = 0";
            break;
        case 3:
            $cSQL_arr['Where'] = " AND (tartikel.nIstVater != 1 OR tartikel.kEigenschaftKombi > 0)";
            break;
    }
    if (isset($cExportEinstellungAssoc_arr['exportformate_lager_ueber_null']) &&
        $cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'] === 'Y') {
        $cSQL_arr['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y'))";
    } elseif (isset($cExportEinstellungAssoc_arr['exportformate_lager_ueber_null']) &&
        $cExportEinstellungAssoc_arr['exportformate_lager_ueber_null'] === 'O') {
        $cSQL_arr['Where'] .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                                    OR tartikel.cLagerKleinerNull = 'Y')";
    }

    if (isset($cExportEinstellungAssoc_arr['exportformate_preis_ueber_null']) &&
        $cExportEinstellungAssoc_arr['exportformate_preis_ueber_null'] === 'Y') {
        $cSQL_arr['Join'] .= " JOIN tpreise ON tpreise.kArtikel = tartikel.kArtikel
                                AND tpreise.kKundengruppe = " . (int)$oExportformat->kKundengruppe . "
                                AND tpreise.fVKNetto > 0";
    }

    if (isset($cExportEinstellungAssoc_arr['exportformate_beschreibung']) &&
        $cExportEinstellungAssoc_arr['exportformate_beschreibung'] === 'Y') {
        $cSQL_arr['Where'] .= " AND tartikel.cBeschreibung != ''";
    }

    return $cSQL_arr;
}

/**
 * @deprecated since 4.05
 * @param object $oExportformat
 * @return mixed
 */
function holeMaxExportArtikelAnzahl(&$oExportformat)
{
    $cSQL_arr = baueArtikelExportSQL($oExportformat);
    $conf     = Shop::getSettings([CONF_GLOBAL]);
    $sql      = 'AND NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))';
    if (isset($conf['global']['global_erscheinende_kaeuflich']) &&
        $conf['global']['global_erscheinende_kaeuflich'] === 'Y') {
        $sql = 'AND (
                    NOT (DATE(tartikel.dErscheinungsdatum) > DATE(NOW()))
                    OR  (
                            DATE(tartikel.dErscheinungsdatum) > DATE(NOW())
                            AND (tartikel.cLagerBeachten = "N" 
                                OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = "Y")
                        )
                )';
    }
    $cid = 'xp_' . md5(json_encode($cSQL_arr) . $sql);
    if (($count = Shop::Cache()->get($cid)) !== false) {
        return $count;
    }

    $count = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tartikel
            LEFT JOIN tartikelattribut 
                ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            LEFT JOIN tartikelsichtbarkeit 
                ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = " . (int)$oExportformat->kKundengruppe . "
            " . $cSQL_arr['Join'] . "
            WHERE tartikelattribut.kArtikelAttribut IS NULL" . $cSQL_arr['Where'] . "
                AND tartikelsichtbarkeit.kArtikel IS NULL
                {$sql}", 1
    );
    Shop::Cache()->set($cid, $count, [CACHING_GROUP_CORE], 120);

    return $count;
}
