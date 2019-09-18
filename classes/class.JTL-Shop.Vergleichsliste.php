<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Vergleichsliste
 */
class Vergleichsliste
{
    /**
     * @var array
     */
    public $oArtikel_arr = [];

    /**
     * Konstruktor
     *
     * @param int       $kArtikel - Falls angegeben, wird der Artikel mit angegebenem kArtikel aus der DB geholt
     * @param int|array $oVariationen_arr
     */
    public function __construct($kArtikel = 0, $oVariationen_arr = 0)
    {
        $kArtikel = (int)$kArtikel;
        if ($kArtikel > 0) {
            //new slim variant for compare list
            if (TEMPLATE_COMPATIBILITY === false) {
                $oArtikel           = new stdClass();
                $tmpName            = Shop::DB()->select('tartikel', 'kArtikel', $kArtikel, null, null, null, null, false, 'cName');
                $oArtikel->kArtikel = $kArtikel;
                $oArtikel->cName    = $tmpName->cName;
            } else {
                //default mode
                $oArtikel = new Artikel();
                $oArtikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
            }
            if (is_array($oVariationen_arr) && count($oVariationen_arr) > 0) {
                $oArtikel->Variationen = $oVariationen_arr;
            }
            $this->oArtikel_arr[] = $oArtikel;

            executeHook(HOOK_VERGLEICHSLISTE_CLASS_EINFUEGEN);
        } elseif (isset($_SESSION['Vergleichsliste'])) {
            $this->oArtikel_arr = $_SESSION['Vergleichsliste']->oArtikel_arr;
        }
    }

    /**
     * Holt alle Artikel mit der aktuellen Sprache bzw Waehrung aus der DB und weißt sie neu der Session zu
     *
     * @return $this
     */
    public function umgebungsWechsel()
    {
        if (count($_SESSION['Vergleichsliste']->oArtikel_arr) > 0) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $oArtikel) {
                //new slim variant for compare list
                if (TEMPLATE_COMPATIBILITY === false) {
                    $oArtikel_tmp           = new stdClass();
                    $oArtikel_tmp->kArtikel = $oArtikel->kArtikel;
                } else {
                    //default mode
                    $oArtikel_tmp = new Artikel($oArtikel->kArtikel);
                    $oArtikel_tmp->fuelleArtikel($oArtikel->kArtikel, $defaultOptions);
                }
                $_SESSION['Vergleichsliste']->oArtikel_arr[$i] = $oArtikel_tmp;
            }
        }

        return $this;
    }

    /**
     * @param int  $kArtikel
     * @param bool $bAufSession
     * @param int  $kKonfigitem
     * @return $this
     */
    public function fuegeEin($kArtikel, $bAufSession = true, $kKonfigitem = 0)
    {
        $kArtikel = (int)$kArtikel;
        // Existiert der Key und ist er noch nicht vorhanden?
        if ($kArtikel > 0 && !$this->artikelVorhanden($kArtikel)) {
            //new slim variant for compare list
            $oArtikel = new Artikel();
            if (TEMPLATE_COMPATIBILITY === false) {
                $oArtikel->kArtikel = $kArtikel;
            } else {
                $oArtikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
            }
            if ($kKonfigitem > 0) {
                // Falls Konfigitem gesetzt Preise + Name überschreiben
                if ((int)$kKonfigitem > 0 && class_exists('Konfigitem')) {
                    $oKonfigitem = new Konfigitem($kKonfigitem);
                    if ($oKonfigitem->getKonfigitem() > 0) {
                        $oArtikel->Preise->cVKLocalized[0] = $oKonfigitem->getPreisLocalized(true, false);
                        $oArtikel->Preise->cVKLocalized[1] = $oKonfigitem->getPreisLocalized(true, false, true);
                        $oArtikel->kSteuerklasse           = $oKonfigitem->getSteuerklasse();
                        unset($oArtikel->cLocalizedVPE);

                        if ($oKonfigitem->getUseOwnName()) {
                            $oArtikel->cName             = $oKonfigitem->getName();
                            $oArtikel->cBeschreibung     = $oKonfigitem->getBeschreibung();
                            $oArtikel->cKurzBeschreibung = $oKonfigitem->getBeschreibung();
                        }
                    }
                }
            }
            if ($oArtikel->kArtikel > 0) {
                $this->oArtikel_arr[] = $oArtikel;
            }
            if ($bAufSession) {
                $_SESSION['Vergleichsliste']->oArtikel_arr = $this->oArtikel_arr;
            }
        }

        return $this;
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public function artikelVorhanden($kArtikel)
    {
        $kArtikel = (int)$kArtikel;
        if ($kArtikel > 0 && count($this->oArtikel_arr) > 0) {
            foreach ($this->oArtikel_arr as $oArtikel) {
                if ((int)$oArtikel->kArtikel === $kArtikel) {
                    return true;
                }
            }
        }

        return false;
    }
}
