<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Plausi
 */
class Plausi
{
    protected $xPostVar_arr;
    protected $xPlausiVar_arr;

    /**
     *
     */
    public function __construct()
    {
        $this->xPostVar_arr   = [];
        $this->xPlausiVar_arr = [];
    }

    /**
     * @return array
     */
    public function getPostVar()
    {
        return $this->xPostVar_arr;
    }

    /**
     * @return array
     */
    public function getPlausiVar()
    {
        return $this->xPlausiVar_arr;
    }

    /**
     * @param array $xVar_arr
     * @param array|null $hasHTML_arr
     * @param bool $toEntities
     * @return bool
     */
    public function setPostVar($xVar_arr, $hasHTML_arr = null, $toEntities = false)
    {
        if (is_array($xVar_arr) && count($xVar_arr) > 0) {
            if (is_array($hasHTML_arr)) {
                $exclude_keys = array_fill_keys($hasHTML_arr, 1);
                $filter_arr   = array_diff_key($xVar_arr, $exclude_keys);
                $exclude_arr  = array_intersect_key($xVar_arr, $exclude_keys);

                if ($toEntities) {
                    array_walk($exclude_arr, function (&$value) {
                        $value = htmlentities($value);
                    });
                }
                $this->xPostVar_arr = array_merge($xVar_arr, StringHandler::filterXSS($filter_arr), $exclude_arr);
            } else {
                $this->xPostVar_arr = StringHandler::filterXSS($xVar_arr);
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $xVar_arr
     * @return bool
     */
    public function setPlausiVar($xVar_arr)
    {
        if (is_array($xVar_arr) && count($xVar_arr) > 0) {
            $this->xPlausiVar_arr = $xVar_arr;

            return true;
        }

        return false;
    }

    /**
     * @param null $cTyp
     * @param bool $bUpdate
     */
    public function doPlausi($cTyp = null, $bUpdate = false)
    {
    }
}
