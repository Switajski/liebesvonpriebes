<?php

/**
 * Class JTLTplHelper
 */
class JTLTplHelper
{
    /**
     * @var Plugin
     */
    private $oPlugin;

    /**
     * @param Plugin $oPlugin
     */
    public function __construct(Plugin $oPlugin)
    {
        $this->oPlugin = $oPlugin;
    }

    /**
     * get options from db
     *
     * @var string $theme
     * @var string $templateDir
     * @return mixed
     */
    public function getOptions($theme = 'evo', $templateDir = null)
    {
        $result = [];
        if ($templateDir === null) {
            $templateDir = PFAD_TEMPLATES . 'Evo/';
        }
        $file   = PFAD_ROOT . $templateDir . 'themes/' . $theme . '/less/variables.less';
        $handle = fopen($file, 'r');
        if ($handle) {
            $vars = [];
            while (($line = fgets($handle)) !== false) {
                // process the line read.
                if (strpos($line, ':') !== false) {
                    $x     = explode(':', $line);
                    $count = count($x);
                    if ($count === 2) {
                        $x[0]   = trim($x[0]);
                        $x[1]   = trim($x[1]);
                        $vars[] = $x;
                    }
                }
            }
            fclose($handle);
            foreach ($vars as $_var) {
                if (strpos($_var[0], '@') === 0) {
                    $value   = $_var[1];
                    $type    = 'text';
                    $value   = str_replace(';', '', $value);
                    $comment = strpos($value, '//');
                    if ($comment !== false) {
                        $value = substr($value, 0, $comment);
                    }
                    $value = trim($value);
                    if ($value[strlen($value) - 1] === ';') {
                        $value = substr($value, 0, strlen($value) - 1);
                    }

                    $pos = strpos($value, 'px');
                    $len = strlen($value);
                    if (strpos($value, '@') === 0) {
                        $type = 'variable';
                    } elseif (strpos($value, '#') !== false
                        || ($value === 'transparent'
                            || strpos($value, 'rgba(') === 0
                            || strpos($value, 'rgb(') === 0
                        )
                    ) {
                        $type = 'colorpicker';
                    } elseif (strpos($value, 'darken(') !== false || strpos($value, 'lighten(') !== false) {
                        $type = 'color';
                    } elseif ($pos === $len - strlen('px')) {
                        $type = 'px';
                    } elseif (is_numeric($value) || strpos($value, '+') !== false || strpos($value, '*') !== false) {
                        $type = 'number';
                    } elseif (strpos($value, 'serif') !== false || strpos($value, 'monospace') !== false) {
                        $type = 'font';
                    }
                    $value = htmlspecialchars($value);

                    $result[$_var[0]] = [
                        'name'        => substr($_var[0], 1),
                        'value'       => $value,
                        'type'        => $type,
                        'description' => ''
                    ];
                }
            }

            foreach ($result as $_name => &$_res) {
                if ($_res['type'] === 'variable') {
                    if (isset($result[$_res['value']])) {
                        $_res['type'] = ($result[$_res['value']]['type'] === 'colorpicker')
                            ? 'color'
                            : $result[$_res['value']]['type'];
                    } else {
                        $_res['type'] = 'text';
                    }
                }
            }
            // note: "colorpicker" for _all_ colors - even variables - would be great,
            // but the js colorpicker defaults to rgba(0,0,0,1) for strings like that
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getFontChooser()
    {
        return '';
//        $fontChooser = '';
//        $fontJson    = file_get_contents($this->oPlugin->cAdminmenuPfad . 'google.json');
//        $fonts       = json_decode($fontJson);
//        if (isset($fonts->items)) {
//            $i           = 0;
//            $fontChooser = '<select class="form-control" id="font-choice" name="font-choice">';
//            foreach ($fonts->items as $font) {
//                $fontChooser .= '<option class="font-option" id="font-option-' . $i . '" value="' .
//                    $font->family . '" data-variants="' . str_replace('"',
//                        '\'', json_encode($font->variants)) . '">' . $font->family .
//                    '</option>';
//                $i++;
//            }
//            $fontChooser .= '</select>';
//            $fontChooser .= '<select id="font-variants" name="font-variants[]" style="display: none;" multiple></select>';
//        }
//
//        return $fontChooser;
    }

    /**
     * save options to db
     *
     * @param array $post
     * @return bool|string
     */
    private function saveOptions($post)
    {
        $theme    = isset($post['theme']) ? basename($post['theme']) : 'evo';
        $themeDir = isset($post['template_dir'])
            ? $post['template_dir'] . 'themes/' . $theme . '/'
            : PFAD_ROOT . PFAD_TEMPLATES . 'Evo/themes/' . $theme . '/';
        $file     = realpath($themeDir) . '/less/variables.less';
        $original = $file . '.original';
        if (strpos($file, PFAD_ROOT . PFAD_TEMPLATES) !== 0) {
            return false;
        }
        if (!file_exists($original) && !copy($file, $original)) {
            return vsprintf($this->oPlugin->oPluginSprachvariableAssoc_arr['file_not_writeable'], $file);
        }
        $saveString = '';
        foreach ($post as $postVar => $value) {
            if (strpos($postVar, 'input-') === 0) {
                $saveString .= ('@' . substr($postVar, strlen('input-')) . ': ' . $value . ';' . "\n");
            }
        }

        return file_put_contents($file, $saveString) > 0
            ? true
            : vsprintf($this->oPlugin->oPluginSprachvariableAssoc_arr['file_not_writeable'], $file);
    }

    /**
     * POST handler for back and frontend
     *
     * @param array $post
     * @return array
     */
    public function handlePost($post)
    {
        $result = [
            'ok'  => false,
            'msg' => utf8_encode($this->oPlugin->oPluginSprachvariableAssoc_arr['msg_invalid_token'])
        ];

        if (true !== validateToken()) {
            return $result;
        }
        if (!Shop::isAdmin()) {
            $result['msg'] = $this->oPlugin->oPluginSprachvariableAssoc_arr['msg_no_admin_user'];

            return $result;
        }
        $result = ['ok' => false, 'msg' => $this->oPlugin->oPluginSprachvariableAssoc_arr['compile_failed']];
        if (!isset($post['template_dir'], $post['theme'])) {
            return $result;
        }
        $theme         = isset($post['theme']) ? basename($post['theme']) : 'evo';
        $templateDir   = isset($post['template_dir'])
            ? realpath($post['template_dir'] . 'themes/' . $theme) . '/'
            : realpath(PFAD_ROOT . PFAD_TEMPLATES . 'Evo/themes/' . $theme) . '/';
        if (strpos($templateDir, PFAD_ROOT . PFAD_TEMPLATES) !== 0) {
            return $result;
        }
        $variablesLess = $templateDir . 'less/variables.less';
        $themeLess     = $templateDir . 'less/theme.less';
        $compiledCSS   = $templateDir . 'bootstrap.css';
        if (!file_exists($variablesLess) || !is_writable($variablesLess)) {
            $result['msg'] = vsprintf($this->oPlugin->oPluginSprachvariableAssoc_arr['file_not_writeable'],
                $variablesLess);

            return $result;
        }
        if (!file_exists($themeLess)) {
            $result['msg'] = vsprintf($this->oPlugin->oPluginSprachvariableAssoc_arr['file_doest_not_exist'],
                $themeLess);

            return $result;
        }
        if (!file_exists($compiledCSS) || !is_writable($compiledCSS)) {
            $result['msg'] = vsprintf($this->oPlugin->oPluginSprachvariableAssoc_arr['file_not_writeable'],
                $compiledCSS);

            return $result;
        }

        $save = $this->saveOptions($post);
        if ($save !== true) {
            $result['msg'] = $save;

            return $result;
        }

        require_once PFAD_ROOT . PFAD_PLUGIN . $this->oPlugin->cVerzeichnis . '/vendor/autoload.php';
        try {
            $parser = new Less_Parser();
            $parser->parseFile($themeLess, '/');
            $css           = $parser->getCss();
            $write         = @file_put_contents($compiledCSS, $css);
            $result['ok']  = ($write > 0);
            $result['msg'] = vsprintf($this->oPlugin->oPluginSprachvariableAssoc_arr['compile_ok'],
                [$theme, $compiledCSS]);
        } catch (Exception $e) {
            $result['msg'] = $this->oPlugin->oPluginSprachvariableAssoc_arr['compile_failed'] . $e->getMessage();
        }

        return $result;
    }
}
