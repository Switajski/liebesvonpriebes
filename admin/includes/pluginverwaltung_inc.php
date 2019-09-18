<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_DBES . 'xml_tools.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

/**
 * @return array
 */
function gibInstalliertePlugins()
{
    $oPlugin_arr    = [];
    $oPluginTMP_arr = Shop::DB()->selectAll('tplugin', [], [], 'kPlugin', 'cName, cAutor, nPrio');
    if (count($oPluginTMP_arr) > 0) {
        foreach ($oPluginTMP_arr as $oPluginTMP) {
            $oPlugin_arr[] = new Plugin($oPluginTMP->kPlugin);
        }
    }

    return $oPlugin_arr;
}

/**
 * @see gibAllePlugins
 *
 * @param array $PluginInstalliert_arr
 * @param bool  $bFehlerhaft - Falls bFehlerhaft = true => gib nur fehlerhafte Plugins zurück
 * @return array - array von Plugins
 * @deprecated since 4.06 - use gibAllePlugins instead
 */
function gibVerfuegbarePlugins($PluginInstalliert_arr, $bFehlerhaft = false)
{
    static $allPlugins = null;

    if (!isset($allPlugins)) {
        $allPlugins = gibAllePlugins($PluginInstalliert_arr);
    }

    return $bFehlerhaft ? $allPlugins->fehlerhaft : $allPlugins->verfuegbar;
}

/**
 * Läuft im Ordner PFAD_ROOT/includes/plugins/ alle Verzeichnisse durch und gibt korrekte Plugins zurück
 *
 * @param array $PluginInstalliert_arr
 * @return object - {installiert[], verfuegbar[], fehlerhaft[]}
 */
function gibAllePlugins($PluginInstalliert_arr)
{
    $cPfad   = PFAD_ROOT . PFAD_PLUGIN;
    $Plugins = (object)[
        'index'       => [],
        'installiert' => [],
        'verfuegbar'  => [],
        'fehlerhaft'  => [],
    ];

    if (is_dir($cPfad)) {
        $Dir               = opendir($cPfad);
        $cInstalledPlugins = array_map(function ($item) {
            return $item->cVerzeichnis;
        }, $PluginInstalliert_arr);

        while (($cVerzeichnis = readdir($Dir)) !== false) {
            if ($cVerzeichnis !== '.' && $cVerzeichnis !== '..') {
                $cXML = $cPfad . $cVerzeichnis . '/' . PLUGIN_INFO_FILE;
                // Ist eine info.xml Datei vorhanden? Wenn nicht, ist das Plugin fehlerhaft und wird nicht angezeigt
                if (file_exists($cXML)) {
                    $xml          = StringHandler::convertISO(file_get_contents($cXML));
                    $XML_arr      = XML_unserialize($xml, 'ISO-8859-1');
                    $XML_arr      = getArrangedArray($XML_arr);
                    $nReturnValue = pluginPlausi(0, $cPfad . $cVerzeichnis);
                    if ($nReturnValue === 90 && in_array($cVerzeichnis, $cInstalledPlugins, true)) {
                        $XML_arr['cVerzeichnis']       = $cVerzeichnis;
                        $XML_arr['shop4compatible']    = isset($XML_arr['jtlshop3plugin'][0]['Shop4Version']);
                        $Plugins->index[$cVerzeichnis] = makeXMLToObj($XML_arr);
                        $Plugins->installiert[]        =& $Plugins->index[$cVerzeichnis];
                    } elseif ($nReturnValue === 126 || $nReturnValue === 1) {
                        $XML_arr['cVerzeichnis']       = $cVerzeichnis;
                        $XML_arr['shop4compatible']    = ($nReturnValue === 1);
                        $Plugins->index[$cVerzeichnis] = makeXMLToObj($XML_arr);
                        $Plugins->verfuegbar[]         =& $Plugins->index[$cVerzeichnis];
                    } elseif ($nReturnValue !== 1 && $nReturnValue !== 126) {
                        $XML_arr['cVerzeichnis']       = $cVerzeichnis;
                        $XML_arr['cFehlercode']        = $nReturnValue;
                        $Plugins->index[$cVerzeichnis] = makeXMLToObj($XML_arr);
                        $Plugins->fehlerhaft[]         = & $Plugins->index[$cVerzeichnis];
                    }
                }
            }
        }

        // Pluginsortierung nach Name
        usort($Plugins->installiert, function ($left, $right) {
            return strcasecmp($left->cName, $right->cName);
        });
        usort($Plugins->verfuegbar, function ($left, $right) {
            return strcasecmp($left->cName, $right->cName);
        });
        usort($Plugins->fehlerhaft, function ($left, $right) {
            return strcasecmp($left->cName, $right->cName);
        });
    }

    return $Plugins;
}

/*
// Plugin Plausi
// Prüft das Plugin auf Plausibilität und ändert gegebenenfalls den Status (insofern es installiert ist)
// Parameter:   kPlugin, falls vorhanden, ansonsten muss eine 0 übergeben werden
//              cVerzeichnis, gib das Verzeichnis zum Plugin an.
// Return:
// 1    = Alles O.K.
// 2    = Falsche Übergabeparameter
// 3    = Verzeichnis existiert nicht
// 4    = info.xml existiert nicht
// 5    = Kein Plugin in der DB anhand von kPlugin gefunden
// 6    = Der Pluginname entspricht nicht der Konvention
// 7    = Die PluginID entspricht nicht der Konvention
// 8    = Der Installationsknoten ist nicht vorhanden
// 9    = Erste Versionsnummer entspricht nicht der Konvention
// 10   = Die Versionsnummer entspricht nicht der Konvention
// 11   = Das Versionsdatum entspricht nicht der Konvention
// 12   = SQL Datei für die aktuelle Version existiert nicht
// 13   = Keine Hooks vorhanden
// 14   = Die Hook Werte entsprechen nicht den Konventionen
// 15   = CustomLink Name entspricht nicht der Konvention
// 16   = CustomLink Dateiname entspricht nicht der Konvention
// 17   = CustomLink Datei existiert nicht
// 18   = EinstellungsLink Name entspricht nicht der Konvention
// 19   = Einstellungen fehlen
// 20   = Einstellungen type entspricht nicht der Konvention
// 21   = Einstellungen initialValue entspricht nicht der Konvention
// 22   = Einstellungen sort entspricht nicht der Konvention
// 23   = Einstellungen Name entspricht nicht der Konvention
// 24   = Keine SelectboxOptionen vorhanden
// 25   = Die Option entspricht nicht der Konvention
// 26   = Keine Sprachvariablen vorhanden
// 27   = Variable Name entspricht nicht der Konvention
// 28   = Keine lokalisierte Sprachvariable vorhanden
// 29   = Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention
// 30   = Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention
// 31   = Die Hook Datei ist nicht vorhanden
// 33   = Einstellungen conf entspricht nicht der Konvention
// 34   = Einstellungen ValueName entspricht nicht der Konvention
// 35   = XML Version entspricht nicht der Konvention
// 36   = Shop Version entspricht nicht der Konvention
// 37   = Shop Version ist zu niedrig
// 38   = Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde
// 39   = Link Filename entspricht nicht der Konvention
// 40   = LinkName entspricht nicht der Konvention
// 41   = Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention
// 42   = Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention
// 43   = Die ISO der Linksprache entspricht nicht der Konvention
// 44   = Der Seo Name entspricht nicht der Konvention
// 45   = Der Name entspricht nicht der Konvention
// 46   = Der Title entspricht nicht der Konvention
// 47   = Der MetaTitle entspricht nicht der Konvention
// 48   = Die MetaKeywords entsprechen nicht der Konvention
// 49   = Die MetaDescription entspricht nicht der Konvention
// 50   = Der Name in den Zahlungsmethoden entspricht nicht der Konvention
// 51   = Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention
// 52   = TSCode in den Zahlungsmethoden entspricht nicht der Konvention
// 53   = PreOrder in den Zahlungsmethoden entspricht nicht der Konvention
// 54   = ClassFile in den Zahlungsmethoden entspricht nicht der Konvention
// 55   = Die Datei für die Klasse der Zahlungsmethode existiert nicht
// 56   = TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
// 57   = Die Datei für das Template der Zahlungsmethode existiert nicht
// 58   = Keine Sprachen in den Zahlungsmethoden hinterlegt
// 59   = Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention
// 60   = Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 61   = Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 62   = Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 63   = Zahlungsmethode Einstellungen type entspricht nicht der Konvention
// 64   = Zahlungsmethode Einstellungen initialValue entspricht nicht der Konvention
// 65   = Zahlungsmethode Einstellungen sort entspricht nicht der Konvention
// 66   = Zahlungsmethode Einstellungen conf entspricht nicht der Konvention
// 67   = Zahlungsmethode Einstellungen Name entspricht nicht der Konvention
// 68   = Zahlungsmethode Einstellungen ValueName entspricht nicht der Konvention
// 69   = Keine SelectboxOptionen vorhanden
// 70   = Die Option entspricht nicht der Konvention
// 71   = Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention
// 72   = Soap in den Zahlungsmethoden entspricht nicht der Konvention
// 73   = Curl in den Zahlungsmethoden entspricht nicht der Konvention
// 74   = Sockets in den Zahlungsmethoden entspricht nicht der Konvention
// 75	= ClassName in den Zahlungsmethoden entspricht nicht der Konvention
// 76	= Der Templatename entspricht nicht der Konvention
// 77	= Die Templatedatei für den Frontend Link existiert nicht
// 78	= Es darf nur ein Templatename oder ein Fullscreen Templatename existieren
// 79	= Der Fullscreen Templatename entspricht nicht der Konvention
// 80	= Die Fullscreen Templatedatei für den Frontend Link existiert nicht
// 81	= Für ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden
// 82 	= Keine Box vorhanden
// 83 	= Box Name entspricht nicht der Konvention
// 84 	= Box Templatedatei entspricht nicht der Konvention
// 85 	= Box Templatedatei existiert nicht
// 86	= Lizenzklasse existiert nicht
// 87	= Name der Lizenzklasse entspricht nicht der konvention
// 88	= Lizenklasse ist nicht definiert
// 89	= Methode checkLicence in der Lizenzklasse ist nicht definiert
// 90	= PluginID bereits in der Datenbank vorhanden
// 91	= Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde
// 92	= Template Name entspricht nicht der Konvention
// 93	= Template Type entspricht nicht der Konvention
// 94 	= Template ModulId entspricht nicht der Konvention
// 95 	= Template Active entspricht nicht der Konvention
// 96 	= Template AKZ entspricht nicht der Konvention
// 97 	= Template AGB entspricht nicht der Konvention
// 98	= Template WRB entspricht nicht der Konvention
// 99	= Die ISO der Emailtemplate Sprache entspricht nicht der Konvention
// 100	= Der Subject Name entspricht nicht der Konvention
// 101	= Keine Templatesprachen vorhanden
// 102  = CheckBoxFunction Name entspricht nicht der Konvention
// 103  = CheckBoxFunction ID entspricht nicht der Konvention
// 104  = Frontend Link Attribut NoFollow entspricht nicht der Konvention
// 105  = Keine Widgets vorhanden
// 106  = Widget Title entspricht nicht der Konvention
// 107  = Widget Class entspricht nicht der Konvention
// 108  = Die Datei für die Klasse des AdminWidgets existiert nicht
// 109  = Container im Widget entspricht nicht der Konvention
// 110  = Pos im Widget entspricht nicht der Konvention
// 111  = Expanded im Widget entspricht nicht der Konvention
// 112  = Active im Widget entspricht nicht der Konvention
// 113  = AdditionalTemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
// 114  = Die Datei für das Zusatzschritt-Template der Zahlungsmethode existiert nicht
// 115  = Keine Formate vorhanden
// 116  = Format Name entspricht nicht der Konvention
// 117  = Format Filename entspricht nicht der Konvention
// 118  = Format enthaelt weder Content, noch eine Contentdatei
// 119  = Format Encoding entspricht nicht der Konvention
// 120  = Format ShippingCostsDeliveryCountry entspricht nicht der Konvention
// 121  = Format ContenFile entspricht nicht der Konvention
// 122 	= Kein Template vorhanden
// 123 	= Templatedatei entspricht nicht der Konvention
// 124 	= Templatedatei existiert nicht
// 125  = Uninstall File existiert nicht
*/
/**
 * @param int    $kPlugin
 * @param string $cVerzeichnis
 * @return int
 */
function pluginPlausi($kPlugin, $cVerzeichnis = '')
{
    // Plugin kommt aus der Datenbank
    $kPlugin = (int)$kPlugin;
    if ($kPlugin > 0) {
        // Plugin aus der DB holen
        $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
            $cPfad = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis;
            if (is_dir($cPfad)) {
                $cInfofile = "{$cPfad}/" . PLUGIN_INFO_FILE;
                if (file_exists($cInfofile)) {
                    $xml     = StringHandler::convertISO(file_get_contents($cInfofile));
                    $XML_arr = XML_unserialize($xml, 'ISO-8859-1');
                    $XML_arr = getArrangedArray($XML_arr);
                    // Interne Plugin Plausi
                    return pluginPlausiIntern($XML_arr, PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis);
                }

                return 4; // info.xml existiert nicht
            }

            return 3; // Verzeichnis existiert nicht
        }

        return 5; // Kein Plugin in der DB anhand von kPlugin gefunden
    } elseif (strlen($cVerzeichnis) > 0) { // Plugin wird anhand des Verzeichnisses geprüft
        if (is_dir($cVerzeichnis)) {
            $cInfofile = "{$cVerzeichnis}/" . PLUGIN_INFO_FILE;
            if (file_exists($cInfofile)) {
                $xml     = StringHandler::convertISO(file_get_contents($cInfofile));
                $XML_arr = XML_unserialize($xml, 'ISO-8859-1');
                $XML_arr = getArrangedArray($XML_arr);
                // Interne Plugin Plausi
                return pluginPlausiIntern($XML_arr, $cVerzeichnis);
            }

            return 4; // info.xml existiert nicht
        }

        return 3; // Verzeichnis existiert nicht
    }

    return 2; // Falsche Übergabeparameter
}

/*
// Prüft die XML Struktur des Plugins
// Return:
// 1    = Alles O.K.
// 6    = Der Pluginname entspricht nicht der Konvention
// 7    = Die PluginID entspricht nicht der Konvention
// 8    = Der Installationsknoten ist nicht vorhanden
// 9    = Erste Versionsnummer entspricht nicht der Konvention
// 10   = Die Versionsnummer entspricht nicht der Konvention
// 11   = Das Versionsdatum entspricht nicht der Konvention
// 12   = SQL Datei für die aktuelle Version existiert nicht
// 13   = Keine Hooks vorhanden
// 14   = Die Hook Werte entsprechen nicht den Konventionen
// 15   = CustomLink Name entspricht nicht der Konvention
// 16   = CustomLink Dateiname entspricht nicht der Konvention
// 17   = CustomLink Datei existiert nicht
// 18   = EinstellungsLink Name entspricht nicht der Konvention
// 19   = Einstellungen fehlen
// 20   = Einstellungen type entspricht nicht der Konvention
// 21   = Einstellungen initialValue entspricht nicht der Konvention
// 22   = Einstellungen sort entspricht nicht der Konvention
// 23   = Einstellungen Name entspricht nicht der Konvention
// 24   = Keine SelectboxOptionen vorhanden
// 25   = Die Option entspricht nicht der Konvention
// 26   = Keine Sprachvariablen vorhanden
// 27   = Variable Name entspricht nicht der Konvention
// 28   = Keine lokalisierte Sprachvariable vorhanden
// 29   = Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention
// 30   = Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention
// 31   = Die Hook Datei ist nicht vorhanden
// 32   = Version existiert nicht im Versionsordner
// 33   = Einstellungen conf entspricht nicht der Konvention
// 34   = Einstellungen ValueName entspricht nicht der Konvention
// 35   = XML Version entspricht nicht der Konvention
// 36   = Shop Version entspricht nicht der Konvention
// 37   = Shop Version ist zu niedrig
// 38   = Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde
// 39   = Link Filename entspricht nicht der Konvention
// 40   = LinkName entspricht nicht der Konvention
// 41   = Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention
// 42   = Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention
// 43   = Die ISO der Linksprache entspricht nicht der Konvention
// 44   = Der Seo Name entspricht nicht der Konvention
// 45   = Der Name entspricht nicht der Konvention
// 46   = Der Title entspricht nicht der Konvention
// 47   = Der MetaTitle entspricht nicht der Konvention
// 48   = Die MetaKeywords entsprechen nicht der Konvention
// 49   = Die MetaDescription entspricht nicht der Konvention
// 50   = Der Name in den Zahlungsmethoden entspricht nicht der Konvention
// 51   = Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention
// 52   = TSCode in den Zahlungsmethoden entspricht nicht der Konvention
// 53   = PreOrder in den Zahlungsmethoden entspricht nicht der Konvention
// 54   = ClassFile in den Zahlungsmethoden entspricht nicht der Konvention
// 55   = Die Datei für die Klasse der Zahlungsmethode existiert nicht
// 56   = TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
// 57   = Die Datei für das Template der Zahlungsmethode existiert nicht
// 58   = Keine Sprachen in den Zahlungsmethoden hinterlegt
// 59   = Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention
// 60   = Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 61   = Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 62   = Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 63   = Zahlungsmethode Einstellungen type entspricht nicht der Konvention
// 64   = Zahlungsmethode Einstellungen initialValue entspricht nicht der Konvention
// 65   = Zahlungsmethode Einstellungen sort entspricht nicht der Konvention
// 66   = Zahlungsmethode Einstellungen conf entspricht nicht der Konvention
// 67   = Zahlungsmethode Einstellungen Name entspricht nicht der Konvention
// 68   = Zahlungsmethode Einstellungen ValueName entspricht nicht der Konvention
// 69   = Keine SelectboxOptionen vorhanden
// 70   = Die Option entspricht nicht der Konvention
// 71   = Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention
// 72   = Soap in den Zahlungsmethoden entspricht nicht der Konvention
// 73   = Curl in den Zahlungsmethoden entspricht nicht der Konvention
// 74   = Sockets in den Zahlungsmethoden entspricht nicht der Konvention
// 75	= ClassName in den Zahlungsmethoden entspricht nicht der Konvention
// 76	= Der Templatename entspricht nicht der Konvention
// 77	= Die Templatedatei für den Frontend Link existiert nicht
// 78	= Es darf nur ein Templatename oder ein Fullscreen Templatename existieren
// 79	= Der Fullscreen Templatename entspricht nicht der Konvention
// 80	= Die Fullscreen Templatedatei für den Frontend Link existiert nicht
// 81	= Für ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden
// 82 	= Keine Box vorhanden
// 83 	= Box Name entspricht nicht der Konvention
// 84 	= Box Templatedatei entspricht nicht der Konvention
// 85 	= Box Templatedatei existiert nicht
// 86	= Lizenzklasse existiert nicht
// 87	= Name der Lizenzklasse entspricht nicht der konvention
// 88	= Lizenklasse ist nicht definiert
// 89	= Methode checkLicence in der Lizenzklasse ist nicht definiert
// 90	= PluginID bereits in der Datenbank vorhanden
// 91	= Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde
// 92	= Template Name entspricht nicht der Konvention
// 93	= Template Type entspricht nicht der Konvention
// 94 	= Template ModulId entspricht nicht der Konvention
// 95 	= Template Active entspricht nicht der Konvention
// 96 	= Template AKZ entspricht nicht der Konvention
// 97 	= Template AGB entspricht nicht der Konvention
// 98	= Template WRB entspricht nicht der Konvention
// 99	= Die ISO der Emailtemplate Sprache entspricht nicht der Konvention
// 100	= Der Subject Name entspricht nicht der Konvention
// 101	= Keine Templatesprachen vorhanden
// 102  = CheckBoxFunction Name entspricht nicht der Konvention
// 103  = CheckBoxFunction ID entspricht nicht der Konvention
// 104  = Frontend Link Attribut NoFollow entspricht nicht der Konvention
// 105  = Keine Widgets vorhanden
// 106  = Widget Title entspricht nicht der Konvention
// 107  = Widget Class entspricht nicht der Konvention
// 108  = Die Datei für die Klasse des AdminWidgets existiert nicht
// 109  = Container im Widget entspricht nicht der Konvention
// 110  = Pos im Widget entspricht nicht der Konvention
// 111  = Expanded im Widget entspricht nicht der Konvention
// 112  = Active im Widget entspricht nicht der Konvention
// 113  = AdditionalTemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
// 114  = Die Datei für das Zusatzschritt-Template der Zahlungsmethode existiert nicht
// 115  = Keine Formate vorhanden
// 116  = Format Name entspricht nicht der Konvention
// 117  = Format Filename entspricht nicht der Konvention
// 118  = Format enthaelt weder Content, noch eine Contentdatei
// 119  = Format Encoding entspricht nicht der Konvention
// 120  = Format ShippingCostsDeliveryCountry entspricht nicht der Konvention
// 121  = Format ContenFile entspricht nicht der Konvention
// 122 	= Kein Template vorhanden
// 123 	= Templatedatei entspricht nicht der Konvention
// 124 	= Templatedatei existiert nicht
// 125  = Uninstall File existiert nicht
// 126  = Nicht Shop4-kompatibel, aber evtl. lauffähig
// 127  = Plugin benoetig Ioncube
// 128  = OptionsSource-Datei wurde nicht angegeben
// 129  = OptionsSource-Datei existiert nicht
// 130  = Bootstrap-Klasse existiert nicht
// 131  = Bootstrap-Klasse muss das Interface IPlugin implementieren
*/

/**
 * @param array  $XML_arr
 * @param string $cVerzeichnis
 * @return int
 */
