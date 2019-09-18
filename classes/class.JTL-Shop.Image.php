<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
use Imanee\Imanee;

require_once __DIR__ . '/core/class.core.Shop.php';

/**
 * Class Image
 */
class Image
{
    /**
     * Image types
     */
    const TYPE_PRODUCT         = 'product';
    const TYPE_CATEGORY        = 'category';
    const TYPE_CONFIGGROUP     = 'configgroup';
    const TYPE_VARIATION       = 'variation';
    const TYPE_MANUFACTURER    = 'manufacturer';
    const TYPE_ATTRIBUTE       = 'attribute';
    const TYPE_ATTRIBUTE_VALUE = 'attributevalue';

    /**
     * Image sizes
     */
    const SIZE_XS = 'xs';
    const SIZE_SM = 'sm';
    const SIZE_MD = 'md';
    const SIZE_LG = 'lg';

    /**
     * Image type map
     *
     * @var array
     */
    private static $typeMapper = [
        'artikel'      => self::TYPE_PRODUCT,
        'produkte'     => self::TYPE_PRODUCT,
        'kategorien'   => self::TYPE_CATEGORY,
        'kategorie'    => self::TYPE_CATEGORY,
        'konfigurator' => self::TYPE_CONFIGGROUP,
        'variationen'  => self::TYPE_VARIATION,
        'hersteller'   => self::TYPE_MANUFACTURER,
        'merkmale'     => self::TYPE_ATTRIBUTE,
        'merkmalwerte' => self::TYPE_ATTRIBUTE_VALUE
    ];

    /**
     * Image size map
     *
     * @var array
     */
    private static $sizeMapper = [
        'mini'   => self::SIZE_XS,
        'klein'  => self::SIZE_SM,
        'normal' => self::SIZE_MD,
        'gross'  => self::SIZE_LG
    ];

    /**
     * Image size map
     *
     * @var array
     */
    private static $positionMapper = [
        'oben'         => Imanee::IM_POS_TOP_CENTER,
        'oben-rechts'  => Imanee::IM_POS_TOP_RIGHT,
        'rechts'       => Imanee::IM_POS_MID_RIGHT,
        'unten-rechts' => Imanee::IM_POS_BOTTOM_RIGHT,
        'unten'        => Imanee::IM_POS_BOTTOM_CENTER,
        'unten-links'  => Imanee::IM_POS_BOTTOM_LEFT,
        'links'        => Imanee::IM_POS_MID_LEFT,
        'oben-links'   => Imanee::IM_POS_TOP_LEFT,
        'zentriert'    => Imanee::IM_POS_MID_CENTER
    ];

    /**
     * Image settings
     *
     * @var array
     */
    private static $settings;

    /**
     * Get image key by filepath
     *
     * @todo Support all types and map to the according table
     * @param string $path filepath
     * @param string $type produkt, hersteller, ..
     * @param int    $number
     * @return int foreign key
     */
    public static function getByPath($path, $type, $number = 1)
    {
        $number = (int)$number;
        $path   = Shop::DB()->escape($path);
        $item   = Shop::DB()->executeQueryPrepared(
            "SELECT kArtikel AS id, nNr AS number, cPfad AS path 
                FROM tartikelpict 
                WHERE cPfad = :path 
                AND nNr = :nr 
                LIMIT 1",
            ['path' => $path, 'nr' => $number],
            1
        );

        return is_object($item) ? $item : null;
    }

    /**
     * Get image key by id
     *
     * @TODO: Support all types and map to the according table
     * @todo: unsed param $type
     * @param int    $id
     * @param string $type produkt, hersteller, ..
     * @param int    $number
     * @return int foreign key
     */
    public static function getById($id, $type, $number = 1)
    {
        $id     = (int)$id;
        $number = (int)$number;
        $item   = Shop::DB()->query("
            SELECT kArtikel AS id, nNr AS number, cPfad AS path 
                FROM tartikelpict 
                WHERE kArtikel = '{$id}' 
                    AND nNr = '{$number}' 
                ORDER BY nNr LIMIT 1", 1
        );

        return is_object($item) ? $item : null;
    }

