<?php
namespace Hyperwallet\Model;

use Hyperwallet\Util\StringToDataConverter;

/**
 * Represents a V3 Payment
 *
 * @property string $token The payment token
 * @property \DateTime $createdOn The payment creation date
 *
 * @property string $clientPaymentId The client payment id
 * @property string $amount The payment amount
 * @property string $currency The payment currency
 *
 * @property string $description The payment description
 * @property string $memo The payment memo
 * @property string $purpose The payment purpose
 * @property \DateTime $releaseOn The payment release date
 *
 * @property string $destinationToken The payment destination token
 * @property string $programToken The payment program token
 *
 * @package Hyperwallet\Model
 */
class Payment extends BaseModel implements IProgramAware {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'createdOn');

    /**
     * Creates a instance of Payment
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the payment token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the payment token
     *
     * @param string $token
     * @return Payment
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the payment creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the client payment id
     *
     * @return string
     */
    public function getClientPaymentId() {
        return $this->clientPaymentId;
    }

    /**
     * Set the client payment id
     *
     * @param string $clientPaymentId
     * @return Payment
     */
    public function setClientPaymentId($clientPaymentId) {
        $this->clientPaymentId = $clientPaymentId;
        return $this;
    }

    /**
     * Get the payment amount
     *
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Set the payment amount
     *
     * @param string $amount
     * @return Payment
     */
    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get the payment currency
     *
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * Set the payment currency
     *
     * @param string $currency
     * @return Payment
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Get the payment description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set the payment description
     *
     * @param string $description
     * @return Payment
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the payment memo
     *
     * @return string
     */
    public function getMemo() {
        return $this->memo;
    }

    /**
     * Set the payment memo
     *
     * @param string $memo
     * @return Payment
     */
    public function setMemo($memo) {
        $this->memo = $memo;
        return $this;
    }

    /**
     * Get the payment purpose
     *
     * @return string
     */
    public function getPurpose() {
        return $this->purpose;
    }

    /**
     * Set the paymeny purpose
     *
     * @param string $purpose
     * @return Payment
     */
    public function setPurpose($purpose) {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * Get the payment release date
     * @return \DateTime
     */
    public function getReleaseOn() {
        return $this->releaseOn ? new \DateTime($this->releaseOn) : null;
    }

    /**
     * Set the payment release date
     *
     * @param string $releaseOn |null
     * @return Payment
     */
    public function setReleaseOn($releaseOn = null)
    {
        $this->releaseOn = StringToDataConverter::convertStringToDate($releaseOn, 'Y-m-d\TH:i:s');
        return $this;
    }

    /**
     * Get the payment destination token
     *
     * @return string
     */
    public function getDestinationToken() {
        return $this->destinationToken;
    }

    /**
     * Set the payment destination token
     *
     * @param string $destinationToken
     * @return Payment
     */
    public function setDestinationToken($destinationToken) {
        $this->destinationToken = $destinationToken;
        return $this;
    }

    /**
     * Get the payment program token
     *
     * @return string
     */
    public function getProgramToken() {
        return $this->programToken;
    }

    /**
     * Set the payment program token
     *
     * @param string $programToken
     * @return Payment
     */
    public function setProgramToken($programToken) {
        $this->programToken = $programToken;
        return $this;
    }

}
