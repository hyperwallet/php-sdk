<?php

namespace Hyperwallet\Model;

/**
 * Represents a V3 PayPal Account Status Transition
 *
 * @package Hyperwallet\Model
 */
class PayPalAccountStatusTransition extends StatusTransition
{

    const TRANSITION_DE_ACTIVATED = 'DE_ACTIVATED';

    /**
     * Creates a instance of PayPalAccountStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array())
    {
        parent::__construct($properties);
    }

}
