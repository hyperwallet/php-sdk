<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Prepaid Card
 *
 * @property string $token
 * @property string $type
 *
 * @property string $status
 * @property \DateTime $createdOn
 *
 * @property string $transferMethodCountry
 * @property string $transferMethodCurrency
 *
 * @property string $cardType
 *
 * @property string $cardPackage
 * @property string $cardNumber
 * @property string $cardBrand
 * @property \DateTime $dateOfExpiry
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

    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * @return string
     */
    public function getCardPackage() {
        return $this->cardPackage;
    }

    /**
     * @param string $cardPackage
     * @return PrepaidCard
     */
    public function setCardPackage($cardPackage) {
        $this->cardPackage = $cardPackage;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @param string $token
     * @return PrepaidCard
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PrepaidCard
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->status;
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
    public function getTransferMethodCountry() {
        return $this->transferMethodCountry;
    }

    /**
     * @return string
     */
    public function getTransferMethodCurrency() {
        return $this->transferMethodCurrency;
    }

    /**
     * @return string
     */
    public function getCardType() {
        return $this->cardType;
    }

    /**
     * @return string
     */
    public function getCardNumber() {
        return $this->cardNumber;
    }

    /**
     * @return string
     */
    public function getCardBrand() {
        return $this->cardBrand;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfExpiry() {
        return $this->dateOfExpiry ? new \DateTime($this->dateOfExpiry) : null;
    }

}
