<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class StringHandler
 */
class StringHandler
{
    /**
     * @param string $cString
     * @param int    $cFlag
     * @param string $cEncoding
     * @return string
     */
    public static function htmlentities($cString, $cFlag = ENT_COMPAT, $cEncoding = JTL_CHARSET)
    {
        return htmlentities($cString, $cFlag, $cEncoding);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function unhtmlentities($string)
    {
        // replace numeric entities
        $string = preg_replace_callback(
            '~&#x([0-9a-fA-F]+);~i',
            function ($x) {
                return chr(hexdec($x[1]));
            },
            $string
        );
        $string = preg_replace_callback(
            '~&#([0-9]+);~',
            function ($x) {
                return chr($x[1]);
            },
            $string
        );

        return self::htmlentitydecode($string);
    }

    /**
     * @param string $cString
     * @param int    $cFlag
     * @param string $cEncoding
     * @return string
     */
    public static function htmlspecialchars($cString, $cFlag = ENT_COMPAT, $cEncoding = JTL_CHARSET)
    {
        return htmlspecialchars($cString, $cFlag, $cEncoding);
    }

    /**
     * @param string $cString
     * @param int    $cFlag
     * @param string $cEncoding
     * @return string
     */
    public static function htmlentitydecode($cString, $cFlag = ENT_COMPAT, $cEncoding = JTL_CHARSET)
    {
        return html_entity_decode($cString, $cFlag, $cEncoding);
    }

    /**
     * @param int    $cFlag
     * @param string $cEncoding
     * @return array
     */
    public static function gethtmltranslationtable($cFlag = ENT_QUOTES, $cEncoding = JTL_CHARSET)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return get_html_translation_table(HTML_ENTITIES, $cFlag, $cEncoding);
        }

