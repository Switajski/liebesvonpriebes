<?php
/*
 *  SimpleCSS Parser
 *  (c) 2010 Andreas Juetten <andreasjuetten@gmx.de>
 */

define('LF', "\n\n");

/**
 * Class SimpleCSS
 */
class SimpleCSS
{
    /**
     * @var array
     */
    public $cCSS_arr = [];

    /**
     * @param $cSelector
     * @param $cAttribute
     * @param $cValue
     * @return $this
     */
    public function addCSS($cSelector, $cAttribute, $cValue)
    {
        if (isset($this->cCSS_arr[$cSelector])) {
            $this->cCSS_arr[$cSelector] = array_merge($this->cCSS_arr[$cSelector], [$cAttribute => $cValue]);
        } else {
            $this->cCSS_arr[$cSelector] = [$cAttribute => $cValue];
        }

        return $this;
    }

    /**
     * @param $cFile
     * @return bool
     */
    public function addFile($cFile)
    {
        if (!file_exists($cFile)) {
            return false;
        }
        $cData            = file_get_contents($cFile);
        $cData            = preg_replace('!/\*.*?\*/!s', '', $cData);
        $cData_arr        = preg_split('/\{|\}/', $cData);
        $dataCount        = count($cData_arr);
        $cSelector_arr    = [];
        $cCSSBaseAttr_arr = [];
        for ($i = 0; $i < $dataCount; $i++) {
            if ($i % 2 === 0) {
                $cCSSBaseAttr_arr = [];
                $cSelector_arr    = explode(',', $cData_arr[$i]);
            }
            if ($i % 2 === 1) {
                $cAttr_arr = explode(';', $cData_arr[$i]);
                $cAttr_arr = $this->trimCSSData($cAttr_arr);
                foreach ($cAttr_arr as $cAttr) {
                    $cTmpAttr_arr = explode(':', $cAttr);
                    if (is_array($cTmpAttr_arr) && count($cTmpAttr_arr) === 2) {
                        $cName                    = trim($cTmpAttr_arr[0]);
                        $cCSSBaseAttr_arr[$cName] = trim($cTmpAttr_arr[1]);
                    }
                }

                foreach ($cSelector_arr as $cSelector) {
                    $cSelector = trim($cSelector);
                    $cSelector = preg_replace('#\s+#', ' ', $cSelector);
                    if (isset($this->cCSS_arr[$cSelector])) {
                        $this->cCSS_arr[$cSelector] = array_merge($this->cCSS_arr[$cSelector], $cCSSBaseAttr_arr);
                    } else {
                        $this->cCSS_arr[$cSelector] = $cCSSBaseAttr_arr;
                    }
                }
            }
        }

        return (boolean) (count($this->cCSS_arr) > 0);
    }

    /**
     * @param $cData_arr
     * @return array
     */
    public function trimCSSData($cData_arr)
    {
        $cCSS_arr = [];
        foreach ($cData_arr as $cData) {
            $cData = trim($cData);
            if ($cData !== '') {
                $cCSS_arr[] = $cData;
            }
        }

        return $cCSS_arr;
    }

    /**
     * @param $cSelector
     * @return bool
     */
    public function getSelector($cSelector)
    {
        if (is_array($this->cCSS_arr) && count($this->cCSS_arr) && isset($this->cCSS_arr[$cSelector])) {
            return $this->cCSS_arr[$cSelector];
        }

        return false;
    }

    /**
     * @param $cSelector
     * @param $cKey
     * @return bool
     */
    public function getAttribute($cSelector, $cKey)
    {
        $cAttr_arr = $this->getSelector($cSelector);
        if (is_array($cAttr_arr) && count($cAttr_arr)) {
            foreach ($cAttr_arr as $cAttrKey => $cValue) {
                if (strcasecmp($cAttrKey, $cKey) === 0) {
                    return $cValue;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCSS()
    {
        if (is_array($this->cCSS_arr) && count($this->cCSS_arr)) {
            return $this->cCSS_arr;
        }

        return [];
    }

    /**
     * @return string
     */
    public function renderCSS()
    {
        $cOut = '';
        if (is_array($this->cCSS_arr) && count($this->cCSS_arr)) {
            foreach ($this->cCSS_arr as $cSelector => $cAttribute) {
                $cOut .= $cSelector . ' {' . LF;
                foreach ($cAttribute as $cKey => $cValue) {
                    if (strlen($cKey) && strlen($cValue)) {
                        $cOut .= '   ' . $cKey . ': ' . $cValue . ';' . LF;
                    }
                }
                $cOut .= '}' . LF;
            }
        }

        return $cOut;
    }

    /**
     * @param string $cOrdner
     * @return string
     */
    public function getTemplatePath($cOrdner)
    {
        return realpath(PFAD_ROOT . 'templates/' . basename($cOrdner)) . '/';
    }

    /**
     * @param string $cOrdner
     * @return string
     */
    public function getCustomCSSFile($cOrdner)
    {
        return $this->getTemplatePath($cOrdner) . 'themes/custom.css';
    }

    /**
     * @param $cValue
     * @param $cType
     * @return bool|string
     */
    public function getAttrAs($cValue, $cType)
    {
        $cMatch_arr = [];

        switch ($cType) {
            case 'color': {
                // rgb(255,255,255)
                if (preg_match('/rgb(\s*)\(([\d\s]+),([\d\s]+),([\d\s]+)\)/', $cValue, $cMatch_arr)) {
                    return $this->rgb2html((int)$cMatch_arr[2], (int)$cMatch_arr[3], (int)$cMatch_arr[4]);
                } // #fff or #ffffff
                elseif (preg_match('/#([\w\d]+)/', $cValue, $cMatch_arr)) {
                    return trim($cMatch_arr[0]);
                }
                break;
            }

            case 'size': {
                // 1.2em 15% '12 px'
                if (preg_match('/([\d\.]+)(.*)/', $cValue, $cMatch_arr)) {
                    $cOut['numeric'] = (float)$cMatch_arr[1];
                    $cOut['unit']    = trim($cMatch_arr[2]);

                    return $cOut;
                }
                break;
            }

            default:
                break;
        }

        return false;
    }

    /**
     * @param $r
     * @param $g
     * @param $b
     * @return string
     */
    public function rgb2html($r, $g, $b)
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

    /**
     * @param $color
     * @return array|bool
     */
    public function html2rgb($color)
    {
        if ($color[0] === '#') {
            $color = substr($color, 1);
        }
        if (strlen($color) === 6) {
            list($r, $g, $b) = [
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            ];
        } elseif (strlen($color) === 3) {
            list($r, $g, $b) = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return [$r, $g, $b];
    }

    /**
     * @return array
     */
    public function getUnits()
    {
        return ['em', 'px', '%'];
    }
}
