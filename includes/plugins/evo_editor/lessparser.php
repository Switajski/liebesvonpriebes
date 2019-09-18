<?php
/**
 * generates SQL statements from less files for evo_editor
 *
 * example usage: $php lessparser.php > example.sql && mysql mydatabas < example.sql
 *
 */

$dir      = '../../../templates/Evo/themes/';
$iterator = new DirectoryIterator($dir);
$themes   = array();
$deletes  = array();
$sql      = '';
$i        = 0;

foreach ($iterator as $file) {
    if ($file->isDir() && file_exists($file->getRealPath() . '/less/theme.less') && file_exists($file->getRealPath() . '/less/variables.less')) {
        $themes[] = array('name' => $file->getBasename(), 'path' => $file->getRealPath());
    }
}

foreach ($themes as $theme) {
    $file   = $theme['path'] . '/less/variables.less';
    $handle = fopen($file, 'r');
    if ($handle) {
        $vars = array();
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
        if (count($vars) > 0) {
            $deletes[] = 'DELETE FROM `xplugin_jtl_template_demo_css` WHERE theme = "' . $theme['name'] . '";' . "\n";
            $sql .= "INSERT INTO `xplugin_jtl_template_demo_css` (`option_name`, `option_value`, `option_type`, `option_description`, `theme`) VALUES ";
            $inserts = array();
            foreach ($vars as $_var) {
                if (strpos($_var[0], '@') === 0) {
                    $type = 'text';
                    if (strpos($_var[1], 'px') !== false) {
                        $type = 'text';
                    } elseif (strpos($_var[1], '#') !== false || strpos($_var[1],
                            'darken') !== false || strpos($_var[1], 'lighten') !== false
                    ) {
                        $type = 'colorpicker';
                    }
                    $value   = $_var[1];
                    $value   = str_replace('"', '', $value);
                    $value   = str_replace(';', '', $value);
                    $comment = strpos($value, '//');
                    if ($comment !== false) {
                        $value = substr($value, 0, $comment);
                    }
                    $value = trim($value);
                    if ($value[strlen($value) - 1] === ';') {
                        $value = substr($value, 0, strlen($value) - 1);
                    }
//                    if ($value[0] === '#') {
//                        $value = substr($value, 1);
//                    }
                    if (strpos($value, '/') !== false) {
                        //                $value = '"' . $value . '"';
                        continue;
                    }

                    $inserts[] = "('" . substr($_var[0],
                            1) . "', '" . $value . "', '" . $type . "', '" . $_var[0] . "', '" . $theme['name'] . "')";
                }
            }
            $sql .= implode(",\n", $inserts) . ";\n";
        }
    }
}
echo implode("\n", $deletes) . $sql;
