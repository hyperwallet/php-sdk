<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Bank Account
 *
 * @property string $token The bank account token
 * @property string $type The transfer method type
 *
 * @property string $status The bank account status
 * @property \DateTime $createdOn The bank account creation date
 *
 * @property string $transferMethodCountry The transfer method country
 * @property string $transferMethodCurrency The transfer method currency
 *
 * @property string $bankName The bank name
 * @property string $bankId The bank id
 * @property string $branchName The branch name
 * @property string $branchId The branch id
 * @property string $bankAccountId The bank account id
 * @property string $bankAccountPurpose The bank account purpose
 *
 * @property string $branchAddressLine1 The branch address line 1
 * @property string $branchAddressLine2 The branch address line 2
 * @property string $branchCity The branch city
 * @property string $branchStateProvince The branch state or province
 * @property string $branchCountry The branch country
 * @property string $branchPostalCode The branch postal code
 *
 * @property string $wireInstructions The wire instructions
 *
 * @property string $intermediaryBankId The intermediary bank id
 * @property string $intermediaryBankName The intermediary bank name
 * @property string $intermediaryBankAccountId The intermediary bank account id
 *
 * @property string $intermediaryAddressLine1 The intermediary address line 1
 * @property string $intermediaryAddressLine2 The intermediary address line 2
 * @property string $intermediaryCity The intermediary city
 * @property string $intermediaryStateProvince The intermediary state or province
 * @property string $intermediaryCountry The intermediary country
 * @property string $intermediaryPostalCode The intermediary postal code
 *
 * @property string $profileType The profile type
 *
 * @property string $businessName The business name
 * @property string $businessOperatingName The business operating name
 * @property string $businessRegistrationId The business registration id
 * @property string $businessRegistrationCountry The business registration country
 *
 * @property string $firstName The first name
 * @property string $middleName The middle name
 * @property string $lastName The last name
 * @property \DateTime $dateOfBirth The date of birth
 * @property string $countryOfBirth The country of birth
 * @property string $countryOfNationality The country of nationality
 * @property string $phoneNumber The phone number
 * @property string $mobileNumber The mobile number
 *
 * @property string $governmentId The government id
 *
 * @property string $addressLine1 The address line 1
 * @property string $city The city
 * @property string $stateProvince The state or province
 * @property string $country The country
 * @property string $postalCode The postal code
 *
 * @property string $taxId The registration number
 * @property string $taxReasonId The tax registration reason code
 *
 * @package Hyperwallet\Model
 */
