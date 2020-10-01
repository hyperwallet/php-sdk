<?php
namespace Hyperwallet\Model;

/**
 * Represents a v4 User Status Transition
 *
 * @package Hyperwallet\Model
 */
class UserStatusTransition extends StatusTransition {

    /**
     * Creates a instance of UserStatusTransition
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct($properties);
    }

}
