<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Status Transition
 *
 * @property string $token
 * @property \DateTime $createdOn
 *
 * @property string $transition
 *
 * @property string $fromStatus
 * @property string $toStatus
 * @property string $notes
 *
 * @package Hyperwallet\Model
 */
abstract class StatusTransition extends BaseModel {

    /**
     * @internal
     *
     * @var string[]
     */
    protected static $READ_ONLY_FIELDS = array('token', 'createdOn', 'fromStatus', 'toStatus');
    
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
     * @param string $token
     * @return StatusTransition
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
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
    public function getTransition() {
        return $this->transition;
    }

    /**
     * @param string $transition
     * @return StatusTransition
     */
    public function setTransition($transition) {
        $this->transition = $transition;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromStatus() {
        return $this->fromStatus;
    }

    /**
     * @return string
     */
    public function getToStatus() {
        return $this->toStatus;
    }

    /**
     * @return string
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return StatusTransition
     */
    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

}
