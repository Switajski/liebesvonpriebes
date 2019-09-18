<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

$oAccount->permission('SETTINGS_ARTICLEOVERVIEW_VIEW', true, true);
/** @global JTLSmarty $smarty */
$kSektion         = CONF_ARTIKELUEBERSICHT;
$Einstellungen    = Shop::getSettings([$kSektion]);
$standardwaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
$step             = 'einstellungen bearbeiten';
$cHinweis         = '';
$cFehler          = '';
$Conf             = [];

if (isset($_GET['action']) && $_GET['action'] === 'createIndex') {
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-type: application/json');

    $index = strtolower(StringHandler::xssClean($_GET['index']));

    if (!in_array($index, ['tartikel', 'tartikelsprache'], true)) {
        header(makeHTTPHeader(403), true);
        echo json_encode((object)['error' => 'Ungültiger Index angegeben']);
        exit;
    }

    try {
        if (Shop::DB()->query("SHOW INDEX FROM $index WHERE KEY_NAME = 'idx_{$index}_fulltext'", 1)) {
            Shop::DB()->executeQuery("ALTER TABLE $index DROP KEY idx_{$index}_fulltext", 10);
        }
    } catch (Exception $e) {
        // Fehler beim Index löschen ignorieren
        null;
    }

    if ($_GET['create'] === 'Y') {
        $cSuchspalten_arr = array_map(function ($item) {
            $item_arr = explode('.', $item, 2);

            return $item_arr[1];
        }, gibSuchSpalten());

        switch ($index) {
            case 'tartikel':
                $cSpalten_arr = array_intersect(
                    $cSuchspalten_arr,
                    ['cName', 'cSeo', 'cSuchbegriffe', 'cArtNr', 'cKurzBeschreibung', 'cBeschreibung', 'cBarcode', 'cISBN', 'cHAN', 'cAnmerkung']
                );
                break;
            case 'tartikelsprache':
                $cSpalten_arr = array_intersect($cSuchspalten_arr, ['cName', 'cSeo', 'cKurzBeschreibung', 'cBeschreibung']);
                break;
            default:
                header(makeHTTPHeader(403), true);
                echo json_encode((object)['error' => 'Ungültiger Index angegeben']);
                exit;
        }

        try {
            Shop::DB()->executeQuery(
                "UPDATE tsuchcache SET dGueltigBis = DATE_ADD(NOW(), INTERVAL 10 MINUTE)",
                10
            );

            $res = Shop::DB()->executeQuery(
                "ALTER TABLE $index
                    ADD FULLTEXT KEY idx_{$index}_fulltext (" . implode(', ', $cSpalten_arr) . ")",
                10
            );
        } catch (Exception $e) {
            $res = 0;
        }

        if ($res === 0) {
            $cFehler      = 'Der Index für die Volltextsuche konnte nicht angelegt werden! Die Volltextsuche wird deaktiviert.';
            $shopSettings = Shopsetting::getInstance();
            $settings     = $shopSettings[Shopsetting::mapSettingName(CONF_ARTIKELUEBERSICHT)];

            if ($settings['suche_fulltext'] !== 'N') {
                $settings['suche_fulltext'] = 'N';
                saveAdminSectionSettings($kSektion, $settings);

                Shop::Cache()->flushTags([
                    CACHING_GROUP_OPTION,
                    CACHING_GROUP_CORE,
                    CACHING_GROUP_ARTICLE,
                    CACHING_GROUP_CATEGORY
                ]);
                $shopSettings->reset();
            }
        } else {
            $cHinweis = 'Der Volltextindex für ' . $index . ' wurde angelegt!';
        }
    } else {
        $cHinweis = 'Der Volltextindex für ' . $index . ' wurde gelöscht!';
    }

    header(makeHTTPHeader(200), true);
    echo json_encode((object)['error' => $cFehler, 'hinweis' => $cHinweis]);
    exit;
}

