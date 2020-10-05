<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 User Status Transition
 *
 * @package Hyperwallet\Model
 */
class UserStatusTransition extends StatusTransition {
    const TRANSITION_REQUESTED = 'REQUESTED';
    const TRANSITION_EXPIRED = 'EXPIRED';
    const TRANSITION_VERIFIED = 'VERIFIED';
    const TRANSITION_READY_FOR_REVIEW = 'READY_FOR_REVIEW';
    const TRANSITION_NOT_REQUIRED = 'NOT_REQUIRED';
    const TRANSITION_FAILED = 'FAILED';
    const TRANSITION_UNDER_REVIEW = 'UNDER_REVIEW';

    /**
     * Creates a instance of UserStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}