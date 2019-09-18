<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PremiumPlugin
 */
class PremiumPlugin
{
    const CERTIFICATION_LOGO = 'https://images.jtl-software.de/servicepartner/cert/jtl_certified_128.png';

    /**
     * @var array
     */
    private $advantages = [];

    /**
     * @var array
     */
    private $howTos = [];

    /**
     * @var string
     */
    private $longDescription = '';

    /**
     * @var string
     */
    private $shortDescription = '';

    /**
     * @var string
     */
    private $author;

    /**
     * @var array
     */
    private $badges = [];

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var array
     */
    private $buttons = [];

    /**
     * @var bool
     */
    private $isInstalled;

    /**
     * @var bool
     */
    private $isActivated;

    /**
     * @var bool
     */
    private $exists;

    /**
     * @var string
     */
    private $pluginID;

    /**
     * @var int
     */
    private $kPlugin;

    /**
     * @var stdClass
     */
    private $servicePartner;

    /**
     * @var array
     */
    private $screenShots = [];

    /**
     * @var string
     */
    private $headerColor = '#313131';

    /**
     * @var string
     */
    private $downloadLink;
    
    /**
     * PremiumPlugin constructor.
     * @param string $pluginID
     */
    public function __construct($pluginID)
    {
        $plugin            = Plugin::getPluginById($pluginID);
        $this->pluginID    = $pluginID;
        $this->exists      = file_exists(PFAD_ROOT . PFAD_PLUGIN . $pluginID . '/info.xml');
        $this->isInstalled = ($plugin !== null && $plugin->kPlugin > 0);
        $this->isActivated = ($this->isInstalled && (int)$plugin->nStatus === 2);
        $this->kPlugin     = $this->isInstalled ? (int)$plugin->kPlugin : 0;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setPluginID($id)
    {
        $this->pluginID = $id;

        return $this;
    }

    /**
     * @param $link
     * @return $this
     */
    public function setDownloadLink($link)
    {
        $this->downloadLink = $link;
        
        return $this;
    }

    /**
     * @return null|string
     */
    public function getDownloadLink()
    {
        return $this->downloadLink;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function setHeaderColor($color)
    {
        $this->headerColor = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeaderColor()
    {
        return $this->headerColor;
    }

    /**
     * @return int
     */
    public function getKPlugin()
    {
        return $this->kPlugin;
    }

    /**
     * @return string|null
     */
    public function getPluginID()
    {
        return $this->pluginID;
    }

    /**
     * @return bool
     */
    public function getExists()
    {
        return $this->exists;
    }

    /**
     * @return bool
     */
    public function getIsActivated()
    {
        return $this->isActivated;
    }

    /**
     * @return bool
     */
    public function getIsInstalled()
    {
        return $this->isInstalled;
    }

    /**
     * @param stdClass $sp
     * @return $this
     */
    public function setServicePartner($sp)
    {
        $this->servicePartner = $sp;
        
        return $this;
    }

    /**
     * @return null|stdClass
     */
    public function getservicePartner()
    {
        return $this->servicePartner;
    }

    /**
     * @param array $screenShots
     * @return $this
     */
    public function setScreenshots(array $screenShots)
    {
        $this->screenShots = $screenShots;

        return $this;
    }

    /**
     * @param stdClass $screenShot
     * @return $this
     */
    public function addScreenShot(stdClass $screenShot)
    {
        $this->screenShots[] = $screenShot;

        return $this;
    }

    /**
     * @return array
     */
    public function getScreenShots()
    {
        return $this->screenShots;
    }

    /**
     * @return null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $title
     * @param string $description
     * @return $this
     */
    public function setLongDescription($title, $description)
    {
        $this->longDescription        = new stdClass();
        $this->longDescription->title = $title;
        $this->longDescription->html  = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * @param string $title
     * @param string $description
     * @return $this
     */
    public function setShortDescription($title, $description)
    {
        $this->shortDescription        = new stdClass();
        $this->shortDescription->title = $title;
        $this->shortDescription->html  = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param string $advantage
     * @return $this
     */
    public function addAdvantage($advantage)
    {
        $this->advantages[] = $advantage;
        return $this;
    }

    /**
     * @param array $advantages
     * @return $this
     */
    public function setAdvantages(array $advantages)
    {
        $this->advantages = $advantages;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdvantages()
    {
        return $this->advantages;
    }

    /**
     * @param string $howTo
     * @return $this
     */
    public function addHowTo($howTo)
    {
        $this->howTos[] = $howTo;
        return $this;
    }

    /**
     * @param array $howTos
     * @return $this
     */
    public function setHowTos(array $howTos)
    {
        $this->howTos = $howTos;
        return $this;
    }

    /**
     * @return array
     */
    public function getHowTos()
    {
        return $this->howTos;
    }

    /**
     * @param string $url
     * @param bool   $relative
     * @return $this
     */
    public function addBadge($url, $relative = true)
    {
        $this->badges[] = $relative
            ? (Shop::getURL() . '/' . PFAD_ADMIN . PFAD_GFX . 'PremiumPlugins/' . $url)
            : $url;
        
        return $this;
    }

    /**
     * @param array $badges
     * @return $this
     */
    public function setBadges(array $badges)
    {
        $this->badges = $badges;
        return $this;
    }

    /**
     * @return array
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * @return string
     */
    public function getCertifcationLogo()
    {
        return self::CERTIFICATION_LOGO;
    }

    /**
     * @return array
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * @param string      $caption
     * @param string      $link
     * @param string      $class
     * @param null|string $fa
     * @param bool        $external
     * @return $this
     */
    public function addButton($caption, $link, $class = 'btn btn-default', $fa = null, $external = false)
    {
        $btn             = new stdClass();
        $btn->link       = $link;
        $btn->caption    = $caption;
        $btn->class      = $class;
        $btn->fa         = $fa;
        $btn->external   = $external;
        if ($external === true) {
            $btn->fa .= ' fa-external-link';
        }
        $this->buttons[] = $btn;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCertifcates()
    {
        return (isset($this->servicePartner->oZertifizierungen_arr) && count($this->servicePartner->oZertifizierungen_arr) > 0);
    }
}
