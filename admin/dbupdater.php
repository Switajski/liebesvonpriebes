<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
set_time_limit(0);

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Updater.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.AjaxResponse.php';
/** @global JTLSmarty $smarty */
$hasPermission = $oAccount->permission('SHOP_UPDATE_VIEW', false, false);
$action        = isset($_GET['action']) ? $_GET['action'] : null;

if ($action === null && !$hasPermission) {
    $oAccount->redirectOnFailure();
    exit;
}

if ($hasPermission === false) {
    $action = 'login';
}

$updater   = new Updater();
$response  = new AjaxResponse();
$template  = Template::getInstance();

// clear tempate cache
$_smarty = new JTLSmarty(true, true);
$_smarty->clearCompiledTemplate();

// clear data cache
Shop::Cache()->flushAll();

$buildStatus = function () use ($updater, $smarty, $template) {
    $currentFileVersion     = $updater->getCurrentFileVersion();
    $currentDatabaseVersion = $updater->getCurrentDatabaseVersion();
    $version                = $updater->getVersion();
    $updatesAvailable       = $updater->hasPendingUpdates();
    $updateError            = $updater->error();

    if (defined('ADMIN_MIGRATION') && ADMIN_MIGRATION) {
        $smarty->assign('manager', new MigrationManager());
    }

    $smarty
        ->assign('updatesAvailable', $updatesAvailable)
        ->assign('currentFileVersion', $currentFileVersion)
        ->assign('currentDatabaseVersion', $currentDatabaseVersion)
        ->assign('version', $version)
        ->assign('updateError', $updateError)
        ->assign('currentTemplateFileVersion', $template->xmlData->cShopVersion)
        ->assign('currentTemplateDatabaseVersion', $template->shopVersion);
};
$verified = validateToken();
switch ($action) {
    default:
        $buildStatus();
        $smarty->display('dbupdater.tpl');
        break;

    case 'status_tpl':
        $buildStatus();

        $result = $response->buildResponse([
            'tpl' => $smarty->fetch('tpl_inc/dbupdater_status.tpl')
        ]);

        $response->makeResponse($result, $action);
        break;

    case 'login':
        $result = $response->buildError('Unauthorized', 401);
        $response->makeResponse($result, $action);
        break;

    case 'update':
        if ($verified) {
            try {
                if ($template->xmlData->cShopVersion != $template->shopVersion) {
                    if ($template->setTemplate($template->xmlData->cName, $template->xmlData->eTyp)) {
                        unset($_SESSION['cTemplate'], $_SESSION['template']);
                    }
                }

                $fileVersion     = $updater->getCurrentFileVersion();
                $dbVersion       = $updater->getCurrentDatabaseVersion();

                $updateResult    = $updater->update();
                $availableUpdate = $updater->hasPendingUpdates();

                if ($updateResult instanceof IMigration) {
                    $updateResult = sprintf('Migration: %s', $updateResult->getDescription());
                } else {
                    $updateResult = sprintf('Version: %.2f', $updateResult / 100);
                }

                $result = $response->buildResponse([
                    'result'          => $updateResult,
                    'currentVersion'  => $dbVersion,
                    'updatedVersion'  => $dbVersion,
                    'availableUpdate' => $availableUpdate
                ]);
            } catch (Exception $e) {
                $result = $response->buildError($e->getMessage());
            }
        } else {
            $result = $response->buildError('CSRF validation failed.', 401);
            $response->makeResponse($result, $action);
        }

        $response->makeResponse($result, $action);
        break;

    case 'backup':
        $result = null;
        if ($verified) {
            try {
                $file = $updater->createSqlDumpFile(true);
                $updater->createSqlDump($file, true);

                $file   = basename($file);
                $params = http_build_query(['action' => 'download', 'file' => $file], '', '&');
                $url    = Shop::getAdminURL() . '/dbupdater.php?' . $params;

                $data = (object) [
                    'url'  => $url,
                    'file' => $file
                ];

                $result = $response->buildResponse($data);
            } catch (Exception $e) {
                $result = $response->buildError($e->getMessage());
            }
        } else {
            $result = $response->buildError('CSRF validation failed.', 401);
            $response->makeResponse($result, $action);
        }

        $response->makeResponse($result, $action);
        break;

    /*
    case 'migrate':
    {
        try {
            $executed   = [];
            $migrations = $pendingMigrations();

            foreach ($migrations as $version => $pending) {
                $migration = new MigrationManager($version);

                foreach ($pending as $id) {
                    $migration->executeMigrationById($id, IMigration::UP);
                    $executed[] = $id;
                }
            }

            $result = $updater->buildResponse([
                'migrations' => $executed
            ]);
        } catch (Exception $e) {
            $result = $response->buildError($e->getMessage());
        }

        $response->makeResponse($result, $action);
        break;
    }
    */

    case 'migration':
        if ($verified) {
            $id        = isset($_GET['id']) ? $_GET['id'] : null;
            $version   = isset($_GET['version']) ? (int)$_GET['version'] : null;
            $direction = isset($_GET['dir']) ? $_GET['dir'] : null;

            try {
                $migration = new MigrationManager($version);

                if ($id !== null && in_array($direction, [IMigration::UP, IMigration::DOWN], true)) {
                    $migration->executeMigrationById($id, $direction);
                }

                $result = $response->buildResponse($id);
            } catch (Exception $e) {
                $result = $response->buildError($e->getMessage());
            }
        } else {
            $result = $response->buildError('CSRF validation failed.', 401);
        }

        $response->makeResponse($result, $action);
        break;

    case 'download':
        if (!$verified || !isset($_GET['file'])) {
            return;
        }

        $file = $_GET['file'];

        if (!preg_match('/^([0-9_a-z]+).sql.gz$/', $file, $m)) {
            return;
        }

        $filePath = PFAD_ROOT . PFAD_EXPORT_BACKUP . $file;

        if (!file_exists($filePath)) {
            return;
        }

        $response->pushFile($filePath, 'application/x-gzip');
        break;
}
