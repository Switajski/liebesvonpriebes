<?php
/**
 * Security Interface
 * @access public
 * @author Daniel Boehmer JTL-Software GmbH
 * @copyright 2006-2011, JTL-Software
 */
interface ISecurity
{
    public function createKey($bReturnKey = true);
    public function setParam_arr(array $cParam_arr);
    public function getSHA1Key();
    public function getParam_arr();
    public function setProjectId($cProjectId);
    public function setAuthHash($cAuthHash);
}
