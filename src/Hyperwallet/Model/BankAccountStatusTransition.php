<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Bank Account Status Transition
 *
 * @package Hyperwallet\Model
 */
class BankAccountStatusTransition extends StatusTransition {

    const TRANSITION_DE_ACTIVATED = 'DE-ACTIVATED';

    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}