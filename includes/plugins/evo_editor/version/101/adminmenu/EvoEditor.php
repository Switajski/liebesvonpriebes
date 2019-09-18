<?php

/**
 * Class EvoEditor.
 */
class EvoEditor
{
    /**
     * @var EvoEditor
     */
    private static $instance;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $themesPath;

    /**
     * @var string
     */
    private $parentThemesPath;

    /**
     * @var string
     */
    private $jsPath;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $parentTemplate;


    private $userTheme = '';

    private $userTemplate;

    private $userData;

    /**
     * Constructor.
     */
    private function __construct()
    {
        global $oPlugin;
        if ($oPlugin === null || (int)$oPlugin->nStatus !== 2) {
            exit();
        }

        require_once realpath(__DIR__ . '/../../../') . '/vendor/autoload.php';
        require_once realpath(__DIR__ . '/.././../../../../') . '/config.JTL-Shop.ini.php';
        require_once realpath(__DIR__ . '/.././../../../../') . '/defines.php';

        $template             = Template::getInstance();
        $this->template       = $template->getFrontendTemplate();
        $this->parentTemplate = $template->getParent();
        if ($this->parentTemplate !== null) {
            $this->parentThemesPath = PFAD_ROOT . PFAD_TEMPLATES . $this->parentTemplate . '/themes/';
        }

        $this->path       = __DIR__;
        $this->url        = Shop::getURL() . '/' .
            PFAD_PLUGIN . $oPlugin->cVerzeichnis . '/' .
            PFAD_PLUGIN_VERSION . $oPlugin->nVersion . '/' . PFAD_PLUGIN_ADMINMENU;
        $this->themesPath = PFAD_ROOT . PFAD_TEMPLATES . $this->template . '/themes/';
        $this->jsPath     = PFAD_ROOT . PFAD_TEMPLATES . $this->template . '/js/';
    }

    /**
     * Returns class singleton instance.
     *
     * @return EvoEditor
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Shows editor in admin backend.
     */
    public function showEditor()
    {
        global $smarty;

        $smarty->assign('URL', $this->url)
               ->assign('themes', $this->getThemes())
               ->display($this->path . '/templates/editor.tpl');
    }

    /**
     * @param null|array $theme
     * @return array
     */
    public function getThemes($theme = null)
    {
        $themes = [];
        if (is_dir($this->themesPath) && $handle = opendir($this->themesPath)) {
            while (false !== ($file = readdir($handle))) {
                if (($file !== '.' && $file !== '..' && $file !== 'fonts' && $file !== 'base')
                    && ($theme === null || (is_array($theme) && in_array($file, $theme, true)))
                ) {
                    $themes[] = [
                        'template' => $this->template,
                        'theme'    => $file,
                    ];
                }
            }
            closedir($handle);
        }
        if ($this->parentThemesPath !== null
            && is_dir($this->parentThemesPath)
            && $handle = opendir($this->parentThemesPath)
        ) {
            while (false !== ($file = readdir($handle))) {
                if (($file !== '.' && $file !== '..' && $file !== 'fonts' && $file !== 'base')
                    && ($theme === null || (is_array($theme) && in_array($file, $theme, true)))
                ) {
                    $themes[] = [
                        'template' => $this->parentTemplate,
                        'theme'    => $file,
                    ];
                }
            }
            closedir($handle);
        }
        asort($themes);

        return $themes;
    }

