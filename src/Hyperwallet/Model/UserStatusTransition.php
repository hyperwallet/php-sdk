<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 User Status Transition
 *
 * @package Hyperwallet\Model
 */
class UserStatusTransition extends StatusTransition {
    const TRANSITION_FROZEN = 'FROZEN';
    const TRANSITION_ACTIVATED = 'ACTIVATED';
    const TRANSITION_LOCKED = 'LOCKED';
    const TRANSITION_DE_ACTIVATED = 'DE_ACTIVATED';
    const TRANSITION_PRE_ACTIVATED = 'PRE-ACTIVATED';

    /**
     * Creates a instance of UserStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}