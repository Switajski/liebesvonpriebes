<?php

/**
 *
 * @author andre
 */
interface IDocument
{
    public function isValid();

    public function getClassName();

    public function getId();

    public function getName();

    public function getURL();

    public function setId($nId);

    public function setName($cName, $cLanguageIso);

    public function setURL($cURL, $cLanguageISO);
}
