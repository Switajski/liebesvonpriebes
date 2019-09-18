<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

ob_start();

require_once __DIR__ . '/syncinclude.php';
// configuration
require_once __DIR__ . '/../includes/config.JTL-Shop.ini.php';
require_once __DIR__ . '/../includes/defines.php';
error_reporting(SYNC_LOG_LEVEL);
// basic classes
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceDB.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.NiceMail.php';
require_once PFAD_ROOT . PFAD_CLASSES_CORE . 'class.core.Nice.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Synclogin.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Sprache.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Path.php';
// global helper
require_once PFAD_ROOT . PFAD_INCLUDES . 'tools.Global.php';
require_once PFAD_ROOT . PFAD_BLOWFISH . 'xtea.class.php';
require_once PFAD_ROOT . PFAD_PCLZIP . 'pclzip.lib.php';
// database
//$DB = new NiceDB(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// language
$oSprache = Sprache::getInstance(true);

/**
 * Class NetSyncRequest
 */
class NetSyncRequest
{
    const Unknown = 0;
    const UploadFiles = 1;
    const UploadFileData = 2;
    const DownloadFolders = 3;
    const DownloadFilesInFolder = 4;
    const CronjobTrigger = 5;
    const CronjobStatus = 6;
    const CronjobHistory = 7;
}

/**
 * Class NetSyncResponse
 */
class NetSyncResponse
{
    const Unknown = -1;
    const Ok = 0;
    const ErrorLogin = 1;
    const ErrorDeserialize = 2;
    const ReceivingData = 3;
    const FolderNotExists = 4;
    const ErrorInternal = 5;
    const ErrorNoLicense = 6;
}

/**
 * Class SystemFile
 */
class SystemFile
{
    /**
     * @var int
     */
    public $kFileID;

    /**
     * @var string
     */
    public $cFilepath;

    /**
     * @var string
     */
    public $cRelFilepath;

    /**
     * @var string
     */
    public $cFilename;

    /**
     * @var string
     */
    public $cDirname;

    /**
     * @var string
     */
    public $cExtension;

    /**
     * @var int
     */
    public $nUploaded;

    /**
     * @var int
     */
    public $nBytes;

    /**
     * @param int    $kFileID
     * @param string $cFilepath
     * @param string $cRelFilepath
     * @param string $cFilename
     * @param string $cDirname
     * @param string $cExtension
     * @param int    $nUploaded
     * @param int    $nBytes
     */
    public function __construct($kFileID, $cFilepath, $cRelFilepath, $cFilename, $cDirname, $cExtension, $nUploaded, $nBytes)
    {
        $this->kFileID      = $kFileID;
        $this->cFilepath    = $cFilepath;
        $this->cRelFilepath = $cRelFilepath;
        $this->cFilename    = $cFilename;
        $this->cDirname     = $cDirname;
        $this->cExtension   = $cExtension;
        $this->nUploaded    = $nUploaded;
        $this->nBytes       = $nBytes;
    }
}

/**
 * Class SystemFolder
 */
class SystemFolder
{
    /**
     * @var string
     */
    public $cBaseName;

    /**
     * @var string
     */
    public $cBasePath;

    /**
     * @var array
     */
    public $oSubFolders;

    /**
     * @param string $cBaseName
     * @param string $cBasePath
     * @param array  $oSubFolders
     */
    public function __construct($cBaseName = '', $cBasePath = '', $oSubFolders = [])
    {
        $this->cBaseName   = $cBaseName;
        $this->cBasePath   = $cBasePath;
        $this->oSubFolders = $oSubFolders;
    }
}

/**
 * Class CronjobStatus
 */
class CronjobStatus
{
    /**
     * @var string
     */
    public $cExportformat;

    /**
     * @var string
     */
    public $cStartDate;

    /**
     * @var int
     */
    public $nRepeat;

    /**
     * @var int
     */
    public $nDone;

    /**
     * @var int
     */
    public $nOverall;

    /**
     * @var string
     */
    public $cLastStartDate;

    /**
     * @var string
     */
    public $cNextStartDate;