function pluginPlausiIntern($XML_arr, $cVerzeichnis)
{
    $cVersionsnummer        = '';
    $isShop4Compatible      = false;
    $requiresMissingIoncube = false;
    $nXMLShopVersion        = 0; // Shop-Version die das Plugin braucht um zu laufen
    $nShopVersion           = 0; // Aktuelle Shop-Version
    // Shopversion holen
    $oVersion = Shop::DB()->query("SELECT nVersion FROM tversion LIMIT 1", 1);

    if ($oVersion->nVersion > 0) {
        $nShopVersion = (int)$oVersion->nVersion;
    }
    // XML-Versionsnummer
    preg_match('/[0-9]{3}/', $XML_arr['jtlshop3plugin'][0]['XMLVersion'], $cTreffer_arr);
    if (count($cTreffer_arr) === 0) {
        return 35;
    }
    if (strlen($cTreffer_arr[0]) !== strlen($XML_arr['jtlshop3plugin'][0]['XMLVersion']) &&
        (int)$XML_arr['jtlshop3plugin'][0]['XMLVersion'] >= 100) {
        return 35; //XML-Version entspricht nicht der Konvention
    }
    $nXMLVersion = (int)$XML_arr['jtlshop3plugin'][0]['XMLVersion'];
    // XML-ShopVersionsnummer
    if (empty($XML_arr['jtlshop3plugin'][0]['ShopVersion']) && empty($XML_arr['jtlshop3plugin'][0]['Shop4Version'])) {
        return 36;
    }
    if ((isset($XML_arr['jtlshop3plugin'][0]['ShopVersion']) &&
            strlen($cTreffer_arr[0]) !== strlen($XML_arr['jtlshop3plugin'][0]['ShopVersion']) &&
            (int)$XML_arr['jtlshop3plugin'][0]['ShopVersion'] >= 300) ||
        (isset($XML_arr['jtlshop3plugin'][0]['Shop4Version']) &&
            strlen($cTreffer_arr[0]) !== strlen($XML_arr['jtlshop3plugin'][0]['Shop4Version']) &&
            (int)$XML_arr['jtlshop3plugin'][0]['Shop4Version'] >= 300)) {
        return 36; //Shop-Version entspricht nicht der Konvention
    } else {
        if (isset($XML_arr['jtlshop3plugin'][0]['Shop4Version'])) {
            $nXMLShopVersion   = (int)$XML_arr['jtlshop3plugin'][0]['Shop4Version'];
            $isShop4Compatible = true;
        } else {
            $nXMLShopVersion = (int)$XML_arr['jtlshop3plugin'][0]['ShopVersion'];
        }
        //check if plugin need ioncube loader but extension is not loaded
        if (isset($XML_arr['jtlshop3plugin'][0]['LicenceClassFile']) && !extension_loaded('ionCube Loader')) {
            //ioncube is not loaded
            $nLastVersionKey    = count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1;
            $nLastPluginVersion = (int)$XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr'];
            //try to read license file
            if (file_exists($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                PFAD_PLUGIN_LICENCE . $XML_arr['jtlshop3plugin'][0]['LicenceClassFile'])
            ) {
                $content = file_get_contents($cVerzeichnis . '/' .
                    PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                    PFAD_PLUGIN_LICENCE . $XML_arr['jtlshop3plugin'][0]['LicenceClassFile']);
                //ioncube encoded files usually have a header that checks loaded extions itself
                //but it can also be in short form, where there are no opening php tags
                if ((strpos($content, 'ionCube') !== false &&
                        strpos($content, 'extension_loaded') !== false) ||
                    strpos($content, '<?php') === false) {
                    $requiresMissingIoncube = true;
                }
            }
        }
    }
    // Shop-Version ausreichend?
    if (!$nShopVersion || !$nXMLShopVersion || $nXMLShopVersion > $nShopVersion) {
        return 37; //Shop-Version ist zu niedrig
    }
    // Prüfe Pluginname
    preg_match(
        "/[a-zA-Z0-9äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\(\)_ -]+/",
        $XML_arr['jtlshop3plugin'][0]['Name'],
        $cTreffer_arr
    );
    if (strlen($cTreffer_arr[0]) === strlen($XML_arr['jtlshop3plugin'][0]['Name'])) {
        // Prüfe PluginID
        preg_match("/[a-zA-Z0-9_]+/", $XML_arr['jtlshop3plugin'][0]['PluginID'], $cTreffer_arr);
        if (strlen($cTreffer_arr[0]) === strlen($XML_arr['jtlshop3plugin'][0]['PluginID'])) {
            // Existiert die PluginID bereits?
            $oPluginTMP = Shop::DB()->select('tplugin', 'cPluginID', $XML_arr['jtlshop3plugin'][0]['PluginID']);

            if (isset($oPluginTMP->kPlugin) && $oPluginTMP->kPlugin > 0) {
                return 90; //PluginID bereits in der Datenbank vorhanden
            }

            //Finde aktuelle Version
            $nLastVersionKey    = count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1;
            $nLastPluginVersion = (int)$XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr'];

            if (isset($XML_arr['jtlshop3plugin'][0]['LicenceClassFile']) &&
                strlen($XML_arr['jtlshop3plugin'][0]['LicenceClassFile']) > 0) {
                //Existiert die Lizenzdatei?
                if (!file_exists($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                    PFAD_PLUGIN_LICENCE . $XML_arr['jtlshop3plugin'][0]['LicenceClassFile'])
                ) {
                    return 86; //Datei der Lizenzklasse existiert nicht
                }
                //Klassenname gesetzt?
                if (!isset($XML_arr['jtlshop3plugin'][0]['LicenceClass']) ||
                    strlen($XML_arr['jtlshop3plugin'][0]['LicenceClass']) === 0
                ) {
                    return 87; //Name der Lizenzklasse entspricht nicht der konvention
                }
                if ($XML_arr['jtlshop3plugin'][0]['LicenceClass'] !==
                    $XML_arr['jtlshop3plugin'][0]['PluginID'] . PLUGIN_LICENCE_CLASS
                ) {
                    return 87; //Name der Lizenzklasse entspricht nicht der konvention
                }
                if (!$requiresMissingIoncube) {
                    require_once $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $nLastPluginVersion . '/' .
                        PFAD_PLUGIN_LICENCE . $XML_arr['jtlshop3plugin'][0]['LicenceClassFile'];
                } else {
                    return 127;
                }
                // Existiert die Klasse?
                if (!class_exists($XML_arr['jtlshop3plugin'][0]['LicenceClass'])) {
                    return 88; // Lizenklasse ist nicht definiert
                }
                //Methode checkLicence defininiert?
                $cClassMethod_arr = get_class_methods($XML_arr['jtlshop3plugin'][0]['LicenceClass']);
                $bClassMethod     = false;
                if (is_array($cClassMethod_arr) &&
                    count($cClassMethod_arr) > 0 &&
                    in_array(PLUGIN_LICENCE_METHODE, $cClassMethod_arr, true)
                ) {
                    $bClassMethod = true;
                }
                if (!$bClassMethod) {
                    return 89;// Methode checkLicence in der Lizenzklasse ist nicht definiert
                }
            }

            //Prüfe Bootstrapper
            $cBootstrapNamespace = $XML_arr['jtlshop3plugin'][0]['PluginID'];
            $cBootstrapClassFile = $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION .
                $nLastPluginVersion . '/' . PLUGIN_BOOTSTRAPPER;

            if (is_file($cBootstrapClassFile)) {
                $cClass = sprintf('%s\\%s', $cBootstrapNamespace, 'Bootstrap');

                require_once $cBootstrapClassFile;

                if (!class_exists($cClass)) {
                    return 130;
                }

                $bootstrapper = new $cClass((object)['cPluginID' => $cBootstrapNamespace]);

                if (!is_subclass_of($bootstrapper, 'AbstractPlugin')) {
                    return 131;
                }
            }

            //Prüfe Install Knoten
            if (isset($XML_arr['jtlshop3plugin'][0]['Install']) && is_array($XML_arr['jtlshop3plugin'][0]['Install'])) {
                //Gibts Versionen
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']) &&
                    count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']) > 0
                ) {
                    //Ist die 1. Versionsnummer korrekt?
                    if ((int)$XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']['0 attr']['nr'] !== 100) {
                        return 9;//Erste Versionsnummer entspricht nicht der Konvention
                    }
                    //Laufe alle Versionen durch
                    foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'] as $i => $Version) {
                        preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                        preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                        if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                            $cVersionsnummer = $Version['nr'];
                            // Entpricht die Versionsnummer
                            preg_match("/[0-9]+/", $Version['nr'], $cTreffer_arr);
                            if (strlen($cTreffer_arr[0]) !== strlen($Version['nr'])) {
                                return 10; //Die Versionsnummer entspricht nicht der Konvention
                            }
                        } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                            // Prüfe SQL und CreateDate
                            if (isset($Version['SQL']) &&
                                strlen($Version['SQL']) > 0 &&
                                !file_exists($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                    PFAD_PLUGIN_SQL . $Version['SQL'])
                             ) {
                                return 12;//SQL Datei für die aktuelle Version existiert nicht
                            }
                            // Prüfe Versionsordner
                            if (!is_dir($cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer)) {
                                return 32;//Version existiert nicht im Versionsordner
                            }
                            preg_match(
                                '/[0-9]{4}-[0-1]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/',
                                $Version['CreateDate'],
                                $cTreffer_arr
                            );
                            if (strlen($cTreffer_arr[0]) !== strlen($Version['CreateDate'])) {
                                return 11;//Das Versionsdatum entspricht nicht der Konvention
                            }
                        }
                    }
                }
                //Auf Hooks prüfen
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'])) {
                    if (count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0]) === 1) {
                        //Es gibt mehr als einen Hook
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0]['Hook'] as $i => $Hook_arr) {
                            preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                            preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                                if (strlen($Hook_arr['id']) === 0) {
                                    return 14;//Die Hook -erte entsprechen nicht den Konventionen
                                }
                            } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($i)) {
                                if (strlen($Hook_arr) === 0) {
                                    return 14;//Die Hook-Werte entsprechen nicht den Konventionen
                                }
                                //Hook include Datei vorhanden?
                                if (!file_exists($cVerzeichnis . '/' .
                                    PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                    PFAD_PLUGIN_FRONTEND . $Hook_arr)
                                ) {
                                    return 31;// Die Hook-Datei ist nicht vorhanden
                                }
                            }
                        }
                    } elseif (count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0]) > 1) {
                        //Es gibt nur einen Hook
                        $Hook_arr = $XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0];
                        //Hook-Name und ID prüfen
                        if ((int)$Hook_arr['Hook attr']['id'] === 0 || strlen($Hook_arr['Hook']) === 0) {
                            return 14;//Die Hook-Werte entsprechen nicht den Konventionen
                        }
                        //Hook include Datei vorhanden?
                        if (!file_exists($cVerzeichnis . '/' .
                            PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                            PFAD_PLUGIN_FRONTEND . $Hook_arr['Hook'])
                        ) {
                            return 31;// Die Hook-Datei ist nicht vorhanden
                        }
                    }
                }
                //Plausi Adminmenü & Einstellungen (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])) {
                    //Adminsmenüs vorhanden?
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink']) > 0
                    ) {
                        $nSort = 0;
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink'] as $i => $Customlink_arr) {
                            preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                            preg_match("/[0-9]+/", $i, $cTreffer2_arr);

                            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                                $nSort = (int)$Customlink_arr['sort'];
                            } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                                // Name prüfen
                                preg_match(
                                    "/[a-zA-Z0-9äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\_\- ]+/",
                                    $Customlink_arr['Name'],
                                    $cTreffer_arr
                                );
                                if (strlen($cTreffer_arr[0]) !== strlen($Customlink_arr['Name']) || strlen($Customlink_arr['Name']) === 0) {
                                    return 15;//CustomLink Name entspricht nicht der Konvention
                                }
                                if (strlen($Customlink_arr['Filename']) > 0) {
                                    if (!file_exists($cVerzeichnis . '/' .
                                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                        PFAD_PLUGIN_ADMINMENU . $Customlink_arr['Filename'])
                                    ) {
                                        return 17;//CustomLink Datei existiert nicht
                                    }
                                } else {
                                    return 16;//CustomLink Dateiname entspricht nicht der Konvention
                                }
                            }
                        }
                    }

                    // Einstellungen vorhanden?
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink']) > 0
                    ) {
                        $nSort = 0;
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink'] as $i => $Settingslink_arr) {
                            preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                            preg_match("/[0-9]+/", $i, $cTreffer2_arr);

                            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                                $nSort = (int)$Settingslink_arr['sort'];
                            } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                                // EinstellungsLink Name prüfen
                                if (!empty($Settingslink_arr['Name']) && strlen($Settingslink_arr['Name']) > 0) {
                                    // Einstellungen prüfen
                                    $cTyp = '';
                                    if (isset($Settingslink_arr['Setting']) &&
                                        is_array($Settingslink_arr['Setting']) &&
                                        count($Settingslink_arr['Setting']) > 0
                                    ) {
                                        foreach ($Settingslink_arr['Setting'] as $j => $Setting_arr) {
                                            preg_match("/[0-9]+\sattr/", $j, $cTreffer3_arr);
                                            preg_match("/[0-9]+/", $j, $cTreffer4_arr);

                                            if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                                                $cTyp = $Setting_arr['type'];

                                                // Einstellungen type prüfen
                                                if (strlen($Setting_arr['type']) === 0) {
                                                    return 20;//Einstellungen type entspricht nicht der Konvention
                                                }
                                                // Einstellungen initialValue prüfen
                                                //if(strlen($Setting_arr['initialValue']) == 0)
                                                //return 21;  // Einstellungen initialValue entspricht nicht der Konvention

                                                // Einstellungen sort prüfen
                                                if (strlen($Setting_arr['sort']) === 0) {
                                                    return 22;//Einstellungen sort entspricht nicht der Konvention
                                                }
                                                // Einstellungen conf prüfen
                                                if (strlen($Setting_arr['conf']) === 0) {
                                                    return 33;//Einstellungen conf entspricht nicht der Konvention
                                                }
                                            } elseif (strlen($cTreffer4_arr[0]) === strlen($j)) {
                                                // Einstellungen Name prüfen
                                                if (strlen($Setting_arr['Name']) === 0) {
                                                    return 23;//Einstellungen Name entspricht nicht der Konvention
                                                }
                                                // Einstellungen ValueName prüfen
                                                if (!isset($Setting_arr['ValueName']) ||
                                                    !is_string($Setting_arr['ValueName']) ||
                                                    strlen($Setting_arr['ValueName']) === 0) {
                                                    return 34;//Einstellungen ValueName entspricht nicht der Konvention
                                                }
                                                // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                                if ($cTyp === 'selectbox') {
                                                    // SelectboxOptions prüfen
                                                    if (isset($Setting_arr['OptionsSource']) &&
                                                        is_array($Setting_arr['OptionsSource']) &&
                                                        count($Setting_arr['OptionsSource']) > 0) {
                                                        if (empty($Setting_arr['OptionsSource'][0]['File'])) {
                                                            return 128;
                                                        }
                                                        if (!file_exists($cVerzeichnis . '/' .
                                                            PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                                            PFAD_PLUGIN_ADMINMENU . $Setting_arr['OptionsSource'][0]['File'])
                                                        ) {
                                                            return 129;
                                                        }
                                                    } elseif (isset($Setting_arr['SelectboxOptions']) &&
                                                        is_array($Setting_arr['SelectboxOptions']) &&
                                                        count($Setting_arr['SelectboxOptions']) > 0
                                                    ) {
                                                        // Es gibt mehr als 1 Option
                                                        if (count($Setting_arr['SelectboxOptions'][0]) === 1) {
                                                            foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                                                preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                                                preg_match("/[0-9]+/", $y, $cTreffer7_arr);

                                                                if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                                    // Value prüfen
                                                                    if (strlen($Option_arr['value']) === 0) {
                                                                        return 25;//Die Option entspricht nicht der Konvention
                                                                    }
                                                                    // Sort prüfen
                                                                    if (strlen($Option_arr['sort']) === 0) {
                                                                        return 25;//Die Option entspricht nicht der Konvention
                                                                    }
                                                                } elseif (strlen($cTreffer7_arr[0]) === strlen($y)) {
                                                                    // Name prüfen
                                                                    if (strlen($Option_arr) === 0) {
                                                                        return 25;//Die Option entspricht nicht der Konvention
                                                                    }
                                                                }
                                                            }
                                                        } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) { //Es gibt nur 1 Option
                                                            // Value prüfen
                                                            if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                                                return 25;//Die Option entspricht nicht der Konvention
                                                            }

                                                            // Sort prüfen
                                                            if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                                                return 25;//Die Option entspricht nicht der Konvention
                                                            }
                                                            // Name prüfen
                                                            if (strlen($Setting_arr['SelectboxOptions'][0]['Option']) === 0) {
                                                                return 25;//Die Option entspricht nicht der Konvention
                                                            }
                                                        }
                                                    } else {
                                                        return 24;//Keine SelectboxOptionen vorhanden
                                                    }
                                                } elseif ($cTyp === 'radio') {
                                                    //radioOptions prüfen
                                                    if (isset($Setting_arr['OptionsSource']) &&
                                                        is_array($Setting_arr['OptionsSource']) &&
                                                        count($Setting_arr['OptionsSource']) > 0
                                                    ) {
                                                        //do nothing for now
                                                    } elseif (isset($Setting_arr['RadioOptions']) &&
                                                        is_array($Setting_arr['RadioOptions']) &&
                                                        count($Setting_arr['RadioOptions']) > 0
                                                    ) {
                                                        // Es gibt mehr als 1 Option
                                                        if (count($Setting_arr['RadioOptions'][0]) === 1) {
                                                            foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                                                preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                                                preg_match("/[0-9]+/", $y, $cTreffer7_arr);
                                                                if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                                    // Value prüfen
                                                                    if (strlen($Option_arr['value']) === 0) {
                                                                        return 25;// Die Option entspricht nicht der Konvention
                                                                    }
                                                                    // Sort prüfen
                                                                    if (strlen($Option_arr['sort']) === 0) {
                                                                        return 25;// Die Option entspricht nicht der Konvention
                                                                    }
                                                                } elseif (strlen($cTreffer7_arr[0]) === strlen($y)) {
                                                                    // Name prüfen
                                                                    if (strlen($Option_arr) === 0) {
                                                                        return 25;// Die Option entspricht nicht der Konvention
                                                                    }
                                                                }
                                                            }
                                                        } elseif (count($Setting_arr['RadioOptions'][0]) === 2) {
                                                            // Es gibt nur 1 Option

                                                            // Value prüfen
                                                            if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['value']) === 0) {
                                                                return 25;// Die Option entspricht nicht der Konvention
                                                            }
                                                            // Sort prüfen
                                                            if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                                                return 25;// Die Option entspricht nicht der Konvention
                                                            }
                                                            // Name prüfen
                                                            if (strlen($Setting_arr['RadioOptions'][0]['Option']) === 0) {
                                                                return 25;// Die Option entspricht nicht der Konvention
                                                            }
                                                        }
                                                    } else {
                                                        return 24;// Keine SelectboxOptionen vorhanden
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        return 19;//Einstellungen fehlen
                                    }
                                } else {
                                    return 18;//EinstellungsLink Name entspricht nicht der Konvention
                                }
                            }
                        }
                    }
                }
                // Plausi FrontendLinks (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'])
                ) {
                    // Links prüfen
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link']) > 0
                    ) {
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'] as $u => $Link_arr) {
                            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
                            preg_match("/[0-9]+/", $u, $cTreffer2_arr);

                            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                                // Filename prüfen
                                if (strlen($Link_arr['Filename']) === 0) {
                                    return 39;// Link Filename entspricht nicht der Konvention
                                }
                                // LinkName prüfen
                                preg_match(
                                    "/[a-zA-Z0-9äÄöÖüÜß" . utf8_decode('äÄüÜöÖß') . "\_\- ]+/",
                                    $Link_arr['Name'],
                                    $cTreffer1_arr
                                );
                                if (strlen($cTreffer1_arr[0]) !== strlen($Link_arr['Name'])) {
                                    return 40;// LinkName entspricht nicht der Konvention
                                }
                                // Templatename UND Fullscreen Templatename vorhanden?
                                // Es darf nur entweder oder geben
                                if (isset($Link_arr['Template'], $Link_arr['FullscreenTemplate']) &&
                                    strlen($Link_arr['Template']) > 0 &&
                                    strlen($Link_arr['FullscreenTemplate']) > 0
                                ) {
                                    return 78;// Es darf nur ein Templatename oder ein Fullscreen Templatename existieren
                                }
                                // Templatename prüfen
                                if (!isset($Link_arr['FullscreenTemplate']) ||
                                    strlen($Link_arr['FullscreenTemplate']) === 0
                                ) {
                                    if (strlen($Link_arr['Template']) === 0) {
                                        return 81;// Für ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden
                                    }
                                    preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $Link_arr['Template'], $cTreffer1_arr);
                                    if (strlen($cTreffer1_arr[0]) === strlen($Link_arr['Template'])) {
                                        if (!file_exists($cVerzeichnis . '/' .
                                            PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                            PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $Link_arr['Template'])
                                        ) {
                                            return 77;// Die Templatedatei für den Frontend Link existiert nicht
                                        }
                                    } else {
                                        return 76;// Der Templatename entspricht nicht der Konvention
                                    }
                                }

                                // Fullscreen Templatename prüfen
                                if (!isset($Link_arr['Template']) || strlen($Link_arr['Template']) === 0) {
                                    if (strlen($Link_arr['FullscreenTemplate']) === 0) {
                                        return 81;
                                    }    // Für ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden

                                    preg_match("/[a-zA-Z0-9\/_\-.]+.tpl/", $Link_arr['FullscreenTemplate'], $cTreffer1_arr);
                                    if (strlen($cTreffer1_arr[0]) === strlen($Link_arr['FullscreenTemplate'])) {
                                        if (!file_exists($cVerzeichnis . '/' .
                                            PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                            PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $Link_arr['FullscreenTemplate'])
                                        ) {
                                            return 80;
                                        }   // Die Fullscreen Templatedatei für den Frontend Link existiert nicht
                                    } else {
                                        return 79;
                                    } // Der Fullscreen Templatename entspricht nicht der Konvention
                                }
                                // Angabe ob erst Sichtbar nach Login prüfen
                                preg_match("/[NY]{1,1}/", $Link_arr['VisibleAfterLogin'], $cTreffer2_arr);
                                if (strlen($cTreffer2_arr[0]) !== strlen($Link_arr['VisibleAfterLogin'])) {
                                    return 41;// Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention
                                }
                                // Abgabe ob ein Druckbutton gezeigt werden soll prüfen
                                preg_match("/[NY]{1,1}/", $Link_arr['PrintButton'], $cTreffer3_arr);
                                if (strlen($cTreffer3_arr[0]) !== strlen($Link_arr['PrintButton'])) {
                                    return 42;// Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention
                                }
                                // Abgabe ob NoFollow Attribut gezeigt werden soll prüfen
                                if (isset($Link_arr['NoFollow'])) {
                                    preg_match("/[NY]{1,1}/", $Link_arr['NoFollow'], $cTreffer3_arr);
                                } else {
                                    $cTreffer3_arr = [];
                                }
                                if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) !== strlen($Link_arr['NoFollow'])) {
                                    return 104;// Frontend Link Attribut NoFollow entspricht nicht der Konvention
                                }
                                // LinkSprachen prüfen
                                if (isset($Link_arr['LinkLanguage']) &&
                                    is_array($Link_arr['LinkLanguage']) &&
                                    count($Link_arr['LinkLanguage']) > 0
                                ) {
                                    foreach ($Link_arr['LinkLanguage'] as $l => $LinkLanguage_arr) {
                                        preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                                        preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                                        if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                                            // ISO prüfen
                                            preg_match("/[A-Z]{3}/", $LinkLanguage_arr['iso'], $cTreffer_arr);
                                            if (strlen($LinkLanguage_arr['iso']) === 0 ||
                                                strlen($cTreffer_arr[0]) !== strlen($LinkLanguage_arr['iso'])
                                            ) {
                                                return 43;//  Die ISO der Linksprache entspricht nicht der Konvention
                                            }
                                        } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                                            // Seo prüfen
                                            preg_match("/[a-zA-Z0-9- ]+/", $LinkLanguage_arr['Seo'], $cTreffer1_arr);
                                            if (strlen($LinkLanguage_arr['Seo']) === 0 ||
                                                strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['Seo'])
                                            ) {
                                                return 44;// Der Seo Name entspricht nicht der Konvention
                                            }
                                            // Name prüfen
                                            preg_match(
                                                "/[a-zA-Z0-9äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\- ]+/",
                                                $LinkLanguage_arr['Name'],
                                                $cTreffer1_arr
                                            );
                                            if (strlen($LinkLanguage_arr['Name']) === 0 ||
                                                strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['Name'])
                                            ) {
                                                return 45;// Der Name entspricht nicht der Konvention
                                            }
                                            // Title prüfen
                                            preg_match(
                                                "/[a-zA-Z0-9äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\- ]+/",
                                                $LinkLanguage_arr['Title'],
                                                $cTreffer1_arr
                                            );
                                            if (strlen($LinkLanguage_arr['Title']) === 0 ||
                                                strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['Title'])
                                            ) {
                                                return 46;// Der Title entspricht nicht der Konvention
                                            }
                                            // MetaTitle prüfen
                                            preg_match(
                                                "/[a-zA-Z0-9äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\,\.\- ]+/",
                                                $LinkLanguage_arr['MetaTitle'],
                                                $cTreffer1_arr
                                            );
                                            if (strlen($LinkLanguage_arr['MetaTitle']) === 0 ||
                                                strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['MetaTitle'])
                                            ) {
                                                if (strlen($LinkLanguage_arr['MetaTitle']) === 0) {
                                                    return 47;// Der MetaTitle entspricht nicht der Konvention
                                                }
                                            }
                                            // MetaKeywords prüfen
                                            preg_match(
                                                "/[a-zA-Z0-9äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\,\- ]+/",
                                                $LinkLanguage_arr['MetaKeywords'],
                                                $cTreffer1_arr
                                            );
                                            if (strlen($LinkLanguage_arr['MetaKeywords']) === 0 ||
                                                strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['MetaKeywords'])
                                            ) {
                                                return 48;// Die MetaKeywords entsprechen nicht der Konvention
                                            }
                                            // MetaDescription prüfen
                                            preg_match(
                                                "/[a-zA-Z0-9äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\,\.\- ]+/",
                                                $LinkLanguage_arr['MetaDescription'],
                                                $cTreffer1_arr
                                            );
                                            if (strlen($LinkLanguage_arr['MetaDescription']) === 0 ||
                                                strlen($cTreffer1_arr[0]) !== strlen($LinkLanguage_arr['MetaDescription'])
                                            ) {
                                                return 49;// Die MetaDescription entspricht nicht der Konvention
                                            }
                                        }
                                    }
                                } else {
                                    return 43;// Keine Linksprachen vorhanden
                                }
                            }
                        }
                    } else {
                        return 38; // Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde
                    }
                }
                // Plausi Zahlungsmethode (PaymentMethod) (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod']) &&
                    isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) &&
                    count($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) > 0
                ) {
                    foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'] as $u => $Method_arr) {
                        preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
                        preg_match("/[0-9]+/", $u, $cTreffer2_arr);
                        if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                            // Name prüfen
                            preg_match(
                                "/[a-zA-Z0-9äÄöÖüÜß" . utf8_decode('äÄüÜöÖß') . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                                $Method_arr['Name'],
                                $cTreffer1_arr
                            );
                            if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Name'])) {
                                return 50;// Der Name in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // Sort prüfen
                            preg_match("/[0-9]+/", $Method_arr['Sort'], $cTreffer1_arr);
                            if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Sort'])) {
                                return 71;// Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // SendMail prüfen
                            preg_match("/[0-1]{1}/", $Method_arr['SendMail'], $cTreffer1_arr);
                            if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['SendMail'])) {
                                return 51;// Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // TSCode prüfen
                            preg_match('/[A-Z_]+/', $Method_arr['TSCode'], $cTreffer1_arr);
                            if (strlen($cTreffer1_arr[0]) === strlen($Method_arr['TSCode'])) {
                                $cTSCode_arr = [
                                    'DIRECT_DEBIT',
                                    'CREDIT_CARD',
                                    'INVOICE',
                                    'CASH_ON_DELIVERY',
                                    'PREPAYMENT',
                                    'CHEQUE',
                                    'PAYBOX',
                                    'PAYPAL',
                                    'CASH_ON_PICKUP',
                                    'FINANCING',
                                    'LEASING',
                                    'T_PAY',
                                    'CLICKANDBUY',
                                    'GIROPAY',
                                    'GOOGLE_CHECKOUT',
                                    'SHOP_CARD',
                                    'DIRECT_E_BANKING',
                                    'OTHER'
                                ];
                                if (!in_array($Method_arr['TSCode'], $cTSCode_arr, true)) {
                                    return 52;// TSCode in den Zahlungsmethoden entspricht nicht der Konvention
                                }
                            } else {
                                return 52;// TSCode in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // PreOrder (nWaehrendbestellung) prüfen
                            preg_match("/[0-1]{1}/", $Method_arr['PreOrder'], $cTreffer1_arr);
                            if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['PreOrder'])) {
                                return 53;// PreOrder in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // Soap prüfen
                            preg_match("/[0-1]{1}/", $Method_arr['Soap'], $cTreffer1_arr);
                            if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Soap'])) {
                                return 72;// Soap in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // Curl prüfen
                            preg_match("/[0-1]{1}/", $Method_arr['Curl'], $cTreffer1_arr);
                            if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Curl'])) {
                                return 73;// Curl in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // Sockets prüfen
                            preg_match('/[0-1]{1}/', $Method_arr['Sockets'], $cTreffer1_arr);
                            if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['Sockets'])) {
                                return 74;// Sockets in den Zahlungsmethoden entspricht nicht der Konvention
                            }
                            // ClassFile prüfen
                            if (isset($Method_arr['ClassFile'])) {
                                preg_match('/[a-zA-Z0-9\/_\-.]+.php/', $Method_arr['ClassFile'], $cTreffer1_arr);
                                if (strlen($cTreffer1_arr[0]) === strlen($Method_arr['ClassFile'])) {
                                    if (!file_exists($cVerzeichnis . '/' .
                                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                        PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['ClassFile'])
                                    ) {
                                        return 55;// Die Datei für die Klasse der Zahlungsmethode existiert nicht
                                    }
                                } else {
                                    return 54;// ClassFile in den Zahlungsmethoden entspricht nicht der Konvention
                                }
                            }
                            // ClassName prüfen
                            if (isset($Method_arr['ClassName'])) {
                                preg_match("/[a-zA-Z0-9\/_\-]+/", $Method_arr['ClassName'], $cTreffer1_arr);
                                if (strlen($cTreffer1_arr[0]) !== strlen($Method_arr['ClassName'])) {
                                    return 75;// ClassName in den Zahlungsmethoden entspricht nicht der Konvention
                                }
                            }
                            // TemplateFile prüfen
                            if (isset($Method_arr['TemplateFile']) && strlen($Method_arr['TemplateFile']) > 0) {
                                preg_match(
                                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                                    $Method_arr['TemplateFile'],
                                    $cTreffer1_arr
                                );
                                if (strlen($cTreffer1_arr[0]) === strlen($Method_arr['TemplateFile'])) {
                                    if (!file_exists($cVerzeichnis . '/' .
                                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                        PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['TemplateFile'])
                                    ) {
                                        return 57;// Die Datei für das Template der Zahlungsmethode existiert nicht
                                    }
                                } else {
                                    return 56;// TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
                                }
                            }
                            // Zusatzschritt-TemplateFile prüfen
                            if (isset($Method_arr['AdditionalTemplateFile']) && strlen($Method_arr['AdditionalTemplateFile']) > 0) {
                                preg_match(
                                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                                    $Method_arr['AdditionalTemplateFile'],
                                    $cTreffer1_arr
                                );
                                if (strlen($cTreffer1_arr[0]) === strlen($Method_arr['AdditionalTemplateFile'])) {
                                    if (!file_exists($cVerzeichnis . '/' .
                                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                        PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['AdditionalTemplateFile'])
                                    ) {
                                        return 114;// Die Datei für das Zusatzschritt-Template der Zahlungsmethode existiert nicht
                                    }
                                } else {
                                    return 113;// Zusatzschritt-TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
                                }
                            }
                            // ZahlungsmethodeSprachen prüfen
                            if (isset($Method_arr['MethodLanguage']) &&
                                is_array($Method_arr['MethodLanguage']) &&
                                count($Method_arr['MethodLanguage']) > 0
                            ) {
                                foreach ($Method_arr['MethodLanguage'] as $l => $MethodLanguage_arr) {
                                    preg_match('/[0-9]+\sattr/', $l, $cTreffer1_arr);
                                    preg_match('/[0-9]+/', $l, $cTreffer2_arr);
                                    if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                                        // ISO prüfen
                                        preg_match("/[A-Z]{3}/", $MethodLanguage_arr['iso'], $cTreffer_arr);
                                        if (strlen($MethodLanguage_arr['iso']) === 0 ||
                                            strlen($cTreffer_arr[0]) !== strlen($MethodLanguage_arr['iso'])
                                        ) {
                                            return 59;//  Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention
                                        }
                                    } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($l)) {
                                        // Name prüfen
                                        if (!isset($MethodLanguage_arr['Name'])) {
                                            return 60;// Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention
                                        }
                                        preg_match(
                                            "/[a-zA-Z0-9äÄöÖüÜß" . utf8_decode('äÄüÜöÖß') . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                                            $MethodLanguage_arr['Name'],
                                            $cTreffer1_arr
                                        );
                                        if (strlen($cTreffer1_arr[0]) !== strlen($MethodLanguage_arr['Name'])) {
                                            return 60;// Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention
                                        }
                                        // ChargeName prüfen
                                        if (!isset($MethodLanguage_arr['ChargeName'])) {
                                            return 61;// Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention
                                        }
                                        preg_match(
                                            "/[a-zA-Z0-9äÄöÖüÜß" . utf8_decode('äÄüÜöÖß') . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                                            $MethodLanguage_arr['ChargeName'],
                                            $cTreffer1_arr
                                        );
                                        if (strlen($cTreffer1_arr[0]) !== strlen($MethodLanguage_arr['ChargeName'])) {
                                            return 61;// Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention
                                        }
                                        // InfoText prüfen
                                        if (!isset($MethodLanguage_arr['InfoText'])) {
                                            return 62;// Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention
                                        }
                                        preg_match(
                                            "/[a-zA-Z0-9äÄöÖüÜß" . utf8_decode('äÄüÜöÖß') . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                                            $MethodLanguage_arr['InfoText'],
                                            $cTreffer1_arr
                                        );
                                        if (isset($cTreffer1_arr[0]) &&
                                            strlen($cTreffer1_arr[0]) !== strlen($MethodLanguage_arr['InfoText'])
                                        ) {
                                            return 62;// Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention
                                        }
                                    }
                                }
                            } else {
                                return 58;// Keine Sprachen in den Zahlungsmethoden hinterlegt
                            }
                            // Zahlungsmethode Einstellungen prüfen
                            $cTyp = '';
                            if (isset($Method_arr['Setting']) &&
                                is_array($Method_arr['Setting']) &&
                                count($Method_arr['Setting']) > 0
                            ) {
                                foreach ($Method_arr['Setting'] as $j => $Setting_arr) {
                                    preg_match('/[0-9]+\sattr/', $j, $cTreffer3_arr);
                                    preg_match('/[0-9]+/', $j, $cTreffer4_arr);
                                    if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                                        $cTyp = $Setting_arr['type'];
                                        // Einstellungen type prüfen
                                        if (strlen($Setting_arr['type']) === 0) {
                                            return 63;// Einstellungen type entspricht nicht der Konvention
                                        }
                                        // Einstellungen initialValue prüfen
                                        //if(strlen($Setting_arr['initialValue']) == 0)
                                        //return 64;  // Einstellungen initialValue entspricht nicht der Konvention

                                        // Einstellungen sort prüfen
                                        if (strlen($Setting_arr['sort']) === 0) {
                                            return 65;// Einstellungen sort entspricht nicht der Konvention
                                        }
                                        // Einstellungen conf prüfen
                                        if (strlen($Setting_arr['conf']) === 0) {
                                            return 66;// Einstellungen conf entspricht nicht der Konvention
                                        }
                                    } elseif (isset($cTreffer4_arr[0]) && strlen($cTreffer4_arr[0]) === strlen($j)) {
                                        // Einstellungen Name prüfen
                                        if (strlen($Setting_arr['Name']) === 0) {
                                            return 67;// Einstellungen Name entspricht nicht der Konvention
                                        }
                                        // Einstellungen ValueName prüfen
                                        if (strlen($Setting_arr['ValueName']) === 0) {
                                            return 68;// Einstellungen ValueName entspricht nicht der Konvention
                                        }
                                        // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                        if ($cTyp === 'selectbox') {
                                            // SelectboxOptions prüfen
                                            if (isset($Setting_arr['SelectboxOptions']) &&
                                                is_array($Setting_arr['SelectboxOptions']) &&
                                                count($Setting_arr['SelectboxOptions']) > 0
                                            ) {
                                                // Es gibt mehr als 1 Option
                                                if (count($Setting_arr['SelectboxOptions'][0]) === 1) {
                                                    foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                                        preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);
                                                        preg_match('/[0-9]+/', $y, $cTreffer7_arr);
                                                        if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                            // Value prüfen
                                                            if (strlen($Option_arr['value']) === 0) {
                                                                return 70;// Die Option entspricht nicht der Konvention
                                                            }
                                                            // Sort prüfen
                                                            if (strlen($Option_arr['sort']) === 0) {
                                                                return 70;// Die Option entspricht nicht der Konvention
                                                            }
                                                        } elseif (isset($cTreffer7_arr[0]) && strlen($cTreffer7_arr[0]) === strlen($y)) {
                                                            // Name prüfen
                                                            if (strlen($Option_arr) === 0) {
                                                                return 70;// Die Option entspricht nicht der Konvention
                                                            }
                                                        }
                                                    }
                                                } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) { //Es gibt nur 1 Option
                                                    // Value prüfen
                                                    if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                                        return 70;// Die Option entspricht nicht der Konvention
                                                    }
                                                    // Sort prüfen
                                                    if (strlen($Setting_arr['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                                        return 70;// Die Option entspricht nicht der Konvention
                                                    }
                                                    // Name prüfen
                                                    if (strlen($Setting_arr['SelectboxOptions'][0]['Option']) === 0) {
                                                        return 70;// Die Option entspricht nicht der Konvention
                                                    }
                                                }
                                            } else {
                                                return 69;// Keine SelectboxOptionen vorhanden
                                            }
                                        } elseif ($cTyp === 'radio') {
                                            // SelectboxOptions prüfen
                                            if (isset($Setting_arr['RadioOptions']) &&
                                                is_array($Setting_arr['RadioOptions']) &&
                                                count($Setting_arr['RadioOptions']) > 0
                                            ) {
                                                // Es gibt mehr als 1 Option
                                                if (count($Setting_arr['RadioOptions'][0]) === 1) {
                                                    foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                                        preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                                        preg_match("/[0-9]+/", $y, $cTreffer7_arr);
                                                        if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                            // Value prüfen
                                                            if (strlen($Option_arr['value']) === 0) {
                                                                return 70;// Die Option entspricht nicht der Konvention
                                                            }
                                                            // Sort prüfen
                                                            if (strlen($Option_arr['sort']) === 0) {
                                                                return 70;// Die Option entspricht nicht der Konvention
                                                            }
                                                        } elseif (isset($cTreffer7_arr[0]) && strlen($cTreffer7_arr[0]) === strlen($y)) {
                                                            // Name prüfen
                                                            if (strlen($Option_arr) === 0) {
                                                                return 70;// Die Option entspricht nicht der Konvention
                                                            }
                                                        }
                                                    }
                                                } elseif (count($Setting_arr['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                                                    // Value prüfen
                                                    if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['value']) === 0) {
                                                        return 70;// Die Option entspricht nicht der Konvention
                                                    }
                                                    // Sort prüfen
                                                    if (strlen($Setting_arr['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                                        return 70;// Die Option entspricht nicht der Konvention
                                                    }
                                                    // Name prüfen
                                                    if (strlen($Setting_arr['RadioOptions'][0]['Option']) === 0) {
                                                        return 70;// Die Option entspricht nicht der Konvention
                                                    }
                                                }
                                            } else {
                                                return 69;// Keine SelectboxOptionen vorhanden
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Plausi Boxenvorlagen (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'])
                ) {
                    // Boxen prüfen
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box']) > 0
                    ) {
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box'] as $h => $Box_arr) {
                            preg_match("/[0-9]+/", $h, $cTreffer3_arr);
                            if (strlen($cTreffer3_arr[0]) === strlen($h)) {
                                // Box Name prüfen
                                if (strlen($Box_arr['Name']) === 0) {
                                    return 83;// Box Name entspricht nicht der Konvention
                                }
                                // Box TemplateFile prüfen
                                if (strlen($Box_arr['TemplateFile']) > 0) {
                                    if (!file_exists($cVerzeichnis . '/' .
                                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                        PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_BOXEN . $Box_arr['TemplateFile'])
                                    ) {
                                        return 85;// Box Templatedatei existiert nicht
                                    }
                                } else {
                                    return 84;// Box Templatedatei entspricht nicht der Konvention
                                }
                            }
                        }
                    } else {
                        return 82;// Keine Box vorhanden
                    }
                }

                // Plausi Emailvorlagen (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'])
                ) {
                    // EmailTemplates prüfen
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template']) > 0
                    ) {
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'] as $u => $Template_arr) {
                            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
                            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
                            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                                // Template Name prüfen
                                preg_match(
                                    "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . " ]+/",
                                    $Template_arr['Name'],
                                    $cTreffer1_arr
                                );
                                if (strlen($cTreffer1_arr[0]) !== strlen($Template_arr['Name'])) {
                                    return 92;// Template Name entspricht nicht der Konvention
                                }
                                // Template Typ prüfen
                                if ($Template_arr['Type'] !== 'text/html' && $Template_arr['Type'] !== 'text') {
                                    return 93;// Template Type entspricht nicht der Konvention
                                }
                                // Template ModulId prüfen
                                if (strlen($Template_arr['ModulId']) === 0) {
                                    return 94;// Template ModulId entspricht nicht der Konvention
                                }
                                // Template Active prüfen
                                if (strlen($Template_arr['Active']) === 0) {
                                    return 95;// Template Active entspricht nicht der Konvention
                                }
                                // Template AKZ prüfen
                                if (strlen($Template_arr['AKZ']) === 0) {
                                    return 96;// Template AKZ entspricht nicht der Konvention
                                }
                                // Template AGB prüfen
                                if (strlen($Template_arr['AGB']) === 0) {
                                    return 97;// Template AGB entspricht nicht der Konvention
                                }
                                // Template WRB prüfen
                                if (strlen($Template_arr['WRB']) === 0) {
                                    return 98;// Template WRB entspricht nicht der Konvention
                                }
                                // Template Sprachen prüfen
                                if (isset($Template_arr['TemplateLanguage']) &&
                                    is_array($Template_arr['TemplateLanguage']) &&
                                    count($Template_arr['TemplateLanguage']) > 0
                                ) {
                                    foreach ($Template_arr['TemplateLanguage'] as $l => $TemplateLanguage_arr) {
                                        preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                                        preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                                        if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                                            // ISO prüfen
                                            preg_match("/[A-Z]{3}/", $TemplateLanguage_arr['iso'], $cTreffer_arr);
                                            if (strlen($TemplateLanguage_arr['iso']) === 0 ||
                                                strlen($cTreffer_arr[0]) !== strlen($TemplateLanguage_arr['iso'])
                                            ) {
                                                return 99;//Die ISO der Emailtemplate Sprache entspricht nicht der Konvention
                                            }
                                        } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                                            // Subject prüfen
                                            preg_match("/[a-zA-Z0-9\/_\-.#: ]+/", $TemplateLanguage_arr['Subject'], $cTreffer1_arr);
                                            if (strlen($TemplateLanguage_arr['Subject']) === 0 ||
                                                strlen($cTreffer1_arr[0]) !== strlen($TemplateLanguage_arr['Subject'])
                                            ) {
                                                return 100;// Der Subject Name entspricht nicht der Konvention
                                            }
                                        }
                                    }
                                } else {
                                    return 101;// Keine Templatesprachen vorhanden
                                }
                            }
                        }
                    } else {
                        return 91;// Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde
                    }
                }
                // Plausi Locales (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'])
                ) {
                    // Variablen prüfen
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable']) > 0
                    ) {
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable'] as $t => $Variable_arr) {
                            preg_match("/[0-9]+/", $t, $cTreffer2_arr);
                            if (strlen($cTreffer2_arr[0]) === strlen($t)) {
                                // Variablen Name prüfen
                                if (strlen($Variable_arr['Name']) === 0) {
                                    return 27;// Variable Name entspricht nicht der Konvention
                                }
                                // Variable Localized prüfen
                                // Nur eine Sprache vorhanden
                                if (isset($Variable_arr['VariableLocalized attr']) &&
                                    is_array($Variable_arr['VariableLocalized attr']) &&
                                    count($Variable_arr['VariableLocalized attr']) > 0
                                ) {
                                    if (isset($Variable_arr['VariableLocalized attr']['iso'])) {
                                        // ISO prüfen
                                        preg_match("/[A-Z]{3}/", $Variable_arr['VariableLocalized attr']['iso'], $cTreffer_arr);
                                        if (strlen($cTreffer_arr[0]) !== strlen($Variable_arr['VariableLocalized attr']['iso'])) {
                                            return 29;//Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention
                                        }
                                        // Name prüfen
                                        if (strlen($Variable_arr['VariableLocalized']) === 0) {
                                            return 30;// Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention
                                        }
                                    } else {
                                        return 28;//Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention
                                    }
                                } // Mehr als eine Sprache vorhanden
                                elseif (isset($Variable_arr['VariableLocalized']) &&
                                    is_array($Variable_arr['VariableLocalized']) &&
                                    count($Variable_arr['VariableLocalized']) > 0
                                ) {
                                    foreach ($Variable_arr['VariableLocalized'] as $i => $VariableLocalized_arr) {
                                        preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                                        preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                                        if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                                            // ISO prüfen
                                            preg_match("/[A-Z]{3}/", $VariableLocalized_arr['iso'], $cTreffer_arr);
                                            if (strlen($VariableLocalized_arr['iso']) === 0 ||
                                                strlen($cTreffer_arr[0]) !== strlen($VariableLocalized_arr['iso'])
                                            ) {
                                                return 29;//Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention
                                            }
                                        } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($i)) {
                                            // Name prüfen
                                            if (strlen($VariableLocalized_arr) === 0) {
                                                return 30;//Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention
                                            }
                                        }
                                    }
                                } else {
                                    return 28;// Keine lokalisierte Sprachvariable vorhanden
                                }
                            }
                        }
                    } else {
                        return 26;// Keine Sprachvariablen vorhanden
                    }
                }

                // Plausi CheckBoxFunction (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction']) &&
                    isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) &&
                    count($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) > 0
                ) {
                    foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'] as $t => $Function_arr) {
                        preg_match("/[0-9]+/", $t, $cTreffer2_arr);
                        if (strlen($cTreffer2_arr[0]) === strlen($t)) {
                            // Function Name prüfen
                            if (strlen($Function_arr['Name']) === 0) {
                                return 102;// Funktion Name entspricht nicht der Konvention
                            }
                            // Function ID prüfen
                            if (strlen($Function_arr['ID']) === 0) {
                                return 103;// Funktion ID entspricht nicht der Konvention
                            }
                        }
                    }
                }
                // Plausi AdminWidgets (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'])
                ) {
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']) > 0
                    ) {
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'] as $u => $Widget_arr) {
                            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
                            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
                            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                                // Widget Title prüfen
                                preg_match(
                                    "/[a-zA-Z0-9\/_\-äÄüÜöÖß" . utf8_decode('äÄüÜöÖß') . "\(\) ]+/",
                                    $Widget_arr['Title'],
                                    $cTreffer1_arr
                                );
                                if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Title'])) {
                                    return 106;// Widget Title entspricht nicht der Konvention
                                }
                                // Widget Class prüfen
                                preg_match("/[a-zA-Z0-9\/_\-.]+/", $Widget_arr['Class'], $cTreffer1_arr);
                                if (strlen($cTreffer1_arr[0]) === strlen($Widget_arr['Class'])) {
                                    if (!file_exists(
                                        $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                        PFAD_PLUGIN_ADMINMENU . PFAD_PLUGIN_WIDGET .
                                        'class.Widget' . $Widget_arr['Class'] . '_' .
                                        $XML_arr['jtlshop3plugin'][0]['PluginID'] . '.php'
                                    )
                                    ) {
                                        return 108;// Die Datei für die Klasse des AdminWidgets existiert nicht
                                    }
                                } else {
                                    return 107;// Widget Class entspricht nicht der Konvention
                                }
                                // Widget Container prüfen
                                if ($Widget_arr['Container'] !== 'center' &&
                                    $Widget_arr['Container'] !== 'left' &&
                                    $Widget_arr['Container'] !== 'right'
                                ) {
                                    return 109;// Container im Widget entspricht nicht der Konvention
                                }
                                // Widget Pos prüfen
                                preg_match("/[0-9]+/", $Widget_arr['Pos'], $cTreffer1_arr);
                                if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Pos'])) {
                                    return 110;// Pos im Widget entspricht nicht der Konvention
                                }
                                // Widget Expanded prüfen
                                preg_match("/[0-1]{1}/", $Widget_arr['Expanded'], $cTreffer1_arr);
                                if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Expanded'])) {
                                    return 111;// Expanded im Widget entspricht nicht der Konvention
                                }
                                // Widget Active prüfen
                                preg_match("/[0-1]{1}/", $Widget_arr['Active'], $cTreffer1_arr);
                                if (strlen($cTreffer1_arr[0]) !== strlen($Widget_arr['Active'])) {
                                    return 112;// Active im Widget entspricht nicht der Konvention
                                }
                            }
                        }
                    } else {
                        return 105;// Keine Widgets vorhanden
                    }
                }

                // Plausi Exportformate (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'])
                ) {
                    // Formate prüfen
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']) &&
                        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']) &&
                        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']) > 0
                    ) {
                        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'] as $h => $Format_arr) {
                            preg_match("/[0-9]+\sattr/", $h, $cTreffer1_arr);
                            preg_match("/[0-9]+/", $h, $cTreffer2_arr);
                            if (strlen($cTreffer2_arr[0]) === strlen($h)) {
                                // Name prüfen
                                if (strlen($Format_arr['Name']) === 0) {
                                    return 116;// Format Name entspricht nicht der Konvention
                                }
                                // Filename prüfen
                                if (strlen($Format_arr['FileName']) === 0) {
                                    return 117;// Format Filename entspricht nicht der Konvention
                                }
                                // Content prüfen
                                if ((!isset($Format_arr['Content']) || strlen($Format_arr['Content']) === 0) &&
                                    (!isset($Format_arr['ContentFile']) || strlen($Format_arr['ContentFile']) === 0)
                                ) {
                                    return 118;// Format enthaelt weder Content, noch eine Contentdatei
                                }
                                // Encoding prüfen
                                if (strlen($Format_arr['Encoding']) === 0 || ($Format_arr['Encoding'] !== 'ASCII' && $Format_arr['Encoding'] !== 'UTF-8')) {
                                    return 119;// Format Encoding entspricht nicht der Konvention
                                }
                                // Encoding prüfen
                                if (strlen($Format_arr['ShippingCostsDeliveryCountry']) === 0) {
                                    return 120;// Format ShippingCostsDeliveryCountry entspricht nicht der Konvention
                                }
                                // Encoding prüfen
                                if (strlen($Format_arr['ContentFile']) > 0 && !file_exists(
                                    $cVerzeichnis . '/' . PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                    PFAD_PLUGIN_ADMINMENU . PFAD_PLUGIN_EXPORTFORMAT . $Format_arr['ContentFile']
                                )) {
                                    return 121;// Format ContentFile entspricht nicht der Konvention
                                }
                            }
                        }
                    } else {
                        return 115;// Keine Formate vorhanden
                    }
                }
                // Plausi ExtendedTemplate (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates']) &&
                    is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
                ) {
                    // Template prüfen
                    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'][0]['Template'])) {
                        $cTemplate_arr = (array)$XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'][0]['Template'];
                        foreach ($cTemplate_arr as $cTemplate) {
                            preg_match('/[a-zA-Z0-9\/_\-]+\.tpl/', $cTemplate, $cTreffer3_arr);
                            if (strlen($cTreffer3_arr[0]) === strlen($cTemplate)) {
                                if (!file_exists($cVerzeichnis . '/' .
                                    PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                                    PFAD_PLUGIN_FRONTEND . PFAD_PLUGIN_TEMPLATE . $cTemplate)
                                ) {
                                    return 124;// Templatedatei existiert nicht
                                }
                            } else {
                                return 123;// Templatedatei entspricht nicht der Konvention
                            }
                        }
                    } else {
                        return 122;// Kein Template vorhanden
                    }
                }

                // Plausi Uninstall (falls vorhanden)
                if (isset($XML_arr['jtlshop3plugin'][0]['Uninstall']) &&
                    strlen($XML_arr['jtlshop3plugin'][0]['Uninstall']) > 0 &&
                    !file_exists($cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $cVersionsnummer . '/' .
                        PFAD_PLUGIN_UNINSTALL . $XML_arr['jtlshop3plugin'][0]['Uninstall'])
                ) {
                    return 125;// Uninstall File existiert nicht
                }
                // Interne XML prüfung mit höheren XML Versionen
                if ($nXMLVersion > 100) {
                    $nReturnValue = pluginPlausiInterVersion($XML_arr, $nXMLVersion, $cVerzeichnis);
                    if ($nReturnValue === 1) {
                        return $isShop4Compatible ? 1 : 126;// Alles O.K./Warnung Shop4
                    }

                    return $nReturnValue;
                } else {
                    return $isShop4Compatible ? 1 : 126;
                }
            } else {
                return 8;// Der Installationsknoten ist nicht vorhanden
            }
        } else {
            return 7;// Die PluginID entspricht nicht der Konvention
        }
    } else {
        return 6;// Der Pluginname entspricht nicht der Konvention
    }
}

