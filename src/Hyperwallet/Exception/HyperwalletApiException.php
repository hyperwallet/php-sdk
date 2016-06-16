<?php
namespace Hyperwallet\Exception;
use Hyperwallet\Response\ErrorResponse;

/**
 * The hyperwallet exception for api errors
 *
 * @package Hyperwallet
 */
class HyperwalletApiException extends HyperwalletException {

    /**
     * @var ErrorResponse
     */
    private $errorResponse;

    /**
     * Create a instance of the HyperwalletArgumentException
     *
     * @param ErrorResponse $errorResponse The error response
     * @param \Exception|null $previous The original exception
     */
    public function __construct(ErrorResponse $errorResponse, \Exception $previous) {
        parent::__construct($errorResponse[0]->getMessage(), null, $previous);

        $this->errorResponse = $errorResponse;
    }

    /**
     * The error response or null if not available
     *
     * @return ErrorResponse
     */
    public function getErrorResponse() {
        return $this->errorResponse;
    }

}
