<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_CLASSES . 'class.JTL-Shopadmin.Plausi.php';

/**
 * Class PlausiKundenfeld
 */
class PlausiKundenfeld extends Plausi
{
    /**
     * @param null|string $cTyp
     * @param bool        $bUpdate
     * @return bool
     */
    public function doPlausi($cTyp = null, $bUpdate = false)
    {
        if (count($this->xPostVar_arr) > 0) {
            // cName
            if (!isset($this->xPostVar_arr['cName']) || strlen($this->xPostVar_arr['cName']) === 0) {
                $this->xPlausiVar_arr['cName'] = 1;
            }
            // cWawi
            if (!isset($this->xPostVar_arr['cWawi']) || strlen($this->xPostVar_arr['cWawi']) === 0) {
                $this->xPlausiVar_arr['cWawi'] = 1;
            }
            // cTyp
            if (!isset($this->xPostVar_arr['cTyp']) || strlen($this->xPostVar_arr['cTyp']) === 0) {
                $this->xPlausiVar_arr['cTyp'] = 1;
            }
            // nSort
            if (!isset($this->xPostVar_arr['nSort'])) {
                $this->xPlausiVar_arr['nSort'] = 1;
            }
            // nPflicht
            if (!isset($this->xPostVar_arr['nPflicht'])) {
                $this->xPlausiVar_arr['nPflicht'] = 1;
            }
            // nEdit
            if (!isset($this->xPostVar_arr['nEdit'])) {
                $this->xPlausiVar_arr['nEdit'] = 1;
            }
            if ($cTyp === 'auswahl') {
                if (!is_array($this->xPostVar_arr['cWert']) ||
                    count($this->xPostVar_arr['cWert']) === 0 ||
                    strlen($this->xPostVar_arr['cWert'][0]) === 0
                ) {
                    $this->xPlausiVar_arr['cWert'] = 1;
                }
            } elseif (!$bUpdate) {
                $oKundenfeld = Shop::DB()->select(
                    'tkundenfeld',
                    'kSprache', (int)$_SESSION['kSprache'],
                    'cName', Shop::DB()->escape($this->xPostVar_arr['cName'])
                );
                if (isset($oKundenfeld->kKundenfeld) && $oKundenfeld->kKundenfeld > 0) {
                    $this->xPlausiVar_arr['cName'] = 2;
                }
            }

            return true;
        }

        return false;
    }
}
