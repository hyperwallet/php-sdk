<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 HyperwalletVerificationDocument
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

    /**
     * Get the document type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }


    /**
     * Get the country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }


    /**
     * Get the document reasons
     *
     * @return HyperwalletVerificationDocumentReason $reasons
     */
    public function getReasons() {
        return $this->reasons;
    }


    /**
     * Get the uploadFiles
     *
     * @return object $uploadFiles
     */
    public function getUploadFiles() {
        return $this->uploadFiles;
    }

}

/**
 * Represents a V3 HyperwalletVerificationDocumentCollection
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
   
    public function getIterator() : ArrayIterator {
        return new ArrayIterator($this->documents);
    }
   
}
