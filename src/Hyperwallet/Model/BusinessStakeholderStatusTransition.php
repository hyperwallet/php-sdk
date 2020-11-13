<?php


namespace Hyperwallet\Model;

/**
 * Represents a V4 Business Stakeholder Status Transition
 *
 * @package Hyperwallet\Model
 */
class BusinessStakeholderStatusTransition extends StatusTransition
{
    const TRANSITION_ACTIVATED = 'ACTIVATED';
    const TRANSITION_DE_ACTIVATED = 'DE_ACTIVATED';

    /**
     * Creates a instance of BusinessStakeholderStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array())
    {
        parent::__construct($properties);
    }
}