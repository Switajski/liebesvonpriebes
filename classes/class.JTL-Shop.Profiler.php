<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Profiler
 */
class Profiler
{
    /**
     * @var Profiler
     */
    private static $_instance;

    /**
     * @var bool
     */
    public static $functional = false;

    /**
     * @var bool
     */
    public static $enabled = false;

    /**
     * @var bool
     */
    public static $started = false;

    /**
     * @var array
     */
    public static $data = [];

    /**
     * @var string
     */
    public static $dataDir = '/tmp';

    /**
     * @var int
     */
    public static $flags = -1;

    /**
     * @var array
     */
    public static $options = [];

    /**
     * @var object
     */
    public static $run;

    /**
     * set to true to finish profiling
     * used to not save sql statements created by the profiler itself
     *
     * @var bool
     */
    private static $stopProfiling = false;

    /**
     * @var array
     */
    private static $pluginProfile = [];

    /**
     * @var array
     */
    private static $sqlProfile = [];

    /**
     * @var array
     */
    private static $sqlErrors = [];

    /**
     * @var array
     */
    private static $cacheProfile = [
        'options' => [],
        'get'     => ['success' => [], 'failure' => []],
        'set'     => ['success' => [], 'failure' => []],
        'flush'   => ['success' => [], 'failure' => []],
    ];

    /**
     * @var null|string
     */
    public static $method;

    /**
     * @param int    $flags
     * @param array  $options
     * @param string $dir
     */
    private function __construct($flags, $options, $dir)
    {
        if (defined('PROFILE_SHOP') && PROFILE_SHOP === true) {
            self::$enabled = true;
            if (function_exists('xhprof_enable')) {
                self::$method = 'xhprof';
            } elseif (function_exists('tideways_enable')) {
                self::$method = 'tideways';
            }
            if (self::$method !== null) {
                self::$functional = true;
                if ($flags === -1) {
                    $flags = (self::$method === 'xhprof') ?
                        (XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY) :
                        (TIDEWAYS_FLAGS_CPU | TIDEWAYS_FLAGS_MEMORY | TIDEWAYS_FLAGS_NO_SPANS);
                }
                self::$flags   = $flags;
                self::$options = $options;
                self::$dataDir = $dir;
            }
        }
    }

    /**
     * @param int    $flags
     * @param array  $options
     * @param string $dir
     * @return Profiler
     */
    public static function getInstance($flags = -1, $options = [], $dir = '/tmp')
    {
        return (self::$_instance === null) ? new self($flags, $options, $dir) : self::$_instance;
    }

    /**
     * check if one of the profilers is active
     *
     * @return int - 0: none, 1: NiceDB profiler, 2: xhprof, 3: plugin profiler, 4: plugin, xhprof, 5: DB, plugin, 6: DB, xhprof, 7: all
     */
    public static function getIsActive()
    {
        if (PROFILE_QUERIES !== false && PROFILE_SHOP === true && PROFILE_PLUGINS === true
        ) {
            return 7;
        }
        if (PROFILE_QUERIES !== false && PROFILE_SHOP === true
        ) {
            return 6;
        }
        if (PROFILE_QUERIES !== false && PROFILE_PLUGINS === true
        ) {
            return 5;
        }
        if (PROFILE_SHOP === true && PROFILE_PLUGINS === true
        ) {
            return 4;
        }
        if (PROFILE_PLUGINS === true) {
            return 3;
        }
        if (PROFILE_SHOP === true) {
            return 2;
        }
        if (PROFILE_QUERIES !== false) {
            return 1;
        }

        return 0;
    }

    /**
     * @param string $action
     * @param string $status
     * @param string $key
     */
    public static function setCacheProfile($action = 'get', $status = 'success', $key)
    {
        self::$cacheProfile[$action][$status][] = $key;
    }

    /**
     * set plugin profiler run
     *
     * @param mixed $data
     * @return bool
     */
    public static function setPluginProfile($data)
    {
        if (defined('PROFILE_PLUGINS') && PROFILE_PLUGINS === true) {
            self::$pluginProfile[] = $data;

            return true;
        }

        return false;
    }

