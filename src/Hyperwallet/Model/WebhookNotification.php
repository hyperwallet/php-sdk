<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Webhook Notification
 *
 * @property string $token The webhook notification token
 * @property string $type The webhook notification type
 * @property \DateTime $createdOn The webhook notification creation date
 *
 * @package Hyperwallet\Model
 */
class WebhookNotification extends BaseModel {

    /**
     * The webhook notification payload
     *
     * @var object
     */
    private $object;

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'type', 'createdOn');

    public static function FILTERS_ARRAY() {
        return array('programToken','createdBefore', 'createdAfter', 'type', 'sortBy', 'offset', 'limit');
    }

    /**
     * Creates a instance of WebhookNotification
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);

        $this->object = null;
        if (isset($properties['type'])) {
            if (strpos($properties['type'], 'USERS.BANK_ACCOUNTS') === 0) {
                $this->object = new BankAccount($properties['object']);
            } else if (strpos($properties['type'], 'USERS.PREPAID_CARDS') === 0) {
                $this->object = new PrepaidCard($properties['object']);
            } else if (strpos($properties['type'], 'USERS') === 0) {
                $this->object = new User($properties['object']);
            } else if (strpos($properties['type'], 'PAYMENTS') === 0) {
                $this->object = new Payment($properties['object']);
            }
        }
    }

    /**
     * Get the webhook notification token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Get the webhook notification type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get the webhook notification creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the webhook notification payload
     *
     * @return BankAccount|PrepaidCard|User|Payment|null
     */
    public function getObject() {
        return $this->object;
    }

}
