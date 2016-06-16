<?php
namespace Hyperwallet\Model;

/**
 * This is the base model for handling updates and insert field calculations
 *
 * @package Hyperwallet\Model
 */
class BaseModel {

    /**
     * @internal
     *
     * @var array
     */
    private $properties;

    /**
     * @internal
     *
     * @var array
     */
    private $updatedProperties;

    /**
     * @internal
     *
     * @var array
     */
    private $readOnlyProperties;

    public function __construct(array $readOnlyProperties = array(), array $properties = array()) {
        $this->readOnlyProperties = $readOnlyProperties;
        $this->properties = $properties;
        $this->updatedProperties = array();
    }

    /**
     * @internal
     *
     * Magic get method
     *
     * @param $key
     * @return mixed
     */
    public function __get($key) {
        if ($this->__isset($key)) {
            return $this->properties[$key];
        }
        return null;
    }

    /**
     * @internal
     *
     * Magic set method
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value) {
        if (!in_array($key, $this->updatedProperties)) {
            $this->updatedProperties[] = $key;
        }

        $this->properties[$key] = $value;
    }

    /**
     * @internal
     *
     * Magic isset method
     *
     * @param $key
     * @return bool
     *
     * @access private
     */
    public function __isset($key) {
        return isset($this->properties[$key]);
    }

    /**
     * @internal
     *
     * Magic unset method
     *
     * @param $key
     */
    public function __unset($key) {
        unset($this->updatedProperties[array_search($key, $this->updatedProperties)]);
        unset($this->properties[$key]);
    }

    /**
     * @internal
     *
     * @return array
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * @internal
     *
     * @return array
     */
    public function getPropertiesForCreate() {
        return array_diff_key($this->properties, array_flip($this->readOnlyProperties));
    }

    /**
     * @internal
     * 
     * @return array
     */
    public function getPropertiesForUpdate() {
        return array_intersect_key($this->getPropertiesForCreate(), array_flip($this->updatedProperties));
    }

}
