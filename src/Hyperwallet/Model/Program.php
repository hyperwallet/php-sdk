<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Program
 *
 * @property string $token
 * @property \DateTime $createdOn
 * @property string $name
 * @property string $parentToken
 *
 * @package Hyperwallet\Model
 */
class Program extends BaseModel {

    /**
     * @internal
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'createdOn', 'name', 'parentToken');

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
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getParentToken() {
        return $this->parentToken;
    }

}
