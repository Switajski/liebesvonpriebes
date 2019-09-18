<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class IOError implements JsonSerializable
{
    /**
     * @var string
     */
    public $message = '';

    /**
     * @var int
     */
    public $code = 500;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * IOError constructor.
     *
     * @param $message
     * @param int $code
     * @param array|null $errors
     */
    public function __construct($message, $code = 500, array $errors = null)
    {
        $this->message = $message;
        $this->code    = $code;
        $this->errors  = $errors;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'error' => [
                'message' => $this->message,
                'code'    => $this->code,
                'errors'  => $this->errors
            ]
        ];
    }
}