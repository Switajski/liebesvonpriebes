<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class IOResponse
 */
class IOResponse implements JsonSerializable
{
    /**
     * @var array
     */
    private $assigns = [];

    /**
     * @var array
     */
    private $scripts = [];

    /**
     * @param string $target
     * @param string $attr
     * @param mixed $data
     * @return $this
     */
    public function assign($target, $attr, $data)
    {
        $this->assigns[] = (object)[
            'target' => $target,
            'attr'   => $attr,
            'data'   => $data
        ];

        return $this;
    }

    /**
     * @param string $js
     * @return $this
     */
    public function script($js)
    {
        $this->scripts[] = $js;

        return $this;
    }

    /**
     * @param string $function
     * @return $this
     */
    public function jsfunc($function)
    {
        $arguments = func_get_args();
        array_shift($arguments);

        $filtered = $arguments;

        array_walk($filtered, function (&$value, $key) {

            switch (gettype($value)) {
                case 'array':
                case 'object':
                case 'string':
                    $value = utf8_convert_recursive($value);
                    $value = json_encode($value);
                    break;

                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'integer':
                case 'double':
                    // nothing todo
                    break;

                case 'resource':
                case 'NULL':
                case 'unknown type':
                default:
                    $value = 'null';
                    break;
            }
        });

        $argumentlist = implode(', ', $filtered);
        $syntax       = sprintf('%s(%s);', $function, $argumentlist);

        $this->script($syntax);

        if (defined('IO_LOG_CONSOLE') && IO_LOG_CONSOLE === true) {
            $reset  = 'background: transparent; color: #000;';
            $orange = 'background: #e86c00; color: #fff;';
            $grey   = 'background: #e8e8e8; color: #333;';

            $args = json_encode(utf8_convert_recursive($arguments));

            $this->script("console.groupCollapsed('%c CALL %c {$function}()', '$orange', '$reset');");
            $this->script("console.log('%c METHOD %c {$function}()', '$grey', '$reset');");
            $this->script("console.log('%c PARAMS %c', '$grey', '$reset', " . $args . ");");

            $this->script("console.groupCollapsed('%c TOGGLE DEBUG TRACE %c', '$grey', '$reset');");

            foreach ($this->generateCallTrace() as $trace) {
                $this->script("console.log('%c TRACE %c', '$grey', '$reset', " . json_encode($trace) . ");");
            }

            $this->script("console.groupEnd();");
            $this->script("console.groupEnd();");
        }

        return $this;
    }

    /**
     * @return array
     */
    public function generateCallTrace()
    {
        $str   = (new Exception())->getTraceAsString();
        $trace = explode("\n", $str);
        $trace = array_reverse($trace);
        array_shift($trace);
        array_pop($trace);
        $result = [];

        foreach ($trace as $i => $t) {
            $result[] = '#' . ($i + 1) . substr($t, strpos($t, ' '));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'js'  => $this->scripts,
            'css' => $this->assigns,
        ];
    }
}
