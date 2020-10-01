<?php
namespace Hyperwallet\Model;

/**
 * Represents a v4 Receipt
 *
 * @property string $journalId The journal id
 * @property string $type The transaction type
 * @property \DateTime $createdOn The transaction creation date
 * @property string $entry The entry type
 * @property string $sourceToken The source token
 * @property string $destinationToken The destination token
 * @property string $amount The transaction amount
 * @property string $fee The fee amount
 * @property string $currency The transaction currency
 * @property double $foreignExchangeRate The foreign exchange rate
 * @property string $foreignExchangeCurrency The foreign exchange currency
 * @property array $details The transaction details
 *
 * @package Hyperwallet\Model
 */
class Receipt extends BaseModel {

    /**
     * @internal
     *
     * Read only fields
     *
     * @var string[]
     */
    private static $READ_ONLY_FIELDS = array('journalId', 'type', 'createdOn', 'entry', 'sourceToken', 'destinationToken', 'amount', 'fee', 'currency', 'foreignExchangeRate', 'foreignExchangeCurrency', 'details');

    const ENTRY_DEBIT = 'DEBIT';
    const ENTRY_CREDIT = 'CREDIT';

    // Generic Fees
    const TYPE_ANNUAL_FEE = 'ANNUAL_FEE';
    const TYPE_ANNUAL_FEE_REFUND = 'ANNUAL_FEE_REFUND';
    const TYPE_CUSTOMER_SERVICE_FEE = 'CUSTOMER_SERVICE_FEE';
    const TYPE_CUSTOMER_SERVICE_FEE_REFUND = 'CUSTOMER_SERVICE_FEE_REFUND';
    const TYPE_EXPEDITED_SHIPPING_FEE = 'EXPEDITED_SHIPPING_FEE';
    const TYPE_GENERIC_FEE_REFUND = 'GENERIC_FEE_REFUND';
    const TYPE_MONTHLY_FEE = 'MONTHLY_FEE';
    const TYPE_MONTHLY_FEE_REFUND = 'MONTHLY_FEE_REFUND';
    const TYPE_PAYMENT_EXPIRY_FEE = 'PAYMENT_EXPIRY_FEE';
    const TYPE_PAYMENT_FEE = 'PAYMENT_FEE';
    const TYPE_PROCESSING_FEE = 'PROCESSING_FEE';
    const TYPE_STANDARD_SHIPPING_FEE = 'STANDARD_SHIPPING_FEE';
    const TYPE_TRANSFER_FEE = 'TRANSFER_FEE';

    // Generic Payment Types
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    const TYPE_FOREIGN_EXCHANGE = 'FOREIGN_EXCHANGE';
    const TYPE_DEPOSIT = 'DEPOSIT';
    const TYPE_MANUAL_ADJUSTMENT = 'MANUAL_ADJUSTMENT';
    const TYPE_PAYMENT_EXPIRATION = 'PAYMENT_EXPIRATION';

    // Related to Bank Accounts
    const TYPE_BANK_ACCOUNT_TRANSFER_FEE = 'BANK_ACCOUNT_TRANSFER_FEE';
    const TYPE_BANK_ACCOUNT_TRANSFER_RETURN = 'BANK_ACCOUNT_TRANSFER_RETURN';
    const TYPE_BANK_ACCOUNT_TRANSFER_RETURN_FEE = 'BANK_ACCOUNT_TRANSFER_RETURN_FEE';
    const TYPE_TRANSFER_TO_BANK_ACCOUNT = 'TRANSFER_TO_BANK_ACCOUNT';