        return get_html_translation_table(HTML_ENTITIES);
    }

    /**
     * @param string|array $input
     * @param int          $nSuche
     * @return mixed|string
     */
    public static function filterXSS($input, $nSuche = 0)
    {
        if (is_array($input)) {
            foreach ($input as &$a) {
                $a = self::filterXSS($a);
            }

            return $input;
        }
        $cString = trim(strip_tags($input));
        $cString = ($nSuche == 1) ?
            str_replace(['\\\'', '\\'], '', $cString) :
            str_replace(['\"', '\\\'', '\\', '"', '\''], '', $cString);

        if (strlen($cString) > 10 && $nSuche == 1) {
            $cString = substr(str_replace(['(', ')', ';'], '', $cString), 0, 50);
        }

        return $cString;
    }

    /**
     * check if string already is utf8 encoded
     *
     * @source http://w3.org/International/questions/qa-forms-utf-8.html
     * @param string $string
     * @return int
     */
    public static function is_utf8($string)
    {
        $res = preg_match(
            '%^(?:[\x09\x0A\x0D\x20-\x7E]  # ASCII
                                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                                |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                                |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                                |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                                |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
                            )*$%xs', $string
        );
        if ($res === false) {
            //some kind of pcre error happend - probably PREG_JIT_STACKLIMIT_ERROR.
            //we could check this via preg_last_error()
            $res = function_exists('mb_detect_encoding')
                ? (int)(mb_detect_encoding($string, 'UTF-8', true) === 'UTF-8')
                : 0;
        }

        return $res;
    }

    /**
     * @param string $data
     * @return mixed|string
     */
    public static function xssClean($data)
    {
        $convert = false;
        if (!self::is_utf8($data)) {
            //with non-utf8 input this function would return an empty string
            $convert = true;
            $data    = utf8_encode($data);
        }
        // Fix &entity\n;
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
            '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i',
            '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i',
            '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
            '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data     = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
                '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $convert ? utf8_decode($data) : $data;
    }

    /**
     * @param string $cData
     * @return string
     */
    public static function convertUTF8($cData)
    {
        return mb_convert_encoding($cData, 'UTF-8', mb_detect_encoding($cData, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
    }

    /**
     * @param string $cData
     * @return string
     */
    public static function convertISO($cData)
    {
        return mb_convert_encoding($cData, 'ISO-8859-1', mb_detect_encoding($cData, 'UTF-8, ISO-8859-1, ISO-8859-15', true));
    }

    /**
     * @param string $ISO
     * @return mixed
     */
    public static function convertISO2ISO639($ISO)
    {
        $cISO_arr = self::getISOMappings();

        return $cISO_arr[$ISO];
    }

    /**
     * @param string $ISO
     * @return int|string
     */
    public static function convertISO6392ISO($ISO)
    {
        $cISO_arr = self::getISOMappings();
        foreach ($cISO_arr as $cISO639 => $cISO) {
            if (strtolower($cISO) == strtolower($ISO)) {
                return $cISO639;
            }
        }

        return $ISO;
    }

    /**
     * @return array
     */
    public static function getISOMappings()
    {
        $cIso639_2To639_1 = [
            'aar' => 'aa', // Afar
            'abk' => 'ab', // Abkhazian
            'afr' => 'af', // Afrikaans
            'aka' => 'ak', // Akan
            'alb' => 'sq', // Albanian
            'amh' => 'am', // Amharic
            'ara' => 'ar', // Arabic
            'arg' => 'an', // Aragonese
            'arm' => 'hy', // Armenian
            'asm' => 'as', // Assamese
            'ava' => 'av', // Avaric
            'ave' => 'ae', // Avestan
            'aym' => 'ay', // Aymara
            'aze' => 'az', // Azerbaijani
            'bak' => 'ba', // Bashkir
            'bam' => 'bm', // Bambara
            'baq' => 'eu', // Basque
            'bel' => 'be', // Belarusian
            'ben' => 'bn', // Bengali
            'bih' => 'bh', // Bihari languages
            'bis' => 'bi', // Bislama
            'bos' => 'bs', // Bosnian
            'bre' => 'br', // Breton
            'bul' => 'bg', // Bulgarian
            'bur' => 'my', // Burmese
            'cat' => 'ca', // Catalan; Valencian
            'cze' => 'cs', // Czech
            'cha' => 'ch', // Chamorro
            'che' => 'ce', // Chechen
            'chi' => 'zh', // Chinese
            'chu' => 'cu', // Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic
            'chv' => 'cv', // Chuvash
            'cor' => 'kw', // Cornish
            'cos' => 'co', // Corsican
            'cre' => 'cr', // Cree
            'dan' => 'da', // Danish
            'div' => 'dv', // Divehi; Dhivehi; Maldivian
            'dut' => 'nl', // Dutch; Flemish
            'dzo' => 'dz', // Dzongkha
            'eng' => 'en', // English
            'epo' => 'eo', // Esperanto
            'est' => 'et', // Estonian
            'ewe' => 'ee', // Ewe
            'fao' => 'fo', // Faroese
            'fij' => 'fj', // Fijian
            'fin' => 'fi', // Finnish
            'fre' => 'fr', // French
            'fry' => 'fy', // Western Frisian
            'ful' => 'ff', // Fulah
            'geo' => 'ka', // Georgian
            'ger' => 'de', // German
            'gla' => 'gd', // Gaelic; Scottish Gaelic
            'gle' => 'ga', // Irish
            'glg' => 'gl', // Galician
            'glv' => 'gv', // Manx
            'gre' => 'el', // Greek, Modern (1453-)
            'grn' => 'gn', // Guarani
            'guj' => 'gu', // Gujarati
            'hat' => 'ht', // Haitian; Haitian Creole
            'hau' => 'ha', // Hausa
            'heb' => 'he', // Hebrew
            'her' => 'hz', // Herero
            'hin' => 'hi', // Hindi
            'hmo' => 'ho', // Hiri Motu
            'hrv' => 'hr', // Croatian
            'hun' => 'hu', // Hungarian
            'ibo' => 'ig', // Igbo
            'ice' => 'is', // Icelandic
            'ido' => 'io', // Ido
            'iii' => 'ii', // Sichuan Yi; Nuosu
            'iku' => 'iu', // Inuktitut
            'ile' => 'ie', // Interlingue; Occidental
            'ina' => 'ia', // Interlingua (International Auxiliary Language Association)
            'ind' => 'id', // Indonesian
            'ipk' => 'ik', // Inupiaq
            'ita' => 'it', // Italian
            'jav' => 'jv', // Javanese
            'jpn' => 'ja', // Japanese
            'kal' => 'kl', // Kalaallisut; Greenlandic
            'kan' => 'kn', // Kannada
            'kas' => 'ks', // Kashmiri
            'kau' => 'kr', // Kanuri
            'kaz' => 'kk', // Kazakh
            'khm' => 'km', // Central Khmer
            'kik' => 'ki', // Kikuyu; Gikuyu
            'kin' => 'rw', // Kinyarwanda
            'kir' => 'ky', // Kirghiz; Kyrgyz
            'kom' => 'kv', // Komi
            'kon' => 'kg', // Kongo
            'kor' => 'ko', // Korean
            'kua' => 'kj', // Kuanyama; Kwanyama
            'kur' => 'ku', // Kurdish
            'lao' => 'lo', // Lao
            'lat' => 'la', // Latin
            'lav' => 'lv', // Latvian
            'lim' => 'li', // Limburgan; Limburger; Limburgish
            'lin' => 'ln', // Lingala
            'lit' => 'lt', // Lithuanian
            'ltz' => 'lb', // Luxembourgish; Letzeburgesch
            'lub' => 'lu', // Luba-Katanga
            'lug' => 'lg', // Ganda
            'mac' => 'mk', // Macedonian
            'mah' => 'mh', // Marshallese
            'mal' => 'ml', // Malayalam
            'mao' => 'mi', // Maori
            'mar' => 'mr', // Marathi
            'may' => 'ms', // Malay
            'mlg' => 'mg', // Malagasy
            'mlt' => 'mt', // Maltese
            'mon' => 'mn', // Mongolian
            'nau' => 'na', // Nauru
            'nav' => 'nv', // Navajo; Navaho
            'nbl' => 'nr', // Ndebele, South; South Ndebele
            'nde' => 'nd', // Ndebele, North; North Ndebele
            'ndo' => 'ng', // Ndonga
            'nep' => 'ne', // Nepali
            'nno' => 'nn', // Norwegian Nynorsk; Nynorsk, Norwegian
            'nob' => 'nb', // Bokm?l, Norwegian; Norwegian Bokm?l
            'nor' => 'no', // Norwegian
            'nya' => 'ny', // Chichewa; Chewa; Nyanja
            'oci' => 'oc', // Occitan (post 1500)
            'oji' => 'oj', // Ojibwa
            'ori' => 'or', // Oriya
            'orm' => 'om', // Oromo
            'oss' => 'os', // Ossetian; Ossetic
            'pan' => 'pa', // Panjabi; Punjabi
            'per' => 'fa', // Persian
            'pli' => 'pi', // Pali
            'pol' => 'pl', // Polish
            'por' => 'pt', // Portuguese
            'pus' => 'ps', // Pushto; Pashto
            'que' => 'qu', // Quechua
            'roh' => 'rm', // Romansh
            'rum' => 'ro', // Romanian; Moldavian; Moldovan
            'run' => 'rn', // Rundi
            'rus' => 'ru', // Russian
            'sag' => 'sg', // Sango
            'san' => 'sa', // Sanskrit
            'sin' => 'si', // Sinhala; Sinhalese
            'slo' => 'sk', // Slovak
            'slv' => 'sl', // Slovenian
            'sme' => 'se', // Northern Sami
            'smo' => 'sm', // Samoan
            'sna' => 'sn', // Shona
            'snd' => 'sd', // Sindhi
            'som' => 'so', // Somali
            'sot' => 'st', // Sotho, Southern
            'spa' => 'es', // Spanish; Castilian
            'srd' => 'sc', // Sardinian
            'srp' => 'sr', // Serbian
            'ssw' => 'ss', // Swati
            'sun' => 'su', // Sundanese
            'swa' => 'sw', // Swahili
            'swe' => 'sv', // Swedish
            'tah' => 'ty', // Tahitian
            'tam' => 'ta', // Tamil
            'tat' => 'tt', // Tatar
            'tel' => 'te', // Telugu
            'tgk' => 'tg', // Tajik
            'tgl' => 'tl', // Tagalog
            'tha' => 'th', // Thai
            'tib' => 'bo', // Tibetan
            'tir' => 'ti', // Tigrinya
            'ton' => 'to', // Tonga (Tonga Islands)
            'tsn' => 'tn', // Tswana
            'tso' => 'ts', // Tsonga
            'tuk' => 'tk', // Turkmen
            'tur' => 'tr', // Turkish
            'twi' => 'tw', // Twi
            'uig' => 'ug', // Uighur; Uyghur
            'ukr' => 'uk', // Ukrainian
            'urd' => 'ur', // Urdu
            'uzb' => 'uz', // Uzbek
            'ven' => 've', // Venda
            'vie' => 'vi', // Vietnamese
            'vol' => 'vo', // Volapük
            'wel' => 'cy', // Welsh
            'wln' => 'wa', // Walloon
            'wol' => 'wo', // Wolof
            'xho' => 'xh', // Xhosa
            'yid' => 'yi', // Yiddish
            'yor' => 'yo', // Yoruba
            'zha' => 'za', // Zhuang; Chuang
            'zul' => 'zu'
        ];

        return $cIso639_2To639_1;
    }

    /**
     * @param string $string
     * @return string|mixed
     */
    public static function removeDoubleSpaces($string)
    {
        if (!is_string($string)) {
            return $string;
        }
        $string = preg_quote($string, '|');

        return preg_replace('|  +|', ' ', $string);
    }

    /**
     * @param string $string
     * @return mixed
     */
    public static function removeWhitespace($string)
    {
        return preg_replace('/\s+/', ' ', $string);
    }

    /**
     * Creating semicolon separated key string
     *
     * @param array $keys
     * @return string
     */
    public static function createSSK($keys)
    {
        if (!is_array($keys) || count($keys) === 0) {
            return '';
        }

        return sprintf(';%s;', implode(';', $keys));
    }

    /**
     * Parse a semicolon separated key string to an array
     *
     * @param string $ssk
     * @return array
     */
    public static function parseSSK($ssk)
    {
        if (is_string($ssk)) {
            return array_filter(explode(';', $ssk));
        }

        return [];
    }

    /**
     * @note PHP's FILTER_SANITIZE_EMAIL cannot handle unicode -
     * without idn_to_ascii (PECL) this will fail with umlaut domains
     *
     * @param string $input
     * @param bool   $validate
     * @return string|false - a filtered string or false if invalid
     */
    public static function filterEmailAddress($input, $validate = true)
    {
        if ((function_exists('mb_detect_encoding') && mb_detect_encoding($input) !== 'UTF-8') || !self::is_utf8($input)) {
            $input = utf8_encode($input);
        }
        $input     = function_exists('idn_to_ascii') ? idn_to_ascii($input) : $input;
        $sanitized = filter_var($input, FILTER_SANITIZE_EMAIL);

        return $validate
            ? filter_var($sanitized, FILTER_VALIDATE_EMAIL)
            : $sanitized;
    }

    /**
     * @note PHP's FILTER_SANITIZE_URL cannot handle unicode -
     * without idn_to_ascii (PECL) this will fail with umlaut domains
     *
     * @param string $input
     * @param bool   $validate
     * @return string|false - a filtered string or false if invalid
     */
    public static function filterURL($input, $validate = true)
    {
        if ((function_exists('mb_detect_encoding') && mb_detect_encoding($input) !== 'UTF-8') || !self::is_utf8($input)) {
            $input = utf8_encode($input);
        }
        $input     = function_exists('idn_to_ascii') ? idn_to_ascii($input) : $input;
        $sanitized = filter_var($input, FILTER_SANITIZE_URL);

        return $validate
            ? filter_var($sanitized, FILTER_VALIDATE_URL)
            : $sanitized;
    }

    /**
     * Build an URL string from a given associative array of parts according to PHP's parse_url()
     *
     * @param array $parts
     * @return string - the resulting URL
     */
    public static function buildUrl($parts)
    {
        return (isset($parts['scheme']) ? $parts['scheme'] . '://' : '') .
            (isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@' : '') .
            (isset($parts['host']) ? $parts['host'] : '') .
            (isset($parts['port']) ? ':' . $parts['port'] : '') .
            (isset($parts['path']) ? $parts['path'] : '') .
            (isset($parts['query']) ? '?' . $parts['query'] : '') .
            (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * @param String $str
     * @return String string without umlats or accents
     */
    public static function remove_accent($str)
    {
        $a = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ',
              'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ',
              'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
              'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č',
              'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě',
              'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ',
              'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ',
              'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ',
              'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř',
              'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ',
              'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ',
              'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ',
              'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $b = ['A', 'A', 'A', 'A', 'Ae', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N',
              'O', 'O', 'O', 'O', 'Oe', 'O', 'U', 'U', 'U', 'Ue', 'Y', 's', 'a', 'a', 'a', 'a', 'ae', 'a', 'ae',
              'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'oe', 'o',
              'u', 'u', 'u', 'ue', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c',
              'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e',
              'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h',
              'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k',
              'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n',
              'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r',
              'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't',
              'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y',
              'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o',
              'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];

        return str_replace($a, $b, $str);
    }
}
