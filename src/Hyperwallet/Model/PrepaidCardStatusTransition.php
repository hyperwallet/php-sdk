<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Prepaid Card Status Transition
 *
 * @package Hyperwallet\Model
 */
class PrepaidCardStatusTransition extends StatusTransition {

    const TRANSITION_DE_ACTIVATED = 'DE_ACTIVATED';
    const TRANSITION_SUSPENDED = 'SUSPENDED';
    const TRANSITION_UNSUSPENDED = 'UNSUSPENDED';
    const TRANSITION_LOST_OR_STOLEN = 'LOST_OR_STOLEN';
    const TRANSITION_LOCKED = 'LOCKED';
    const TRANSITION_UNLOCKED = 'UNLOCKED';

    /**
     * Creates a instance of PrepaidCardStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}