/**
 * @todo: use/remove
 * @param array $XML_arr
 * @param int   $nXMLVersion
 * @param string $cVerzeichnis
 * @return int
 */
function pluginPlausiInterVersion($XML_arr, $nXMLVersion, $cVerzeichnis)
{
    switch ($nXMLVersion) {
        case 101:
            // Teste etwas im XML
            return 1; // Alles O.K.
            break;

        case 102:
            // Teste etwas im XML
            return 1; // Alles O.K.
            break;

    }

    return 1;
}

/**
 * Versucht ein ausgewähltes Plugin zu updaten
 *
 * @param int $kPlugin
 * @return int
 */
function updatePlugin($kPlugin)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin > 0) {
        $oPluginTMP = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($oPluginTMP->kPlugin) && $oPluginTMP->kPlugin > 0) {
            $oPlugin = new Plugin($oPluginTMP->kPlugin);

            return installierePluginVorbereitung($oPlugin->cVerzeichnis, $oPlugin);
        }

        return 3;// Es konnte kein Plugin in der Datenbank gefunden werden.
    }

    return 2;// $kPlugin wurde nicht übergeben
}

/**
 * Versucht ein ausgewähltes Plugin zu vorzubereiten und danach zu installieren
 *
 * @param string     $cVerzeichnis
 * @param int|Plugin $oPluginOld
 * @return int
 */
