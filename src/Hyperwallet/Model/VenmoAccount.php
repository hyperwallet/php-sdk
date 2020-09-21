<?php


namespace Hyperwallet\Model;
/**
 * Represents a V3 Venmo Account
 *
 * @property string $token The Venmo account token
 * @property string $status The Venmo account status
 * @property \DateTime $createdOn The Venmo account creation date
 * @property string $type The transfer method type
 * @property string $transferMethodCountry The transfer method country
 * @property string $transferMethodCurrency The transfer method currency
 * @property string $isDefaultTransferMethod The is default transfer method
 * @property string $accountId The Venmo account
 *
 * @package Hyperwallet\Model
 */
class VenmoAccount extends BaseModel
{
    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn');

    /**
     * Creates a instance of VenmoAccount
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array())
    {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the Venmo account token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the Venmo account token
     *
     * @param string $token
     * @return VenmoAccount
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the Venmo account status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the Venmo account creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the transfer method type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the transfer method type
     *
     * @param string $type
     * @return VenmoAccount
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the transfer method country
     *
     * @return string
     */
    public function getTransferMethodCountry()
    {
        return $this->transferMethodCountry;
    }

    /**
     * Set the transfer method country
     *
     * @param string $transferMethodCountry
     * @return VenmoAccount
     */
    public function setTransferMethodCountry($transferMethodCountry)
    {
        $this->transferMethodCountry = $transferMethodCountry;
        return $this;
    }

    /**
     * Get the transfer method currency
     *
     * @return string
     */
    public function getTransferMethodCurrency()
    {
        return $this->transferMethodCurrency;
    }

    /**
     * Set the transfer method currency
     *
     * @param string $transferMethodCurrency
     * @return VenmoAccount
     */
    public function setTransferMethodCurrency($transferMethodCurrency)
    {
        $this->transferMethodCurrency = $transferMethodCurrency;
        return $this;
    }

    /**
     * Get the is default transfer method
     *
     * @return string
     */
    public function getIsDefaultTransferMethod()
    {
        return $this->isDefaultTransferMethod;
    }

    /**
     * Set the is default transfer method
     *
     * @param string $isDefaultTransferMethod
     * @return VenmoAccount
     */
    public function setIsDefaultTransferMethod($isDefaultTransferMethod)
    {
        $this->isDefaultTransferMethod = $isDefaultTransferMethod;
        return $this;
    }

    /**
     * Get the Venmo account
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Set the Venmo account
     *
     * @param string $accountId
     * @return VenmoAccount
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }


}