    // Related to Cards
    const TYPE_CARD_ACTIVATION_FEE = 'CARD_ACTIVATION_FEE';
    const TYPE_CARD_ACTIVATION_FEE_WAIVER = 'CARD_ACTIVATION_FEE_WAIVER';
    const TYPE_CARD_FEE = 'CARD_FEE';
    const TYPE_MANUAL_TRANSFER_TO_PREPAID_CARD = 'MANUAL_TRANSFER_TO_PREPAID_CARD';
    const TYPE_PREPAID_CARD_ACCOUNT_DEPOSIT = 'PREPAID_CARD_ACCOUNT_DEPOSIT';
    const TYPE_PREPAID_CARD_ACCOUNT_FEE = 'PREPAID_CARD_ACCOUNT_FEE';
    const TYPE_PREPAID_CARD_ANNUAL_FEE_DISCOUNT = 'PREPAID_CARD_ANNUAL_FEE_DISCOUNT';
    const TYPE_PREPAID_CARD_BALANCE_INQUIRY_FEE = 'PREPAID_CARD_BALANCE_INQUIRY_FEE';
    const TYPE_PREPAID_CARD_BILL_REPRINT_FEE = 'PREPAID_CARD_BILL_REPRINT_FEE';
    const TYPE_PREPAID_CARD_CASH_ADVANCE = 'PREPAID_CARD_CASH_ADVANCE';
    const TYPE_PREPAID_CARD_ATM_OR_CASH_ADVANCE_FEE = 'PREPAID_CARD_ATM_OR_CASH_ADVANCE_FEE';
    const TYPE_PREPAID_CARD_CASH_ADVANCE_CHARGEBACK = 'PREPAID_CARD_CASH_ADVANCE_CHARGEBACK';
    const TYPE_PREPAID_CARD_CASH_ADVANCE_CHARGEBACK_REVERSAL = 'PREPAID_CARD_CASH_ADVANCE_CHARGEBACK_REVERSAL';
    const TYPE_PREPAID_CARD_CASH_ADVANCE_REPRESS = 'PREPAID_CARD_CASH_ADVANCE_REPRESS';
    const TYPE_PREPAID_CARD_CASH_ADVANCE_REPRESS_REVERSAL = 'PREPAID_CARD_CASH_ADVANCE_REPRESS_REVERSAL';
    const TYPE_PREPAID_CARD_CHARGEBACK = 'PREPAID_CARD_CHARGEBACK';
    const TYPE_PREPAID_CARD_CHARGEBACK_REFUND = 'PREPAID_CARD_CHARGEBACK_REFUND';
    const TYPE_PREPAID_CARD_CHARGEBACK_REFUND_REVERSAL = 'PREPAID_CARD_CHARGEBACK_REFUND_REVERSAL';
    const TYPE_PREPAID_CARD_CHARGEBACK_REVERSAL = 'PREPAID_CARD_CHARGEBACK_REVERSAL';
    const TYPE_PREPAID_CARD_COMMISSION_OR_FEE = 'PREPAID_CARD_COMMISSION_OR_FEE';
    const TYPE_PREPAID_CARD_DEBIT_TRANSFER = 'PREPAID_CARD_DEBIT_TRANSFER';
    const TYPE_PREPAID_CARD_DISPUTED_CHARGE_REFUND = 'PREPAID_CARD_DISPUTED_CHARGE_REFUND';
    const TYPE_PREPAID_CARD_DISPUTE_DEPOSIT = 'PREPAID_CARD_DISPUTE_DEPOSIT';
    const TYPE_PREPAID_CARD_DOCUMENT_REQUEST_FEE = 'PREPAID_CARD_DOCUMENT_REQUEST_FEE';
    const TYPE_PREPAID_CARD_DOMESTIC_CASH_WITHDRAWAL_FEE = 'PREPAID_CARD_DOMESTIC_CASH_WITHDRAWAL_FEE';
    const TYPE_PREPAID_CARD_EMERGENCY_CASH = 'PREPAID_CARD_EMERGENCY_CASH';
    const TYPE_PREPAID_CARD_EMERGENCY_CARD = 'PREPAID_CARD_EMERGENCY_CARD';
    const TYPE_PREPAID_CARD_EXCHANGE_RATE_DIFFERENCE = 'PREPAID_CARD_EXCHANGE_RATE_DIFFERENCE';
    const TYPE_PREPAID_CARD_INCOME = 'PREPAID_CARD_INCOME';
    const TYPE_PREPAID_CARD_LOAD_FEE = 'PREPAID_CARD_LOAD_FEE';
    const TYPE_PREPAID_CARD_MANUAL_UNLOAD = 'PREPAID_CARD_MANUAL_UNLOAD';
    const TYPE_PREPAID_CARD_OVERDUE_PAYMENT_INTEREST = 'PREPAID_CARD_OVERDUE_PAYMENT_INTEREST';
    const TYPE_PREPAID_CARD_OVERSEAS_CASH_WITHDRAWAL_FEE = 'PREPAID_CARD_OVERSEAS_CASH_WITHDRAWAL_FEE';
    const TYPE_PREPAID_CARD_PAYMENT = 'PREPAID_CARD_PAYMENT';
    const TYPE_PREPAID_CARD_PIN_CHANGE_FEE = 'PREPAID_CARD_PIN_CHANGE_FEE';
    const TYPE_PREPAID_CARD_PIN_REPRINT_FEE = 'PREPAID_CARD_PIN_REPRINT_FEE';
    const TYPE_PREPAID_CARD_PRIORITY_PASS_FEE = 'PREPAID_CARD_PRIORITY_PASS_FEE';
    const TYPE_PREPAID_CARD_PRIORITY_PASS_RENEWAL = 'PREPAID_CARD_PRIORITY_PASS_RENEWAL';
    const TYPE_PREPAID_CARD_RECURRING_INTEREST = 'PREPAID_CARD_RECURRING_INTEREST';
    const TYPE_PREPAID_CARD_REFUND = 'PREPAID_CARD_REFUND';
    const TYPE_PREPAID_CARD_REFUND_REPRESS = 'PREPAID_CARD_REFUND_REPRESS';
    const TYPE_PREPAID_CARD_REFUND_REPRESS_REVERSAL = 'PREPAID_CARD_REFUND_REPRESS_REVERSAL';
    const TYPE_PREPAID_CARD_REPLACEMENT_FEE = 'PREPAID_CARD_REPLACEMENT_FEE';
    const TYPE_PREPAID_CARD_SALE = 'PREPAID_CARD_SALE';
    const TYPE_PREPAID_CARD_SALE_REPRESS = 'PREPAID_CARD_SALE_REPRESS';
    const TYPE_PREPAID_CARD_SALE_REVERSAL = 'PREPAID_CARD_SALE_REVERSAL';
    const TYPE_PREPAID_CARD_STATEMENT_FEE = 'PREPAID_CARD_STATEMENT_FEE';
    const TYPE_PREPAID_CARD_TELEPHONE_SUPPORT_FEE = 'PREPAID_CARD_TELEPHONE_SUPPORT_FEE';
    const TYPE_PREPAID_CARD_TRANSFER_FEE = 'PREPAID_CARD_TRANSFER_FEE';
    const TYPE_PREPAID_CARD_TRANSFER_RETURN = 'PREPAID_CARD_TRANSFER_RETURN';
    const TYPE_PREPAID_CARD_UNLOAD = 'PREPAID_CARD_UNLOAD';
    const TYPE_PREPAID_CARD_BANK_WITHDRAWAL_REVERSAL = 'PREPAID_CARD_BANK_WITHDRAWAL_REVERSAL';
    const TYPE_PREPAID_CARD_BANK_WITHDRAWAL_CHARGEBACK = 'PREPAID_CARD_BANK_WITHDRAWAL_CHARGEBACK';
    const TYPE_TRANSFER_TO_PREPAID_CARD = 'TRANSFER_TO_PREPAID_CARD';

