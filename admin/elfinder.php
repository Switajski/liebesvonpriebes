<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @global JTLSmarty    $smarty
 * @global AdminAccount $oAccount
 */

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'elfinder_inc.php';

if (validateToken()) {
    $mediafilesSubdir = 'Bilder';
    $mediafilesType   = verifyGPDataString('mediafilesType');
    $elfinderCommand  = verifyGPDataString('cmd');
    $isCKEditor       = verifyGPDataString('ckeditor') === '1';
    $CKEditorFuncNum  = verifyGPDataString('CKEditorFuncNum');

    switch ($mediafilesType) {
        case 'image':
            $mediafilesSubdir = 'Bilder';
            break;
        case 'video':
            $mediafilesSubdir = 'Videos';
            break;
        case 'music':
            $mediafilesSubdir = 'Musik';
            break;
        case 'misc':
            $mediafilesSubdir = 'Sonstiges';
            break;
        default:
            break;
    }

    if (!empty($elfinderCommand)) {
        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        // run elFinder
        $connector = new elFinderConnector(new elFinder([
            'bind' => [
                'rm rename' => function ($cmd, &$result, $args, $elfinder, $volume) use ($mediafilesSubdir) {
                    $sizes = ['xs', 'sm', 'md', 'lg', 'xl'];

                    foreach ($result['removed'] as $filename) {
                        foreach ($sizes as $size) {
                            $scaledFile = PFAD_ROOT . PFAD_MEDIAFILES
                                . "$mediafilesSubdir/.$size/{$filename['name']}";

                            if (file_exists($scaledFile)) {
                                @unlink($scaledFile);
                            }
                        }
                    }
                },
            ],
            'roots' => [
                // Items volume
                [
                    // driver for accessing file system (REQUIRED)
                    'driver'        => 'LocalFileSystem',
                    // path to files (REQUIRED)
                    'path'          => PFAD_ROOT . PFAD_MEDIAFILES . $mediafilesSubdir,
                    // URL to files (REQUIRED)
                    'URL'           => parse_url(
                        URL_SHOP . '/' . PFAD_MEDIAFILES . $mediafilesSubdir,
                        PHP_URL_PATH
                    ),
                    // to make hash same to Linux one on windows too
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                    // All Mimetypes not allowed to upload
                    'uploadDeny'    => ['all'],
                    // Mimetype `image` and `text/plain` allowed to upload
                    'uploadAllow'   => ['image', 'text/plain'],
                    // allowed Mimetype `image` and `text/plain` only
                    'uploadOrder'   => ['deny', 'allow'],
                    // disable and hide dot starting files (OPTIONAL)
                    'accessControl' => 'access',
                ],
            ],
        ]));

        $connector->run();
    } else {
        $smarty
            ->assign('mediafilesType', $mediafilesType)
            ->assign('mediafilesSubdir', $mediafilesSubdir)
            ->assign('isCKEditor', $isCKEditor)
            ->assign('CKEditorFuncNum', $CKEditorFuncNum)
            ->assign('templateUrl', Shop::getURL() . '/' . PFAD_ADMIN . $currentTemplateDir)
            ->display('elfinder.tpl');
    }
}