<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NiceDB
 * Class for handling mysql DB
 *
 * @method int|object|array query(string $stmt, int $return, int|bool $echo = false, bool $bExecuteHook = false)
 * @method int|object|array queryPrepared(string $stmt, array $params, int $return, int|bool $echo = false, bool $bExecuteHook = false)
 * @method PDOStatement|int exQuery(string $stmt)
 * @method null|object select(string $tableName, string|array $keyname, string|int|array $keyvalue, string|null $keyname1 = null, string|int $keyvalue1 = null, string|null $keyname2 = null, string|int $keyvalue2 = null, bool $echo = false, string $select = '*')
 * @method int insert(string $tableName, object $object, int|bool $echo = false, bool $bExecuteHook = false)
 * @method int delete(string $tableName, string|array $keyname, string|int|array $keyvalue, bool|int $echo = false)
 * @method int update(string $tableName, string|array $keyname, string|int|array $keyvalue, object $object, int|bool $echo = false)
 * @method array selectAll(string $tableName, string|array $keys, string|int|array $values, string $select = '*', string $orderBy = '', string $limit = '')
 * @method string realEscape($string)
 * @method string pdoEscape($string)
 * @method string info()
 * @method string stats()
 * @method mixed getErrorCode()
 * @method string getErrorMessage()
 * @method mixed getError()
 */
class NiceDB
{
    /**
     * @var pdo
     */
    protected $db;

    /**
     * @var bool
     */
    protected $isConnected = false;

    /**
     * @var bool
     */
    public $logErrors = false;

    /**
     * @var string
     */
    public $logfileName;

    /**
     * debug mode
     *
     * @var bool
     */
    private $debug = false;

    /**
     * debug level, 0 no debug, 1 normal, 2 verbose, 3 very verbose with backtrace
     *
     * @var int
     */
    private $debugLevel = 0;

    /**
     * @var bool
     */
    private $collectData = false;

    /**
     * @var NiceDB
     */
    private static $instance;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string
     */
    public $state = 'instanciated';

    /**
     * @var array
     */
    private $config;

    /**
     * @var int
     */
    private $transactionCount = 0;

    /**
     * create DB Connection with default parameters
     *
     * @param string $dbHost
     * @param string $dbUser
     * @param string $dbPass
     * @param string $dbName
     * @param bool   $debugOverride
     * @throws Exception
     */
    public function __construct($dbHost, $dbUser, $dbPass, $dbName, $debugOverride = false)
    {
        $this->config = [
            'driver'   => 'mysql',
            'host'     => $dbHost,
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPass,
            'charset'  => 'latin1',
        ];
        $options = [];
        $dsn     = 'mysql:dbname=' . $dbName;
        if (defined('DB_SOCKET')) {
            $dsn .= ';unix_socket=' . DB_SOCKET;
        } else {
            if (defined('DB_SSL_KEY') && defined('DB_SSL_CERT') && defined('DB_SSL_CA')) {
                $options = [
                    PDO::MYSQL_ATTR_SSL_KEY  => DB_SSL_KEY,
                    PDO::MYSQL_ATTR_SSL_CERT => DB_SSL_CERT,
                    PDO::MYSQL_ATTR_SSL_CA   => DB_SSL_CA
                ];
            }
            $dsn .= ';host=' . $dbHost;
        }
        if (defined('DB_PERSISTENT_CONNECTIONS') && is_bool(DB_PERSISTENT_CONNECTIONS)) {
            $options[PDO::ATTR_PERSISTENT] = DB_PERSISTENT_CONNECTIONS;
        }
        if (JTL_CHARSET === 'iso-8859-1') {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'latin1'";
        }
        $this->pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        if (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === true) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        if (!(defined('DB_DEFAULT_SQL_MODE') && DB_DEFAULT_SQL_MODE === true)) {
            $this->pdo->exec("SET SQL_MODE=''");
        }
        if (defined('PFAD_LOGFILES')) {
            $this->logfileName = PFAD_LOGFILES . 'DB_errors.log';
        }
        if ($debugOverride === false) {
            if (defined('PROFILE_QUERIES') && PROFILE_QUERIES !== false) {
                if (defined('DEBUG_LEVEL')) {
                    $this->debugLevel = (int)DEBUG_LEVEL;
                }
                if (defined('PROFILE_QUERIES_ACTIVATION_FUNCTION') && is_callable(PROFILE_QUERIES_ACTIVATION_FUNCTION)) {
                    $this->collectData = (bool) call_user_func(PROFILE_QUERIES_ACTIVATION_FUNCTION);
                } elseif (PROFILE_QUERIES === true) {
                    $this->debug = true;
                }
                if ($this->debug === true && is_numeric(PROFILE_QUERIES)) {
                    $this->debugLevel = (int)PROFILE_QUERIES;
                }
            }
        }
        if (defined('ES_DB_LOGGING') && ES_DB_LOGGING !== false && ES_DB_LOGGING !== 0) {
            $this->logErrors = true;
        }
        $this->isConnected = true;
        self::$instance    = $this;
    }

