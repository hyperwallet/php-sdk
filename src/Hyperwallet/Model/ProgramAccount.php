<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Program Account
 *
 * @property string $token
 * @property string $type
 * @property \DateTime $createdOn
 * @property string $email
 *
 * @package Hyperwallet\Model
 */
class ProgramAccount extends BaseModel {

    /**
     * @internal
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'type', 'createdOn', 'email');

    const TYPE_FUNDING = 'FUNDING';
    const TYPE_MERCHANT = 'MERCHANT';
    const TYPE_REVENUE = 'REVENUE';
    const TYPE_COLLECTIONS = 'COLLECTIONS';
    const TYPE_VIRTUAL_INCENTIVES = 'VIRTUAL_INCENTIVES';
    const TYPE_POST_FUNDING = 'POST_FUNDING';

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
     * @return string
     */
    public function getType() {
        return $this->type;
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
    public function getEmail() {
        return $this->email;
    }

}
