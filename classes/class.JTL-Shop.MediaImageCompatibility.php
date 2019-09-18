<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MediaImageCompatibility
 */
class MediaImageCompatibility implements IMedia
{
    const REGEX = '/^bilder\/produkte\/(?P<size>mini|klein|normal|gross)\/(?P<path>(?P<name>[a-zA-Z0-9\-_]+)\.(?P<ext>jpg|jpeg|png|gif))$/';

    /**
     * @param string $request
     * @return bool
     */
    public function isValid($request)
    {
        return in_array((int)IMAGE_COMPATIBILITY_LEVEL, [1, 2], true)
            && $this->parse($request) !== null;
    }

    /**
     * @param string $request
     * @return mixed
     */
    public function handle($request)
    {
        $req      = $this->parse($request);
        $path     = strtolower($req['path']);
        $fallback = Shop::DB()->executeQueryPrepared(
            "SELECT h.kArtikel, h.nNr, a.cSeo, a.cName, a.cArtNr, a.cBarcode 
                FROM tartikelpicthistory h 
                INNER JOIN tartikel a 
                  ON h.kArtikel = a.kArtikel 
                  WHERE LOWER(h.cPfad) = :path",
            ['path' => $path],
            1
        );

        if (is_object($fallback)) {
            $req['number'] = (int)$fallback->nNr;
        } elseif ((int)IMAGE_COMPATIBILITY_LEVEL === 2) {
            $name = $req['name'];
            // remove number
            if (preg_match('/^(.*)_b?(\d+)$/', $name, $matches)) {
                $name          = $matches[1];
                $req['number'] = (int)$matches[2];
            }

            $articleNumber = $barcode = $seo = $name;
            // remove concat
            $exploded = explode('_', $name, 2);
            if (count($exploded) === 2) {
                $articleNumber = $exploded[0];
                $barcode       = $seo       = $name       = $exploded[1];
            }
            // replace vowel mutation
            $name          = str_replace('-', ' ', $this->replaceVowelMutation($name));
            $articleNumber = $this->replaceVowelMutation($articleNumber);
            $barcode       = $this->replaceVowelMutation($barcode);
            // lowercase + escape
            $name          = strtolower(Shop::DB()->escape($name));
            $articleNumber = strtolower(Shop::DB()->escape($articleNumber));
            $barcode       = strtolower(Shop::DB()->escape($barcode));
            $seo           = strtolower(Shop::DB()->escape($seo));

            $fallback = Shop::DB()->executeQuery("
              SELECT a.kArtikel, a.cSeo, a.cName, a.cArtNr, a.cBarcode 
              FROM tartikel a 
              WHERE 
                  LOWER(a.cName) = '{$name}' OR 
                  LOWER(a.cSeo) = '{$seo}' OR
                  LOWER(a.cBarcode) = '{$barcode}' OR 
                  LOWER(a.cArtNr) = '{$articleNumber}'", 1
            );
        }

        if (is_object($fallback) && (int)$fallback->kArtikel > 0) {
            $number   = isset($req['number']) ? (int)$req['number'] : 1;
            $thumbUrl = Shop::getURL() . '/' . MediaImage::getThumb(Image::TYPE_PRODUCT, $fallback->kArtikel, $fallback, Image::mapSize($req['size']), $number);

            http_response_code(301);
            header("Location: {$thumbUrl}");
            exit;
        }

        return false;
    }

    /**
     * @param string $str
     * @return string
     */
    private function replaceVowelMutation($str)
    {
        $src = ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü', utf8_decode('ä'), utf8_decode('ö'), utf8_decode('ü'), utf8_decode('ß'), utf8_decode('Ä'), utf8_decode('Ö'), utf8_decode('Ü')];
        $rpl = ['ae', 'oe', 'ue', 'ss', 'AE', 'OE', 'UE', 'ae', 'oe', 'ue', 'ss', 'AE', 'OE', 'UE'];

        return str_replace($rpl, $src, $str);
    }

    /**
     * @param string $request
     * @return array|null
     */
    private function parse($request)
    {
        if (!is_string($request) || strlen($request) == 0) {
            return null;
        }

        if ($request[0] === '/') {
            $request = substr($request, 1);
        }

        if (preg_match(self::REGEX, $request, $matches)) {
            return array_intersect_key($matches, array_flip(array_filter(array_keys($matches), 'is_string')));
        }

        return null;
    }
}