    /**
     * @param null|string $DBHost
     * @param null|string $DBUser
     * @param null|string $DBpass
     * @param null|string $DBdatabase
     * @return NiceDB
     */
    public static function getInstance($DBHost = null, $DBUser = null, $DBpass = null, $DBdatabase = null)
    {
        return self::$instance !== null
            ? self::$instance
            : new self($DBHost, $DBUser, $DBpass, $DBdatabase);
    }

    /**
     * descructor for debugging purposes and closing db connection
     */
    public function __destruct()
    {
        $this->state = 'destructed';
        if ($this->isConnected) {
            $this->close();
        }
    }

    /**
     * Database configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * avoid destructer races with object cache
     *
     * @return $this
     */
    public function reInit()
    {
        $dsn = 'mysql:dbname=' . $this->config['database'];
        if (defined('DB_SOCKET')) {
            $dsn .= ';unix_socket=' . DB_SOCKET;
        } else {
            $dsn .= ';host=' . $this->config['host'];
        }
        $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password']);
        if (JTL_CHARSET === 'iso-8859-1') {
            $this->pdo->exec("SET NAMES 'latin1'");
        }

        return $this;
    }

    /**
     * object wrapper
     * this allows to call NiceDB->query() etc.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $mapping = self::map($method);

        return $mapping !== null
            ? call_user_func_array([$this, $mapping], $arguments)
            : null;
    }

    /**
     * static wrapper
     * this allows to call NiceShop::DB()->query() etc.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $mapping = self::map($method);

        return $mapping !== null
            ? call_user_func_array([self::$instance, $mapping], $arguments)
            : null;
    }

    /**
     * map function calls to real functions
     *
     * @param string $method
     * @return string|null
     */
    private static function map($method)
    {
        static $mapping = [
            'query'           => 'executeQuery',
            'queryPrepared'   => 'executeQueryPrepared',
            'exQuery'         => 'executeExQuery',
            'select'          => 'selectSingleRow',
            'insert'          => 'insertRow',
            'delete'          => 'deleteRow',
            'update'          => 'updateRow',
            'realEscape'      => 'escape',
            'pdoEscape'       => 'escape',
            'info'            => 'getServerInfo',
            'stats'           => 'getServerStats',
            'getErrorCode'    => '_getErrorCode',
            'getErrorMessage' => '_getErrorMessage',
            'getError'        => '_getError',
            'selectAll'       => 'selectArray',
            'isConnected'     => 'isConnected'
        ];

        return isset($mapping[$method]) ? $mapping[$method] : null;
    }

    /**
     * replay query with EXPLAIN command to get affected tables
     * collect data
     * enrich with backtrace
     *
     * @param string     $type
     * @param string     $stmt
     * @param int        $time
     * @param null|array $backtrace
     * @return $this
     */
    private function analyzeQuery($type = '', $stmt, $time = 0, $backtrace = null)
    {
        $explain = 'EXPLAIN ' . $stmt;
        try {
            $res = $this->pdo->query($explain);
        } catch (PDOException $e) {
            if (defined('NICEDB_EXCEPTION_ECHO') && NICEDB_EXCEPTION_ECHO === true) {
                Shop::dbg($stmt, false, 'Exception when trying to analyze query: ');
            }

            return $this;
        }
        if ($backtrace !== null) {
            $strippedBacktrace = [];
            foreach ($backtrace as $_bt) {
                if (!isset($_bt['class'])) {
                    $_bt['class'] = '';
                }
                if (!isset($_bt['function'])) {
                    $_bt['function'] = '';
                }
                if (
                    isset($_bt['file']) &&
                    !($_bt['class'] === 'NiceDB' && $_bt['function'] === '__call') &&
                    strpos($_bt['file'], 'class.core.NiceDB.php') === false
                ) {
                    $strippedBacktrace[] = [
                        'file'     => $_bt['file'],
                        'line'     => $_bt['line'],
                        'class'    => $_bt['class'],
                        'function' => $_bt['function']
                    ];
                }
            }
            $backtrace = $strippedBacktrace;
        }
        if ($res !== false) {
            while (($row = $res->fetchObject()) !== false) {
                if (!empty($row->table)) {
                    $tableData            = new stdClass();
                    $tableData->type      = $type;
                    $tableData->table     = $row->table;
                    $tableData->count     = 1;
                    $tableData->time      = $time;
                    $tableData->hash      = md5($stmt);
                    $tableData->statement = null;
                    $tableData->backtrace = null;
                    if ($this->debugLevel > 1) {
                        $tableData->statement = preg_replace('/\s\s+/', ' ', substr($stmt, 0, 500));
                        $tableData->backtrace = $backtrace;
                    }
                    Profiler::setSQLProfile($tableData);
                } elseif ($this->debugLevel > 1 && isset($row->Extra)) {
                    $tableData            = new stdClass();
                    $tableData->type      = $type;
                    $tableData->message   = $row->Extra;
                    $tableData->statement = preg_replace('/\s\s+/', ' ', $stmt);
                    $tableData->backtrace = $backtrace;
                    Profiler::setSQLError($tableData);
                }
            }
        }

        return $this;
    }