function installierePluginVorbereitung($cVerzeichnis, $oPluginOld = 0)
{
    if (strlen($cVerzeichnis) > 0) {
        // Plugin wurde schon installiert?
        $oPluginTMP = new stdClass();
        if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
            $oPluginTMP = Shop::DB()->select('tplugin', 'cVerzeichnis', $cVerzeichnis);
        }
        if (!isset($oPluginTMP->kPlugin) || !$oPluginTMP->kPlugin || $oPluginOld->kPlugin > 0) {
            $cPfad = realpath(PFAD_ROOT . PFAD_PLUGIN . basename($cVerzeichnis));
            if (file_exists($cPfad . '/' . PLUGIN_INFO_FILE)) {
                $xml     = StringHandler::convertISO(file_get_contents($cPfad . '/' . PLUGIN_INFO_FILE));
                $XML_arr = XML_unserialize($xml, 'ISO-8859-1');
                $XML_arr = getArrangedArray($XML_arr);
                // Interne Plugin Plausi
                $nReturnValue = pluginPlausiIntern($XML_arr, $cPfad);
                // Work Around
                if (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0 && $nReturnValue === 90) {
                    $nReturnValue = 1;
                }
                // Alles O.K. => installieren
                if ($nReturnValue === 1 || $nReturnValue === 126) {
                    // Plugin wird installiert
                    $nReturnValue = installierePlugin($XML_arr, $cVerzeichnis, $oPluginOld);

                    if ($nReturnValue === 1) {
                        return 1;
                    }
                    $nSQLFehlerCode_arr = [
                        2  => 152,
                        3  => 153,
                        4  => 154,
                        5  => 155,
                        6  => 156,
                        7  => 157,
                        8  => 158,
                        9  => 159,
                        10 => 160,
                        11 => 161,
                        12 => 162,
                        13 => 163,
                        14 => 164,
                        15 => 165,
                        16 => 166,
                        22 => 202,
                        23 => 203,
                        24 => 204,
                        25 => 205,
                        26 => 206,
                        27 => 207,
                        28 => 208
                    ];

                    return $nSQLFehlerCode_arr[$nReturnValue];
                }

                return $nReturnValue;
            }

            return 3;// info.xml existiert nicht
        }

        return 4;// Plugin wurde schon installiert
    }

    return 2;// $cVerzeichnis wurde nicht übergeben}
}

/*
// Return:
// 1 = Alles O.K.
// 2 = Main Plugindaten nicht korrekt
// 3 = Ein Hook konnte nicht in die Datenbank gespeichert werden
// 4 = Ein Adminmenü Customlink konnte nicht in die Datenbank gespeichert werden
// 5 = Ein Adminmenü Settingslink konnte nicht in die Datenbank gespeichert werden
// 6 = Eine Einstellung konnte nicht in die Datenbank gespeichert werden
// 7 = Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
// 8 = Ein Link konnte nicht in die Datenbank gespeichert werden
// 9 = Eine Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
// 10 = Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
// 11 = Eine Einstellung der Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
// 12 = Es konnte keine Linkgruppe im Shop gefunden werden
// 13 = Eine Boxvorlage konnte nicht in die Datenbank gespeichert werden
// 14 = Eine Emailvorlage konnte nicht in die Datenbank gespeichert werden
// 15 = Ein AdminWidget konnte nicht in die Datenbank gespeichert werden
// 16 = Ein Exportformat konnte nicht in die Datenbank gespeichert werden
// 17 = Ein Template konnte nicht in die Datenbank gespeichert werden
// 18 = Eine Uninstall Datei konnte nicht in die Datenbank gespeichert werden

// ### logikSQLDatei
// 22 = Plugindaten fehlen
// 23 = SQL hat einen Fehler verursacht
// 24 = Versuch eine nicht Plugintabelle zu löschen
// 25 = Versuch eine nicht Plugintabelle anzulegen
// 26 = SQL Datei ist leer oder konnte nicht geparsed werden
// 27 = Sync Übergabeparameter nicht korrekt
// 28 = Update konnte nicht gesynct werden
*/

/**
 * Installiert ein Plugin
 *
 * @param array  $XML_arr
 * @param string $cVerzeichnis
 * @param Plugin $oPluginOld
 * @return int
 */
function installierePlugin($XML_arr, $cVerzeichnis, $oPluginOld)
{
    $nLastVersionKey   = count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1; // Finde aktuelle Version
    $nXMLVersion       = (int)$XML_arr['jtlshop3plugin'][0]['XMLVersion']; // XML Version
    $cLizenzKlasse     = '';
    $cLizenzKlasseName = '';
    $nStatus           = 2;
    $_tags             = [];
    $tagsToFlush       = [];
    if (isset($XML_arr['jtlshop3plugin'][0]['LicenceClass']) &&
        strlen($XML_arr['jtlshop3plugin'][0]['LicenceClass']) > 0 &&
        isset($XML_arr['jtlshop3plugin'][0]['LicenceClassFile']) &&
        strlen($XML_arr['jtlshop3plugin'][0]['LicenceClassFile']) > 0
    ) {
        $cLizenzKlasse     = $XML_arr['jtlshop3plugin'][0]['LicenceClass'];
        $cLizenzKlasseName = $XML_arr['jtlshop3plugin'][0]['LicenceClassFile'];
        $nStatus           = 5;
    }
    // tplugin füllen
    $oPlugin                       = new stdClass();
    $oPlugin->cName                = $XML_arr['jtlshop3plugin'][0]['Name'];
    $oPlugin->cBeschreibung        = $XML_arr['jtlshop3plugin'][0]['Description'];
    $oPlugin->cAutor               = $XML_arr['jtlshop3plugin'][0]['Author'];
    $oPlugin->cURL                 = $XML_arr['jtlshop3plugin'][0]['URL'];
    $oPlugin->cIcon                = isset($XML_arr['jtlshop3plugin'][0]['Icon'])
        ? $XML_arr['jtlshop3plugin'][0]['Icon']
        : null;
    $oPlugin->cVerzeichnis         = $cVerzeichnis;
    $oPlugin->cPluginID            = $XML_arr['jtlshop3plugin'][0]['PluginID'];
    $oPlugin->cFehler              = '';
    $oPlugin->cLizenz              = '';
    $oPlugin->cLizenzKlasse        = $cLizenzKlasse;
    $oPlugin->cLizenzKlasseName    = $cLizenzKlasseName;
    $oPlugin->nStatus              = $nStatus;
    $oPlugin->nVersion             = (int)$XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr'];
    $oPlugin->nXMLVersion          = $nXMLVersion;
    $oPlugin->nPrio                = 0;
    $oPlugin->dZuletztAktualisiert = 'now()';
    $oPlugin->dErstellt            = $XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$nLastVersionKey]['CreateDate'];
    $oPlugin->bBootstrap           = 0;

    if (is_file(PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
        PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . 'bootstrap.php')
    ) {
        $oPlugin->bBootstrap = 1;
    }

    if (!empty($XML_arr['jtlshop3plugin'][0]['Install'][0]['FlushTags'])) {
        $_tags = explode(',', $XML_arr['jtlshop3plugin'][0]['Install'][0]['FlushTags']);
    }

    foreach ($_tags as $_tag) {
        if (defined(trim($_tag))) {
            $tagsToFlush[] = constant(trim($_tag));
        }
    }
    if (count($tagsToFlush) > 0) {
        Shop::Cache()->flushTags($tagsToFlush);
    }

    if (isset($oPluginOld->cLizenz, $oPluginOld->nStatus) &&
        (int)$oPluginOld->nStatus > 0 &&
        strlen($oPluginOld->cLizenz) > 0 &&
        is_file(PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
            PFAD_PLUGIN_LICENCE . $oPlugin->cLizenzKlasseName)
    ) {
        require_once PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' .
            PFAD_PLUGIN_LICENCE . $oPlugin->cLizenzKlasseName;
        $oPluginLicence = new $oPlugin->cLizenzKlasse();
        $cLicenceMethod = PLUGIN_LICENCE_METHODE;
        if ($oPluginLicence->$cLicenceMethod($oPluginOld->cLizenz)) {
            $oPlugin->cLizenz = $oPluginOld->cLizenz;
            $oPlugin->nStatus = $oPluginOld->nStatus;
        }
    }
    $oPlugin->dInstalliert = (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0) ?
        $oPluginOld->dInstalliert :
        'now()';
    $kPlugin          = Shop::DB()->insert('tplugin', $oPlugin);
    $nVersion         = (int)$XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr'];
    $oPlugin->kPlugin = $kPlugin;

    if ($kPlugin > 0) {
        $res = installPluginTables($XML_arr, $oPlugin, $oPluginOld);

        if ($res > 0) {
            deinstallierePlugin($kPlugin, $nXMLVersion);

            return $res;
        }

        // SQL installieren
        $bSQLFehler   = false;
        $nReturnValue = 1;
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'] as $i => $Version_arr) {
            if (isset($oPluginOld->kPlugin, $Version_arr['nr']) &&
                $nVersion &&
                $oPluginOld->nVersion >= (int)$Version_arr['nr']
            ) {
                continue;
            }
            preg_match('/[0-9]+\sattr/', $i, $cTreffer1_arr);

            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                $nVersionTMP = (int)$Version_arr['nr'];
                $xy          = trim(str_replace('attr', '', $i));
                $cSQLDatei   = (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$xy]['SQL'])
                    ? $XML_arr['jtlshop3plugin'][0]['Install'][0]['Version'][$xy]['SQL']
                    : '');

                if (strlen($cSQLDatei) === 0) {
                    continue;
                }

                /*
                // Führe aktuelle SQL Datei aus
                // 1 = Alles O.K.
                // 22 = Plugindaten fehlen
                // 23 = SQL hat einen Fehler verursacht
                // 24 = Versuch eine nicht Plugintabelle zu löschen
                // 25 = Versuch eine nicht Plugintabelle anzulegen
                // 26 = SQL Datei ist leer oder konnte nicht geparsed werden
                */
                $nReturnValue       = logikSQLDatei($cSQLDatei, $nVersionTMP, $oPlugin);
                $nSQLFehlerCode_arr = [1 => 1, 2 => 22, 3 => 23, 4 => 24, 5 => 25, 6 => 26];
                $nReturnValue       = $nSQLFehlerCode_arr[$nReturnValue];

                if ($nReturnValue != 1) {
                    Jtllog::writeLog(
                        'SQL-Fehler bei der Plugin-Installation von kPlugin ' . $oPlugin->kPlugin . ', Fehlercode: ' .
                        $nReturnValue,
                        JTLLOG_LEVEL_ERROR,
                        false,
                        'kPlugin',
                        $kPlugin
                    );
                    $bSQLFehler = true;
                    break;
                }
            }
        }

        // Ist ein SQL Fehler aufgetreten? Wenn ja, deinstalliere wieder alles
        if ($bSQLFehler) {
            deinstallierePlugin($oPlugin->kPlugin, $nXMLVersion);
        }

        if ($nReturnValue === 1 && $oPluginOld === 0 && ($p = Plugin::bootstrapper($oPlugin->kPlugin)) !== null) {
            $p->installed();
        }

        // Installation von höheren XML Versionen
        if ($nXMLVersion > 100 && ($nReturnValue === 126 || $nReturnValue === 1)) {
            $nReturnValue = installierePluginVersion($XML_arr, $cVerzeichnis, $oPluginOld, $nXMLVersion);
            // Update
            if (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin > 0 && $nReturnValue === 1) {
                // Update erfolgreich => sync neue Version auf altes Plugin
                $nReturnValue       = syncPluginUpdate($oPlugin->kPlugin, $oPluginOld, $nXMLVersion);
                $nSQLFehlerCode_arr = [1 => 1, 2 => 27, 3 => 28];
                $nReturnValue       = $nSQLFehlerCode_arr[$nReturnValue];
            }
        } else {
            if (isset($oPluginOld->kPlugin) && $oPluginOld->kPlugin && ($nReturnValue === 126 || $nReturnValue === 1)) {
                // Update erfolgreich => sync neue Version auf altes Plugin
                $nReturnValue       = syncPluginUpdate($oPlugin->kPlugin, $oPluginOld, $nXMLVersion);
                $nSQLFehlerCode_arr = [1 => 1, 2 => 27, 3 => 28];
                $nReturnValue       = $nSQLFehlerCode_arr[$nReturnValue];
            }
        }

        return $nReturnValue;
    }

    return 2; // Main Plugindaten nicht korrekt
}

/**
 * Installiert die tplugin* Tabellen für ein Plugin in der Datenbank
 *
 * @param array  $XML_arr
 * @param object $oPlugin
 * @param object $oPluginOld
 * @return int
 */