    /**
     * @param int    $kCron
     * @param string $cExportformat
     * @param string $cStartDate
     * @param int    $nRepeat
     * @param int    $nDone
     * @param int    $nOverall
     * @param string $cLastStartDate
     * @param string $cNextStartDate
     */
    public function __construct($kCron, $cExportformat, $cStartDate, $nRepeat, $nDone, $nOverall, $cLastStartDate, $cNextStartDate)
    {
        $this->kCron          = $kCron;
        $this->cExportformat  = $cExportformat;
        $this->cStartDate     = $cStartDate;
        $this->nRepeat        = $nRepeat;
        $this->nDone          = $nDone;
        $this->nOverall       = $nOverall;
        $this->cLastStartDate = $cLastStartDate;
        $this->cNextStartDate = $cNextStartDate;
    }
}

/**
 * Class CronjobHistory
 */
class CronjobHistory
{
    /**
     * @var string
     */
    public $cExportformat;

    /**
     * @var string
     */
    public $cDateiname;

    /**
     * @var int
     */
    public $nDone;

    /**
     * @var string
     */
    public $cLastStartDate;

    /**
     * @param string $cExportformat
     * @param string $cDateiname
     * @param int    $nDone
     * @param string $cLastStartDate
     */
    public function __construct($cExportformat, $cDateiname, $nDone, $cLastStartDate)
    {
        $this->cExportformat  = $cExportformat;
        $this->cDateiname     = $cDateiname;
        $this->nDone          = $nDone;
        $this->cLastStartDate = $cLastStartDate;
    }
}

/**
 * Class NetSyncHandler
 */
class NetSyncHandler
{
    /**
     * @var NetSyncHandler
     */
    private static $oInstance;

    /**
     *
     */
    protected function init()
    {
    }

    /**
     * @param $eRequest
     */
    protected function request($eRequest)
    {
    }

    /**
     * @param Exception $oException
     */
    public static function exception($oException)
    {
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (self::$oInstance !== null) {
            throw new Exception('Class ' . __CLASS__ . ' already created');
        }
        self::$oInstance = $this;
        $this->init();
        if (!$this->isAuthed()) {
            static::throwResponse(NetSyncResponse::ErrorLogin);
        }
        $this->request((int)$_REQUEST['e']);
    }

    /**
     * @param string $cClass
     */
    public static function create($cClass)
    {
        if (self::$oInstance === null) {
            if (class_exists($cClass)) {
                new $cClass;
                set_exception_handler([$cClass, 'exception']);
            }
        }
    }

    /**
     * @return bool
     */
    protected function isAuthed()
    {
        // by token
        if (isset($_REQUEST['t'])) {
            session_id($_REQUEST['t']);
            session_start();

            return $_SESSION['bAuthed'];
        }
        // by syncdata
        $cName   = utf8_decode(urldecode($_REQUEST['uid']));
        $cPass   = utf8_decode(urldecode($_REQUEST['upwd']));
        $bAuthed = false;
        if (strlen($cName) > 0 && strlen($cPass) > 0) {
            $oSync   = new Synclogin();
            $bAuthed = ($cName === $oSync->cName && $cPass === $oSync->cPass);
        }
        if ($bAuthed) {
            session_start();
            $_SESSION['bAuthed'] = $bAuthed;
        }

        return $bAuthed;
    }

    /**
     * @param int        $nCode
     * @param null|mixed $oData
     */
    protected static function throwResponse($nCode, $oData = null)
    {
        $oResponse         = new stdClass();
        $oResponse->nCode  = $nCode;
        $oResponse->cToken = '';
        $oResponse->oData  = null;

        if ($nCode === 0) {
            $oResponse->cToken = session_id();
            $oResponse->oData  = $oData;
        }

        $cJson = json_encode($oResponse);
        $cJson = utf8_encode($cJson);

        echo $cJson;
        exit;
    }

