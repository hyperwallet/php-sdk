<?php
namespace Hyperwallet\Model;

/**
 * Represents a V3 User
 *
 * @property string $token
 * @property string $status
 *
 * @property \DateTime $createdOn
 *
 * @property string $clientUserId
 * @property string $profileType
 *
 * @property string $businessType
 * @property string $businessName
 * @property string $businessRegistrationId
 * @property string $businessRegistrationStateProvince
 * @property string $businessRegistrationCountry
 * @property string $businessContactRole
 *
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property \DateTime $dateOfBirth
 * @property string $countryOfBirth
 * @property string $countryOfNationality
 * @property string $gender
 * @property string $phoneNumber
 * @property string $mobileNumber
 * @property string $email
 *
 * @property string $governmentId
 * @property string $passportId
 * @property string $driversLicenseId
 * @property string $employerId
 *
 * @property string $addressLine1
 * @property string $addressLine2
 * @property string $city
 * @property string $stateProvince
 * @property string $country
 * @property string $postalCode
 *
 * @property string $language
 * @property string $programToken
 *
 * @package Hyperwallet\Model
 */
class User extends BaseModel implements IProgramAware {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn');

    const STATUS_PRE_ACTIVATED = 'PRE_ACTIVATED';
    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_LOCKED = 'LOCKED';
    const STATUS_FROZEN = 'FROZEN';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';

    const PROFILE_TYPE_INDIVIDUAL = 'INDIVIDUAL';
    const PROFILE_TYPE_BUSINESS = 'BUSINESS';

    const BUSINESS_TYPE_CORPORATION = 'CORPORATION';
    const BUSINESS_TYPE_PARTNERSHIP = 'PARTNERSHIP';

    const BUSINESS_CONTACT_ROLE_DIRECTOR = 'DIRECTOR';
    const BUSINESS_CONTACT_ROLE_OWNER = 'OWNER';
    const BUSINESS_CONTACT_ROLE_OTHER = 'OTHER';

    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

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
     * @return User
     */
    public function setToken($token) {
        $this->token = $token;
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
    public function getClientUserId() {
        return $this->clientUserId;
    }

    /**
     * @param string $clientUserId
     * @return User
     */
    public function setClientUserId($clientUserId) {
        $this->clientUserId = $clientUserId;
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
     * @return User
     */
    public function setProfileType($profileType) {
        $this->profileType = $profileType;
        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessType() {
        return $this->businessType;
    }

    /**
     * @param string $businessType
     * @return User
     */
    public function setBusinessType($businessType) {
        $this->businessType = $businessType;
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
     * @return User
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
     * @return User
     */
    public function setBusinessRegistrationId($businessRegistrationId) {
        $this->businessRegistrationId = $businessRegistrationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessRegistrationStateProvince() {
        return $this->businessRegistrationStateProvince;
    }

    /**
     * @param string $businessRegistrationStateProvince
     * @return User
     */
    public function setBusinessRegistrationStateProvince($businessRegistrationStateProvince) {
        $this->businessRegistrationStateProvince = $businessRegistrationStateProvince;
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
     * @return User
     */
    public function setBusinessRegistrationCountry($businessRegistrationCountry) {
        $this->businessRegistrationCountry = $businessRegistrationCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getBusinessContactRole() {
        return $this->businessContactRole;
    }

    /**
     * @param string $businessContactRole
     * @return User
     */
    public function setBusinessContactRole($businessContactRole) {
        $this->businessContactRole = $businessContactRole;
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setCountryOfNationality($countryOfNationality) {
        $this->countryOfNationality = $countryOfNationality;
        return $this;
    }

    /**
     * @return string
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @param string $gender
     * @return User
     */
    public function setGender($gender) {
        $this->gender = $gender;
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
     * @return User
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
     * @return User
     */
    public function setMobileNumber($mobileNumber) {
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;
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
     * @return User
     */
    public function setGovernmentId($governmentId) {
        $this->governmentId = $governmentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassportId() {
        return $this->passportId;
    }

    /**
     * @param string $passportId
     * @return User
     */
    public function setPassportId($passportId) {
        $this->passportId = $passportId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDriversLicenseId() {
        return $this->driversLicenseId;
    }

    /**
     * @param string $driversLicenseId
     * @return User
     */
    public function setDriversLicenseId($driversLicenseId) {
        $this->driversLicenseId = $driversLicenseId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmployerId() {
        return $this->employerId;
    }

    /**
     * @param string $employerId
     * @return User
     */
    public function setEmployerId($employerId) {
        $this->employerId = $employerId;
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
     * @return User
     */
    public function setAddressLine1($addressLine1) {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2() {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine2
     * @return User
     */
    public function setAddressLine2($addressLine2) {
        $this->addressLine2 = $addressLine2;
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param string $language
     * @return User
     */
    public function setLanguage($language) {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getProgramToken() {
        return $this->programToken;
    }

    /**
     * @param string $programToken
     * @return User
     */
    public function setProgramToken($programToken) {
        $this->programToken = $programToken;
        return $this;
    }

}
