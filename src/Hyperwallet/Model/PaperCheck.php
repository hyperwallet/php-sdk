<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 Paper Check
 *
 * @property string $token The paper check token
 * @property string $status The paper check status
 * @property \DateTime $createdOn The paper check creation date
 * @property string $type The transfer method type
 * @property string $transferMethodCountry The transfer method country
 * @property string $transferMethodCurrency The transfer method currency
 * @property string $addressLine1 The address line #1
 * @property string $addressLine2 The address line #2
 * @property string $businessContactRole The business contact role
 * @property string $businessName The business name
 * @property string $businessOperatingName The business operating name
 * @property string $businessRegistrationCountry The business registration country
 * @property string $businessRegistrationId The business registration id
 * @property string $businessRegistrationStateProvince The business registration state province
 * @property string $businessType The business type
 * @property string $city The city
 * @property string $country The country
 * @property string $countryOfBirth The country of birth
 * @property string $countryOfNationality The country of nationality
 * @property \DateTime $dateOfBirth The date of birth
 * @property string $driversLicenseId The drivers license id
 * @property string $employerId The employer id
 * @property string $firstName The first name
 * @property string $gender The gender
 * @property string $governmentId The government id
 * @property string $governmentIdType The government id type
 * @property string $isDefaultTransferMethod The is default transfer method
 * @property string $lastName The last name
 * @property string $middleName The middle name
 * @property string $mobileNumber The mobile number
 * @property string $passportId The passport id
 * @property string $phoneNumber The phone number
 * @property string $postalCode The postal code
 * @property string $profileType The profile type
 * @property string $shippingMethod The shipping method
 * @property string $stateProvince The state province
 *
 * @package Hyperwallet\Model
 */

class PaperCheck extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('phoneNumber', 'passportId', 'mobileNumber', 'middleName', 'lastName', 'governmentIdType', 'governmentId', 'gender', 'firstName', 'employerId', 'driversLicenseId', 'dateOfBirth', 'countryOfNationality', 'countryOfBirth', 'businessType', 'businessRegistrationStateProvince', 'businessRegistrationId', 'businessRegistrationCountry', 'businessName', 'businessOperatingName', 'businessContactRole', 'createdOn', 'status', 'token');

    const TYPE_PAPER_CHECK = 'PAPER_CHECK';

    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_VERIFIED = 'VERIFIED';
    const STATUS_INVALID = 'INVALID';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';

    const BUSINESS_CONTACT_ROLE_DIRECTOR = 'DIRECTOR';
    const BUSINESS_CONTACT_ROLE_OWNER = 'OWNER';
    const BUSINESS_CONTACT_ROLE_OTHER = 'OTHER';

    const BUSINESS_TYPE_CORPORATION = 'CORPORATION';
    const BUSINESS_TYPE_PARTNERSHIP = 'PARTNERSHIP';

    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    const GOVERNMENT_ID_TYPE_PASSPORT = 'PASSPORT';
    const GOVERNMENT_ID_TYPE_NATIONAL_ID_CARD = 'NATIONAL_ID_CARD';

    const PROFILE_TYPE_INDIVIDUAL = 'INDIVIDUAL';
    const PROFILE_TYPE_BUSINESS = 'BUSINESS';

    const SHIPPING_METHOD_STANDARD = 'STANDARD';
    const SHIPPING_METHOD_EXPEDITED = 'EXPEDITED';

    /**
     * Creates a instance of PaperCheck
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the paper check token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the paper check token
     *
     * @param string $token
     * @return PaperCheck
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the paper check status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the paper check creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
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
     * @return PaperCheck
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
     * @return PaperCheck
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
     * @return PaperCheck
     */
    public function setTransferMethodCurrency($transferMethodCurrency) {
        $this->transferMethodCurrency = $transferMethodCurrency;
        return $this;
    }

    /**
     * Get the address line #1
     *
     * @return string
     */
    public function getAddressLine1() {
        return $this->addressLine1;
    }

    /**
     * Set the address line #1
     *
     * @param string $addressLine1
     * @return PaperCheck
     */
    public function setAddressLine1($addressLine1) {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * Get the address line #2
     *
     * @return string
     */
    public function getAddressLine2() {
        return $this->addressLine2;
    }

    /**
     * Set the address line #2
     *
     * @param string $addressLine2
     * @return PaperCheck
     */
    public function setAddressLine2($addressLine2) {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    /**
     * Get the business contact role
     *
     * @return string
     */
    public function getBusinessContactRole() {
        return $this->businessContactRole;
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
     * Get the business operating name
     *
     * @return string
     */
    public function getBusinessOperatingName()
    {
        return $this->businessOperatingName;
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
     * Get the business registration id
     *
     * @return string
     */
    public function getBusinessRegistrationId() {
        return $this->businessRegistrationId;
    }

    /**
     * Get the business registration state province
     *
     * @return string
     */
    public function getBusinessRegistrationStateProvince() {
        return $this->businessRegistrationStateProvince;
    }

    /**
     * Get the business type
     *
     * @return string
     */
    public function getBusinessType() {
        return $this->businessType;
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
     * @return PaperCheck
     */
    public function setCity($city) {
        $this->city = $city;
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
     * @return PaperCheck
     */
    public function setCountry($country) {
        $this->country = $country;
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
     * Get the country of nationality
     *
     * @return string
     */
    public function getCountryOfNationality() {
        return $this->countryOfNationality;
    }

    /**
     * Get the date of birth
     *
     * @return \DateTime
     */
    public function getDateOfBirth() {
        return $this->dateOfBirth ? new \DateTime($this->dateOfBirth) : null;
    }

    /**
     * Get the drivers license id
     *
     * @return string
     */
    public function getDriversLicenseId() {
        return $this->driversLicenseId;
    }

    /**
     * Get the employer id
     *
     * @return string
     */
    public function getEmployerId() {
        return $this->employerId;
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
     * Get the gender
     *
     * @return string
     */
    public function getGender() {
        return $this->gender;
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
     * Get the government id type
     *
     * @return string
     */
    public function getGovernmentIdType() {
        return $this->governmentIdType;
    }

    /**
     * Get the is default transfer method
     *
     * @return string
     */
    public function getIsDefaultTransferMethod() {
        return $this->isDefaultTransferMethod;
    }

    /**
     * Set the is default transfer method
     *
     * @param string $isDefaultTransferMethod
     * @return PaperCheck
     */
    public function setIsDefaultTransferMethod($isDefaultTransferMethod) {
        $this->isDefaultTransferMethod = $isDefaultTransferMethod;
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
     * Get the middle name
     *
     * @return string
     */
    public function getMiddleName() {
        return $this->middleName;
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
     * Get the passport id
     *
     * @return string
     */
    public function getPassportId() {
        return $this->passportId;
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
     * @return PaperCheck
     */
    public function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
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
     * @return PaperCheck
     */
    public function setProfileType($profileType) {
        $this->profileType = $profileType;
        return $this;
    }

    /**
     * Get the shipping method
     *
     * @return string
     */
    public function getShippingMethod() {
        return $this->shippingMethod;
    }

    /**
     * Set the shipping method
     *
     * @param string $shippingMethod
     * @return PaperCheck
     */
    public function setShippingMethod($shippingMethod) {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * Get the state province
     *
     * @return string
     */
    public function getStateProvince() {
        return $this->stateProvince;
    }

    /**
     * Set the state province
     *
     * @param string $stateProvince
     * @return PaperCheck
     */
    public function setStateProvince($stateProvince) {
        $this->stateProvince = $stateProvince;
        return $this;
    }

}
