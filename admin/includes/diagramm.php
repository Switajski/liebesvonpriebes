<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$bExtern = true;
require_once __DIR__ . '/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admininclude.php';
require_once PFAD_ROOT . PFAD_GRAPHCLASS . 'graph.php';

//### Prüfung welcher Graph angezeigt werden soll
if (isset($_SESSION['nDiagrammTyp'])) {
    switch ((int)$_SESSION['nDiagrammTyp']) {
        case 1: // Umsatz - Jahr
            erstelleUmsatzGraph($_SESSION['oY1_arr'], $_SESSION['oY2_arr'], $_SESSION['nYmax'], 1);
            break;
        case 2: // Umsatz - Monat
            erstelleUmsatzGraph($_SESSION['oY1_arr'], $_SESSION['oY2_arr'], $_SESSION['nYmax'], 2);
            break;
        case 3:    // Besuchte Seiten Top10
            erstelleTop10Graph($_SESSION['oGraphData_arr'], $_SESSION['nYmax'], $_SESSION['cGraphFilter']);
            break;
        case 4: // Top Vergleichsliste
            erstelleTopVergleichslisteGraph($_SESSION['oGraphData_arr'], $_SESSION['nYmax'], $_SESSION['Vergleichsliste']->nAnzahl);
            break;
        case 5: // Kampagne DetailStats
            erstelleKampagneDetailGraph($_SESSION['Kampagne']->oKampagneDetailGraph, $_GET['kKampagneDef']);
            break;
    }
}

/**
 * @param array $oY1_arr
 * @param array $oY2_arr
 * @param int   $nYmax
 * @param int   $nDiagrammTyp
 */
function erstelleUmsatzGraph($oY1_arr, $oY2_arr, $nYmax, $nDiagrammTyp)
{
    if (count($oY1_arr) > 0 && count($oY2_arr) > 0) {
        $CGraph = new graph(785, 400);

        $CGraph->parameter['path_to_fonts']     = PFAD_GRAPHCLASS . 'fonts/';
        $CGraph->parameter['y_label_right']     = 'Umsatz';
        $CGraph->parameter['x_grid']            = 'none';
        $CGraph->parameter['y_decimal_right']   = 2;
        $CGraph->parameter['y_min_right']       = 0;
        $CGraph->parameter['y_max_right']       = $nYmax;
        $CGraph->parameter['y_axis_gridlines']  = 11;
        $CGraph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
        $CGraph->parameter['shadow']            = 'none';
        $CGraph->parameter['title']             = 'Umsatzstatistik';
        $CGraph->parameter['x_label']           = 'Monate';
        $CGraph->x_data                         = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        $CGraph->y_data['alpha']                = [];
        $CGraph->y_data['beta']                 = [];

        // Monatsumsatz?
        if ($nDiagrammTyp == 2) {
            $CGraph->parameter['x_label'] = 'Tage';
            $CGraph->x_data               = [];

            for ($i = 1; $i <= 31; $i++) {
                $CGraph->x_data[] = $i;
            }
        }

        // Balken 1 (Umsatz pro Monat) Werte Array aufbauen
        foreach ($CGraph->x_data as $i => $x_data) {
            $tmpUmsatz = 0;

            foreach ($oY1_arr as $oY1) {
                if ((int)$oY1->ZeitWert === ($i + 1)) {
                    $tmpUmsatz = $oY1->Umsatz;
                }
            }

            $CGraph->y_data['alpha'][] = $tmpUmsatz;
        }
        // Balken 2 (Durchschnittsumsatz pro Monat) Werte Array aufbauen
        foreach ($CGraph->x_data as $i => $x_data) {
            $tmpUmsatz = 0;

            foreach ($oY2_arr as $oY2) {
                if ((int)$oY2->ZeitWert === $i + 1) {
                    $tmpUmsatz = $oY2->Umsatz;
                }
            }

            $CGraph->y_data['beta'][] = $tmpUmsatz;
        }

        if (count($CGraph->y_data['alpha']) > 1 && count($CGraph->y_data['beta']) > 1 && count($CGraph->x_data) > 1) {
            $CGraph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
            $CGraph->y_format['beta']  = ['colour' => 'red', 'bar' => 'fill', 'bar_size' => 0.3, 'y_axis' => 'right'];

            $CGraph->y_order = ['alpha', 'beta'];

            $CGraph->draw_stack();
        }
    }
}

/**
 * @param array  $oGraphData_arr
 * @param int    $nYmax
 * @param string $cGraphFilter
 */
