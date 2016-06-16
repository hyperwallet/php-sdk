<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Payment
 *
 * @property string $token
 * @property \DateTime $createdOn
 *
 * @property string $clientPaymentId
 * @property string $amount
 * @property string $currency
 *
 * @property string $description
 * @property string $memo
 * @property string $purpose
 * @property \DateTime $releaseOn
 *
 * @property string $destinationToken
 * @property string $programToken
 *
 * @package Hyperwallet\Model
 */
class Payment extends BaseModel implements IProgramAware {

    /**
     * @internal
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'createdOn');

    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Payment
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * @return string
     */
    public function getClientPaymentId() {
        return $this->clientPaymentId;
    }

    /**
     * @param string $clientPaymentId
     * @return Payment
     */
    public function setClientPaymentId($clientPaymentId) {
        $this->clientPaymentId = $clientPaymentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return Payment
     */
    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Payment
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Payment
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getMemo() {
        return $this->memo;
    }

    /**
     * @param string $memo
     * @return Payment
     */
    public function setMemo($memo) {
        $this->memo = $memo;
        return $this;
    }

    /**
     * @return string
     */
    public function getPurpose() {
        return $this->purpose;
    }

    /**
     * @param string $purpose
     * @return Payment
     */
    public function setPurpose($purpose) {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReleaseOn() {
        return $this->releaseOn ? new \DateTime($this->releaseOn) : null;
    }

    /**
     * @param \DateTime $releaseOn
     * @return Payment
     */
    public function setReleaseOn(\DateTime $releaseOn = null) {
        $this->releaseOn = $releaseOn == null ? null : $releaseOn->format('Y-m-d\TH:i:s');
        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationToken() {
        return $this->destinationToken;
    }

    /**
     * @param string $destinationToken
     * @return Payment
     */
    public function setDestinationToken($destinationToken) {
        $this->destinationToken = $destinationToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getProgramToken() {
        return $this->programToken;
    }

    /**
     * @param string $programToken
     * @return Payment
     */
    public function setProgramToken($programToken) {
        $this->programToken = $programToken;
        return $this;
    }

}