    /**
     * set sql profiler run
     *
     * @param mixed $data
     * @return bool
     */
    public static function setSQLProfile($data)
    {
        if (self::$stopProfiling === false) {
            self::$sqlProfile[] = $data;

            return true;
        }

        return false;
    }

    /**
     * set sql profiler run
     *
     * @param mixed $data
     * @return bool
     */
    public static function setSQLError($data)
    {
        if (self::$stopProfiling === false) {
            self::$sqlErrors[] = $data;

            return true;
        }

        return false;
    }

    /**
     * save sql profiler run to DB
     *
     * @return bool
     */
    public static function saveSQLProfile()
    {
        self::$stopProfiling = true;
        if (PROFILE_QUERIES_ECHO !== true && count(self::$sqlProfile) > 0) {
            //create run object
            $run        = new stdClass();
            $run->url   = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $run->ptype = 'sql';
            //build stats for this run
            $run->total_count = 0; //total number of queries
            $run->total_time  = 0.0; //total execution time
            //filter duplicated queries
            $filtered = [];
            foreach (self::$sqlProfile as $_queryRun) {
                if (!isset($filtered[$_queryRun->hash])) {
                    $obj                        = new stdClass();
                    $obj->runtime               = $_queryRun->time;
                    $obj->runcount              = $_queryRun->count;
                    $obj->statement             = trim($_queryRun->statement);
                    $obj->tablename             = $_queryRun->table;
                    $obj->data                  = isset($_queryRun->backtrace)
                        ? serialize(['backtrace' => $_queryRun->backtrace])
                        : null;
                    $filtered[$_queryRun->hash] = $obj;
                } else {
                    $filtered[$_queryRun->hash]->runtime += $_queryRun->time;
                    ++$filtered[$_queryRun->hash]->runcount;
                }
                $run->total_time += $_queryRun->time;
                ++$run->total_count;
            }
            //insert profiler run into DB - return a new primary key
            $runID = Shop::DB()->insert('tprofiler', $run);
            if (is_numeric($runID)) {
                //set runID for all filtered queries and save to DB
                $runID = (int)$runID;
                foreach ($filtered as $_queryRun) {
                    $_queryRun->runID = $runID;
                    Shop::DB()->insert('tprofiler_runs', $_queryRun);
                }
                foreach (self::$sqlErrors as $_error) {
                    $_queryRun            = new stdClass();
                    $_queryRun->runID     = $runID;
                    $_queryRun->tablename = 'error';
                    $_queryRun->runtime   = 0;
                    $_queryRun->statement = trim($_error->statement);
                    $_queryRun->data      = serialize(['message' => $_error->message, 'backtrace' => $_error->backtrace]);
                    Shop::DB()->insert('tprofiler_runs', $_queryRun);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * save plugin profiler run to DB
     *
     * @return bool
     */
    public static function savePluginProfile()
    {
        self::$stopProfiling = true;
        if (defined('PROFILE_PLUGINS') && PROFILE_PLUGINS === true && count(self::$pluginProfile) > 0) {
            $run              = new stdClass();
            $run->url         = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $run->ptype       = 'plugin';
            $run->total_count = 0;
            $run->total_time  = 0.0;

            $hooks = [];
            //combine multiple calls of the same file
            foreach (self::$pluginProfile as $_fileRun) {
                if (isset($_fileRun['hookID'])) {
                    //update run stats
                    $run->total_count++;
                    $run->total_time += $_fileRun['runtime'];
                    if (!isset($hooks[$_fileRun['hookID']])) {
                        $hooks[$_fileRun['hookID']][] = $_fileRun;
                    } else {
                        $foundInList = false;
                        //check if the same file has been executed multiple times for this hook
                        foreach ($hooks[$_fileRun['hookID']] as &$_run) {
                            if ($_run['file'] === $_fileRun['file']) {
                                ++$_run['runcount'];
                                $_run['runtime'] += $_fileRun['runtime'];
                                $foundInList = true;
                                break;
                            }
                        }
                        unset($_run);
                        if ($foundInList === false) {
                            $hooks[$_fileRun['hookID']][] = $_fileRun;
                        }
                    }
                }
            }
            self::$pluginProfile = [];
            foreach ($hooks as $_hook) {
                foreach ($_hook as $_file) {
                    self::$pluginProfile[] = $_file;
                }
            }
            $runID = Shop::DB()->insert('tprofiler', $run);
            if (is_numeric($runID)) {
                $runID = (int)$runID;
                foreach (self::$pluginProfile as $_fileRun) {
                    $obj           = new stdClass();
                    $obj->runID    = $runID;
                    $obj->hookID   = isset($_fileRun['hookID']) ? $_fileRun['hookID'] : 0;
                    $obj->filename = $_fileRun['file'];
                    $obj->runtime  = $_fileRun['runtime'];
                    $obj->runcount = $_fileRun['runcount'];
                    Shop::DB()->insert('tprofiler_runs', $obj);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * return all the sql profile data currently collected
     * for the use in plugins like JTLDebug
     *
     * @return array
     */
    public static function getCurrentSQLProfile()
    {
        return self::$sqlProfile;
    }

    /**
     * return all the plugin profile data currently collected
     * for the use in plugins like JTLDebug
     *
     * @return array
     */
    public static function getCurrentPluginProfile()
    {
        return self::$pluginProfile;
    }

    /**
     * return all the cache profile data currently collected
     * for the use in plugins like JTLDebug
     *
     * @return array
     */
    public static function getCurrentCacheProfile()
    {
        return self::$cacheProfile;
    }

    /**
     * get plugin profiler data from DB
     *
     * @param bool $combined
     * @return array
     */
    public static function getPluginProfiles($combined = false)
    {
        return self::getProfile('plugin', $combined);
    }

    /**
     * @param bool $combined
     * @return array
     */
    public static function getSQLProfiles($combined = false)
    {
        return self::getProfile('sql', $combined);
    }

    /**
     * generic profiler getter
     *
     * @param string $type
     * @param bool   $combined
     * @return array
     */
    private static function getProfile($type = 'plugin', $combined = false)
    {
        if ($combined === true) {
            return Shop::DB()->query("
                SELECT *
                    FROM tprofiler
                    JOIN tprofiler_runs 
                        ON tprofiler.runID = tprofiler_runs.runID
                    WHERE ptype = '" . $type . "'
                    ORDER BY runID DESC", 2
            );
        }
        $profiles = Shop::DB()->selectAll('tprofiler', 'ptype', $type, '*', 'runID DESC');
        $data     = [];
        if (is_array($profiles)) {
            foreach ($profiles as $_profile) {
                $_profile->data = Shop::DB()->selectAll(
                    'tprofiler_runs',
                    'runID',
                    (int)$_profile->runID,
                    '*', 'runtime DESC'
                );
                $data[] = $_profile;
            }
        }

        return $data;
    }

    /**
     * @param int    $flags
     * @param array  $options
     * @param string $dir
     * @return bool
     */
    public static function start($flags = -1, $options = [], $dir = '/tmp')
    {
        if (defined('PROFILE_SHOP') && PROFILE_SHOP === true) {
            self::$enabled = true;
            if (function_exists('xhprof_enable')) {
                self::$method = 'xhprof';
            } elseif (function_exists('tideways_enable')) {
                self::$method = 'tideways';
            }
            if (self::$method !== null) {
                self::$functional = true;
                if ($flags === -1) {
                    $flags = (self::$method === 'xhprof')
                        ? (XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY)
                        : (TIDEWAYS_FLAGS_CPU | TIDEWAYS_FLAGS_MEMORY | TIDEWAYS_FLAGS_NO_SPANS);
                }
                self::$flags   = $flags;
                self::$options = $options;
                self::$dataDir = $dir;
            }
        }
        if (self::$enabled === true && self::$functional === true) {
            self::$started = true;
            if (self::$method === 'xhprof') {
                xhprof_enable(self::$flags, self::$options);
            } else {
                tideways_enable(self::$flags);
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function getIsStarted()
    {
        return self::$started;
    }

    /**
     * @return bool
     */
    public static function finish()
    {
        if (self::$enabled === true && self::$functional === true) {
            self::$data = (self::$method === 'xhprof')
                ? xhprof_disable()
                : tideways_disable();

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public static function getData()
    {
        $html  = '';
        $runID = 0;
        if (self::$enabled === true && self::$functional === true) {
            require_once PFAD_ROOT . 'xhprof_lib/utils/xhprof_lib.php';
            require_once PFAD_ROOT . 'xhprof_lib/utils/xhprof_runs.php';
            if (self::$method === 'xhprof') {
                self::$run = new XHProfRuns_Default('/tmp');
                $runID     = self::$run->save_run(self::$data, 'xhprof_jtl');
            } else {
                $runID    = uniqid();
                $filename = sys_get_temp_dir() . '/' . $runID . '.xhprof_jtl.xhprof';
                file_put_contents($filename, serialize(self::$data));
            }
            $html = '<div class="profile-wrapper" style="position:fixed;z-index:9999;bottom:5px;left:5px;">
                        <a class="btn btn-danger" target="_blank" rel="nofollow" href="' .
                            Shop::getURL() . '/xhprof_html/index.php?run=' . $runID . '&source=xhprof_jtl">
                        View profile
                        </a>
                    </div>';
        }

        return [
            'html'   => $html,
            'run'    => self::$run,
            'run_id' => $runID
        ];
    }

    /**
     * output sql profiler data
     */
    public static function output()
    {
        if (PROFILE_QUERIES_ECHO === true && count(self::$sqlProfile) > 0) {
            $totalQueries = 0;
            $selects      = 0;
            $inserts      = 0;
            $deletes      = 0;
            $executes     = 0;
            $updates      = 0;
            $errors       = count(self::$sqlErrors);
            foreach (self::$sqlProfile as $_query) {
                if (isset($_query->type)) {
                    if ($_query->type === 'delete') {
                        ++$deletes;
                    } elseif ($_query->type === 'executeQuery') {
                        ++$executes;
                    } elseif ($_query->type === 'update') {
                        ++$updates;
                    } elseif ($_query->type === 'select') {
                        ++$selects;
                    } elseif ($_query->type === 'insert') {
                        ++$inserts;
                    }
                }
                ++$totalQueries;
            }
            echo '
                <style>
                    #pfdbg{
                        max-width:99%;opacity:0.85;position:absolute;z-index:999999;
                        background:#efefef;top:50px;left:10px;padding:10px;font-size:11px;
                        border:1px solid black;box-shadow:1px 1px 3px rgba(0,0,0,0.4);border-radius:3px;
                    }
                    #dbg-close{
                        float:right;
                    }
                    .sql-statement{
                        white-space: pre-wrap;
                        word-wrap: break-word;
                    }
                </style>
                <div id="pfdbg">' .
                '<button id="dbg-close" class="btn btn-close" onclick="$(\'#pfdbg\').hide();return false;">X</button>' .
                '<strong>Total Queries:</strong> ' . $totalQueries .
                '<br><strong>ExecuteQueries:</strong> ' . $executes .
                '<br><strong>SingleRowSelects:</strong> ' . $selects .
                '<br><strong>Updates:</strong> ' . $updates .
                '<br><strong>Inserts:</strong> ' . $inserts .
                '<br><strong>Deletes:</strong> ' . $deletes .
                '<br><strong>Errors:</strong> ' . $errors .
                '<br><strong>Statements:</strong> ' .
                '<ul class="sql-tables-list">';
            foreach (self::$sqlProfile as $_query) {
                echo '<li class="sql-table"><span class="table-name">' . $_query->table . '</span> (' . $_query->time . 's)';
                if (isset($_query->statement)) {
                    echo '<pre class="sql-statement">' . $_query->statement . '</pre>';
                }
                if (!empty($_query->backtrace)) {
                    echo '<ul class="backtrace">';
                    foreach ($_query->backtrace as $_bt) {
                        echo '<li class="backtrace-item">' .
                            $_bt['file'] . ':' . $_bt['line'] . ' - ' . (isset($_bt['class'])
                                ? ($_bt['class'] . '::')
                                : '') . $_bt['function'] . '()' .
                            '</li>';
                    }
                    echo '</ul>';
                }
            }
            echo '</ul>';
            if ($errors > 0) {
                echo '<br><strong>Errors:</strong> ' .
                    '<ul class="sql-tables-list">';
                foreach (self::$sqlErrors as $_error) {
                    echo '<li>' . $_error->message . ' for query <pre class="sql-statement">' . $_error->statement  . '</pre></li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }
    }
}