    /**
     * close db connection
     *
     * @return bool
     */
    public function close()
    {
        $this->pdo = null;

        return true;
    }

    /**
     * check if connected
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->isConnected;
    }

    /**
     * get server version information
     *
     * @return string
     */
    public function getServerInfo()
    {
        return $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * get server stats
     *
     * @return string
     */
    public function getServerStats()
    {
        return $this->pdo->getAttribute(PDO::ATTR_SERVER_INFO);
    }

    /**
     * get db object
     *
     * @return PDO
     */
    public function DB()
    {
        return $this->pdo;
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * insert row into db
     *
     * @access public
     * @param string   $tableName - table name
     * @param object   $object - object to insert
     * @param int|bool $echo - true -> print statement
     * @param bool     $bExecuteHook - true -> execute corresponding hook
     * @return int - 0 if fails, PrimaryKeyValue if successful
     */
    public function insertRow($tableName, $object, $echo = false, $bExecuteHook = false)
    {
        $start   = ($this->debug === true || $this->collectData === true)
            ? microtime(true)
            : 0;
        $arr     = get_object_vars($object);
        $keys    = []; //column names
        $values  = []; //column values - either sql statement like "now()" or prepared like ":my-var-name"
        $assigns = []; //assignments from prepared var name to values, will be inserted in ->prepare()

        if (!is_array($arr)) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog('insertRow: Objekt enthaelt nichts! - Tablename:' . $tableName);
            }

            return 0;
        }
        foreach ($arr as $_key => $_val) {
            $keys[] = $_key;
            if ($_val === '_DBNULL_') {
                $_val = null;
            } elseif ($_val === null) {
                $_val = '';
            }
            if (strtolower($_val) === 'now()') {
                $values[] = $_val;
            } else {
                $values[]             = ':' . $_key;
                $assigns[':' . $_key] = $_val;
            }
        }
        $stmt = "INSERT INTO " . $tableName . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";
        if ($echo) {
            echo $stmt;
        }
        try {
            $s   = $this->pdo->prepare($stmt);
            $res = $s->execute($assigns);
        } catch (PDOException $e) {
            if (defined('NICEDB_EXCEPTION_ECHO') && NICEDB_EXCEPTION_ECHO === true) {
                Shop::dbg($stmt, false, 'NiceDB exception when inserting row: ');
                Shop::dbg($assigns, false, 'Bound params:');
                Shop::dbg($e->getMessage(), false);
            }
            if (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === true) {
                Shop::dbg(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), false, 'Backtrace:');
            }

            return 0;
        }

        if ($bExecuteHook) {
            executeHook(HOOK_NICEDB_CLASS_INSERTROW, [
                'mysqlerrno' => $this->pdo->errorCode(),
                'statement'  => $stmt
            ]);
        }

