<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
use Imanee\Imanee;

/**
 * Class MediaImage.
 */
class MediaImage implements IMedia
{
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param string $type
     * @param string $id
     * @param object $mixed
     * @param string $size
     * @param int    $number
     *
     * @return MediaImageRequest
     */
    public static function getRequest($type, $id, $mixed, $size, $number = 1)
    {
        $name = Image::getCustomName($type, $mixed);
        $req  = MediaImageRequest::create([
            'id'     => $id,
            'type'   => $type,
            'number' => $number,
            'name'   => $name,
            'size'   => $size,
        ]);

        return $req;
    }

    /**
     * @param string $type
     * @param string $id
     * @param object $mixed
     * @param string $size
     * @param int    $number
     *
     * @return string
     */
    public static function getThumb($type, $id, $mixed, $size, $number = 1)
    {
        $name     = Image::getCustomName($type, $mixed);
        $settings = Image::getSettings();
        $req      = MediaImageRequest::create([
            'id'     => $id,
            'type'   => $type,
            'number' => $number,
            'name'   => $name,
            'ext'    => $settings['format'],
        ]);
        $thumb    = $req->getThumb($size);
        $thumbAbs = PFAD_ROOT . $thumb;

        if (!file_exists($thumbAbs) && !file_exists(PFAD_ROOT . $req->getRaw())) {
            $fallback = $req->getFallbackThumb($size);
            $thumb    = file_exists(PFAD_ROOT . $fallback)
                ? $fallback : BILD_KEIN_ARTIKELBILD_VORHANDEN;
        }

        return $thumb;
    }

    /**
     * @param string $type
     * @param string $id
     * @param string $size
     * @param int    $number
     *
     * @return string
     */
    public static function getThumbUrl($type, $id, $size, $number = 1)
    {
        $req = MediaImageRequest::create([
            'type'   => $type,
            'id'     => $id,
            'number' => $number,
        ]);

        return $req->getThumbUrl($size);
    }

