<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Prepaid Card
 *
 * @property string $token The prepaid card token
 * @property string $type The transfer method type
 *
 * @property string $status The prepaid card status
 * @property \DateTime $createdOn The prepaid card creation date
 *
 * @property string $transferMethodCountry The transfer method country
 * @property string $transferMethodCurrency The transfer method currency
 *
 * @property string $cardType The prepaid card type
 *
 * @property string $cardPackage The prepaid card package
 * @property string $cardNumber The prepaid card number
 * @property string $cardBrand The prepaid card brand
 * @property \DateTime $dateOfExpiry The prepaid card expiry date
 *
 * @package Hyperwallet\Model
 */
class PrepaidCard extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn', 'transferMethodCountry', 'transferMethodCurrency', 'cardType', 'cardNumber', 'cardBrand', 'dateOfExpiry');

    const TYPE_PREPAID_CARD = 'PREPAID_CARD';

    const STATUS_QUEUED = 'QUEUED';
    const STATUS_PRE_ACTIVATED = 'PRE_ACTIVATED';
    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_DECLINED = 'DECLINED';
    const STATUS_LOCKED = 'LOCKED';
    const STATUS_SUSPENDED = 'SUSPENDED';
    const STATUS_LOST_OR_STOLEN = 'LOST_OR_STOLEN';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';
    const STATUS_COMPLIANCE_HOLD = 'COMPLIANCE_HOLD';
    const STATUS_KYC_HOLD = 'KYC_HOLD';

    const CARD_TYPE_PERSONALIZED = 'PERSONALIZED';
    const CARD_TYPE_VIRTUAL = 'VIRTUAL';

    const CARD_BRAND_VISA = 'VISA';
    const CARD_BRAND_MASTERCARD = 'MASTERCARD';

    public static function FILTERS_ARRAY() {
        return array('status');
    }

    /**
     * Creates a instance of PrepaidCard
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the prepaid card package
     *
     * @return string
     */
    public function getCardPackage() {
        return $this->cardPackage;
    }

    /**
     * Set the prepaid card package
     *
     * @param string $cardPackage
     * @return PrepaidCard
     */
    public function setCardPackage($cardPackage) {
        $this->cardPackage = $cardPackage;
        return $this;
    }

    /**
     * Get the prepaid card token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Get the prepaid card token
     *
     * @param string $token
     * @return PrepaidCard
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
     * @return PrepaidCard
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the prepaid card status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the prepaid card creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
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
     * Get the transfer method currency
     *
     * @return string
     */
    public function getTransferMethodCurrency() {
        return $this->transferMethodCurrency;
    }

    /**
     * Get the prepaid card type
     *
     * @return string
     */
    public function getCardType() {
        return $this->cardType;
    }

    /**
     * Get the prepaid card number
     *
     * @return string
     */
    public function getCardNumber() {
        return $this->cardNumber;
    }

    /**
     * Get the prepaid card brand
     *
     * @return string
     */
    public function getCardBrand() {
        return $this->cardBrand;
    }

    /**
     * Get the prepaid card expiry date
     *
     * @return \DateTime
     */
    public function getDateOfExpiry() {
        return $this->dateOfExpiry ? new \DateTime($this->dateOfExpiry) : null;
    }

}
