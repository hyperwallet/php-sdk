<?php
namespace Hyperwallet\Model;

/**
 * Represents a v4 Error
 *
 * @package Hyperwallet\Model
 */
class Error {

    /**
     * The field name
     *
     * @var string
     */
    private $fieldName;

    /**
     * The error message
     *
     * @var string
     */
    private $message;

    /**
     * The error code
     *
     * @var string
     */
    private $code;

    /**
     * The related resources
     *
     * @var array
     */
    private $relatedResources;

    /**
     * Creates a instance of Error
     *
     * @param array $error A single error response map
     */
    public function __construct(array $error) {
        $this->message = $error['message'];
        $this->code = $error['code'];

        if (isset($error['fieldName'])) {
            $this->fieldName = $error['fieldName'];
        }

        if (isset($error['relatedResources'])) {
            $this->relatedResources = $error['relatedResources'];
        }
    }

    /**
     * Get the field name
     *
     * @return string
     */
    public function getFieldName() {
        return $this->fieldName;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Get the error code
     *
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

   /**
     * Get list of related resources
     *
     * @return array
     */
    public function getRelatedResources() {
        return $this->relatedResources;
    }
}
