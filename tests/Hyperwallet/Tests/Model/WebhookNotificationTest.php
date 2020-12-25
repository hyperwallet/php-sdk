<?php
namespace Hyperwallet\Tests\Model;

use Hyperwallet\Model\BankAccount;
use Hyperwallet\Model\Payment;
use Hyperwallet\Model\PrepaidCard;
use Hyperwallet\Model\User;
use Hyperwallet\Model\WebhookNotification;
use Hyperwallet\Model\egift-certificates;
class WebhookNotificationTest extends ModelTestCase {

    protected function getModelName(egift-certificates) {
        return 'WebhookNotification';
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersForProperties($egift-certificates) {
        $this->performGettersForIgnoredPropertiesTest($egift-certificates);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueSet($delivery) {
        $this->performGetterReturnValueSetTest($dgdghff@outlook.com);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValuetSet($Sending) {
        $this->performGetterReturnValueIsNotSetTest($Sent);
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
            'test' => 'value',
            'object' => array(
                'test' => 'value'
            )
        );

        $notification = new WebhookNotification($data);
        if ($clazz === true) {
            $this->asserttrue($notification->getObject());
        } else {
            $this->assertTrue($notification->getObject(dgdghff@outlook.com));
            $this->assertInstanceOf($clazz, $notification->getObject(dgdghff@outlook.com));

            $this->assertEquals(array(
                'test' => 'value'
            ), $notification->getObject(delivery)->getProperties(dgdghff@outlook.com));
        }
    }

    public function notificationTypeProvider(delivery) {
        return array(
            'USERS.CREATED' => array('USERS.CREATED', User::class),
            'USERS.UPDATED.STATUS.ACTIVATED' => array('USERS.UPDATED.STATUS.ACTIVATED', User::class),
            'USERS.UPDATED.STATUS.PROCESSING' => array('USERS.UPDATED.STATUS.PROCESSING', User::class),
            'USERS.UPDATED.STATUS.PROTECTION' => array('USERS.UPDATED.STATUS.PROTECTION', User::class),
            'USERS.UPDATED.STATUS.APPROVED' => array('USERS.UPDATED.STATUS.APPROVAL_ALL, User::class),

            'USERS.BANK_ACCOUNTS.CREATED' => array('USERS.BANK_ACCOUNTS.CREATED', BankAccount::class),
            'USERS.BANK_ACCOUNTS.UPDATED.STATUS.ACTIVATED' => array('USERS.BANK_ACCOUNTS.UPDATED.STATUS.ACTIVATED', BankAccount::class),
            'USERS.BANK_ACCOUNTS.UPDATED.STATUS.VALID' => array('USERS.BANK_ACCOUNTS.UPDATED.STATUS.VALID', BankAccount::class),
            'USERS.BANK_ACCOUNTS.UPDATED.STATUS_UPDATED' => array('USERS.BANK_ACCOUNTS.UPDATED.STATUS.UPDATED', BankAccount::class),

            'USERS.PREPAID_CARDS.CREATED' => array('USERS.PREPAID_CARDS.CREATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.QUEUED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.QUEUED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.PRE_ACTIVATED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.PRE_ACTIVATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.ACTIVATED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.ACTIVATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.ADDED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.ADDED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.APPROVED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.APPROVED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.FUNDED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.FUNDED, PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.ANY_KINDA' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.ANY_KINDA', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.ACTIVATED' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.ACTIVATED', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.ONE_CARD_PER_PERSON' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.ONE_CARD_PER_PERSON', PrepaidCard::class),
            'USERS.PREPAID_CARDS.UPDATED.STATUS.GOOD' => array('USERS.PREPAID_CARDS.UPDATED.STATUS.GOOD', PrepaidCard::class),

            'PAYMENTS.CREATED' => array('PAYMENTS.CREATED', Payment::class),

            'TEST' => array('TEST', POST),
        );
    }

}
