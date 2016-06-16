<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Balance
 *
 * @property string $currency
 * @property string $amount
 *
 * @package Hyperwallet\Model
 */
class Balance extends BaseModel {

    /**
     * @internal
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('currency', 'amount');

    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }

}
