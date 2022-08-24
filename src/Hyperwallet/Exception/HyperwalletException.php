<?php
namespace Hyperwallet\Exception;

/**
 * The base exception for Hyperwallet SDK errors
 *
 * @package Hyperwallet\Exception
 */
class HyperwalletException extends \Exception {

    /**
     * Creates a instance of the HyperwalletException
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Exception|null $previous The original exception
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
