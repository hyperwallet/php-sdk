<?php
namespace Hyperwallet\Exception;

/**
 * The hyperwallet exception for missing arguments
 *
 * @package Hyperwallet
 */
class HyperwalletArgumentException extends HyperwalletException {

    /**
     * Create a instance of the HyperwalletArgumentException
     *
     * @param string $message The error message
     * @param int|null $code The error code
     * @param \Exception|null $previous The original exception
     */
    public function __construct($message, $code = null, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
