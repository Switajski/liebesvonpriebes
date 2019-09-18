<?php

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Slide
 *
 * @access public
 */
class Slide
{
    /**
     * @var int
     */
    public $kSlide;

    /**
     * @var int
     */
    public $kSlider;

    /**
     * @var string
     */
    public $cTitel;

    /**
     * @var string
     */
    public $cBild;

    /**
     * @var string
     */
    public $cText;

    /**
     * @var string
     */
    public $cThumbnail;

    /**
     * @var string
     */
    public $cLink;

    /**
     * @var int
     */
    public $nSort;

    /**
     * @var string
     */
    public $cBildAbsolut;

    /**
     * @var string
     */
    public $cThumbnailAbsolut;

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @param int $kSlider
     * @param int $kSlide
     * @return bool
     */
    public function load($kSlider = 0, $kSlide = 0)
    {
        if (
            ((int)$kSlider > 0) ||
            (!empty($this->kSlider) && (int)$this->kSlider > 0 && (int)$kSlide > 0) ||
            (!empty($this->kSlide) && (int)$this->kSlide > 0)
        ) {
            if (empty($kSlider) || (int)$kSlider === 0) {
                $kSlider = $this->kSlider;
            }
            if (empty($kSlide) || (int)$kSlide === 0) {
                $kSlide = $this->kSlide;
            }

            $oSlide = Shop::DB()->select('tslide', 'kSlide', (int)$kSlide);

            if (is_object($oSlide)) {
                $cSlide_arr = (array)$oSlide;
                $this->set($cSlide_arr);

                return true;
            }
        }

        return false;
    }

    /**
     * @param array $cData_arr
     * @return $this
     */
    public function set(array $cData_arr)
    {
        $cObjectFields_arr = get_class_vars('Slide');
        foreach ($cObjectFields_arr as $cField => $cValue) {
            if (isset($cData_arr[$cField])) {
                $this->$cField = $cData_arr[$cField];
            }
        }
        $this->setAbsoluteImagePaths();

        return $this;
    }

    /**
     * @return $this
     */
    private function setAbsoluteImagePaths()
    {
        $shopURL                 = Shop::getURL();
        $this->cBildAbsolut      = $shopURL . '/' . PFAD_MEDIAFILES . str_replace($shopURL . '/' . PFAD_MEDIAFILES, '', $this->cBild);
        $this->cThumbnailAbsolut = $shopURL . '/' . PFAD_MEDIAFILES . str_replace($shopURL . '/' . PFAD_MEDIAFILES, '', $this->cThumbnail);

        return $this;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!empty($this->cBild)) {
            $cShopUrl  = parse_url(Shop::getURL(), PHP_URL_PATH);
            $cShopUrl2 = parse_url(URL_SHOP, PHP_URL_PATH);
            if (strrpos($cShopUrl, '/') !== (strlen($cShopUrl) - 1)) {
                $cShopUrl .= '/';
            }
            if (strrpos($cShopUrl2, '/') !== (strlen($cShopUrl2) - 1)) {
                $cShopUrl2 .= '/';
            }
            $cPfad  = $cShopUrl . PFAD_MEDIAFILES;
            $cPfad2 = $cShopUrl2 . PFAD_MEDIAFILES;
            if (strpos($this->cBild, $cPfad) !== false) {
                $nStrLength       = strlen($cPfad);
                $this->cBild      = substr($this->cBild, $nStrLength);
                $this->cThumbnail = '.thumbs/' . $this->cBild;
            } elseif (strpos($this->cBild, $cPfad2) !== false) {
                $nStrLength       = strlen($cPfad2);
                $this->cBild      = substr($this->cBild, $nStrLength);
                $this->cThumbnail = '.thumbs/' . $this->cBild;
            }
        }

        if ($this->kSlide !== null) {
            return $this->update();
        }

        return $this->append();
    }

    /**
     * @return int
     */
    private function update()
    {
        $oSlide = clone $this;
        if (empty($oSlide->cThumbnail)) {
            unset($oSlide->cThumbnail);
        }
        unset($oSlide->cBildAbsolut, $oSlide->cThumbnailAbsolut, $oSlide->kSlide);

        return Shop::DB()->update('tslide', 'kSlide', (int)$this->kSlide, $oSlide);
    }

    /**
     * @return bool
     */
    private function append()
    {
        if (!empty($this->cBild)) {
            $oSlide = clone $this;
            unset($oSlide->cBildAbsolut, $oSlide->cThumbnailAbsolut, $oSlide->kSlide);
            if ($this->nSort === null) {
                $oSort = Shop::DB()->query("
                SELECT nSort
                    FROM tslide
                    WHERE kSlider = " . $this->kSlider . "
                    ORDER BY nSort DESC LIMIT 1", 1
                );
                $oSlide->nSort = (!is_object($oSort) || $oSort->nSort == 0) ? 1 : ($oSort->nSort + 1);
            }
            $kSlide = Shop::DB()->insert('tslide', $oSlide);
            if ((int)$kSlide > 0) {
                $this->kSlide = $kSlide;

                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        if ($this->kSlide !== null && (int)$this->kSlide > 0) {
            $bSuccess = Shop::DB()->delete('tslide', 'kSlide', (int)$this->kSlide);

            return (int)$bSuccess !== 0;
        }

        return false;
    }
}
