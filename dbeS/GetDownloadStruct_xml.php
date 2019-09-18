<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/NetSync_inc.php';

/**
 * Class ArticleDownloads
 */
class ArticleDownloads extends NetSyncHandler
{
    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @param int $eRequest
     */
    protected function request($eRequest)
    {
        switch ($eRequest) {
            case NetSyncRequest::DownloadFolders:
                $bPreview            = (int)$_POST['bPreview'];
                $oDownloadFolder_arr = getFolderStruct($bPreview ? PFAD_DOWNLOADS_PREVIEW : PFAD_DOWNLOADS);
                self::throwResponse(NetSyncResponse::Ok, $oDownloadFolder_arr);
                break;

            case NetSyncRequest::DownloadFilesInFolder:
                $bPreview = (int)$_POST['bPreview'];
                if (!isset($_POST['cBasePath']) || empty($_POST['cBasePath'])) {
                    $_POST['cBasePath'] = $bPreview ? PFAD_DOWNLOADS_PREVIEW : PFAD_DOWNLOADS;
                }
                $cBasePath = $_POST['cBasePath'];
                if (is_dir($cBasePath)) {
                    $oFiles_arr = getFilesStruct($cBasePath, $bPreview);
                    self::throwResponse(NetSyncResponse::Ok, $oFiles_arr);
                } else {
                    self::throwResponse(NetSyncResponse::FolderNotExists);
                }
                break;

        }
    }
}

NetSyncHandler::create('ArticleDownloads');
