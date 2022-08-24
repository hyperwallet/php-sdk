<?php
namespace Hyperwallet\Exception;

/**
 * The Hyperwallet exception for missing arguments
 *
 * @package Hyperwallet\Exception
 */
class HyperwalletArgumentException extends HyperwalletException {

    /**
     * Creates a instance of the HyperwalletArgumentException
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param \Exception|null $previous The original exception
     */
    public function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
