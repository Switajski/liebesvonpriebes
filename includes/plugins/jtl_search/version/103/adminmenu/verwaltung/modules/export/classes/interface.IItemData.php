<?php

/**
 *
 * @author andre
 */
interface IItemData
{
    public function loadFromDB($kItem);

    public function getCount();

    public static function getItemKeys(JTLSearchDB $oDB, $nLimitN, $nLimitM);

    public function getFilledObject();
}
