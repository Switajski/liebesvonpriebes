<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
use Imanee\Imanee;

require_once __DIR__ . '/class.JTL-Shop.Image.php';

/**
 * Class ImageStorage
 */
class ImageStorage
{
    /**
     * @var ImageReq
     */
    private $req;

    /**
     * @param ImageReq $req
     */
    public function __construct(ImageReq $req)
    {
        $this->req = $req;
    }

    /**
     * @throws Exception
     */
    public function output()
    {
        $thumbPath = $this->req->getThumbPath(true);
        if (!is_file($thumbPath)) {
            $oldThumbPath = $this->req->getThumbPath(true, true);
            if (is_file($oldThumbPath)) {
                $thumbPath = $oldThumbPath;
            } else {
                $this->render();
            }
        }
    }

    /**
     * @throws Exception
     */
    private function render()
    {
        $filepath       = ImageCache::getThumbFilePath($this->req);
        $masterFilepath = ImageCache::getMasterFilePath($this->req);

        if (!ImageCache::exists($this->req, true)) {
            throw new Exception(sprintf('Master image "%s" does not exist', $masterFilepath));
        }

        $size     = $this->req->getSize();
        $settings = Image::getSettings();
        $imanee   = new Imanee($masterFilepath);

        $width = $settings['scale']
            ? $size->getWidth()
            : min($size->getWidth(), $imanee->getWidth());

        $height = $settings['scale']
            ? $size->getHeight()
            : min($size->getHeight(), $imanee->getHeight());

        $imanee->resize($width, $height);

        if (isset($settings['branding']) & $this->req->getSize()->getType() === Image::SIZE_LG) {
            $branding   = $settings['branding'];
            $brandImage = new Imanee($branding->path);

            if ($branding->size > 0) {
                $brandWidth  = round(($imanee->getWidth() * $branding->size) / 100.0);
                $brandHeight = round(($brandWidth / $brandImage->getWidth()) * $brandImage->getHeight());

                $width  = min($brandImage->getWidth(), $brandWidth);
                $height = min($brandImage->getHeight(), $brandHeight);

                $brandImage->resize($width, $height);
            }

            $imanee->watermark($brandImage, $branding->position, $branding->transparency);
        }

        $imanee->write($filepath, $settings['quality']);
    }

    /**
     *
     */
    private function pushHeader()
    {
        $filepath       = ImageCache::getThumbFilePath($this->req);
        $masterFilepath = ImageCache::getMasterFilePath($this->req);

        $filesize         = filesize($filepath);
        $lastModifiedDate = filemtime($masterFilepath);
        $tagHash          = md5($lastModifiedDate . $filepath);
        $imageHash        = $this->req->getHash(true);
        $gmtDateTime      = gmdate('D, d M Y H:i:s', $lastModifiedDate) . ' GMT';
        $mimeType         = Image::getMimeType($filepath);

        header("Content-Type: {$mimeType}");
        header("Content-Length: {$filesize}");
        header("Cache-Control: public");
        header("Etag: {$tagHash}");
        header("Last-Modified: {$gmtDateTime}");
        header("X-Image-Hash: {$imageHash}");

        $httpModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            : null;

        $httpNoneMatch = isset($_SERVER['HTTP_IF_NONE_MATCH'])
            ? trim($_SERVER['HTTP_IF_NONE_MATCH'])
            : null;

        if ($httpModifiedSince == $lastModifiedDate || $httpNoneMatch == $tagHash) {
            header(makeHTTPHeader(304));
            exit;
        }
    }
}
