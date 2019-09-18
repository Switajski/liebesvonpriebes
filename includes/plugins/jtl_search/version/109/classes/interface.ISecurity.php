<?php

/**
 * Security Interface
 *
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
interface ISecurity
{
    /**
     * @param bool $bReturnKey
     * @return mixed
     */
    public function createKey($bReturnKey = true);

    /**
     * @param array $cParam_arr
     * @return mixed
     */
    public function setParam_arr(array $cParam_arr);

    /**
     * @return mixed
     */
    public function getSHA1Key();

    /**
     * @return mixed
     */
    public function getParam_arr();

    /**
     * @param $cProjectId
     * @return mixed
     */
    public function setProjectId($cProjectId);

    /**
     * @param $cAuthHash
     * @return mixed
     */
    public function setAuthHash($cAuthHash);
}
