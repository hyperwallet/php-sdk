<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 Payment Status Transition
 *
 * @package Hyperwallet\Model
 */
class PaymentStatusTransition extends StatusTransition {

    const TRANSITION_CANCELLED = 'CANCELLED';

    /**
     * Creates a instance of PaymentStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}
