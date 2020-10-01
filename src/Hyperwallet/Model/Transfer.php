<?php
namespace Hyperwallet\Model;

/**
 * Represents a v4 Transfer
 *
 * @property string $token The transfer token
 * @property string $status The transfer status
 * @property \DateTime $createdOn The transfer creation date
 * @property string $clientTransferId The client transfer id
 * @property string $sourceToken The source token
 * @property string $sourceAmount The source amount
 * @property string $sourceFeeAmount The source fee amount
 * @property string $sourceCurrency The source currency
 * @property string $destinationToken The destination token
 * @property string $destinationAmount The destination amount
 * @property string $destinationFeeAmount The destination fee amount
 * @property string $destinationCurrency The destination currency
 * @property array $foreignExchanges The foreign exchanges
 * @property string $notes The notes
 * @property string $memo The memo
 * @property \DateTime $expiresOn The transfer expiration date

 *
 * @package Hyperwallet\Model
 */

class Transfer extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn', 'sourceFeeAmount', 'destinationFeeAmount', 'foreignExchanges', 'expiresOn');

    const STATUS_QUOTED = 'QUOTED';
    const STATUS_SCHEDULED = 'SCHEDULED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_VERIFICATION_REQUIRED = 'VERIFICATION_REQUIRED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_RETURNED = 'RETURNED';
    const STATUS_FAILED = 'FAILED';
    const STATUS_EXPIRED = 'EXPIRED';

    /**
     * Creates a instance of Transfer
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the transfer token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the transfer token
     *
     * @param string $token
     * @return Transfer
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the transfer status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the transfer creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get transfer clientTransferId
     *
     * @return string
     */
    public function getClientTransferId() {
        return $this->clientTransferId;
    }

    /**
     * Set transfer clientTransferId
     *
     * @param string $clientTransferId
     * @return Transfer
     */
    public function setClientTransferId($clientTransferId) {
        $this->clientTransferId = $clientTransferId;
        return $this;
    }

    /**
     * Get transfer sourceToken
     *
     * @return string
     */
    public function getSourceToken() {
        return $this->sourceToken;
    }

    /**
     * Set transfer sourceToken
     *
     * @param string $sourceToken
     * @return Transfer
     */
    public function setSourceToken($sourceToken) {
        $this->sourceToken = $sourceToken;
        return $this;
    }

    /**
     * Get transfer sourceAmount
     *
     * @return string
     */
    public function getSourceAmount() {
        return $this->sourceAmount;
    }

    /**
     * Set transfer sourceAmount
     *
     * @param string $sourceAmount
     * @return Transfer
     */
    public function setSourceAmount($sourceAmount) {
        $this->sourceAmount = $sourceAmount;
        return $this;
    }

    /**
     * Get transfer sourceFeeAmount
     *
     * @return string
     */
    public function getSourceFeeAmount() {
        return $this->sourceFeeAmount;
    }

    /**
     * Get transfer sourceCurrency
     *
     * @return string
     */
    public function getSourceCurrency() {
        return $this->sourceCurrency;
    }

    /**
     * Set transfer sourceCurrency
     *
     * @param string $sourceCurrency
     * @return Transfer
     */
    public function setSourceCurrency($sourceCurrency) {
        $this->sourceCurrency = $sourceCurrency;
        return $this;
    }

    /**
     * Get transfer destinationToken
     *
     * @return string
     */
    public function getDestinationToken() {
        return $this->destinationToken;
    }

    /**
     * Set transfer destinationToken
     *
     * @param string $destinationToken
     * @return Transfer
     */
    public function setDestinationToken($destinationToken) {
        $this->destinationToken = $destinationToken;
        return $this;
    }

    /**
     * Get transfer destinationAmount
     *
     * @return string
     */
    public function getDestinationAmount() {
        return $this->destinationAmount;
    }

    /**
     * Set transfer destinationAmount
     *
     * @param string $destinationAmount
     * @return Transfer
     */
    public function setDestinationAmount($destinationAmount) {
        $this->destinationAmount = $destinationAmount;
        return $this;
    }

    /**
     * Get transfer destinationFeeAmount
     *
     * @return string
     */
    public function getDestinationFeeAmount() {
        return $this->destinationFeeAmount;
    }

    /**
     * Get transfer destinationCurrency
     *
     * @return string
     */
    public function getDestinationCurrency() {
        return $this->destinationCurrency;
    }

    /**
     * Set transfer destinationCurrency
     *
     * @param string $destinationCurrency
     * @return Transfer
     */
    public function setDestinationCurrency($destinationCurrency) {
        $this->destinationCurrency = $destinationCurrency;
        return $this;
    }

    /**
     * Get transfer foreignExchanges
     *
     * @return array
     */
    public function getForeignExchanges() {
        return $this->foreignExchanges;
    }

    /**
     * Get transfer notes
     *
     * @return string
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * Set transfer notes
     *
     * @param string $notes
     * @return Transfer
     */
    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get transfer memo
     *
     * @return string
     */
    public function getMemo() {
        return $this->memo;
    }

    /**
     * Set transfer memo
     *
     * @param string $memo
     * @return Transfer
     */
    public function setMemo($memo) {
        $this->memo = $memo;
        return $this;
    }

    /**
     * Get transfer expiresOn
     *
     * @return \DateTime
     */
    public function getExpiresOn() {
        return $this->expiresOn ? new \DateTime($this->expiresOn) : null;
    }
}
