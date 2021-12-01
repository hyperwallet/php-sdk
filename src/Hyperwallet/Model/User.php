<?php
namespace Hyperwallet\Model;

/**
 * Represents a V4 User
 *
 * @property string $token The user token
 * @property string $status The user status
 * @property string $verificationStatus The status of user verification
 * @property string $taxVerificationStatus The status of tax verification
 * @property string $businessStakeholderVerificationStatus The status of Business Stakeholder verification
 * @property string $letterOfAuthorizationStatus The status of Letter of Authorization verification
 *
 * @property \DateTime $createdOn The user creation date
 * @property string $clientUserId The client user id
 * @property string $profileType The profile type
 *
 * @property string $businessType The business type
 * @property string $businessName The business name
 * @property string $businessOperatingName The business operating name
 * @property string $businessRegistrationId The business registration id
 * @property string $businessRegistrationCountry The business registration country
 * @property string $businessRegistrationStateProvince The business registration state or province
 * @property string $businessContactRole The business contact role
 *
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
 * @property string $governmentIdType The status of Letter of Authorization verification
 * @property string $passportId The passport id
 * @property string $driversLicenseId The drivers license id
 * @property string $employerId The employer id
 *
 * @property string $addressLine1 The address line 1
 * @property string $addressLine2 The address line 2
 * @property string $city The city
 * @property string $stateProvince The state or province
 * @property string $postalCode The postal code
 * @property string $country The country
 *
 * @property string $language The user language
 * @property string $programToken The users program token
 * @property string $timeZone The users program token
 * @property HyperwalletVerificationDocumentCollection $documents The array of documents of type HyperwalletVerificationDocument returned for document upload
 * @property array $links The array of HATEOS links
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

    private static $READ_ONLY_FIELDS = array('token', 'status', 'createdOn', 'documents');

    const STATUS_PRE_ACTIVATED = 'PRE_ACTIVATED';
    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_LOCKED = 'LOCKED';
    const STATUS_FROZEN = 'FROZEN';
    const STATUS_DE_ACTIVATED = 'DE_ACTIVATED';

    const PROFILE_TYPE_INDIVIDUAL = 'INDIVIDUAL';
    const PROFILE_TYPE_BUSINESS = 'BUSINESS';
    const PROFILE_TYPE_UNKNOWN = 'UNKNOWN';

    const BUSINESS_TYPE_CORPORATION = 'CORPORATION';
    const BUSINESS_TYPE_PARTNERSHIP = 'PARTNERSHIP';

    const BUSINESS_CONTACT_ROLE_DIRECTOR = 'DIRECTOR';
    const BUSINESS_CONTACT_ROLE_OWNER = 'OWNER';
    const BUSINESS_CONTACT_ROLE_OTHER = 'OTHER';

    const GENDER_MALE = 'MALE';
    const GENDER_FEMALE = 'FEMALE';

    const VERIFICATION_STATUS_NOT_REQUIRED = 'NOT_REQUIRED';
    const VERIFICATION_STATUS_REQUIRED = 'REQUIRED';
    const VERIFICATION_STATUS_FAILED = 'FAILED';
    const VERIFICATION_STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const VERIFICATION_STATUS_VERIFIED = 'VERIFIED';
    const VERIFICATION_STATUS_REQUESTED = 'REQUESTED';
    const VERIFICATION_STATUS_EXPIRED = 'EXPIRED';
    const VERIFICATION_STATUS_READY_FOR_REVIEW='READY_FOR_REVIEW';

    const TAX_VERIFICATION_STATUS_NOT_REQUIRED = 'NOT_REQUIRED';
    const TAX_VERIFICATION_STATUS_REQUIRED = 'REQUIRED';
    const TAX_VERIFICATION_STATUS_VERIFIED= 'VERIFIED';
    const TAX_VERIFICATION_STATUS_UNDER_REVIEW = 'UNDER_REVIEW';

    const BUSINESSS_STAKEHOLDER_VERIFICATION_STATUS_NOT_REQUIRED = 'NOT_REQUIRED';
    const BUSINESSS_STAKEHOLDER_VERIFICATION_STATUS_REQUIRED = 'REQUIRED';
    const BUSINESSS_STAKEHOLDER_VERIFICATION_STATUS_FAILED = 'FAILED';
    const BUSINESSS_STAKEHOLDER_VERIFICATION_STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const BUSINESSS_STAKEHOLDER_VERIFICATION_STATUS_VERIFIED = 'VERIFIED';
    const BUSINESSS_STAKEHOLDER_VERIFICATION_STATUS_READY_FOR_REVIEW = 'READY_FOR_REVIEW';

    const LETTER_OF_AUTHORIZATION_STATUS_NOT_REQUIRED = 'NOT_REQUIRED';
    const LETTER_OF_AUTHORIZATION_STATUS_REQUIRED = 'REQUIRED';
    const LETTER_OF_AUTHORIZATION_STATUS_FAILED = 'FAILED';
    const LETTER_OF_AUTHORIZATION_STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const LETTER_OF_AUTHORIZATION_STATUS_VERIFIED = 'VERIFIED';
    const LETTER_OF_AUTHORIZATION_STATUS_READY_FOR_REVIEW = 'READY_FOR_REVIEW';

    const GOVERNMENT_ID_TYPE_PASSPORT = 'PASSPORT';
    const GOVERNMENT_ID_TYPE_NATIONAL_ID_CARD = 'NATIONAL_ID_CARD';

    public static function FILTERS_ARRAY() {
        return array('clientUserId','email','programToken','status','verificationStatus', 'taxVerificationStatus', 'createdBefore', 'createdAfter', 'sortBy', 'limit');
    }

    /**
     * Creates a instance of User
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the user token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Set the user token
     *
     * @param string $token
     * @return User
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Get the user status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * set the User status
     *
     * @param string $status
     * @return User
     */
   /*
    public function setStatus($status) {
        $this->$status = $status;
        return $this;
    }

*/
    /**
     * Get the user creation time
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the client user id
     *
     * @return string
     */
    public function getClientUserId() {
        return $this->clientUserId;
    }

    /**
     * Set the client user id
     *
     * @param string $clientUserId
     * @return User
     */
    public function setClientUserId($clientUserId) {
        $this->clientUserId = $clientUserId;
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
     * @return User
     */
    public function setProfileType($profileType) {
        $this->profileType = $profileType;
        return $this;
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
     * Set the business type
     *
     * @param string $businessType
     * @return User
     */
    public function setBusinessType($businessType) {
        $this->businessType = $businessType;
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
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setBusinessRegistrationId($businessRegistrationId) {
        $this->businessRegistrationId = $businessRegistrationId;
        return $this;
    }

    /**
     * Get the business registration state or province
     *
     * @return string
     */
    public function getBusinessRegistrationStateProvince() {
        return $this->businessRegistrationStateProvince;
    }

    /**
     * Set the business registartion state or province
     *
     * @param string $businessRegistrationStateProvince
     * @return User
     */
    public function setBusinessRegistrationStateProvince($businessRegistrationStateProvince) {
        $this->businessRegistrationStateProvince = $businessRegistrationStateProvince;
        return $this;
    }

    /**
     * Get the business registartion country
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
     * @return User
     */
    public function setBusinessRegistrationCountry($businessRegistrationCountry) {
        $this->businessRegistrationCountry = $businessRegistrationCountry;
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
     * Set the business contact role
     *
     * @param string $businessContactRole
     * @return User
     */
    public function setBusinessContactRole($businessContactRole) {
        $this->businessContactRole = $businessContactRole;
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setGovernmentId($governmentId) {
        $this->governmentId = $governmentId;
        return $this;
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
     * Set the passport id
     *
     * @param string $passportId
     * @return User
     */
    public function setPassportId($passportId) {
        $this->passportId = $passportId;
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
     * @return User
     */
    public function setDriversLicenseId($driversLicenseId) {
        $this->driversLicenseId = $driversLicenseId;
        return $this;
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
     * Set the employer id
     *
     * @param string $employerId
     * @return User
     */
    public function setEmployerId($employerId) {
        $this->employerId = $employerId;
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * Get the user language
     *
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * Set the user language
     *
     * @param string $language
     * @return User
     */
    public function setLanguage($language) {
        $this->language = $language;
        return $this;
    }

    /**
     * Get the users program token
     *
     * @return string
     */
    public function getProgramToken() {
        return $this->programToken;
    }

    /**
     * Set the users program token
     *
     * @param string $programToken
     * @return User
     */
    public function setProgramToken($programToken) {
        $this->programToken = $programToken;
        return $this;
    }

    /**
     * Get the users verification status
     *
     * @return string
     */
    public function getVerificationStatus() {
        return $this->verificationStatus;
    }

    /**
     * Set the users verification status
     *
     * @param string $verificationStatus
     * @return User
     */
    public function setVerificationStatus($verificationStatus) {
        $this->verificationStatus = $verificationStatus;
        return $this;
    }

    /**
     * Get the tax verification status
     *
     * @return string
     */
    public function getTaxVerificationStatus() {
        return $this->taxVerificationStatus;
    }

    /**
     * Set the tax verification status
     *
     * @param string $taxVerificationStatus
     * @return User
     */
    public function setTaxVerificationStatus($taxVerificationStatus) {
        $this->taxVerificationStatus = $taxVerificationStatus;
        return $this;
    }

    public function getDocuments() {
        return $this->documents;
    }

    /**
     * get Business Stakeholder verification status
     *
     * @return string
     */
    public function getBusinessStakeholderVerificationStatus() {
        return $this->businessStakeholderVerificationStatus;
    }

    /**
     * set Business Stakeholder verification status
     *
     * @param string $businessStakeholderVerificationStatus
     * @return User
     */
    public function setBusinessStakeholderVerificationStatus($businessStakeholderVerificationStatus) {
        $this->businessStakeholderVerificationStatus = $businessStakeholderVerificationStatus;
        return $this;
    }

    /**
     * Get the users Letter of Authorization status
     *
     * @return string
     */
    public function getLetterOfAuthorizationStatus() {
        return $this->letterOfAuthorizationStatus;
    }

    /**
     * Set the users Letter of Authorization status
     *
     * @param string $letterOfAuthorizationStatus
     * @return User
     */
    public function setLetterOfAuthorizationStatus($letterOfAuthorizationStatus) {
        $this->letterOfAuthorizationStatus = $letterOfAuthorizationStatus;
        return $this;
    }

    /**
     * get the users government Id Type
     *
     * @return string
     */
    public function getGovernmentIdType() {
        return $this->governmentIdType;
    }

    /**
     * Set the users government Id Type
     *
     * @param string $governmentIdType
     * @return User
     */
    public function setGovernmentIdType($governmentIdType) {
        $this->governmentIdType = $governmentIdType;
        return $this;
    }

    /**
     * get the user's time zone
     *
     * @return string
     */
    public function getTimeZone() {
        return $this->timeZone;
    }

    /**
     * set the user's time zone
     *
     * @param string $timeZone
     * @return User
     */
    public function setTimeZone($timeZone) {
        $this->timeZone = $timeZone;
        return $this;
    }

    /**
     * get the HATEOS links
     *
     * @return array
     */
    public function getLinks() {
        return $this->links;
    }

    /**
     * set the HATEOS links
     *
     * @param array $links
     * @return User
     */
    public function setLinks($links) {
        $this->links = $links;
        return $this;
    }
}
