<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MediaImageRequest.
 */
class MediaImageRequest
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string|int
     */
    public $id;

    /**
     * @var string|string
     */
    public $name;

    /**
     * @var string
     */
    public $size;

    /**
     * @var int
     */
    public $number;

    /**
     * @var int
     */
    public $ratio;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $ext;

    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * @param array|object $mixed
     *
     * @return MediaImageRequest
     */
    public static function create($mixed)
    {
        $new = new self();

        return $new->copy($mixed, $new);
    }

    /**
     * @param array|object      $mixed
     * @param MediaImageRequest $new
     *
     * @return MediaImageRequest
     */
    public function copy(&$mixed, MediaImageRequest $new)
    {
        $mixed = (object) $mixed;
        foreach ($mixed as $property => &$value) {
            $new->$property = &$value;
            unset($mixed->$property);
        }
        unset($value);
        $mixed = null;

        return $new;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = 'image';
        }

        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return MediaImageSize
     */
    public function getSize()
    {
        return new MediaImageSize($this->size);
    }

    /**
     * @return string
     */
    public function getSizeType()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return max((int) $this->number, 1);
    }

    /**
     * @return int
     */
    public function getRatio()
    {
        return max((int) $this->ratio, 1);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (empty($this->path)) {
            $this->path = $this->getPathById();
        }

        return $this->path;
    }

    /**
     * @return string
     */
    public function getExt()
    {
        if (empty($this->ext)) {
            $info      = pathinfo($this->getPath());
            $this->ext = isset($info['extension'])
                ? $info['extension']
                : null;
        }

        return $this->ext;
    }

    /**
     * @param bool $absolute
     *
     * @return null|string
     */
    public function getRaw($absolute = false)
    {
        $path = $this->getPath();
        $path = empty($path) ? null : sprintf('%s%s', self::getStoragePath(), $path);

        return ($path !== null && $absolute === true)
            ? PFAD_ROOT . $path
            : $path;
    }

    /**
     * @param null|MediaImageSize $size
     * @param bool                $absolute
     *
     * @return string
     */
    public function getThumb($size = null, $absolute = false)
    {
        $size = $size !== null
            ? $size
            : $this->getSize();
        $number = $this->getNumber() > 1
            ? '~' . $this->getNumber()
            : '';
        $settings = Image::getSettings();
        $ext      = $this->ext ?: $settings['format'];

        $thumb = sprintf('%s/%d/%s/%s%s.%s', self::getCachePath($this->getType()), $this->getId(), $size, $this->getName(), $number, $ext);

        return ($absolute === true)
            ? PFAD_ROOT . $thumb
            : $thumb;
    }

    /**
     * @param null|string|MediaImageSize $size
     *
     * @return string
     */
    public function getFallbackThumb($size = null)
    {
        $size = $size !== null
            ? $size
            : $this->getSize();

        return sprintf('%s/%s/%s', rtrim(PFAD_PRODUKTBILDER, '/'), Image::mapSize($size, true), $this->getPath());
    }

    /**
     * @param null|string $size
     *
     * @return string
     */
    public function getThumbUrl($size = null)
    {
        return Shop::getURL() . '/' . $this->getThumb($size);
    }

    /**
     * @return string|null
     */
    public function getPathById()
    {
        $id     = $this->getId();
        $type   = $this->getType();
        $number = $this->getNumber();

        if ($path = $this->cachedPath()) {
            return $path;
        }

        $item = Shop::DB()->query("
          SELECT kArtikel AS id, nNr AS number, cPfad AS path
            FROM tartikelpict
            WHERE kArtikel = {$id} AND nNr = {$number} ORDER BY nNr LIMIT 1", 1
        );

        $path = isset($item->path)
            ? $item->path
            : null;

        $this->cachedPath($path);

        return $path;
    }

    /**
     * @return string|null
     */
    protected function cachedPath($path = null)
    {
        $hash = sprintf('%s-%s-%s', $this->getId(),
            $this->getNumber(), $this->getType());

        if ($path === null) {
            if (array_key_exists($hash, static::$cache)) {
                return static::$cache[$hash];
            }

            return;
        }

        static::$cache[$hash] = $path;
    }

    /**
     * @return string
     */
    public static function getStoragePath()
    {
        return PFAD_MEDIA_IMAGE_STORAGE;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public static function getCachePath($type)
    {
        return PFAD_MEDIA_IMAGE . $type;
    }
}
