<?php

/**
 * Interface IItemData
 */
interface IItemData
{
    /**
     * @param $kItem
     * @return mixed
     */
    public function loadFromDB($kItem);

    /**
     * @return mixed
     */
    public function getCount();

    /**
     * @param             $nLimitN
     * @param             $nLimitM
     * @return mixed
     */
    public static function getItemKeys($nLimitN, $nLimitM);

    /**
     * @return mixed
     */
    public function getFilledObject();
}