function installPluginTables($XML_arr, $oPlugin, $oPluginOld)
{
    $kPlugin      = $oPlugin->kPlugin;
    $cVerzeichnis = $oPlugin->cVerzeichnis;
    $nVersion     = $oPlugin->nVersion;

    // used in ExportFormate
    $kKundengruppeStd = Kundengruppe::getDefaultGroupID();
    $oSprache         = gibStandardsprache(true);
    $kSpracheStd      = $oSprache->kSprache;
    $kWaehrungStd     = gibStandardWaehrung();

    // tpluginhook füllen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'])
    ) {
        if (count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0]) === 1) {
            // Es gibt mehr als einen Hook
            $nHookID   = 0;
            $nPriority = 5;
            foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0]['Hook'] as $i => $Hook_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nHookID   = (int)$Hook_arr['id'];
                    $nPriority = isset($Hook_arr['priority']) ? (int)$Hook_arr['priority'] : 5;
                } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($i)) {
                    $oPluginHook             = new stdClass();
                    $oPluginHook->kPlugin    = $kPlugin;
                    $oPluginHook->nHook      = $nHookID;
                    $oPluginHook->nPriority  = $nPriority;
                    $oPluginHook->cDateiname = $Hook_arr;

                    $kPluginHook = Shop::DB()->insert('tpluginhook', $oPluginHook);

                    if (!$kPluginHook) {
                        return 3;//Ein Hook konnte nicht in die Datenbank gespeichert werden
                    }
                }
            }
        } elseif (count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0]) > 1) {
            // Es gibt nur einen Hook
            $Hook_arr = $XML_arr['jtlshop3plugin'][0]['Install'][0]['Hooks'][0];

            $oPluginHook             = new stdClass();
            $oPluginHook->kPlugin    = $kPlugin;
            $oPluginHook->nHook      = (int)$Hook_arr['Hook attr']['id'];
            $oPluginHook->nPriority  = isset($Hook_arr['Hook attr']['priority'])
                ? (int)$Hook_arr['Hook attr']['priority']
                : 5;
            $oPluginHook->cDateiname = $Hook_arr['Hook'];

            $kPluginHook = Shop::DB()->insert('tpluginhook', $oPluginHook);

            if (!$kPluginHook) {
                return 3;//Ein Hook konnte nicht in die Datenbank gespeichert werden
            }
        }
    }
    // tpluginuninstall füllen
    if (isset($XML_arr['jtlshop3plugin'][0]['Uninstall']) &&
        strlen($XML_arr['jtlshop3plugin'][0]['Uninstall']) > 0
    ) {
        $oPluginUninstall             = new stdClass();
        $oPluginUninstall->kPlugin    = $kPlugin;
        $oPluginUninstall->cDateiname = $XML_arr['jtlshop3plugin'][0]['Uninstall'];

        $kPluginUninstall = Shop::DB()->insert('tpluginuninstall', $oPluginUninstall);

        if (!$kPluginUninstall) {
            return 18;//Eine Uninstall-Datei konnte nicht in die Datenbank gespeichert werden
        }
    }
    // tpluginadminmenu füllen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'])
    ) {
        // Adminsmenüs vorhanden?
        if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink']) &&
            is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink']) &&
            count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink']) > 0
        ) {
            $nSort = 0;
            foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Customlink'] as $i => $Customlink_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);

                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nSort = (int)$Customlink_arr['sort'];
                } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                    $oAdminMenu             = new stdClass();
                    $oAdminMenu->kPlugin    = $kPlugin;
                    $oAdminMenu->cName      = $Customlink_arr['Name'];
                    $oAdminMenu->cDateiname = $Customlink_arr['Filename'];
                    $oAdminMenu->nSort      = $nSort;
                    $oAdminMenu->nConf      = 0;

                    $kPluginAdminMenu = Shop::DB()->insert('tpluginadminmenu', $oAdminMenu);

                    if (!$kPluginAdminMenu) {
                        return 4;//Ein Adminmenü-Customlink konnte nicht in die Datenbank gespeichert werden
                    }
                }
            }
        }
        // Einstellungen vorhanden?
        if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink']) &&
            is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink']) &&
            count($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink']) > 0
        ) {
            $nSort = 0;
            foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Adminmenu'][0]['Settingslink'] as $i => $Settingslink_arr) {
                preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);
                preg_match("/[0-9]+/", $i, $cTreffer2_arr);
                if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                    $nSort = (int)$Settingslink_arr['sort'];
                } elseif (strlen($cTreffer2_arr[0]) === strlen($i)) {
                    // tpluginadminmenu füllen
                    $oAdminMenu             = new stdClass();
                    $oAdminMenu->kPlugin    = $kPlugin;
                    $oAdminMenu->cName      = $Settingslink_arr['Name'];
                    $oAdminMenu->cDateiname = '';
                    $oAdminMenu->nSort      = $nSort;
                    $oAdminMenu->nConf      = 1;

                    $kPluginAdminMenu = Shop::DB()->insert('tpluginadminmenu', $oAdminMenu);

                    if ($kPluginAdminMenu > 0) {
                        $cTyp          = '';
                        $cInitialValue = '';
                        $nSort         = 0;
                        $cConf         = 'Y';
                        $multiple      = false;
                        foreach ($Settingslink_arr['Setting'] as $j => $Setting_arr) {
                            preg_match("/[0-9]+\sattr/", $j, $cTreffer3_arr);
                            preg_match("/[0-9]+/", $j, $cTreffer4_arr);

                            if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                                $cTyp          = $Setting_arr['type'];
                                $multiple      = (isset($Setting_arr['multiple']) && $Setting_arr['multiple'] === 'Y' && $cTyp === 'selectbox');
                                $cInitialValue = ($multiple === true) ?
                                    serialize([$Setting_arr['initialValue']])
                                    : $Setting_arr['initialValue'];
                                $nSort         = $Setting_arr['sort'];
                                $cConf         = $Setting_arr['conf'];
                            } elseif (strlen($cTreffer4_arr[0]) === strlen($j)) {
                                // tplugineinstellungen füllen
                                $oPluginEinstellungen          = new stdClass();
                                $oPluginEinstellungen->kPlugin = $kPlugin;
                                $oPluginEinstellungen->cName   = is_array($Setting_arr['ValueName'])
                                    ? $Setting_arr['ValueName']['0']
                                    : $Setting_arr['ValueName'];
                                $oPluginEinstellungen->cWert   = $cInitialValue;

                                Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellungen);
                                // tplugineinstellungenconf füllen
                                $oPluginEinstellungenConf                   = new stdClass();
                                $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                                $oPluginEinstellungenConf->kPluginAdminMenu = $kPluginAdminMenu;
                                $oPluginEinstellungenConf->cName            = $Setting_arr['Name'];
                                $oPluginEinstellungenConf->cBeschreibung    = (!isset($Setting_arr['Description']) || is_array($Setting_arr['Description']))
                                    ? ''
                                    : $Setting_arr['Description'];
                                $oPluginEinstellungenConf->cWertName = is_array($Setting_arr['ValueName'])
                                    ? $Setting_arr['ValueName']['0']
                                    : $Setting_arr['ValueName'];
                                $oPluginEinstellungenConf->cInputTyp = $cTyp;
                                $oPluginEinstellungenConf->nSort     = $nSort;
                                $oPluginEinstellungenConf->cConf     = $cConf;
                                //dynamic data source for selectbox/radio
                                if ($cTyp === 'selectbox' || $cTyp === 'radio') {
                                    if (isset($Setting_arr['OptionsSource'][0]['File'])) {
                                        $oPluginEinstellungenConf->cSourceFile = $Setting_arr['OptionsSource'][0]['File'];
                                    }
                                    if ($multiple === true) {
                                        $oPluginEinstellungenConf->cConf = 'M';
                                    }
                                }
                                $kPluginEinstellungenConf = Shop::DB()->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
                                // tplugineinstellungenconfwerte füllen
                                if ($kPluginEinstellungenConf > 0) {
                                    $nSort = 0;
                                    // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                    if ($cTyp === 'selectbox') {
                                        if (isset($Setting_arr['OptionsSource']) && is_array($Setting_arr['OptionsSource']) && count($Setting_arr['OptionsSource']) > 0) {
                                            //do nothing for now
                                        } elseif (count($Setting_arr['SelectboxOptions'][0]) === 1) { // Es gibt mehr als 1 Option
                                            foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                                preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);

                                                if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                    $cWert = $Option_arr['value'];
                                                    $nSort = $Option_arr['sort'];
                                                    $yx    = substr($y, 0, strpos($y, ' '));
                                                    $cName = $Setting_arr['SelectboxOptions'][0]['Option'][$yx];

                                                    $oPluginEinstellungenConfWerte                           = new stdClass();
                                                    $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                    $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                    $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                    $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                    Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                                }
                                            }
                                        } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) {
                                            // Es gibt nur eine Option
                                            $oPluginEinstellungenConfWerte                           = new stdClass();
                                            $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                            $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['SelectboxOptions'][0]['Option'];
                                            $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['value'];
                                            $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['sort'];

                                            Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                        }
                                    } elseif ($cTyp === 'radio') {
                                        if (isset($Setting_arr['OptionsSource']) && is_array($Setting_arr['OptionsSource']) && count($Setting_arr['OptionsSource']) > 0) {

                                        } elseif (count($Setting_arr['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                                            foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                                preg_match("/[0-9]+\sattr/", $y, $cTreffer6_arr);
                                                if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                    $cWert = $Option_arr['value'];
                                                    $nSort = $Option_arr['sort'];
                                                    $yx    = substr($y, 0, strpos($y, ' '));
                                                    $cName = $Setting_arr['RadioOptions'][0]['Option'][$yx];

                                                    $oPluginEinstellungenConfWerte                           = new stdClass();
                                                    $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                    $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                    $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                    $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                    Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                                }
                                            }
                                        } elseif (count($Setting_arr['RadioOptions'][0]) === 2) {
                                            // Es gibt nur eine Option
                                            $oPluginEinstellungenConfWerte                           = new stdClass();
                                            $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                            $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['RadioOptions'][0]['Option'];
                                            $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['RadioOptions'][0]['Option attr']['value'];
                                            $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['RadioOptions'][0]['Option attr']['sort'];

                                            Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                        }
                                    }
                                } else {
                                    return 6;// Eine Einstellung konnte nicht in die Datenbank gespeichert werden
                                }
                            }
                        }
                    } else {
                        return 5;// Ein Adminmenü Settingslink konnte nicht in die Datenbank gespeichert werden
                    }
                }
            }
        }
    }
    // FrontendLinks (falls vorhanden)
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'])
    ) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['FrontendLink'][0]['Link'] as $u => $Link_arr) {
            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
            $oLink       = new stdClass();
            if (empty($Link_arr['LinkGroup'])) {
                // linkgroup not set? default to 'hidden'
                $Link_arr['LinkGroup'] = 'hidden';
            }
            $oLinkgruppe = Shop::DB()->select('tlinkgruppe', 'cName', $Link_arr['LinkGroup']);
            if ($oLinkgruppe === null) {
                // linkgroup not in database? create it anew
                $oLinkgruppe                = new stdClass();
                $oLinkgruppe->cName         = $Link_arr['LinkGroup'];
                $oLinkgruppe->cTemplatename = $Link_arr['LinkGroup'];
                $oLinkgruppe->kLinkgruppe   = Shop::DB()->insert('tlinkgruppe', $oLinkgruppe);
            }
            if (isset($oLinkgruppe->kLinkgruppe) && $oLinkgruppe->kLinkgruppe > 0) {
                $kLinkgruppe = $oLinkgruppe->kLinkgruppe;
            } else {
                return 12; // Es konnte keine Linkgruppe im Shop gefunden werden
            }

            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                $kLinkOld                  = (!empty($oPluginOld->kPlugin))
                    ? Shop::DB()->select('tlink', 'kPlugin', $oPluginOld->kPlugin, 'cName', $Link_arr['Name'])
                    : null;
                $oLink->kLinkgruppe        = $kLinkgruppe;
                $oLink->kPlugin            = $kPlugin;
                $oLink->cName              = $Link_arr['Name'];
                $oLink->nLinkart           = LINKTYP_PLUGIN;
                $oLink->cSichtbarNachLogin = $Link_arr['VisibleAfterLogin'];
                $oLink->cDruckButton       = $Link_arr['PrintButton'];
                $oLink->cNoFollow          = isset($Link_arr['NoFollow']) ? $Link_arr['NoFollow'] : null;
                $oLink->nSort              = LINKTYP_PLUGIN;
                $oLink->bSSL               = isset($Link_arr['SSL'])
                    ? (int)$Link_arr['SSL']
                    : 0;
                // tlink füllen
                $kLink = Shop::DB()->insert('tlink', $oLink);

                if ($kLink > 0) {
                    $oLinkSprache        = new stdClass();
                    $oLinkSprache->kLink = $kLink;
                    // Hole alle Sprachen des Shops
                    // Assoc cISO
                    $oSprachAssoc_arr = gibAlleSprachen(2);
                    // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
                    // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
                    // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
                    $bLinkStandard   = false;
                    $oLinkSpracheStd = new stdClass();

                    foreach ($Link_arr['LinkLanguage'] as $l => $LinkLanguage_arr) {
                        preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                        preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                        if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                            $oLinkSprache->cISOSprache = strtolower($LinkLanguage_arr['iso']);
                        } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                            // tlinksprache füllen
                            $oLinkSprache->cSeo             = checkSeo(getSeo($LinkLanguage_arr['Seo']));
                            $oLinkSprache->cName            = $LinkLanguage_arr['Name'];
                            $oLinkSprache->cTitle           = $LinkLanguage_arr['Title'];
                            $oLinkSprache->cContent         = '';
                            $oLinkSprache->cMetaTitle       = $LinkLanguage_arr['MetaTitle'];
                            $oLinkSprache->cMetaKeywords    = $LinkLanguage_arr['MetaKeywords'];
                            $oLinkSprache->cMetaDescription = $LinkLanguage_arr['MetaDescription'];

                            Shop::DB()->insert('tlinksprache', $oLinkSprache);
                            // Erste Linksprache vom Plugin als Standard setzen
                            if (!$bLinkStandard) {
                                $oLinkSpracheStd = $oLinkSprache;
                                $bLinkStandard   = true;
                            }

                            if ($oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache > 0) {
                                $or = isset($kLinkOld->kLink) ? (' OR kKey = ' . (int)$kLinkOld->kLink) : '';
                                Shop::DB()->query(
                                    "DELETE FROM tseo
                                            WHERE cKey = 'kLink'
                                                AND (kKey = " . (int)$kLink . $or . ")
                                                AND kSprache = " . (int)$oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache, 4
                                );
                                // tseo füllen
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = checkSeo(getSeo($LinkLanguage_arr['Seo']));
                                $oSeo->cKey     = 'kLink';
                                $oSeo->kKey     = $kLink;
                                $oSeo->kSprache = $oSprachAssoc_arr[$oLinkSprache->cISOSprache]->kSprache;

                                Shop::DB()->insert('tseo', $oSeo);
                            }

                            if (isset($oSprachAssoc_arr[$oLinkSprache->cISOSprache])) {
                                // Resette aktuelle Sprache
                                unset($oSprachAssoc_arr[$oLinkSprache->cISOSprache]);
                                $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                            }
                        }
                    }
                    // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
                    if (count($oSprachAssoc_arr) > 0) {
                        foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                            //$oSprache = $oSprachAssoc;
                            if ($oSprachAssoc->kSprache > 0) {
                                Shop::DB()->delete(
                                    'tseo',
                                    ['cKey', 'kKey', 'kSprache'],
                                    ['kLink', (int)$kLink, (int)$oSprachAssoc->kSprache]
                                );
                                // tseo füllen
                                $oSeo           = new stdClass();
                                $oSeo->cSeo     = checkSeo(getSeo($oLinkSpracheStd->cSeo));
                                $oSeo->cKey     = 'kLink';
                                $oSeo->kKey     = $kLink;
                                $oSeo->kSprache = $oSprachAssoc->kSprache;

                                Shop::DB()->insert('tseo', $oSeo);
                                // tlinksprache füllen
                                $oLinkSpracheStd->cSeo        = $oSeo->cSeo;
                                $oLinkSpracheStd->cISOSprache = $oSprachAssoc->cISO;
                                Shop::DB()->insert('tlinksprache', $oLinkSpracheStd);
                            }
                        }
                    }
                    // tpluginhook füllen (spezieller Ausnahmefall für Frontend Links)
                    $oPluginHook             = new stdClass();
                    $oPluginHook->kPlugin    = $kPlugin;
                    $oPluginHook->nHook      = HOOK_SEITE_PAGE_IF_LINKART;
                    $oPluginHook->cDateiname = PLUGIN_SEITENHANDLER;

                    $kPluginHook = Shop::DB()->insert('tpluginhook', $oPluginHook);

                    if (!$kPluginHook) {
                        return 3; // Ein Hook konnte nicht in die Datenbank gespeichert werden
                    }
                    // tpluginlinkdatei füllen
                    $oPluginLinkDatei                      = new stdClass();
                    $oPluginLinkDatei->kPlugin             = $kPlugin;
                    $oPluginLinkDatei->kLink               = $kLink;
                    $oPluginLinkDatei->cDatei              = isset($Link_arr['Filename'])
                        ? $Link_arr['Filename']
                        : null;
                    $oPluginLinkDatei->cTemplate           = isset($Link_arr['Template'])
                        ? $Link_arr['Template']
                        : null;
                    $oPluginLinkDatei->cFullscreenTemplate = isset($Link_arr['FullscreenTemplate'])
                        ? $Link_arr['FullscreenTemplate']
                        : null;

                    Shop::DB()->insert('tpluginlinkdatei', $oPluginLinkDatei);
                } else {
                    return 8; // Ein Link konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
    }
    // Zahlungsmethode (PaymentMethod) (falls vorhanden)
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod']) &&
        isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) &&
        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method']) > 0
    ) {
        $shopURL = Shop::getURL(true) . '/';
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['PaymentMethod'][0]['Method'] as $u => $Method_arr) {
            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                $oZahlungsart                         = new stdClass();
                $oZahlungsart->cName                  = $Method_arr['Name'];
                $oZahlungsart->cModulId               = gibPlugincModulId($kPlugin, $Method_arr['Name']);
                $oZahlungsart->cKundengruppen         = '';
                $oZahlungsart->cPluginTemplate        = isset($Method_arr['TemplateFile'])
                    ? $Method_arr['TemplateFile']
                    : null;
                $oZahlungsart->cZusatzschrittTemplate = isset($Method_arr['AdditionalTemplateFile'])
                    ? $Method_arr['AdditionalTemplateFile']
                    : null;
                $oZahlungsart->nSort                  = isset($Method_arr['Sort'])
                    ? (int)$Method_arr['Sort']
                    : 0;
                $oZahlungsart->nMailSenden            = isset($Method_arr['SendMail'])
                    ? (int)$Method_arr['SendMail']
                    : 0;
                $oZahlungsart->nActive                = 1;
                $oZahlungsart->cAnbieter              = is_array($Method_arr['Provider'])
                    ? ''
                    : $Method_arr['Provider'];
                $oZahlungsart->cTSCode                = is_array($Method_arr['TSCode'])
                    ? ''
                    : $Method_arr['TSCode'];
                $oZahlungsart->nWaehrendBestellung    = (int)$Method_arr['PreOrder'];
                $oZahlungsart->nCURL                  = (int)$Method_arr['Curl'];
                $oZahlungsart->nSOAP                  = (int)$Method_arr['Soap'];
                $oZahlungsart->nSOCKETS               = (int)$Method_arr['Sockets'];
                $oZahlungsart->cBild                  = isset($Method_arr['PictureURL'])
                    ? $shopURL . PFAD_PLUGIN . $cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $nVersion . '/' .
                    PFAD_PLUGIN_PAYMENTMETHOD . $Method_arr['PictureURL']
                    : '';
                $oZahlungsart->nNutzbar = 0;
                $bPruefen               = false;
                if ($oZahlungsart->nCURL == 0 && $oZahlungsart->nSOAP == 0 && $oZahlungsart->nSOCKETS == 0) {
                    $oZahlungsart->nNutzbar = 1;
                } else {
                    $bPruefen = true;
                }
                $kZahlungsart               = Shop::DB()->insert('tzahlungsart', $oZahlungsart);
                $oZahlungsart->kZahlungsart = $kZahlungsart;

                if ($bPruefen) {
                    aktiviereZahlungsart($oZahlungsart);
                }

                $cModulId = $oZahlungsart->cModulId;

                if (!$kZahlungsart) {
                    return 9; //Eine Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
                }
                // tpluginzahlungsartklasse füllen
                $oPluginZahlungsartKlasse                         = new stdClass();
                $oPluginZahlungsartKlasse->cModulId               = gibPlugincModulId($kPlugin, $Method_arr['Name']);
                $oPluginZahlungsartKlasse->kPlugin                = $kPlugin;
                $oPluginZahlungsartKlasse->cClassPfad             = isset($Method_arr['ClassFile'])
                    ? $Method_arr['ClassFile']
                    : null;
                $oPluginZahlungsartKlasse->cClassName             = isset($Method_arr['ClassName'])
                    ? $Method_arr['ClassName']
                    : null;
                $oPluginZahlungsartKlasse->cTemplatePfad          = isset($Method_arr['TemplateFile'])
                    ? $Method_arr['TemplateFile']
                    : null;
                $oPluginZahlungsartKlasse->cZusatzschrittTemplate = isset($Method_arr['AdditionalTemplateFile'])
                    ? $Method_arr['AdditionalTemplateFile']
                    : null;

                Shop::DB()->insert('tpluginzahlungsartklasse', $oPluginZahlungsartKlasse);

                $cISOSprache = '';
                // Hole alle Sprachen des Shops
                // Assoc cISO
                $oSprachAssoc_arr = gibAlleSprachen(2);
                // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
                // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
                // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
                $bZahlungsartStandard   = false;
                $oZahlungsartSpracheStd = new stdClass();

                foreach ($Method_arr['MethodLanguage'] as $l => $MethodLanguage_arr) {
                    preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                    preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                    if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                        $cISOSprache = strtolower($MethodLanguage_arr['iso']);
                    } elseif (strlen($cTreffer2_arr[0]) === strlen($l)) {
                        $oZahlungsartSprache               = new stdClass();
                        $oZahlungsartSprache->kZahlungsart = $kZahlungsart;
                        $oZahlungsartSprache->cISOSprache  = $cISOSprache;
                        $oZahlungsartSprache->cName        = $MethodLanguage_arr['Name'];
                        $oZahlungsartSprache->cGebuehrname = $MethodLanguage_arr['ChargeName'];
                        $oZahlungsartSprache->cHinweisText = $MethodLanguage_arr['InfoText'];
                        // Erste ZahlungsartSprache vom Plugin als Standard setzen
                        if (!$bZahlungsartStandard) {
                            $oZahlungsartSpracheStd = $oZahlungsartSprache;
                            $bZahlungsartStandard   = true;
                        }
                        $kZahlungsartTMP = Shop::DB()->insert('tzahlungsartsprache', $oZahlungsartSprache);
                        if (!$kZahlungsartTMP) {
                            return 10; // Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
                        }

                        if (isset($oSprachAssoc_arr[$oZahlungsartSprache->cISOSprache])) {
                            // Resette aktuelle Sprache
                            unset($oSprachAssoc_arr[$oZahlungsartSprache->cISOSprache]);
                            $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                        }
                    }
                }

                // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
                if (count($oSprachAssoc_arr) > 0) {
                    foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                        $oZahlungsartSpracheStd->cISOSprache = $oSprachAssoc->cISO;
                        $kZahlungsartTMP                     = Shop::DB()->insert('tzahlungsartsprache', $oZahlungsartSpracheStd);
                        if (!$kZahlungsartTMP) {
                            return 10; // Eine Sprache in den Zahlungsmethoden konnte nicht in die Datenbank gespeichert werden
                        }
                    }
                }
                // Zahlungsmethode Einstellungen
                // Vordefinierte Einstellungen
                $cName_arr         = ['Anzahl Bestellungen n&ouml;tig', 'Mindestbestellwert', 'Maximaler Bestellwert'];
                $cWertName_arr     = ['min_bestellungen', 'min', 'max'];
                $cBeschreibung_arr = [
                    'Nur Kunden, die min. soviele Bestellungen bereits durchgef&uuml;hrt haben, k&ouml;nnen diese Zahlungsart nutzen.',
                    'Erst ab diesem Bestellwert kann diese Zahlungsart genutzt werden.',
                    'Nur bis zu diesem Bestellwert wird diese Zahlungsart angeboten. (einschliesslich)'];
                $nSort_arr         = [100, 101, 102];

                for ($z = 0; $z < 3; $z++) {
                    // tplugineinstellungen füllen
                    $oPluginEinstellungen          = new stdClass();
                    $oPluginEinstellungen->kPlugin = $kPlugin;
                    $oPluginEinstellungen->cName   = $cModulId . '_' . $cWertName_arr[$z];
                    $oPluginEinstellungen->cWert   = 0;

                    Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellungen);
                    // tplugineinstellungenconf füllen
                    $oPluginEinstellungenConf                   = new stdClass();
                    $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                    $oPluginEinstellungenConf->kPluginAdminMenu = 0;
                    $oPluginEinstellungenConf->cName            = $cName_arr[$z];
                    $oPluginEinstellungenConf->cBeschreibung    = $cBeschreibung_arr[$z];
                    $oPluginEinstellungenConf->cWertName        = $cModulId . '_' . $cWertName_arr[$z];
                    $oPluginEinstellungenConf->cInputTyp        = 'zahl';
                    $oPluginEinstellungenConf->nSort            = $nSort_arr[$z];
                    $oPluginEinstellungenConf->cConf            = 'Y';

                    Shop::DB()->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
                }

                if (isset($Method_arr['Setting']) &&
                    is_array($Method_arr['Setting']) &&
                    count($Method_arr['Setting']) > 0
                ) {
                    $cTyp          = '';
                    $cInitialValue = '';
                    $nSort         = 0;
                    $cConf         = 'Y';
                    $multiple      = false;
                    foreach ($Method_arr['Setting'] as $j => $Setting_arr) {
                        preg_match('/[0-9]+\sattr/', $j, $cTreffer3_arr);
                        preg_match('/[0-9]+/', $j, $cTreffer4_arr);

                        if (isset($cTreffer3_arr[0]) && strlen($cTreffer3_arr[0]) === strlen($j)) {
                            $cTyp          = $Setting_arr['type'];
                            $multiple      = (isset($Setting_arr['multiple']) && $Setting_arr['multiple'] === 'Y' && $cTyp === 'selectbox');
                            $cInitialValue = ($multiple === true)
                                ? serialize([$Setting_arr['initialValue']])
                                : $Setting_arr['initialValue'];
                            $nSort         = $Setting_arr['sort'];
                            $cConf         = $Setting_arr['conf'];
                        } elseif (strlen($cTreffer4_arr[0]) === strlen($j)) {
                            // tplugineinstellungen füllen
                            $oPluginEinstellungen          = new stdClass();
                            $oPluginEinstellungen->kPlugin = $kPlugin;
                            $oPluginEinstellungen->cName   = $cModulId . '_' . $Setting_arr['ValueName'];
                            $oPluginEinstellungen->cWert   = $cInitialValue;

                            Shop::DB()->insert('tplugineinstellungen', $oPluginEinstellungen);

                            // tplugineinstellungenconf füllen
                            $oPluginEinstellungenConf                   = new stdClass();
                            $oPluginEinstellungenConf->kPlugin          = $kPlugin;
                            $oPluginEinstellungenConf->kPluginAdminMenu = 0;
                            $oPluginEinstellungenConf->cName            = $Setting_arr['Name'];
                            $oPluginEinstellungenConf->cBeschreibung    = (!isset($Setting_arr['Description']) || is_array($Setting_arr['Description']))
                                ? ''
                                : $Setting_arr['Description'];
                            $oPluginEinstellungenConf->cWertName = $cModulId . '_' . $Setting_arr['ValueName'];
                            $oPluginEinstellungenConf->cInputTyp = $cTyp;
                            $oPluginEinstellungenConf->nSort     = $nSort;
                            $oPluginEinstellungenConf->cConf     = ($cTyp === 'selectbox' && $multiple === true)
                                ? 'M'
                                : $cConf;

                            $kPluginEinstellungenConf = Shop::DB()->insert('tplugineinstellungenconf', $oPluginEinstellungenConf);
                            // tplugineinstellungenconfwerte füllen
                            if ($kPluginEinstellungenConf > 0) {
                                // Ist der Typ eine Selectbox => Es müssen SelectboxOptionen vorhanden sein
                                if ($cTyp === 'selectbox') {
                                    if (isset($Setting_arr['OptionsSource']) &&
                                        is_array($Setting_arr['OptionsSource']) &&
                                        count($Setting_arr['OptionsSource']) > 0
                                    ) {
                                        //do nothing for now
                                    } elseif (count($Setting_arr['SelectboxOptions'][0]) === 1) {
                                        // Es gibt mehr als eine Option
                                        foreach ($Setting_arr['SelectboxOptions'][0]['Option'] as $y => $Option_arr) {
                                            preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);

                                            if (isset($cTreffer6_arr[0]) && strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                $cWert = $Option_arr['value'];
                                                $nSort = $Option_arr['sort'];
                                                $yx    = substr($y, 0, strpos($y, ' '));
                                                $cName = $Setting_arr['SelectboxOptions'][0]['Option'][$yx];

                                                $oPluginEinstellungenConfWerte                           = new stdClass();
                                                $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                            }
                                        }
                                    } elseif (count($Setting_arr['SelectboxOptions'][0]) === 2) {
                                        // Es gibt nur eine Option
                                        $oPluginEinstellungenConfWerte                           = new stdClass();
                                        $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['SelectboxOptions'][0]['Option'];
                                        $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['value'];
                                        $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['SelectboxOptions'][0]['Option attr']['sort'];

                                        Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                    }
                                } elseif ($cTyp === 'radio') {
                                    if (isset($Setting_arr['OptionsSource']) &&
                                        is_array($Setting_arr['OptionsSource']) &&
                                        count($Setting_arr['OptionsSource']) > 0
                                    ) {
                                        //do nothing for now
                                    } elseif (count($Setting_arr['RadioOptions'][0]) === 1) { // Es gibt mehr als eine Option
                                        foreach ($Setting_arr['RadioOptions'][0]['Option'] as $y => $Option_arr) {
                                            preg_match('/[0-9]+\sattr/', $y, $cTreffer6_arr);
                                            if (strlen($cTreffer6_arr[0]) === strlen($y)) {
                                                $cWert = $Option_arr['value'];
                                                $nSort = $Option_arr['sort'];
                                                $yx    = substr($y, 0, strpos($y, ' '));
                                                $cName = $Setting_arr['RadioOptions'][0]['Option'][$yx];

                                                $oPluginEinstellungenConfWerte                           = new stdClass();
                                                $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                                $oPluginEinstellungenConfWerte->cName                    = $cName;
                                                $oPluginEinstellungenConfWerte->cWert                    = $cWert;
                                                $oPluginEinstellungenConfWerte->nSort                    = $nSort;

                                                Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                            }
                                        }
                                    } elseif (count($Setting_arr['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                                        $oPluginEinstellungenConfWerte                           = new stdClass();
                                        $oPluginEinstellungenConfWerte->kPluginEinstellungenConf = $kPluginEinstellungenConf;
                                        $oPluginEinstellungenConfWerte->cName                    = $Setting_arr['RadioOptions'][0]['Option'];
                                        $oPluginEinstellungenConfWerte->cWert                    = $Setting_arr['RadioOptions'][0]['Option attr']['value'];
                                        $oPluginEinstellungenConfWerte->nSort                    = $Setting_arr['RadioOptions'][0]['Option attr']['sort'];

                                        Shop::DB()->insert('tplugineinstellungenconfwerte', $oPluginEinstellungenConfWerte);
                                    }
                                }
                            } else {
                                return 11; // Eine Einstellung der Zahlungsmethode konnte nicht in die Datenbank gespeichert werden
                            }
                        }
                    }
                }
            }
        }
    }
    // tboxvorlage füllen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'])
    ) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Boxes'][0]['Box'] as $h => $Box_arr) {
            preg_match("/[0-9]+/", $h, $cTreffer3_arr);
            if (strlen($cTreffer3_arr[0]) === strlen($h)) {
                $oBoxvorlage              = new stdClass();
                $oBoxvorlage->kCustomID   = $kPlugin;
                $oBoxvorlage->eTyp        = 'plugin';
                $oBoxvorlage->cName       = $Box_arr['Name'];
                $oBoxvorlage->cVerfuegbar = $Box_arr['Available'];
                $oBoxvorlage->cTemplate   = $Box_arr['TemplateFile'];

                $kBoxvorlage = Shop::DB()->insert('tboxvorlage', $oBoxvorlage);

                if (!$kBoxvorlage) {
                    return 13; //Eine Boxvorlage konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
    }
    // tplugintemplate füllen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'])
    ) {
        $cTemplate_arr = (array)$XML_arr['jtlshop3plugin'][0]['Install'][0]['ExtendedTemplates'][0]['Template'];

        foreach ($cTemplate_arr as $cTemplate) {
            preg_match("/[a-zA-Z0-9\/_\-]+\.tpl/", $cTemplate, $cTreffer3_arr);
            if (strlen($cTreffer3_arr[0]) === strlen($cTemplate)) {
                $oPluginTemplate            = new stdClass();
                $oPluginTemplate->kPlugin   = $kPlugin;
                $oPluginTemplate->cTemplate = $cTemplate;

                $kPluginTemplate = Shop::DB()->insert('tplugintemplate', $oPluginTemplate);

                if (!$kPluginTemplate) {
                    return 17; //Ein Template konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
    }
    // Emailtemplates (falls vorhanden)
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'])
    ) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Emailtemplate'][0]['Template'] as $u => $Template_arr) {
            preg_match("/[0-9]+\sattr/", $u, $cTreffer1_arr);
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);

            $oTemplate = new stdClass();
            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                $oTemplate->kPlugin       = $kPlugin;
                $oTemplate->cName         = $Template_arr['Name'];
                $oTemplate->cBeschreibung = is_array($Template_arr['Description'])
                    ? $Template_arr['Description'][0]
                    : $Template_arr['Description'];
                $oTemplate->cMailTyp      = isset($Template_arr['Type'])
                    ? $Template_arr['Type']
                    : 'text/html';
                $oTemplate->cModulId      = $Template_arr['ModulId'];
                $oTemplate->cDateiname    = isset($Template_arr['Filename'])
                    ? $Template_arr['Filename']
                    : null;
                $oTemplate->cAktiv        = isset($Template_arr['Active'])
                    ? $Template_arr['Active']
                    : 'N';
                $oTemplate->nAKZ          = isset($Template_arr['AKZ'])
                    ? $Template_arr['AKZ']
                    : 0;
                $oTemplate->nAGB          = isset($Template_arr['AGB'])
                    ? $Template_arr['AGB']
                    : 0;
                $oTemplate->nWRB          = isset($Template_arr['WRB'])
                    ? $Template_arr['WRB']
                    : 0;
                $oTemplate->nWRBForm      = isset($Template_arr['WRBForm'])
                    ? $Template_arr['WRBForm']
                    : 0;
                // tpluginemailvorlage füllen
                $kEmailvorlage = Shop::DB()->insert('tpluginemailvorlage', $oTemplate);

                if ($kEmailvorlage > 0) {
                    $oTemplateSprache                = new stdClass();
                    $cISOSprache                     = '';
                    $oTemplateSprache->kEmailvorlage = $kEmailvorlage;
                    // Hole alle Sprachen des Shops
                    // Assoc cISO
                    $oSprachAssoc_arr = gibAlleSprachen(2);
                    // Ist das erste Standard Template gesetzt worden? => wird etwas weiter unten gebraucht
                    // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
                    // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
                    $bTemplateStandard   = false;
                    $oTemplateSpracheStd = new stdClass();

                    foreach ($Template_arr['TemplateLanguage'] as $l => $TemplateLanguage_arr) {
                        preg_match("/[0-9]+\sattr/", $l, $cTreffer1_arr);
                        preg_match("/[0-9]+/", $l, $cTreffer2_arr);
                        if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($l)) {
                            $cISOSprache = strtolower($TemplateLanguage_arr['iso']);
                        } elseif (isset($cTreffer2_arr[0]) && strlen($cTreffer2_arr[0]) === strlen($l)) {
                            // tpluginemailvorlagesprache füllen
                            $oTemplateSprache->kEmailvorlage = $kEmailvorlage;
                            $oTemplateSprache->kSprache      = $oSprachAssoc_arr[$cISOSprache]->kSprache;
                            $oTemplateSprache->cBetreff      = $TemplateLanguage_arr['Subject'];
                            $oTemplateSprache->cContentHtml  = $TemplateLanguage_arr['ContentHtml'];
                            $oTemplateSprache->cContentText  = $TemplateLanguage_arr['ContentText'];
                            $oTemplateSprache->cPDFS         = isset($TemplateLanguage_arr['PDFS'])
                                ? $TemplateLanguage_arr['PDFS']
                                : null;
                            $oTemplateSprache->cDateiname    = isset($TemplateLanguage_arr['Filename'])
                                ? $TemplateLanguage_arr['Filename']
                                : null;

                            if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
                                Shop::DB()->insert('tpluginemailvorlagesprache', $oTemplateSprache);
                            }

                            Shop::DB()->insert('tpluginemailvorlagespracheoriginal', $oTemplateSprache);
                            // Erste Templatesprache vom Plugin als Standard setzen
                            if (!$bTemplateStandard) {
                                $oTemplateSpracheStd = $oTemplateSprache;
                                $bTemplateStandard   = true;
                            }

                            if (isset($oSprachAssoc_arr[$cISOSprache])) {
                                // Resette aktuelle Sprache
                                unset($oSprachAssoc_arr[$cISOSprache]);
                                $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                            }
                        }
                    }
                    // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
                    if (count($oSprachAssoc_arr) > 0) {
                        foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                            //$oSprache = $oSprachAssoc;
                            if ($oSprachAssoc->kSprache > 0) {
                                // tpluginemailvorlagesprache füllen
                                $oTemplateSpracheStd->kSprache = $oSprachAssoc->kSprache;

                                if (!isset($oPluginOld->kPlugin) || !$oPluginOld->kPlugin) {
                                    Shop::DB()->insert('tpluginemailvorlagesprache', $oTemplateSpracheStd);
                                }

                                Shop::DB()->insert('tpluginemailvorlagespracheoriginal', $oTemplateSpracheStd);
                            }
                        }
                    }
                } else {
                    return 14; //Eine Emailvorlage konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
    }
    // tpluginsprachvariable + tpluginsprachvariablesprache füllen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'])
    ) {
        // Hole alle Sprachen des Shops
        // Assoc cISO
        $oSprachStandardAssoc_arr = gibAlleSprachen(2);

        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['Locales'][0]['Variable'] as $t => $Variable_arr) {
            $oSprachAssoc_arr = $oSprachStandardAssoc_arr;
            preg_match("/[0-9]+/", $t, $cTreffer1_arr);
            if (strlen($cTreffer1_arr[0]) === strlen($t)) {
                // tpluginsprachvariable füllen
                $oPluginSprachVariable          = new stdClass();
                $oPluginSprachVariable->kPlugin = $kPlugin;
                $oPluginSprachVariable->cName   = $Variable_arr['Name'];
                if (isset($Variable_arr['Description']) && is_array($Variable_arr['Description'])) {
                    $oPluginSprachVariable->cBeschreibung = '';
                } else {
                    $oPluginSprachVariable->cBeschreibung = preg_replace('/\s+/', ' ', $Variable_arr['Description']);
                }

                $kPluginSprachvariable = Shop::DB()->insert('tpluginsprachvariable', $oPluginSprachVariable);

                if ($kPluginSprachvariable > 0) {
                    // Ist der erste Standard Link gesetzt worden? => wird etwas weiter unten gebraucht
                    // Falls Shopsprachen vom Plugin nicht berücksichtigt wurden, werden diese weiter unten
                    // nachgetragen. Dafür wird die erste Sprache vom Plugin als Standard genutzt.
                    $bVariableStandard   = false;
                    $oVariableSpracheStd = new stdClass();
                    // Nur eine Sprache vorhanden
                    if (isset($Variable_arr['VariableLocalized attr']) &&
                        is_array($Variable_arr['VariableLocalized attr']) &&
                        count($Variable_arr['VariableLocalized attr']) > 0
                    ) {
                        // tpluginsprachvariablesprache füllen
                        $oPluginSprachVariableSprache                        = new stdClass();
                        $oPluginSprachVariableSprache->kPluginSprachvariable = $kPluginSprachvariable;
                        $oPluginSprachVariableSprache->cISO                  = $Variable_arr['VariableLocalized attr']['iso'];
                        $oPluginSprachVariableSprache->cName                 = preg_replace('/\s+/', ' ', $Variable_arr['VariableLocalized']);

                        Shop::DB()->insert('tpluginsprachvariablesprache', $oPluginSprachVariableSprache);

                        // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                        if (!$bVariableStandard) {
                            $oVariableSpracheStd = $oPluginSprachVariableSprache;
                            $bVariableStandard   = true;
                        }

                        if (isset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)])) {
                            // Resette aktuelle Sprache
                            unset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)]);
                            $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                        }
                    } elseif (isset($Variable_arr['VariableLocalized']) &&
                        is_array($Variable_arr['VariableLocalized']) &&
                        count($Variable_arr['VariableLocalized']) > 0
                    ) {
                        // Mehr Sprachen vorhanden
                        foreach ($Variable_arr['VariableLocalized'] as $i => $VariableLocalized_arr) {
                            preg_match("/[0-9]+\sattr/", $i, $cTreffer1_arr);

                            if (isset($cTreffer1_arr[0]) && strlen($cTreffer1_arr[0]) === strlen($i)) {
                                $cISO = $VariableLocalized_arr['iso'];
                                //$yx = substr($i, 0, 1);
                                $yx    = substr($i, 0, strpos($i, ' '));
                                $cName = $Variable_arr['VariableLocalized'][$yx];
                                // tpluginsprachvariablesprache füllen
                                $oPluginSprachVariableSprache                        = new stdClass();
                                $oPluginSprachVariableSprache->kPluginSprachvariable = $kPluginSprachvariable;
                                $oPluginSprachVariableSprache->cISO                  = $cISO;
                                $oPluginSprachVariableSprache->cName                 = preg_replace('/\s+/', ' ', $cName);

                                Shop::DB()->insert('tpluginsprachvariablesprache', $oPluginSprachVariableSprache);
                                // Erste PluginSprachVariableSprache vom Plugin als Standard setzen
                                if (!$bVariableStandard) {
                                    $oVariableSpracheStd = $oPluginSprachVariableSprache;
                                    $bVariableStandard   = true;
                                }

                                if (isset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)])) {
                                    // Resette aktuelle Sprache

                                    unset($oSprachAssoc_arr[strtolower($oPluginSprachVariableSprache->cISO)]);
                                    $oSprachAssoc_arr = array_merge($oSprachAssoc_arr);
                                }
                            }
                        }
                    }
                    // Sind noch Sprachen im Shop die das Plugin nicht berücksichtigt?
                    if (count($oSprachAssoc_arr) > 0) {
                        foreach ($oSprachAssoc_arr as $oSprachAssoc) {
                            $oVariableSpracheStd->cISO = strtoupper($oSprachAssoc->cISO);
                            $kPluginSprachVariableTMP  = Shop::DB()->insert('tpluginsprachvariablesprache', $oVariableSpracheStd);
                            if (!$kPluginSprachVariableTMP) {
                                return 7; // Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
                            }
                        }
                    }
                } else {
                    return 7; // Eine Sprachvariable konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
    }
    // CheckBox tcheckboxfunktion fuellen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction']) &&
        isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) &&
        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function']) > 0
    ) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['CheckBoxFunction'][0]['Function'] as $t => $Function_arr) {
            preg_match("/[0-9]+/", $t, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) === strlen($t)) {
                $oCheckBoxFunktion          = new stdClass();
                $oCheckBoxFunktion->kPlugin = $kPlugin;
                $oCheckBoxFunktion->cName   = $Function_arr['Name'];
                $oCheckBoxFunktion->cID     = $oPlugin->cPluginID . '_' . $Function_arr['ID'];
                Shop::DB()->insert('tcheckboxfunktion', $oCheckBoxFunktion);
            }
        }
    }
    // AdminWidgets tadminwidgets fuellen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget']) &&
        isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']) &&
        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget']) > 0
    ) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['AdminWidget'][0]['Widget'] as $u => $Widget_arr) {
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                $oAdminWidget               = new stdClass();
                $oAdminWidget->kPlugin      = $kPlugin;
                $oAdminWidget->cTitle       = $Widget_arr['Title'];
                $oAdminWidget->cClass       = $Widget_arr['Class'] . '_' . $oPlugin->cPluginID;
                $oAdminWidget->eContainer   = $Widget_arr['Container'];
                $oAdminWidget->cDescription = $Widget_arr['Description'];
                if (is_array($oAdminWidget->cDescription)) {
                    //@todo: when description is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                    $oAdminWidget->cDescription = $oAdminWidget->cDescription[0];
                }
                $oAdminWidget->nPos      = $Widget_arr['Pos'];
                $oAdminWidget->bExpanded = $Widget_arr['Expanded'];
                $oAdminWidget->bActive   = $Widget_arr['Active'];
                $kWidget                 = Shop::DB()->insert('tadminwidgets', $oAdminWidget);

                if (!$kWidget) {
                    return 15;// Ein AdminWidget konnte nicht in die Datenbank gespeichert werden
                }
            }
        }
    }
    // ExportFormate in texportformat fuellen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat']) &&
        isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']) &&
        is_array($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']) &&
        count($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format']) > 0
    ) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['ExportFormat'][0]['Format'] as $u => $Format_arr) {
            preg_match("/[0-9]+/", $u, $cTreffer2_arr);
            if (strlen($cTreffer2_arr[0]) === strlen($u)) {
                $oExportformat                   = new stdClass();
                $oExportformat->kKundengruppe    = $kKundengruppeStd;
                $oExportformat->kSprache         = $kSpracheStd;
                $oExportformat->kWaehrung        = $kWaehrungStd;
                $oExportformat->kKampagne        = 0;
                $oExportformat->kPlugin          = $kPlugin;
                $oExportformat->cName            = $Format_arr['Name'];
                $oExportformat->cDateiname       = $Format_arr['FileName'];
                $oExportformat->cKopfzeile       = $Format_arr['Header'];
                $oExportformat->cContent         = (isset($Format_arr['Content']) && strlen($Format_arr['Content']) > 0)
                    ? $Format_arr['Content']
                    : 'PluginContentFile_' . $Format_arr['ContentFile'];
                $oExportformat->cFusszeile       = isset($Format_arr['Footer'])
                    ? $Format_arr['Footer']
                    : null;
                $oExportformat->cKodierung       = isset($Format_arr['Encoding'])
                    ? $Format_arr['Encoding']
                    : 'ASCII';
                $oExportformat->nSpecial         = 0;
                $oExportformat->nVarKombiOption  = isset($Format_arr['VarCombiOption'])
                    ? $Format_arr['VarCombiOption']
                    : 1;
                $oExportformat->nSplitgroesse    = isset($Format_arr['SplitSize'])
                    ? $Format_arr['SplitSize']
                    : 0;
                $oExportformat->dZuletztErstellt = '0000-00-00 00:00:00';
                if (is_array($oExportformat->cKopfzeile)) {
                    //@todo: when cKopfzeile is empty, this becomes an array with indices [0] => '' and [0 attr] => ''
                    $oExportformat->cKopfzeile = $oExportformat->cKopfzeile[0];
                }
                if (is_array($oExportformat->cContent)) {
                    $oExportformat->cContent = $oExportformat->cContent[0];
                }
                if (is_array($oExportformat->cFusszeile)) {
                    $oExportformat->cFusszeile = $oExportformat->cFusszeile[0];
                }
                $kExportformat = Shop::DB()->insert('texportformat', $oExportformat);

                if (!$kExportformat) {
                    return 16;// Ein Exportformat konnte nicht in die Datenbank gespeichert werden
                } else {
                    // Einstellungen
                    // <OnlyStockGreaterZero>N</OnlyStockGreaterZero> => exportformate_lager_ueber_null
                    $oExportformatEinstellungen                = new stdClass();
                    $oExportformatEinstellungen->kExportformat = $kExportformat;
                    $oExportformatEinstellungen->cName         = 'exportformate_lager_ueber_null';
                    $oExportformatEinstellungen->cWert         = strlen($Format_arr['OnlyStockGreaterZero']) !== 0
                        ? $Format_arr['OnlyStockGreaterZero']
                        : 'N';
                    Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
                    // <OnlyPriceGreaterZero>N</OnlyPriceGreaterZero> => exportformate_preis_ueber_null
                    $oExportformatEinstellungen                = new stdClass();
                    $oExportformatEinstellungen->kExportformat = $kExportformat;
                    $oExportformatEinstellungen->cName         = 'exportformate_preis_ueber_null';
                    $oExportformatEinstellungen->cWert         = $Format_arr['OnlyPriceGreaterZero'] === 'Y'
                        ? 'Y'
                        : 'N';
                    Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
                    // <OnlyProductsWithDescription>N</OnlyProductsWithDescription> => exportformate_beschreibung
                    $oExportformatEinstellungen                = new stdClass();
                    $oExportformatEinstellungen->kExportformat = $kExportformat;
                    $oExportformatEinstellungen->cName         = 'exportformate_beschreibung';
                    $oExportformatEinstellungen->cWert         = $Format_arr['OnlyProductsWithDescription'] === 'Y'
                        ? 'Y'
                        : 'N';
                    Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
                    // <ShippingCostsDeliveryCountry>DE</ShippingCostsDeliveryCountry> => exportformate_lieferland
                    $oExportformatEinstellungen                = new stdClass();
                    $oExportformatEinstellungen->kExportformat = $kExportformat;
                    $oExportformatEinstellungen->cName         = 'exportformate_lieferland';
                    $oExportformatEinstellungen->cWert         = $Format_arr['ShippingCostsDeliveryCountry'];
                    Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
                    // <EncodingQuote>N</EncodingQuote> => exportformate_quot
                    $oExportformatEinstellungen                = new stdClass();
                    $oExportformatEinstellungen->kExportformat = $kExportformat;
                    $oExportformatEinstellungen->cName         = 'exportformate_quot';
                    $oExportformatEinstellungen->cWert         = $Format_arr['EncodingQuote'] === 'Y'
                        ? 'Y'
                        : 'N';
                    Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
                    // <EncodingDoubleQuote>N</EncodingDoubleQuote> => exportformate_equot
                    $oExportformatEinstellungen                = new stdClass();
                    $oExportformatEinstellungen->kExportformat = $kExportformat;
                    $oExportformatEinstellungen->cName         = 'exportformate_equot';
                    $oExportformatEinstellungen->cWert         = $Format_arr['EncodingDoubleQuote'] === 'Y'
                        ? 'Y'
                        : 'N';
                    Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
                    // <EncodingSemicolon>N</EncodingSemicolon> => exportformate_semikolon
                    $oExportformatEinstellungen                = new stdClass();
                    $oExportformatEinstellungen->kExportformat = $kExportformat;
                    $oExportformatEinstellungen->cName         = 'exportformate_semikolon';
                    $oExportformatEinstellungen->cWert         = $Format_arr['EncodingSemicolon'] === 'Y'
                        ? 'Y'
                        : 'N';
                    Shop::DB()->insert('texportformateinstellungen', $oExportformatEinstellungen);
                }
            }
        }
    }
    // Resourcen in tplugin_ressources fuellen
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'])) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['CSS'][0]['file'] as $file) {
            if (isset($file['name'])) {
                $oFile          = new stdClass();
                $oFile->kPlugin = $kPlugin;
                $oFile->type    = 'css';
                $oFile->path     = $file['name'];
                $oFile->priority = isset($file['priority']) ? $file['priority'] : 5;
                Shop::DB()->insert('tplugin_resources', $oFile);
                unset($oFile);
            }
        }
    }
    if (isset($XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'])) {
        foreach ($XML_arr['jtlshop3plugin'][0]['Install'][0]['JS'][0]['file'] as $file) {
            if (isset($file['name'])) {
                $oFile          = new stdClass();
                $oFile->kPlugin = $kPlugin;
                $oFile->type    = 'js';
                $oFile->path     = $file['name'];
                $oFile->priority = isset($file['priority']) ? $file['priority'] : 5;
                $oFile->position = isset($file['position']) ? $file['position'] : 'head';
                Shop::DB()->insert('tplugin_resources', $oFile);
                unset($oFile);
            }
        }
    }

    return 0;
}

