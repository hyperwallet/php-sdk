<?php
namespace Hyperwallet\Exception;

/**
 * The base exception for SDK errors
 *
 * @package Hyperwallet
 */
class HyperwalletException extends \Exception {

    /**
     * Create a instance of the HyperwalletException
     *
     * @param string $message The error message
     * @param int|null $code The error code
     * @param \Exception|null $previous The original exception
     */
    public function __construct($message, $code = null, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
