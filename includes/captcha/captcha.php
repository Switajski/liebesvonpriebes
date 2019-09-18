<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include __DIR__ . '/../config.JTL-Shop.ini.php';

/**
 * @param string $font
 * @param string $text
 * @param int    $sec
 * @return resource
 */
function erstelleCaptcha($font, $text, $sec)
{
    $font = PFAD_ROOT . 'includes/captcha/' . $font;
    $text = strtoupper($text);
    $im   = imagecreatetruecolor(200, 60);
    imagefilledrectangle($im, 0, 0, 199, 59, imagecolorallocate($im, 255, 255, 255));

    if ($sec >= 3) {
        for ($i = 0; $i < 8; $i++) {
            imageline(
                $im,
                rand(0, 200),
                rand(0, 60),
                rand(0, 200),
                rand(0, 60),
                imagecolorallocate($im, rand(0, 230), rand(0, 230),rand(0, 230))
            );
        }
    }

    imagettftext(
        $im,
        35,
        rand(-20, 20),
        20,
        40,
        imagecolorallocate($im, rand(0, 215), rand(0, 215), rand(0, 215)),
        $font, 
        $text{0}
    );
    imagettftext(
        $im,
        35,
        rand(-20, 20),
        70,
        40,
        imagecolorallocate($im, rand(0, 215), rand(0, 215), rand(0, 215)),
        $font, 
        $text{1}
    );
    imagettftext(
        $im,
        35,
        rand(-20, 20),
        110,
        40,
        imagecolorallocate($im, rand(0, 215), rand(0, 215), rand(0, 215)),
        $font,
        $text{2}
    );
    imagettftext(
        $im,
        35,
        rand(-20, 20),
        150,
        40,
        imagecolorallocate($im, rand(0, 215), rand(0, 215), rand(0, 215)),
        $font,
        $text{3}
    );

    if ($sec >= 3) {
        for ($i = 0; $i < 8; $i++) {
            imageline(
                $im, 
                rand(0, 200), 
                rand(0, 60), 
                rand(0, 200),
                rand(0, 60), 
                imagecolorallocate($im, rand(0, 250), rand(0, 250), rand(0, 250))
            );
        }
    }

    return $im;
}

$fonts  = [];
$folder = dir('ttf/');
while ($font = $folder->read()) {
    if (stripos($font, '.ttf') !== false) {
        $fonts[] = $font;
    }
}
$folder->close();

/**
 * @param string $encoded
 * @return string
 */
function decodeCode($encoded)
{
    $encoded = (string)$encoded;
    if (!$encoded) {
        return '0';
    }

    $key  = BLOWFISH_KEY;
    $mod1 = (ord($key[0]) + ord($key[1]) + ord($key[2])) % 9 + 1;
    $mod2 = strlen($_SERVER['DOCUMENT_ROOT']) % 9 + 1;

    $s1e = (int)substr($encoded, 12, 3) + $mod2 - $mod1 - 123;
    $s2e = (int)substr($encoded, 15, 3) + $mod1 - $mod2 - 234;
    $s3e = (int)substr($encoded, 3, 3) - $mod1 - 345;
    $s4e = (int)substr($encoded, 7, 3) - $mod2 - 456;

    return chr($s1e) . chr($s2e) . chr($s3e) . chr($s4e);
}

header('Content-type: image/png');
$im = erstelleCaptcha('ttf/' . $fonts[array_rand($fonts)], decodeCode($_GET['c']), $_GET['s']);
imagepng($im);
