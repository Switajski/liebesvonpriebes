<?php
/**
 * helper for generating debug output
 *
 * @package     jtl_debug
 * @createdAt   18.11.14
 * @author      Felix Moche <felix.moche@jtl-software.com>
 */

/**
 * Class jtl_debug_util
 */
class jtl_debug_util
{
    /**
     * @var array
     */
    private $userDebug = [];

    /**
     * custom debug output
     *
     * @param mixed  $var
     * @param string $name
     * @return $this
     */
    public function dump($var, $name = 'dumped_var')
    {
        $this->userDebug[$name] = $var;

        return $this;
    }

    /**
     * @return array
     */
    public function getUserDebug()
    {
        return $this->userDebug;
    }
}

/**
 * Class jtl_debug
 */
class jtl_debug
{
    /**
     * collection of debug sections
     *
     * @var array
     */
    private $sections = [];

    /**
     * processing times
     *
     * @var array
     */
    private $timings = [];

    /**
     * plugin object
     *
     * @var Plugin
     */
    private $oPlugin;

    /**
     * customer user debugger class
     *
     * @var jtl_debug_util
     */
    private $userDebugger;

    /**
     * list of php errors
     *
     * @var array
     */
    private $errors = [];

    /**
     * additional data to debug
     *
     * @var array
     */
    protected $additional = [];

    /**
     * plugin instance
     *
     * @var jtl_debug|null
     */
    private static $_instance;

    /**
     * initialize plugin
     *
     * @param Plugin $oPlugin - the plugin object for initialization
     */
    private function __construct(Plugin $oPlugin)
    {
        $this->oPlugin   = $oPlugin;
        self::$_instance = $this;
    }

    /**
     * singleton
     *
     * @param Plugin $oPlugin - the plugin object for initialization
     * @return jtl_debug
     */
    public static function getInstance(Plugin $oPlugin)
    {
        return (self::$_instance === null) ? new self($oPlugin) : self::$_instance;
    }

