<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 Program
 *
 * @property string $token The program token
 * @property \DateTime $createdOn The program creation date
 * @property string $name The program name
 * @property string $parentToken The parent program token
 *
 * @package Hyperwallet\Model
 */
class Program extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'createdOn', 'name', 'parentToken');

    /**
     * Creates a instance of Program
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the program token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Get the program creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the program name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the parent program token
     *
     * @return string
     */
    public function getParentToken() {
        return $this->parentToken;
    }

}