    // Related to Donations
    const TYPE_DONATION = 'DONATION';
    const TYPE_DONATION_FEE = 'DONATION_FEE';
    const TYPE_DONATION_RETURN = 'DONATION_RETURN';

    // Related to Merchant Payments
    const TYPE_MERCHANT_PAYMENT = 'MERCHANT_PAYMENT';
    const TYPE_MERCHANT_PAYMENT_FEE = 'MERCHANT_PAYMENT_FEE';
    const TYPE_MERCHANT_PAYMENT_REFUND = 'MERCHANT_PAYMENT_REFUND';
    const TYPE_MERCHANT_PAYMENT_RETURN = 'MERCHANT_PAYMENT_RETURN';

    // Related to MoneyGram
    const TYPE_MONEYGRAM_TRANSFER_RETURN = 'MONEYGRAM_TRANSFER_RETURN';
    const TYPE_TRANSFER_TO_MONEYGRAM = 'TRANSFER_TO_MONEYGRAM';

    // Related to Paper Checks
    const TYPE_PAPER_CHECK_FEE = 'PAPER_CHECK_FEE';
    const TYPE_PAPER_CHECK_REFUND = 'PAPER_CHECK_REFUND';
    const TYPE_TRANSFER_TO_PAPER_CHECK = 'TRANSFER_TO_PAPER_CHECK';

