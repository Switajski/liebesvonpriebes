<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int    $nTyp
 * @param string $nDateStampVon
 * @param string $nDateStampBis
 * @param int    $nAnzeigeIntervall
 * @return array|mixed
 */
function gibBackendStatistik($nTyp, $nDateStampVon, $nDateStampBis, &$nAnzeigeIntervall)
{
    if ($nTyp > 0 && $nDateStampVon > 0 && $nDateStampBis > 0) {
        $oStatistik        = new Statistik($nDateStampVon, $nDateStampBis);
        $nAnzeigeIntervall = $oStatistik->getAnzeigeIntervall();
        $oStat_arr         = [];

        switch ($nTyp) {
            // Besucher Stats
            case STATS_ADMIN_TYPE_BESUCHER:
                $oStat_arr = $oStatistik->holeBesucherStats();
                break;

            // Kundenherkunft
            case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
                $oStat_arr = $oStatistik->holeKundenherkunftStats();
                break;

            // Umsatz
            case STATS_ADMIN_TYPE_SUCHMASCHINE:
                $oStat_arr = $oStatistik->holeBotStats();
                break;

            // Top besuchte Seiten
            case STATS_ADMIN_TYPE_UMSATZ:
                $oStat_arr = $oStatistik->holeUmsatzStats();
                break;

            // Suchbegriffe
            case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
                $oStat_arr = $oStatistik->holeEinstiegsseiten();
                break;
        }

        return $oStat_arr;
    }

    return [];
}

/**
 * @param int $nTagVon
 * @param int $nMonatVon
 * @param int $nJahrVon
 * @param int $nTagBis
 * @param int $nMonatBis
 * @param int $nJahrBis
 * @return bool
 */
function statsDatumPlausi($nTagVon, $nMonatVon, $nJahrVon, $nTagBis, $nMonatBis, $nJahrBis)
{
    if ($nTagVon <= 0 || $nTagVon > 31) {
        return false;
    }
    if ($nMonatVon <= 0 || $nMonatVon > 12) {
        return false;
    }
    if ($nJahrVon <= 0) {
        return false;
    }
    if ($nTagBis <= 0 || $nTagBis > 31) {
        return false;
    }
    if ($nMonatBis <= 0 || $nMonatBis > 12) {
        return false;
    }
    if ($nJahrBis <= 0) {
        return false;
    }

    return true;
}

/**
 * @param int $nZeitraum
 * @return stdClass
 */
function berechneStatZeitraum($nZeitraum)
{
    $oZeit                = new stdClass();
    $oZeit->nDateStampVon = 0;
    $oZeit->nDateStampBis = 0;
    if ((int)$nZeitraum > 0) {
        switch ($nZeitraum) {
            // Heute
            case 1:
                $oZeit->nDateStampVon = mktime(0, 0, 0, (int)date('n'), (int)date('j'), (int)date('Y'));
                $oZeit->nDateStampBis = mktime(23, 59, 59, (int)date('n'), (int)date('j'), (int)date('Y'));
                break;

            // diese Woche
            case 2:
                $nDatum_arr           = ermittleDatumWoche(date('Y') . '-' . date('m') . '-' . date('d'));
                $oZeit->nDateStampVon = $nDatum_arr[0];
                $oZeit->nDateStampBis = $nDatum_arr[1];
                break;

            // letzte Woche
            case 3:
                $nTag   = (int)date('d') - 7;
                $nMonat = (int)date('m');
                $nJahr  = (int)date('Y');
                if ($nTag < 1) {
                    $nMonat--;
                    if ($nMonat < 1) {
                        $nMonat = 12;
                        $nJahr--;
                    }

                    $nTag = (int)date('t', mktime(0, 0, 0, $nMonat, 1, $nJahr));
                }

                $nDatum_arr           = ermittleDatumWoche($nJahr . '-' . $nMonat . '-' . $nTag);
                $oZeit->nDateStampVon = $nDatum_arr[0];
                $oZeit->nDateStampBis = $nDatum_arr[1];
                break;

            // diesen Monat
            case 4:
                $oZeit->nDateStampVon = firstDayOfMonth();
                $oZeit->nDateStampBis = lastDayOfMonth();
                break;

            // letzten Monat
            case 5:
                $nMonat = (int)date('m') - 1;
                $nJahr  = (int)date('Y');

                if ($nMonat < 1) {
                    $nMonat = 12;
                    $nJahr--;
                }

                $oZeit->nDateStampVon = firstDayOfMonth($nMonat, $nJahr);
                $oZeit->nDateStampBis = lastDayOfMonth($nMonat, $nJahr);
                break;

            // dieses Jahr
            case 6:
                $oZeit->nDateStampVon = mktime(0, 0, 0, 1, 1, (int)date('Y'));
                $oZeit->nDateStampBis = mktime(23, 59, 59, 12, 31, (int)date('Y'));
                break;

            // letztes Jahr
            case 7:
                $nJahr                = (int)date('Y') - 1;
                $oZeit->nDateStampVon = mktime(0, 0, 0, 1, 1, $nJahr);
                $oZeit->nDateStampBis = mktime(23, 59, 59, 12, 31, $nJahr);
                break;
        }
    }

    return $oZeit;
}

