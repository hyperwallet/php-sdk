<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 Bank Account
 *
 * @property string $token
 * @property string $type
 *
 * @property string $status
 * @property \DateTime $createdOn
 *
 * @property string $transferMethodCountry
 * @property string $transferMethodCurrency
 *
 * @property string $bankName
 * @property string $bankId
 * @property string $branchName
 * @property string $branchId
 * @property string $bankAccountId
 * @property string $bankAccountRelationship
 * @property string $bankAccountPurpose
 *
 * @property string $branchAddressLine1
 * @property string $branchAddressLine2
 * @property string $branchCity
 * @property string $branchStateProvince
 * @property string $branchCountry
 * @property string $branchPostalCode
 *
 * @property string $wireInstructions
 *
 * @property string $intermediaryBankId
 * @property string $intermediaryBankName
 * @property string $intermediaryBankAccountId
 *
 * @property string $intermediaryAddressLine1
 * @property string $intermediaryAddressLine2
 * @property string $intermediaryCity
 * @property string $intermediaryStateProvince
 * @property string $intermediaryCountry
 * @property string $intermediaryPostalCode
 *
 * @property string $profileType
 *
 * @property string $businessName
 * @property string $businessRegistrationId
 * @property string $businessRegistrationCountry
 *
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property \DateTime $dateOfBirth
 * @property string $countryOfBirth
 * @property string $countryOfNationality
 * @property string $phoneNumber
 * @property string $mobileNumber
 *
 * @property string $governmentId
 *
 * @property string $addressLine1
 * @property string $city
 * @property string $stateProvince
 * @property string $country
 * @property string $postalCode
 *
 * @package Hyperwallet\Model
 */
class BankAccount extends BaseModel {