if (isset($_POST['einstellungen_bearbeiten']) && (int)$_POST['einstellungen_bearbeiten'] === 1 && $kSektion > 0 && validateToken()) {
    $sucheFulltext = isset($_POST['suche_fulltext']) ? in_array($_POST['suche_fulltext'], ['Y', 'B'], true) : false;

    if ($sucheFulltext) {
        // Bei Volltextsuche die Mindeswortlänge an den DB-Parameter anpassen
        $oValue                     = Shop::DB()->query('select @@ft_min_word_len AS ft_min_word_len', 1);
        $_POST['suche_min_zeichen'] = $oValue ? $oValue->ft_min_word_len : $_POST['suche_min_zeichen'];
    }

    $shopSettings  = Shopsetting::getInstance();
    $cHinweis     .= saveAdminSectionSettings($kSektion, $_POST);

    Shop::Cache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE, CACHING_GROUP_ARTICLE, CACHING_GROUP_CATEGORY]);
    $shopSettings->reset();

    $fulltextChanged = false;
    foreach ([
            'suche_fulltext',
            'suche_prio_name',
            'suche_prio_suchbegriffe',
            'suche_prio_artikelnummer',
            'suche_prio_kurzbeschreibung',
            'suche_prio_beschreibung',
            'suche_prio_ean',
            'suche_prio_isbn',
            'suche_prio_han',
            'suche_prio_anmerkung'
        ] as $sucheParam) {
        if ($_POST[$sucheParam] != $Einstellungen['artikeluebersicht'][$sucheParam]) {
            $fulltextChanged = true;
            break;
        }
    }
    if ($fulltextChanged) {
        $smarty->assign('createIndex', $sucheFulltext ? 'Y' : 'N');
    } else {
        $smarty->assign('createIndex', false);
    }

    if ($sucheFulltext && $fulltextChanged) {
        $cHinweis .= ' Volltextsuche wurde aktiviert.';
    } elseif ($fulltextChanged) {
        $cHinweis .= ' Volltextsuche wurde deaktiviert.';
    }

    $Einstellungen = Shop::getSettings([$kSektion]);
} else {
    $smarty->assign('createIndex', false);
}

$section = Shop::DB()->select('teinstellungensektion', 'kEinstellungenSektion', $kSektion);
$Conf    = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE nModul = 0 
            AND kEinstellungenSektion = $kSektion
        ORDER BY nSort", 2
);

$configCount = count($Conf);
for ($i = 0; $i < $configCount; $i++) {
    if (in_array($Conf[$i]->cInputTyp, ['selectbox', 'listbox'], true)) {
        $Conf[$i]->ConfWerte = Shop::DB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$Conf[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
    }

    if (isset($Conf[$i]->cWertName)) {
        $Conf[$i]->gesetzterWert = $Einstellungen['artikeluebersicht'][$Conf[$i]->cWertName];
    }
}

if ($Einstellungen['artikeluebersicht']['suche_fulltext'] !== 'N'
    && (!Shop::DB()->query("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'", 1)
        || !Shop::DB()->query("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'", 1))) {
    $cFehler = 'Der Volltextindex ist nicht vorhanden! ' .
        'Die Erstellung des Index kann jedoch einige Zeit in Anspruch nehmen. ' .
        '<a href="sucheinstellungen.php" title="Aktualisieren"><i class="alert-danger fa fa-refresh"></i></a>';
    Notification::getInstance()->add(NotificationEntry::TYPE_WARNING, 'Der Volltextindex wird erstellt!', 'sucheinstellungen.php');
}

$smarty->configLoad('german.conf', 'einstellungen')
    ->assign('action', 'sucheinstellungen.php')
    ->assign('kEinstellungenSektion', $kSektion)
    ->assign('Sektion', $section)
    ->assign('Conf', $Conf)
    ->assign('cPrefDesc', $smarty->getConfigVars('prefDesc' . $kSektion))
    ->assign('cPrefURL', $smarty->getConfigVars('prefURL' . $kSektion))
    ->assign('step', $step)
    ->assign('cHinweis', $cHinweis)
    ->assign('cFehler', $cFehler)
    ->assign('waehrung', $standardwaehrung->cName)
    ->display('sucheinstellungen.tpl');