/**
 * @param array $oStat_arr
 * @param int   $nAnzeigeIntervall
 * @param int   $nTyp
 * @return string|bool
 */
function getJSON($oStat_arr, $nAnzeigeIntervall, $nTyp)
{
    require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';
    $data = [];
    if (!is_array($oStat_arr) || count($oStat_arr) === 0) {
        return false;
    }
    if ((int)$nAnzeigeIntervall === 0) {
        return false;
    }
    if (!$nTyp) {
        return false;
    }
    foreach ($oStat_arr as $oStat) {
        $data[] = (int)$oStat->nCount;
    }
    // min und max berechnen
    $fMax = round((float)max($data), 2);
    $fMin = round((float)min($data), 2);
    // padding
    $fMin -= $fMin * 0.25;
    $fMax += $fMax * 0.25;
    if ($fMin <= 0) {
        $fMin = 0;
    }
    // abrunden
    $fMin  = floor($fMin);
    $fMax  = floor($fMax);
    $fStep = floor(($fMax - $fMin) / 10);

    switch ($nTyp) {
        // Besucher Stats
        case STATS_ADMIN_TYPE_BESUCHER:
            $cSpalteX = 'dZeit';
            // x achse daten
            $x_labels_arr = [];
            foreach ($oStat_arr as $oStat) {
                $x_labels_arr[] = (string) $oStat->$cSpalteX;
            }

            return setDot($data, $x_labels_arr, null, $fMin, $fMax, $fStep, 'Besucher');
            break;

        // Kundenherkunft
        case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
            $cSpalteX = 'cReferer';
            // x achse daten
            $x_labels_arr = [];
            foreach ($oStat_arr as $oStat) {
                $x_labels_arr[] = (string) $oStat->$cSpalteX;
            }

            return setPie($data, $x_labels_arr);
            break;

        // Suchmaschine
        case STATS_ADMIN_TYPE_SUCHMASCHINE:
            $cSpalteX = 'cUserAgent';
            // x achse daten
            $x_labels_arr = [];
            foreach ($oStat_arr as $oStat) {
                if (strlen($oStat->$cSpalteX) > 0) {
                    $x_labels_arr[] = (string) $oStat->$cSpalteX;
                } else {
                    $cSpalteX       = 'cName';
                    $x_labels_arr[] = (string) $oStat->$cSpalteX;
                }
            }

            return setPie($data, $x_labels_arr);
            break;

        // Umsatz
        case STATS_ADMIN_TYPE_UMSATZ:
            $cSpalteX = 'dZeit';
            // x achse daten
            $x_labels_arr = [];
            foreach ($oStat_arr as $oStat) {
                $x_labels_arr[] = (string) $oStat->$cSpalteX;
            }

            $oWaehrung = Shop::DB()->query(
                "SELECT *
                    FROM twaehrung
                    WHERE cStandard = 'Y'", 1
            );

            return setDot($data, $x_labels_arr, null, $fMin, $fMax, $fStep, $oWaehrung->cName);
            break;

        // Suchbegriffe
        case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
            $cSpalteX = 'cEinstiegsseite';
            // x achse daten
            $x_labels_arr = [];
            foreach ($oStat_arr as $oStat) {
                $x_labels_arr[] = (string) $oStat->$cSpalteX;
            }

            return setPie($data, $x_labels_arr);
            break;
    }

    return false;
}

/**
 * @param mixed  $data
 * @param array  $x_labels_arr
 * @param array  $y_labels_arr
 * @param float  $fMin
 * @param float  $fMax
 * @param float  $fStep
 * @param string $cToolTip
 * @return string
 */
