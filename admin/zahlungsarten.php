<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('ORDER_PAYMENT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'zahlungsarten_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
/** @global JTLSmarty $smarty */
$standardwaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');
$hinweis          = '';
$step             = 'uebersicht';
// Check Nutzbar
if (verifyGPCDataInteger('checkNutzbar') === 1) {
    pruefeZahlungsartNutzbarkeit();
    $hinweis = 'Ihre Zahlungsarten wurden auf Nutzbarkeit gepr&uuml;ft.';
}
// reset log
if (($action = verifyGPDataString('a')) !== '' &&
    ($kZahlungsart = verifyGPCDataInteger('kZahlungsart')) > 0 &&
    $action === 'logreset' && validateToken()) {
    $oZahlungsart = Shop::DB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

    if (isset($oZahlungsart->cModulId) && strlen($oZahlungsart->cModulId) > 0) {
        require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ZahlungsLog.php';
        $oZahlungsLog = new ZahlungsLog($oZahlungsart->cModulId);
        $oZahlungsLog->loeschen();

        $hinweis = 'Der Fehlerlog von ' . $oZahlungsart->cName . ' wurde erfolgreich zur&uuml;ckgesetzt.';
    }
}
if (verifyGPCDataInteger('kZahlungsart') > 0 && $action !== 'logreset' && validateToken()) {
    if ($action === 'payments') {
        // Zahlungseingaenge
        $step = 'payments';
    } elseif ($action === 'log') {
        // Log einsehen
        $step = 'log';
    } else {
        $step = 'einstellen';
    }
}

if (isset($_POST['einstellungen_bearbeiten'], $_POST['kZahlungsart']) &&
    (int)$_POST['einstellungen_bearbeiten'] === 1 && (int)$_POST['kZahlungsart'] > 0 && validateToken()) {
    $step              = 'uebersicht';
    $zahlungsart       = Shop::DB()->select('tzahlungsart', 'kZahlungsart', (int)$_POST['kZahlungsart']);
    $nMailSenden       = (int)$_POST['nMailSenden'];
    $nMailSendenStorno = (int)$_POST['nMailSendenStorno'];
    $nMailBits         = 0;
    if (is_array($_POST['kKundengruppe'])) {
        $cKundengruppen = StringHandler::createSSK($_POST['kKundengruppe']);
        if (in_array(0, $_POST['kKundengruppe'])) {
            unset($cKundengruppen);
        }
    }
    if ($nMailSenden) {
        $nMailBits |= ZAHLUNGSART_MAIL_EINGANG;
    }
    if ($nMailSendenStorno) {
        $nMailBits |= ZAHLUNGSART_MAIL_STORNO;
    }
    if (!isset($cKundengruppen)) {
        $cKundengruppen = '';
    }

    $nWaehrendBestellung = isset($_POST['nWaehrendBestellung'])
        ? (int)$_POST['nWaehrendBestellung']
        : $zahlungsart->nWaehrendBestellung;

    $upd                      = new stdClass();
    $upd->cKundengruppen      = $cKundengruppen;
    $upd->nSort               = (int)$_POST['nSort'];
    $upd->nMailSenden         = $nMailBits;
    $upd->cBild               = $_POST['cBild'];
    $upd->nWaehrendBestellung = $nWaehrendBestellung;
    Shop::DB()->update('tzahlungsart', 'kZahlungsart', (int)$zahlungsart->kZahlungsart, $upd);
    // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
    if (strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
        $kPlugin     = gibkPluginAuscModulId($zahlungsart->cModulId);
        $cModulId    = gibPlugincModulId($kPlugin, $zahlungsart->cName);
        $Conf        = Shop::DB()->query("
            SELECT *
                FROM tplugineinstellungenconf
                WHERE cWertName LIKE '" . $cModulId . "\_%'
                AND cConf = 'Y' ORDER BY nSort", 2
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; $i++) {
            $aktWert          = new stdClass();
            $aktWert->kPlugin = $kPlugin;
            $aktWert->cName   = $Conf[$i]->cWertName;
            $aktWert->cWert   = $_POST[$Conf[$i]->cWertName];

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::DB()->delete('tplugineinstellungen', ['kPlugin', 'cName'], [$kPlugin, $Conf[$i]->cWertName]);
            Shop::DB()->insert('tplugineinstellungen', $aktWert);
        }
    } else {
        $Conf        = Shop::DB()->selectAll(
            'teinstellungenconf',
            ['cModulId', 'cConf'],
            [$zahlungsart->cModulId, 'Y'],
            '*',
            'nSort'
        );
        $configCount = count($Conf);
        for ($i = 0; $i < $configCount; ++$i) {
            $aktWert                        = new stdClass();
            $aktWert->cWert                 = $_POST[$Conf[$i]->cWertName];
            $aktWert->cName                 = $Conf[$i]->cWertName;
            $aktWert->kEinstellungenSektion = CONF_ZAHLUNGSARTEN;
            $aktWert->cModulId              = $zahlungsart->cModulId;

            switch ($Conf[$i]->cInputTyp) {
                case 'kommazahl':
                    $aktWert->cWert = (float)str_replace(',', '.', $aktWert->cWert);
                    break;
                case 'zahl':
                case 'number':
                    $aktWert->cWert = (int)$aktWert->cWert;
                    break;
                case 'text':
                    $aktWert->cWert = substr($aktWert->cWert, 0, 255);
                    break;
            }
            Shop::DB()->delete(
                'teinstellungen',
                ['kEinstellungenSektion', 'cName'],
                [CONF_ZAHLUNGSARTEN, $Conf[$i]->cWertName]
            );
            Shop::DB()->insert('teinstellungen', $aktWert);
        }
    }

    $sprachen = gibAlleSprachen();
    if (!isset($zahlungsartSprache)) {
        $zahlungsartSprache = new stdClass();
    }
    $zahlungsartSprache->kZahlungsart = (int)$_POST['kZahlungsart'];
    foreach ($sprachen as $sprache) {
        $zahlungsartSprache->cISOSprache = $sprache->cISO;
        $zahlungsartSprache->cName       = $zahlungsart->cName;
        if ($_POST['cName_' . $sprache->cISO]) {
            $zahlungsartSprache->cName = $_POST['cName_' . $sprache->cISO];
        }
        $zahlungsartSprache->cGebuehrname      = $_POST['cGebuehrname_' . $sprache->cISO];
        $zahlungsartSprache->cHinweisText      = $_POST['cHinweisText_' . $sprache->cISO];
        $zahlungsartSprache->cHinweisTextShop  = $_POST['cHinweisTextShop_' . $sprache->cISO];

        Shop::DB()->delete(
            'tzahlungsartsprache',
            ['kZahlungsart', 'cISOSprache'],
            [(int)$_POST['kZahlungsart'],$sprache->cISO]
        );
        Shop::DB()->insert('tzahlungsartsprache', $zahlungsartSprache);
    }

    Shop::Cache()->flushAll();
    $hinweis = 'Zahlungsart gespeichert.';
    $step    = 'uebersicht';
}

if ($step === 'einstellen') {
    $zahlungsart = Shop::DB()->select('tzahlungsart', 'kZahlungsart', verifyGPCDataInteger('kZahlungsart'));
    if ($zahlungsart === null) {
        $step    = 'uebersicht';
        $hinweis = 'Zahlungsart nicht gefunden.';
    } else {
        // Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen
        if ($zahlungsart->nSOAP == 1 || $zahlungsart->nCURL == 1 || $zahlungsart->nSOCKETS == 1) {
            aktiviereZahlungsart($zahlungsart);
        }
        // Weiche fuer eine normale Zahlungsart oder eine Zahlungsart via Plugin
        if (strpos($zahlungsart->cModulId, 'kPlugin_') !== false) {
            $kPlugin     = gibkPluginAuscModulId($zahlungsart->cModulId);
            $cModulId    = gibPlugincModulId($kPlugin, $zahlungsart->cName);
            $Conf        = Shop::DB()->query("
                SELECT *
                    FROM tplugineinstellungenconf
                    WHERE cWertName LIKE '" . $cModulId . "\_%'
                    ORDER BY nSort", 2
            );
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; ++$i) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = Shop::DB()->selectAll(
                        'tplugineinstellungenconfwerte',
                        'kPluginEinstellungenConf',
                        (int)$Conf[$i]->kPluginEinstellungenConf,
                        '*',
                        'nSort'
                    );
                }
                $setValue = Shop::DB()->select(
                    'tplugineinstellungen',
                    'kPlugin',
                    (int)$Conf[$i]->kPlugin,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = $setValue->cWert;
            }
        } else {
            $Conf        = Shop::DB()->selectAll(
                'teinstellungenconf',
                'cModulId',
                $zahlungsart->cModulId,
                '*',
                'nSort'
            );
            $configCount = count($Conf);
            for ($i = 0; $i < $configCount; ++$i) {
                if ($Conf[$i]->cInputTyp === 'selectbox') {
                    $Conf[$i]->ConfWerte = Shop::DB()->selectAll(
                        'teinstellungenconfwerte',
                        'kEinstellungenConf',
                        (int)$Conf[$i]->kEinstellungenConf,
                        '*',
                        'nSort'
                    );
                }
                $setValue = Shop::DB()->select(
                    'teinstellungen',
                    'kEinstellungenSektion',
                    CONF_ZAHLUNGSARTEN,
                    'cName',
                    $Conf[$i]->cWertName
                );
                $Conf[$i]->gesetzterWert = isset($setValue->cWert)
                    ? $setValue->cWert
                    : null;
            }
        }

        $kundengruppen = Shop::DB()->query("SELECT * FROM tkundengruppe ORDER BY cName", 2);
        $smarty->assign('Conf', $Conf)
               ->assign('zahlungsart', $zahlungsart)
               ->assign('kundengruppen', $kundengruppen)
               ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($zahlungsart))
               ->assign('sprachen', gibAlleSprachen())
               ->assign('Zahlungsartname', getNames($zahlungsart->kZahlungsart))
               ->assign('Gebuehrname', getshippingTimeNames($zahlungsart->kZahlungsart))
               ->assign('cHinweisTexte_arr', getHinweisTexte($zahlungsart->kZahlungsart))
               ->assign('cHinweisTexteShop_arr', getHinweisTexteShop($zahlungsart->kZahlungsart))
               ->assign('ZAHLUNGSART_MAIL_EINGANG', ZAHLUNGSART_MAIL_EINGANG)
               ->assign('ZAHLUNGSART_MAIL_STORNO', ZAHLUNGSART_MAIL_STORNO);
    }
} elseif ($step === 'log') {
    require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.ZahlungsLog.php';

    $kZahlungsart = verifyGPCDataInteger('kZahlungsart');
    $oZahlungsart = Shop::DB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);

    if (isset($oZahlungsart->cModulId) && strlen($oZahlungsart->cModulId) > 0) {
        $oZahlungsLog = new ZahlungsLog($oZahlungsart->cModulId);
        $smarty->assign('oLog_arr', $oZahlungsLog->holeLog())
               ->assign('kZahlungsart', $kZahlungsart);
    }
} elseif ($step === 'payments') {
    if (isset($_POST['action'], $_POST['kEingang_arr']) &&
        $_POST['action'] === 'paymentwawireset' &&
        validateToken()
    ) {
        $kEingang_arr = $_POST['kEingang_arr'];
        array_walk($kEingang_arr, function (&$i) {
            $i = (int)$i;
        });
        Shop::DB()->query("
            UPDATE tzahlungseingang
                SET cAbgeholt = 'N'
                WHERE kZahlungseingang IN (" . implode(',', $kEingang_arr) . ")",
            10);
    }

    $kZahlungsart = verifyGPCDataInteger('kZahlungsart');

    $oFilter = new Filter('payments-' . $kZahlungsart);
    $oFilter->addTextfield(
        ['Suchbegriff', 'Sucht in Bestell-Nr., Betrag, Kunden-Vornamen, E-Mail-Adresse, Hinweis'],
        ['cBestellNr', 'fBetrag', 'cVorname', 'cMail', 'cHinweis']
    );
    $oFilter->addDaterangefield('Zeitraum', 'dZeit');
    $oFilter->assemble();

    $oZahlungsart        = Shop::DB()->select('tzahlungsart', 'kZahlungsart', $kZahlungsart);
    $oZahlunseingang_arr = Shop::DB()->query("
        SELECT ze.*, b.kZahlungsart, b.cBestellNr, k.kKunde, k.cVorname, k.cNachname, k.cMail
            FROM tzahlungseingang AS ze
                JOIN tbestellung AS b
                    ON ze.kBestellung = b.kBestellung
                JOIN tkunde AS k
                    ON b.kKunde = k.kKunde
            WHERE b.kZahlungsart = " . (int)$kZahlungsart . "
                " . ($oFilter->getWhereSQL() !== '' ? " AND " . $oFilter->getWhereSQL() : "") . "
            ORDER BY dZeit DESC",
        2);
    $oPagination         = (new Pagination('payments' . $kZahlungsart))
        ->setItemArray($oZahlunseingang_arr)
        ->assemble();

    foreach ($oZahlunseingang_arr as &$oZahlunseingang) {
        $oZahlunseingang->cNachname = entschluesselXTEA($oZahlunseingang->cNachname);
        $oZahlunseingang->dZeit     = date_create($oZahlunseingang->dZeit)->format('d.m.Y\<\b\r\>H:i');
    }

    $smarty->assign('oZahlungsart', $oZahlungsart)
           ->assign('oZahlunseingang_arr', $oPagination->getPageItems())
           ->assign('oPagination', $oPagination)
           ->assign('oFilter', $oFilter);
}

if ($step === 'uebersicht') {
    $oZahlungsart_arr = Shop::DB()->selectAll(
        'tzahlungsart',
        'nActive',
        1,
        '*',
        'cAnbieter, cName, nSort, kZahlungsart'
    );

    foreach ($oZahlungsart_arr as $oZahlungsart) {
        $oZahlungsart->nEingangAnzahl = (int)Shop::DB()->query("
                    SELECT count(*) AS nAnzahl
                        FROM tzahlungseingang AS ze
                            JOIN tbestellung AS b
                                ON ze.kBestellung = b.kBestellung
                        WHERE b.kZahlungsart = " . $oZahlungsart->kZahlungsart,
            1)->nAnzahl;

        $oZahlungsart->nLogCount = ZahlungsLog::count($oZahlungsart->cModulId);
        // jtl-shop/issues#288
        $oZahlungsart->nErrorLogCount = ZahlungsLog::count($oZahlungsart->cModulId, JTLLOG_LEVEL_ERROR);
    }

    $smarty->assign('zahlungsarten', $oZahlungsart_arr);
}
$smarty->assign('step', $step)
       ->assign('waehrung', $standardwaehrung->cName)
       ->assign('cHinweis', $hinweis)
       ->display('zahlungsarten.tpl');
