<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Paper Check Status Transition
 *
 * @package Hyperwallet\Model
 */
class PaperCheckStatusTransition extends StatusTransition {

    const TRANSITION_DE_ACTIVATED = 'DE_ACTIVATED';

    /**
     * Creates a instance of PaperCheckStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}