    // Related to PayNearMe
    const TYPE_PAYNEARME_CASH_DEPOSIT = 'PAYNEARME_CASH_DEPOSIT';

    // Related to Users or Program Accounts
    const TYPE_ACCOUNT_CLOSURE = 'ACCOUNT_CLOSURE';
    const TYPE_ACCOUNT_CLOSURE_FEE = 'ACCOUNT_CLOSURE_FEE';
    const TYPE_ACCOUNT_UNLOAD = 'ACCOUNT_UNLOAD';
    const TYPE_DORMANT_USER_FEE = 'DORMANT_USER_FEE';
    const TYPE_DORMANT_USER_FEE_REFUND = 'DORMANT_USER_FEE_REFUND';
    const TYPE_PAYMENT = 'PAYMENT';
    const TYPE_PAYMENT_CANCELLATION = 'PAYMENT_CANCELLATION';
    const TYPE_PAYMENT_REVERSAL = 'PAYMENT_REVERSAL';
    const TYPE_PAYMENT_REVERSAL_FEE = 'PAYMENT_REVERSAL_FEE';
    const TYPE_PAYMENT_RETURN = 'PAYMENT_RETURN';
    const TYPE_TRANSFER_TO_PROGRAM_ACCOUNT = 'TRANSFER_TO_PROGRAM_ACCOUNT';
    const TYPE_TRANSFER_TO_USER = 'TRANSFER_TO_USER';

    // Related to Virtual Incentives
    const TYPE_VIRTUAL_INCENTIVE_CANCELLATION = 'VIRTUAL_INCENTIVE_CANCELLATION';
    const TYPE_VIRTUAL_INCENTIVE_ISSUANCE = 'VIRTUAL_INCENTIVE_ISSUANCE';
    const TYPE_VIRTUAL_INCENTIVE_PURCHASE = 'VIRTUAL_INCENTIVE_PURCHASE';
    const TYPE_VIRTUAL_INCENTIVE_REFUND = 'VIRTUAL_INCENTIVE_REFUND';

    // Related to Western Union and WUBS
    const TYPE_TRANSFER_TO_WESTERN_UNION = 'TRANSFER_TO_WESTERN_UNION';
    const TYPE_TRANSFER_TO_WUBS_WIRE = 'TRANSFER_TO_WUBS_WIRE';
    const TYPE_WESTERN_UNION_TRANSFER_RETURN = 'WESTERN_UNION_TRANSFER_RETURN';
    const TYPE_WUBS_WIRE_TRANSFER_RETURN = 'WUBS_WIRE_TRANSFER_RETURN';

    // Related to Wire Transfers
    const TYPE_TRANSFER_TO_WIRE = 'TRANSFER_TO_WIRE';
    const TYPE_WIRE_TRANSFER_FEE = 'WIRE_TRANSFER_FEE';
    const TYPE_WIRE_TRANSFER_RETURN = 'WIRE_TRANSFER_RETURN';

