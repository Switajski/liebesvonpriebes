<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SimpleXMLObject
 */
class SimpleXMLObject
{
    /**
     * @return object
     */
    public function attributes()
    {
        $container = get_object_vars($this);

        return (object)$container['@attributes'];
    }

    /**
     * @return object
     */
    public function content()
    {
        $container = get_object_vars($this);

        return (object)$container['@content'];
    }
}

/**
 * Class SimpleXML
 */
class SimpleXML
{
    /**
     * @var array
     */
    public $result = [];

    /**
     * @var int
     */
    public $ignore_level = 0;

    /**
     * @var bool
     */
    public $skip_empty_values = false;

    /**
     * @var
     */
    public $php_errormsg;

    /**
     * @var string
     */
    public $evalCode = '';

    /**
     * @param int    $level
     * @param array  $tags
     * @param mixed  $value
     * @param string $type
     */
    public function array_insert($level, $tags, $value, $type)
    {
        $temp = '';
        for ($c = $this->ignore_level + 1; $c < $level + 1; $c++) {
            if (isset($tags[$c]) && (is_numeric(trim($tags[$c])) || trim($tags[$c]))) {
                if (is_numeric($tags[$c])) {
                    $temp .= '[' . $tags[$c] . ']';
                } else {
                    $temp .= '["' . $tags[$c] . '"]';
                }
            }
        }
        $this->evalCode .= '$this->result' . $temp . "=\"" . addslashes($value) . "\";//(" . $type . ")\n";
    }

    /**
     * @param array $array
     * @return array
     */
    public function xml_tags($array)
    {
        $repeats_temp  = [];
        $repeats_count = [];
        $repeats       = [];

        if (is_array($array)) {
            $n = count($array) - 1;
            for ($i = 0; $i < $n; $i++) {
                $idn = $array[$i]['tag'] . $array[$i]['level'];
                if (in_array($idn, $repeats_temp)) {
                    ++$repeats_count[array_search($idn, $repeats_temp)];
                } else {
                    $repeats_temp[]                                   = $idn;
                    $repeats_count[array_search($idn, $repeats_temp)] = 1;
                }
            }
        }
        $n = count($repeats_count);
        for ($i = 0; $i < $n; $i++) {
            if ($repeats_count[$i] > 1) {
                $repeats[] = $repeats_temp[$i];
            }
        }
        unset($repeats_temp, $repeats_count);

        return array_unique($repeats);
    }

    /**
     * @param array $arg_array
     * @return array|SimpleXMLObject
     */
    public function array2object($arg_array)
    {
        $tmp = new stdClass();
        if (is_array($arg_array)) {
            $keys = array_keys($arg_array);
            if (!is_numeric($keys[0])) {
                $tmp = new SimpleXMLObject;
            }
            foreach ($keys as $key) {
                if (is_numeric($key)) {
                    $has_number = true;
                }
                if (is_string($key)) {
                    $has_string = true;
                }
            }
            if (isset($has_number) && !isset($has_string)) {
                foreach ($arg_array as $key => $value) {
                    $tmp[] = $this->array2object($value);
                }
            } elseif (isset($has_string)) {
                foreach ($arg_array as $key => $value) {
                    if (is_string($key)) {
                        $tmp->$key = $this->array2object($value);
                    }
                }
            }
        } elseif (is_object($arg_array)) {
            foreach ($arg_array as $key => $value) {
                $tmp->$key = (is_array($value) || is_object($value))
                    ? $this->array2object($value)
                    : $value;
            }
        } else {
            $tmp = $arg_array;
        }

        return $tmp; //return the object
    }

    /**
     * @param array $array
     * @return array
     */
    public function array_reindex($array)
    {
        if (is_array($array)) {
            if ($array[0] && count($array) === 1) {
                return $this->array_reindex($array[0]);
            } else {
                foreach ($array as $keys => $items) {
                    if (is_array($items)) {
                        if (is_numeric($keys)) {
                            $array[$keys] = $this->array_reindex($items);
                        } else {
                            $array[$keys] = $this->array_reindex(array_merge([], $items));
                        }
                    }
                }
            }
        }

        return $array;
    }

