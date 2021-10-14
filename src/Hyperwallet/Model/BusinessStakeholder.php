<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 BusinessStakeholder
 *
 * @property string $token The BusinessStakeholder token
 * @property boolen $isBusinessContact The business contact
 * @property boolen $isDirector The Director
 * @property boolen $isUltimateBeneficialOwner The UltimateBeneficial Owner
 * @property boolen $isSeniorManagingOfficial The Senior Managing Official
 * @property string $verificationStatus The status of Business user verification
 * @property string $status The Business user status
 * @property \DateTime $createdOn The Business user creation date
 * @property string $profileType The profile type
 * @property string $firstName The first name
 * @property string $middleName The middle name
 * @property string $lastName The last name
 * @property \DateTime $dateOfBirth The date of birth
 * @property string $countryOfBirth The country of birth
 * @property string $countryOfNationality The country of nationality
 * @property string $gender The gender
 * @property string $phoneNumber The phone number
 * @property string $mobileNumber The mobile number
 * @property string $email The email
 *
 * @property string $governmentId The goverment id
 * @property string $governmentIdType The goverment id Type
 * @property string $driversLicenseId The drivers license id
 *
 * @property string $addressLine1 The address line 1
 * @property string $addressLine2 The address line 2
 * @property string $city The city
 * @property string $stateProvince The state or province
 * @property string $country The country
 * @property string $postalCode The postal code
 * @property array $documents The array of documents returned for document upload
 *
 * @package Hyperwallet\Model
 */

class BusinessStakeholder extends BaseModel {
    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn', 'documents');

    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';

    const PROFILE_TYPE_INDIVIDUAL = 'INDIVIDUAL';

    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    const VERIFICATION_STATUS_REQUIRED = 'REQUIRED';
    const VERIFICATION_STATUS_NOT_REQUIRED = 'NOT_REQUIRED';
    const VERIFICATION_STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const VERIFICATION_STATUS_VERIFIED = 'VERIFIED';
    const VERIFICATION_STATUS_READY_FOR_REVIEW = 'READY_FOR_REVIEW';

    const GOVERNMENT_ID_TYPE_PASSPORT = 'PASSPORT';
    const GOVERNMENT_ID_TYPE_NATIONAL_ID_CARD = 'NATIONAL_ID_CARD';

    public static function FILTERS_ARRAY() {
        return array('status', 'createdBefore', 'createdAfter', 'sortBy', 'limit');
    }

    /**
     * Creates a instance of BusinessStakeholder
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the BusinessStakeholder token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the BusinessStakeholder token
     *
     * @param string $token
     * @return BusinessStakeholder
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the BusinessStakeholder Contact
     *
     * @return Boolean
     */
    public function getIsBusinessContact() {
        return $this->isBusinessContact;
    }
    /**
     * Set the BusinessStakeholder Contact
     *
     * @param Boolean $isBusinessContact

     * @return BusinessStakeholder
     */
    public function setIsBusinessContact($isBusinessContact) {
        $this->isBusinessContact = $isBusinessContact;
        return $this;
    }

    /**
     * Get the BusinessStakeholder Director
     *
     * @return Boolean
     */
    public function getIsDirector() {
        return $this->isDirector;
    }

    /**
     * Set the BusinessStakeholder Director
     *
     * @param Boolean $isDirector

     * @return BusinessStakeholder
     */
    public function setIsDirector($isDirector) {
        $this->isDirector = $isDirector;
        return $this;
    }

    /**
     * Get the BusinessStakeholder Ultimate Beneficial Owner
     *
     * @return Boolean
     */
    public function getIsUltimateBeneficialOwner() {
        return $this->isUltimateBeneficialOwner;
    }

    /**
     * Set the BusinessStakeholder Ultimate Beneficial Owner
     *
     * @param Boolean $isUltimateBeneficialOwner

     * @return BusinessStakeholder
     */
    public function setIsUltimateBeneficialOwner($isUltimateBeneficialOwner) {
        $this->isUltimateBeneficialOwner = $isUltimateBeneficialOwner;
        return $this;
    }

    /**
     * Get the BusinessStakeholder Senior Managing Official
     *
     * @return Boolean
     */
    public function getIsSeniorManagingOfficial() {
        return $this->isSeniorManagingOfficial;
    }

    /**
     * Set the BusinessStakeholder Senior Managing Official
     *
     * @param Boolean $isSeniorManagingOfficial

     * @return BusinessStakeholder
     */
    public function setIsSeniorManagingOfficial($isSeniorManagingOfficial) {
        $this->isSeniorManagingOfficial = $isSeniorManagingOfficial;
        return $this;
    }

    /**
     * Get the BusinessStakeholder verification status
     *
     * @return string
     */
    public function getVerificationStatus() {
        return $this->verificationStatus;
    }

    /**
     * Set the BusinessStakeholder verification status
     *
     * @param string $verificationStatus
     * @return BusinessStakeholder
     */
    public function setVerificationStatus($verificationStatus) {
        $this->verificationStatus = $verificationStatus;
        return $this;
    }

    /**
     * Get the BusinessStakeholder status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the BusinessStakeholder creation time
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
     */
    public function setCountryOfNationality($countryOfNationality) {
        $this->countryOfNationality = $countryOfNationality;
        return $this;
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
     * Set the gender
     *
     * @param string $gender
     * @return BusinessStakeholder
     */
    public function setGender($gender) {
        $this->gender = $gender;
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
     */
    public function setMobileNumber($mobileNumber) {
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    /**
     * Get the email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set the email
     *
     * @param string $email
     * @return BusinessStakeholder
     */
    public function setEmail($email) {
        $this->email = $email;
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
     * @return BusinessStakeholder
     */
    public function setGovernmentId($governmentId) {
        $this->governmentId = $governmentId;
        return $this;
    }

    /**
     * Get the business governmentIdType
     *
     * @return string
     */
    public function getGovernmentIdType() {
        return $this->governmentIdType;
    }

    /**
     * Set the business governmentIdType
     *
     * @param string $businessType
     * @return BusinessStakeholder
     */
    public function setGovernmentIdType($governmentIdType) {
        $this->governmentIdType = $governmentIdType;
        return $this;
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
     * Set the drivers license id
     *
     * @param string $driversLicenseId
     * @return BusinessStakeholder
     */
    public function setDriversLicenseId($driversLicenseId) {
        $this->driversLicenseId = $driversLicenseId;
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
     * @return BusinessStakeholder
     */
    public function setAddressLine1($addressLine1) {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * Get the address line 2
     *
     * @return string
     */
    public function getAddressLine2() {
        return $this->addressLine2;
    }

    /**
     * Set the address line 2
     *
     * @param string $addressLine2
     * @return BusinessStakeholder
     */
    public function setAddressLine2($addressLine2) {
        $this->addressLine2 = $addressLine2;
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
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
     * @return BusinessStakeholder
     */
    public function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getDocuments() {
        return $this->documents;
    }

}
