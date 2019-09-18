<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param string $cSeo
 * @return mixed
 */
function getSeo($cSeo)
{
    return iso2ascii($cSeo);
}

/**
 * @param string $cSeo
 * @return string
 */
function checkSeo($cSeo)
{
    if (!$cSeo || !is_string($cSeo)) {
        return '';
    }

    $exists = Shop::DB()->select('tseo', 'cSeo', $cSeo);
    if ($exists === null) {
        return $cSeo;
    }

    Shop::DB()->query('SET @IKEY := 0', 10);
    $obj = Shop::DB()->query(
        "SELECT oseo.newSeo
            FROM (
	            SELECT CONCAT('{$cSeo}', '_', @IKEY:=@IKEY+1) newSeo, @IKEY nOrder
	            FROM tseo AS iseo
                WHERE iseo.cSeo LIKE '{$cSeo}%'
		            AND iseo.cSeo RLIKE '^{$cSeo}(_[0-9]+)?$'
            ) AS oseo
            WHERE oseo.newSeo NOT IN (
	            SELECT iseo.cSeo
	            FROM tseo AS iseo
	            WHERE iseo.cSeo LIKE '{$cSeo}_%'
		            AND iseo.cSeo RLIKE '^{$cSeo}_[0-9]+$'
            )
            ORDER BY oseo.nOrder
            LIMIT 1", 1
    );

    return isset($obj->newSeo) ? $obj->newSeo : $cSeo;
}

/**
 * @param string $str
 * @return mixed
 */
function iso2ascii($str)
{
    $arr = [
        chr(161) => 'A', chr(163) => 'L', chr(165) => 'L', chr(166) => 'S', chr(169) => 'S',
        chr(170) => 'S', chr(171) => 'T', chr(172) => 'Z', chr(174) => 'Z', chr(175) => 'Z',
        chr(177) => 'a', chr(179) => 'l', chr(181) => 'l', chr(182) => 's', chr(185) => 's',
        chr(186) => 's', chr(187) => 't', chr(188) => 'z', chr(190) => 'z', chr(191) => 'z',
        chr(192) => 'R', chr(193) => 'A', chr(194) => 'A', chr(195) => 'A', chr(196) => 'Ae',
        chr(197) => 'L', chr(198) => 'C', chr(199) => 'C', chr(200) => 'C', chr(201) => 'E',
        chr(202) => 'E', chr(203) => 'E', chr(204) => 'E', chr(205) => 'I', chr(206) => 'I',
        chr(207) => 'D', chr(208) => 'D', chr(209) => 'N', chr(210) => 'N', chr(211) => 'O',
        chr(212) => 'O', chr(213) => 'O', chr(214) => 'Oe', chr(216) => 'R', chr(217) => 'U',
        chr(218) => 'U', chr(219) => 'U', chr(220) => 'Ue', chr(221) => 'Y', chr(222) => 'T',
        chr(223) => 'ss', chr(224) => 'r', chr(225) => 'a', chr(226) => 'a', chr(227) => 'a',
        chr(228) => 'ae', chr(229) => 'l', chr(230) => 'c', chr(231) => 'c', chr(232) => 'c',
        chr(233) => 'e', chr(234) => 'e', chr(235) => 'e', chr(236) => 'e', chr(237) => 'i',
        chr(238) => 'i', chr(239) => 'd', chr(240) => 'd', chr(241) => 'n', chr(242) => 'n',
        chr(243) => 'o', chr(244) => 'o', chr(245) => 'o', chr(246) => 'oe', chr(248) => 'r',
        chr(249) => 'u', chr(250) => 'u', chr(251) => 'u', chr(252) => 'ue', chr(253) => 'y',
        chr(254) => 't', chr(32) => '-', chr(58) => '-', chr(59) => '-',
        chr(92)  => '-', chr(43) => '-', chr(38) => '-', chr(180) => ''
    ];
    $str = preg_replace('~[^\w\-/]~', '', strtr($str, $arr));

    while (strpos($str, '--') !== false) {
        $str = str_replace('--', '-', $str);
    }

    return $str;
}

/**
 * Get flat SEO-URL path (removes all slashes from seo-url-path, including leading and trailing slashes)
 *
 * @param string $cSeoPath - the seo path e.g. "My/Product/Name"
 * @return string - flat SEO-URL Path e.g. "My-Product-Name"
 */
function getFlatSeoPath($cSeoPath)
{
    $trimChars = ' -_';

    return trim(str_replace('/', '-', $cSeoPath), $trimChars);
}