    /**
     * @param string $filename
     * @param string $mimetype
     * @param string $outname
     */
    public function streamFile($filename, $mimetype, $outname = '')
    {
        $HTTP_USER_AGENT = (!empty($_SERVER['HTTP_USER_AGENT']))
            ? $_SERVER['HTTP_USER_AGENT']
            : '';
        $browser_agent   = 'other';
        if (preg_match('/^Opera(\/| )([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT) === 1) {
            $browser_agent = 'opera';
        } elseif (preg_match('/^MSIE ([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT) === 1) {
            $browser_agent = 'ie';
        } elseif (preg_match('/^OmniWeb\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT) === 1) {
            $browser_agent = 'omniweb';
        } elseif (preg_match('/^Netscape([0-9]{1})/', $HTTP_USER_AGENT) === 1) {
            $browser_agent = 'netscape';
        } elseif (preg_match('/^Mozilla\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT) === 1) {
            $browser_agent = 'mozilla';
        } elseif (preg_match('/^Konqueror\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT) === 1) {
            $browser_agent = 'konqueror';
        }
        if (($mimetype === 'application/octet-stream') || ($mimetype === 'application/octetstream')) {
            $mimetype = 'application/octet-stream';
            if (($browser_agent === 'ie') || ($browser_agent === 'opera')) {
                $mimetype = 'application/octetstream';
            }
        }

        @ob_end_clean();
        @ini_set('zlib.output_compression', 'Off');

        header('Pragma: public');
        header('Content-Transfer-Encoding: none');

        if ($outname === '') {
            $outname = basename($filename);
        }
        if ($browser_agent === 'ie') {
            header('Content-Type: ' . $mimetype);
            header('Content-Disposition: inline; filename="' . $outname . '"');
        } else {
            header('Content-Type: ' . $mimetype . '; name="' . $outname . '"');
            header('Content-Disposition: attachment; filename=' . $outname);
        }
        $size = @filesize($filename);
        if ($size) {
            header("Content-length: $size");
        }
        readfile($filename);
        unlink($filename);
        exit;
    }
}

/**
 * @param string $cBaseDir
 * @return array
 */
function getFolderStruct($cBaseDir)
{
    $oFolder_arr     = [];
    $cBaseDir        = realpath($cBaseDir);
    $cFolderData_arr = scandir($cBaseDir);
    foreach ($cFolderData_arr as $cFolderItem) {
        if ($cFolderItem === '.' || $cFolderItem === '..' || $cFolderItem[0] === '.') {
            continue;
        }
        $cFolderPath = $cBaseDir . DIRECTORY_SEPARATOR . $cFolderItem;
        if (is_dir($cFolderPath)) {
            $oFolder              = new SystemFolder($cFolderItem, $cFolderPath);
            $oFolder_arr[]        = $oFolder;
            $oFolder->oSubFolders = getFolderStruct($cFolderPath);
        }
    }

    return $oFolder_arr;
}

/**
 * @param string $cBaseDir
 * @param bool   $bPreview
 * @return array
 */
function getFilesStruct($cBaseDir, $bPreview = false)
{
    $nIndex          = 0;
    $oFiles_arr      = [];
    $cBaseDir        = realpath($cBaseDir);
    $cFolderData_arr = scandir($cBaseDir);
    foreach ($cFolderData_arr as $cFile) {
        if ($cFile === '.' || $cFile === '..' || $cFile[0] === '.') {
            continue;
        }
        $cFilePath = $cBaseDir . DIRECTORY_SEPARATOR . $cFile;
        if (is_file($cFilePath)) {
            $cInfo_arr    = pathinfo($cFilePath);
            $cRelFilePath = substr($cFilePath, strlen($bPreview ? PFAD_DOWNLOADS_PREVIEW : PFAD_DOWNLOADS));
            $oFile        = new SystemFile(
                $nIndex++,
                $cFilePath,
                $cRelFilePath,
                $cInfo_arr['filename'],
                $cInfo_arr['dirname'],
                $cInfo_arr['extension'],
                filemtime($cFilePath),
                filesize($cFilePath)
            );
            $oFiles_arr[] = $oFile;
        }
    }

    return $oFiles_arr;
}
