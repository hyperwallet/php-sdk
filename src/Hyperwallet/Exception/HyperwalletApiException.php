<?php
namespace Hyperwallet\Exception;
use Hyperwallet\Response\ErrorResponse;

/**
 * The Hyperwallet exception for api errors
 *
 * @package Hyperwallet\Exception
 */
class HyperwalletApiException extends HyperwalletException {

    /**
     * The error response
     *
     * @var ErrorResponse
     */
    private $errorResponse;

    /**
     * Related resources
     *
     * @var array
     */
    private $relatedResources;

    /**
     * Creates a instance of the HyperwalletArgumentException
     *
     * @param ErrorResponse $errorResponse The error response
     * @param \Exception|null $previous The original exception
     */
    public function __construct(ErrorResponse $errorResponse, \Exception $previous) {
        $message = $errorResponse[0]->getMessage();
//        $message = $errorResponse[0] == null ? "Failed to get any error message from response" : $errorResponse[0]->getMessage();
        parent::__construct($message, null, $previous);

        $this->errorResponse = $errorResponse;
        $this->relatedResources = $errorResponse[0] == null ? array() : $errorResponse[0]->getRelatedResources();
    }

    /**
     * The error response or null if not available
     *
     * @return ErrorResponse
     */
    public function getErrorResponse() {
        return $this->errorResponse;
    }

    /**
     * The related resources or null if not available
     *
     * @return array
     */
    public function getRelatedResources() {
        return $this->relatedResources;
    }

}
