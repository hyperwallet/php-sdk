<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Error
 *
 * @package Hyperwallet\Model
 */
class Error {

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $code;

    public function __construct(array $error) {
        $this->message = $error['message'];
        $this->code = $error['code'];

        if (isset($error['fieldName'])) {
            $this->fieldName = $error['fieldName'];
        }
    }

    /**
     * @return string
     */
    public function getFieldName() {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

}