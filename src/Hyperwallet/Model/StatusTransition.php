<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 Status Transition
 *
 * @property string $token The status transition token
 * @property \DateTime $createdOn The status transition creation date
 *
 * @property string $transition The status transition
 *
 * @property string $fromStatus The old status
 * @property string $toStatus The new status
 * @property string $notes The status transition notes
 *
 * @package Hyperwallet\Model
 */
abstract class StatusTransition extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    protected static $READ_ONLY_FIELDS = array('token', 'createdOn', 'fromStatus', 'toStatus');

    /**
     * Creates a instance of StatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the status transition token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the status transition token
     *
     * @param string $token
     * @return StatusTransition
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the status transition creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the status transition
     *
     * @return string
     */
    public function getTransition() {
        return $this->transition;
    }

    /**
     * Set the status transition
     *
     * @param string $transition
     * @return StatusTransition
     */
    public function setTransition($transition) {
        $this->transition = $transition;
        return $this;
    }

    /**
     * Get the old status
     *
     * @return string
     */
    public function getFromStatus() {
        return $this->fromStatus;
    }

    /**
     * Get the new status
     *
     * @return string
     */
    public function getToStatus() {
        return $this->toStatus;
    }

    /**
     * Get the status transition notes
     *
     * @return string
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * Set the status transition notes
     *
     * @param string $notes
     * @return StatusTransition
     */
    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

}