    /**
     * Returns JSON data for called action.
     *
     * @param string $action
     */
    public function json($action)
    {
        try {
            $data = $this->call($action);
        } catch (\Exception $e) {
            $data = $this->msg('danger', $e->getMessage());
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * @return $this
     */
    private function setUserData()
    {
        $this->userTheme = isset($_REQUEST['theme'])
            ? basename($_REQUEST['theme'])
            : '';
        $this->userTemplate = isset($_REQUEST['template'])
            ? basename($_REQUEST['template'])
            : '';

        if (isset($_REQUEST['data'])) {
            $this->userData = new stdClass();
            $this->userData->file = '';
            $this->userData->content = '';
            $this->userData->name = '';
            if (isset($_REQUEST['data']['file'])) {
                $this->userData->file = strpos($_REQUEST['data']['file'], PFAD_ROOT) === 0
                    ? realpath($_REQUEST['data']['file'])
                    : (realpath($this->path . '/../../../') . '/less/' . basename($_REQUEST['data']['file']));
            }
            if (isset($_REQUEST['data']['name'])) {
                $this->userData->name = basename($_REQUEST['data']['name']);
            }
            if (isset($_REQUEST['data']['content'])) {
                $this->userData->content = $_REQUEST['data']['content'];
            }
        }

        return $this;
    }

    /**
     * @param string $action
     * @return array|mixed
     */
    public function call($action)
    {
        if (!Shop::isAdmin()) {
            return $this->msg('danger', 'Unauthorized');
        }
        if (method_exists($this, $action)) {
            return $this->setUserData()->$action();
        }

        return $this->msg('danger', 'Method not found');
    }

    /**
     * @return array
     */
    private function minify()
    {
        require_once __DIR__ . '/Compiler.php';
        $out = $this->jsPath . 'evo.min.js';

        if (file_exists($out)) {
            unlink($out);
        }

        $data  = '';
        $files = glob($this->jsPath . '*.js');

        foreach ($files as $file) {
            $data .= file_get_contents($file);
        }

        try {
            $min = JTL\Evo\Minify_JS_ClosureCompiler::minify($data);
            if (file_put_contents($out, $min) === false) {
                return $this->msg('danger', 'Fehler beim Speichern der Datei');
            }
            return $this->msg('success', 'Javascript wurde kompiliert');
        } catch (\Exception $e) {
            return $this->msg('danger', $e->getMessage());
        }
    }

    /**
     * Read themes current less files.
     *
     * @param string|null $theme
     * @return array|string -  json output
     */
    private function changeTheme($theme = null)
    {
        if ($this->userTheme === '') {
            return '';
        }
        $theme    = $theme === null ? $this->userTheme : $theme;
        $template = !empty($this->userTemplate) ? $this->userTemplate : null;
        if ($template !== null) {
            $this->themesPath = PFAD_ROOT . PFAD_TEMPLATES . $template . '/themes/';
        }
        if (is_writable($this->themesPath . $theme)) {
            $files   = [];
            $customs = [];

            if (($handle = opendir(realpath($this->themesPath . $theme . '/less'))) !== false) {
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '_assigns.less'
                        && $file !== 'theme.less.tmp.less'
                        && strpos($file, '.') > 0
                        && strpos($file, '.less.original') === false
                    ) {
                        $files[]   = [
                            'file' => $file,
                            'path' => $this->themesPath . $theme . '/less/' . $file,
                        ];
                        $customs[] = [
                            'file' => $file,
                            'path' => $this->themesPath . $theme . '/less/' . $file,
                        ];
                    }
                }
                closedir($handle);
            }
            if (($handle = opendir(realpath($this->themesPath . 'base/less'))) !== false) {
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '_assigns.less'
                        && $file !== 'theme.less.tmp.less'
                        && strpos($file, '.') > 0
                        && strpos($file, '.less.original') === false
                    ) {
                        $files[] = [
                            'file' => $file,
                            'path' => $this->themesPath . 'base/less/' . $file,
                        ];
                    }
                }
                closedir($handle);
            }
            sort($files);

            $return['fn']              = 'showFiles';
            $return['data']['files']   = $files;
            $return['data']['customs'] = $customs;

