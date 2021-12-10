<?php
namespace Hyperwallet\Tests\Model;

use Hyperwallet\Model\BankAccount;
use Hyperwallet\Model\BankCard;
use Hyperwallet\Model\PaperCheck;
use Hyperwallet\Model\Payment;
use Hyperwallet\Model\PayPalAccount;
use Hyperwallet\Model\PrepaidCard;
use Hyperwallet\Model\Transfer;
use Hyperwallet\Model\TransferRefund;
use Hyperwallet\Model\User;
use Hyperwallet\Model\VenmoAccount;
use Hyperwallet\Model\WebhookNotification;

class WebhookNotificationTest extends ModelTestCase {

    protected function getModelName() {
        return 'WebhookNotification';
    }

    /**
     * @dataProvider ignoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersForIgnoredProperties($property) {
        $this->performGettersForIgnoredPropertiesTest($property);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($property) {
        $this->performGetterReturnValueIsSetTest($property);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsNotSet($property) {
        $this->performGetterReturnValueIsNotSetTest($property);
    }

    /**
     * @dataProvider notificationTypeProvider
     *
     * @param string $type The notification type
     * @param object $clazz The expected class type
     *
     */
    public function testConstructorObjectConversion($type, $clazz) {
        $data = array(
            'type' => $type,
            'test2' => 'value2',
            'object' => array(
                'test' => 'value'
            )
        );

        $notification = new WebhookNotification($data);
        if ($clazz === null) {
            $this->assertNull($notification->getObject());
        } else {
            $this->assertNotNull($notification->getObject());
            $this->assertInstanceOf($clazz, $notification->getObject());

            $this->assertEquals(array(
                'test' => 'value'
            ), $notification->getObject()->getProperties());
        }
    }