    /**
     * check if plugin output is activated
     *
     * @return bool
     */
    public function getIsActivated()
    {
        return (($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_enable'] === 'Y') && //debug has to be enabled
            (($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_on_query_string'] === 'N') || //and always be active
                ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_on_query_string'] === 'Y' &&
                    isset($_GET[$this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_query_string']])) || //or get parameter has to be set
                (isset($_COOKIE['JTL_DEBUG_ENABLED']) && $_COOKIE['JTL_DEBUG_ENABLED'] === '1') //or cookie is set
            ));
    }

    /**
     * custom error handler
     *
     * @param int    $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int    $errLine
     * @return bool
     */
    public function jtlDebugErrorHandler($errNo, $errStr, $errFile, $errLine)
    {
        switch ($errNo) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $errors = 'Notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $errors = 'Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $errors = 'Fatal Error';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $errors = 'Deprecated';
                break;
            default:
                $errors = 'Unknown Error';
                break;
        }
        $msg = [
            'NO'   => $errNo,
            'MSG'  => $errStr,
            'FILE' => $errFile,
            'LINE' => $errLine,
        ];

        $this->errors[$errFile][$errors][$errStr] = $msg;

        return true;
    }

    /**
     * set custom error handler
     *
     * @return $this
     */
    public function setErrorHandler()
    {
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_errors'] === 'Y') {
            set_error_handler([$this, 'jtlDebugErrorHandler'], E_ALL);
        }

        return $this;
    }

    /**
     * ensures that jtl_debug hooks are executed last to get all the information needed from other plugins
     *
     * @return $this
     */
    public function makeLast()
    {
        $hookList = method_exists('Plugin', 'getHookList')
            ? Plugin::getHookList()
            : $GLOBALS['oPluginHookListe_arr'];
        $foundAt  = null;
        foreach ($hookList as $_idx => $_hook) {
            if (isset($_hook->cDateiname) && $_hook->cDateiname === '140_jtl_debug.php') {
                $foundAt = $_idx;
                break;
            }
        }
        if ($foundAt !== null) {
            $jtlHook = $hookList[$foundAt];
            unset($hookList[$foundAt]);
            $hookList[] = $jtlHook;
            if (method_exists('Plugin', 'setHookList')) {
                Plugin::setHookList($hookList);
            } else {
                $GLOBALS['oPluginHookListe_arr'] = $hookList;
            }
        }

        return $this;
    }

    /**
     * initialize custom debugger
     *
     * @return $this
     */
    public function initUserDebugger()
    {
        $this->userDebugger = new jtl_debug_util();
        $GLOBALS['dbg']     = $this->userDebugger;

        return $this;
    }

    /**
     * @return jtl_debug_util
     */
    public function getUserDebugger()
    {
        return $this->userDebugger;
    }

    /**
     * check if array is associative
     *
     * @param array $array
     * @return bool
     */
    private function is_assoc(array $array)
    {
        foreach (array_keys($array) as $k => $v) {
            if ($k !== $v) {
                return true;
            }
        }

        return false;
    }

    /**
     * gather output from phpinfo()
     *
     * @return array
     */
    private function getPhpInfo()
    {
        ob_start();
        phpinfo();
        $phpInfo = ['version' => PHP_VERSION, 'phpinfo' => []];
        if (preg_match_all(
            '#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?>' .
            '<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>' .
            '(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
            ob_get_clean(),
            $matches, PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                if (strlen($match[1])) {
                    $phpInfo[$match[1]] = [];
                } elseif (isset($match[3])) {
                    if ($match[2] !== 'Directive') {
                        $keys                           = array_keys($phpInfo);
                        $phpInfo[end($keys)][$match[2]] = isset($match[4])
                            ? [
                                'global' => strip_tags($match[3]),
                                'local'  => strip_tags($match[4])
                            ]
                            : strip_tags($match[3]);
                    }
                }
            }
        }

        return $phpInfo;
    }

    /**
     * gather active hooks information
     *
     * @return array
     */
    private function getHooks()
    {
        $hooks = [];
        //get some additional information about hooking plugins
        foreach (Plugin::getHookList() as $hookId => $hookArray) {
            foreach ($hookArray as $hookObject) {
                if (isset($hookObject->kPlugin, $hookObject->cDateiname)) {
                    $oPluginTmp                              = new Plugin($hookObject->kPlugin);
                    $tmpPlugin                               = new stdClass();
                    $tmpPlugin->ID                           = $hookObject->kPlugin;
                    $tmpPlugin->Version                      = $hookObject->nVersion;
                    $tmpPlugin->Path                         = PFAD_ROOT . PFAD_PLUGIN . $oPluginTmp->cVerzeichnis . '/' .
                        PFAD_PLUGIN_VERSION . $oPluginTmp->nVersion . '/' . PFAD_PLUGIN_FRONTEND;
                    $tmpPlugin->Filename                     = $hookObject->cDateiname;
                    $tmpPlugin->Author                       = $oPluginTmp->cAutor;
                    $tmpPlugin->CreatedDate                  = $oPluginTmp->dErstellt;
                    $tmpPlugin->InstallDate                  = $oPluginTmp->dInstalliert;
                    $tmpPlugin->Description                  = $oPluginTmp->cBeschreibung;
                    $hooks[$hookId][$hookObject->cDateiname] = $tmpPlugin;
                }
            }
        }

        return $hooks;
    }

    /**
     * store json in session
     *
     * @param string $json
     * @return $this
     */
    private function storeOutputAjax($json)
    {
        if (Shop::Cache()->set('jtl_debug_json', $json, [CACHING_GROUP_PLUGIN, 'jtl_debug']) !== false) {
            return $this;
        }
        $_SESSION['jtl-debug-session'] = $json;

        return $this;
    }

    /**
     * output json for ajax call
     */
    public static function getOutputAjax()
    {
        $json = Shop::Cache()->get('jtl_debug_json');
        Shop::Cache()->flush('jtl_debug_json');
        if ($json === false && isset($_SESSION['jtl-debug-session'])) {
            $json = $_SESSION['jtl-debug-session'];
        }
        if ($json === false) {
            die('x');
        }
        ob_end_clean();
        header('Content-type: application/json; charset=utf-8');
        unset($_SESSION['jtl-debug-session']);
        die($json);
    }

    /**
     * transform output to an object that is easy to consume by the javascript frontend
     *
     * @param mixed  $node
     * @param string $key
     * @param null   $parent
     * @param bool   $showPath
     * @return array
     */
    private function transform($node, $key, $parent = null, $showPath = false)
    {
        $key = utf8_encode($key);
        $res = [
            'type' => gettype($node),
            'key'  => $key,
        ];
        //test for assoc array
        if ($res['type'] === 'array' && $this->is_assoc($node)) {
            $res['type'] = 'assoc_array';
        }
        //we don't care what numeric type it is, we just want to know if it is a number
        if (is_numeric($node)) {
            $res['type'] = 'number';
        }
        //build path
        if ($showPath === true && isset($parent, $parent['path'], $key)) {
            if ($parent['path'] === '') {
                $res['path'] = '$' . $key;
            } elseif ($parent['type'] === 'array') {
                $res['path'] = $parent['path'] . '[' . $key . ']';
            } elseif ($parent['type'] === 'assoc_array') {
                $res['path'] = $parent['path'] . '.' . $key;
            } elseif ($parent['type'] === 'object') {
                $res['path'] = $parent['path'] . '->' . $key;
            } else {
                $res['path'] = '$' . $key;
            }
        } else {
            $res['path'] = '';
        }
        if ($res['type'] === 'object' || $res['type'] === 'array' || $res['type'] === 'assoc_array') {
            //build children array recursively
            $res['children'] = [];
            $res['length']   = 0;
            foreach ($node as $cKey => $value) {
                $res['children'][utf8_encode($cKey)] = $this->transform($value, $cKey, $res, $showPath);
                ++$res['length'];
            }
        } else {
            //simple data type
            $res['value'] = is_string($node) ? utf8_encode($node) : $node;
        }

        return $res;
    }

    /**
     * add a section
     *
     * @param array|object $input
     * @param string       $sectionName
     * @param bool         $showPath
     * @return $this
     */
    private function addSection($input, $sectionName, $showPath = false)
    {
        $startTime                                = microtime(true);
        $this->sections[$sectionName]             = $this->transform($input, null, null, $showPath);
        $this->sections[$sectionName]['type']     = 'section';
        $this->sections[$sectionName]['name']     = $sectionName;
        $this->sections[$sectionName]['showPath'] = $showPath;
        $this->timings[$sectionName]              = microtime(true) - $startTime;

        return $this;
    }

    /**
     * return the sections as JSON object
     *
     * @return string
     */
    private function getSectionsJSON()
    {
        return json_encode($this->sections);
    }

    /**
     * @param array $array
     * @param int   $order
     * @return bool
     */
    private function ksortRecursive(&$array, $order = SORT_NATURAL) {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->ksortRecursive($value, $order);
            }
        }

        return ksort($array, $order);
    }

