<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Bank Card
 *
 * @property string $token The bank card token
 * @property string $type The transfer method type
 *
 * @property string $status The bank card status
 * @property \DateTime $createdOn The bank card creation date
 *
 * @property string $transferMethodCountry The bank card country
 * @property string $transferMethodCurrency The bank card currency
 *
 * @property string $cardType The bank card type
 *
 * @property string $cardNumber The bank card number
 * @property string $cardBrand The bank card brand
 * @property \DateTime $dateOfExpiry The bank card expiry date
 *
 * @package Hyperwallet\Model
 */
class BankCard extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn', 'transferMethodCountry', 'transferMethodCurrency', 'cardType', 'cardNumber', 'cardBrand', 'dateOfExpiry');

    const TYPE_BANK_CARD = 'BANK_CARD';

    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_DECLINED = 'VERIFIED';
    const STATUS_LOCKED = 'INVALID';
    const STATUS_SUSPENDED = 'DE_ACTIVATED';

    const CARD_TYPE_DEBIT = 'DEBIT';

    const CARD_BRAND_VISA = 'VISA';
    const CARD_BRAND_MASTERCARD = 'MASTERCARD';

    /**
     * Creates a instance of BankCard
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the bank card token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Get the bank card token
     *
     * @param string $token
     * @return BankCard
     */
    public function setToken($token) {
        $this->token = $token;
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
     * @return BankCard
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the bank card status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the bank card creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the bank card country
     *
     * @return string
     */
    public function getTransferMethodCountry() {
        return $this->transferMethodCountry;
    }

    /**
     * Get the bank card currency
     *
     * @return string
     */
    public function getTransferMethodCurrency() {
        return $this->transferMethodCurrency;
    }

    /**
     * Get the bank card brand
     *
     * @return string
     */
    public function getCardBrand() {
        return $this->cardBrand;
    }

    /**
     * Get the bank card number
     *
     * @return string
     */
    public function getCardNumber() {
        return $this->cardNumber;
    }

    /**
     * Get the bank card type
     *
     * @return string
     */
    public function getCardType() {
        return $this->cardType;
    }

    /**
     * Get the bank card expiry date
     *
     * @return \DateTime
     */
    public function getDateOfExpiry() {
        return $this->dateOfExpiry ? new \DateTime($this->dateOfExpiry) : null;
    }

}