    /**
     * @param array $array
     * @return array
     */
    public function xml_reorganize($array)
    {
        $count       = count($array);
        $repeat      = $this->xml_tags($array);
        $repeatedone = false;
        $tags        = [];
        $k           = 0;
        for ($i = 0; $i < $count; $i++) {
            switch ($array[$i]['type']) {
                case 'open':
                    $tags[] = $array[$i]['tag'];
                    if ($i > 0 && ($array[$i - 1]['type'] === 'close') && ($array[$i]['tag'] == $array[$i - 1]['tag'])) {
                        $k++;
                    }
                    if (isset($array[$i]['value']) && ($array[$i]['value'] || !$this->skip_empty_values)) {
                        $tags[] = '@content';
                        $this->array_insert(count($tags), $tags, $array[$i]['value'], "open");
                        array_pop($tags);
                    }

                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        if ($repeatedone && ($repeatedone == $array[$i]['tag'] . $array[$i]['level'])) {
                            $tags[] = (string)($k++);
                        } else {
                            $repeatedone = $array[$i]['tag'] . $array[$i]['level'];
                            $tags[]      = (string)$k;
                        }
                    }

                    if (isset($array[$i]['attributes']) && $array[$i]['attributes'] && $array[$i]['level'] != $this->ignore_level) {
                        $tags[] = '@attributes';
                        foreach ($array[$i]['attributes'] as $attrkey => $attr) {
                            $tags[] = $attrkey;
                            $this->array_insert(count($tags), $tags, $attr, "open");
                            array_pop($tags);
                        }
                        array_pop($tags);
                    }
                    break;

                case 'close':
                    array_pop($tags);
                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        if ($repeatedone == $array[$i]['tag'] . $array[$i]['level']) {
                            array_pop($tags);
                        } else {
                            $repeatedone = $array[$i + 1]['tag'] . $array[$i + 1]['level'];
                            array_pop($tags);
                        }
                    }
                    break;

                case 'complete':
                    $tags[] = $array[$i]['tag'];
                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        if ($repeatedone && $repeatedone == $array[$i]['tag'] . $array[$i]['level']) {
                            $tags[] = (string)$k;
                        } else {
                            $repeatedone = $array[$i]['tag'] . $array[$i]['level'];
                            $tags[]      = (string)$k;
                        }
                    }

                    if (isset($array[$i]['value']) && ($array[$i]['value'] || !$this->skip_empty_values)) {
                        if (isset($array[$i]['attributes']) && $array[$i]['attributes']) {
                            $tags[] = '@content';
                            $this->array_insert(count($tags), $tags, $array[$i]['value'], "complete");
                            array_pop($tags);
                        } else {
                            $this->array_insert(count($tags), $tags, $array[$i]['value'], "complete");
                        }
                    }

                    if (isset($array[$i]['attributes']) && $array[$i]['attributes']) {
                        $tags[] = '@attributes';
                        foreach ($array[$i]['attributes'] as $attrkey => $attr) {
                            $tags[] = $attrkey;
                            $this->array_insert(count($tags), $tags, $attr, "complete");
                            array_pop($tags);
                        }
                        array_pop($tags);
                    }

                    if (in_array($array[$i]['tag'] . $array[$i]['level'], $repeat)) {
                        array_pop($tags);
                        $k++;
                    }

                    array_pop($tags);
                    break;
            }
        }
        eval($this->evalCode);

        return $this->array_reindex($this->result);
    }

    /**
     * @param string $file
     * @param string $resulttype
     * @param string $encoding
     * @return array|SimpleXMLObject|string
     */
    public function xml_load_file($file, $resulttype = 'object', $encoding = 'UTF-8')
    {
        $this->result   = '';
        $this->evalCode = '';
        $data           = file_get_contents($file);
        if (!$data) {
            return 'Cannot open xml document: ' . $file;
        }

        return xml_load_string($data);
    }

    /**
     * @param string $data
     * @param string $resulttype
     * @param string $encoding
     * @return array|SimpleXMLObject|string
     */
    public function xml_load_string($data, $resulttype = 'object', $encoding = 'UTF-8')
    {
        $errmsg = '';
        $parser = xml_parser_create($encoding);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        $ok = xml_parse_into_struct($parser, $data, $values);
        if (!$ok) {
            $errmsg = sprintf(
                "XML parse error %d '%s' at line %d, column %d (byte index %d)",
                xml_get_error_code($parser),
                xml_error_string(xml_get_error_code($parser)),
                xml_get_current_line_number($parser),
                xml_get_current_column_number($parser),
                xml_get_current_byte_index($parser)
            );
        }

        xml_parser_free($parser);
        if (!$ok) {
            return $errmsg;
        }
        if ($resulttype === 'array') {
            return $this->xml_reorganize($values);
        }

        // default $resulttype is 'object'
        return $this->array2object($this->xml_reorganize($values));
    }
}

if (!function_exists('simplexml_load_file')) {
    /**
     * @param string $file
     * @return array|SimpleXMLObject|string
     */
    function simplexml_load_file($file)
    {
        $sx = new SimpleXML();

        return $sx->xml_load_file($file);
    }
}

if (!function_exists('xml_load_string')) {
    /**
     * @param string $data
     * @return array|SimpleXMLObject|string
     */
    function xml_load_string($data)
    {
        $sx = new SimpleXML();

        return $sx->xml_load_string($data);
    }
}