    /**
     *  Global image settings
     *
     * @return array|mixed
     */
    public static function getSettings()
    {
        if (self::$settings === null) {
            $settings = Shop::getSettings([CONF_BILDER]);
            $settings = array_shift($settings);
            $branding = self::getBranding();

            self::$settings = [
                'background' => $settings['bilder_hintergrundfarbe'],
                'container'  => $settings['container_verwenden'] === 'Y',
                'format'     => strtolower($settings['bilder_dateiformat']),
                'scale'      => $settings['bilder_skalieren'] === 'Y',
                'quality'    => (int)$settings['bilder_jpg_quali'],
                'branding'   => isset($branding[self::TYPE_PRODUCT]) ? $branding[self::TYPE_PRODUCT] : null,
                'size'       => [
                    self::SIZE_XS => [
                        'width'  => (int)$settings['bilder_artikel_mini_breite'],
                        'height' => (int)$settings['bilder_artikel_mini_hoehe']
                    ],
                    self::SIZE_SM => [
                        'width'  => (int)$settings['bilder_artikel_klein_breite'],
                        'height' => (int)$settings['bilder_artikel_klein_hoehe']
                    ],
                    self::SIZE_MD => [
                        'width'  => (int)$settings['bilder_artikel_normal_breite'],
                        'height' => (int)$settings['bilder_artikel_normal_hoehe']
                    ],
                    self::SIZE_LG => [
                        'width'  => (int)$settings['bilder_artikel_gross_breite'],
                        'height' => (int)$settings['bilder_artikel_gross_hoehe']
                    ]
                ],
                'naming'   => [
                    self::TYPE_PRODUCT   => (int)$settings['bilder_artikel_namen'],
                    self::TYPE_CATEGORY  => (int)$settings['bilder_kategorie_namen'],
                    self::TYPE_VARIATION => (int)$settings['bilder_variation_namen']
                ]
            ];
        }

        return self::$settings;
    }

    /**
     * Convert old size naming
     *
     * @param string     $size
     * @param bool|false $flip
     * @return null
     */
    public static function mapSize($size, $flip = false)
    {
        $size   = strtolower($size);
        $mapper = $flip ? array_flip(self::$sizeMapper) : self::$sizeMapper;
        if (array_key_exists($size, $mapper)) {
            return $mapper[$size];
        }

        return null;
    }

    /**
     * Convert old type naming
     *
     * @param string     $type
     * @param bool|false $flip
     * @return null
     */
    public static function mapType($type, $flip = false)
    {
        $type   = strtolower($type);
        $mapper = $flip ? array_flip(self::$typeMapper) : self::$typeMapper;
        if (array_key_exists($type, $mapper)) {
            return $mapper[$type];
        }

        return null;
    }

    /**
     * Convert old position naming
     *
     * @param string     $position
     * @param bool|false $flip
     * @return null
     */
    public static function mapPosition($position, $flip = false)
    {
        $position = strtolower($position);
        $mapper   = $flip ? array_flip(self::$positionMapper) : self::$positionMapper;
        if (array_key_exists($position, $mapper)) {
            return $mapper[$position];
        }

        return null;
    }

    /**
     * Convert old branding naming
     *
     * @todo Caching
     * @return array
     */
    private static function getBranding()
    {
        $branding    = [];
        $brandingTmp = Shop::DB()->query("SELECT tbranding.cBildKategorie 
            AS type, tbrandingeinstellung.cPosition AS position, tbrandingeinstellung.cBrandingBild AS path,
            tbrandingeinstellung.dTransparenz AS transparency, tbrandingeinstellung.dGroesse AS size
            FROM tbrandingeinstellung
            INNER JOIN tbranding ON tbrandingeinstellung.kBranding = tbranding.kBranding
            WHERE tbrandingeinstellung.nAktiv = 1", 2);

        foreach ($brandingTmp as $b) {
            $b->size            = (int)$b->size;
            $b->transparency    = (int)$b->transparency;
            $b->type            = self::mapType($b->type);
            $b->position        = self::mapPosition($b->position);
            $b->path            = PFAD_ROOT . PFAD_BRANDINGBILDER . $b->path;
            $branding[$b->type] = $b;
        }

        return $branding;
    }

    /**
     * @param string $filepath
     * @return int|string
     */
    public static function getMimeType($filepath)
    {
        $type = self::getImageType($filepath);

        return $type !== null
            ? image_type_to_mime_type($type)
            : IMAGETYPE_JPEG;
    }

    /**
     * @param string $filepath
     * @return int|null
     */
    public static function getImageType($filepath)
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($filepath);
        }

        $info = getimagesize($filepath);
        if (is_array($info) && isset($info['type'])) {
            return $info['type'];
        }

