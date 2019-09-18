<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/globalinclude.php';
require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Preisverlauf.php';

// kArtikel;kKundengruppe;kSteuerklasse;fMwSt
list($_GET['kArtikel'], $_GET['kKundengruppe'], $_GET['kSteuerklasse'], $_GET['fMwSt']) = explode(';', $_GET['cOption']);

if (!isset($_GET['kKundengruppe'])) {
    $_GET['kKundengruppe'] = 1;
}
if (!isset($_GET['kSteuerklasse'])) {
    $_GET['kSteuerklasse'] = 1;
}

/**
 * @param array $data
 * @param int   $max
 * @return mixed
 */
function expandPriceArray($data, $max)
{
    for ($i = 1; $i <= $max; $i++) {
        if (!isset($data[$i]) && $i > 1) {
            $data[$i] = $data[$i - 1];
        }
    }

    return $data;
}

if (isset($_GET['kArtikel'])) {
    $session       = Session::getInstance();
    $Einstellungen = Shop::getSettings([CONF_PREISVERLAUF]);
    $kArtikel      = (int)$_GET['kArtikel'];
    $kKundengruppe = (int)$_GET['kKundengruppe'];
    $kSteuerklasse = (int)$_GET['kSteuerklasse'];
    $nMonat        = (int)$Einstellungen['preisverlauf']['preisverlauf_anzahl_monate'];

    if (count($Einstellungen) > 0) {
        $oPreisConfig           = new stdClass();
        $oPreisConfig->Waehrung = $_SESSION['Waehrung']->cName;
        $oPreisConfig->Netto    = ($_SESSION['Kundengruppe']->nNettoPreise == 1)
            ? 0
            : $_GET['fMwSt'];
        $oVerlauf     = new Preisverlauf();
        $oVerlauf_arr = $oVerlauf->gibPreisverlauf($kArtikel, $kKundengruppe, $nMonat);
        // Array drehen :D
        $oVerlauf_arr = array_reverse($oVerlauf_arr);
        $data         = [];
        foreach ($oVerlauf_arr as $oItem) {
            $fPreis = round((float)($oItem->fVKNetto + ($oItem->fVKNetto * ($oPreisConfig->Netto / 100.0))), 2);
            $data[] = $fPreis;
        }
        $d = new solid_dot();
        $d->size(3);
        $d->halo_size(1);
        $d->colour('#000');
        $d->tooltip('#val# ' . $oPreisConfig->Waehrung);

        $bar = new bar();
        $bar->set_values($data);
        $bar->set_colour('#8cb9fd');
        $bar->set_tooltip('#val# ' . $oPreisConfig->Waehrung);

        // min und max berechnen @todo: $data must contain at least one element
        $fMaxPreis = round((float)max($data), 2);
        $fMinPreis = round((float)min($data), 2);

        // x achse
        $x = new x_axis();
        $x->set_colour('#bfbfbf');
        $x->set_grid_colour('#f0f0f0');
        $x_labels = [];

        foreach ($oVerlauf_arr as $oItem) {
            $x_labels[] = date('d.m.', $oItem->timestamp);
        }
        $x->labels         = new stdClass();
        $x->labels->labels = $x_labels;

        // y achse
        $y = new y_axis();
        $y->set_colour('#bfbfbf');
        $y->set_grid_colour('#f0f0f0');
        $fMinPreis -= 10.00;
        $fMaxPreis += 10.00;
        if ($fMinPreis < 0) {
            $fMinPreis = 0;
        }
        $y->set_range((int)$fMinPreis, (int)$fMaxPreis, 10);

        // chart
        $chart = new open_flash_chart();
        $chart->add_element($bar);
        $chart->set_x_axis($x);
        $chart->set_y_axis($y);
        $chart->set_bg_colour('#ffffff');
        $chart->set_number_format(2, true, true, false);

        echo $chart->toPrettyString();
    }
}