/**
 * Installation von höheren XML Versionen
 *
 * @todo: use/remove
 * @param array  $XML_arr
 * @param string $cVerzeichnis
 * @param Plugin $oPluginOld
 * @param int    $nXMLVersion
 * @return int
 *
 * 40+ = Fehler Version 101
 * 50+ = Fehler Version 102
 */
function installierePluginVersion($XML_arr, $cVerzeichnis, $oPluginOld, $nXMLVersion)
{
    switch ($nXMLVersion) {
        case 101:
            // Installiere etwas
            return 1;
            break;

        case 102:
            // Installiere etwas
            return 1;
            break;

    }

    return 1;
}

/**
 * Laedt das Plugin neu, d.h. liest die XML Struktur neu ein, fuehrt neue SQLs aus.
 *
 * @param Plugin $oPlugin
 * @param bool   $forceReload
 * @return int
 * 200 = kein Reload nötig, da info file älter als dZuletztAktualisiert
 * siehe return Codes von installierePluginVorbereitung()
 */
function reloadPlugin($oPlugin, $forceReload = false)
{
    $cXMLPath = PFAD_ROOT . PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' . PLUGIN_INFO_FILE;
    if (!file_exists($cXMLPath)) {
        return -1;
    }
    $oLastUpdate    = new DateTimeImmutable($oPlugin->dZuletztAktualisiert);
    $nLastUpdate    = $oLastUpdate->getTimestamp();
    $nLastXMLChange = filemtime($cXMLPath);

    if ($nLastXMLChange > $nLastUpdate || $forceReload === true) {
        return installierePluginVorbereitung($oPlugin->cVerzeichnis, $oPlugin);
    }

    return 200; // kein Reload nötig, da info file älter als dZuletztAktualisiert
}

/**
 * Wenn ein Update erfolgreich mit neuer kPlugin in der Datenbank ist
 * wird der alte kPlugin auf die neue Version übertragen und
 * die alte Plugin-Version deinstalliert.
 *
 * @param int    $kPlugin
 * @param Plugin $oPluginOld
 * @param int    $nXMLVersion
 * @return int
 * 1 = Alles O.K.
 * 2 = Übergabeparameter nicht korrekt
 * 3 = Update konnte nicht installiert werden
 */
