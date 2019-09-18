<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
include PFAD_ROOT . PFAD_INCLUDES . 'spiderlist_inc.php';

//besucherzähler
if (!isset($_SESSION['oBesucher'])) {
    $userAgent    = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $kBesucherBot = istSpider($userAgent);
    if ($kBesucherBot > 0) {
        Shop::DB()->query("UPDATE tbesucherbot SET dZeit = now() WHERE kBesucherBot = " . $kBesucherBot, 4);
    }
    archiviereBesucher();
    //schaue, ob für diese SessionID schon ein Besucher existiert
    $besucher = Shop::DB()->select('tbesucher', 'cSessID', session_id());
    if (!isset($besucher->kBesucher)) {
        //schaue, ob für diese IP + Browser schon ein Besucher existiert
        $besucher = Shop::DB()->select('tbesucher', 'cID', md5($userAgent . gibIP()));
    }
    if (!isset($besucher->kBesucher)) {
        //erstelle neuen Besucher
        //alltime BEsucherzähler hochsetzen
        Shop::DB()->query("UPDATE tbesucherzaehler SET nZaehler = nZaehler+1", 4);
        //neuen Besucher erstellen
        $besucher                    = new stdClass();
        $besucher->cIP               = gibIP();
        $besucher->cSessID           = session_id();
        $besucher->cID               = md5($userAgent . gibIP());
        $besucher->kKunde            = 0;
        $besucher->kBestellung       = 0;
        $besucher->cEinstiegsseite   = $_SERVER['REQUEST_URI'];
        $besucher->cReferer          = gibReferer();
        $besucher->cUserAgent        = StringHandler::filterXSS($_SERVER['HTTP_USER_AGENT']);
        $besucher->cBrowser          = gibBrowser();
        $besucher->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
        $besucher->dLetzteAktivitaet = 'now()';
        $besucher->dZeit             = 'now()';
        $besucher->kBesucherBot      = $kBesucherBot;
        $besucher->kBesucher         = Shop::DB()->insert('tbesucher', $besucher);
        if ($besucher->cReferer) { //falls SuMa -> Suchstrings festhalten
            werteRefererAus($besucher->kBesucher, $besucher->cReferer);
        }
    }
    //Besucher in der Session festhalten, falls einer erstellt oder rausgeholt
    if (isset($besucher->kBesucher) && $besucher->kBesucher > 0) {
        $besucher->kBesucher   = (int)$besucher->kBesucher;
        $besucher->kKunde      = (int)$besucher->kKunde;
        $_SESSION['oBesucher'] = $besucher;
    }
}
//Besucheraktivität aktualisieren
if (isset($_SESSION['oBesucher']->kBesucher) && $_SESSION['oBesucher']->kBesucher > 0) {
    $_upd                    = new stdClass();
    $_upd->dLetzteAktivitaet = 'now()';
    $_upd->cAusstiegsseite   = $_SERVER['REQUEST_URI'];
    Shop::DB()->update('tbesucher', 'kBesucher', (int)$_SESSION['oBesucher']->kBesucher, $_upd);
}

/**
 * @return string
 */
function gibBrowser()
{
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
    if (strpos($agent, 'msie') !== false) {
        $pos = strpos($agent, 'msie');

        return 'Internet Explorer ' . (int)substr($agent, $pos + 4);
    }
    if (strpos($agent, 'opera') !== false) {
        return 'Opera';
    }
    if (strpos($agent, 'safari') !== false) {
        return 'Safari';
    }
    if (strpos($agent, 'firefox') !== false) {
        return 'Firefox';
    }
    if (strpos($agent, 'chrome') !== false) {
        return 'Chrome';
    }

    return 'Sonstige';
}

/**
 * @return string
 */
function gibReferer()
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        $teile = explode('/', $_SERVER['HTTP_REFERER']);

        return StringHandler::filterXSS(strtolower($teile[2]));
    }

    return '';
}

/**
 * @return string
 */
