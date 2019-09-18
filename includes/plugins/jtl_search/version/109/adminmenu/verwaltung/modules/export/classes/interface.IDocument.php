<?php

/**
 * Interface IDocument
 */
interface IDocument
{
    /**
     * @return mixed
     */
    public function isValid();

    /**
     * @return mixed
     */
    public function getClassName();

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getURL();

    /**
     * @param $nId
     * @return mixed
     */
    public function setId($nId);

    /**
     * @param $cName
     * @param $cLanguageIso
     * @return mixed
     */
    public function setName($cName, $cLanguageIso);

    /**
     * @param $cURL
     * @param $cLanguageISO
     * @return mixed
     */
    public function setURL($cURL, $cLanguageISO);
}