    const DETAILS_FIELD_KEY_CLIENT_PAYMENT_ID = 'clientPaymentId';
    const DETAILS_FIELD_KEY_NOTES = 'notes';
    const DETAILS_FIELD_KEY_MEMO = 'memo';
    const DETAILS_FIELD_KEY_RETURN_OR_RECALL_REASON = 'returnOrRecallReason';
    const DETAILS_FIELD_KEY_WEBSITE = 'website';
    const DETAILS_FIELD_KEY_PAYER_NAME = 'payerName';
    const DETAILS_FIELD_KEY_PAYEE_NAME = 'payeeName';
    const DETAILS_FIELD_KEY_CHARITY_NAME = 'charityName';
    const DETAILS_FIELD_KEY_CARD_HOLDER_NAME = 'cardHolderName';
    const DETAILS_FIELD_KEY_BANK_NAME = 'bankName';
    const DETAILS_FIELD_KEY_BANK_ID = 'bankId';
    const DETAILS_FIELD_KEY_BRANCH_NAME = 'branchName';
    const DETAILS_FIELD_KEY_BRANCH_ID = 'branchId';
    const DETAILS_FIELD_KEY_BANK_ACCOUNT_ID = 'bankAccountId';
    const DETAILS_FIELD_KEY_BANK_ACCOUNT_PURPOSE = 'bankAccountPurpose';
    const DETAILS_FIELD_KEY_BRANCH_ADDRESS_LINE1 = 'branchAddressLine1';
    const DETAILS_FIELD_KEY_BRANCH_ADDRESS_LINE2 = 'branchAddressLine2';
    const DETAILS_FIELD_KEY_BRANCH_CITY = 'branchCity';
    const DETAILS_FIELD_KEY_BRANCH_STATE_PROVINCE = 'branchStateProvince';
    const DETAILS_FIELD_KEY_BRANCH_COUNTRY = 'branchCountry';
    const DETAILS_FIELD_KEY_BRANCH_POSTAL_CODE = 'branchPostalCode';
    const DETAILS_FIELD_KEY_CHECK_NUMBER = 'checkNumber';
    const DETAILS_FIELD_KEY_CARD_NUMBER = 'cardNumber';
    const DETAILS_FIELD_KEY_CARD_EXPIRY_DATE = 'cardExpiryDate';
    const DETAILS_FIELD_KEY_PAYEE_EMAIL = 'payeeEmail';
    const DETAILS_FIELD_KEY_PAYEE_ADDRESS_LINE1 = 'payeeAddressLine1';
    const DETAILS_FIELD_KEY_PAYEE_ADDRESS_LINE2 = 'payeeAddressLine2';
    const DETAILS_FIELD_KEY_PAYEE_CITY = 'payeeCity';
    const DETAILS_FIELD_KEY_PAYEE_STATE_PROVINCE = 'payeeStateProvince';
    const DETAILS_FIELD_KEY_PAYEE_COUNTRY = 'payeeCountry';
    const DETAILS_FIELD_KEY_PAYEE_POSTAL_CODE = 'payeePostalCode';
    const DETAILS_FIELD_KEY_PAYMENT_EXPIRY_DATE = 'paymentExpiryDate';
    const DETAILS_FIELD_KEY_SECURITY_QUESTION = 'securityQuestion';
    const DETAILS_FIELD_KEY_SECURITY_ANSWER = 'securityAnswer';

    /**
     * Creates a instance of Balance
     *
     * @param string[] $properties The default properties
     */
    public function __construct(array $properties = array()) {
        parent::__construct(self::$READ_ONLY_FIELDS, $properties);
    }

    /**
     * Get the journal id
     *
     * @return string
     */
    public function getJournalId() {
        return $this->journalId;
    }

    /**
     * Get the transcation type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get the transaction creation date
     *
     * @return \DateTime
     */
    public function getCreatedOn() {
        return $this->createdOn ? new \DateTime($this->createdOn) : null;
    }

    /**
     * Get the entry type
     *
     * @return string
     */
    public function getEntry() {
        return $this->entry;
    }

    /**
     * Get the source token
     *
     * @return string
     */
    public function getSourceToken() {
        return $this->sourceToken;
    }

    /**
     * Get the destination token
     *
     * @return string
     */
    public function getDestinationToken() {
        return $this->destinationToken;
    }

    /**
     * Get the transaction amount
     *
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Get the transaction fee amount
     *
     * @return string
     */
    public function getFee() {
        return $this->fee;
    }

    /**
     * Get the transaction currency
     *
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * Get the foreign exchange rate
     *
     * @return float
     */
    public function getForeignExchangeRate() {
        return $this->foreignExchangeRate;
    }

    /**
     * Get the foreign exchange currency
     *
     * @return string
     */
    public function getForeignExchangeCurrency() {
        return $this->foreignExchangeCurrency;
    }

    /**
     * Get the transaction details (note this is a multi dimensional array see `DETAILS_FIELD_KEY_*` for possible keys)
     *
     * @return array
     */
    public function getDetails() {
        return $this->details;
    }

}
