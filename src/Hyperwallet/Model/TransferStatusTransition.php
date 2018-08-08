<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Transfer Status Transition
 *
 * @package Hyperwallet\Model
 */
class TransferStatusTransition extends StatusTransition {

    const TRANSITION_QUOTED = 'QUOTED';
    const TRANSITION_SCHEDULED = 'SCHEDULED';
    const TRANSITION_IN_PROGRESS = 'IN_PROGRESS';
    const TRANSITION_VERIFICATION_REQUIRED = 'VERIFICATION_REQUIRED';
    const TRANSITION_COMPLETED = 'COMPLETED';
    const TRANSITION_CANCELLED = 'CANCELLED';
    const TRANSITION_RETURNED = 'RETURNED';
    const TRANSITION_FAILED = 'FAILED';
    const TRANSITION_EXPIRED = 'EXPIRED';

    /**
     * Creates a instance of TransferStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}