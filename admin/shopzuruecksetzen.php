<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';

$oAccount->permission('RESET_SHOP_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
if (isset($_POST['zuruecksetzen']) && (int)$_POST['zuruecksetzen'] === 1 && validateToken()) {
    $cOption_arr = $_POST['cOption_arr'];
    if (is_array($cOption_arr) && count($cOption_arr) > 0) {
        foreach ($cOption_arr as $cOption) {
            switch ($cOption) {
                // JTL-Wawi Inhalte
                case 'artikel':
                    Shop::DB()->query("TRUNCATE tartikel", 4);
                    Shop::DB()->query("TRUNCATE tartikelabnahme", 4);
                    Shop::DB()->query("TRUNCATE tartikelattribut", 4);
                    Shop::DB()->query("TRUNCATE tartikelkategorierabatt", 4);
                    Shop::DB()->query("TRUNCATE tartikelkonfiggruppe", 4);
                    Shop::DB()->query("TRUNCATE tartikelmerkmal", 4);
                    Shop::DB()->query("TRUNCATE tartikelpict", 4);
                    Shop::DB()->query("TRUNCATE tartikelsichtbarkeit", 4);
                    Shop::DB()->query("TRUNCATE tartikelsonderpreis", 4);
                    Shop::DB()->query("TRUNCATE tartikelsprache", 4);
                    Shop::DB()->query("TRUNCATE tartikelwarenlager", 4);
                    Shop::DB()->query("TRUNCATE tattribut", 4);
                    Shop::DB()->query("TRUNCATE tattributsprache", 4);
                    Shop::DB()->query("TRUNCATE tbild", 4);
                    Shop::DB()->query("TRUNCATE teigenschaft", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftkombiwert", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftsichtbarkeit", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftsprache", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftwert", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftwertabhaengigkeit", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftwertaufpreis", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftwertpict", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftwertsichtbarkeit", 4);
                    Shop::DB()->query("TRUNCATE teigenschaftwertsprache", 4);
                    Shop::DB()->query("TRUNCATE teinheit", 4);
                    Shop::DB()->query("TRUNCATE tkategorie", 4);
                    Shop::DB()->query("TRUNCATE tkategorieartikel", 4);
                    Shop::DB()->query("TRUNCATE tkategorieattribut", 4);
                    Shop::DB()->query("TRUNCATE tkategorieattributsprache", 4);
                    Shop::DB()->query("TRUNCATE tkategoriekundengruppe", 4);
                    Shop::DB()->query("TRUNCATE tkategoriemapping", 4);
                    Shop::DB()->query("TRUNCATE tkategoriepict", 4);
                    Shop::DB()->query("TRUNCATE tkategoriesichtbarkeit", 4);
                    Shop::DB()->query("TRUNCATE tkategoriesprache", 4);
                    Shop::DB()->query("TRUNCATE tmediendatei", 4);
                    Shop::DB()->query("TRUNCATE tmediendateiattribut", 4);
                    Shop::DB()->query("TRUNCATE tmediendateisprache", 4);
                    Shop::DB()->query("TRUNCATE tmerkmal", 4);
                    Shop::DB()->query("TRUNCATE tmerkmalsprache", 4);
                    Shop::DB()->query("TRUNCATE tmerkmalwert", 4);
                    Shop::DB()->query("TRUNCATE tmerkmalwertbild", 4);
                    Shop::DB()->query("TRUNCATE tmerkmalwertsprache", 4);
                    Shop::DB()->query("TRUNCATE tpreise", 4);
                    Shop::DB()->query("TRUNCATE tpreis", 4);
                    Shop::DB()->query("TRUNCATE tpreisdetail", 4);
                    Shop::DB()->query("TRUNCATE tsonderpreise", 4);
                    Shop::DB()->query("TRUNCATE txsell", 4);
                    Shop::DB()->query("TRUNCATE txsellgruppe", 4);
                    Shop::DB()->query("TRUNCATE thersteller", 4);
                    Shop::DB()->query("TRUNCATE therstellersprache", 4);
                    Shop::DB()->query("TRUNCATE tlieferstatus", 4);
                    Shop::DB()->query("TRUNCATE tkonfiggruppe", 4);
                    Shop::DB()->query("TRUNCATE tkonfigitem", 4);
                    Shop::DB()->query("TRUNCATE tkonfiggruppesprache", 4);
                    Shop::DB()->query("TRUNCATE tkonfigitempreis", 4);
                    Shop::DB()->query("TRUNCATE tkonfigitemsprache", 4);
                    Shop::DB()->query("TRUNCATE twarenlager", 4);
                    Shop::DB()->query("TRUNCATE twarenlagersprache", 4);

                    Shop::DB()->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kArtikel'
                            OR cKey = 'kKategorie'
                            OR cKey = 'kMerkmalWert'
                            OR cKey = 'kHersteller'", 4
                    );
                    break;

                case 'revisions':
                    Shop::DB()->query("TRUNCATE trevisions", 4);
                    break;

                // Shopinhalte
                case 'news':
                    $_index = Shop::DB()->query("SELECT kNews FROM tnews;", 2);
                    foreach ($_index as $i) {
                        loescheNewsBilderDir($i->kNews, PFAD_ROOT . PFAD_NEWSBILDER);
                    }
                    Shop::DB()->query("TRUNCATE tnews", 4);
                    Shop::DB()->delete('trevisions', 'type', 'news', 4);
                    Shop::DB()->query("TRUNCATE tnewskategorie", 4);
                    Shop::DB()->query("TRUNCATE tnewskategorienews", 4);
                    Shop::DB()->query("TRUNCATE tnewskommentar", 4);
                    Shop::DB()->query("TRUNCATE tnewsmonatsuebersicht", 4);

                    Shop::DB()->query(
                        "DELETE FROM tseo
                            WHERE cKey = 'kNews'
                              OR cKey = 'kNewsKategorie'
                              OR cKey = 'kNewsMonatsUebersicht'", 4
                    );
                    break;

                case 'bestseller':
                    Shop::DB()->query("TRUNCATE tbestseller", 4);
                    break;

                case 'besucherstatistiken':
                    Shop::DB()->query("TRUNCATE tbesucher", 4);
                    Shop::DB()->query("TRUNCATE tbesucherarchiv", 4);
                    Shop::DB()->query("TRUNCATE tbesuchteseiten", 4);
                    break;

                case 'preisverlaeufe':
                    Shop::DB()->query("TRUNCATE tpreisverlauf", 4);
                    break;

                case 'umfragen':
                    Shop::DB()->query("TRUNCATE tumfrage", 4);
                    Shop::DB()->query("TRUNCATE tumfragedurchfuehrung", 4);
                    Shop::DB()->query("TRUNCATE tumfragedurchfuehrungantwort", 4);
                    Shop::DB()->query("TRUNCATE tumfragefrage", 4);
                    Shop::DB()->query("TRUNCATE tumfragefrageantwort", 4);
                    Shop::DB()->query("TRUNCATE tumfragematrixoption", 4);

                    Shop::DB()->delete('tseo', 'cKey', 'kUmfrage');
                    break;

                case 'verfuegbarkeitsbenachrichtigungen':
                    Shop::DB()->query("TRUNCATE tverfuegbarkeitsbenachrichtigung", 4);
                    break;

                // Benutzergenerierte Inhalte
                case 'suchanfragen':
                    Shop::DB()->query("TRUNCATE tsuchanfrage", 4);
                    Shop::DB()->query("TRUNCATE tsuchanfrageerfolglos", 4);
                    Shop::DB()->query("TRUNCATE tsuchanfragemapping", 4);
                    Shop::DB()->query("TRUNCATE tsuchanfragencache", 4);
                    Shop::DB()->query("TRUNCATE tsuchcache", 4);
                    Shop::DB()->query("TRUNCATE tsuchcachetreffer", 4);

                    Shop::DB()->delete('tseo', 'cKey', 'kSuchanfrage');
                    break;

                case 'tags':
                    Shop::DB()->query("TRUNCATE ttagmapping", 4);
                    Shop::DB()->query("TRUNCATE ttag", 4);
                    Shop::DB()->query("TRUNCATE ttagartikel", 4);
                    Shop::DB()->query("TRUNCATE ttagkunde", 4);

                    Shop::DB()->delete('tseo', 'cKey', 'kTag');
                    break;

                case 'bewertungen':
                    Shop::DB()->query("TRUNCATE tartikelext", 4);
                    Shop::DB()->query("TRUNCATE tbewertung", 4);
                    Shop::DB()->query("TRUNCATE tbewertungguthabenbonus", 4);
                    Shop::DB()->query("TRUNCATE tbewertunghilfreich", 4);
                    break;

                // Shopkunden & Kunden werben Kunden & Bestellungen & Kupons
                case 'shopkunden':
                    Shop::DB()->query("TRUNCATE tkunde", 4);
                    Shop::DB()->query("TRUNCATE tkundenattribut", 4);
                    Shop::DB()->query("TRUNCATE tkundendatenhistory", 4);
                    Shop::DB()->query("TRUNCATE tkundenfeld", 4);
                    Shop::DB()->query("TRUNCATE tkundenfeldwert", 4);
                    Shop::DB()->query("TRUNCATE tkundenherkunft", 4);
                    Shop::DB()->query("TRUNCATE tkundenkontodaten", 4);
                    Shop::DB()->query("TRUNCATE tkundenwerbenkunden", 4);
                    Shop::DB()->query("TRUNCATE tlieferadresse", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbpers", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbperspos", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbpersposeigenschaft", 4);
                    Shop::DB()->query("TRUNCATE twunschliste", 4);
                    Shop::DB()->query("TRUNCATE twunschlistepos", 4);
                    Shop::DB()->query("TRUNCATE twunschlisteposeigenschaft", 4);
                    break;
                case 'kwerbenk':
                    Shop::DB()->query("TRUNCATE tkundenwerbenkunden", 4);
                    Shop::DB()->query("TRUNCATE tkundenwerbenkundenbonus", 4);
                    break;
                case 'bestellungen':
                    Shop::DB()->query("TRUNCATE tbestellid", 4);
                    Shop::DB()->query("TRUNCATE tbestellstatus", 4);
                    Shop::DB()->query("TRUNCATE tbestellung", 4);
                    Shop::DB()->query("TRUNCATE tlieferschein", 4);
                    Shop::DB()->query("TRUNCATE tlieferscheinpos", 4);
                    Shop::DB()->query("TRUNCATE tlieferscheinposinfo", 4);
                    Shop::DB()->query("TRUNCATE twarenkorb", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbpers", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbperspos", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbpersposeigenschaft", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbpos", 4);
                    Shop::DB()->query("TRUNCATE twarenkorbposeigenschaft", 4);
                    break;
                case 'kupons':
                    Shop::DB()->query("TRUNCATE tkupon", 4);
                    Shop::DB()->query("TRUNCATE tkuponbestellung", 4);
                    Shop::DB()->query("TRUNCATE tkuponkunde", 4);
                    Shop::DB()->query("TRUNCATE tkuponneukunde", 4);
                    Shop::DB()->query("TRUNCATE tkuponsprache", 4);
                    break;
            }
        }
        Shop::Cache()->flushAll();
        Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
        $cHinweis = 'Der Shop wurde mit Ihren gew&auml;hlten Optionen zur&uuml;ckgesetzt.';
    } else {
        $cFehler = 'Bitte w&auml;hlen Sie mindestens eine Option aus.';
    }

    executeHook(HOOK_BACKEND_SHOP_RESET_AFTER);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->display('shopzuruecksetzen.tpl');
