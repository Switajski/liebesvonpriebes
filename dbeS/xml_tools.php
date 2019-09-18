<?php
###################################################################################
#
# XML Library, by Keith Devens, version 1.2b
# http://keithdevens.com/software/phpxml
#
# This code is Open Source, released under terms similar to the Artistic License.
# Read the license at http://keithdevens.com/software/license
#
###################################################################################

/**
 * takes raw XML as a parameter (a string)
 * and returns an equivalent PHP data structure
 *
 * @param string $xml
 * @param string $cEncoding
 * @return array|null
 */
function XML_unserialize(&$xml, $cEncoding = 'UTF-8')
{
    $xml_parser = new XML($cEncoding);
    $data       = $xml_parser->parse($xml);
    $xml_parser->destruct();

    return $data;
}

/**
 * serializes any PHP data structure into XML
 * Takes one parameter: the data to serialize. Must be an array.
 *
 * @param array $data
 * @param int   $level
 * @param null  $prior_key
 * @return string
 */
function XML_serialize(&$data, $level = 0, $prior_key = null)
{
    if ($level === 0) {
        ob_start();
        echo '<?xml version="1.0" ?>', "\n";
    }
    foreach ($data as $key => $value) {
        if (!strpos($key, ' attr')) //if it's not an attribute
            // we don't treat attributes by themselves, so for an empty element
            // that has attributes you still need to set the element to NULL
        {
            if (is_array($value) && array_key_exists(0, $value)) {
                XML_serialize($value, $level, $key);
            } else {
                $tag = $prior_key ?: $key;
                echo str_repeat("\t", $level), '<', $tag;
                if (array_key_exists("$key attr", $data)) { // if there's an attribute for this element
                    foreach($data["$key attr"] as $attr_name => $attr_value) {
                        echo ' ', $attr_name, '="', StringHandler::htmlspecialchars($attr_value), '"';
                    }
                    reset($data["$key attr"]);
                }

                if ($value === null) {
                    echo " />\n";
                } elseif (!is_array($value)) {
                    echo '>', StringHandler::htmlspecialchars($value), "</$tag>\n";
                } else {
                    echo ">\n", XML_serialize($value, $level + 1), str_repeat("\t", $level), "</$tag>\n";
                }
            }
        }
    }
    reset($data);
    if ($level === 0) {
        $str = ob_get_contents();
        ob_end_clean();

        return $str;
    }
}

/**
 * utility class to be used with PHP's XML handling functions
 *
 * Class XML
 */
class XML
{
    public $parser;   #a reference to the XML parser
    public $document; #the entire XML structure built up so far
    public $parent;   #a pointer to the current parent - the parent will be an array
    public $stack;    #a stack of the most recent parent at each nesting level
    public $last_opened_tag; #keeps track of the last tag opened.
    public $data;

    /**
     * XML constructor.
     * @param string $cEncoding
     */
    public function __construct($cEncoding)
    {
        //$this->parser = &xml_parser_create("UTF-8");
        $this->parser = xml_parser_create($cEncoding);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, JTL_CHARSET);

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'open', 'close');
        xml_set_character_data_handler($this->parser, 'data');
    }

    /**
     *
     */
    public function destruct()
    {
        xml_parser_free($this->parser);
    }

    /**
     * @param mixed $data
     * @return array|null
     */
    public function parse(&$data)
    {
        $this->document = [];
        $this->stack    = [];
        $this->parent   = &$this->document;

        return xml_parse($this->parser, $data, true) ? $this->document : null;
    }

    /**
     * @param string $parser
     * @param mixed  $tag
     * @param mixed  $attributes
     */
    public function open(&$parser, $tag, $attributes)
    {
        $this->data            = ''; #stores temporary cdata
        $this->last_opened_tag = $tag;
        if (is_array($this->parent) and array_key_exists($tag, $this->parent)) { #if you've seen this tag before
            if (is_array($this->parent[$tag]) and array_key_exists(0, $this->parent[$tag])) { #if the keys are numeric
                #this is the third or later instance of $tag we've come across
                $key = count_numeric_items($this->parent[$tag]);
            } else {
                #this is the second instance of $tag that we've seen. shift around
                if (array_key_exists("$tag attr", $this->parent)) {
                    $arr = ['0 attr' => &$this->parent["$tag attr"], &$this->parent[$tag]];
                    unset($this->parent["$tag attr"]);
                } else {
                    $arr = [&$this->parent[$tag]];
                }
                $this->parent[$tag] = &$arr;
                $key                = 1;
            }
            $this->parent = &$this->parent[$tag];
        } else {
            $key = $tag;
        }

        if ($attributes) {
            $this->parent["$key attr"] = $attributes;
        }
        $this->parent  = &$this->parent[$key];
        $this->stack[] = &$this->parent;
    }

    /**
     * @param resource $parser
     * @param string $data
     */
    public function data(&$parser, $data)
    {
        if ($this->last_opened_tag != null) #you don't need to store whitespace in between tags
        {
            $this->data .= $data;
        }
    }

    /**
     * @param resource $parser
     * @param string $tag
     */
    public function close(&$parser, $tag)
    {
        if ($this->last_opened_tag == $tag) {
            $this->parent          = $this->data;
            $this->last_opened_tag = null;
        }
        array_pop($this->stack);
        if ($this->stack) {
            $this->parent = &$this->stack[count($this->stack) - 1];
        }
    }
}

/**
 * @param array $array
 * @return int
 */
function count_numeric_items(&$array)
{
    return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
}