function erstelleTop10Graph($oGraphData_arr, $nYmax, $cGraphFilter)
{
    if (count($oGraphData_arr) > 0) {
        $CGraph = new graph(785, 400);

        $CGraph->parameter['path_to_fonts']     = PFAD_ROOT . PFAD_GRAPHCLASS . 'fonts/';
        $CGraph->parameter['y_label_right']     = 'Anzahl Besuche';
        $CGraph->parameter['x_grid']            = 'none';
        $CGraph->parameter['y_decimal_right']   = 2;
        $CGraph->parameter['y_min_right']       = 0;
        $CGraph->parameter['y_max_right']       = $nYmax;
        $CGraph->parameter['y_axis_gridlines']  = 11;
        $CGraph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
        $CGraph->parameter['shadow']            = 'none';
        $CGraph->parameter['title']             = 'Top10 ' . $cGraphFilter;
        $CGraph->parameter['x_label']           = $cGraphFilter;
        $CGraph->x_data                         = [];
        $CGraph->y_data['alpha']                = [];

        if (count($oGraphData_arr) > 0) {
            // Array sortieren
            usort($oGraphData_arr, "Sortierung");

            foreach ($oGraphData_arr as $i => $oGraphData) {
                if ($i > 10) {
                    // Nach 10 Elemente stoppen (Top10)

                    break;
                }

                $CGraph->x_data[]          = $oGraphData->cName;
                $CGraph->y_data['alpha'][] = $oGraphData->nWert;
            }
        }

        if (count($CGraph->x_data) > 1 && count($CGraph->y_data['alpha']) > 1) {
            $CGraph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
            $CGraph->y_order           = ['alpha'];

            $CGraph->draw_stack();
        }
    }
}

/**
 * @param array $oGraphData_arr
 * @param int   $nYmax
 * @param int   $nAnzahl
 */
function erstelleTopVergleichslisteGraph($oGraphData_arr, $nYmax, $nAnzahl)
{
    if (is_array($oGraphData_arr) && count($oGraphData_arr) > 0) {
        $CGraph = new graph(785, 400);

        $CGraph->parameter['path_to_fonts']     = PFAD_ROOT . PFAD_GRAPHCLASS . 'fonts/';
        $CGraph->parameter['y_label_right']     = 'Anzahl Vergleiche';
        $CGraph->parameter['x_grid']            = 'none';
        $CGraph->parameter['y_decimal_right']   = 2;
        $CGraph->parameter['y_min_right']       = 0;
        $CGraph->parameter['y_max_right']       = $nYmax;
        $CGraph->parameter['y_axis_gridlines']  = 11;
        $CGraph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
        $CGraph->parameter['shadow']            = 'none';
        $CGraph->parameter['title']             = 'Top' . $nAnzahl . ' Artikel die verglichen wurden';
        $CGraph->parameter['x_label']           = 'Artikel';
        $CGraph->x_data                         = [];
        $CGraph->y_data['alpha']                = [];

        foreach ($oGraphData_arr as $oGraphData) {
            $CGraph->x_data[]          = $oGraphData->cArtikelName;
            $CGraph->y_data['alpha'][] = $oGraphData->nAnzahl;
        }

        if (count($CGraph->x_data) > 1 && count($CGraph->y_data['alpha']) > 1) {
            $CGraph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
            $CGraph->y_order           = ['alpha'];

            $CGraph->draw_stack();
        }
    }
}

/**
 * @param object $oKampagneDetailGraph
 * @param int    $kKampagneDef
 */
function erstelleKampagneDetailGraph($oKampagneDetailGraph, $kKampagneDef)
{
    $CGraph = new graph(950, 400);

    $CGraph->parameter['path_to_fonts']     = PFAD_ROOT . PFAD_GRAPHCLASS . 'fonts/';
    $CGraph->parameter['y_label_right']     = 'Anzahl';
    $CGraph->parameter['x_grid']            = 'none';
    $CGraph->parameter['y_decimal_right']   = 2;
    $CGraph->parameter['y_min_right']       = 0;
    $CGraph->parameter['y_max_right']       = ceil((int)$oKampagneDetailGraph->nGraphMaxAssoc_arr[$kKampagneDef] * 1.1);
    $CGraph->parameter['y_axis_gridlines']  = 11;
    $CGraph->parameter['y_axis_text_right'] = 2;  //print a tick every 2nd grid line
    $CGraph->parameter['shadow']            = 'none';
    $CGraph->parameter['title']             = $oKampagneDetailGraph->oKampagneDef_arr[$kKampagneDef]->cName;
    $CGraph->x_data                         = [];
    $CGraph->y_data['alpha']                = [];

    if (is_array($oKampagneDetailGraph->oKampagneDetailGraph_arr) && count($oKampagneDetailGraph->oKampagneDetailGraph_arr) > 0) {
        foreach ($oKampagneDetailGraph->oKampagneDetailGraph_arr as $oKampagneDetailGraphDef_arr) {
            $CGraph->x_data[]          = "(" . $oKampagneDetailGraphDef_arr[$kKampagneDef] . ") " . $oKampagneDetailGraphDef_arr['cDatum'];
            $CGraph->y_data['alpha'][] = $oKampagneDetailGraphDef_arr[$kKampagneDef];
        }
    }
    // Balken 1 (Umsatz pro Monat) Werte Array aufbauen
    if (count($CGraph->y_data['alpha']) > 1 && count($CGraph->x_data) > 1) {
        $CGraph->y_format['alpha'] = ['colour' => 'blue', 'bar' => 'fill', 'bar_size' => 0.8, 'y_axis' => 'right'];
        $CGraph->y_order           = ['alpha'];
        $CGraph->draw_stack();
    }
}

/**
 * @param object $oA
 * @param object $oB
 * @return int
 */
function Sortierung($oA, $oB)
{
    if ($oA->nWert == $oB->nWert) {
        return 0;
    }

    return ($oA->nWert < $oB->nWert) ? +1 : -1;
}
