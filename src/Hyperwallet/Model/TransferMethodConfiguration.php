<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Program
 *
 * @property string[] $countries
 * @property string[] $currencies
 * @property string $type
 * @property string $profileType
 * @property array $fields
 *
 * @package Hyperwallet\Model
 */
class TransferMethodConfiguration extends BaseModel {

    /**
     * @internal
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('countries', 'currencies', 'type', 'profileType', 'fields');

    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * @return string[]
     */
    public function getCountries() {
        return $this->countries;
    }

    /**
     * @return string[]
     */
    public function getCurrencies() {
        return $this->currencies;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getProfileType() {
        return $this->profileType;
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }

}
