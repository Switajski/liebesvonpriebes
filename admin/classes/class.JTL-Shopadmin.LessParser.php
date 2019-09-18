<?php

/**
 * Class LessParser
 */
class LessParser
{
    /**
     * @var array
     */
    private $_stack = [];

    /**
     * @param string $file
     * @return $this
     */
    public function read($file)
    {
        $lines = file($file, FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match('/@([\d\w\-]+)\s*:\s*([^;]+)/', $line, $matches)) {
                list(, $key, $value) = $matches;
                $this->_stack[$key]  = $value;
            }
        }

        return $this;
    }

    /**
     * @param string $file
     * @return int
     */
    public function write($file)
    {
        $content = '';
        foreach ($this->_stack as $key => $value) {
            $content .= "@{$key}: {$value};\r\n";
        }

        return file_put_contents($file, $content);
    }

    /**
     * @return array
     */
    public function getStack()
    {
        return $this->_stack;
    }

    /**
     * @return array
     */
    public function getColors()
    {
        $colors = [];
        foreach ($this->_stack as $key => $value) {
            $color = $this->getAs($value, 'color');
            if ($color) {
                $colors[$key] = $color;
            }
        }

        return $colors;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->_stack[$key] = $value;

        return $this;
    }

    /**
     * @param string      $key
     * @param null|string $type
     * @return bool|null
     */
    public function get($key, $type = null)
    {
        $value = isset($this->_stack[$key]) ?
            $this->_stack[$key] : null;

        if ($value !== null && !$type !== null) {
            $typedValue = $this->getAs($value, $type);
            if ($typedValue !== false) {
                return $typedValue;
            }
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string $type
     * @return bool|string
     */
    protected function getAs($value, $type)
    {
        $matches = [];

        switch (strtolower($type)) {
            case 'color':
                // rgb(255,255,255)
                if (preg_match('/rgb(\s*)\(([\d\s]+),([\d\s]+),([\d\s]+)\)/', $value, $matches)) {
                    return $this->rgb2html((int)$matches[2], (int)$matches[3], (int)$matches[4]);
                } // #fff or #ffffff
                elseif (preg_match('/#([\w\d]+)/', $value, $matches)) {
                    return trim($matches[0]);
                }
                break;

            case 'size':
                // 1.2em 15% '12 px'
                if (preg_match('/([\d\.]+)(.*)/', $value, $matches)) {
                    $pair = [
                        'numeric' => (float)$matches[1],
                        'unit'    => trim($matches[2])
                    ];

                    return $pair['numeric'];
                }
                break;

            default:
                break;
        }

        return false;
    }

    /**
     * @param int|array $r
     * @param int       $g
     * @param int       $b
     * @return string
     */
    protected function rgb2html($r, $g, $b)
    {
        if (is_array($r) && count($r) === 3) {
            list($r, $g, $b) = $r;
        }

        $r = (int)$r;
        $g = (int)$g;
        $b = (int)$b;

        $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
        $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
        $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

        $color = (strlen($r) < 2 ? '0' : '') . $r;
        $color .= (strlen($g) < 2 ? '0' : '') . $g;
        $color .= (strlen($b) < 2 ? '0' : '') . $b;

        return '#' . $color;
    }
}