    /**
     * @param string $type
     * @param bool   $filesize
     *
     * @return object
     *
     * @throws Exception
     */
    public static function getStats($type, $filesize = false)
    {
        $result = (object) [
            'total'     => 0,
            'corrupted' => 0,
            'fallback'  => 0,
            'generated' => [
                Image::SIZE_XS => 0,
                Image::SIZE_SM => 0,
                Image::SIZE_MD => 0,
                Image::SIZE_LG => 0,
            ],
            'totalSize'     => 0,
            'generatedSize' => [
                Image::SIZE_XS => 0,
                Image::SIZE_SM => 0,
                Image::SIZE_MD => 0,
                Image::SIZE_LG => 0,
            ],
        ];

        $images = self::getImages($type);
        foreach ($images as $image) {
            $raw = $image->getRaw(true);
            ++$result->total;
            if (!file_exists($raw)) {
                $fallback = $image->getFallbackThumb(Image::SIZE_XS);
                if (file_exists(PFAD_ROOT . $fallback)) {
                    ++$result->fallback;
                } else {
                    ++$result->corrupted;
                }
            } else {
                foreach ([Image::SIZE_XS, Image::SIZE_SM, Image::SIZE_MD, Image::SIZE_LG] as $size) {
                    $thumb = $image->getThumb($size, true);
                    if (file_exists($thumb)) {
                        ++$result->generated[$size];
                        if ($filesize === true) {
                            $result->generatedSize[$size] = filesize($thumb);
                            $result->totalSize += $result->generatedSize[$size];
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string   $type
     * @param null|int $id
     */
    public static function clearCache($type, $id = null)
    {
        $directory = PFAD_ROOT . MediaImageRequest::getCachePath($type);
        if ($id !== null) {
            $directory = $directory . '/' . (int) $id;
        }

        try {
            $rdi = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);

            foreach (new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST) as $value) {
                $value->isFile()
                    ? @unlink($value)
                    : @rmdir($value);
            }

            if ($id !== null) {
                @rmdir($directory);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param string $request
     *
     * @return bool
     */
    public function isValid($request)
    {
        return $this->parse($request) !== null;
    }

    /**
     * @param string $imageUrl
     *
     * @return MediaImageRequest
     */
    public static function toRequest($imageUrl)
    {
        $self = new self();

        return $self->create($imageUrl);
    }

    /**
     * @param string $request
     *
     * @return mixed|void
     *
     * @throws Exception
     */
    public function handle($request)
    {
        try {
            $request  = '/' . ltrim($request, '/');
            $mediaReq = $this->create($request);

            $imgNames = Shop::DB()->executeQueryPrepared(
                "SELECT kArtikel, cName, cSeo, cArtNr, cBarcode
                    FROM tartikel AS a
                    WHERE kArtikel = :kArtikel
                UNION SELECT asp.kArtikel, asp.cName, asp.cSeo, a.cArtNr, a.cBarcode
                    FROM tartikelsprache AS asp JOIN tartikel AS a ON asp.kArtikel = a.kArtikel
                    WHERE asp.kArtikel = :kArtikel",
                ['kArtikel' => (int)$mediaReq->id],
                2
            );

            if (count($imgNames) === 0) {
                throw new Exception("No such product id: " . (int)$mediaReq->id);
            }

            $matchFound = false;

            foreach ($imgNames as $imgName) {
                $imgName->imgPath = MediaImage::getThumb(
                    $mediaReq->type, $mediaReq->id, $imgName, $mediaReq->size, $mediaReq->number
                );

                if ('/' . $imgName->imgPath === $request) {
                    $matchFound = true;
                    $thumbPath  = PFAD_ROOT . $imgName->imgPath;
                    break;
                }
            }

            if ($matchFound === false) {
                header('Location: ' . Shop::getURL() . '/' . $imgNames[0]->imgPath, true, 301);
                exit;
            }

            if (!is_file($thumbPath)) {
                Image::render($mediaReq, null);
            }

            $imanee = new Imanee($thumbPath);

            self::writeHttp($imanee);
        } catch (Exception $e) {
            $display = (string) strtolower(ini_get('display_errors'));
            if (in_array($display, ['on', '1', 'true'])) {
                $imanee = Image::error($mediaReq, $e->getMessage());

                self::writeHttp($imanee, true);
            } else {
                http_response_code(500);
                // echo $e->getTraceAsString();
            }
        }
        exit;
    }

    /**
     * @param Imanee $imanee
     * @param bool   $nocache
     */
    public static function writeHttp(Imanee $imanee, $nocache = false)
    {
        $data = $imanee->output();
        $size = strlen($data);

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Accept-Ranges: none');
        header('Content-Encoding: None', true);

        header("Content-Length: {$size}");
        header("Content-Type: {$imanee->getMime()}");

        if ($nocache === true) {
            $format   = 'D, d M Y H:i:s \G\M\T';
            $expires  = new DateTime('+1 month', new DateTimezone('UTC'));
            $modified = new DateTime('now', new DateTimezone('UTC'));

            header('Cache-Control: max-age=2592000');
            header("Expires: {$expires->format($format)}");
            header("Last-Modified: {$modified->format($format)}");
        }

        echo $data;
    }

    /**
     * @param MediaImageRequest $req
     * @param bool              $overwrite
     *
     * @return array
     */
    public static function cacheImage(MediaImageRequest $req, $overwrite = false)
    {
        $result   = [];
        $rawImage = null;
        $rawPath  = $req->getRaw(true);

        if ($overwrite === true) {
            self::clearCache($req->getType(), $req->getId());
        }

        foreach ([Image::SIZE_XS, Image::SIZE_SM, Image::SIZE_MD, Image::SIZE_LG] as $size) {
            $res = (object) [
                'success'    => true,
                'error'      => null,
                'renderTime' => 0,
                'cached'     => false,
            ];

            try {
                $req->size   = $size;
                $thumbPath   = $req->getThumb(null, true);
                $res->cached = is_file($thumbPath);

                if ($res->cached === false) {
                    $renderStart = microtime(true);
                    if ($rawImage === null) {
                        if (!is_file($rawPath)) {
                            throw new Exception(sprintf('Image "%s" does not exist', $rawPath));
                        }
                        $rawImage = new Imanee($rawPath);
                    }
                    Image::render($req, $rawImage);
                    $res->renderTime = (microtime(true) - $renderStart) * 1000;
                }
            } catch (Exception $e) {
                $res->success = false;
                $res->error   = $e->getMessage();
            }

            $result[$size] = $res;
        }

        if ($rawImage !== null) {
            unset($rawImage);
        }

        return $result;
    }

    /**
     * @param string   $type
     * @param bool     $notCached
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return MediaImageRequest[]
     *
     * @throws Exception
     */
    public static function getImages($type, $notCached = false, $offset = null, $limit = null)
    {
        $requests = [];
        switch ($type) {
            case Image::TYPE_PRODUCT:
                //only select the necessary columns to save memory
                $cols = '';
                $conf = Image::getSettings();
                switch ($conf['naming']['product']) {
                    case 0:
                        break;
                    case 1:
                        $cols = ', tartikel.cArtNr';
                        break;
                    case 2:
                        $cols = ', tartikel.cSeo, tartikel.cName';
                        break;
                    case 3:
                        $cols = ', tartikel.cArtNr, tartikel.cSeo, tartikel.cName';
                        break;
                    case 4:
                        $cols = ', tartikel.cBarcode';
                        break;
                    default:
                        break;
                }
                $limitStmt = '';
                if ($limit !== null) {
                    $limitStmt = ' LIMIT ';
                    if ($offset !== null) {
                        $limitStmt .= (int) $offset . ', ';
                    }
                    $limitStmt .= (int) $limit;
                }
                $images = Shop::DB()->query('
                    SELECT tartikelpict.cPfad AS path, tartikelpict.nNr AS number, tartikelpict.kArtikel ' . $cols . '
                        FROM tartikelpict
                        INNER JOIN tartikel
                          ON tartikelpict.kArtikel = tartikel.kArtikel' . $limitStmt, 10);
                break;

            default:
                throw new Exception('Not implemented');
        }

        while ($image = $images->fetch(PDO::FETCH_OBJ)) {
            $req = MediaImageRequest::create([
                'id'     => $image->kArtikel,
                'type'   => $type,
                'name'   => Image::getCustomName($type, $image),
                'number' => $image->number,
                'path'   => $image->path,
            ]);

            if ($notCached && self::isCached($req)) {
                continue;
            }

            $requests[] = $req;
        }

        return $requests;
    }

    /**
     * @param MediaImageRequest $req
     *
     * @return bool
     */
    public static function isCached(MediaImageRequest $req)
    {
        return file_exists($req->getThumb(Image::SIZE_XS, true)) &&
                file_exists($req->getThumb(Image::SIZE_SM, true)) &&
                file_exists($req->getThumb(Image::SIZE_MD, true)) &&
                file_exists($req->getThumb(Image::SIZE_LG, true));
    }

    /**
     * @param string $request
     *
     * @return array|null
     */
    private function parse($request)
    {
        if (!is_string($request) || strlen($request) === 0) {
            return;
        }

        if ($request[0] === '/') {
            $request = substr($request, 1);
        }

        if (preg_match(MEDIAIMAGE_REGEX, $request, $matches)) {
            return array_intersect_key($matches, array_flip(array_filter(array_keys($matches), 'is_string')));
        }

        return;
    }

    /**
     * @param string $request
     *
     * @return MediaImageRequest
     */
    private function create($request)
    {
        $matches = $this->parse($request);

        return MediaImageRequest::create($matches);
    }

    public static function getPrimaryNumber($type, $id)
    {
        $prepared = self::getImageStmt($type, $id);
        if ($prepared !== null) {
            $primary = Shop::DB()->queryPrepared($prepared->stmt, $prepared->bind, 1);
            if (is_object($primary)) {
                return max(1, (int) $primary->number);
            }
        }

        return;
    }

    /**
     * @param string $type
     * @param int    $id
     *
     * @return bool
     */
    public static function getImageStmt($type, $id)
    {
        $id = (int) $id;
        switch ($type) {
            case Image::TYPE_PRODUCT:
                $res = ['stmt' => 'SELECT kArtikel, nNr AS number FROM tartikelpict WHERE kArtikel = :kArtikel GROUP BY cPfad ORDER BY nNr ASC', 'bind' => ['kArtikel' => $id]];
                break;
            case Image::TYPE_CATEGORY:
                $res = ['stmt' => 'SELECT kKategorie, 0 AS number FROM tkategoriepict WHERE kKategorie = :kKategorie', 'bind' => ['kKategorie' => $id]];
                break;
            case Image::TYPE_CONFIGGROUP:
                $res = ['stmt' => 'SELECT cBildpfad, 0 AS number FROM tkonfiggruppe WHERE kKonfiggruppe = :kKonfiggruppe ORDER BY nSort ASC', 'bind' => ['kKonfiggruppe' => $id]];
                break;
            case Image::TYPE_VARIATION:
                $res = ['stmt' => 'SELECT kEigenschaftWert, 0 AS number FROM teigenschaftwertpict WHERE kEigenschaftWert = :kEigenschaftWert', 'bind' => ['kEigenschaftWert' => $id]];
                break;
            case Image::TYPE_MANUFACTURER:
                $res = ['stmt' => 'SELECT cBildpfad, 0 AS number FROM thersteller WHERE kHersteller = :kHersteller', 'bind' => ['kHersteller' => $id]];
                break;
            case Image::TYPE_ATTRIBUTE:
                $res = ['stmt' => 'SELECT cBildpfad, 0 AS number FROM tmerkmal WHERE kMerkmal = :kMerkmal ORDER BY nSort ASC', 'bind' => ['kMerkmal' => $id]];
                break;
            case Image::TYPE_ATTRIBUTE_VALUE:
                $res = ['stmt' => 'SELECT cBildpfad, 0 AS number FROM tmerkmalwert WHERE kMerkmalWert = :kMerkmalWert ORDER BY nSort ASC', 'bind' => ['kMerkmalWert' => $id]];
                break;
            default:
                return;
        }

        return (object) $res;
    }

    /**
     * @param string $type
     * @param int    $id
     *
     * @return bool
     */
    public static function imageCount($type, $id)
    {
        $id       = (int) $id;
        $prepared = static::getImageStmt($type, $id);

        if ($prepared !== null) {
            $imageCount = Shop::DB()->queryPrepared($prepared->stmt, $prepared->bind, 3);

            return is_numeric($imageCount) ? (int) $imageCount : 0;
        }

        return 0;
    }

    /**
     * @param string $type
     * @param int    $id
     *
     * @return bool
     */
    public static function hasImage($type, $id)
    {
        return static::imageCount($type, $id) > 0;
    }

    /**
     * @param string $type
     * @param string $id
     * @param object $mixed
     * @param string $size
     * @param int    $number
     *
     * @return string
     */
    public static function getRawOrFilesize($type, $id, $mixed, $size, $number = 1)
    {
        $name     = Image::getCustomName($type, $mixed);
        $settings = Image::getSettings();
        $req      = MediaImageRequest::create([
            'id'     => $id,
            'type'   => $type,
            'number' => $number,
            'name'   => $name,
            'ext'    => $settings['format'],
        ]);
        $thumb    = $req->getThumb($size);
        $thumbAbs = PFAD_ROOT . $thumb;

        if (!file_exists($thumbAbs) && !file_exists(PFAD_ROOT . $req->getRaw())) {
            $fallback = $req->getFallbackThumb($size);
            $thumb    = file_exists(PFAD_ROOT . $fallback)
                ? PFAD_ROOT . $fallback
                : BILD_KEIN_ARTIKELBILD_VORHANDEN;

            return filesize($thumb);
        }

        return $req->getPath();
    }
}
