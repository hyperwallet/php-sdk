<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 Program
 *
 * @property string[] $countries The transfer method countries
 * @property string[] $currencies The transfer method currencies
 * @property string $type The transfer method type
 * @property string $profileType The profile type
 * @property array $fields All transfer method field definitions
 *
 * @package Hyperwallet\Model
 */
class TransferMethodConfiguration extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('countries', 'currencies', 'type', 'profileType', 'fields');

    /**
     * Creates a instance of TransferMethodConfiguration
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the transfer method countries
     *
     * @return string[]
     */
    public function getCountries() {
        return $this->countries;
    }

    /**
     * Get the transfer method currencies
     *
     * @return string[]
     */
    public function getCurrencies() {
        return $this->currencies;
    }

    /**
     * Get the transfer method type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get the profile type
     *
     * @return string
     */
    public function getProfileType() {
        return $this->profileType;
    }

    /**
     * Get all transfer method field definitions
     *
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

}