function setDot($data, $x_labels_arr, $y_labels_arr, $fMin, $fMax, $fStep, $cToolTip = '')
{
    $d = new solid_dot();
    $d->size(3);
    $d->halo_size(1);
    $d->colour('#0343a3');
    $d->tooltip('#val# ' . $cToolTip);

    $area = new area();
    $area->set_width(2);
    $area->set_default_dot_style($d);
    $area->set_colour('#8cb9fd');
    $area->set_fill_colour('#8cb9fd');
    $area->set_fill_alpha(0.2);
    $area->set_values($data);
    // x achse labels
    $x_labels = new x_axis_labels();
    $x_labels->set_steps(1);
    $x_labels->set_vertical();
    $x_labels->set_colour('#000');
    $x_labels->set_labels($x_labels_arr);
    // x achse
    $x = new x_axis();
    $x->set_colour('#bfbfbf');
    $x->set_grid_colour('#f0f0f0');
    $x->set_labels($x_labels);
    // y achse
    $y = new y_axis();
    $y->set_colour('#bfbfbf');
    $y->set_grid_colour('#f0f0f0');

    $y->set_range($fMin, $fMax, $fStep);
    // chart
    $chart = new open_flash_chart();
    $chart->add_element($area);
    $chart->set_x_axis($x);
    $chart->set_y_axis($y);
    $chart->set_bg_colour('#ffffff');
    $chart->set_number_format(2, true, true, false);

    return $chart->toPrettyString();
}

/**
 * @param array $data_arr
 * @param array $x_labels_arr
 * @return string
 */
function setPie($data_arr, $x_labels_arr)
{
    $merge_arr = [];
    // Nur max. 10 Werte anzeigen, danach als Sonstiges
    foreach ($data_arr as $i => $data) {
        if ($i > 5) {
            $data_arr[5] += $data;
        }
        if ($i > 5) {
            unset($data_arr[$i]);
        }
    }
    $nValueSonstiges = isset($data_arr[5]) ? $data_arr[5] : null;
    $nPosSonstiges   = 0;
    usort($data_arr, 'cmpStat');

    foreach ($data_arr as $i => $data) {
        if ($data == $nValueSonstiges) {
            $nPosSonstiges = $i;
            break;
        }
    }
    foreach ($x_labels_arr as $j => $x_labels) {
        if ($j > 5) {
            unset($x_labels_arr[$j]);
        }
    }
    $x_labels_arr[$nPosSonstiges] = 'Sonstige';
    foreach ($data_arr as $i => $data) {
        $cLabel      = $x_labels_arr[$i] . '(' . number_format((float)$data, 0, ',', '.') . ')';
        $merge_arr[] = new pie_value($data, $cLabel);
    }

    $pie = new pie();
    $pie->set_start_angle(35);
    $pie->set_animate(true);
    $pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
    $pie->set_colours(['#1C9E05', '#D4FA00', '#9E1176', '#FF368D', '#454545']);
    $pie->set_values($merge_arr);

    $chart = new open_flash_chart();
    $chart->add_element($pie);
    $chart->set_x_axis(null);
    $chart->set_bg_colour('#ffffff');
    $chart->set_number_format(0, true, true, false);

    return $chart->toPrettyString();
}

/**
 * @param $a
 * @param $b
 * @return int
 */
function cmpStat($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a > $b) ? -1 : 1;
}

/**
 * @param int $nTyp
 * @return mixed
 */
function gibMappingDaten($nTyp)
{
    if (!$nTyp) {
        return [];
    }

    $cMapping_arr                                   = [];
    $cMapping_arr[STATS_ADMIN_TYPE_BESUCHER]        = [
        'nCount' => 'Anzahl',
        'dZeit'  => 'Datum'
    ];
    $cMapping_arr[STATS_ADMIN_TYPE_KUNDENHERKUNFT]  = [
        'nCount'   => 'Anzahl',
        'dZeit'    => 'Datum',
        'cReferer' => 'Herkunft'
    ];
    $cMapping_arr[STATS_ADMIN_TYPE_SUCHMASCHINE]    = [
        'nCount'     => 'Anzahl',
        'dZeit'      => 'Datum',
        'cUserAgent' => 'UserAgent'
    ];
    $cMapping_arr[STATS_ADMIN_TYPE_UMSATZ]          = [
        'nCount' => 'Betrag',
        'dZeit'  => 'Datum'
    ];
    $cMapping_arr[STATS_ADMIN_TYPE_EINSTIEGSSEITEN] = [
        'nCount'          => 'Anzahl',
        'dZeit'           => 'Datum',
        'cEinstiegsseite' => 'Einstiegsseite'
    ];

    return $cMapping_arr[$nTyp];
}

/**
 * @param int $type
 * @return string
 */
function GetTypeNameStats($type)
{
    $names = [
        1 => 'Besucher',
        2 => 'Kundenherkunft',
        3 => 'Suchmaschinen',
        4 => 'Umsatz',
        5 => 'Einstiegsseite'
    ];

    if (isset($names[$type])) {
        return $names[$type];
    }

    return '';
}

/**
 * @param int $type
 * @return stdClass
 */
