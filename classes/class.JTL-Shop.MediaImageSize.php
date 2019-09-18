<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MediaImageSize
 */
class MediaImageSize
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        if ($this->width === null) {
            $this->width = $this->getSize('width');
        }

        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        if ($this->height === null) {
            $this->height = $this->getSize('height');
        }

        return $this->height;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return mixed
     */
    private function getSize($type)
    {
        $settings = Image::getSettings();

        return $settings['size'][$this->type][$type];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s', $this->getType());
    }
}