function syncPluginUpdate($kPlugin, $oPluginOld, $nXMLVersion)
{
    $kPlugin    = (int)$kPlugin;
    $kPluginOld = (int)$oPluginOld->kPlugin;
    // Altes Plugin deinstallieren
    $nReturnValue = deinstallierePlugin($kPluginOld, $nXMLVersion, true, $kPlugin);

    if ($nReturnValue === 1) {
        // tplugin
        $upd          = new stdClass();
        $upd->kPlugin = $kPluginOld;
        Shop::DB()->update('tplugin', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginhook', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginadminmenu', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginsprachvariable', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tadminwidgets', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginsprachvariablecustomsprache', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tplugin_resources', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tplugincustomtabelle', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tplugintemplate', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginlinkdatei', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('tpluginemailvorlage', 'kPlugin', $kPlugin, $upd);
        Shop::DB()->update('texportformat', 'kPlugin', $kPlugin, $upd);
        // tplugineinstellungen
        $oPluginEinstellung_arr = Shop::DB()->query(
            "SELECT *
                FROM tplugineinstellungen
                WHERE kPlugin IN (" . $kPluginOld . ", " . $kPlugin . ")
                ORDER BY kPlugin", 2
        );
        if (is_array($oPluginEinstellung_arr) && count($oPluginEinstellung_arr) > 0) {
            $oEinstellung_arr = [];
            foreach ($oPluginEinstellung_arr as $oPluginEinstellung) {
                $cName = str_replace(
                    ['kPlugin_' . $kPluginOld . '_', 'kPlugin_' . $kPlugin . '_'],
                    '',
                    $oPluginEinstellung->cName
                );
                if (!isset($oEinstellung_arr[$cName])) {
                    $oEinstellung_arr[$cName] = new stdClass();

                    $oEinstellung_arr[$cName]->kPlugin = $kPluginOld;
                    $oEinstellung_arr[$cName]->cName   = str_replace(
                        'kPlugin_' . $kPlugin . '_',
                        'kPlugin_' . $kPluginOld . '_',
                        $oPluginEinstellung->cName
                    );
                    $oEinstellung_arr[$cName]->cWert   = $oPluginEinstellung->cWert;
                }
            }
            Shop::DB()->query("
                DELETE FROM tplugineinstellungen 
                    WHERE kPlugin IN (" . $kPluginOld . ", " . $kPlugin . ")", 3
            );

            foreach ($oEinstellung_arr as $oEinstellung) {
                Shop::DB()->insert('tplugineinstellungen', $oEinstellung);
            }
        }
        Shop::DB()->query(
            "UPDATE tplugineinstellungen
                SET kPlugin = " . $kPluginOld . ",
                    cName = REPLACE(cName, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin, 3
        );
        // tplugineinstellungenconf
        Shop::DB()->query(
            "UPDATE tplugineinstellungenconf
                SET kPlugin = " . $kPluginOld . ",
                    cWertName = REPLACE(cWertName, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin, 3
        );
        // tboxvorlage
        $upd = new stdClass();
        $upd->kCustomID = $kPluginOld;
        Shop::DB()->update('tboxvorlage', ['kCustomID', 'eTyp'], [$kPlugin, 'plugin'], $upd);
        // tpluginzahlungsartklasse
        Shop::DB()->query(
            "UPDATE tpluginzahlungsartklasse
                SET kPlugin = " . $kPluginOld . ",
                    cModulId = REPLACE(cModulId, 'kPlugin_" . $kPlugin . "_', 'kPlugin_" . $kPluginOld . "_')
                WHERE kPlugin = " . $kPlugin, 3
        );
        // tpluginemailvorlageeinstellungen
        //@todo: this part was really messed up - check.
        $oPluginEmailvorlageAlt = Shop::DB()->select('tpluginemailvorlage', 'kPlugin', $kPluginOld);
        $oEmailvorlage          = Shop::DB()->select('tpluginemailvorlage', 'kPlugin', $kPlugin);
        if (isset($oEmailvorlage->kEmailvorlage, $oPluginEmailvorlageAlt->kEmailvorlage)) {
            $upd = new stdClass();
            $upd->kEmailvorlage = $oEmailvorlage->kEmailvorlage;
            Shop::DB()->update('tpluginemailvorlageeinstellungen', 'kEmailvorlage', $oPluginEmailvorlageAlt->kEmailvorlage, $upd);
        }
        // tpluginemailvorlagesprache
        $kEmailvorlageNeu = 0;
        $kEmailvorlageAlt = 0;
        if (isset($oPluginOld->oPluginEmailvorlageAssoc_arr) && count($oPluginOld->oPluginEmailvorlageAssoc_arr) > 0) {
            foreach ($oPluginOld->oPluginEmailvorlageAssoc_arr as $cModulId => $oPluginEmailvorlageAlt) {
                $oPluginEmailvorlageNeu = Shop::DB()->select(
                    'tpluginemailvorlage',
                    'kPlugin',
                    $kPluginOld,
                    'cModulId',
                    $cModulId,
                    null,
                    null,
                    false,
                    'kEmailvorlage'
                );
                if (isset($oPluginEmailvorlageNeu->kEmailvorlage) && $oPluginEmailvorlageNeu->kEmailvorlage > 0) {
                    if ($kEmailvorlageNeu == 0 || $kEmailvorlageAlt == 0) {
                        $kEmailvorlageNeu = $oPluginEmailvorlageNeu->kEmailvorlage;
                        $kEmailvorlageAlt = $oPluginEmailvorlageAlt->kEmailvorlage;
                    }
                    $upd = new stdClass();
                    $upd->kEmailvorlage = $oPluginEmailvorlageNeu->kEmailvorlage;
                    Shop::DB()->update(
                        'tpluginemailvorlagesprache',
                        'kEmailvorlage',
                        $oPluginEmailvorlageAlt->kEmailvorlage,
                        $upd
                    );
                }
            }
        }
        // tpluginemailvorlageeinstellungen
        $upd = new stdClass();
        $upd->kEmailvorlage = $kEmailvorlageNeu;
        Shop::DB()->update('tpluginemailvorlageeinstellungen', 'kEmailvorlage', $kEmailvorlageAlt, $upd);
        // tlink
        $upd = new stdClass();
        $upd->kPlugin = $kPluginOld;
        Shop::DB()->update('tlink', 'kPlugin', $kPlugin, $upd);
        // tboxen
        // Ausnahme: Gibt es noch eine Boxenvorlage in der Pluginversion?
        // Falls nein -> lösche tboxen mit dem entsprechenden kPlugin
        $oObj = Shop::DB()->select('tboxvorlage', 'kCustomID', $kPluginOld, 'eTyp', 'plugin');
        if (isset($oObj->kBoxvorlage) && (int)$oObj->kBoxvorlage > 0) {
            // tboxen kCustomID
            $upd = new stdClass();
            $upd->kBoxvorlage = $oObj->kBoxvorlage;
            Shop::DB()->update('tboxen', 'kCustomID', $kPluginOld, $upd);
        } else {
            Shop::DB()->delete('tboxen', 'kCustomID', $kPluginOld);
        }
        // tcheckboxfunktion
        $upd = new stdClass();
        $upd->kPlugin = $kPluginOld;
        Shop::DB()->update('tcheckboxfunktion', 'kPlugin', $kPlugin, $upd);
        // tspezialseite
        Shop::DB()->update('tspezialseite', 'kPlugin', $kPlugin, $upd);
        // tzahlungsart
        $oZahlungsartOld_arr = Shop::DB()->query("
            SELECT kZahlungsart, cModulId 
                FROM tzahlungsart 
                WHERE cModulId LIKE 'kPlugin_{$kPluginOld}_%'", 2
        );

        if (is_array($oZahlungsartOld_arr) && count($oZahlungsartOld_arr) > 0) {
            foreach ($oZahlungsartOld_arr as $oZahlungsartOld) {
                $cModulIdNew     = str_replace("kPlugin_{$kPluginOld}_", "kPlugin_{$kPlugin}_", $oZahlungsartOld->cModulId);
                $oZahlungsartNew = Shop::DB()->query("
                      SELECT kZahlungsart 
                          FROM tzahlungsart 
                          WHERE cModulId LIKE '{$cModulIdNew}'", 1
                );
                $cNewSetSQL      = '';
                if (isset($oZahlungsartOld->kZahlungsart, $oZahlungsartNew->kZahlungsart)) {
                    Shop::DB()->query(
                        "DELETE tzahlungsart, tzahlungsartsprache
                            FROM tzahlungsart
                            JOIN tzahlungsartsprache 
                                ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                            WHERE tzahlungsart.kZahlungsart = " . $oZahlungsartOld->kZahlungsart, 3
                    );

                    $cNewSetSQL = " , kZahlungsart = " . $oZahlungsartOld->kZahlungsart;
                    $upd = new stdClass();
                    $upd->kZahlungsart = $oZahlungsartOld->kZahlungsart;
                    Shop::DB()->update('tzahlungsartsprache', 'kZahlungsart', $oZahlungsartNew->kZahlungsart, $upd);
                }

                Shop::DB()->query(
                    "UPDATE tzahlungsart
                        SET cModulId = '{$oZahlungsartOld->cModulId}'
                        " . $cNewSetSQL . "
                        WHERE cModulId LIKE '{$cModulIdNew}'", 3
                );
            }
        }

        return 1;
    }
    deinstallierePlugin($kPlugin, $nXMLVersion);

    return 3;
}

/**
 * Versucht, ein ausgewähltes Plugin zu deinstallieren
 *
 * @param int  $kPlugin
 * @param int  $nXMLVersion
 * @param bool $bUpdate
 * @param null $kPluginNew
 * @return int
 * 1 = Alles O.K.
 * 2 = $kPlugin wurde nicht übergeben
 * 3 = SQL-Fehler
 */
function deinstallierePlugin($kPlugin, $nXMLVersion, $bUpdate = false, $kPluginNew = null)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin > 0) {
        $oPlugin = new Plugin($kPlugin, false, true); // suppress reload = true um Endlosrekursion zu verhindern
        if ($oPlugin->kPlugin > 0) {
            if (!$bUpdate) {
                // Plugin wird vollständig deinstalliert
                if (isset($oPlugin->oPluginUninstall->kPluginUninstall) &&
                    (int)$oPlugin->oPluginUninstall->kPluginUninstall > 0
                ) {
                    try {
                        include $oPlugin->cPluginUninstallPfad;
                    } catch (Exception $exc) {
                    }
                }
                // Custom Tables löschen
                $oCustomTabelle_arr = Shop::DB()->selectAll('tplugincustomtabelle', 'kPlugin', $kPlugin);
                if (is_array($oCustomTabelle_arr) && count($oCustomTabelle_arr) > 0) {
                    foreach ($oCustomTabelle_arr as $j => $oCustomTabelle) {
                        Shop::DB()->query("DROP TABLE IF EXISTS " . $oCustomTabelle->cTabelle, 4);
                    }
                }
                doSQLDelete($kPlugin, $bUpdate, $kPluginNew);
            } else {
                // Plugin wird nur teilweise deinstalliert, weil es danach ein Update gibt
                doSQLDelete($kPlugin, $bUpdate, $kPluginNew);
            }
            Shop::Cache()->flushAll();
            // Deinstallation für eine höhere XML Version
            if ($nXMLVersion > 100) {
                return deinstallierePluginVersion($kPlugin, $nXMLVersion, $bUpdate);
            }

            if ($p = Plugin::bootstrapper($kPlugin)) {
                $p->uninstalled();
            }

            return 1; // Alles O.K.
        }

        return 4;
    }

    return 2;// $kPlugin wurde nicht übergeben
}

/**
 * @param int      $kPlugin
 * @param bool     $bUpdate
 * @param null|int $kPluginNew
 */
function doSQLDelete($kPlugin, $bUpdate, $kPluginNew = null)
{
    $kPlugin = (int)$kPlugin;
    // Kein Update => alles deinstallieren
    if (!$bUpdate) {
        Shop::DB()->query(
            "DELETE tpluginsprachvariablesprache, tpluginsprachvariablecustomsprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache 
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache 
                    ON tpluginsprachvariablecustomsprache.cSprachvariable = tpluginsprachvariable.cName
                    AND tpluginsprachvariablecustomsprache.kPlugin = tpluginsprachvariable.kPlugin
                WHERE tpluginsprachvariable.kPlugin = " . $kPlugin, 3
        );

        Shop::DB()->delete('tplugineinstellungen', 'kPlugin', $kPlugin);
        Shop::DB()->delete('tplugincustomtabelle', 'kPlugin', $kPlugin);
        Shop::DB()->delete('tpluginlinkdatei', 'kPlugin', $kPlugin);
        Shop::DB()->query(
            "DELETE tzahlungsartsprache, tzahlungsart
                FROM tzahlungsart
                LEFT JOIN tzahlungsartsprache 
                    ON tzahlungsartsprache.kZahlungsart = tzahlungsart.kZahlungsart
                WHERE tzahlungsart.cModulId LIKE 'kPlugin_" . $kPlugin . "_%'", 3
        );

        Shop::DB()->query(
            "DELETE tboxen, tboxvorlage
                FROM tboxvorlage
                LEFT JOIN tboxen ON tboxen.kBoxvorlage = tboxvorlage.kBoxvorlage
                WHERE tboxvorlage.kCustomID = " . $kPlugin . "
                    AND tboxvorlage.eTyp = 'plugin'", 3
        );

        Shop::DB()->query(
            "DELETE tpluginemailvorlageeinstellungen, tpluginemailvorlagespracheoriginal, 
                tpluginemailvorlage, tpluginemailvorlagesprache
                FROM tpluginemailvorlage
                LEFT JOIN tpluginemailvorlagespracheoriginal 
                    ON tpluginemailvorlagespracheoriginal.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlageeinstellungen 
                    ON tpluginemailvorlageeinstellungen.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                LEFT JOIN tpluginemailvorlagesprache 
                    ON tpluginemailvorlagesprache.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                WHERE tpluginemailvorlage.kPlugin = " . $kPlugin, 3
        );
    } else { // Update => nur teilweise deinstallieren
        Shop::DB()->query(
            "DELETE tpluginsprachvariablesprache, tpluginsprachvariable
                FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablesprache 
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                WHERE tpluginsprachvariable.kPlugin = " . $kPlugin, 3
        );

        Shop::DB()->delete('tboxvorlage', ['kCustomID', 'eTyp'], [$kPlugin, 'plugin']);
        Shop::DB()->delete('tpluginlinkdatei', 'kPlugin', $kPlugin);
        Shop::DB()->query(
            "DELETE tpluginemailvorlage, tpluginemailvorlagespracheoriginal
                FROM tpluginemailvorlage
                LEFT JOIN tpluginemailvorlagespracheoriginal 
                    ON tpluginemailvorlagespracheoriginal.kEmailvorlage = tpluginemailvorlage.kEmailvorlage
                WHERE tpluginemailvorlage.kPlugin = " . $kPlugin, 3
        );
    }
    Shop::DB()->query(
        "DELETE tpluginsqlfehler, tpluginhook
            FROM tpluginhook
            LEFT JOIN tpluginsqlfehler 
                ON tpluginsqlfehler.kPluginHook = tpluginhook.kPluginHook
            WHERE tpluginhook.kPlugin = " . $kPlugin, 3
    );
    Shop::DB()->delete('tpluginadminmenu', 'kPlugin', $kPlugin);
    Shop::DB()->query(
        "DELETE tplugineinstellungenconfwerte, tplugineinstellungenconf
            FROM tplugineinstellungenconf
            LEFT JOIN tplugineinstellungenconfwerte 
                ON tplugineinstellungenconfwerte.kPluginEinstellungenConf = tplugineinstellungenconf.kPluginEinstellungenConf
            WHERE tplugineinstellungenconf.kPlugin = " . $kPlugin, 3
    );

    Shop::DB()->delete('tpluginuninstall', 'kPlugin', $kPlugin);
    //delete ressource entries
    Shop::DB()->delete('tplugin_resources', 'kPlugin', $kPlugin);
    // tlinksprache && tseo
    $oObj_arr = [];
    if ($kPluginNew !== null && $kPluginNew > 0) {
        $kPluginNew = (int)$kPluginNew;
        $oObj_arr   = Shop::DB()->query(
            "SELECT kLink
                FROM tlink
                WHERE kPlugin IN ({$kPlugin}, {$kPluginNew})
                    ORDER BY kLink", 2
        );
    }
    if (is_array($oObj_arr) && count($oObj_arr) === 2) {
        $oLinkspracheOld_arr = Shop::DB()->selectAll('tlinksprache', 'kLink', $oObj_arr[0]->kLink);
        if (is_array($oLinkspracheOld_arr) && count($oLinkspracheOld_arr) > 0) {
            $oSprachAssoc_arr = gibAlleSprachen(2);

            foreach ($oLinkspracheOld_arr as $oLinkspracheOld) {
                $_upd       = new stdClass();
                $_upd->cSeo = $oLinkspracheOld->cSeo;
                Shop::DB()->update(
                    'tlinksprache',
                    ['kLink', 'cISOSprache'],
                    [$oObj_arr[1]->kLink, $oLinkspracheOld->cISOSprache],
                    $_upd
                );
                $kSprache = $oSprachAssoc_arr[$oLinkspracheOld->cISOSprache]->kSprache;
                Shop::DB()->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $oObj_arr[0]->kLink, $kSprache]
                );
                $_upd       = new stdClass();
                $_upd->cSeo = $oLinkspracheOld->cSeo;
                Shop::DB()->update(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kLink', $oObj_arr[1]->kLink, $kSprache],
                    $_upd
                );
            }
        }
    }
    Shop::DB()->query(
        "DELETE tlinksprache, tseo, tlink
            FROM tlink
            LEFT JOIN tlinksprache 
                ON tlinksprache.kLink = tlink.kLink
            LEFT JOIN tseo 
                ON tseo.cKey = 'kLink' 
                AND tseo.kKey = tlink.kLink
            WHERE tlink.kPlugin = " . $kPlugin, 3
    );
    Shop::DB()->delete('tpluginzahlungsartklasse', 'kPlugin', $kPlugin);
    Shop::DB()->delete('tplugintemplate', 'kPlugin', $kPlugin);
    Shop::DB()->delete('tcheckboxfunktion', 'kPlugin', $kPlugin);
    Shop::DB()->delete('tadminwidgets', 'kPlugin', $kPlugin);
    Shop::DB()->query(
        "DELETE texportformateinstellungen, texportformatqueuebearbeitet, texportformat
            FROM texportformat
            LEFT JOIN texportformateinstellungen 
                ON texportformateinstellungen.kExportformat = texportformat.kExportformat
            LEFT JOIN texportformatqueuebearbeitet 
                ON texportformatqueuebearbeitet.kExportformat = texportformat.kExportformat
            WHERE texportformat.kPlugin = " . $kPlugin, 3
    );
    Shop::DB()->delete('tplugin', 'kPlugin', $kPlugin);
}

/**
 * Deinstallation für eine höhere XML Version
 *
 * @param int $kPlugin
 * @param int $nXMLVersion
 * @param bool $bUpdate
 * @return int
 * 10+ = XML Version 101
 * 20+ = XML Version 102
 */
function deinstallierePluginVersion($kPlugin, $nXMLVersion, $bUpdate)
{
    switch ($nXMLVersion) {
        case 101:
            // Deinstalliere etwas
            return 1;
            break;

        case 102:
            // Deinstalliere etwas
            return 1;
            break;
        default:
            return 0;
    }
}

/**
 * Versucht ein ausgewähltes Plugin zu aktivieren
 *
 * @param int $kPlugin
 * @return int
 */
function aktivierePlugin($kPlugin)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin > 0) {
        $oPlugin = Shop::DB()->select('tplugin', 'kPlugin', $kPlugin);
        if (isset($oPlugin->kPlugin) && $oPlugin->kPlugin > 0) {
            $cPfad        = PFAD_ROOT . PFAD_PLUGIN;
            $nReturnValue = pluginPlausi(0, $cPfad . $oPlugin->cVerzeichnis);

            if ($nReturnValue === 1 || $nReturnValue === 90 || $nReturnValue === 126) {
                $_upd_plg          = new stdClass();
                $_upd_plg->nStatus = 2;
                $nRow              = Shop::DB()->update('tplugin', 'kPlugin', $kPlugin, $_upd_plg);

                $_upd_wdg          = new stdClass();
                $_upd_wdg->bActive = 1;
                Shop::DB()->update('tadminwidgets', 'kPlugin', $kPlugin, $_upd_wdg);

                $_upd_lnk            = new stdClass();
                $_upd_lnk->bIsActive = 1;
                Shop::DB()->update('tlink', 'kPlugin', $kPlugin, $_upd_lnk);

                if ($p = Plugin::bootstrapper($kPlugin)) {
                    $p->enabled();
                }

                if ($nRow > 0) {
                    return 1; // Alles O.K.
                }

                return 3; // Plugin wurde nicht in der Datenbank gefunden
            }

            return $nReturnValue; // Plugin konnte aufgrund eines Fehlers nicht aktiviert werden.
        }

        return 3;
    }

    return 2; // $kPlugin wurde nicht übergeben
}

/**
 * Versucht ein ausgewähltes Plugin zu deaktivieren
 *
 * @param int $kPlugin
 * @return int
 */
function deaktivierePlugin($kPlugin)
{
    $kPlugin = (int)$kPlugin;
    if ($kPlugin > 0) {
        if ($p = Plugin::bootstrapper($kPlugin)) {
            $p->disabled();
        }

        $_upd_plg          = new stdClass();
        $_upd_plg->nStatus = 1;
        Shop::DB()->update('tplugin', 'kPlugin', $kPlugin, $_upd_plg);

        $_upd_wdg          = new stdClass();
        $_upd_wdg->bActive = 0;
        Shop::DB()->update('tadminwidgets', 'kPlugin', $kPlugin, $_upd_wdg);

        $_upd_lnk            = new stdClass();
        $_upd_lnk->bIsActive = 0;
        Shop::DB()->update('tlink', 'kPlugin', $kPlugin, $_upd_lnk);

        Shop::Cache()->flushTags([CACHING_GROUP_PLUGIN . '_' . $kPlugin]);

        return 1;
    }

    return 2; // $kPlugin wurde nicht übergeben
}

/**
 * Baut aus einer XML ein Objekt
 *
 * @param array $XML
 * @return stdClass
 */
function makeXMLToObj($XML)
{
    $oObj = new stdClass();
    if (isset($XML['jtlshop3plugin']) && is_array($XML['jtlshop3plugin'])) {
        if (!isset($XML['jtlshop3plugin'][0]['Install'][0]['Version'])) {
            return $oObj;
        }
        if (!isset($XML['jtlshop3plugin'][0]['Name'])) {
            return $oObj;
        }
        if (!isset($XML['jtlshop3plugin'][0]['Description'])) {
            return $oObj;
        }
        if (!isset($XML['jtlshop3plugin'][0]['Author'])) {
            return $oObj;
        }
        $nLastVersionKey = count($XML['jtlshop3plugin'][0]['Install'][0]['Version']) / 2 - 1;

        $oObj->cName           = $XML['jtlshop3plugin'][0]['Name'];
        $oObj->cDescription    = $XML['jtlshop3plugin'][0]['Description'];
        $oObj->cAuthor         = $XML['jtlshop3plugin'][0]['Author'];
        $oObj->cPluginID       = $XML['jtlshop3plugin'][0]['PluginID'];
        $oObj->cIcon           = isset($XML['jtlshop3plugin'][0]['Icon'])
            ? $XML['jtlshop3plugin'][0]['Icon'] 
            : null;
        $oObj->cVerzeichnis    = $XML['cVerzeichnis'];
        $oObj->shop4compatible = (!empty($XML['shop4compatible'])) 
            ? $XML['shop4compatible'] 
            : false;
        $oObj->nVersion        = (int)$XML['jtlshop3plugin'][0]['Install'][0]['Version'][$nLastVersionKey . ' attr']['nr'];
        $oObj->cVersion        = number_format($oObj->nVersion / 100, 2);
    }

    if (empty($oObj->cName) && empty($oObj->cDescription) && !empty($XML['cVerzeichnis'])) {
        $oObj->cName        = $XML['cVerzeichnis'];
        $oObj->cDescription = '';
        $oObj->cVerzeichnis = $XML['cVerzeichnis'];
    }
    if (isset($XML['cFehlercode']) && strlen($XML['cFehlercode']) > 0) {
        $oObj->cFehlercode         = $XML['cFehlercode'];
        $oObj->cFehlerBeschreibung = mappePlausiFehler($XML['cFehlercode'], $oObj);
    }

    return $oObj;
}

/**
 * Führt das SQL einer bestimmten Version pro Plugin aus
 * Füllt tplugincustomtabelle falls Tabellen angelegt werden im SQL
 *
 * @param string        $cSQLDatei
 * @param int           $nVersion
 * @param Plugin|object $oPlugin
 * @return int

 * 1 = Alles O.K.
 * 2 = Plugindaten fehlen
 * 3 = SQL hat einen Fehler verursacht
 * 4 = Versuch eine nicht Plugintabelle zu löschen
 * 5 = Versuch eine nicht Plugintabelle anzulegen
 * 6 = SQL Datei ist leer oder konnte nicht geparsed werden
 */
function logikSQLDatei($cSQLDatei, $nVersion, $oPlugin)
{
    if (strlen($cSQLDatei) > 0 && (int)$nVersion >= 100 && (int)$oPlugin->kPlugin > 0 && strlen($oPlugin->cPluginID) > 0) {
        $cSQL_arr = parseSQLDatei($cSQLDatei, $oPlugin->cVerzeichnis, $nVersion);

        if (is_array($cSQL_arr) && count($cSQL_arr) > 0) {
            foreach ($cSQL_arr as $cSQL) {
                $cSQL = removeNumerousWhitespaces($cSQL);
                // SQL legt eine neue Tabelle an => fülle tplugincustomtabelle
                if (strpos(strtolower($cSQL), 'create table') !== false) {
                    //when using "create table if not exists" statement, the table name is at index 5, otherwise at 2
                    $tableNameAtIndex = (strpos(strtolower($cSQL), 'create table if not exists') !== false) ? 5 : 2;
                    $cSQLTMP_arr      = explode(' ', $cSQL);
                    $cTabelle         = str_replace(["'", "`"], '', $cSQLTMP_arr[$tableNameAtIndex]);
                    preg_match("/xplugin[_]{1}" . $oPlugin->cPluginID . "[_]{1}[a-zA-Z0-9_]+/", $cTabelle, $cTreffer_arr);
                    if (!isset($cTreffer_arr[0]) || strlen($cTreffer_arr[0]) !== strlen($cTabelle)) {
                        return 5;// Versuch eine nicht Plugintabelle anzulegen
                    }
                    // Prüfen, ob nicht bereits vorhanden => Wenn nein, anlegen
                    $oPluginCustomTabelleTMP = Shop::DB()->select('tplugincustomtabelle', 'cTabelle', $cTabelle);
                    if (!isset($oPluginCustomTabelleTMP->kPluginCustomTabelle) || !$oPluginCustomTabelleTMP->kPluginCustomTabelle) {
                        $oPluginCustomTabelle           = new stdClass();
                        $oPluginCustomTabelle->kPlugin  = $oPlugin->kPlugin;
                        $oPluginCustomTabelle->cTabelle = $cTabelle;

                        Shop::DB()->insert('tplugincustomtabelle', $oPluginCustomTabelle);
                    }
                } elseif (strpos(strtolower($cSQL), 'drop table') !== false) {
                    // SQL versucht eine Tabelle zu löschen => prüfen ob es sich um eine Plugintabelle handelt
                    //when using "drop table if exists" statement, the table name is at index 5, otherwise at 2
                    $tableNameAtIndex = (strpos(strtolower($cSQL), 'drop table if exists') !== false) ? 4 : 2;
                    $cSQLTMP_arr      = explode(' ', removeNumerousWhitespaces($cSQL));
                    $cTabelle         = str_replace(["'", "`"], '', $cSQLTMP_arr[$tableNameAtIndex]);
                    preg_match("/xplugin[_]{1}" . $oPlugin->cPluginID . "[_]{1}[a-zA-Z0-9]+/", $cTabelle, $cTreffer_arr);
                    if (strlen($cTreffer_arr[0]) !== strlen($cTabelle)) {
                        return 4;// Versuch eine nicht Plugintabelle zu löschen
                    }
                }

                Shop::DB()->query($cSQL, 4);
                $nErrno = Shop::DB()->getErrorCode();
                // Es gab ein SQL Fehler => fülle tpluginsqlfehler
                if ($nErrno) {
                    Jtllog::writeLog(
                        'SQL Fehler beim Installieren des Plugins (' . $oPlugin->cName . '): ' .
                        str_replace("'", '', Shop::DB()->getErrorMessage()), 
                        JTLLOG_LEVEL_ERROR, 
                        false, 
                        'kPlugin', 
                        $oPlugin->kPlugin
                    );

                    return 3;// SQL hat einen Fehler verursacht
                }
            }

            return 1;// Alles O.K.
        }

        return 6;// SQL Datei ist leer oder konnte nicht geparsed werden
    }

    return 2;// Plugindaten fehlen
}

/**
 * Mehrfach Leerzeichen entfernen
 *
 * @param string $cStr
 * @return mixed
 */
function removeNumerousWhitespaces($cStr)
{
    if (strlen($cStr) > 0) {
        while (strpos($cStr, '  ')) {
            $cStr = str_replace('  ', ' ', $cStr);
        }
    }

    return $cStr;
}

/**
 * Geht die angegebene SQL durch und formatiert diese. Immer 1 SQL pro Zeile.
 *
 * @param string $cSQLDatei
 * @param string $cVerzeichnis
 * @param int    $nVersion
 * @return array
 */
function parseSQLDatei($cSQLDatei, $cVerzeichnis, $nVersion)
{
    $cSQLDateiPfad = PFAD_ROOT . PFAD_PLUGIN . $cVerzeichnis . '/' . 
        PFAD_PLUGIN_VERSION . $nVersion . '/' . 
        PFAD_PLUGIN_SQL;

    if (file_exists($cSQLDateiPfad . $cSQLDatei)) {
        $file_handle = fopen($cSQLDateiPfad . $cSQLDatei, 'r');
        $cSQL_arr    = [];
        $cLine       = '';
        $i           = 0;
        while (($cData = fgets($file_handle)) !== false) {
            $cData = trim($cData);
            if ($cData !== '' && strpos($cData, '--') !== 0) {
                if (strpos($cData, 'CREATE TABLE') !== false) {
                    $cLine .= trim($cData);
                } elseif (strpos($cData, 'INSERT') !== false) {
                    $cLine .= trim($cData);
                } else {
                    $cLine .= trim($cData);
                }

                if (substr($cData, strlen($cData) - 1, 1) === ';') {
                    $cSQL_arr[] = $cLine;
                    $cLine      = '';
                }
            }

            $i++;
        }
        fclose($file_handle);

        return $cSQL_arr;
    }

    return [];// SQL Datei existiert nicht
}

/**
 * Gibt die nächst höheren SQL Versionen als Array
 *
 * @param string $cPluginVerzeichnis
 * @param int    $nVersion
 * @return array|bool
 */
function gibHoehereSQLVersionen($cPluginVerzeichnis, $nVersion)
{
    $cSQLVerzeichnis = PFAD_ROOT . PFAD_PLUGIN . $cPluginVerzeichnis . '/' . PFAD_PLUGIN_VERSION;
    if (is_dir($cSQLVerzeichnis)) {
        $nVerzeichnis_arr = [];
        $Dir              = opendir($cSQLVerzeichnis);
        while (($cVerzeichnis = readdir($Dir)) !== false) {
            if ($cVerzeichnis !== '.' && $cVerzeichnis !== '..' && is_dir($cSQLVerzeichnis . $cVerzeichnis)) {
                $nVerzeichnis_arr[] = (int)$cVerzeichnis;
            }
        }
        closedir($Dir);
        if (count($nVerzeichnis_arr) > 0) {
            usort($nVerzeichnis_arr, 'pluginverwaltungcmp');
            foreach ($nVerzeichnis_arr as $i => $nVerzeichnis) {
                if ($nVersion > $nVerzeichnis) {
                    unset($nVerzeichnis_arr[$i]);
                }
            }

            return array_merge($nVerzeichnis_arr);
        }
    }

    return false;
}

/**
 * Hilfsfunktion für usort
 *
 * @param int $a
 * @param int $b
 * @return int
 */
function pluginverwaltungcmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;
}

/**
 * Holt alle PluginSprachvariablen (falls vorhanden)
 *
 * @param int $kPlugin
 * @return array
 */
