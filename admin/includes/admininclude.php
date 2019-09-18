<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

// Defines
if (!isset($bExtern) || !$bExtern) {
    define('DEFINES_PFAD', __DIR__ . '/../../includes/');
    require DEFINES_PFAD . 'config.JTL-Shop.ini.php';
    require DEFINES_PFAD . 'defines.php';
    require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admindefines.php';
    // Existiert Konfiguration?
    defined('DB_HOST') || die('Kein MySql-Datenbank Host angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_NAME') || die('Kein MySql Datenbanknamen angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_USER') || die('Kein MySql-Datenbank Benutzer angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
    defined('DB_PASS') || die('Kein MySql-Datenbank Passwort angegeben. Bitte config.JTL-Shop.ini.php bearbeiten!');
}

require PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';
require PFAD_ROOT . PFAD_INCLUDES . 'error_handler.php';
require PFAD_ROOT . PFAD_INCLUDES . 'plugin_inc.php';
require PFAD_ROOT . PFAD_INCLUDES . 'autoload.php';
require PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceDB.php';
require PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Nice.php';
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'benutzerverwaltung_inc.php';
require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'admin_tools.php';

if (!function_exists('Shop')) {
    /**
     * @return Shop
     */
    function Shop()
    {
        return Shop::getInstance();
    }
}

// Datenbankverbindung aufbauen - ohne Debug Modus
$DB = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME, true);

$cache = JTLCache::getInstance();
$cache->setJtlCacheConfig();

$session = AdminSession::getInstance();

require PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';

Shop::bootstrap();

if ($oAccount->logged()) {
    Shop::fire('backend.notification', Notification::getInstance()->buildDefault());
}

if (isset($_POST['revision-action'], $_POST['revision-type'], $_POST['revision-id'])
    && validateToken()
    && $oAccount->logged()
) {
    $revision = new Revision();
    if ($_POST['revision-action'] === 'restore') {
        $revision->restoreRevision(
            $_POST['revision-type'],
            $_POST['revision-id'],
            isset($_POST['revision-secondary']) && $_POST['revision-secondary'] === '1',
            empty($_POST['restore-utf8']) || ($_POST['restore-utf8'] === '1')
        );
    } elseif ($_POST['revision-action'] === 'delete') {
        $revision->deleteRevision($_POST['revision-id']);
    }
}
