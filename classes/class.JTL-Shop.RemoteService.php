<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class RemoteService
 */
class RemoteService
{
    use SingletonTrait;

    const URI = 'https://api.jtl-software.de/shop';

    protected function init()
    {
        if (!isset($_SESSION['rs'])) {
            $_SESSION['rs'] = [];
        }
    }

    public function getSubscription()
    {
        if (!isset($_SESSION['rs']['subscription'])) {
            $nice = Nice::getInstance();

            $subscription = $this->call('check/subscription', [
                'key' =>  $nice->getAPIKey(),
                'domain' =>  $nice->getDomain(),
            ]);

            $_SESSION['rs']['subscription'] = (isset($subscription->kShop) && $subscription->kShop > 0)
                ? $subscription : null;
        }

        return $_SESSION['rs']['subscription'];
    }

    public function getAvailableVersions()
    {
        if (!isset($_SESSION['rs']['versions'])) {
            $_SESSION['rs']['versions'] = $this->call('v2/versions');
        }

        return $_SESSION['rs']['versions'];
    }

    public function getLatestVersion()
    {
        $nVersion = Shop::getVersion();
        $nMinorVersion = (int)JTL_MINOR_VERSION;
        $oVersions = $this->getAvailableVersions();

        $oStableVersions = array_filter((array)$oVersions, function($v) use($nVersion, $nMinorVersion) {
            return $v->channel == 'Stable' && (int)$v->version >= $nVersion;
        });

        if (count($oStableVersions) > 0) {
            $oVersions = $oStableVersions;
        }

        return end($oVersions);
    }

    public function hasNewerVersion()
    {
        if (JTL_MINOR_VERSION === '#JTL_MINOR_VERSION#') {
            return false;
        }

        $nVersion = Shop::getVersion();
        $nMinorVersion = (int)JTL_MINOR_VERSION;
        $oVersion = $this->getLatestVersion();

        return $oVersion &&
            ((int)$oVersion->version > $nVersion ||
                ((int)$oVersion->version == $nVersion && $oVersion->build > $nMinorVersion));
    }

    protected function call($uri, $data = null)
    {
        $uri = self::URI . '/' . ltrim($uri, '/');
        $content = http_get_contents($uri, 10, $data);
        if (!is_null($content) && !empty($content)) {
            return json_decode($content);
        }
        return null;
    }
}