        if (!$res) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog(
                    $stmt . "\n" .
                    $this->pdo->errorCode() . ': ' . $this->pdo->errorInfo() .
                    "\n\nBacktrace:" . print_r(debug_backtrace(), 1)
                );
            }
            if ($this->debug === true || $this->collectData === true) {
                $end       = microtime(true);
                $backtrace = null;
                if ($this->debugLevel > 2) {
                    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                }
                $arr = get_object_vars($object);
                if (!is_array($arr)) {
                    if ($this->logErrors && $this->logfileName) {
                        $this->writeLog('insertRow: Objekt enthaelt nichts! - Tablename:' . $tableName);
                    }

                    return 0;
                }
                $columns  = '(';
                $values   = '(';
                $keys     = array_keys($arr);
                $keyCount = count($keys);
                for ($i = 0; $i < $keyCount; $i++) {
                    if ($i === (count($keys) - 1)) {
                        $columns .= $keys[$i] . ') values';
                        if ($object->$keys[$i] === '_DBNULL_') {
                            $values .= 'null' . ')';
                        } elseif ($object->$keys[$i] === 'now()') {
                            $values .= $object->$keys[$i] . ')';
                        } else {
                            $values .= '' . $this->pdoEscape($object->$keys[$i]) . ')';
                        }
                    } else {
                        $columns .= $keys[$i] . ', ';
                        if ($object->$keys[$i] === '_DBNULL_') {
                            $values .= 'null' . ', ';
                        } elseif ($object->$keys[$i] === 'now()') {
                            $values .= $object->$keys[$i] . ', ';
                        } else {
                            $values .= '' . $this->pdoEscape($object->$keys[$i]) . ', ';
                        }
                    }
                }
                $stmt = "INSERT INTO $tableName $columns $values";
                $this->analyzeQuery('insert', $stmt, $end - $start, $backtrace);
            }

            return 0;
        }
        $id = $this->pdo->lastInsertId();
        if (($this->debug === true || $this->collectData === true) && strpos($tableName, 'tprofiler') !== 0) {
            $end       = microtime(true);
            $backtrace = null;
            if ($this->debugLevel > 2) {
                $backtrace = debug_backtrace();
            }
            $arr = get_object_vars($object);
            if (!is_array($arr)) {
                if ($this->logErrors && $this->logfileName) {
                    $this->writeLog('insertRow: Objekt enthaelt nichts! - Tablename:' . $tableName);
                }

                return 0;
            }
            $columns  = '(';
            $values   = '(';
            $keys     = array_keys($arr);
            $keyCount = count($keys);
            for ($i = 0; $i < $keyCount; $i++) {
                $property = $keys[$i];
                if ($i === (count($keys) - 1)) {
                    $columns .= $property . ') values';
                    if ($object->$property === '_DBNULL_') {
                        $values .= 'null' . ')';
                    } elseif ($object->$property === 'now()') {
                        $values .= $object->$property . ')';
                    } else {
                        $values .= '"' . $this->pdoEscape($object->$property) . '")';
                    }
                } else {
                    $columns .= $property . ', ';
                    if ($object->$property === '_DBNULL_') {
                        $values .= 'null' . ', ';
                    } elseif ($object->$property === 'now()') {
                        $values .= $object->$property . ', ';
                    } else {
                        $values .= '"' . $this->pdoEscape($object->$property) . '", ';
                    }
                }
            }
            $this->analyzeQuery('insert', "INSERT INTO $tableName $columns $values", $end - $start, $backtrace);
        }

        return $id > 0 ? $id : 1;
    }

    /**
     * update table row
     *
     * @access public
     * @param string           $tableName - table name
     * @param string|array     $keyname   - Name of Key which should be compared
     * @param int|string|array $keyvalue  - Value of Key which should be compared
     * @param object           $object    - object to update with
     * @param int|bool         $echo      - true -> print statement
     * @return int - -1 if fails, number of affected rows if successful
     */
    public function updateRow($tableName, $keyname, $keyvalue, $object, $echo = false)
    {
        $start   = ($this->debug === true || $this->collectData === true)
            ? microtime(true)
            : 0;
        $arr     = get_object_vars($object);
        $updates = []; //list of "<column name>=?" or "<column name>=now()" strings
        $assigns = []; //list of values to insert as param for ->prepare()
        if (!is_array($arr)) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog('updateRow: Objekt enthaelt nichts! - Tablename:' . $tableName);
            }

            return -1;
        }
        if (!$keyname || !$keyvalue) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog(
                    'updateRow: Kein keyname oder keyvalue! - ' .
                    'Tablename:' . $tableName .
                    ' Keyname: ' . $keyname .
                    ' - Keyvalue: ' . $keyvalue
                );
            }

            return -1;
        }
        foreach ($arr as $_key => $_val) {
            if ($_val === '_DBNULL_') {
                $_val = null;
            } elseif ($_val === null) {
                $_val = '';
            }
            if (strtolower($_val) === 'now()') {
                $updates[] = $_key . '=' . $_val;
            } else {
                $updates[] = $_key . '=?';
                $assigns[] = $_val;
            }
        }
        if (is_array($keyname) && is_array($keyvalue)) {
            if (count($keyname) !== count($keyvalue)) {
                if ($this->logErrors && $this->logfileName) {
                    $this->writeLog(
                        'updateRow: ' .
                        'Anzahl an Schluesseln passt nicht zu Anzahl an Werten - ' .
                        'Tablename:' . $tableName
                    );
                }

                return -1;
            }
            $keynamePrepared = array_map(function ($_v) {
                return $_v . '=?';
            }, $keyname);
            $where   = ' WHERE ' . implode(' AND ', $keynamePrepared);
            foreach ($keyvalue as $_v) {
                $assigns[] = $_v;
            }
        } else {
            $assigns[] = $keyvalue;
            $where     = ' WHERE ' . $keyname . '=?';
        }
        $stmt = 'UPDATE ' . $tableName . ' SET ' . implode(',', $updates) . $where;
        if ($echo) {
            echo $stmt;
        }

        try {
            $s   = $this->pdo->prepare($stmt);
            $res = $s->execute($assigns);
        } catch (PDOException $e) {
            if (defined('NICEDB_EXCEPTION_ECHO') && NICEDB_EXCEPTION_ECHO === true) {
                Shop::dbg($stmt, false, 'NiceDB exception when updating row: ');
                Shop::dbg($assigns, false, 'Bound params:');
                Shop::dbg($e->getMessage(), false);
            }
            if (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === true) {
                Shop::dbg(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), false, 'Backtrace:');
            }

            return -1;
        }
        if (!$res) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog($stmt . "\n" . $this->pdo->errorCode() . ": " . $this->pdo->errorInfo());
            }
            $ret = -1;
        } else {
            $ret = $s->rowCount();
        }

        if (($this->debug === true || $this->collectData === true) && strpos($tableName, 'tprofiler') !== 0) {
            $end       = microtime(true);
            $backtrace = null;
            if ($this->debugLevel > 2) {
                $backtrace = debug_backtrace();
            }
            $arr     = get_object_vars($object);
            $updates = [];
            foreach ($arr as $_key => $_val) {
                if ($_val === '_DBNULL_') {
                    $_val = null;
                } elseif ($_val === null) {
                    $_val = '';
                }
                $updates[] = $_key . '="' . $_val . '"';
            }
            if (is_array($keyname) && is_array($keyvalue)) {
                $combined = [];
                foreach ($keyname as $i => $key) {
                    $combined[] = $key . '=' . $keyvalue[$i];
                }
                $where = ' WHERE ' . implode(' AND ', $combined);
            } else {
                $where = ' WHERE ' . $keyname . '=' . $keyvalue;
            }
            $stmt = 'UPDATE ' . $tableName . ' SET ' . implode(',', $updates) . $where;
            $this->analyzeQuery('update', $stmt, $end - $start, $backtrace);
        }

        return $ret;
    }

    /**
     * selects all (*) values in a single row from a table - gives just one row back!
     *
     * @access public
     * @param string           $tableName - Tabellenname
     * @param string|array     $keyname - Name of Key which should be compared
     * @param string|int|array $keyvalue - Value of Key which should be compared
     * @param string|null      $keyname1 - Name of Key which should be compared
     * @param string|int       $keyvalue1 - Value of Key which should be compared
     * @param string|null      $keyname2 - Name of Key which should be compared
     * @param string|int       $keyvalue2 - Value of Key which should be compared
     * @param bool             $echo - true -> print statement
     * @param string           $select - the key to select
     * @return null|object - null if fails, resultObject if successful
     */
    public function selectSingleRow(
        $tableName,
        $keyname,
        $keyvalue,
        $keyname1 = null,
        $keyvalue1 = null,
        $keyname2 = null,
        $keyvalue2 = null,
        $echo = false,
        $select = '*'
    )
    {
        $start   = ($this->debug === true || $this->collectData === true)
            ? microtime(true)
            : 0;
        $keys    = is_array($keyname) ? $keyname : [$keyname, $keyname1, $keyname2];
        $values  = is_array($keyvalue) ? $keyvalue : [$keyvalue, $keyvalue1, $keyvalue2];
        $assigns = [];
        $i       = 0;
        foreach ($keys as &$_key) {
            if ($_key !== null) {
                $_key .= '=?';
                $assigns[] = $values[$i];
            } else {
                unset($keys[$i]);
            }
            ++$i;
        }
        unset($_key);
        $stmt = 'SELECT ' . $select .
            ' FROM ' . $tableName .
            ((count($keys) > 0)
                ? (' WHERE ' . implode(' AND ', $keys))
                : ''
            );
        if ($echo) {
            echo $stmt;
        }
        try {
            $s   = $this->pdo->prepare($stmt);
            $res = $s->execute($assigns);
        } catch (PDOException $e) {
            if (defined('NICEDB_EXCEPTION_ECHO') && NICEDB_EXCEPTION_ECHO === true) {
                Shop::dbg($stmt, false, 'NiceDB exception when selecting row: ');
                Shop::dbg($assigns, false, 'Bound params:');
                Shop::dbg($e->getMessage(), false);
            }
            if (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === true) {
                Shop::dbg(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), false, 'Backtrace:');
            }

            return null;
        }
        if (!$res) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog($stmt . "\n" . $this->pdo->errorCode() . ': ' . $this->pdo->errorInfo());
            }

            return null;
        }
        $ret = $s->fetchObject();
        if ($this->debug === true || $this->collectData === true) {
            $end       = microtime(true);
            $backtrace = null;
            if ($this->debugLevel > 2) {
                $backtrace = debug_backtrace();
            }
            if ($this->debug === true || $this->collectData === true) {
                $start = microtime(true);
            }
            $keys    = is_array($keyname) ? $keyname : [$keyname, $keyname1, $keyname2];
            $values  = is_array($keyvalue) ? $keyvalue : [$keyvalue, $keyvalue1, $keyvalue2];
            $i       = 0;
            foreach ($keys as &$k) {
                if ($k !== null) {
                    $k .= '=';
                    if (is_string($values[$i])) {
                        $k .= '\'' . $values[$i] . '\'';
                    } else {
                        $k .= $values[$i];
                    }
                } else {
                    unset($keys[$i]);
                }
                ++$i;
            }
            unset($k);
            $stmt = 'SELECT ' . $select .
                ' FROM ' . $tableName .
                ((count($keys) > 0)
                    ? (' WHERE ' . implode(' AND ', $keys))
                    : ''
                );
            $this->analyzeQuery('select', $stmt, $end - $start, $backtrace);
        }

        return $ret !== false ? $ret : null;
    }

    /**
     * @param string       $tableName
     * @param string|array $keys
     * @param string|array $values
     * @param string       $select
     * @param string       $orderBy
     * @param string       $limit
     * @return array
     * @throws InvalidArgumentException
     */
    public function selectArray($tableName, $keys, $values, $select = '*', $orderBy = '', $limit = '')
    {
        $keys         = is_array($keys) ? $keys : [$keys];
        $values       = is_array($values) ? $values : [$values];
        $kv           = [];
        if (count($keys) !== count($values)) {
            throw new InvalidArgumentException('Number of keys must be equal to number of given keys. Got ' .
                count($keys) . ' key(s) and ' . count($values) . ' value(s).');
        }
        foreach ($keys as $_key) {
            $kv[] = $_key . '=:' . $_key;
        }
        $stmt = 'SELECT ' . $select . ' FROM ' . $tableName .
            ((count($keys) > 0) ?
                (' WHERE ' . implode(' AND ', $kv)) :
                ''
            ) .
            ((!empty($orderBy)) ? (' ORDER BY ' . $orderBy) : '') .
            ((!empty($limit)) ? (' LIMIT ' . $limit) : '');

        return $this->executeQueryPrepared($stmt, array_combine($keys, $values), 2);
    }

    /**
     * executes query and returns misc data
     *
     * @access public
     * @param string   $stmt - Statement to be executed
     * @param int      $return - what should be returned.
     * 1  - single fetched object
     * 2  - array of fetched objects
     * 3  - affected rows
     * 8  - fetched assoc array
     * 9  - array of fetched assoc arrays
     * 10 - result of querysingle
     * @param int|bool $echo print current stmt
     * @param bool     $bExecuteHook should function executeHook be executed
     * @param callable $fnInfo statistic callback
     * @return array|object|int - 0 if fails, 1 if successful or LastInsertID if specified
     * @throws InvalidArgumentException
     */
    public function executeQuery($stmt, $return, $echo = false, $bExecuteHook = false, $fnInfo = null)
    {
        return $this->_execute(0, $stmt, null, $return, $echo, $bExecuteHook, $fnInfo);
    }

    /**
     * executes query and returns misc data
     *
     * @access public
     * @param string   $stmt - Statement to be executed
     * @param array    $params - An array of values with as many elements as there are bound parameters in the SQL statement being executed
     * @param int      $return - what should be returned.
     * 1  - single fetched object
     * 2  - array of fetched objects
     * 3  - affected rows
     * 8  - fetched assoc array
     * 9  - array of fetched assoc arrays
     * 10 - result of querysingle
     * @param int|bool $echo print current stmt
     * @param bool     $bExecuteHook should function executeHook be executed
     * @param callable $fnInfo statistic callback
     * @return array|object|int - 0 if fails, 1 if successful or LastInsertID if specified
     * @throws InvalidArgumentException
     */
    public function executeQueryPrepared($stmt, array $params, $return, $echo = false, $bExecuteHook = false, $fnInfo = null)
    {
        return $this->_execute(1, $stmt, $params, $return, $echo, $bExecuteHook, $fnInfo);
    }

    /**
     * executes query and returns misc data
     *
     * @access protected
     * @param int           $type - Type [0 => query, 1 => prepared]
     * @param string        $stmt - Statement to be executed
     * @param array         $params - An array of values with as many elements as there are bound parameters in the SQL statement being executed
     * @param int           $return - what should be returned.
     * @param int|bool      $echo print current stmt
     * @param bool          $bExecuteHook should function executeHook be executed
     * @param null|callable $fnInfo
     * 1  - single fetched object
     * 2  - array of fetched objects
     * 3  - affected rows
     * 7  - last inserted id
     * 8  - fetched assoc array
     * 9  - array of fetched assoc arrays
     * 10 - result of querysingle
     * @return array|object|int - 0 if fails, 1 if successful or LastInsertID if specified
     * @throws InvalidArgumentException
     */
    protected function _execute($type, $stmt, $params, $return, $echo = false, $bExecuteHook = false, $fnInfo = null)
    {
        $type   = (int)$type;
        $return = (int)$return;
        $params = is_array($params) ? $params : [];
        if (!in_array($type, [0, 1], true)) {
            throw new InvalidArgumentException("\$type parameter must be 0 or 1, given '{$type}'");
        }

        if ($return <= 0 || $return > 11) {
            throw new InvalidArgumentException("\$return parameter must be between 1 - 11, given '{$return}'");
        }

        if ($fnInfo !== null && !is_callable($fnInfo)) {
            $t = gettype($fnInfo);
            throw new InvalidArgumentException("\$fnInfo parameter is not callable, given type '{$t}'");
        }

        if ($echo) {
            echo $stmt;
        }

        $start = ($this->debug === true || $this->collectData === true || $bExecuteHook === true || $fnInfo !== null)
            ? microtime(true)
            : 0;
        try {
            if ($type === 0) {
                $res = $this->pdo->query($stmt);
            } else {
                $res  = $this->pdo->prepare($stmt);
                $stmt = $this->readableQuery($stmt, $params);
                foreach ($params as $k => $v) {
                    $this->_bind($res, $k, $v);
                }
                if ($res->execute() === false) {
                    return 0;
                }
            }
        } catch (PDOException $e) {
            if (defined('NICEDB_EXCEPTION_ECHO') && NICEDB_EXCEPTION_ECHO === true) {
                Shop::dbg($stmt, false, 'Exception when trying to execute query: ');
                Shop::dbg($e->getMessage(), false, 'Exception:');
            }

            if (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === true) {
                Shop::dbg(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), false, 'Backtrace:');
            }

            if ($this->transactionCount > 0) {
                throw $e;
            }

            return 0;
        }

        if ($bExecuteHook || $fnInfo !== null) {
            $info = [
                'mysqlerrno' => $this->pdo->errorCode(),
                'statement'  => $stmt,
                'time'       => microtime(true) - $start
            ];

            if ($bExecuteHook) {
                executeHook(HOOK_NICEDB_CLASS_EXECUTEQUERY, $info);
            }

            if ($fnInfo !== null) {
                $fnInfo($info);
            }
        }

        if (!$res) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog(
                    $stmt . "\n" .
                    $this->pdo->errorCode() . ': ' . $this->pdo->errorInfo() .
                    "\n\nBacktrace: " . print_r(debug_backtrace(), true)
                );
            }

            return 0;
        }

        switch ($return) {
            case 1:
                $ret = $res->fetchObject();
                break;
            case 2:
                $ret = [];
                while (($row = $res->fetchObject()) !== false) {
                    $ret[] = $row;
                }
                break;
            case 3:
                $ret = $res->rowCount();
                break;
            case 7:
                $id = $this->pdo->lastInsertId();
                $ret = ($id > 0) ? $id : 1;
                break;
            case 8:
                $ret = $res->fetchAll(PDO::FETCH_NAMED);
                if (is_array($ret) && isset($ret[0])) {
                    $ret = $ret[0];
                } else {
                    $ret = null;
                }
                break;
            case 9:
                $ret = $res->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 10:
                $ret = $res;
                break;
            case 11:
                $ret = $res->fetchAll(PDO::FETCH_BOTH);
                break;
            default:
                $ret = true;
                break;
        }

        if ($this->debug === true || $this->collectData === true) {
            // @todo
            if ($type === 0) {
                $backtrace = null;
                if ($this->debugLevel > 2) {
                    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                }
                $this->analyzeQuery('_execute', $stmt, microtime(true) - $start, $backtrace);
            }
        }

        return $ret;
    }

    /**
     * delete row from table
     *
     * @access public
     * @param string           $tableName - table name
     * @param string|array     $keyname - Name of Key which should be compared
     * @param string|int|array $keyvalue - Value of Key which should be compared
     * @param bool|int         $echo - true -> print statement
     * @return int - -1 if fails, #affectedRows if successful
     */
    public function deleteRow($tableName, $keyname, $keyvalue, $echo = false)
    {
        $start = 0;
        if ($this->debug === true || $this->collectData === true) {
            $start = microtime(true);
        }
        $assigns = [];
        if (is_array($keyname) && is_array($keyvalue)) {
            if (count($keyname) !== count($keyvalue)) {
                if ($this->logErrors && $this->logfileName) {
                    $this->writeLog('deleteRow: ' .
                        'Anzahl an Schluesseln passt nicht zu Anzahl an Werten - ' .
                        'Tablename:' . $tableName
                    );
                }

                return -1;
            }
            $keyname = array_map(function ($_v) {
                return $_v . '=?';
            }, $keyname);
            $where   = implode(' AND ', $keyname);
            foreach ($keyvalue as $_v) {
                $assigns[] = $_v;
            }
        } else {
            $assigns[] = $keyvalue;
            $where     = $keyname . '=?';
        }

        $stmt = 'DELETE FROM ' . $tableName . ' WHERE ' . $where;

        if ($echo) {
            echo $stmt;
        }
        try {
            $s   = $this->pdo->prepare($stmt);
            $res = $s->execute($assigns);
        } catch (PDOException $e) {
            if (defined('NICEDB_EXCEPTION_ECHO') && NICEDB_EXCEPTION_ECHO === true) {
                Shop::dbg($stmt, false, 'NiceDB exception when deleting row: ');
                Shop::dbg($e->getMessage(), false);
            }
            if (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === true) {
                Shop::dbg(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), false, 'Backtrace:');
            }

            return -1;
        }
        if (!$res) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog($stmt . "\n" . $this->pdo->errorCode() . ': ' . $this->pdo->errorInfo());
            }

            return -1;
        }
        $ret = $s->rowCount();
        if ($this->debug === true || $this->collectData === true) {
            $end       = microtime(true);
            $backtrace = null;
            if ($this->debugLevel > 2) {
                $backtrace = debug_backtrace();
            }
            if (!is_int($keyvalue)) {
                $keyvalue = $this->pdoEscape($keyvalue);
            }
            $stmt = 'DELETE FROM ' . $tableName . ' WHERE ' . $keyname . '=' . $keyvalue;
            $this->analyzeQuery('delete', $stmt, $end - $start, $backtrace);
        }

        return $ret;
    }

    /**
     * executes a query and gives back the result
     *
     * @access public
     * @param string $stmt - Statement to be executed
     * @return PDOStatement|int
     */
    public function executeExQuery($stmt)
    {
        try {
            $res = $this->pdo->query($stmt);
        } catch (PDOException $e) {
            if (defined('NICEDB_EXCEPTION_ECHO') && NICEDB_EXCEPTION_ECHO === true) {
                Shop::dbg($stmt, false, 'NiceDB exception when executing: ');
                Shop::dbg($e->getMessage(), false);
            }
            if (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === true) {
                Shop::dbg(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), false, 'Backtrace:');
            }

            return 0;
        }
        if (!$res) {
            if ($this->logErrors && $this->logfileName) {
                $this->writeLog($stmt . "\n" . $this->pdo->errorCode() . ': ' . $this->pdo->errorInfo());
            }

            return 0;
        }

        return $res;
    }

    /**
     * @param mixed $res
     * @return bool
     * @deprecated since 4.0
     */
    protected function isMysqliResult($res)
    {
        return is_object($res) && get_class($res) === 'mysqli_result';
    }

    /**
     * @param mixed $res
     * @return bool
     */
    protected function isPdoResult($res)
    {
        return is_object($res) && get_class($res) === 'PDOStatement';
    }

    /**
     * Quotes a string with outer quotes for use in a query.
     *
     * @param string|bool $string
     * @return string
     */
    public function quote($string)
    {
        if (is_bool($string)) {
            $string = $string ?: '0';
        }

        return $this->pdo->quote($string);
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string $string
     * @return string
     */
    public function escape($string)
    {
        $quotedString = $this->quote($string);

        // remove outer single quotes
        return preg_replace('/^\'(.*)\'$/', '$1', $quotedString);
    }

    /**
     * logger
     *
     * @param string $entry - entry to log
     * @return $this
     */
    public function writeLog($entry)
    {
        $logfile = fopen($this->logfileName, 'a');
        fwrite(
            $logfile,
            "\n[" . date('m.d.y H:i:s') . ' ' . microtime() . '] ' .
            $_SERVER['SCRIPT_NAME'] . "\n" . $entry
        );
        fclose($logfile);

        return $this;
    }

    /**
     * @return mixed
     */
    public function _getErrorCode()
    {
        $errorCode = $this->pdo->errorCode();

        return ($errorCode !== '00000') ? $errorCode : 0;
    }

    /**
     * @return mixed
     */
    public function _getError()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * @return string
     */
    public function _getErrorMessage()
    {
        $error = $this->_getError();
        if (is_array($error) && isset($error[2])) {
            return is_string($error[2]) ? $error[2] : '';
        }

        return '';
    }

    /**
     * @return boolean
     */
    public function beginTransaction()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($this->transactionCount++ <= 0) {
            return $this->pdo->beginTransaction();
        }

        return $this->transactionCount >= 0;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        if ($this->transactionCount-- === 1) {
            return $this->pdo->commit();
        }

        if (!defined('NICEDB_EXCEPTION_BACKTRACE') ||
            (defined('NICEDB_EXCEPTION_BACKTRACE') && NICEDB_EXCEPTION_BACKTRACE === false)) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        }

        return $this->transactionCount <= 0;
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        $result = false;
        if ($this->transactionCount >= 0) {
            $result = $this->pdo->rollBack();
        }
        $this->transactionCount = 0;

        return $result;
    }

    /**
     * @param PDOStatement $stmt
     * @param string       $parameter
     * @param mixed        $value
     * @param int|null     $type
     */
    protected function _bind(PDOStatement $stmt, $parameter, $value, $type = null)
    {
        $parameter = $this->_bindName($parameter);

        if ($type === null) {
            switch (true) {
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }
        }

        $stmt->bindValue($parameter, $value, $type);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function _bindName($name)
    {
        if (is_string($name)) {
            return ':' . ltrim($name, ':');
        }

        return $name;
    }

    /**
     * @param string $query
     * @param array  $params
     * @return string
     */
    public function readableQuery($query, $params)
    {
        $keys   = [];
        $values = [];

        foreach ($params as $key => $value) {
            $key    = is_string($key)
                ? $this->_bindName($key)
                : '[?]';
            $keys[] = '/' . $key . '/';
            $value  = is_int($value)
                ? (int)$value
                : $this->quote($value);

            $values[] = $value;
        }

        return preg_replace($keys, $values, $query, 1, $count);
    }
}