function gibBot()
{
    $bot   = '';
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (
        strpos($agent, 'googlebot') !== false ||
        strpos($agent, 'bingbot') !== false ||
        strpos($agent, 'inktomi.com') !== false ||
        strpos($agent, 'yahoo! slurp') !== false ||
        strpos($agent, 'msnbot') !== false ||
        strpos($agent, 'teoma') !== false ||
        strpos($agent, 'crawler') !== false ||
        strpos($agent, 'scooter') !== false ||
        strpos($agent, 'ask jeeves') !== false ||
        strpos($agent, 'fireball')
    ) {
        $bot = 'unbekannter Bot';
        if (strpos($agent, 'googlebot') !== false) {
            $bot = 'Google';
        } elseif (strpos($agent, 'bingbot') !== false) {
            $bot = 'Bing';
        } elseif (strpos($agent, 'inktomi.com') !== false) {
            $bot = 'Inktomi';
        } elseif (strpos($agent, 'yahoo! slurp') !== false) {
            $bot = 'Yahoo!';
        } elseif (strpos($agent, 'msnbot') !== false) {
            $bot = 'MSN';
        } elseif (strpos($agent, 'teoma') !== false) {
            $bot = 'Teoma';
        } elseif (strpos($agent, 'crawler') !== false) {
            $bot = 'Crawler';
        } elseif (strpos($agent, 'scooter') !== false) {
            $bot = 'Scooter';
        } elseif (strpos($agent, 'fireball') !== false) {
            $bot = 'Fireball';
        } elseif (strpos($agent, 'ask jeeves') !== false) {
            $bot = 'Ask';
        }
    }

    return $bot;
}

/**
 * @param int    $kBesucher
 * @param string $referer
 */
function werteRefererAus($kBesucher, $referer)
{
    $kBesucher           = (int)$kBesucher;
    $roh                 = $_SERVER['HTTP_REFERER'];
    $ausdruck            = new stdClass();
    $ausdruck->kBesucher = $kBesucher;
    $ausdruck->cRohdaten = StringHandler::filterXSS($_SERVER['HTTP_REFERER']);
    $param               = '';
    if (strpos($referer, '.google.') !== false ||
        strpos($referer, 'suche.t-online.') !== false ||
        strpos($referer, 'search.live.') !== false ||
        strpos($referer, '.aol.') !== false ||
        strpos($referer, '.aolsvc.') !== false ||
        strpos($referer, '.ask.') !== false ||
        strpos($referer, 'search.icq.') !== false ||
        strpos($referer, 'search.msn.') !== false ||
        strpos($referer, '.exalead.') !== false
    ) {
        $param = 'q';
    } elseif (strpos($referer, 'suche.web') !== false) {
        $param = 'su';
    } elseif (strpos($referer, 'suche.aolsvc') !== false) {
        $param = 'query';
    } elseif (strpos($referer, 'search.yahoo') !== false) {
        $param = 'p';
    } elseif (strpos($referer, 'search.ebay') !== false) {
        $param = 'satitle';
    }
    if ($param !== '') {
        preg_match("/(\?$param|&$param)=[^&]+/i", $roh, $treffer);
        $ausdruck->cSuchanfrage = isset($treffer[0]) ? utf8_decode(urldecode(substr($treffer[0], 3))) : null;
        if ($ausdruck->cSuchanfrage) {
            Shop::DB()->insert('tbesuchersuchausdruecke', $ausdruck);
        }
    }
}

/**
 * @param string $referer
 * @return int
 */
function istSuchmaschine($referer)
{
    if (!$referer) {
        return 0;
    }
    if (strpos($referer, '.google.') !== false ||
        strpos($referer, '.bing.') !== false ||
        strpos($referer, 'suche.') !== false ||
        strpos($referer, 'search.') !== false ||
        strpos($referer, '.yahoo.') !== false ||
        strpos($referer, '.fireball.') !== false ||
        strpos($referer, '.seekport.') !== false ||
        strpos($referer, '.keywordspy.') !== false ||
        strpos($referer, '.hotfrog.') !== false ||
        strpos($referer, '.altavista.') !== false ||
        strpos($referer, '.ask.') !== false
    ) {
        return 1;
    }

    return 0;
}

/**
 * @param string $cUserAgent
 * @return int
 */
function istSpider($cUserAgent)
{
    $cSpider_arr       = getSpiderArr();
    $oBesucherBot      = null;
    $cBotUserAgent_arr = array_keys($cSpider_arr);
    if (is_array($cBotUserAgent_arr) && count($cBotUserAgent_arr) > 0) {
        foreach ($cBotUserAgent_arr as $i => $cBotUserAgent) {
            if (strpos($cUserAgent, $cBotUserAgent) !== false) {
                $oBesucherBot = Shop::DB()->select('tbesucherbot', 'cUserAgent', $cBotUserAgent);

                break;
            }
        }
    }

    return (isset($oBesucherBot->kBesucherBot) && (int)$oBesucherBot->kBesucherBot > 0)
        ? (int)$oBesucherBot->kBesucherBot
        : 0;
}
