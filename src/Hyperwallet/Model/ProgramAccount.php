<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Program Account
 *
 * @property string $token The program account token
 * @property string $type The program account type
 * @property \DateTime $createdOn The program account creation date
 * @property string $email The program account email
 *
 * @package Hyperwallet\Model
 */
class ProgramAccount extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
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

    /**
     * Creates a instance of ProgramAccount
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the program account token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Get the program account type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get the program account creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the program account email
     * 
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

}