    /**
     * @internal
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn');

    const TYPE_BANK_ACCOUNT = 'BANK_ACCOUNT';
    const TYPE_WIRE_ACCOUNT = 'WIRE_ACCOUNT';

    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_INVALID = 'INVALID';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';

    const BANK_ACCOUNT_RELATIONSHIP_SELF = 'SELF';
    const BANK_ACCOUNT_RELATIONSHIP_JOINT_ACCOUNT = 'JOINT_ACCOUNT';
    const BANK_ACCOUNT_RELATIONSHIP_SPOUSE = 'SPOUSE';
    const BANK_ACCOUNT_RELATIONSHIP_RELATIVE = 'RELATIVE';
    const BANK_ACCOUNT_RELATIONSHIP_BUSINESS_PARTNER = 'BUSINESS_PARTNER';
    const BANK_ACCOUNT_RELATIONSHIP_UPLINE = 'UPLINE';
    const BANK_ACCOUNT_RELATIONSHIP_DOWNLINE = 'DOWNLINE';
    const BANK_ACCOUNT_RELATIONSHIP_OWN_COMPANY = 'OWN_COMPANY';
    const BANK_ACCOUNT_RELATIONSHIP_BILL_PAYMENT = 'BILL_PAYMENT';
    const BANK_ACCOUNT_RELATIONSHIP_OTHER = 'OTHER';

    const PROFILE_TYPE_INDIVIDUAL = 'INDIVIDUAL';
    const PROFILE_TYPE_BUSINESS = 'BUSINESS';

    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @param string $token
     * @return BankAccount
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getBankAccountId() {
        return $this->bankAccountId;
    }

    /**
     * @param string $bankAccountId
     * @return BankAccount
     */
    public function setBankAccountId($bankAccountId) {
        $this->bankAccountId = $bankAccountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     * @return BankAccount
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransferMethodCountry() {
        return $this->transferMethodCountry;
    }

    /**
     * @param string $transferMethodCountry
     * @return BankAccount
     */
    public function setTransferMethodCountry($transferMethodCountry) {
        $this->transferMethodCountry = $transferMethodCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransferMethodCurrency() {
        return $this->transferMethodCurrency;
    }

    /**
     * @param string $transferMethodCurrency
     * @return BankAccount
     */
    public function setTransferMethodCurrency($transferMethodCurrency) {
        $this->transferMethodCurrency = $transferMethodCurrency;
        return $this;
    }

    /**
     * @return string
     */
    public function getBankName() {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     * @return BankAccount
     */
    public function setBankName($bankName) {
        $this->bankName = $bankName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBankId() {
        return $this->bankId;
    }

    /**
     * @param string $bankId
     * @return BankAccount
     */
    public function setBankId($bankId) {
        $this->bankId = $bankId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchName() {
        return $this->branchName;
    }

    /**
     * @param string $branchName
     * @return BankAccount
     */
    public function setBranchName($branchName) {
        $this->branchName = $branchName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchId() {
        return $this->branchId;
    }

    /**
     * @param string $branchId
     * @return BankAccount
     */
    public function setBranchId($branchId) {
        $this->branchId = $branchId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * @return string
     */
    public function getBankAccountRelationship() {
        return $this->bankAccountRelationship;
    }

    /**
     * @param string $bankAccountRelationship
     * @return BankAccount
     */
    public function setBankAccountRelationship($bankAccountRelationship) {
        $this->bankAccountRelationship = $bankAccountRelationship;
        return $this;
    }

    /**
     * @return string
     */
    public function getBankAccountPurpose() {
        return $this->bankAccountPurpose;
    }

    /**
     * @param string $bankAccountPurpose
     * @return BankAccount
     */
    public function setBankAccountPurpose($bankAccountPurpose) {
        $this->bankAccountPurpose = $bankAccountPurpose;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchAddressLine1() {
        return $this->branchAddressLine1;
    }

    /**
     * @param string $branchAddressLine1
     * @return BankAccount
     */
    public function setBranchAddressLine1($branchAddressLine1) {
        $this->branchAddressLine1 = $branchAddressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchAddressLine2() {
        return $this->branchAddressLine2;
    }

    /**
     * @param string $branchAddressLine2
     * @return BankAccount
     */
    public function setBranchAddressLine2($branchAddressLine2) {
        $this->branchAddressLine2 = $branchAddressLine2;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchCity() {
        return $this->branchCity;
    }

    /**
     * @param string $branchCity
     * @return BankAccount
     */
    public function setBranchCity($branchCity) {
        $this->branchCity = $branchCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchStateProvince() {
        return $this->branchStateProvince;
    }

    /**
     * @param string $branchStateProvince
     * @return BankAccount
     */
    public function setBranchStateProvince($branchStateProvince) {
        $this->branchStateProvince = $branchStateProvince;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchCountry() {
        return $this->branchCountry;
    }

    /**
     * @param string $branchCountry
     * @return BankAccount
     */
    public function setBranchCountry($branchCountry) {
        $this->branchCountry = $branchCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getBranchPostalCode() {
        return $this->branchPostalCode;
    }

    /**
     * @param string $branchPostalCode
     * @return BankAccount
     */
    public function setBranchPostalCode($branchPostalCode) {
        $this->branchPostalCode = $branchPostalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getWireInstructions() {
        return $this->wireInstructions;
    }

    /**
     * @param string $wireInstructions
     * @return BankAccount
     */
    public function setWireInstructions($wireInstructions) {
        $this->wireInstructions = $wireInstructions;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryBankId() {
        return $this->intermediaryBankId;
    }

    /**
     * @param string $intermediaryBankId
     * @return BankAccount
     */
    public function setIntermediaryBankId($intermediaryBankId) {
        $this->intermediaryBankId = $intermediaryBankId;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryBankName() {
        return $this->intermediaryBankName;
    }

    /**
     * @param string $intermediaryBankName
     * @return BankAccount
     */
    public function setIntermediaryBankName($intermediaryBankName) {
        $this->intermediaryBankName = $intermediaryBankName;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryBankAccountId() {
        return $this->intermediaryBankAccountId;
    }

    /**
     * @param string $intermediaryBankAccountId
     * @return BankAccount
     */
    public function setIntermediaryBankAccountId($intermediaryBankAccountId) {
        $this->intermediaryBankAccountId = $intermediaryBankAccountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryAddressLine1() {
        return $this->intermediaryAddressLine1;
    }

    /**
     * @param string $intermediaryAddressLine1
     * @return BankAccount
     */
    public function setIntermediaryAddressLine1($intermediaryAddressLine1) {
        $this->intermediaryAddressLine1 = $intermediaryAddressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryAddressLine2() {
        return $this->intermediaryAddressLine2;
    }

    /**
     * @param string $intermediaryAddressLine2
     * @return BankAccount
     */
    public function setIntermediaryAddressLine2($intermediaryAddressLine2) {
        $this->intermediaryAddressLine2 = $intermediaryAddressLine2;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryCity() {
        return $this->intermediaryCity;
    }

    /**
     * @param string $intermediaryCity
     * @return BankAccount
     */
    public function setIntermediaryCity($intermediaryCity) {
        $this->intermediaryCity = $intermediaryCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryStateProvince() {
        return $this->intermediaryStateProvince;
    }

    /**
     * @param string $intermediaryStateProvince
     * @return BankAccount
     */
    public function setIntermediaryStateProvince($intermediaryStateProvince) {
        $this->intermediaryStateProvince = $intermediaryStateProvince;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryCountry() {
        return $this->intermediaryCountry;
    }

    /**
     * @param string $intermediaryCountry
     * @return BankAccount
     */
    public function setIntermediaryCountry($intermediaryCountry) {
        $this->intermediaryCountry = $intermediaryCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntermediaryPostalCode() {
        return $this->intermediaryPostalCode;
    }

    /**
     * @param string $intermediaryPostalCode
     * @return BankAccount
     */
    public function setIntermediaryPostalCode($intermediaryPostalCode) {
        $this->intermediaryPostalCode = $intermediaryPostalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfileType() {
        return $this->profileType;
    }

    /**
     * @param string $profileType
     * @return BankAccount
     */
    public function setProfileType($profileType) {
        $this->profileType = $profileType;
        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessName() {
        return $this->businessName;
    }

    /**
     * @param string $businessName
     * @return BankAccount
     */
    public function setBusinessName($businessName) {
        $this->businessName = $businessName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessRegistrationId() {
        return $this->businessRegistrationId;
    }

    /**
     * @param string $businessRegistrationId
     * @return BankAccount
     */
    public function setBusinessRegistrationId($businessRegistrationId) {
        $this->businessRegistrationId = $businessRegistrationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessRegistrationCountry() {
        return $this->businessRegistrationCountry;
    }

    /**
     * @param string $businessRegistrationCountry
     * @return BankAccount
     */
    public function setBusinessRegistrationCountry($businessRegistrationCountry) {
        $this->businessRegistrationCountry = $businessRegistrationCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return BankAccount
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getMiddleName() {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     * @return BankAccount
     */
    public function setMiddleName($middleName) {
        $this->middleName = $middleName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return BankAccount
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfBirth() {
        return $this->dateOfBirth ? new \DateTime($this->dateOfBirth) : null;
    }

    /**
     * @param \DateTime $dateOfBirth
     * @return BankAccount
     */
    public function setDateOfBirth(\DateTime $dateOfBirth = null) {
        $this->dateOfBirth = $dateOfBirth == null ? null : $dateOfBirth->format('Y-m-d');
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfBirth() {
        return $this->countryOfBirth;
    }

    /**
     * @param string $countryOfBirth
     * @return BankAccount
     */
    public function setCountryOfBirth($countryOfBirth) {
        $this->countryOfBirth = $countryOfBirth;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfNationality() {
        return $this->countryOfNationality;
    }

    /**
     * @param string $countryOfNationality
     * @return BankAccount
     */
    public function setCountryOfNationality($countryOfNationality) {
        $this->countryOfNationality = $countryOfNationality;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return BankAccount
     */
    public function setPhoneNumber($phoneNumber) {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobileNumber() {
        return $this->mobileNumber;
    }

    /**
     * @param string $mobileNumber
     * @return BankAccount
     */
    public function setMobileNumber($mobileNumber) {
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getGovernmentId() {
        return $this->governmentId;
    }

    /**
     * @param string $governmentId
     * @return BankAccount
     */
    public function setGovernmentId($governmentId) {
        $this->governmentId = $governmentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine1() {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine1
     * @return BankAccount
     */
    public function setAddressLine1($addressLine1) {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @param string $city
     * @return BankAccount
     */
    public function setCity($city) {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getStateProvince() {
        return $this->stateProvince;
    }

    /**
     * @param string $stateProvince
     * @return BankAccount
     */
    public function setStateProvince($stateProvince) {
        $this->stateProvince = $stateProvince;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * @param string $country
     * @return BankAccount
     */
    public function setCountry($country) {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode() {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     * @return BankAccount
     */
    public function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
        return $this;
    }

}
