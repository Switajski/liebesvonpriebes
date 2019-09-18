<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param $index
 * @param $create
 * @return array|IOError
 */
function createSearchIndex($index, $create)
{
    require_once PFAD_ROOT . PFAD_INCLUDES . 'suche_inc.php';

    $index    = strtolower(StringHandler::xssClean($index));
    $cHinweis = '';
    $cFehler  = '';

    if (!in_array($index, ['tartikel', 'tartikelsprache'], true)) {
        return new IOError('Ungültiger Index angegeben', 403);
    }

    try {
        if (Shop::DB()->query("SHOW INDEX FROM $index WHERE KEY_NAME = 'idx_{$index}_fulltext'", 1)) {
            Shop::DB()->executeQuery("ALTER TABLE $index DROP KEY idx_{$index}_fulltext", 10);
        }
    } catch (Exception $e) {
        // Fehler beim Index löschen ignorieren
        null;
    }

    if ($create === 'Y') {
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
                return new IOError('Ungültiger Index angegeben', 403);
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
                saveAdminSectionSettings(CONF_ARTIKELUEBERSICHT, $settings);

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

    return $cFehler !== '' ? new IOError($cFehler) : ['hinweis' => $cHinweis];
}

/**
 * @return array|IOError
 */
function clearSearchCache()
{
    Shop::DB()->executeQuery("DELETE FROM tsuchcachetreffer", 10);
    Shop::DB()->executeQuery("DELETE FROM tsuchcache", 10);

    return ['hinweis' => 'Der Such-Cache wurde gelöscht'];
}
