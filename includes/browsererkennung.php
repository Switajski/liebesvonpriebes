<?php
/*
 * simple browser detection
 * (c) 2010 andreasjuetten@gmx.de
 *
 * supported browser types:
 * msie, firefox, chrome, iphone, ipad, ipod, safari, opera, opera mini, netscape
 */

define('BROWSER_UNKNOWN', 0);
define('BROWSER_MSIE', 1);
define('BROWSER_FIREFOX', 2);
define('BROWSER_CHROME', 3);
define('BROWSER_SAFARI', 4);
define('BROWSER_OPERA', 5);
define('BROWSER_NETSCAPE', 6);

/**
 * @param null|string $cUserAgent
 * @return stdClass
 */
function getBrowser($cUserAgent = null)
{
    $oBrowser            = new stdClass();
    $oBrowser->nType     = 0;
    $oBrowser->bMobile   = false;
    $oBrowser->cName     = 'Unknown';
    $oBrowser->cBrowser  = 'unknown';
    $oBrowser->cPlatform = 'unknown';
    $oBrowser->cVersion  = '0';

    // agent
    $cUserAgent       = (isset($_SERVER['HTTP_USER_AGENT']) && $cUserAgent === null)
        ? $_SERVER['HTTP_USER_AGENT']
        : $cUserAgent;
    $oBrowser->cAgent = $cUserAgent;
    // mobile
    $oBrowser->bMobile = preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile' .
            '|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker' .
            '|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
            $oBrowser->cAgent,
            $cMatch_arr
        ) ||
        preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)' .
            '|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )' .
            '|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa' .
            '|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob' .
            '|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)' .
            '|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)' .
            '|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)' .
            '|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)' .
            '|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])' .
            '|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)' .
            '|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)' .
            '|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1' .
            '|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio' .
            '|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa' .
            '(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)' .
            '|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)' .
            '|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)' .
            '|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)' .
            '|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',
            substr($oBrowser->cAgent, 0, 4),
            $cMatch_arr
        );
    // platform
    if (preg_match('/linux/i', $cUserAgent)) {
        $oBrowser->cPlatform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $cUserAgent)) {
        $oBrowser->cPlatform = 'mac';
    } elseif (preg_match('/windows|win32/i', $cUserAgent)) {
        $oBrowser->cPlatform = preg_match('/windows mobile|wce/i', $cUserAgent)
            ? 'mobile'
            : 'windows';
    }
    // browser
    if (preg_match('/MSIE/i', $cUserAgent) && !preg_match('/Opera/i', $cUserAgent)) {
        $oBrowser->nType    = BROWSER_MSIE;
        $oBrowser->cName    = 'Internet Explorer';
        $oBrowser->cBrowser = 'msie';
    } elseif (preg_match('/Firefox/i', $cUserAgent)) {
        $oBrowser->nType    = BROWSER_FIREFOX;
        $oBrowser->cName    = 'Mozilla Firefox';
        $oBrowser->cBrowser = 'firefox';
    } elseif (preg_match('/Chrome/i', $cUserAgent)) {
        $oBrowser->nType    = BROWSER_CHROME;
        $oBrowser->cName    = 'Google Chrome';
        $oBrowser->cBrowser = 'chrome';
    } elseif (preg_match('/Safari/i', $cUserAgent)) {
        $oBrowser->nType = BROWSER_SAFARI;
        if (preg_match('/iPhone/i', $cUserAgent)) {
            $oBrowser->cName    = 'Apple iPhone';
            $oBrowser->cBrowser = 'iphone';
        } elseif (preg_match('/iPad/i', $cUserAgent)) {
            $oBrowser->cName    = 'Apple iPad';
            $oBrowser->cBrowser = 'ipad';
        } elseif (preg_match('/iPod/i', $cUserAgent)) {
            $oBrowser->cName    = 'Apple iPod';
            $oBrowser->cBrowser = 'ipod';
        } else {
            $oBrowser->cName    = 'Apple Safari';
            $oBrowser->cBrowser = 'safari';
        }
    } elseif (preg_match('/Opera/i', $cUserAgent)) {
        $oBrowser->nType = BROWSER_OPERA;
        if (preg_match('/Opera Mini/i', $cUserAgent)) {
            $oBrowser->cName    = 'Opera Mini';
            $oBrowser->cBrowser = 'opera_mini';
        } else {
            $oBrowser->cName    = 'Opera';
            $oBrowser->cBrowser = 'opera';
        }
    } elseif (preg_match('/Netscape/i', $cUserAgent)) {
        $oBrowser->nType    = BROWSER_NETSCAPE;
        $oBrowser->cName    = 'Netscape';
        $oBrowser->cBrowser = 'netscape';
    }
    // version
    $cKnown   = ['version', 'other', 'mobile', $oBrowser->cBrowser];
    $cPattern = '/(?<browser>' . implode('|', $cKnown) . ')[\/ ]+(?<version>[0-9.|a-zA-Z.]*)/i';
    preg_match_all($cPattern, $cUserAgent, $aMatches);
    if (count($aMatches['browser']) !== 1) {
        $oBrowser->cVersion = '0';
        if (isset($aMatches['version'][0])
            && strripos($cUserAgent, 'Version') < strripos($cUserAgent, $oBrowser->cBrowser)
        ) {
            $oBrowser->cVersion = $aMatches['version'][0];
        } elseif (isset($aMatches['version'][1])) {
            $oBrowser->cVersion = $aMatches['version'][1];
        }
    } else {
        $oBrowser->cVersion = $aMatches['version'][0];
    }
    if (strlen($oBrowser->cVersion) === 0) {
        $oBrowser->cVersion = '0';
    }

    return $oBrowser;
}