class BankAccount extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn');

    const TYPE_BANK_ACCOUNT = 'BANK_ACCOUNT';
    const TYPE_WIRE_ACCOUNT = 'WIRE_ACCOUNT';

    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_INVALID = 'INVALID';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';

    const PROFILE_TYPE_INDIVIDUAL = 'INDIVIDUAL';
    const PROFILE_TYPE_BUSINESS = 'BUSINESS';

    /**
     * Creates a instance of BankAccount
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the bank account token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the bank account token
     *
     * @param string $token
     * @return BankAccount
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the bank account id
     *
     * @return string
     */
    public function getBankAccountId() {
        return $this->bankAccountId;
    }

    /**
     * Set the bank account id
     *
     * @param string $bankAccountId
     * @return BankAccount
     */
    public function setBankAccountId($bankAccountId) {
        $this->bankAccountId = $bankAccountId;
        return $this;
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
     * Set the transfer method type
     *
     * @param string $type
     * @return BankAccount
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the transfer method country
     *
     * @return string
     */
    public function getTransferMethodCountry() {
        return $this->transferMethodCountry;
    }

    /**
     * Set the transfer method country
     *
     * @param string $transferMethodCountry
     * @return BankAccount
     */
    public function setTransferMethodCountry($transferMethodCountry) {
        $this->transferMethodCountry = $transferMethodCountry;
        return $this;
    }

    /**
     * Get the transfer method currency
     *
     * @return string
     */
    public function getTransferMethodCurrency() {
        return $this->transferMethodCurrency;
    }

    /**
     * Set the transfer method currency
     *
     * @param string $transferMethodCurrency
     * @return BankAccount
     */
    public function setTransferMethodCurrency($transferMethodCurrency) {
        $this->transferMethodCurrency = $transferMethodCurrency;
        return $this;
    }

    /**
     * Get the bank name
     *
     * @return string
     */
    public function getBankName() {
        return $this->bankName;
    }

    /**
     * Set the bank name
     *
     * @param string $bankName
     * @return BankAccount
     */
    public function setBankName($bankName) {
        $this->bankName = $bankName;
        return $this;
    }

    /**
     * Get the bank id
     *
     * @return string
     */
    public function getBankId() {
        return $this->bankId;
    }

    /**
     * Set the bank id
     *
     * @param string $bankId
     * @return BankAccount
     */
    public function setBankId($bankId) {
        $this->bankId = $bankId;
        return $this;
    }

    /**
     * Get the branch name
     *
     * @return string
     */
    public function getBranchName() {
        return $this->branchName;
    }

    /**
     * Set the branch name
     *
     * @param string $branchName
     * @return BankAccount
     */
    public function setBranchName($branchName) {
        $this->branchName = $branchName;
        return $this;
    }

    /**
     * Get the branch id
     *
     * @return string
     */
    public function getBranchId() {
        return $this->branchId;
    }

    /**
     * Set the branch id
     *
     * @param string $branchId
     * @return BankAccount
     */
    public function setBranchId($branchId) {
        $this->branchId = $branchId;
        return $this;
    }

    /**
     * Get the bank account status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the bank account creation date
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the bank account purpose
     *
     * @return string
     */
    public function getBankAccountPurpose() {
        return $this->bankAccountPurpose;
    }

    /**
     * Set the bank account purpose
     *
     * @param string $bankAccountPurpose
     * @return BankAccount
     */
    public function setBankAccountPurpose($bankAccountPurpose) {
        $this->bankAccountPurpose = $bankAccountPurpose;
        return $this;
    }

    /**
     * Get the branch address line 1
     *
     * @return string
     */
    public function getBranchAddressLine1() {
        return $this->branchAddressLine1;
    }

    /**
     * Set the branch address line 1
     *
     * @param string $branchAddressLine1
     * @return BankAccount
     */
    public function setBranchAddressLine1($branchAddressLine1) {
        $this->branchAddressLine1 = $branchAddressLine1;
        return $this;
    }

    /**
     * Get the branch address line 2
     *
     * @return string
     */
    public function getBranchAddressLine2() {
        return $this->branchAddressLine2;
    }

    /**
     * Set the branch address line 2
     *
     * @param string $branchAddressLine2
     * @return BankAccount
     */
    public function setBranchAddressLine2($branchAddressLine2) {
        $this->branchAddressLine2 = $branchAddressLine2;
        return $this;
    }

    /**
     * Get the branch city
     *
     * @return string
     */
    public function getBranchCity() {
        return $this->branchCity;
    }

    /**
     * Set the branch city
     *
     * @param string $branchCity
     * @return BankAccount
     */
    public function setBranchCity($branchCity) {
        $this->branchCity = $branchCity;
        return $this;
    }

    /**
     * Get the branch state or province
     *
     * @return string
     */
    public function getBranchStateProvince() {
        return $this->branchStateProvince;
    }

    /**
     * Set the branch state or province
     *
     * @param string $branchStateProvince
     * @return BankAccount
     */
    public function setBranchStateProvince($branchStateProvince) {
        $this->branchStateProvince = $branchStateProvince;
        return $this;
    }

    /**
     * Get the branch country
     *
     * @return string
     */
    public function getBranchCountry() {
        return $this->branchCountry;
    }

    /**
     * Set the branch country
     *
     * @param string $branchCountry
     * @return BankAccount
     */
    public function setBranchCountry($branchCountry) {
        $this->branchCountry = $branchCountry;
        return $this;
    }

    /**
     * Get the branch postal code
     *
     * @return string
     */
    public function getBranchPostalCode() {
        return $this->branchPostalCode;
    }

    /**
     * Set the branch postal code
     *
     * @param string $branchPostalCode
     * @return BankAccount
     */
    public function setBranchPostalCode($branchPostalCode) {
        $this->branchPostalCode = $branchPostalCode;
        return $this;
    }

    /**
     * Get the wire instructions
     *
     * @return string
     */
    public function getWireInstructions() {
        return $this->wireInstructions;
    }

    /**
     * Set the wire instructions
     *
     * @param string $wireInstructions
     * @return BankAccount
     */
    public function setWireInstructions($wireInstructions) {
        $this->wireInstructions = $wireInstructions;
        return $this;
    }

    /**
     * Get the intermediary bank id
     *
     * @return string
     */
    public function getIntermediaryBankId() {
        return $this->intermediaryBankId;
    }

    /**
     * Set the intermediary bank id
     *
     * @param string $intermediaryBankId
     * @return BankAccount
     */
    public function setIntermediaryBankId($intermediaryBankId) {
        $this->intermediaryBankId = $intermediaryBankId;
        return $this;
    }

    /**
     * Get the intermediary bank name
     *
     * @return string
     */
    public function getIntermediaryBankName() {
        return $this->intermediaryBankName;
    }

    /**
     * Set the intermediary bank name
     *
     * @param string $intermediaryBankName
     * @return BankAccount
     */
    public function setIntermediaryBankName($intermediaryBankName) {
        $this->intermediaryBankName = $intermediaryBankName;
        return $this;
    }

    /**
     * Get the intermediary bank account id
     *
     * @return string
     */
    public function getIntermediaryBankAccountId() {
        return $this->intermediaryBankAccountId;
    }

    /**
     * Set the intermediary bank account id
     *
     * @param string $intermediaryBankAccountId
     * @return BankAccount
     */
    public function setIntermediaryBankAccountId($intermediaryBankAccountId) {
        $this->intermediaryBankAccountId = $intermediaryBankAccountId;
        return $this;
    }

    /**
     * Get the intermediary address line 1
     *
     * @return string
     */
    public function getIntermediaryAddressLine1() {
        return $this->intermediaryAddressLine1;
    }

    /**
     * Set the intermediary address line 1
     *
     * @param string $intermediaryAddressLine1
     * @return BankAccount
     */
    public function setIntermediaryAddressLine1($intermediaryAddressLine1) {
        $this->intermediaryAddressLine1 = $intermediaryAddressLine1;
        return $this;
    }

    /**
     * Get the intermediary address line 2
     *
     * @return string
     */
    public function getIntermediaryAddressLine2() {
        return $this->intermediaryAddressLine2;
    }

    /**
     * Set the intermediary address line 2
     *
     * @param string $intermediaryAddressLine2
     * @return BankAccount
     */
    public function setIntermediaryAddressLine2($intermediaryAddressLine2) {
        $this->intermediaryAddressLine2 = $intermediaryAddressLine2;
        return $this;
    }

    /**
     * Get the intermediary city
     *
     * @return string
     */
    public function getIntermediaryCity() {
        return $this->intermediaryCity;
    }

    /**
     * Set the intermediary city
     *
     * @param string $intermediaryCity
     * @return BankAccount
     */
    public function setIntermediaryCity($intermediaryCity) {
        $this->intermediaryCity = $intermediaryCity;
        return $this;
    }

    /**
     * Get the intermediary state or province
     *
     * @return string
     */
    public function getIntermediaryStateProvince() {
        return $this->intermediaryStateProvince;
    }

    /**
     * Set the intermediary state or province
     *
     * @param string $intermediaryStateProvince
     * @return BankAccount
     */
    public function setIntermediaryStateProvince($intermediaryStateProvince) {
        $this->intermediaryStateProvince = $intermediaryStateProvince;
        return $this;
    }

    /**
     * Get the intermediary country
     *
     * @return string
     */
    public function getIntermediaryCountry() {
        return $this->intermediaryCountry;
    }

    /**
     * Set the intermediary country
     *
     * @param string $intermediaryCountry
     * @return BankAccount
     */
    public function setIntermediaryCountry($intermediaryCountry) {
        $this->intermediaryCountry = $intermediaryCountry;
        return $this;
    }

    /**
     * Get the intermediary postal code
     *
     * @return string
     */
    public function getIntermediaryPostalCode() {
        return $this->intermediaryPostalCode;
    }

    /**
     * Set the intermediary postal code
     *
     * @param string $intermediaryPostalCode
     * @return BankAccount
     */
    public function setIntermediaryPostalCode($intermediaryPostalCode) {
        $this->intermediaryPostalCode = $intermediaryPostalCode;
        return $this;
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
     * Set the profile type
     *
     * @param string $profileType
     * @return BankAccount
     */
    public function setProfileType($profileType) {
        $this->profileType = $profileType;
        return $this;
    }

    /**
     * Get the business name
     *
     * @return string
     */
    public function getBusinessName() {
        return $this->businessName;
    }

    /**
     * Set the business name
     *
     * @param string $businessName
     * @return BankAccount
     */
    public function setBusinessName($businessName) {
        $this->businessName = $businessName;
        return $this;
    }

    /**
     * Get the business operating name
     *
     * @return string
     */
    public function getBusinessOperatingName()
    {
        return $this->businessOperatingName;
    }

    /**
     * Set the business operating name
     *
     * @param string $businessOperatingName
     * @return BankAccount
     */
    public function setBusinessOperatingName($businessOperatingName)
    {
        $this->businessOperatingName = $businessOperatingName;
        return $this;
    }

    /**
     * Get the business registration id
     *
     * @return string
     */
    public function getBusinessRegistrationId() {
        return $this->businessRegistrationId;
    }

    /**
     * Set the business registration id
     *
     * @param string $businessRegistrationId
     * @return BankAccount
     */
    public function setBusinessRegistrationId($businessRegistrationId) {
        $this->businessRegistrationId = $businessRegistrationId;
        return $this;
    }

    /**
     * Get the business registration country
     *
     * @return string
     */
    public function getBusinessRegistrationCountry() {
        return $this->businessRegistrationCountry;
    }

    /**
     * Set the business registration country
     *
     * @param string $businessRegistrationCountry
     * @return BankAccount
     */
    public function setBusinessRegistrationCountry($businessRegistrationCountry) {
        $this->businessRegistrationCountry = $businessRegistrationCountry;
        return $this;
    }

    /**
     * Get the first name
     *
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set the first name
     *
     * @param string $firstName
     * @return BankAccount
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get the middle name
     *
     * @return string
     */
    public function getMiddleName() {
        return $this->middleName;
    }

    /**
     * Set the middle name
     *
     * @param string $middleName
     * @return BankAccount
     */
    public function setMiddleName($middleName) {
        $this->middleName = $middleName;
        return $this;
    }

    /**
     * Get the last name
     *
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * Set the last name
     *
     * @param string $lastName
     * @return BankAccount
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get the date of birth
     *
     * @return \DateTime|null
     */
    public function getDateOfBirth() {
        return $this->dateOfBirth ? new \DateTime($this->dateOfBirth) : null;
    }

    /**
     * Set the date of birth
     *
     * @param \DateTime|null $dateOfBirth
     * @return BankAccount
     */
    public function setDateOfBirth(\DateTime $dateOfBirth = null) {
        $this->dateOfBirth = $dateOfBirth == null ? null : $dateOfBirth->format('Y-m-d');
        return $this;
    }

    /**
     * Get the country of birth
     *
     * @return string
     */
    public function getCountryOfBirth() {
        return $this->countryOfBirth;
    }

    /**
     * Set the country of birth
     *
     * @param string $countryOfBirth
     * @return BankAccount
     */
    public function setCountryOfBirth($countryOfBirth) {
        $this->countryOfBirth = $countryOfBirth;
        return $this;
    }

    /**
     * Get the country of nationality
     *
     * @return string
     */
    public function getCountryOfNationality() {
        return $this->countryOfNationality;
    }

    /**
     * Set the country of nationality
     *
     * @param string $countryOfNationality
     * @return BankAccount
     */
    public function setCountryOfNationality($countryOfNationality) {
        $this->countryOfNationality = $countryOfNationality;
        return $this;
    }

    /**
     * Get the phone number
     *
     * @return string
     */
    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    /**
     * Set the phone number
     *
     * @param string $phoneNumber
     * @return BankAccount
     */
    public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * Get the mobile number
     *
     * @return string
     */
    public function getMobileNumber() {
        return $this->mobileNumber;
    }

    /**
     * Set the mobile number
     *
     * @param string $mobileNumber
     * @return BankAccount
     */
    public function setMobileNumber($mobileNumber) {
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    /**
     * Get the government id
     *
     * @return string
     */
    public function getGovernmentId() {
        return $this->governmentId;
    }

    /**
     * Set the government id
     *
     * @param string $governmentId
     * @return BankAccount
     */
    public function setGovernmentId($governmentId) {
        $this->governmentId = $governmentId;
        return $this;
    }

    /**
     * Get the address line 1
     *
     * @return string
     */
    public function getAddressLine1() {
        return $this->addressLine1;
    }

    /**
     * Set the address line 1
     *
     * @param string $addressLine1
     * @return BankAccount
     */
    public function setAddressLine1($addressLine1) {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * Get the city
     *
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Set the city
     *
     * @param string $city
     * @return BankAccount
     */
    public function setCity($city) {
        $this->city = $city;
        return $this;
    }

    /**
     * Get the state or province
     *
     * @return string
     */
    public function getStateProvince() {
        return $this->stateProvince;
    }

    /**
     * Set the state or province
     *
     * @param string $stateProvince
     * @return BankAccount
     */
    public function setStateProvince($stateProvince) {
        $this->stateProvince = $stateProvince;
        return $this;
    }

    /**
     * Get the country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set the country
     *
     * @param string $country
     * @return BankAccount
     */
    public function setCountry($country) {
        $this->country = $country;
        return $this;
    }

    /**
     * Get the postal code
     *
     * @return string
     */
    public function getPostalCode() {
        return $this->postalCode;
    }

    /**
     * Set the postal code
     *
     * @param string $postalCode
     * @return BankAccount
     */
    public function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * Get the registration number
     *
     * @return string
     */
    public function getTaxId() {
        return $this->taxId;
    }

    /**
     * Set the registration number
     *
     * @param string $taxId
     * @return BankAccount
     */
    public function setTaxId(string $taxId) {
        $this->taxId = $taxId;
        return $this;
    }

    /**
     * Get the tax registration reason code
     *
     * @return string
     */
    public function getTaxReasonId() {
        return $this->taxReasonId;
    }

    /**
     * Set the tax registration reason code
     *
     * @param string $taxReasonId
     * @return BankAccount
     */
    public function setTaxReasonId(string $taxReasonId) {
        $this->taxReasonId = $taxReasonId;
        return $this;
    }

}