    public function notificationTypeProvider() {
        return array(
            'USERS.CREATED' => array('USERS.CREATED', User::class),
            'USERS.UPDATED.STATUS.ACTIVATED' => array('USERS.UPDATED.STATUS.ACTIVATED', User::class),
            'USERS.UPDATED.STATUS.LOCKED' => array('USERS.UPDATED.STATUS.LOCKED', User::class),
            'USERS.UPDATED.STATUS.FROZEN' => array('USERS.UPDATED.STATUS.FROZEN', User::class),
            'USERS.UPDATED.STATUS.DE_ACTIVATED' => array('USERS.UPDATED.STATUS.DE_ACTIVATED', User::class),

            'USERS.BANK_ACCOUNTS.CREATED' => array('USERS.BANK_ACCOUNTS.CREATED', BankAccount::class),
            'USERS.BANK_ACCOUNTS.UPDATED.STATUS.ACTIVATED' => array('USERS.BANK_ACCOUNTS.UPDATED.STATUS.ACTIVATED', BankAccount::class),
            'USERS.BANK_ACCOUNTS.UPDATED.STATUS.INVALID' => array('USERS.BANK_ACCOUNTS.UPDATED.STATUS.INVALID', BankAccount::class),
            'USERS.BANK_ACCOUNTS.UPDATED.STATUS.DE_ACTIVATED' => array('USERS.BANK_ACCOUNTS.UPDATED.STATUS.DE_ACTIVATED', BankAccount::class),

            'USERS.PREPAID_CARDS.CREATED' => array('USERS.PREPAID_CARDS.CREATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.QUEUED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.QUEUED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.PRE_ACTIVATED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.PRE_ACTIVATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.ACTIVATED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.ACTIVATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.DECLINED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.DECLINED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.LOCKED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.LOCKED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.SUSPENDED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.SUSPENDED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.LOST_OR_STOLEN' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.LOST_OR_STOLEN', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.DE_ACTIVATED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.DE_ACTIVATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.COMPLIANCE_HOLD' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.COMPLIANCE_HOLD', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.KYC_HOLD' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.KYC_HOLD', PrepaidCard::class),

            'USERS.PAYPAL_ACCOUNTS.CREATED' => array('USERS.PAYPAL_ACCOUNTS.CREATED', PayPalAccount::class),
            'USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.ACTIVATED' => array('USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.ACTIVATED', PayPalAccount::class),
            'USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.DE_ACTIVATED' => array('USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.DE_ACTIVATED', PayPalAccount::class),
            'USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.VERIFIED' => array('USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.VERIFIED', PayPalAccount::class),
            'USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.INVALID' => array('USERS.PAYPAL_ACCOUNTS.UPDATED.STATUS.INVALID', PayPalAccount::class),

            'USERS.VENMO_ACCOUNTS.CREATED' => array('USERS.VENMO_ACCOUNTS.CREATED', VenmoAccount::class),
            'USERS.VENMO_ACCOUNTS.UPDATED.STATUS.ACTIVATED' => array('USERS.VENMO_ACCOUNTS.UPDATED.STATUS.ACTIVATED', VenmoAccount::class),
            'USERS.VENMO_ACCOUNTS.UPDATED.STATUS.DE_ACTIVATED' => array('USERS.VENMO_ACCOUNTS.UPDATED.STATUS.DE_ACTIVATED', VenmoAccount::class),
            'USERS.VENMO_ACCOUNTS.UPDATED.STATUS.VERIFIED' => array('USERS.VENMO_ACCOUNTS.UPDATED.STATUS.VERIFIED', VenmoAccount::class),
            'USERS.VENMO_ACCOUNTS.UPDATED.STATUS.INVALID' => array('USERS.VENMO_ACCOUNTS.UPDATED.STATUS.INVALID', VenmoAccount::class),

            'USERS.BANK_CARDS.CREATED' => array('USERS.BANK_CARDS.CREATED', BankCard::class),
            'USERS.BANK_CARDS.UPDATED.STATUS.ACTIVATED' => array('USERS.BANK_CARDS.UPDATED.STATUS.ACTIVATED', BankCard::class),
            'USERS.BANK_CARDS.UPDATED.STATUS.DE_ACTIVATED' => array('USERS.BANK_CARDS.UPDATED.STATUS.DE_ACTIVATED', BankCard::class),
            'USERS.BANK_CARDS.UPDATED.STATUS.VERIFIED' => array('USERS.BANK_CARDS.UPDATED.STATUS.VERIFIED', BankCard::class),
            'USERS.BANK_CARDS.UPDATED.STATUS.INVALID' => array('USERS.BANK_CARDS.UPDATED.STATUS.INVALID', BankCard::class),

            'USERS.PAPER_CHECKS.CREATED' => array('USERS.PAPER_CHECKS.CREATED', PaperCheck::class),
            'USERS.PAPER_CHECKS.UPDATED' => array('USERS.PAPER_CHECKS.UPDATED', PaperCheck::class),
            'USERS.PAPER_CHECKS.UPDATED.STATUS.ACTIVATED' => array('USERS.PAPER_CHECKS.UPDATED.STATUS.ACTIVATED', PaperCheck::class),
            'USERS.PAPER_CHECKS.UPDATED.STATUS.DE_ACTIVATED' => array('USERS.PAPER_CHECKS.UPDATED.STATUS.DE_ACTIVATED', PaperCheck::class),
            'USERS.PAPER_CHECKS.UPDATED.STATUS.VERIFIED' => array('USERS.PAPER_CHECKS.UPDATED.STATUS.VERIFIED', PaperCheck::class),
            'USERS.PAPER_CHECKS.UPDATED.STATUS.INVALID' => array('USERS.PAPER_CHECKS.UPDATED.STATUS.INVALID', PaperCheck::class),

            'PAYMENTS.CREATED' => array('PAYMENTS.CREATED', Payment::class),
            'PAYMENTS.UPDATED.STATUS.SCHEDULED' => array('PAYMENTS.UPDATED.STATUS.SCHEDULED', Payment::class),
            'PAYMENTS.UPDATED.STATUS.PENDING_ACCOUNT_ACTIVATION' => array('PAYMENTS.UPDATED.STATUS.PENDING_ACCOUNT_ACTIVATION', Payment::class),
            'PAYMENTS.UPDATED.STATUS.PENDING_ID_VERIFICATION' => array('PAYMENTS.UPDATED.STATUS.PENDING_ID_VERIFICATION', Payment::class),
            'PAYMENTS.UPDATED.STATUS.PENDING_TAX_VERIFICATION' => array('PAYMENTS.UPDATED.STATUS.PENDING_TAX_VERIFICATION', Payment::class),


            'TRANSFERS.UPDATED.STATUS.SCHEDULED' => array('TRANSFERS.UPDATED.STATUS.SCHEDULED', Transfer::class),
            'TRANSFERS.UPDATED.STATUS.IN_PROGRESS' => array('TRANSFERS.UPDATED.STATUS.IN_PROGRESS', Transfer::class),
            'TRANSFERS.UPDATED.STATUS.COMPLETED' => array('TRANSFERS.UPDATED.STATUS.COMPLETED', Transfer::class),
            'TRANSFERS.UPDATED.STATUS.FAILED' => array('TRANSFERS.UPDATED.STATUS.FAILED', Transfer::class),

            'TRANSFERS.REFUND.CREATED' => array('TRANSFERS.REFUND.CREATED', TransferRefund::class),
            'TRANSFERS.REFUND.UPDATED' => array('TRANSFERS.REFUND.UPDATED', TransferRefund::class),

            'TEST' => array('TEST', null),
        );
    }

}
