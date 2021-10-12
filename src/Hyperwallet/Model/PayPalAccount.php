<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 PayPal Account
 *
 * @property string $token The PayPal account token
 * @property string $status The PayPal account status
 * @property \DateTime $createdOn The PayPal account creation date
 * @property string $type The transfer method type
 * @property string $transferMethodCountry The transfer method country
 * @property string $transferMethodCurrency The transfer method currency
 * @property string $isDefaultTransferMethod The is default transfer method
 * @property string $email The PayPal account email

 *
 * @package Hyperwallet\Model
 */

class PayPalAccount extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn');

    public static function FILTERS_ARRAY() {
        return array('status', 'type', 'createdOn' , 'createdBefore', 'createdAfter', 'sortBy', 'offset', 'limit');
    }

    /**
     * Creates a instance of PayPalAccount
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the PayPal account token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the PayPal account token
     *
     * @param string $token
     * @return PayPalAccount
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the PayPal account status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the PayPal account creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
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
     * @return PayPalAccount
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
     * @return PayPalAccount
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
     * @return PayPalAccount
     */
    public function setTransferMethodCurrency($transferMethodCurrency) {
        $this->transferMethodCurrency = $transferMethodCurrency;
        return $this;
    }

    /**
     * Get the is default transfer method
     *
     * @return string
     */
    public function getIsDefaultTransferMethod() {
        return $this->isDefaultTransferMethod;
    }

    /**
     * Set the is default transfer method
     *
     * @param string $isDefaultTransferMethod
     * @return PayPalAccount
     */
    public function setIsDefaultTransferMethod($isDefaultTransferMethod) {
        $this->isDefaultTransferMethod = $isDefaultTransferMethod;
        return $this;
    }

    /**
     * Get the PayPal account email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the PayPal account email
     *
     * @param string $email
     * @return PayPalAccount
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
}
