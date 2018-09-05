<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Paypal
 *
 * @property string $token The paypal account token
 * @property string $type The transfer method type
 *
 * @property string $status The paypal account status
 * @property \DateTime $createdOn The paypal account creation date
 *
 * @property string $transferMethodCountry The transfer method country
 * @property string $transferMethodCurrency The transfer method currency
 *
 * @property string $email Paypal Email
 *
 * @package Hyperwallet\Model
 */
class PaypalAccount extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn');

    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_INVALID = 'INVALID';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';

    const PROFILE_TYPE_INDIVIDUAL = 'INDIVIDUAL';
    const PROFILE_TYPE_BUSINESS = 'BUSINESS';

    /**
     * Creates a instance of BankAccount
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the bank account token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the bank account token
     *
     * @param string $token
     * @return BankAccount
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the bank account id
     *
     * @return string
     */
    public function getBankAccountId() {
        return $this->bankAccountId;
    }

    /**
     * Set the bank account id
     *
     * @param string $bankAccountId
     * @return BankAccount
     */
    public function setBankAccountId($bankAccountId) {
        $this->bankAccountId = $bankAccountId;
        return $this;
    }

    /**
     * Get the transfer method type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set the transfer method type
     *
     * @param string $type
     * @return BankAccount
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the transfer method country
     *
     * @return string
     */
    public function getTransferMethodCountry() {
        return $this->transferMethodCountry;
    }

    /**
     * Set the transfer method country
     *
     * @param string $transferMethodCountry
     * @return BankAccount
     */
    public function setTransferMethodCountry($transferMethodCountry) {
        $this->transferMethodCountry = $transferMethodCountry;
        return $this;
    }

    /**
     * Get the transfer method currency
     *
     * @return string
     */
    public function getTransferMethodCurrency() {
        return $this->transferMethodCurrency;
    }

    /**
     * Set the transfer method currency
     *
     * @param string $transferMethodCurrency
     * @return BankAccount
     */
    public function setTransferMethodCurrency($transferMethodCurrency) {
        $this->transferMethodCurrency = $transferMethodCurrency;
        return $this;
    }

    /**
     * Get the paypal email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the paypal email
     *
     * @param string $email
     * @return BankAccount
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
    
}
