<?php
namespace Hyperwallet\Model;

abstract class RejectReason {
    const DOCUMENT_EXPIRED = 0;
    const DOCUMENT_NOT_RELATED_TO_PROFILE = 1;
    const DOCUMENT_NOT_READABLE = 2;
    const DOCUMENT_NOT_DECISIVE = 3;
    const DOCUMENT_NOT_COMPLETE = 4;
    const DOCUMENT_CORRECTION_REQUIRED = 5;
    const DOCUMENT_NOT_VALID_WITH_NOTES = 6;
    const DOCUMENT_TYPE_NOT_VALID = 7;
}


/**
 * Represents a V3 HyperwalletVerificationDocumentReason
 *
 * @property RejectReason $name The reason for rejection
 * @property string $description The description of the rejection
 *
 * @package Hyperwallet\Model
 */
class HyperwalletVerificationDocumentReason extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('name', 'description');

    /**
     * Creates a instance of HyperwalletVerificationReason
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the Rejection Reason
     *
     * @return RejectReason
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
}

/**
 * Represents a V3 HyperwalletVerificationDocumentReasonsCollection
 *
 * @property array $reasons The list of reasons
 *
 * @package Hyperwallet\Model
 */
class HyperwalletVerificationDocumentReasonCollection {

    public function __construct(HyperwalletVerificationDocumentReason ...$reasons) {
        $this->reasons = $reasons;
    }

    public function getReasons() {
        return $this->reasons;
    }
   
    public function getIterator() {
        return new \ArrayIterator($this->reasons);
    }
   
}