function gibSprachVariablen($kPlugin)
{
    $return                 = [];
    $kPlugin                = (int)$kPlugin;
    $oPluginSprachvariablen = Shop::DB()->query(
        "SELECT
            tpluginsprachvariable.kPluginSprachvariable,
            tpluginsprachvariable.kPlugin,
            tpluginsprachvariable.cName,
            tpluginsprachvariable.cBeschreibung,
            COALESCE(tpluginsprachvariablecustomsprache.cISO, tpluginsprachvariablesprache.cISO)  AS cISO,
            COALESCE(tpluginsprachvariablecustomsprache.cName, tpluginsprachvariablesprache.cName) AS customValue
            FROM tpluginsprachvariable
                LEFT JOIN tpluginsprachvariablecustomsprache
                    ON tpluginsprachvariablecustomsprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                LEFT JOIN tpluginsprachvariablesprache
                    ON tpluginsprachvariablesprache.kPluginSprachvariable = tpluginsprachvariable.kPluginSprachvariable
                    AND tpluginsprachvariablesprache.cISO = COALESCE(tpluginsprachvariablecustomsprache.cISO, tpluginsprachvariablesprache.cISO)
            WHERE tpluginsprachvariable.kPlugin = " . $kPlugin . "
            ORDER BY tpluginsprachvariable.kPluginSprachvariable", 9
    );
    if (is_array($oPluginSprachvariablen) && count($oPluginSprachvariablen) > 0) {
        $new = [];
        foreach ($oPluginSprachvariablen as $_sv) {
            if (!isset($new[$_sv['kPluginSprachvariable']])) {
                $var                                   = new stdClass();
                $var->kPluginSprachvariable            = $_sv['kPluginSprachvariable'];
                $var->kPlugin                          = $_sv['kPlugin'];
                $var->cName                            = $_sv['cName'];
                $var->cBeschreibung                    = $_sv['cBeschreibung'];
                $var->oPluginSprachvariableSprache_arr = [
                    $_sv['cISO'] => $_sv['customValue']
                ];
                $new[$_sv['kPluginSprachvariable']] = $var;
            } else {
                $new[$_sv['kPluginSprachvariable']]->oPluginSprachvariableSprache_arr[$_sv['cISO']] = $_sv['customValue'];
            }
        }
        $return = array_values($new);
    }

    return $return;
}

/**
 * Holt alle PluginSprachvariablen (falls vorhanden)
 *
 * @param int $kPlugin
 * @return array
 */
function gibSprachVariablenALT($kPlugin)
{
    $lvArr   = [];
    $kPlugin = (int)$kPlugin;
    if ($kPlugin > 0) {
        // Hole PluginSprachvariablen
        $lvArr = Shop::DB()->selectAll('tpluginsprachvariable', 'kPlugin', $kPlugin);
        if (is_array($lvArr) && count($lvArr) > 0) {
            foreach ($lvArr as $i => $oPluginSprachvariable) {
                // Hole Custom Variablen
                $oPluginSprachvariableCustomSprache_arr = Shop::DB()->selectAll(
                    'tpluginsprachvariablecustomsprache',
                    'cSprachvariable', 
                    $oPluginSprachvariable->cName, 
                    'kPlugin, cSprachvariable, cISO, cName AS cNameSprache'
                );
                if (count($oPluginSprachvariableCustomSprache_arr) > 0) {
                    foreach ($oPluginSprachvariableCustomSprache_arr as $oPluginSprachvariableCustomSprache) {
                        $lvArr[$i]->oPluginSprachvariableSprache_arr[$oPluginSprachvariableCustomSprache->cISO] = 
                            $oPluginSprachvariableCustomSprache->cNameSprache;
                    }
                } else {
                    $oPluginSprachvariableSprache_arr = Shop::DB()->selectAll(
                        'tpluginsprachvariablesprache', 
                        'kPluginSprachvariable', 
                        (int)$oPluginSprachvariable->kPluginSprachvariable, 
                        'cISO, cName AS cNameSprache'
                    );
                    if (count($oPluginSprachvariableSprache_arr) > 0) {
                        foreach ($oPluginSprachvariableSprache_arr as $oPluginSprachvariableSprache) {
                            $lvArr[$i]->oPluginSprachvariableSprache_arr[$oPluginSprachvariableSprache->cISO] = 
                                $oPluginSprachvariableSprache->cNameSprache;
                        }
                    }
                }
            }
        }
    }

    return $lvArr;
}

/*
// 2    = Falsche Übergabeparameter
// 3    = Verzeichnis existiert nicht
// 4    = info.xml existiert nicht
// 5    = Kein Plugin in der DB anhand von kPlugin gefunden
// 6    = Der Pluginname entspricht nicht der Konvention
// 7    = Die PluginID entspricht nicht der Konvention
// 8    = Der Installationsknoten ist nicht vorhanden
// 9    = Erste Versionsnummer entspricht nicht der Konvention
// 10   = Die Versionsnummer entspricht nicht der Konvention
// 11   = Das Versionsdatum entspricht nicht der Konvention
// 12   = SQL Datei für die aktuelle Version existiert nicht
// 13   = Keine Hooks vorhanden
// 14   = Die Hook Werte entsprechen nicht den Konventionen
// 15   = CustomLink Name entspricht nicht der Konvention
// 16   = CustomLink Dateiname entspricht nicht der Konvention
// 17   = CustomLink Datei existiert nicht
// 18   = EinstellungsLink Name entspricht nicht der Konvention
// 19   = Einstellungen fehlen
// 20   = Einstellungen type entspricht nicht der Konvention
// 21   = Einstellungen initialValue entspricht nicht der Konvention
// 22   = Einstellungen sort entspricht nicht der Konvention
// 23   = Einstellungen Name entspricht nicht der Konvention
// 24   = Keine SelectboxOptionen vorhanden
// 25   = Die Option entspricht nicht der Konvention
// 26   = Keine Sprachvariablen vorhanden
// 27   = Variable Name entspricht nicht der Konvention
// 28   = Keine lokalisierte Sprachvariable vorhanden
// 29   = Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention
// 30   = Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention
// 31   = Die Hook Datei ist nicht vorhanden
// 32   = Version existiert nicht im Versionsordner
// 33   = Einstellungen conf entspricht nicht der Konvention
// 34   = Einstellungen ValueName entspricht nicht der Konvention
// 35   = XML Version entspricht nicht der Konvention
// 36   = Shop Version entspricht nicht der Konvention
// 37   = Shop Version ist zu niedrig
// 38   = Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde
// 39   = Link Filename entspricht nicht der Konvention
// 40   = LinkName entspricht nicht der Konvention
// 41   = Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention
// 42   = Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention
// 43   = Die ISO der Linksprache entspricht nicht der Konvention
// 44   = Der Seo Name entspricht nicht der Konvention
// 45   = Der Name entspricht nicht der Konvention
// 46   = Der Title entspricht nicht der Konvention
// 47   = Der MetaTitle entspricht nicht der Konvention
// 48   = Die MetaKeywords entsprechen nicht der Konvention
// 49   = Die MetaDescription entspricht nicht der Konvention
// 50   = Der Name in den Zahlungsmethoden entspricht nicht der Konvention
// 51   = Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention
// 52   = TSCode in den Zahlungsmethoden entspricht nicht der Konvention
// 53   = PreOrder in den Zahlungsmethoden entspricht nicht der Konvention
// 54   = ClassFile in den Zahlungsmethoden entspricht nicht der Konvention
// 55   = Die Datei für die Klasse der Zahlungsmethode existiert nicht
// 56   = TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
// 57   = Die Datei für das Template der Zahlungsmethode existiert nicht
// 58   = Keine Sprachen in den Zahlungsmethoden hinterlegt
// 59   = Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention
// 60   = Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 61   = Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 62   = Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention
// 63   = Zahlungsmethode Einstellungen type entspricht nicht der Konvention
// 64   = Zahlungsmethode Einstellungen initialValue entspricht nicht der Konvention
// 65   = Zahlungsmethode Einstellungen sort entspricht nicht der Konvention
// 66   = Zahlungsmethode Einstellungen conf entspricht nicht der Konvention
// 67   = Zahlungsmethode Einstellungen Name entspricht nicht der Konvention
// 68   = Zahlungsmethode Einstellungen ValueName entspricht nicht der Konvention
// 69   = Keine SelectboxOptionen vorhanden
// 70   = Die Option entspricht nicht der Konvention
// 71   = Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention
// 72   = Soap in den Zahlungsmethoden entspricht nicht der Konvention
// 73   = Curl in den Zahlungsmethoden entspricht nicht der Konvention
// 74 	= Sockets in den Zahlungsmethoden entspricht nicht der Konvention
// 75	= ClassName in den Zahlungsmethoden entspricht nicht der Konvention
// 76	= Der Templatename entspricht nicht der Konvention
// 77	= Die Templatedatei für den Frontend Link existiert nicht
// 78	= Es darf nur ein Templatename oder ein Fullscreen Templatename existieren
// 79	= Der Fullscreen Templatename entspricht nicht der Konvention
// 80	= Die Fullscreen Templatedatei für den Frontend Link existiert nicht
// 81	= Für ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden
// 82 	= Keine Box vorhanden
// 83 	= Box Name entspricht nicht der Konvention
// 84 	= Box Templatedatei entspricht nicht der Konvention
// 85 	= Box Templatedatei existiert nicht
// 86	= Lizenzklasse existiert nicht
// 87	= Name der Lizenzklasse entspricht nicht der konvention
// 88	= Lizenklasse ist nicht definiert
// 89	= Methode checkLicence in der Lizenzklasse ist nicht definiert
// 90	= PluginID bereits in der Datenbank vorhanden
// 91	= Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde
// 92	= Template Name entspricht nicht der Konvention
// 93	= Template Type entspricht nicht der Konvention
// 94 	= Template ModulId entspricht nicht der Konvention
// 95 	= Template Active entspricht nicht der Konvention
// 96 	= Template AKZ entspricht nicht der Konvention
// 97 	= Template AGB entspricht nicht der Konvention
// 98	= Template WRB entspricht nicht der Konvention
// 99	= Die ISO der Emailtemplate Sprache entspricht nicht der Konvention
// 100	= Der Subject Name entspricht nicht der Konvention
// 101	= Keine Templatesprachen vorhanden
// 102  = CheckBoxFunction Name entspricht nicht der Konvention
// 103  = CheckBoxFunction ID entspricht nicht der Konvention
// 104  = Frontend Link Attribut NoFollow entspricht nicht der Konvention
// 105  = Keine Widgets vorhanden
// 106  = Widget Title entspricht nicht der Konvention
// 107  = Widget Class entspricht nicht der Konvention
// 108  = Die Datei für die Klasse des AdminWidgets existiert nicht
// 109  = Container im Widget entspricht nicht der Konvention
// 110  = Pos im Widget entspricht nicht der Konvention
// 111  = Expanded im Widget entspricht nicht der Konvention
// 112  = Active im Widget entspricht nicht der Konvention
// 113  = AdditionalTemplateFile in den Zahlungsmethoden entspricht nicht der Konvention
// 114  = Die Datei für das Zusatzschritt-Template der Zahlungsmethode existiert nicht
// 115  = Keine Formate vorhanden
// 116  = Format Name entspricht nicht der Konvention
// 117  = Format Filename entspricht nicht der Konvention
// 118  = Format enthaelt weder Content, noch eine Contentdatei
// 119  = Format Encoding entspricht nicht der Konvention
// 120  = Format ShippingCostsDeliveryCountry entspricht nicht der Konvention
// 121  = Format ContenFile entspricht nicht der Konvention
// 122 	= Kein Template vorhanden
// 123 	= Templatedatei entspricht nicht der Konvention
// 124 	= Templatedatei existiert nicht
// 125  = Uninstall File existiert nicht
// 127  = Benoetigt ionCube, Extension wurde aber nicht geladen
// 128  = OptionsSource-Datei wurde nicht angegeben
// 129  = OptionsSource-Datei existiert nicht
*/

/**
 * @param int    $nFehlerCode
 * @param object $oPlugin
 * @return string
 */
function mappePlausiFehler($nFehlerCode, $oPlugin)
{
    $return = '';
    if ($nFehlerCode > 0) {
        switch ($nFehlerCode) {
            case 2:
                $return = 'Fehler: Die Plausibilität ist aufgrund fehlender Parameter abgebrochen.';
                break;
            case 3:
                $return = 'Fehler: Das Pluginverzeichnis existiert nicht.';
                break;
            case 4:
                $return = 'Fehler: Die Informations XML Datei existiert nicht.';
                break;
            case 5:
                $return = 'Fehler: Das ausgewählte Plugin wurde nicht in der Datenbank gefunden.';
                break;
            case 6:
                $return = 'Fehler: Der Pluginname entspricht nicht der Konvention.';
                break;
            case 7:
                $return = 'Fehler: Die PluginID entspricht nicht der Konvention.';
                break;
            case 8:
                $return = 'Fehler: Der Installationsknoten ist nicht vorhanden.';
                break;
            case 9:
                $return = 'Fehler: Erste Versionsnummer entspricht nicht der Konvention.';
                break;
            case 10:
                $return = 'Fehler: Die Versionsnummer entspricht nicht der Konvention.';
                break;
            case 11:
                $return = 'Fehler: Das Versionsdatum entspricht nicht der Konvention.';
                break;
            case 12:
                $return = 'Fehler: SQL-Datei für die aktuelle Version existiert nicht.';
                break;
            case 13:
                $return = 'Fehler: Keine Hooks vorhanden.';
                break;
            case 14:
                $return = 'Fehler: Die Hook-Werte entsprechen nicht den Konventionen.';
                break;
            case 15:
                $return = 'Fehler: CustomLink Name entspricht nicht der Konvention.';
                break;
            case 16:
                $return = 'Fehler: Dateiname entspricht nicht der Konvention.';
                break;
            case 17:
                $return = 'Fehler: CustomLink-Datei existiert nicht.';
                break;
            case 18:
                $return = 'Fehler: EinstellungsLink Name entspricht nicht der Konvention.';
                break;
            case 19:
                $return = 'Fehler: Einstellungen fehlen.';
                break;
            case 20:
                $return = 'Fehler: Einstellungen type entspricht nicht der Konvention.';
                break;
            case 21:
                $return = 'Fehler: Einstellungen initialValue entspricht nicht der Konvention.';
                break;
            case 22:
                $return = 'Fehler: Einstellungen sort entspricht nicht der Konvention.';
                break;
            case 23:
                $return = 'Fehler: Einstellungen Name entspricht nicht der Konvention.';
                break;
            case 24:
                $return = 'Fehler: Keine SelectboxOptionen vorhanden.';
                break;
            case 25:
                $return = 'Fehler: Die Option entspricht nicht der Konvention.';
                break;
            case 26:
                $return = 'Fehler: Keine Sprachvariablen vorhanden.';
                break;
            case 27:
                $return = 'Fehler: Variable Name entspricht nicht der Konvention.';
                break;
            case 28:
                $return = 'Fehler: Keine lokalisierte Sprachvariable vorhanden.';
                break;
            case 29:
                $return = 'Fehler: Die ISO der lokalisierten Sprachvariable entspricht nicht der Konvention.';
                break;
            case 30:
                $return = 'Fehler: Der Name der lokalisierten Sprachvariable entspricht nicht der Konvention.';
                break;
            case 31:
                $return = 'Fehler: Die Hook-Datei ist nicht vorhanden.';
                break;
            case 32:
                $return = 'Fehler: Version existiert nicht im Versionsordner.';
                break;
            case 33:
                $return = 'Fehler: Einstellungen conf entspricht nicht der Konvention.';
                break;
            case 34:
                $return = 'Fehler: Einstellungen ValueName entspricht nicht der Konvention.';
                break;
            case 35:
                $return = 'Fehler: XML-Version entspricht nicht der Konvention.';
                break;
            case 36:
                $return = 'Fehler: Shopversion entspricht nicht der Konvention.';
                break;
            case 37:
                $return = 'Fehler: Shopversion ist zu niedrig.';
                break;
            case 38:
                $return = 'Fehler: Keine Frontendlinks vorhanden, obwohl der Node angelegt wurde.';
                break;
            case 39:
                $return = 'Fehler: Link Filename entspricht nicht der Konvention.';
                break;
            case 40:
                $return = 'Fehler: LinkName entspricht nicht der Konvention.';
                break;
            case 41:
                $return = 'Fehler: Angabe ob erst Sichtbar nach Login entspricht nicht der Konvention.';
                break;
            case 42:
                $return = 'Fehler: Abgabe ob eine Druckbutton gezeigt werden soll entspricht nicht der Konvention.';
                break;
            case 43:
                $return = 'Fehler: Die ISO der Linksprache entspricht nicht der Konvention.';
                break;
            case 44:
                $return = 'Fehler: Der Seo Name entspricht nicht der Konvention.';
                break;
            case 45:
                $return = 'Fehler: Der Name entspricht nicht der Konvention.';
                break;
            case 46:
                $return = 'Fehler: Der Title entspricht nicht der Konvention.';
                break;
            case 47:
                $return = 'Fehler: Der MetaTitle entspricht nicht der Konvention.';
                break;
            case 48:
                $return = 'Fehler: Die MetaKeywords entsprechen nicht der Konvention.';
                break;
            case 49:
                $return = 'Fehler: Die MetaDescription entspricht nicht der Konvention.';
                break;
            case 50:
                $return = 'Fehler: Der Name in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 51:
                $return = 'Fehler: Sende Mail in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 52:
                $return = 'Fehler: TSCode in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 53:
                $return = 'Fehler: PreOrder in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 54:
                $return = 'Fehler: ClassFile in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 55:
                $return = 'Fehler: Die Datei für die Klasse der Zahlungsmethode existiert nicht.';
                break;
            case 56:
                $return = 'Fehler: TemplateFile in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 57:
                $return = 'Fehler: Die Datei für das Template der Zahlungsmethode existiert nicht.';
                break;
            case 58:
                $return = 'Fehler: Keine Sprachen in den Zahlungsmethoden hinterlegt.';
                break;
            case 59:
                $return = 'Fehler: Die ISO der Sprache in der Zahlungsmethode entspricht nicht der Konvention.';
                break;
            case 60:
                $return = 'Fehler: Der Name in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case 61:
                $return = 'Fehler: Der ChargeName in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case 62:
                $return = 'Fehler: Der InfoText in den Zahlungsmethoden Sprache entspricht nicht der Konvention.';
                break;
            case 63:
                $return = 'Fehler: Zahlungsmethode Einstellungen type entspricht nicht der Konvention.';
                break;
            case 64:
                $return = 'Fehler: Zahlungsmethode Einstellungen initialValue entspricht nicht der Konvention.';
                break;
            case 65:
                $return = 'Fehler: Zahlungsmethode Einstellungen sort entspricht nicht der Konvention.';
                break;
            case 66:
                $return = 'Fehler: Zahlungsmethode Einstellungen conf entspricht nicht der Konvention.';
                break;
            case 67:
                $return = 'Fehler: Zahlungsmethode Einstellungen Name entspricht nicht der Konvention.';
                break;
            case 68:
                $return = 'Fehler: Zahlungsmethode Einstellungen ValueName entspricht nicht der Konvention.';
                break;
            case 69:
                $return = 'Fehler: Keine SelectboxOptionen vorhanden.';
                break;
            case 70:
                $return = 'Fehler: Die Option entspricht nicht der Konvention.';
                break;
            case 71:
                $return = 'Fehler: Die Sortierung in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 72:
                $return = 'Fehler: Soap in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 73:
                $return = 'Fehler: Curl in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 74:
                $return = 'Fehler: Sockets in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 75:
                $return = 'Fehler: ClassName in den Zahlungsmethoden entspricht nicht der Konvention.';
                break;
            case 76:
                $return = 'Fehler: Der Templatename entspricht nicht der Konvention.';
                break;
            case 77:
                $return = 'Fehler: Die Templatedatei für den Frontend Link existiert nicht.';
                break;
            case 78:
                $return = 'Fehler: Es darf nur ein Templatename oder ein Fullscreen Templatename existieren.';
                break;
            case 79:
                $return = 'Fehler: Der Fullscreen Templatename entspricht nicht der Konvention.';
                break;
            case 80:
                $return = 'Fehler: Die Fullscreen Templatedatei für den Frontend Link existiert nicht.';
                break;
            case 81:
                $return = 'Fehler: Für ein Frontend Link muss ein Templatename oder Fullscreen Templatename angegeben werden.';
                break;
            case 82:
                $return = 'Fehler: Keine Box vorhanden.';
                break;
            case 83:
                $return = 'Fehler: Box Name entspricht nicht der Konvention.';
                break;
            case 84:
                $return = 'Fehler: Box Templatedatei entspricht nicht der Konvention.';
                break;
            case 85:
                $return = 'Fehler: Box Templatedatei existiert nicht.';
                break;
            case 86:
                $return = 'Fehler: Lizenzklasse existiert nicht.';
                break;
            case 87:
                $return = 'Fehler: Name der Lizenzklasse entspricht nicht der konvention.';
                break;
            case 88:
                $return = 'Fehler: Lizenklasse ist nicht definiert.';
                break;
            case 89:
                $return = 'Fehler: Methode checkLicence in der Lizenzklasse ist nicht definiert.';
                break;
            case 90:
                $return = 'Fehler: PluginID bereits in der Datenbank vorhanden.';
                break;
            case 91:
                $return = 'Fehler: Keine Emailtemplates vorhanden, obwohl der Node angelegt wurde.';
                break;
            case 92:
                $return = 'Fehler: Template Name entspricht nicht der Konvention.';
                break;
            case 93:
                $return = 'Fehler: Template Type entspricht nicht der Konvention.';
                break;
            case 94:
                $return = 'Fehler: Template ModulId entspricht nicht der Konvention.';
                break;
            case 95:
                $return = 'Fehler: Template Active entspricht nicht der Konvention.';
                break;
            case 96:
                $return = 'Fehler: Template AKZ entspricht nicht der Konvention.';
                break;
            case 97:
                $return = 'Fehler: Template AGB entspricht nicht der Konvention.';
                break;
            case 98:
                $return = 'Fehler: Template WRB entspricht nicht der Konvention.';
                break;
            case 99:
                $return = 'Fehler: Die ISO der Emailtemplate Sprache entspricht nicht der Konvention.';
                break;
            case 100:
                $return = 'Fehler: Der Subject Name entspricht nicht der Konvention.';
                break;
            case 101:
                $return = 'Fehler: Keine Templatesprachen vorhanden.';
                break;
            case 102:
                $return = 'Fehler: CheckBoxFunction Name entspricht nicht der Konvention.';
                break;
            case 103:
                $return = 'Fehler: CheckBoxFunction ID entspricht nicht der Konvention.';
                break;
            case 104:
                $return = 'Fehler: Frontend Link Attribut NoFollow entspricht nicht der Konvention.';
                break;
            case 105:
                $return = 'Fehler: Keine Widgets vorhanden.';
                break;
            case 106:
                $return = 'Fehler: Widget Title entspricht nicht der Konvention.';
                break;
            case 107:
                $return = 'Fehler: Widget Class entspricht nicht der Konvention.';
                break;
            case 108:
                $return = 'Fehler: Die Datei für die Klasse des AdminWidgets existiert nicht.';
                break;
            case 109:
                $return = 'Fehler: Container im Widget entspricht nicht der Konvention.';
                break;
            case 110:
                $return = 'Fehler: Pos im Widget entspricht nicht der Konvention.';
                break;
            case 111:
                $return = 'Fehler: Expanded im Widget entspricht nicht der Konvention.';
                break;
            case 112:
                $return = 'Fehler: Active im Widget entspricht nicht der Konvention.';
                break;
            case 113:
                $return = 'Fehler: AdditionalTemplateFile in den Zahlungsmethoden entspricht nicht der Konvention';
                break;
            case 114:
                $return = 'Die Datei für das Zusatzschritt-Template der Zahlungsmethode existiert nicht';
                break;
            case 115:
                $return = 'Keine Formate vorhanden';
                break;
            case 116:
                $return = 'Format Name entspricht nicht der Konvention';
                break;
            case 117:
                $return = 'Format Filename entspricht nicht der Konvention';
                break;
            case 118:
                $return = 'Format enthaelt weder Content, noch eine Contentdatei';
                break;
            case 119:
                $return = 'Format Encoding entspricht nicht der Konvention';
                break;
            case 120:
                $return = 'Format ShippingCostsDeliveryCountry entspricht nicht der Konvention';
                break;
            case 121:
                $return = 'Format ContenFile entspricht nicht der Konvention';
                break;
            case 122:
                $return = 'Kein Template vorhanden';
                break;
            case 123:
                $return = 'Templatedatei entspricht nicht der Konvention';
                break;
            case 124:
                $return = 'Templatedatei existiert nicht';
                break;
            case 125:
                $return = 'Fehler: Uninstall File existiert nicht';
                break;
            case 127:
                $return = 'Fehler: Das Plugin ben&ouml;tigt ionCube';
                break;
            case 128:
                $return = 'Fehler: OptionsSource-Datei wurde nicht angegeben';
                break;
            case 129:
                $return = 'Fehler: OptionsSource-Datei existiert nicht';
                break;
            case 130:
                $return = 'Fehler: Bootstrap-Klasse "%cPluginID%\\Bootstrap" existiert nicht';
                break;
            case 131:
                $return = 'Fehler: Bootstrap-Klasse "%cPluginID%\\Bootstrap" muss das Interface "IPlugin" implementieren';
                break;
        }
    }

    $search = array_map(function ($val) {
        return sprintf('%%%s%%', $val);
    }, array_keys((array)$oPlugin));

    $replace = array_values((array)$oPlugin);
    $return  = str_replace($search, $replace, $return);

    return utf8_decode($return);
}
