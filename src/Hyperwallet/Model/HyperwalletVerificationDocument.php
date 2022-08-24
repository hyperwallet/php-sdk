<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 HyperwalletVerificationDocument
 *
 * @property string $category The category of the document
 * @property string $type The type of the document
 * @property string $status The status of the document
 * @property string $country The country origin of the document
 * @property HyperwalletVerificationDocumentReasonCollection $reasons The reasons for the documents rejection of type HyperwalletVerificationDocumentReasons
 * @property \DateTime $createdOn The document creation date
 * @property object $uploadFiles The files uploaded
 *
 * @package Hyperwallet\Model
 */
class HyperwalletVerificationDocument extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('category', 'type', 'status', 'country', 'reasons', 'createdOn', 'uploadFiles');

    /**
     * Creates a instance of HyperwalletVerificationDocument
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the HyperwalletVerificationDocument creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the document status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the document category
     *
     * @return string
     */
    public function getCategory() {
        return $this->category;
    }

    // /**
    //  * Set the document category
    //  *
    //  * @param string $category
    //  * @return HyperwalletVerificationDocument
    //  */
    // public function setCategory($category) {
    //     $this->category = $category;
    //     return $this;
    // }

    /**
     * Get the document type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    // /**
    //  * Set the document type
    //  *
    //  * @param string $type
    //  * @return HyperwalletVerificationDocument
    //  */
    // public function setType($type) {
    //     $this->type = $type;
    //     return $this;
    // }

    /**
     * Get the country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    // /**
    //  * Set the country
    //  *
    //  * @param string $country
    //  * @return HyperwalletVerificationDocument
    //  */
    // public function setCountry($country) {
    //     $this->country = $country;
    //     return $this;
    // }

    /**
     * Get the document reasons
     *
     * @return HyperwalletVerificationDocumentReason $reasons
     */
    public function getReasons($reasons) {
        return $this->reasons;
    }

    // /**
    //  * Set the document reasons
    //  *
    //  * @param HyperwalletVerificationDocumentReason $reasons
    //  * @return HyperwalletVerificationDocument
    //  */
    // public function setReasons($reasons) {
    //     $this->reasons = $reasons;
    //     return $this;
    // }

    /**
     * Get the uploadFiles
     *
     * @return object $uploadFiles
     */
    public function getUploadFiles($uploadFiles) {
        return $this->uploadFiles;
    }

    // /**
    //  * Set the uploadFiles
    //  *
    //  * @param object $uploadFiles
    //  * @return HyperwalletVerificationDocument
    //  */
    // public function setUploadFiles($uploadFiles) {
    //     $this->uploadFiles = $uploadFiles;
    //     return $this;
    // }
}

/**
 * Represents a V4 HyperwalletVerificationDocumentCollection
 *
 * @property array $documents The list of documents
 *
 * @package Hyperwallet\Model
 */
class HyperwalletVerificationDocumentCollection {

    public function __construct(HyperwalletVerificationDocument ...$documents) {
        $this->documents = $documents;
    }

    public function getDocuments() {
        return $this->documents;
    }
   
    public function getIterator() {
        return new \ArrayIterator($this->documents);
    }
   
}