function getAxisNames($type)
{
    $axis    = new stdClass();
    $axis->y = 'nCount';
    switch ($type) {
        case STATS_ADMIN_TYPE_BESUCHER:
            $axis->x = 'dZeit';
            break;
        case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
            $axis->x = 'cReferer';
            break;
        case STATS_ADMIN_TYPE_SUCHMASCHINE:
            $axis->x = 'cUserAgent';
            break;
        case STATS_ADMIN_TYPE_UMSATZ:
            $axis->x = 'dZeit';
            break;
        case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
            $axis->x = 'cEinstiegsseite';
            break;
    }

    return $axis;
}

/**
 * @param array $cMemberRow_arr
 * @param array $cMapping_arr
 * @return array
 */
function mappeDatenMember($cMemberRow_arr, $cMapping_arr)
{
    if (is_array($cMemberRow_arr) && count($cMemberRow_arr) > 0) {
        foreach ($cMemberRow_arr as $i => $cMember_arr) {
            foreach ($cMember_arr as $j => $cMember) {
                $cMemberRow_arr[$i][$j]    = [];
                $cMemberRow_arr[$i][$j][0] = $cMember;
                $cMemberRow_arr[$i][$j][1] = $cMapping_arr[$cMember];
            }
        }
    }

    return $cMemberRow_arr;
}

/**
 * @param array  $stats
 * @param string $name
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 */
function prepareLineChartStats($stats, $name = 'Serie', $axis, $mod = 1)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Linechart.php';

    $chart = new Linechart(['active' => false]);

    if (is_array($stats) && count($stats) > 0) {
        $chart->setActive(true);
        $data = [];
        $y    = $axis->y;
        $x    = $axis->x;
        foreach ($stats as $j => $stat) {
            $obj    = new stdClass();
            $obj->y = (float)$stat->$y;

            if ($j % $mod === 0) {
                $chart->addAxis($stat->$x);
            } else {
                $chart->addAxis('|');
            }

            $data[] = $obj;
        }

        $chart->addSerie($name, $data);
        $chart->memberToJSON();
    }

    return $chart;
}

/**
 * @param array  $stats
 * @param string $name
 * @param object $axis
 * @param int    $maxEntries
 * @return Piechart
 */
function preparePieChartStats($stats, $name = 'Serie', $axis, $maxEntries = 6)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Piechart.php';

    $chart = new Piechart(['active' => false]);

    if (is_array($stats) && count($stats) > 0) {
        $chart->setActive(true);
        $data = [];

        $y = $axis->y;
        $x = $axis->x;

        // Zeige nur $maxEntries Main Member + 1 Sonstige an, sonst wird es zu unuebersichtlich
        if (count($stats) > $maxEntries) {
            $statstmp  = [];
            $other     = new stdClass();
            $other->$y = 0;
            $other->$x = 'Sonstige';
            foreach ($stats as $i => $stat) {
                if ($i < $maxEntries) {
                    $statstmp[] = $stat;
                } else {
                    $other->$y += $stat->$y;
                }
            }

            $statstmp[] = $other;
            $stats      = $statstmp;
        }

        foreach ($stats as $stat) {
            $value  = (float) $stat->$y;
            $data[] = [$stat->$x, $value];
        }

        $chart->addSerie($name, $data);
        $chart->memberToJSON();
    }

    return $chart;
}

/**
 * @param array  $Series
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 */
function prepareLineChartStatsMulti($Series, $axis, $mod = 1)
{
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Linechart.php';

    $chart = new Linechart(['active' => false]);
    if (is_array($Series) && count($Series) > 0) {
        $i = 0;
        foreach ($Series as $Name => $Serie) {
            if (is_array($Serie) && count($Serie) > 0) {
                $chart->setActive(true);
                $data = [];
                $y    = $axis->y;
                $x    = $axis->x;
                foreach ($Serie as $j => $stat) {
                    $obj    = new stdClass();
                    $obj->y = (float) $stat->$y;

                    if ($j % $mod === 0) {
                        $chart->addAxis($stat->$x);
                    } else {
                        $chart->addAxis('|');
                    }

                    $data[] = $obj;
                }

                $Colors = GetLineChartColors($i);
                $chart->addSerie($Name, $data, $Colors[0], $Colors[1]);
                $chart->memberToJSON();
            }

            $i++;
        }
    }

    return $chart;
}

/**
 * @param int $Number
 * @return mixed
 */
function GetLineChartColors($Number)
{
    $Colors = [
        ['#EDEDED', '#EDEDED'],
        ['#989898', '#F78D23']
    ];

    if (isset($Colors[$Number])) {
        return $Colors[$Number];
    }

    return $Colors[0];
}
