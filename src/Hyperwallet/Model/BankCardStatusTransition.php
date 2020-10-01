<?php
namespace Hyperwallet\Model;

/**
 * Represents a v4 Bank Card Status Transition
 *
 * @package Hyperwallet\Model
 */
class BankCardStatusTransition extends StatusTransition {

    const TRANSITION_DE_ACTIVATED = 'DE_ACTIVATED';
    /**
     * Creates a instance of BankCardStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}