        return null;
    }

    /**
     * @param string $type
     * @param object $mixed
     * @return string
     */
    public static function getCustomName($type, $mixed)
    {
        $result   = '';
        $settings = self::getSettings();

        switch ($type) {
            case self::TYPE_PRODUCT:
                switch ($settings['naming']['product']) {
                    case 0:
                        $result = $mixed->kArtikel;
                        break;
                    case 1:
                        $result = $mixed->cArtNr;
                        break;
                    case 2:
                        $result = empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo;
                        break;
                    case 3:
                        $result = sprintf('%s_%s', $mixed->cArtNr, empty($mixed->cSeo) ? $mixed->cName : $mixed->cSeo);
                        break;
                    case 4:
                        $result = $mixed->cBarcode;
                        break;
                }
                break;
            case self::TYPE_VARIATION:
                // todo..
                break;
            case self::TYPE_CATEGORY:
                // todo..
                break;
        }

        return empty($result) ? 'image' : self::getCleanFilename($result);
    }

    /**
     * @param string $filename
     * @return string
     */
    public static function getCleanFilename($filename)
    {
        $filename = strtolower($filename);

        $source   = ['.', ' ', '/', 'ä', 'ö', 'ü', 'ß', utf8_decode('ä'), utf8_decode('ö'), utf8_decode('ü'), utf8_decode('ß')];
        $replace  = ['-', '-', '-', 'ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue', 'ss'];
        $filename = str_replace($source, $replace, $filename);

        return preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $filename);
    }

    /**
     * @param MediaImageRequest $req
     * @param Imanee $rawImage
     * @return string
     * @throws Exception
     * @throws \Imanee\Exception\UnsupportedFormatException
     * @throws \Imanee\Exception\UnsupportedMethodException
     * @internal param string $rawPath
     */
    public static function render(MediaImageRequest $req, Imanee $rawImage = null)
    {
        $rawPath = $req->getRaw(true);

        if (!is_file($rawPath)) {
            throw new Exception(sprintf('Image "%s" does not exist', $rawPath));
        }

        $size   = $req->getSize();
        $width  = $size->getWidth();
        $height = $size->getHeight();

        $settings = self::getSettings();

        $imanee = $rawImage === null
            ? new Imanee($rawPath)
            : clone $rawImage;

        $imanee->resize($width, $height, true, $settings['scale']);

        $background = $settings['format'] === 'png'
            ? 'transparent' : $settings['background'];

        if ($settings['container'] === true) {
            $imanee = (new Imanee())
                ->newImage($width, $height, $background)
                ->setFormat($settings['format'])
                ->placeImage($imanee, Imanee::IM_POS_MID_CENTER, $width, $height);
        }
        else {
            $imanee = (new Imanee())
                ->newImage($imanee->getWidth(), $imanee->getHeight(), $background)
                ->setFormat($settings['format'])
                ->placeImage($imanee, Imanee::IM_POS_MID_CENTER, $imanee->getWidth(), $imanee->getHeight());
        }

        if (isset($settings['branding']) && $req->getSize()->getType() === self::SIZE_LG) {
            $branding   = $settings['branding'];
            $brandImage = new Imanee($branding->path);
            $brandSize  = $brandImage->getSize();

            $containerImage = (new Imanee())
                ->newImage($brandSize['width'], $brandSize['height'], 'transparent')
                ->setFormat('png')
                ->placeImage($brandImage, Imanee::IM_POS_MID_CENTER);

            if ($branding->size > 0) {
                $brandWidth  = round(($imanee->getWidth() * $branding->size) / 100.0);
                $brandHeight = round(($brandWidth / $brandSize['width']) * $brandSize['height']);
                $width       = min($brandSize['width'], $brandWidth);
                $height      = min($brandSize['height'], $brandHeight);

                $containerImage->resize($width, $height, true, true);
            }
            $imanee->watermark($containerImage, $branding->position, $branding->transparency);
        }

        $req->ext  = $settings['format'];
        $thumbnail = $req->getThumb(null, true);
        $directory  = pathinfo($thumbnail, PATHINFO_DIRNAME);

        if (!is_dir($directory) && !mkdir($directory, 0777, true)) {
            $error = error_get_last();
            if (empty($error)) {
                $error = "Unable to create directory {$directory}";
            }
            throw new Exception(is_array($error) ? $error['message'] : $error);
        }

        $imanee->setFormat($settings['format']);
        $imanee->write($thumbnail, $settings['quality']);

        executeHook(HOOK_IMAGE_RENDER, [
            'imanee'   => $imanee,
            'settings' => $settings,
            'path'     => $thumbnail
        ]);

        return $imanee;
    }

    /**
     * @param MediaImageRequest $req
     * @param null $error
     * @throws \Imanee\Exception\UnsupportedMethodException
     * @return Imanee
     */
    public static function error(MediaImageRequest $req, $error = null)
    {
        $size = $req->getSize();

        $imanee = new Imanee();
        $imanee->newImage($size->getWidth(), $size->getHeight(), '#bc3726');
        $imanee->setFormat('jpg');
        $imanee->getResource()->mime = 'image/jpg';

        $drawer = clone $imanee->getDrawer();
        $drawer->setFontColor('white');

        $imanee->setDrawer($drawer);
        $imanee->placeText($error, Imanee::IM_POS_MID_CENTER, $size->getWidth() * 0.9);

        return $imanee;
    }
}