            return $return;
        }

        return $this->msg('danger', 'Dieser Theme-Ordner hat keine Schreibrechte!');
    }

    /**
     * Saves less file.
     *
     * @return array json output
     */
    private function save()
    {
        if (!isset($this->userData->file)) {
            return $this->msg('danger', 'Datei nicht angegeben.');
        }
        $source = $this->userData->file;
        if (!file_exists($source) || !in_array(pathinfo($source)['extension'], ['less', 'css'], true)) {
            return $this->msg('danger', 'Fehler beim Speichern der Datei');
        }
        if (isset($this->userData->content)) {
            if (!file_exists($source . '.original') && !copy($source, $source . '.original')) {
                return $this->msg(
                    'danger',
                    'Fehler beim Erstellen des Backups ' . $source . '.original'
                );
            }
            if (file_put_contents($source, base64_decode($this->userData->content)) === false) {
                return $this->msg('danger', 'Fehler beim Speichern der Datei');
            }

            return $this->msg('success', 'Datei wurde gespeichert');
        }
        $destination = realpath($this->themesPath . $this->userTheme . '/less/') . '/' . $this->userData->name;
        if (strpos($destination, PFAD_ROOT . PFAD_TEMPLATES) !== 0
            || !copy($source, $destination)
            || !in_array(pathinfo($destination)['extension'], ['less', 'css'], true)
        ) {
            return $this->msg('danger', 'Fehler beim Kopieren der Datei (Schreibrechte Theme-Ordner)');
        }

        return $this->open();
    }

    /**
     * Open less file.
     *
     * @return array json output
     */
    private function open()
    {
        $filePath                  = strpos($_REQUEST['data']['file'], PFAD_ROOT) === 0
            ? realpath($this->themesPath . $this->userTheme . '/less/') . '/' . basename($_REQUEST['data']['file'])
            : realpath($_REQUEST['data']['file']) . '/';
        if (strpos($filePath, $this->themesPath) !== 0) {
            return [];
        }
        $return['fn']              = 'openFile';
        $return['data']['file']    = $_REQUEST['data']['file'];
        $return['data']['name']    = $_REQUEST['data']['name'];
        $return['data']['content'] = base64_encode(file_get_contents($filePath));

        return $return;
    }

    /**
     * Removes less file.
     *
     * @return array json output
     */
    private function reset()
    {
        if (empty($this->userData->file)) {
            return $this->msg('danger', 'Datei nicht gefunden.');
        }
        $file     = $this->userData->file;
        $original = $file . '.original';
        $return   = [];
        if (!file_exists($original)) {
            return $this->msg('danger', 'Original nicht gefunden oder Datei noch nicht bearbeitet.');
        }
        if (unlink($file) === true) {
            copy($original, $file);
            $return['fn']              = 'enableFile';
            $return['data']['name']    = basename($file);
            $return['data']['file']    = $file;
            $return['data']['content'] = base64_encode(file_get_contents($original));
        } else {
            $return = $this->msg('danger', 'Fehler beim Löschen der Datei');
        }

        return $return;
    }

    /**
     * Compile theme.
     *
     * @param string|null $theme
     * @param string|null $template
     * @return array - json output
     */
    public function compile($theme = null, $template = null)
    {
        $cacheDir          = PFAD_ROOT . PFAD_COMPILEDIR . 'less';
        $theme             = $theme === null ? $this->userTheme : $theme;
        $template          = $template !== null
            ? $template
            : $this->userTemplate;
        $directory         = $template === null
            ? $this->themesPath . $theme
            : PFAD_ROOT . PFAD_TEMPLATES . $template . '/themes/' . $theme;
        $directory         = realpath($directory) . '/';
        $sourceMapFilename = 'sourcemap.map';
        $options           = [
            'sourceMap'         => true,
            'sourceMapWriteTo'  => $directory . $sourceMapFilename,
            'sourceMapURL'      => $sourceMapFilename,
            'sourceMapRootpath' => '../',
            'sourceMapBasepath' => PFAD_ROOT . PFAD_TEMPLATES . $template . '/themes/',
        ];
        if (strpos($directory, PFAD_ROOT . PFAD_TEMPLATES) === 0 && file_exists($directory . 'less/theme.less')) {
            try {
                if (defined('EVO_COMPILE_CACHE') && EVO_COMPILE_CACHE === true) {
                    if (!file_exists($cacheDir)) {
                        mkdir($cacheDir, 0777);
                    } else { // truncate cachedir
                        array_map('unlink', glob($cacheDir . '/lessphp*'));
                    }
                    $options['cache_dir'] = $cacheDir;
                }
                $parser = new Less_Parser($options);
                $parser->parseFile($directory . 'less/theme.less', '/');
                $css = $parser->getCss();
                file_put_contents($directory . 'bootstrap.css', $css);

                return $this->msg('success', 'Theme erfolgreich nach ' . $directory . 'bootstrap.css kompiliert.');
            } catch (\Exception $e) {
                return $this->msg('danger', $e->getMessage());
            }
        }

        return $this->msg('danger', 'Theme-Ordner wurde nicht gefunden.');
    }

    /**
     * Generate a message callback.
     *
     * @param string $type - message class (danger, success, info)
     * @param string $msg - message text
     * @return array json output
     */
    private function msg($type, $msg)
    {
        return [
            'fn'   => 'message',
            'data' => [
                'type' => $type,
                'msg'  => $msg,
            ],
        ];
    }
}