    /**
     * gather output
     *
     * @return $this
     */
    public function run()
    {
        //set cookie if option enabled
        if (!isset($_COOKIE['JTL_DEBUG_ENABLED']) &&
            $this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_save_cookie'] === 'Y' &&
            $this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_on_query_string'] === 'Y' &&
            isset($_GET[$this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_query_string']])
        ) {
            setcookie('JTL_DEBUG_ENABLED', '1');
        }
        $smarty = Shop::Smarty();
        $languageVars = $this->oPlugin->oPluginSprachvariableAssoc_arr;
        //add user debug output
        if (method_exists($this->userDebugger, 'getUserDebug') && count($this->userDebugger->getUserDebug()) > 0) {
            $this->addSection($this->userDebugger->getUserDebug(), $languageVars['section_user_debug']);
        }
        //add smarty variables
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_smarty_vars'] === 'Y') {
            //get template vars from smarty
            $assignedVars = $smarty->get_template_vars();
            //create smarty debug output
            $this->ksortRecursive($assignedVars, SORT_NATURAL | SORT_FLAG_CASE);
            $this->addSection($assignedVars, $languageVars['section_smarty_variables'], true);
            unset($assignedVars);
        }
        //add templates in use
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_loaded_templates'] === 'Y') {
            $templates = [];
            if (!empty($smarty->_debug->template_data)) {
                foreach ($smarty->_debug->template_data as $_idx) {
                    foreach ($_idx as $_tplID => $_data) {
                        if (!empty($_data['name'])) {
                            $_tplData                  = new stdClass();
                            $_tplData->compileTime     = $_data['compile_time'];
                            $_tplData->renderTime      = $_data['render_time'];
                            $_tplData->cacheTime       = $_data['cache_time'];
                            $_tplData->totalTime       = $_data['total_time'];
                            $templates[$_data['name']] = $_tplData;
                        }
                    }
                }
            }
            $this->addSection($templates, $languageVars['section_loaded_templates'] . ' (' . count($templates) . ')');
            unset($templates);
        }
        //add error log
        if (count($this->errors) > 0) {
            //remove duplicate errors
            $this->errors = array_map('unserialize', array_unique(array_map('serialize', $this->errors)));
            $errorCount   = 0;
            foreach ($this->errors as $errorFile) {
                foreach ($errorFile as $errorType) {
                    $errorCount += count($errorType);
                }
            }
            $this->addSection($this->errors, $languageVars['section_php_errors'] . ' (' . $errorCount . ')');
            unset($this->errors);
        }
        //add session output
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_session'] === 'Y') {
            //really make sure we don't get unwanted recursion
            unset($_SESSION['jtl-debug-session']);
            $this->addSection($_SESSION, '$_SESSION');
        }
        //add get parameters
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_get'] === 'Y') {
            $this->addSection($_GET, '$_GET');
        }
        //add post output
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_post'] === 'Y') {
            $this->addSection($_POST, '$_POST');
        }
        //add cookie output
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_cookie'] === 'Y') {
            $this->addSection($_COOKIE, '$_COOKIE');
        }
        //add phpinfo() - thanks to jon @ http://www.php.net/manual/en/function.phpinfo.php
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_php_info'] === 'Y') {
            $this->addSection($this->getPhpInfo(), 'phpinfo()');
        }
        //add registered hooks output
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_hooks'] === 'Y') {
            $hookCount = method_exists('Plugin', 'getHookList')
                ? count(Plugin::getHookList())
                : count($GLOBALS['oPluginHookListe_arr']);
            $this->addSection($this->getHooks(), $languageVars['section_registered_hooks'] . ' (' .  $hookCount . ')');
        }
        //add shop4 features debugging output
        $cacheOptions = Shop::Cache()->getOptions();
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_cache'] === 'Y' &&
            isset($cacheOptions['debug']) && $cacheOptions['debug'] === true &&
            $cacheOptions['debug_method'] === 'ssd') {
            $cacheOptions['mysql_pass'] = '******';
            if (is_string($cacheOptions['redis_pass'])) {
                $cacheOptions['redis_pass'] = '******';
            }
            $cacheDebug            = Profiler::getCurrentCacheProfile();
            $cacheDebug['options'] = $cacheOptions;
            $this->addSection($cacheDebug, 'Cache');
        }
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_nicedb_profiler'] === 'Y') {
            $this->addSection(Profiler::getCurrentSQLProfile(), 'NiceDB');
        }
        //add plugin profile, ordered by hook
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_plugin_profiler'] === 'Y') {
            $pluginDebug = [];
            foreach (Profiler::getCurrentPluginProfile() as $_profile) {
                if (!isset($pluginDebug[$_profile['hookID']])) {
                    $pluginDebug[$_profile['hookID']] = [];
                }
                $pluginDebug[$_profile['hookID']][] = $_profile;
            }
            $this->addSection($pluginDebug, 'Plugins');
        }
        //add mem usage output
        if ($this->oPlugin->oPluginEinstellungAssoc_arr['jtl_debug_show_mem_usage'] === 'Y') {
            $maxMem = number_format(memory_get_peak_usage(true) / 1024 / 1024, 2, ',', '');
            $this->addSection(null, 'Mem: ' . $maxMem . ' MB');
        }
        //add smarty debug timing for debugging of this plugin itself only
        //uncomment next line for internal time debugging (adds a new section with the transform speed of every section)
        //$this->addSection($this->timings, 'JTL Debug Timings');

        //add ajax session
        $this->storeOutputAjax($this->getSectionsJSON());
        //render template
        pq('body')->append($smarty->fetch($this->oPlugin->cFrontendPfad . 'template/jtl-debug.tpl'));

        return $this;
    }
}
