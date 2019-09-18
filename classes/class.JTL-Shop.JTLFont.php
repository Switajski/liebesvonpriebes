<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class JTLFont
 */
class JTLFont
{
    /**
     * @var int
     */
    public $nSize;

    /**
     * @var string
     */
    public $cFont;

    /**
     * @var string
     */
    public $cColor;

    /**
     * @var int
     */
    public $nMaxWidth;

    /**
     * @var int
     */
    public $nMinWidth;

    /**
     * @var int
     */
    public $nMaxHeight;

    /**
     * @var int
     */
    public $nPadding = 4;

    /**
     * @var string
     */
    public $cCacheDir = '';

    /**
     * @var string
     */
    public $cFontDir = '';

    /**
     * @param string $cFont
     * @param int    $nSize
     * @param string $cColor
     */
    public function __construct($cFont, $nSize, $cColor)
    {
        $this->cCacheDir = PFAD_COMPILEDIR;
        $this->cFontDir  = PFAD_ROOT . PFAD_FONTS;
        $this->cFont     = $cFont;
        $this->nSize     = $nSize;
        $this->cColor    = $cColor;
    }

    /**
     * @param string $cColor
     * @return array|bool
     */
    public function html2rgb($cColor)
    {
        if ($cColor[0] === '#') {
            $cColor = substr($cColor, 1);
        }
        if (strlen($cColor) === 6) {
            list($r, $g, $b) = [
                $cColor[0] . $cColor[1],
                $cColor[2] . $cColor[3],
                $cColor[4] . $cColor[5]
            ];
        } elseif (strlen($cColor) === 3) {
            list($r, $g, $b) = [
                $cColor[0] . $cColor[0],
                $cColor[1] . $cColor[1],
                $cColor[2] . $cColor[2]
            ];
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return [$r, $g, $b];
    }

    /**
     * @param string $cText
     * @return array
     */
    public function calcRect($cText)
    {
        $box    = imagettfbbox($this->nSize, 0, $this->cFontDir . '/' . $this->cFont, $cText);
        $width  = abs($box[2] - $box[0]);
        $height = abs($box[7] - $box[1]);
        $x      = $box[6] * -1;
        $y      = $box[5] * -1;

        return [$width, $height, $x, $y];
    }

    /**
     * @param string $cText
     */
    public function calcMax($cText)
    {
        $strLen = strlen($cText);
        for ($i = 0; $i < $strLen; $i++) {
            $wh = $this->calcRect($cText[$i]);
            if ($this->nMaxWidth < $wh[0]) {
                $this->nMaxWidth = $wh[0];
            }
            $this->nMinWidth = $this->nMaxWidth;
            if ($this->nMinWidth > $wh[0]) {
                $this->nMinWidth = $wh[0];
            }
            if ($this->nMaxHeight < $wh[1]) {
                $this->nMaxHeight = $wh[1];
            }
        }
    }

    /**
     * @param string $cText
     * @return string
     */
    public function getCacheFilePath($cText)
    {
        return $this->cCacheDir . '/' . md5($this->cFont . $this->nSize . $this->cColor . $cText) . '.png';
    }

    /**
     * @param string $char
     * @return string
     */
    public function getImageFilePath($char)
    {
        $calcFor = $char;
        //workaround: character '1' seems to be calculated wrong
        if ($char === '1' && $this->cFont === 'GeosansLight.ttf') {
            $calcFor = 'd';
        }
        $wh     = $this->calcRect($calcFor);
        $cColor = $this->html2rgb($this->cColor);
        if (!is_array($cColor)) {
            $cColor = [0, 0, 0];
        }
        $filePath = $this->getCacheFilePath($char);
        if (!is_file($filePath)) {
            $image      = imagecreate($wh[0] + $this->nPadding, $this->nMaxHeight + $this->nPadding);
            $background = imagecolorallocate($image, 255, 255, 255);
            $cTextColor = imagecolorallocate($image, $cColor[0], $cColor[1], $cColor[2]);
            imagecolortransparent($image, $background);
            imagettftext(
                $image,
                $this->nSize,
                0,
                $wh[2] + $this->nPadding / 2,
                $this->nMaxHeight + $this->nPadding / 2,
                $cTextColor,
                $this->cFontDir . '/' . $this->cFont,
                $char
            );
            imagepng($image, PFAD_ROOT . $filePath);
        }

        return $filePath;
    }

    /**
     * @param string $cText
     * @return array
     */
    public function asArray($cText)
    {
        $list = [];
        $this->calcMax($cText);
        $max = strlen($cText);

        for ($i = 0; $i < $max; $i++) {
            if ($cText[$i] === ' ') {
                $list[] = false;
            } else {
                $list[] = $this->getImageFilePath($cText[$i]);
            }
        }

        return $list;
    }

    /**
     * @param string $cText
     * @return string
     */
    public function asHTML($cText)
    {
        $html  = '';
        $list  = $this->asArray($cText);
        $width = ($this->nMaxWidth + $this->nMinWidth) / 2;
        foreach ($list as $l) {
            if ($l) {
                $html .= '<img src="' . $l . '" border="0" />';
            } else {
                $html .= '<span style="display:inline-block;width:' . $width . 'px"></span>';
            }
        }

        return $html;
    }
}
