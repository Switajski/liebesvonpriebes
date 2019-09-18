<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class IOFile implements JsonSerializable
{
    /**
     * @var string
     */
    public $filename = '';

    /**
     * @var string
     */
    public $mimetype = '';

    /**
     * IOFile constructor.
     *
     * @param $message
     * @param int $code
     * @param array|null $errors
     */
    public function __construct($filename, $mimetype)
    {
        $this->filename = $filename;
        $this->mimetype = $mimetype;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'filename' => $this->filename,
            'mimetype' => $this->mimetype
        ];
    }
}