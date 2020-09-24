<?php


namespace Hyperwallet\Model;

/**
 * Represents a V4 Venmo Account Status Transition
 *
 * @package Hyperwallet\Model
 */
class VenmoAccountStatusTransition extends StatusTransition {
    const TRANSITION_DE_ACTIVATED = 'DE_ACTIVATED';

    /**
     * Creates a instance of VenmoAccountStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}
