<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Balance
 *
 * @property string $currency The currency
 * @property string $amount The amount
 *
 * @package Hyperwallet\Model
 */
class Balance extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('currency', 'amount');

    public static function FILTERS_ARRAY_USER() {
        return array('currency', 'createdBefore', 'createdAfter', 'sortBy', 'offset', 'limit');
    }

    public static function FILTERS_ARRAY_PREPAID_CARD() {
        return array('createdBefore', 'createdAfter');
    }

    public static function FILTERS_ARRAY_ACCOUNT() {
        return array('currency', 'createdBefore', 'createdAfter', 'sortBy', 'offset', 'limit');
    }

    /**
     * Creates a instance of Balance
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the currency
     *
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * Get the amount
     *
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }

}
