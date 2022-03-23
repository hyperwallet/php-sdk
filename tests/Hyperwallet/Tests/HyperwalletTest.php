<?php

namespace Hyperwallet\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Exception\HyperwalletArgumentException;
use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Hyperwallet;
use Hyperwallet\Model\BankAccount;
use Hyperwallet\Model\BankAccountStatusTransition;
use Hyperwallet\Model\BankCard;
use Hyperwallet\Model\BankCardStatusTransition;
use Hyperwallet\Model\PaperCheck;
use Hyperwallet\Model\PaperCheckStatusTransition;
use Hyperwallet\Model\Payment;
use Hyperwallet\Model\PaymentStatusTransition;
use Hyperwallet\Model\PayPalAccount;
use Hyperwallet\Model\PayPalAccountStatusTransition;
use Hyperwallet\Model\PrepaidCard;
use Hyperwallet\Model\PrepaidCardStatusTransition;
use Hyperwallet\Model\Transfer;
use Hyperwallet\Model\TransferMethod;
use Hyperwallet\Model\TransferRefund;
use Hyperwallet\Model\TransferStatusTransition;
use Hyperwallet\Model\User;
use Hyperwallet\Model\UserStatusTransition;
use Hyperwallet\Model\VenmoAccount;
use Hyperwallet\Model\VenmoAccountStatusTransition;
use Hyperwallet\Response\ErrorResponse;
use Hyperwallet\Util\ApiClient;

class HyperwalletTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor_throwErrorIfUsernameIsEmpty() {
        try {
            new Hyperwallet('', 'test-password');
            $this->fail('Expect HyperwalletArgumentException');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('You need to specify your API username and password!', $e->getMessage());
        }
    }

    public function testConstructor_throwErrorIfPasswordIsEmpty() {
        try {
            new Hyperwallet('test-username', '');
            $this->fail('Expect HyperwalletArgumentException');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('You need to specify your API username and password!', $e->getMessage());
        }
    }

    public function testConstructor_defaultServer() {
        $client = new Hyperwallet('test-username', 'test-password');
        $this->validateGuzzleClientSettings($client, 'https://api.sandbox.hyperwallet.com', 'test-username', 'test-password');
    }

    public function testConstructor_changedServer() {
        $client = new Hyperwallet('test-username', 'test-password', null, 'https://test.test');
        $this->validateGuzzleClientSettings($client, 'https://test.test', 'test-username', 'test-password');
    }

    //--------------------------------------
    // TLS verification
    //--------------------------------------

    public function testListUser_noTLSIssues() {
        $client = new Hyperwallet('test-username', 'test-password');
        try {
            $client->listUsers();
            $this->fail('Expect HyperwalletApiException');
        } catch (HyperwalletApiException $e) {
            $this->assertNotNull($e->getPrevious());
            $this->assertNotNull($e->getPrevious()->getResponse());
            $this->assertEquals(401, $e->getPrevious()->getResponse()->getStatusCode());
        }
    }

    //--------------------------------------
    // Users
    //--------------------------------------

    public function testCreateUser_withoutProgramToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $user = new User();

        \Phake::when($apiClientMock)->doPost('/rest/v3/users', array(), $user, array())->thenReturn(array('success' => 'true'));

        // Run test
        $this->assertNull($user->getProgramToken());

        $newUser = $client->createUser($user);
        $this->assertNotNull($newUser);
        $this->assertNull($user->getProgramToken());
        $this->assertEquals(array('success' => 'true'), $newUser->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users', array(), $user, array());
    }

    public function testCreateUser_withProgramTokenAddedByDefault() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $user = new User();

        \Phake::when($apiClientMock)->doPost('/rest/v3/users', array(), $user, array())->thenReturn(array('success' => 'true'));

        // Run test
        $this->assertNull($user->getProgramToken());

        $newUser = $client->createUser($user);
        $this->assertNotNull($newUser);
        $this->assertEquals('test-program-token', $user->getProgramToken());
        $this->assertEquals(array('success' => 'true'), $newUser->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users', array(), $user, array());
    }

    public function testCreateUser_withProgramTokenInUserObject() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $userProperties = array('success' => 'true', 'programToken' => 'test-program-token2', 'taxVerificationStatus' => User::TAX_VERIFICATION_STATUS_NOT_REQUIRED);
        $user = new User($userProperties);
        \Phake::when($apiClientMock)->doPost('/rest/v3/users', array(), $user, array())->thenReturn($userProperties);

        // Run test
        $this->assertEquals('test-program-token2', $user->getProgramToken());

        $newUser = $client->createUser($user);
        $this->assertNotNull($newUser);

        $this->assertEquals('test-program-token2', $user->getProgramToken());
        $this->assertEquals($newUser->getTaxVerificationStatus(), User::TAX_VERIFICATION_STATUS_NOT_REQUIRED); 
        $this->assertEquals($userProperties, $newUser->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users', array(), $user, array());
    }

    public function testGetUser_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getUser('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetUser_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}', array('user-token' => 'test-user-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $user = $client->getUser('test-user-token');
        $this->assertNotNull($user);
        $this->assertEquals(array('success' => 'true'), $user->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}', array('user-token' => 'test-user-token'), array());
    }

    public function testUpdateUser_noToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $user = new User();

        try {
            $client->updateUser($user);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('token is required!', $e->getMessage());
        }
    }

    public function testUpdateUser_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $user = new User(array('token' => 'test-user-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}', array('user-token' => 'test-user-token'), $user, array())->thenReturn(array('success' => 'true'));
        // Run test
        $newUser = $client->updateUser($user);
        $this->assertNotNull($newUser);
        $this->assertEquals(array('success' => 'true'), $newUser->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}', array('user-token' => 'test-user-token'), $user, array());
    }

    public function testListUsers_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users', array(), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $userList = $client->listUsers();
        $this->assertNotNull($userList);
        $this->assertCount(0, $userList);
        $this->assertEquals(1, $userList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users', array(), array());
    }

    public function testListUsers_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users', array(), array('status' => User::STATUS_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $userList = $client->listUsers(array('status' => User::STATUS_ACTIVATED));
        $this->assertNotNull($userList);
        $this->assertCount(1, $userList);
        $this->assertEquals(1, $userList->getCount());

        $this->assertEquals(array('success' => 'true'), $userList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users', array(), array('status' => User::STATUS_ACTIVATED));
    }

    public function testListUser_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listUsers($options=array('status' => User::STATUS_ACTIVATED, 'profileType'=>User::PROFILE_TYPE_INDIVIDUAL));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }


    public function testCreateUserStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new UserStatusTransition();
        $statusTransition->setTransition(UserStatusTransition::TRANSITION_ACTIVATED);

        try {
            $client->createUserStatusTransition('',$statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreateUserStatusTransition_noUserStatusTransition() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new UserStatusTransition();

        try {
            $client->createUserStatusTransition('test-user-token',$statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userStatusTransition is required!', $e->getMessage());
        }
    }

    public function testCreateUserStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new UserStatusTransition();
        $statusTransition->setTransition(UserStatusTransition::TRANSITION_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->createUserStatusTransition('test-user-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), $statusTransition, array());
    }

    public function testActivateUser() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new UserStatusTransition();
        $statusTransition->setTransition(UserStatusTransition::TRANSITION_ACTIVATED);
        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array())->thenReturn(array('success' => 'true'));
        // Run test
        $newStatusTransition = $client->activateUser('test-user-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array());
    }

    public function testDeactivateUser() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new UserStatusTransition();
        $statusTransition->setTransition(UserStatusTransition::TRANSITION_DE_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array())->thenReturn(array('success' => 'true'));
        // Run test
        $newStatusTransition = $client->deactivateUser('test-user-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array());
    }

    public function testLockUser() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new UserStatusTransition();
        $statusTransition->setTransition(UserStatusTransition::TRANSITION_LOCKED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array())->thenReturn(array('success' => 'true'));
        // Run test
        $newStatusTransition = $client->lockUser('test-user-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array());
    }

    public function testFreezeUser() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new UserStatusTransition();
        $statusTransition->setTransition(UserStatusTransition::TRANSITION_FROZEN);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array())->thenReturn(array('success' => 'true'));
        // Run test
        $newStatusTransition = $client->freezeUser('test-user-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array());
    }

    public function testPreactivateUser() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new UserStatusTransition();
        $statusTransition->setTransition(UserStatusTransition::TRANSITION_PRE_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array())->thenReturn(array('success' => 'true'));
        // Run test
        $newStatusTransition = $client->preactivateUser('test-user-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'),
            $statusTransition, array());
    }

    public function testGetUserStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getUserStatusTransition('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetUserStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getUserStatusTransition('test-user-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListUserStatusTransitions_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listUserStatusTransitions('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListUserStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listUserStatusTransitions('test-user-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), array());
    }

    public function testListUserStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), array('transition'=>UserStatusTransition::TRANSITION_DE_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listUserStatusTransitions('test-user-token', array('transition'=>UserStatusTransition::TRANSITION_DE_ACTIVATED));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), array('transition'=>UserStatusTransition::TRANSITION_DE_ACTIVATED));
    }

    public function testListUserStatusTransitions_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listUserStatusTransitions("test-user-token",$options=array('transition'=>UserStatusTransition::TRANSITION_DE_ACTIVATED, 'profileType'=>User::PROFILE_TYPE_INDIVIDUAL));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Client Token
    //--------------------------------------

    public function testGetAuthenticationToken_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getAuthenticationToken('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetAuthenticationToken_allParameters() {
        // Setup data
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/authentication-token', array('user-token' => 'test-user-token'), null, array())->thenReturn(array('value' => 'true'));

        // Run test
        $authenticationToken = $client->getAuthenticationToken('test-user-token');
        $this->assertNotNull($authenticationToken);
        $this->assertEquals(array('value' => 'true'), $authenticationToken->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/authentication-token', array('user-token' => 'test-user-token'), null, array());
    }

    //--------------------------------------
    // Paper Checks
    //--------------------------------------

    public function testCreatePaperCheck_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $paperCheck = new PaperCheck();

        try {
            $client->createPaperCheck('', $paperCheck);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreatePaperCheck_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $paperCheck = new PaperCheck();

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), $paperCheck, array())->thenReturn(array('postalCode' => 'ABCD'));

        // Run test
        $newPaperCheck = $client->createPaperCheck('test-user-token', $paperCheck);
        $this->assertNotNull($newPaperCheck);
        $this->assertEquals(array('postalCode' => 'ABCD'), $newPaperCheck->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), $paperCheck, array());
    }

    public function testGetPaperCheck_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getPaperCheck('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetPaperCheck_noPaperCheckToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getPaperCheck('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paperCheckToken is required!', $e->getMessage());
        }
    }

    public function testGetPaperCheck_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array())->thenReturn(array('postalCode' => 'ABCD'));

        // Run test
        $paperCheck = $client->getPaperCheck('test-user-token', 'test-paper-check-token');
        $this->assertNotNull($paperCheck);
        $this->assertEquals(array('postalCode' => 'ABCD'), $paperCheck->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array());
    }

    public function testUpdatePaperCheck_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $paperCheck = new PaperCheck();

        try {
            $client->updatePaperCheck('', $paperCheck);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testUpdatePaperCheck_noToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $paperCheck = new PaperCheck();

        try {
            $client->updatePaperCheck('test-user-token', $paperCheck);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transfer method token is required!', $e->getMessage());
        }
    }

    public function testUpdatePaperCheck_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $paperCheck = new PaperCheck(array('token' => 'test-paper-check-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-paper-check-token', 'transfer-method-name' => 'paper-checks'), $paperCheck, array())->thenReturn(array('postalCode' => 'ABCD'));

        // Run test
        $newPaperCheck = $client->updatePaperCheck('test-user-token', $paperCheck);
        $this->assertNotNull($newPaperCheck);
        $this->assertEquals(array('postalCode' => 'ABCD'), $newPaperCheck->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-paper-check-token', 'transfer-method-name' => 'paper-checks'), $paperCheck, array());
    }

    public function testListPaperChecks_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $client->listPaperChecks('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListPaperChecks_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $paperCheckList = $client->listPaperChecks('test-user-token');
        $this->assertNotNull($paperCheckList);
        $this->assertCount(0, $paperCheckList);
        $this->assertEquals(1, $paperCheckList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), array());
    }

    public function testListPaperChecks_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), array('status' => PaperCheck::STATUS_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('postalCode' => 'ABCD'))));

        // Run test
        $paperCheckList = $client->listPaperChecks('test-user-token', array('status' => PaperCheck::STATUS_ACTIVATED));
        $this->assertNotNull($paperCheckList);
        $this->assertCount(1, $paperCheckList);
        $this->assertEquals(1, $paperCheckList->getCount());

        $this->assertEquals(array('postalCode' => 'ABCD'), $paperCheckList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), array('status' => PaperCheck::STATUS_ACTIVATED));
    }

    public function testListPaperChecks_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listPaperChecks("user-token",$options=array('status' => PaperCheck::STATUS_ACTIVATED, 'type'=>PaperCheck::TYPE_PAPER_CHECK));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testDeactivatePaperCheck_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivatePaperCheck('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testDeactivatePaperCheck_noPaperCheckToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivatePaperCheck('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paperCheckToken is required!', $e->getMessage());
        }
    }

    public function testDeactivatePaperCheck_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $statusTransition = new PaperCheckStatusTransition();
        $statusTransition->setTransition(PaperCheckStatusTransition::TRANSITION_DE_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), $statusTransition, array())->thenReturn(array('postalCode' => 'ABCD'));

        // Run test
        $newStatusTransition = $client->deactivatePaperCheck('test-user-token', 'test-paper-check-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('postalCode' => 'ABCD'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), $statusTransition, array());
    }

    public function testCreatePaperCheckStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new PaperCheckStatusTransition();

        try {
            $client->createPaperCheckStatusTransition('', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreatePaperCheckStatusTransition_noPaperCheckToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new PaperCheckStatusTransition();

        try {
            $client->createPaperCheckStatusTransition('test-user-token', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paperCheckToken is required!', $e->getMessage());
        }
    }

    public function testCreatePaperCheckStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new PaperCheckStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), $statusTransition, array())->thenReturn(array('postalCode' => 'ABCD'));

        // Run test
        $newStatusTransition = $client->createPaperCheckStatusTransition('test-user-token', 'test-paper-check-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('postalCode' => 'ABCD'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), $statusTransition, array());
    }

    public function testGetPaperCheckStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPaperCheckStatusTransition('', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetPaperCheckStatusTransition_noPaperCheckToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPaperCheckStatusTransition('test-user-token', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paperCheckToken is required!', $e->getMessage());
        }
    }

    public function testGetPaperCheckStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPaperCheckStatusTransition('test-user-token', 'test-paper-check-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetPaperCheckStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('postalCode' => 'ABCD'));

        // Run test
        $statusTransition = $client->getPaperCheckstatusTransition('test-user-token', 'test-paper-check-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('postalCode' => 'ABCD'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListPaperCheckStatusTransitions_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listPaperCheckStatusTransitions('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListPaperCheckStatusTransitions_noPaperCheckToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listPaperCheckStatusTransitions('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paperCheckToken is required!', $e->getMessage());
        }
    }

    public function testListPaperCheckStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listPaperCheckStatusTransitions('test-user-token', 'test-paper-check-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array());
    }

    public function testListPaperCheckStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array('transition' => PaperCheckStatusTransition::TRANSITION_DE_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('postalCode' => 'ABCD'))));

        // Run test
        $statusTransitionList = $client->listPaperCheckStatusTransitions('test-user-token', 'test-paper-check-token', array('transition' => PaperCheckStatusTransition::TRANSITION_DE_ACTIVATED));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('postalCode' => 'ABCD'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array('transition' => PaperCheckStatusTransition::TRANSITION_DE_ACTIVATED));
    }

    public function testListPaperCheckStatusTransitions_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listPaperCheckStatusTransitions('test-user-token', 'test-paper-check-token', array('transition' => PaperCheckStatusTransition::TRANSITION_DE_ACTIVATED, 'status' => PaperCheck::STATUS_ACTIVATED));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Transfers
    //--------------------------------------

    public function testCreateTransfer_noSourceToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $transfer = new Transfer();

        try {
            $client->createTransfer($transfer);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('sourceToken is required!', $e->getMessage());
        }
    }

    public function testCreateTransfer_noDestinationToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $transfer = new Transfer();
        $transfer->setSourceToken('test-source-token');

        try {
            $client->createTransfer($transfer);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('destinationToken is required!', $e->getMessage());
        }
    }

    public function testCreateTransfer_noClientTransferId() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $transfer = new Transfer();
        $transfer->setSourceToken('test-source-token');
        $transfer->setDestinationToken('test-destination-token');

        try {
            $client->createTransfer($transfer);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('clientTransferId is required!', $e->getMessage());
        }
    }

    public function testCreateTransfer_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $transfer = new Transfer();
        $transfer->setSourceToken('test-source-token');
        $transfer->setDestinationToken('test-destination-token');
        $transfer->setClientTransferId('test-clientTransferId');

        \Phake::when($apiClientMock)->doPost('/rest/v3/transfers', array(), $transfer, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $newTransfer = $client->createTransfer($transfer);
        $this->assertNotNull($newTransfer);
        $this->assertEquals(array('token' => 'test-token'), $newTransfer->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/transfers', array(), $transfer, array());
    }

    public function testGetTransfer_noTransferToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getTransfer('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferToken is required!', $e->getMessage());
        }
    }

    public function testGetTransfer_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}', array('transfer-token' => 'test-transfer-token'), array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $transfer = $client->getTransfer('test-transfer-token');
        $this->assertNotNull($transfer);
        $this->assertEquals(array('token' => 'test-token'), $transfer->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}', array('transfer-token' => 'test-transfer-token'), array());
    }

    public function testListTransfers_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers', array(), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $transferList = $client->listTransfers();
        $this->assertNotNull($transferList);
        $this->assertCount(0, $transferList);
        $this->assertEquals(1, $transferList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers', array(), array());
    }

    public function testListTransfers_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers', array(), array('clientTransferId' => 'client-Transfer-Id'))->thenReturn(array('count' => 1, 'data' => array(array('token' => 'test-token'))));

        // Run test
        $transferList = $client->listTransfers(array('clientTransferId' => 'client-Transfer-Id'));
        $this->assertNotNull($transferList);
        $this->assertCount(1, $transferList);
        $this->assertEquals(1, $transferList->getCount());

        $this->assertEquals(array('token' => 'test-token'), $transferList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers', array(), array('clientTransferId' => 'client-Transfer-Id'));
    }

    public function testListTransfers_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listTransfers($options=array('clientTransferId' => 'client-Transfer-Id', 'status'=>Transfer::STATUS_COMPLETED));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testCreateTransferStatusTransition_noTransferToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new TransferStatusTransition();

        try {
            $client->createTransferStatusTransition('', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferToken is required!', $e->getMessage());
        }
    }

    public function testCreateTransferStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new TransferStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/transfers/{transfer-token}/status-transitions', array('transfer-token' => 'test-transfer-token'), $statusTransition, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $newStatusTransition = $client->createTransferStatusTransition('test-transfer-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('token' => 'test-token'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/transfers/{transfer-token}/status-transitions', array('transfer-token' => 'test-transfer-token'), $statusTransition, array());
    }

    public function testGetTransferStatusTransition_noTransferToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getTransferStatusTransition('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferToken is required!', $e->getMessage());
        }
    }

    public function testGetTransferStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getTransferStatusTransition('test-transfer-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetTransferStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/status-transitions/{status-transition-token}', array('transfer-token' => 'test-transfer-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getTransferStatusTransition('test-transfer-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/status-transitions/{status-transition-token}', array('transfer-token' => 'test-transfer-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListTransferStatusTransitions_noTransferToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listTransferStatusTransitions('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transfer token is required!', $e->getMessage());
        }
    }

    public function testListTransferStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/status-transitions', array('transfer-token' => 'test-transfer-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listTransferStatusTransitions('test-transfer-token', array('test' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/status-transitions', array('transfer-token' => 'test-transfer-token'), array('test' => 'value'));
    }

    //--------------------------------------
    // PayPal Accounts
    //--------------------------------------

    public function testCreatePayPalAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $payPalAccount = new PayPalAccount();

        try {
            $client->createPayPalAccount('', $payPalAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreatePayPalAccount_noTransferMethodCountry() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $payPalAccount = new PayPalAccount();

        try {
            $client->createPayPalAccount('test-user-token', $payPalAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferMethodCountry is required!', $e->getMessage());
        }
    }

    public function testCreatePayPalAccount_noTransferMethodCurrency() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $payPalAccount = new PayPalAccount();
        $payPalAccount->setTransferMethodCountry('test-transferMethodCountry');

        try {
            $client->createPayPalAccount('test-user-token', $payPalAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferMethodCurrency is required!', $e->getMessage());
        }
    }

    public function testCreatePayPalAccount_noEmail() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $payPalAccount = new PayPalAccount();
        $payPalAccount->setTransferMethodCountry('test-transferMethodCountry');
        $payPalAccount->setTransferMethodCurrency('test-transferMethodCurrency');

        try {
            $client->createPayPalAccount('test-user-token', $payPalAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('email is required!', $e->getMessage());
        }
    }

    public function testCreatePayPalAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $payPalAccount = new PayPalAccount();
        $payPalAccount->setTransferMethodCountry('test-transferMethodCountry');
        $payPalAccount->setTransferMethodCurrency('test-transferMethodCurrency');
        $payPalAccount->setEmail('test-email');

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), $payPalAccount, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $newPayPalAccount = $client->createPayPalAccount('test-user-token', $payPalAccount);
        $this->assertNotNull($newPayPalAccount);
        $this->assertEquals(array('token' => 'test-token'), $newPayPalAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), $payPalAccount, array());
    }

    public function testGetPayPalAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getPayPalAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetPayPalAccount_noPayPalAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getPayPalAccount('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('payPalAccountToken is required!', $e->getMessage());
        }
    }

    public function testGetPayPalAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{paypal-account-token}', array('user-token' => 'test-user-token', 'paypal-account-token' => 'test-paypal-account-token'), array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $payPalAccount = $client->getPayPalAccount('test-user-token', 'test-paypal-account-token');
        $this->assertNotNull($payPalAccount);
        $this->assertEquals(array('token' => 'test-token'), $payPalAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{paypal-account-token}', array('user-token' => 'test-user-token', 'paypal-account-token' => 'test-paypal-account-token'), array());
    }

    public function testUpdatePayPalAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->updatePayPalAccount('', new PayPalAccount());
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testUpdatePayPalAccount_noPayPalAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->updatePayPalAccount('test-user-token', new PayPalAccount());
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transfer method token is required!', $e->getMessage());
        }
    }

    public function testUpdatePayPalAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $payPalAcc = new PayPalAccount(array('token' => 'test-paypal-account-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-paypal-account-token', 'transfer-method-name' => 'paypal-accounts'), $payPalAcc, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $payPalAccount = $client->updatePayPalAccount('test-user-token', $payPalAcc);
        $this->assertNotNull($payPalAccount);
        $this->assertEquals(array('token' => 'test-token'), $payPalAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-paypal-account-token', 'transfer-method-name' => 'paypal-accounts'), $payPalAcc, array());
    }

    public function testListPayPalAccounts_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $client->listPayPalAccounts('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListPayPalAccounts_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $payPalAccountsList = $client->listPayPalAccounts('test-user-token');
        $this->assertNotNull($payPalAccountsList);
        $this->assertCount(0, $payPalAccountsList);
        $this->assertEquals(1, $payPalAccountsList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), array());
    }

    public function testListPayPalAccounts_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), array('status' =>'ACTIVATED'))->thenReturn(array('count' => 1, 'data' => array(array('token' => 'test-token'))));

        // Run test
        $payPalAccountsList = $client->listPayPalAccounts('test-user-token', array('status' =>'ACTIVATED'));
        $this->assertNotNull($payPalAccountsList);
        $this->assertCount(1, $payPalAccountsList);
        $this->assertEquals(1, $payPalAccountsList->getCount());

        $this->assertEquals(array('token' => 'test-token'), $payPalAccountsList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), array('status' =>'ACTIVATED'));
    }

    public function testListPayPalAccounts_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listPayPalAccounts('test-user-token',$options=array('status' =>'ACTIVATED', 'profileType'=>'INDIVIDUAL'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testDeactivatePayPalAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivatePayPalAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testDeactivatePayPalAccount_noPayPalAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivatePayPalAccount('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('payPalAccountToken is required!', $e->getMessage());
        }
    }

    public function testDeactivatePayPalAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $statusTransition = new PayPalAccountStatusTransition();
        $statusTransition->setTransition(PayPalAccountStatusTransition::TRANSITION_DE_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->deactivatePayPalAccount('test-user-token', 'test-payPal-account-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), $statusTransition, array());
    }

    public function testCreatePayPalAccountStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new PayPalAccountStatusTransition();

        try {
            $client->createPayPalAccountStatusTransition('', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreatePayPalAccountStatusTransition_noPayPalAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new PayPalAccountStatusTransition();

        try {
            $client->createPayPalAccountStatusTransition('test-user-token', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('payPalAccountToken is required!', $e->getMessage());
        }
    }

    public function testCreatePayPalAccountStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new PayPalAccountStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->createPayPalAccountStatusTransition('test-user-token', 'test-payPal-account-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), $statusTransition, array());
    }

    public function testGetPayPalAccountStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPayPalAccountStatusTransition('', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetPayPalAccountStatusTransition_noPayPalAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPayPalAccountStatusTransition('test-user-token', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('payPalAccountToken is required!', $e->getMessage());
        }
    }

    public function testGetPayPalAccountStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPayPalAccountStatusTransition('test-user-token', 'test-payPal-account-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetPayPalAccountStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getPayPalAccountStatusTransition('test-user-token', 'test-payPal-account-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListPayPalAccountStatusTransitions_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listPayPalAccountStatusTransitions('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListPayPalAccountStatusTransitions_noPayPalAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listPayPalAccountStatusTransitions('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('payPalAccountToken is required!', $e->getMessage());
        }
    }

    public function testListPayPalAccountStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listPayPalAccountStatusTransitions('test-user-token', 'test-payPal-account-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), array());
    }

    public function testListPayPalAccountStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), array('transition' => PayPalAccountStatusTransition::TRANSITION_DE_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listPayPalAccountStatusTransitions('test-user-token', 'test-payPal-account-token', array('transition' => PayPalAccountStatusTransition::TRANSITION_DE_ACTIVATED));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts/{payPal-account-token}/status-transitions', array('user-token' => 'test-user-token', 'payPal-account-token' => 'test-payPal-account-token'), array('transition' => PayPalAccountStatusTransition::TRANSITION_DE_ACTIVATED));
    }

    public function testListPayPalAccountStatusTransitions_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listPayPalAccountStatusTransitions('test-user-token', 'test-paypal-account-token', array('transition' => PayPalAccountStatusTransition::TRANSITION_DE_ACTIVATED, 'status' => 'ACTIVATED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Prepaid Cards
    //--------------------------------------

    public function testCreatePrepaidCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $prepaidCard = new PrepaidCard();

        try {
            $client->createPrepaidCard('', $prepaidCard);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreatePrepaidCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $prepaidCard = new PrepaidCard();

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), $prepaidCard, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newPrepaidCard = $client->createPrepaidCard('test-user-token', $prepaidCard);
        $this->assertNotNull($newPrepaidCard);
        $this->assertEquals(array('success' => 'true'), $newPrepaidCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), $prepaidCard, array());
    }

    public function testGetPrepaidCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getPrepaidCard('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetPrepaidCard_noPrepaidCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getPrepaidCard('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('prepaidCardToken is required!', $e->getMessage());
        }
    }

    public function testGetPrepaidCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $prepaidCard = $client->getPrepaidCard('test-user-token', 'test-prepaid-card-token');
        $this->assertNotNull($prepaidCard);
        $this->assertEquals(array('success' => 'true'), $prepaidCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array());
    }

    public function testUpdatePrepaidCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $prepaidCard = new PrepaidCard();

        try {
            $client->updatePrepaidCard('', $prepaidCard);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testUpdatePrepaidCard_noToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $prepaidCard = new PrepaidCard();

        try {
            $client->updatePrepaidCard('test-user-token', $prepaidCard);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transfer method token is required!', $e->getMessage());
        }
    }

    public function testUpdatePrepaidCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $prepaidCard = new PrepaidCard(array('token' => 'test-prepaid-card-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-prepaid-card-token', 'transfer-method-name' => 'prepaid-cards'), $prepaidCard, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newPrepaidCard = $client->updatePrepaidCard('test-user-token', $prepaidCard);
        $this->assertNotNull($newPrepaidCard);
        $this->assertEquals(array('success' => 'true'), $newPrepaidCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-prepaid-card-token', 'transfer-method-name' => 'prepaid-cards'), $prepaidCard, array());
    }

    public function testListPrepaidCards_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $client->listPrepaidCards('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListPrepaidCards_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $prepaidCardList = $client->listPrepaidCards('test-user-token');
        $this->assertNotNull($prepaidCardList);
        $this->assertCount(0, $prepaidCardList);
        $this->assertEquals(1, $prepaidCardList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), array());
    }

    public function testListPrepaidCards_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), array('status' =>PrepaidCard::STATUS_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $prepaidCardList = $client->listPrepaidCards('test-user-token', array('status' =>PrepaidCard::STATUS_ACTIVATED));
        $this->assertNotNull($prepaidCardList);
        $this->assertCount(1, $prepaidCardList);
        $this->assertEquals(1, $prepaidCardList->getCount());

        $this->assertEquals(array('success' => 'true'), $prepaidCardList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), array('status' =>PrepaidCard::STATUS_ACTIVATED));
    }

    public function testListPrepaidCards_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listPrepaidCards('test-user-token',$options=array('status' =>PrepaidCard::STATUS_ACTIVATED, 'type'=>PrepaidCard::CARD_BRAND_VISA));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    /**
     * @dataProvider prepaidCardStatusTransitionProvider
     *
     * @param string $methodName The status transition method name
     */
    public function testStatusTransitionMethods_noUserToken($methodName) {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $transitionMethod = $this->findMethodByName($client, $methodName);

        // Run test
        try {
            $transitionMethod->invoke($client, '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    /**
     * @dataProvider prepaidCardStatusTransitionProvider
     *
     * @param string $methodName The status transition method name
     */
    public function testPrepaidCardStatusTransitionMethods_noPrepaidCardToken($methodName) {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $transitionMethod = $this->findMethodByName($client, $methodName);

        // Run test
        try {
            $transitionMethod->invoke($client, 'test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('prepaidCardToken is required!', $e->getMessage());
        }
    }

    /**
     * @dataProvider prepaidCardStatusTransitionProvider
     *
     * @param string $methodName The status transition method name
     * @param string $transition The status transition to perform
     */
    public function testPrepaidCardStatusTransitionMethods_allParameters($methodName, $transition) {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $transitionMethod = $this->findMethodByName($client, $methodName);

        $statusTransition = new PrepaidCardStatusTransition();
        $statusTransition->setTransition($transition);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $transitionMethod->invoke($client, 'test-user-token', 'test-prepaid-card-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), $statusTransition, array());
    }

    public function testCreatePrepaidCardStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new PrepaidCardStatusTransition();

        try {
            $client->createPrepaidCardStatusTransition('', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreatePrepaidCardStatusTransition_noPrepaidCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new PrepaidCardStatusTransition();

        try {
            $client->createPrepaidCardStatusTransition('test-user-token', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('prepaidCardToken is required!', $e->getMessage());
        }
    }

    public function testCreatePrepaidCardStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new PrepaidCardStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->createPrepaidCardStatusTransition('test-user-token', 'test-prepaid-card-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), $statusTransition, array());
    }

    public function testGetPrepaidCardStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPrepaidCardStatusTransition('', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetPrepaidCardStatusTransition_noPrepaidCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPrepaidCardStatusTransition('test-user-token', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('prepaidCardToken is required!', $e->getMessage());
        }
    }

    public function testGetPrepaidCardStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPrepaidCardStatusTransition('test-user-token', 'test-prepaid-card-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetPrepaidCardStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getPrepaidCardStatusTransition('test-user-token', 'test-prepaid-card-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListPrepaidCardStatusTransitions_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listPrepaidCardStatusTransitions('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListPrepaidCardStatusTransitions_noPrepaidCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listPrepaidCardStatusTransitions('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('prepaidCardToken is required!', $e->getMessage());
        }
    }

    public function testListPrepaidCardStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listPrepaidCardStatusTransitions('test-user-token', 'test-prepaid-card-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array());
    }

    public function testListPrepaidCardStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('transition' => PrepaidCardStatusTransition::TRANSITION_DE_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listPrepaidCardStatusTransitions('test-user-token', 'test-prepaid-card-token', array('transition' => PrepaidCardStatusTransition::TRANSITION_DE_ACTIVATED));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('transition' => PrepaidCardStatusTransition::TRANSITION_DE_ACTIVATED));
    }

    public function testListPrepaidCardStatusTransitions_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listPrepaidCardStatusTransitions('test-user-token', 'test-prepaid-card-token', array('transition' => PrepaidCardStatusTransition::TRANSITION_DE_ACTIVATED, 'status' => PrepaidCard::STATUS_ACTIVATED));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }


    //--------------------------------------
    // Bank Accounts
    //--------------------------------------

    public function testCreateBankAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $bankAccount = new BankAccount();

        try {
            $client->createBankAccount('', $bankAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreateBankAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $bankAccount = new BankAccount();

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), $bankAccount, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newBankAccount = $client->createBankAccount('test-user-token', $bankAccount);
        $this->assertNotNull($newBankAccount);
        $this->assertEquals(array('success' => 'true'), $newBankAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), $bankAccount, array());
    }

    public function testGetBankAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getBankAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetBankAccount_noBankAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getBankAccount('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankAccountToken is required!', $e->getMessage());
        }
    }

    public function testGetBankAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $bankAccount = $client->getBankAccount('test-user-token', 'test-bank-account-token');
        $this->assertNotNull($bankAccount);
        $this->assertEquals(array('success' => 'true'), $bankAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array());
    }

    public function testUpdateBankAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $bankAccount = new BankAccount();

        try {
            $client->updateBankAccount('', $bankAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testUpdateBankAccount_noToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $bankAccount = new BankAccount();

        try {
            $client->updateBankAccount('test-user-token', $bankAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transfer method token is required!', $e->getMessage());
        }
    }

    public function testUpdateBankAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $bankAccount = new BankAccount(array('token' => 'test-bank-account-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-bank-account-token', 'transfer-method-name' => 'bank-accounts'), $bankAccount, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newBankAccount = $client->updateBankAccount('test-user-token', $bankAccount);
        $this->assertNotNull($newBankAccount);
        $this->assertEquals(array('success' => 'true'), $newBankAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-bank-account-token', 'transfer-method-name' => 'bank-accounts'), $bankAccount, array());
    }

    public function testListBankAccounts_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->listBankAccounts('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListBankAccounts_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $bankAccountList = $client->listBankAccounts('test-user-token');
        $this->assertNotNull($bankAccountList);
        $this->assertCount(0, $bankAccountList);
        $this->assertEquals(1, $bankAccountList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), array());
    }

    public function testListBankAccounts_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), array('status' =>BankAccount::STATUS_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $bankAccountList = $client->listBankAccounts('test-user-token', array('status' =>BankAccount::STATUS_ACTIVATED));
        $this->assertNotNull($bankAccountList);
        $this->assertCount(1, $bankAccountList);
        $this->assertEquals(1, $bankAccountList->getCount());

        $this->assertEquals(array('success' => 'true'), $bankAccountList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), array('status' =>BankAccount::STATUS_ACTIVATED));
    }

    public function testListBankAccounts_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listBankAccounts('test-user-token',$options=array('status' =>BankAccount::STATUS_ACTIVATED, 'profileType'=>BankAccount::PROFILE_TYPE_INDIVIDUAL));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testDeactivateBankAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivateBankAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testDeactivateBankAccount_noBankAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivateBankAccount('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankAccountToken is required!', $e->getMessage());
        }
    }

    public function testDeactivateBankAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $statusTransition = new BankAccountStatusTransition();
        $statusTransition->setTransition(BankAccountStatusTransition::TRANSITION_DE_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->deactivateBankAccount('test-user-token', 'test-bank-account-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), $statusTransition, array());
    }

    public function testCreateBankAccountStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new BankAccountStatusTransition();

        try {
            $client->createBankAccountStatusTransition('', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreateBankAccountStatusTransition_noBankAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new BankAccountStatusTransition();

        try {
            $client->createBankAccountStatusTransition('test-user-token', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankAccountToken is required!', $e->getMessage());
        }
    }

    public function testCreateBankAccountStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new BankAccountStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->createBankAccountStatusTransition('test-user-token', 'test-bank-account-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), $statusTransition, array());
    }

    public function testGetBankAccountStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getBankAccountStatusTransition('', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetBankAccountStatusTransition_noBankAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getBankAccountStatusTransition('test-user-token', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankAccountToken is required!', $e->getMessage());
        }
    }

    public function testGetBankAccountStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getBankAccountStatusTransition('test-user-token', 'test-bank-account-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetBankAccountStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getBankAccountStatusTransition('test-user-token', 'test-bank-account-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListBankAccountStatusTransitions_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBankAccountStatusTransitions('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListBankAccountStatusTransitions_noBankAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBankAccountStatusTransitions('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankAccountToken is required!', $e->getMessage());
        }
    }

    public function testListBankAccountStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listBankAccountStatusTransitions('test-user-token', 'test-bank-account-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array());
    }

    public function testListBankAccountStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array('transition' => BankAccountStatusTransition::TRANSITION_DE_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listBankAccountStatusTransitions('test-user-token', 'test-bank-account-token', array('transition' => BankAccountStatusTransition::TRANSITION_DE_ACTIVATED));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array('transition' => BankAccountStatusTransition::TRANSITION_DE_ACTIVATED));
    }

    public function testListBankAccountStatusTransitions_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listBankAccountStatusTransitions('test-user-token', 'test-bank-account-token', array('transition' => BankAccountStatusTransition::TRANSITION_DE_ACTIVATED, 'status' => BankAccount::STATUS_ACTIVATED));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Bank Cards
    //--------------------------------------

    public function testCreateBankCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $bankCard = new BankCard();

        try {
            $client->createBankCard('', $bankCard);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreateBankCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $bankCard = new BankCard();

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), $bankCard, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newBankCard = $client->createBankCard('test-user-token', $bankCard);
        $this->assertNotNull($newBankCard);
        $this->assertEquals(array('success' => 'true'), $newBankCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), $bankCard, array());
    }

    public function testGetBankCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getBankCard('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetBankCard_noBankCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getBankCard('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankCardToken is required!', $e->getMessage());
        }
    }

    public function testGetBankCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $bankCard = $client->getBankCard('test-user-token', 'test-bank-card-token');
        $this->assertNotNull($bankCard);
        $this->assertEquals(array('success' => 'true'), $bankCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array());
    }

    public function testUpdateBankCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $bankCard = new BankCard();

        try {
            $client->updateBankCard('', $bankCard);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testUpdateBankCard_noToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $bankCard = new BankCard();

        try {
            $client->updateBankCard('test-user-token', $bankCard);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transfer method token is required!', $e->getMessage());
        }
    }

    public function testUpdateBankCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $bankCard = new BankCard(array('token' => 'test-bank-card-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-bank-card-token', 'transfer-method-name' => 'bank-cards'), $bankCard, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newBankCard = $client->updateBankCard('test-user-token', $bankCard);
        $this->assertNotNull($newBankCard);
        $this->assertEquals(array('success' => 'true'), $newBankCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-bank-card-token', 'transfer-method-name' => 'bank-cards'), $bankCard, array());
    }

    public function testListBankCards_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $client->listBankCards('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListBankCards_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $bankCardList = $client->listBankCards('test-user-token');
        $this->assertNotNull($bankCardList);
        $this->assertCount(0, $bankCardList);
        $this->assertEquals(1, $bankCardList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), array());
    }

    public function testListBankCards_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), array('status' =>BankCard::STATUS_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $bankCardList = $client->listBankCards('test-user-token', array('status' =>BankCard::STATUS_ACTIVATED));
        $this->assertNotNull($bankCardList);
        $this->assertCount(1, $bankCardList);
        $this->assertEquals(1, $bankCardList->getCount());

        $this->assertEquals(array('success' => 'true'), $bankCardList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), array('status' =>BankCard::STATUS_ACTIVATED));
    }

    public function testListBankCards_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listBankCards('test-user-token',$options=array('status' =>BankCard::STATUS_ACTIVATED, 'profileType'=>BankCard::TYPE_BANK_CARD));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testDeactivateBankCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivateBankCard('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testDeactivateBankCard_noBankAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivateBankCard('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankCardToken is required!', $e->getMessage());
        }
    }

    public function testDeactivateBankCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $statusTransition = new BankCardStatusTransition();
        $statusTransition->setTransition(BankCardStatusTransition::TRANSITION_DE_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->deactivateBankCard('test-user-token', 'test-bank-card-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), $statusTransition, array());
    }

    public function testCreateBankCardStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new BankCardStatusTransition();

        try {
            $client->createBankCardStatusTransition('', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreateBankCardStatusTransition_noBankCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new BankCardStatusTransition();

        try {
            $client->createBankCardStatusTransition('test-user-token', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankCardToken is required!', $e->getMessage());
        }
    }

    public function testCreateBankCardStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new BankCardStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->createBankCardStatusTransition('test-user-token', 'test-bank-card-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), $statusTransition, array());
    }

    public function testGetBankCardStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getBankCardStatusTransition('', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetBankCardStatusTransition_noBankCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getBankCardStatusTransition('test-user-token', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankCardToken is required!', $e->getMessage());
        }
    }

    public function testGetBankCardStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPrepaidCardStatusTransition('test-user-token', 'test-bank-card-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetBankCardStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getBankCardStatusTransition('test-user-token', 'test-bank-card-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListBankCardStatusTransitions_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBankCardStatusTransitions('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListBankCardStatusTransitions_noBankCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBankCardStatusTransitions('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('bankCardToken is required!', $e->getMessage());
        }
    }

    public function testListBankCardStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listBankCardStatusTransitions('test-user-token', 'test-bank-card-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array());
    }

    public function testListBankCardStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array('transition' => BankCardStatusTransition::TRANSITION_DE_ACTIVATED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listBankCardStatusTransitions('test-user-token', 'test-bank-card-token', array('transition' => BankCardStatusTransition::TRANSITION_DE_ACTIVATED));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array('transition' => BankCardStatusTransition::TRANSITION_DE_ACTIVATED));
    }

    public function testListBankCardStatusTransitions_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listBankCardStatusTransitions('test-user-token', 'test-bank-card-token', array('transition' => BankCardStatusTransition::TRANSITION_DE_ACTIVATED, 'status' => 'ACTIVATED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }


    //--------------------------------------
    // Transfer Methods
    //--------------------------------------

    public function testCreateTransferMethod_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->createTransferMethod('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreateTransferMethod_noJsonCacheToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->createTransferMethod('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('jsonCacheToken is required!', $e->getMessage());
        }
    }

    public function testCreateTransferMethod_noPayload() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), null, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ))->thenReturn(array('success' => 'true', 'type' => TransferMethod::TYPE_BANK_ACCOUNT));

        // Run test
        $newTransferMethod = $client->createTransferMethod('test-user-token', 'test-json-cache-token');
        $this->assertNotNull($newTransferMethod);
        $this->assertEquals(array('success' => 'true', 'type' => BankAccount::TYPE_BANK_ACCOUNT), $newTransferMethod->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), null, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ));
    }

    public function testCreateTransferMethod_payload_result_bank_account() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $transferMethod = new TransferMethod();
        $transferMethod->setFirstName('test-first-name');

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), $transferMethod, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ))->thenReturn(array('success' => 'true', 'type' => TransferMethod::TYPE_BANK_ACCOUNT));

        // Run test
        $newTransferMethod = $client->createTransferMethod('test-user-token', 'test-json-cache-token', $transferMethod);
        $this->assertNotNull($newTransferMethod);
        $this->assertInstanceOf(BankAccount::class, $newTransferMethod);
        $this->assertEquals(array('success' => 'true', 'type' => BankAccount::TYPE_BANK_ACCOUNT), $newTransferMethod->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), $transferMethod, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ));
    }

    public function testCreateTransferMethod_payload_result_wire_account() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $transferMethod = new TransferMethod();
        $transferMethod->setFirstName('test-first-name');

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), $transferMethod, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ))->thenReturn(array('success' => 'true', 'type' => TransferMethod::TYPE_WIRE_ACCOUNT));

        // Run test
        $newTransferMethod = $client->createTransferMethod('test-user-token', 'test-json-cache-token', $transferMethod);
        $this->assertNotNull($newTransferMethod);
        $this->assertInstanceOf(BankAccount::class, $newTransferMethod);
        $this->assertEquals(array('success' => 'true', 'type' => BankAccount::TYPE_WIRE_ACCOUNT), $newTransferMethod->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), $transferMethod, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ));
    }

    public function testCreateTransferMethod_payload_result_prepaid_card() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $transferMethod = new TransferMethod();
        $transferMethod->setFirstName('test-first-name');

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), $transferMethod, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ))->thenReturn(array('success' => 'true', 'type' => TransferMethod::TYPE_PREPAID_CARD));

        // Run test
        $newTransferMethod = $client->createTransferMethod('test-user-token', 'test-json-cache-token', $transferMethod);
        $this->assertNotNull($newTransferMethod);
        $this->assertInstanceOf(PrepaidCard::class, $newTransferMethod);
        $this->assertEquals(array('success' => 'true', 'type' => PrepaidCard::TYPE_PREPAID_CARD), $newTransferMethod->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), $transferMethod, array(), array(
            'Json-Cache-Token' => 'test-json-cache-token'
        ));
    }

    //--------------------------------------
    // Balances
    //--------------------------------------

    public function testListBalancesForUser_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBalancesForUser('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListBalancesForUser_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/balances', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $balanceList = $client->listBalancesForUser('test-user-token');
        $this->assertNotNull($balanceList);
        $this->assertCount(0, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/balances', array('user-token' => 'test-user-token'), array());
    }

    public function testListBalancesForUser_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/balances', array('user-token' => 'test-user-token'), array('currency' => 'USD'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listBalancesForUser('test-user-token', array('currency' => 'USD'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/balances', array('user-token' => 'test-user-token'), array('currency' => 'USD'));
    }

    public function testListBalancesForUser_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listBalancesForUser('test-user-token',$options=array('currency' => 'USD', 'status'=> 'COMPLETED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testListBalancesForPrepaidCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBalancesForPrepaidCard('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListBalancesForPrepaidCard_noPrepaidCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBalancesForPrepaidCard('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('prepaidCardToken is required!', $e->getMessage());
        }
    }

    public function testListBalancesForPrepaidCard_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/balances', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $balanceList = $client->listBalancesForPrepaidCard('test-user-token', 'test-prepaid-card-token');
        $this->assertNotNull($balanceList);
        $this->assertCount(0, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/balances', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array());
    }

    public function testListBalancesForPrepaidCard_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/balances', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('createdBefore' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listBalancesForPrepaidCard('test-user-token', 'test-prepaid-card-token', array('createdBefore' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/balances', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('createdBefore' => 'value'));
    }

    public function testListBalancesForPrepaidCard_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $client->listBalancesForPrepaidCard('test-user-token', 'test-prepaid-card-token', array('test' => 'value'), $options=array('createdBefore' => 'value', 'status'=> 'COMPLETED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testListBalancesForAccount_noProgramToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBalancesForAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('programToken is required!', $e->getMessage());
        }
    }

    public function testListBalancesForAccount_noAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listBalancesForAccount('test-program-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('accountToken is required!', $e->getMessage());
        }
    }

    public function testListBalancesForAccount_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/balances', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $balanceList = $client->listBalancesForAccount('test-program-token', 'test-account-token');
        $this->assertNotNull($balanceList);
        $this->assertCount(0, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/balances', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array());
    }

    public function testListBalancesForAccount_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/balances', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('currency' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listBalancesForAccount('test-program-token', 'test-account-token', array('currency' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/balances', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('currency' => 'value'));
    }

    public function testListBalancesForAccount_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $client->listBalancesForAccount('test-user-token', 'test-prepaid-card-token', array('test' => 'value'), $options=array('currency' => 'value', 'status'=> 'COMPLETED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Payments
    //--------------------------------------

    public function testCreatePayment_withoutProgramToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $user = new Payment();

        \Phake::when($apiClientMock)->doPost('/rest/v3/payments', array(), $user, array())->thenReturn(array('success' => 'true'));

        // Run test
        $this->assertNull($user->getProgramToken());

        $newPayment = $client->createPayment($user);
        $this->assertNotNull($newPayment);
        $this->assertNull($user->getProgramToken());
        $this->assertEquals(array('success' => 'true'), $newPayment->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/payments', array(), $user, array());
    }

    public function testCreatePayment_withProgramTokenAddedByDefault() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $user = new Payment();

        \Phake::when($apiClientMock)->doPost('/rest/v3/payments', array(), $user, array())->thenReturn(array('success' => 'true'));

        // Run test
        $this->assertNull($user->getProgramToken());

        $newPayment = $client->createPayment($user);
        $this->assertNotNull($newPayment);
        $this->assertEquals('test-program-token', $user->getProgramToken());
        $this->assertEquals(array('success' => 'true'), $newPayment->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/payments', array(), $user, array());
    }

    public function testCreatePayment_withProgramTokenInPaymentObject() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $user = new Payment(array('programToken' => 'test-program-token2'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/payments', array(), $user, array())->thenReturn(array('success' => 'true'));

        // Run test
        $this->assertEquals('test-program-token2', $user->getProgramToken());

        $newPayment = $client->createPayment($user);
        $this->assertNotNull($newPayment);
        $this->assertEquals('test-program-token2', $user->getProgramToken());
        $this->assertEquals(array('success' => 'true'), $newPayment->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/payments', array(), $user, array());
    }

    public function testGetPayment_noPaymentToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getPayment('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paymentToken is required!', $e->getMessage());
        }
    }

    public function testGetPayment_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments/{payment-token}', array('payment-token' => 'test-payment-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $user = $client->getPayment('test-payment-token');
        $this->assertNotNull($user);
        $this->assertEquals(array('success' => 'true'), $user->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments/{payment-token}', array('payment-token' => 'test-payment-token'), array());
    }

    public function testListPayments_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments', array(), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $userList = $client->listPayments();
        $this->assertNotNull($userList);
        $this->assertCount(0, $userList);
        $this->assertEquals(1, $userList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments', array(), array());
    }

    public function testListPayments_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments', array(), array('clientPaymentId' => 'testClient-PaymentId'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $userList = $client->listPayments(array('clientPaymentId' => 'testClient-PaymentId'));
        $this->assertNotNull($userList);
        $this->assertCount(1, $userList);
        $this->assertEquals(1, $userList->getCount());

        $this->assertEquals(array('success' => 'true'), $userList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments', array(), array('clientPaymentId' => 'testClient-PaymentId'));
    }

    public function testCreatePaymentStatusTransition_noPaymentToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new PaymentStatusTransition();

        try {
            $client->createPaymentStatusTransition('', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paymentToken is required!', $e->getMessage());
        }
    }

    public function testListPayments_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listPayments($options=array('clientPaymentId' => 'testClient-PaymentId', 'status'=> 'COMPLETED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testCreatePaymentStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new PaymentStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->createPaymentStatusTransition('test-payment-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), $statusTransition, array());
    }

    public function testGetPaymentStatusTransition_noPaymentToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPaymentStatusTransition('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paymentToken is required!', $e->getMessage());
        }
    }

    public function testGetPaymentStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getPaymentStatusTransition('test-payment-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetPaymentStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions/{status-transition-token}', array('payment-token' => 'test-payment-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getPaymentStatusTransition('test-payment-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions/{status-transition-token}', array('payment-token' => 'test-payment-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListPaymentStatusTransitions_noPaymentToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listPaymentStatusTransitions('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('paymentToken is required!', $e->getMessage());
        }
    }

    public function testListPaymentStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listPaymentStatusTransitions('test-payment-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), array());
    }

    public function testListPaymentStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), array('transition' => PaymentStatusTransition::TRANSITION_CANCELLED))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listPaymentStatusTransitions('test-payment-token', array('transition' => PaymentStatusTransition::TRANSITION_CANCELLED));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), array('transition' => PaymentStatusTransition::TRANSITION_CANCELLED));
    }

    public function testListPaymentStatusTransitions_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listPaymentStatusTransitions( 'test-payment-token', array('transition' => PaymentStatusTransition::TRANSITION_CANCELLED, 'status' => 'ACTIVATED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Programs
    //--------------------------------------

    public function testGetProgram_noProgramToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getProgram('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('programToken is required!', $e->getMessage());
        }
    }

    public function testGetProgram_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}', array('program-token' => 'test-program-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $program = $client->getProgram('test-program-token');
        $this->assertNotNull($program);
        $this->assertEquals(array('success' => 'true'), $program->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}', array('program-token' => 'test-program-token'), array());
    }

    //--------------------------------------
    // Program Accounts
    //--------------------------------------

    public function testGetProgramAccount_noProgramToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getProgramAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('programToken is required!', $e->getMessage());
        }
    }

    public function testGetProgramAccount_noAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getProgramAccount('test-program-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('accountToken is required!', $e->getMessage());
        }
    }

    public function testGetProgramAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $programAccount = $client->getProgramAccount('test-program-token', 'test-account-token');
        $this->assertNotNull($programAccount);
        $this->assertEquals(array('success' => 'true'), $programAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array());
    }

    //--------------------------------------
    // Transfer Method Configurations
    //--------------------------------------

    public function testGetTransferMethodConfiguration_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getTransferMethodConfiguration('', '', '', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetTransferMethodConfiguration_noCountry() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getTransferMethodConfiguration('test-user-token', '', '', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('country is required!', $e->getMessage());
        }
    }

    public function testGetTransferMethodConfiguration_noCurrency() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getTransferMethodConfiguration('test-user-token', 'US', '', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('currency is required!', $e->getMessage());
        }
    }

    public function testGetTransferMethodConfiguration_noType() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getTransferMethodConfiguration('test-user-token', 'US', 'USD', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('type is required!', $e->getMessage());
        }
    }

    public function testGetTransferMethodConfiguration_noProfileType() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getTransferMethodConfiguration('test-user-token', 'US', 'USD', 'BANK_ACCOUNT', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('profileType is required!', $e->getMessage());
        }
    }

    public function testGetTransferMethodConfiguration_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfer-method-configurations', array(), array(
            'userToken' => 'test-user-token',
            'country' => 'US',
            'currency' => 'USD',
            'type' => 'BANK_ACCOUNT',
            'profileType' => 'INDIVIDUAL'
        ))->thenReturn(array('success' => 'true'));

        // Run test
        $program = $client->getTransferMethodConfiguration('test-user-token', 'US', 'USD', 'BANK_ACCOUNT', 'INDIVIDUAL');
        $this->assertNotNull($program);
        $this->assertEquals(array('success' => 'true'), $program->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfer-method-configurations', array(), array(
            'userToken' => 'test-user-token',
            'country' => 'US',
            'currency' => 'USD',
            'type' => 'BANK_ACCOUNT',
            'profileType' => 'INDIVIDUAL'
        ));
    }

    public function testListTransferMethodConfigurations_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listTransferMethodConfigurations('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListTransferMethodConfigurations_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfer-method-configurations', array(), array('userToken' => 'test-user-token'))->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $userList = $client->listTransferMethodConfigurations('test-user-token');
        $this->assertNotNull($userList);
        $this->assertCount(0, $userList);
        $this->assertEquals(1, $userList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfer-method-configurations', array(), array('userToken' => 'test-user-token'));
    }

    public function testListTransferMethodConfigurations_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfer-method-configurations', array(), array(
            'userToken' => 'test-user-token',
            'limit' => 'test-limit'
        ))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $tmcList = $client->listTransferMethodConfigurations('test-user-token', array('limit' => 'test-limit'));
        $this->assertNotNull($tmcList);
        $this->assertCount(1, $tmcList);
        $this->assertEquals(1, $tmcList->getCount());

        $this->assertEquals(array('success' => 'true'), $tmcList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfer-method-configurations', array(), array(
            'userToken' => 'test-user-token',
            'limit' => 'test-limit'
        ));
    }

    public function testListTransferMethodConfigurations_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listTransferMethodConfigurations('test-user-token', array('limit' => 'test-limit', 'test' => 'value'));

            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Receipts
    //--------------------------------------

    public function testListReceiptsForProgramAccount_noProgramToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listReceiptsForProgramAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('programToken is required!', $e->getMessage());
        }
    }

    public function testListReceiptsForProgramAccount_noAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listReceiptsForProgramAccount('test-program-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('accountToken is required!', $e->getMessage());
        }
    }

    public function testListReceiptsForProgramAccount_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/receipts', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $balanceList = $client->listReceiptsForProgramAccount('test-program-token', 'test-account-token');
        $this->assertNotNull($balanceList);
        $this->assertCount(0, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/receipts', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array());
    }

    public function testListReceiptsForProgramAccount_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/receipts', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('currency' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listReceiptsForProgramAccount('test-program-token', 'test-account-token', array('currency' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/receipts', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('currency' => 'value'));
    }

    public function testListReceiptsForProgramAccount_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $client->listReceiptsForProgramAccount('test-program-token', 'test-account-token', array('currency' => 'value', 'test' => 'value'));

            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testListReceiptsForUser_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listReceiptsForUser('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListReceiptsForUser_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/receipts', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $balanceList = $client->listReceiptsForUser('test-user-token');
        $this->assertNotNull($balanceList);
        $this->assertCount(0, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/receipts', array('user-token' => 'test-user-token'), array());
    }

    public function testListReceiptsForUser_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/receipts', array('user-token' => 'test-user-token'), array('currency' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listReceiptsForUser('test-user-token', array('currency' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/receipts', array('user-token' => 'test-user-token'), array('currency' => 'value'));
    }

    public function testListReceiptsForUser_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $balanceList = $client->listReceiptsForUser('test-user-token', array('currency' => 'value', 'test' => 'value'));

            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testListReceiptsForPrepaidCard_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listReceiptsForPrepaidCard('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListReceiptsForPrepaidCard_noPrepaidCardToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listReceiptsForPrepaidCard('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('prepaidCardToken is required!', $e->getMessage());
        }
    }

    public function testListReceiptsForPrepaidCard_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/receipts', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $balanceList = $client->listReceiptsForPrepaidCard('test-user-token', 'test-prepaid-card-token');
        $this->assertNotNull($balanceList);
        $this->assertCount(0, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/receipts', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array());
    }

    public function testListReceiptsForPrepaidCard_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/receipts', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('createdBefore' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listReceiptsForPrepaidCard('test-user-token', 'test-prepaid-card-token', array('createdBefore' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/receipts', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('createdBefore' => 'value'));
    }

    public function testListReceiptsForPrepaidCard_withInvalidFilter() {
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        try {
            $balanceList = $client->listReceiptsForPrepaidCard('test-user-token', 'test-prepaid-card-token', array('test' => 'value'));

            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    //--------------------------------------
    // Webhook Notifications
    //--------------------------------------

    public function testGetWebhookNotification_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getWebhookNotification('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('webhookNotificationToken is required!', $e->getMessage());
        }
    }

    public function testGetWebhookNotification_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/webhook-notifications/{webhook-notification-token}', array('webhook-notification-token' => 'test-webhook-notification-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $webhookNotification = $client->getWebhookNotification('test-webhook-notification-token');
        $this->assertNotNull($webhookNotification);
        $this->assertEquals(array('success' => 'true'), $webhookNotification->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/webhook-notifications/{webhook-notification-token}', array('webhook-notification-token' => 'test-webhook-notification-token'), array());
    }

    public function testListWebhookNotifications_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/webhook-notifications', array(), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $webhookNotificationList = $client->listWebhookNotifications();
        $this->assertNotNull($webhookNotificationList);
        $this->assertCount(0, $webhookNotificationList);
        $this->assertEquals(1, $webhookNotificationList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/webhook-notifications', array(), array());
    }

    public function testListWebhookNotifications_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/webhook-notifications', array(), array('programToken' => 'test-program-token',
            'createdBefore' => 'test-created-before','createdAfter'=>'test-created-After', 'type'=>'test-type', 'sortBy'=>'test-sortBy',
            'offset'=>'test-offset', 'limit'=>'test-limit'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $webhookNotificationList = $client->listWebhookNotifications(array('programToken' => 'test-program-token',
            'createdBefore' => 'test-created-before','createdAfter'=>'test-created-After', 'type'=>'test-type', 'sortBy'=>'test-sortBy',
            'offset'=>'test-offset', 'limit'=>'test-limit'));
        $this->assertNotNull($webhookNotificationList);
        $this->assertCount(1, $webhookNotificationList);
        $this->assertEquals(1, $webhookNotificationList->getCount());

        $this->assertEquals(array('success' => 'true'), $webhookNotificationList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/webhook-notifications', array(), array('programToken' => 'test-program-token',
            'createdBefore' => 'test-created-before','createdAfter'=>'test-created-After', 'type'=>'test-type', 'sortBy'=>'test-sortBy',
            'offset'=>'test-offset', 'limit'=>'test-limit'));
    }

    public function testListWebhookNotifications_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->listWebhookNotifications($options=array('programToken' => 'test-program-token', 'status'=> 'COMPLETED'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }


    //--------------------------------------
    // Response with error
    //--------------------------------------

    public function testCreateBankAccountWithErrorResponse() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $bankAccount = new BankAccount();

        $errorResponse = new ErrorResponse(0, array('errors' => array(array(
            'message' => 'The information you provided is already registered with this user',
            'code' => 'DUPLICATE_EXTERNAL_ACCOUNT_CREATION',
            'relatedResources' => array(
                'trm-f3d38df1-adb7-4127-9858-e72ebe682a79', 'trm-601b1401-4464-4f3f-97b3-09079ee7723b'
            )))));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), $bankAccount, array())->thenThrow(new HyperwalletApiException($errorResponse, new HyperwalletException("Error message")));

        // Run test
        try {
            $newBankAccount = $client->createBankAccount('test-user-token', $bankAccount);
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('The information you provided is already registered with this user', $e->getMessage());
            $this->assertEquals('DUPLICATE_EXTERNAL_ACCOUNT_CREATION', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertCount(2, $e->getRelatedResources());
            $this->assertEquals('trm-f3d38df1-adb7-4127-9858-e72ebe682a79', $e->getRelatedResources()[0]);
            $this->assertEquals('trm-601b1401-4464-4f3f-97b3-09079ee7723b', $e->getRelatedResources()[1]);
        }

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), $bankAccount, array());
    }

    //--------------------------------------
    // Internal utils
    //--------------------------------------

    private function findMethodByName(Hyperwallet $client, $methodName) {
        $clientClazz = new \ReflectionObject($client);
        return $clientClazz->getMethod($methodName);
    }

    private function validateGuzzleClientSettings(Hyperwallet $client, $server, $username, $password) {
        $clientClazz = new \ReflectionObject($client);
        $apiClientProperty = $clientClazz->getProperty('client');

        $apiClientProperty->setAccessible(true);
        $apiClient = $apiClientProperty->getValue($client);

        $apiClientClazz = new \ReflectionObject($apiClient);
        $guzzleClientProperty = $apiClientClazz->getProperty('client');

        $guzzleClientProperty->setAccessible(true);
        /** @var Client $guzzleClient */
        $guzzleClient = $guzzleClientProperty->getValue($apiClient);

        $this->assertEquals(new Uri($server), $guzzleClient->getConfig('base_uri'));
        $this->assertEquals(array($username, $password), $guzzleClient->getConfig('auth'));
    }

    private function createAndInjectApiClientMock(Hyperwallet $client) {
        /** @var ApiClient $apiClientMock */
        $apiClientMock = \Phake::mock('Hyperwallet\Util\ApiClient');

        $clientClazz = new \ReflectionObject($client);
        $apiClientProperty = $clientClazz->getProperty('client');

        $apiClientProperty->setAccessible(true);
        $apiClientProperty->setValue($client, $apiClientMock);

        return $apiClientMock;
    }

    //--------------------------------------
    // Data provider
    //--------------------------------------

    public function prepaidCardStatusTransitionProvider() {
        return array(
            'suspendPrepaidCard' => array('suspendPrepaidCard', PrepaidCardStatusTransition::TRANSITION_SUSPENDED),
            'unsuspendPrepaidCard' => array('unsuspendPrepaidCard', PrepaidCardStatusTransition::TRANSITION_UNSUSPENDED),
            'lostOrStolenPrepaidCard' => array('lostOrStolenPrepaidCard', PrepaidCardStatusTransition::TRANSITION_LOST_OR_STOLEN),
            'deactivatePrepaidCard' => array('deactivatePrepaidCard', PrepaidCardStatusTransition::TRANSITION_DE_ACTIVATED),
            'lockPrepaidCard' => array('lockPrepaidCard', PrepaidCardStatusTransition::TRANSITION_LOCKED),
            'unlockPrepaidCard' => array('unlockPrepaidCard', PrepaidCardStatusTransition::TRANSITION_UNLOCKED)
        );
    }

    public function testUpdateVerificationStatus_allParameters() {

        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $user = new user(array('verificationStatus'=> User::VERIFICATION_STATUS_REQUESTED));
        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}', array('user-token' => 'test-user-token'), $user, array())->thenReturn(array("status"=> User::STATUS_PRE_ACTIVATED, 'verificationStatus'=> User::VERIFICATION_STATUS_REQUIRED));
        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}', array('user-token' => 'test-user-token'), array())->thenReturn(array("status"=> User::STATUS_PRE_ACTIVATED, 'verificationStatus'=> User::VERIFICATION_STATUS_REQUIRED));

        // Run test
        try {
            $responseUser = $client->updateVerificationStatus('test-user-token', User::VERIFICATION_STATUS_REQUESTED);
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
        $this->assertNotNull($responseUser);
        $this->assertEquals('REQUIRED', $responseUser->getVerificationStatus());
        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}', array('user-token' => 'test-user-token'), $user, array());
    }


    public function testUpdateVerificationStatus_withNullVerificationStatus() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        // Run test
        try {
            $responseUser = $client->updateVerificationStatus('test-user-token', null);
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals("verificationStatus is required!", $e->getMessage());
        }
    }

    //--------------------------------------
    // Document upload for users
    //--------------------------------------

    public static function UPLOAD_SUCCESS_DATA() {
        return array(
            'token' => 'tkn-12345',
            "documents" => array( array(
                "category" => "IDENTIFICATION",
                "type" => "DRIVERS_LICENSE",
                "country" => "AL",
                "status" => "NEW"
            ))
        );
    }

    public static function UPLOAD_REASON_DATA() {
        return array(
            'token' => 'tkn-12345',
            "documents" => array( array(
                "category" => "IDENTIFICATION",
                "type" => "DRIVERS_LICENSE",
                "country" => "AL",
                "status" => "INVALID",
                "reasons" => array(
                    array(
                        "name" => "DOCUMENT_CORRECTION_REQUIRED",
                        "description" => "Document requires correction"
                    ),
                    array(
                        "name" => "DOCUMENT_NOT_DECISIVE",
                        "description" => "Decision cannot be made based on document. Alternative document required"
                    )),
                "createdOn" => "2020-11-24T19:05:02"

            ))
        );
    }

    public static function UPLOAD_REASON_DATA_NO_REASON() {
        return array(
            'token' => 'tkn-12345',
            "documents" => array( array(
                "category" => "IDENTIFICATION",
                "type" => "PASSPORT",
                "country" => "ES",
                "status" => "NEW",
                "createdOn" => "2020-11-24T19:05:02"

            ))
        );
    }

    public static function UPLOAD_REASON_DATA_NO_DOC() {
        return array(
            'token' => 'tkn-12345',
        );
    }

    public function testuploadDocumentsForUser_withoutUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->uploadDocumentsForUser('', null);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testuploadDocumentsForUser() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $userToken = "user-token";

        $options = array(
            'multipart' => [
                [
                    'name'     => 'data',
                    'contents' => '{"documents":[{"type":"DRIVERS_LICENSE","country":"AL","category":"IDENTIFICATION"}]}'
                ],
                [
                    'name'     => 'drivers_license_front',
                    'contents' => fopen(__DIR__ . "/../../resources/license-front.png", "r")
                ],
                [
                    'name'     => 'drivers_license_back',
                    'contents' => fopen(__DIR__ . "/../../resources/license-back.png", 'r')
                ]
            ]
        );

        \Phake::when($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options)->thenReturn(array('success' => 'true'));

        // Run test
        $newUser = $client->uploadDocumentsForUser($userToken, $options);
        $this->assertNotNull($newUser);
        $this->assertNull($newUser->getProgramToken());
        $this->assertEquals(array('success' => 'true'), $newUser->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options);
    }

    public function testuploadDocumentsForUser_parseDocuments() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $userToken = "user-token";

        $options = array(
            'multipart' => [
                [
                    'name'     => 'data',
                    'contents' => '{"documents":[{"type":"DRIVERS_LICENSE","country":"AL","category":"IDENTIFICATION"}]}'
                ],
                [
                    'name'     => 'drivers_license_front',
                    'contents' => fopen(__DIR__ . "/../../resources/license-front.png", "r")
                ],
                [
                    'name'     => 'drivers_license_back',
                    'contents' => fopen(__DIR__ . "/../../resources/license-back.png", 'r')
                ]
            ]
        );
        \Phake::when($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options)->thenReturn($this->UPLOAD_SUCCESS_DATA());

        // Run test
        $newUser = $client->uploadDocumentsForUser($userToken, $options);

        $this->assertNotNull($newUser);
        $this->assertNull($newUser->getProgramToken());
        $this->assertEquals($this->UPLOAD_SUCCESS_DATA()["documents"][0]["type"], $newUser->documents->documents[0]->type);


        // Validate mock
        \Phake::verify($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options);
    }

    public function testuploadDocumentsForUser_parseReasons() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $userToken = "user-token";

        $options = array(
            'multipart' => [
                [
                    'name'     => 'data',
                    'contents' => '{"documents":[{"type":"DRIVERS_LICENSE","country":"AL","category":"IDENTIFICATION"}]}'
                ],
                [
                    'name'     => 'drivers_license_front',
                    'contents' => fopen(__DIR__ . "/../../resources/license-front.png", "r")
                ],
                [
                    'name'     => 'drivers_license_back',
                    'contents' => fopen(__DIR__ . "/../../resources/license-back.png", 'r')
                ]
            ]
        );

        \Phake::when($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options)->thenReturn($this->UPLOAD_REASON_DATA());

        // Run test
        $newUser = $client->uploadDocumentsForUser($userToken, $options);
        $this->assertNotNull($newUser);
        $this->assertNull($newUser->getProgramToken());
        $this->assertEquals($this->UPLOAD_REASON_DATA()["documents"][0]["type"], $newUser->documents->documents[0]->type);
        $this->assertEquals($this->UPLOAD_REASON_DATA()["documents"][0]["reasons"][0]["name"], $newUser->documents->documents[0]->reasons->reasons[0]->name);
        // Validate mock
        \Phake::verify($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options);
    }

    public function testuploadDocumentsForUser_parseNoReasons() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $userToken = "user-token";

        $options = array(
            'multipart' => [
                [
                    'name'     => 'data',
                    'contents' => '{"documents":[{"type":"DRIVERS_LICENSE","country":"AL","category":"IDENTIFICATION"}]}'
                ],
                [
                    'name'     => 'drivers_license_front',
                    'contents' => fopen(__DIR__ . "/../../resources/license-front.png", "r")
                ],
                [
                    'name'     => 'drivers_license_back',
                    'contents' => fopen(__DIR__ . "/../../resources/license-back.png", 'r')
                ]
            ]
        );

        \Phake::when($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options)->thenReturn($this->UPLOAD_REASON_DATA_NO_REASON());
        // Run test
        $newUser = $client->uploadDocumentsForUser($userToken, $options);
        $this->assertNotNull($newUser);
        $this->assertNull($newUser->getProgramToken());
        $this->assertEquals($this->UPLOAD_REASON_DATA_NO_REASON()["documents"][0]["type"], $newUser->documents->documents[0]->type);

        // Validate mock
        \Phake::verify($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options);
    }

    public function testuploadDocumentsForUser_parseNoDocument() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $userToken = "user-token";

        $options = array(
            'multipart' => [
                [
                    'name'     => 'data',
                    'contents' => '{"documents":[{"type":"DRIVERS_LICENSE","country":"AL","category":"IDENTIFICATION"}]}'
                ],
                [
                    'name'     => 'drivers_license_front',
                    'contents' => fopen(__DIR__ . "/../../resources/license-front.png", "r")
                ],
                [
                    'name'     => 'drivers_license_back',
                    'contents' => fopen(__DIR__ . "/../../resources/license-back.png", 'r')
                ]
            ]
        );

        \Phake::when($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options)->thenReturn($this->UPLOAD_REASON_DATA_NO_DOC());

        // Run test
        $newUser = $client->uploadDocumentsForUser($userToken, $options);
        $this->assertNotNull($newUser);
        $this->assertNull($newUser->getProgramToken());
        $this->assertEquals($this->UPLOAD_REASON_DATA_NO_DOC()["token"], $newUser->token);

        // Validate mock
        \Phake::verify($apiClientMock)->putMultipartData('/rest/v3/users/{user-token}', array('user-token' => $userToken), $options);
    }


    //--------------------------------------
    // Venmo Accounts
    //--------------------------------------

    public function testCreateVenmoAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $venmoAccount = new VenmoAccount();

        try {
            $client->createVenmoAccount('', $venmoAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testCreateVenmoAccount_noTransferMethodCountry() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $venmoAccount = new VenmoAccount();

        try {
            $client->createVenmoAccount('test-user-token', $venmoAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferMethodCountry is required!', $e->getMessage());
        }
    }

    public function testCreateVenmoAccount_noTransferMethodCurrency() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $venmoAccount = new VenmoAccount();
        $venmoAccount->setTransferMethodCountry('test-transferMethodCountry');

        try {
            $client->createVenmoAccount('test-user-token', $venmoAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferMethodCurrency is required!', $e->getMessage());
        }
    }

    public function testCreateVenmoAccount_noAccountId() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $venmoAccount = new VenmoAccount();
        $venmoAccount->setTransferMethodCountry('test-transferMethodCountry');
        $venmoAccount->setTransferMethodCurrency('test-transferMethodCurrency');

        try {
            $client->createVenmoAccount('test-user-token', $venmoAccount);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Venmo account is required!', $e->getMessage());
        }
    }

    public function testCreateVenmoAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $venmoAccount = new VenmoAccount();
        $venmoAccount->setTransferMethodCountry('test-transferMethodCountry');
        $venmoAccount->setTransferMethodCurrency('test-transferMethodCurrency');
        $venmoAccount->setAccountId('account-id');

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/venmo-accounts', array('user-token' => 'test-user-token'), $venmoAccount, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $newVenmoAccount = $client->createVenmoAccount('test-user-token', $venmoAccount);
        $this->assertNotNull($newVenmoAccount);
        $this->assertEquals(array('token' => 'test-token'), $newVenmoAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/venmo-accounts', array('user-token' => 'test-user-token'), $venmoAccount, array());
    }

    public function testGetVenmoAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getVenmoAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetVenmoAccount_noVenmoAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->getVenmoAccount('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('venmoAccountToken is required!', $e->getMessage());
        }
    }

    public function testGetVenmoAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $venmoAccount = $client->getVenmoAccount('test-user-token', 'test-venmo-account-token');
        $this->assertNotNull($venmoAccount);
        $this->assertEquals(array('token' => 'test-token'), $venmoAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), array());
    }

    public function testUpdateVenmoAccount_noVenmoAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        try {
            $client->updateVenmoAccount('test-user-token', new VenmoAccount());
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transfer method token is required!', $e->getMessage());
        }
    }

    public function testUpdateVenmoAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $venmoAcc = new VenmoAccount(array('token' => 'test-venmo-account-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-venmo-account-token', 'transfer-method-name' => 'venmo-accounts'), $venmoAcc, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $venmoAccount = $client->updateVenmoAccount('test-user-token', $venmoAcc);
        $this->assertNotNull($venmoAccount);
        $this->assertEquals(array('token' => 'test-token'), $venmoAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/{transfer-method-name}/{transfer-method-token}', array('user-token' => 'test-user-token', 'transfer-method-token' => 'test-venmo-account-token', 'transfer-method-name' => 'venmo-accounts'), $venmoAcc, array());
    }

    public function testListVenmoAccounts_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $client->listVenmoAccounts('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListVenmoAccounts_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $venmoAccountsList = $client->listVenmoAccounts('test-user-token');
        $this->assertNotNull($venmoAccountsList);
        $this->assertCount(0, $venmoAccountsList);
        $this->assertEquals(1, $venmoAccountsList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts', array('user-token' => 'test-user-token'), array());
    }

    public function testListVenmoAccounts_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts', array('user-token' => 'test-user-token'), array('status' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('token' => 'test-token'))));

        // Run test
        $venmoAccountsList = $client->listVenmoAccounts('test-user-token', array('status' => 'value'));
        $this->assertNotNull($venmoAccountsList);
        $this->assertCount(1, $venmoAccountsList);
        $this->assertEquals(1, $venmoAccountsList->getCount());

        $this->assertEquals(array('token' => 'test-token'), $venmoAccountsList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts', array('user-token' => 'test-user-token'), array('status' => 'value'));
    }

    public function testListVenmoAccounts_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $venmoAccountsList = $client->listVenmoAccounts('test-user-token', array('status' => 'value', 'test' => 'value'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testDeactivateVenmoAccount_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivateVenmoAccount('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testDeactivateVenmoAccount_noVenmoAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        // Run test
        try {
            $client->deactivateVenmoAccount('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('venmoAccountToken is required!', $e->getMessage());
        }
    }

    public function testDeactivateVenmoAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        $statusTransition = new VenmoAccountStatusTransition();
        $statusTransition->setTransition(VenmoAccountStatusTransition::TRANSITION_DE_ACTIVATED);

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->deactivateVenmoAccount('test-user-token', 'test-venmo-account-token');
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), $statusTransition, array());
    }

    public function testCreateVenmoAccountStatusTransition_noVenmoAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $statusTransition = new VenmoAccountStatusTransition();

        try {
            $client->createVenmoAccountStatusTransition('test-user-token', '', $statusTransition);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('venmoAccountToken is required!', $e->getMessage());
        }
    }

    public function testCreateVenmoAccountStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $statusTransition = new VenmoAccountStatusTransition(array('transition' => 'test'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), $statusTransition, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newStatusTransition = $client->createVenmoAccountStatusTransition('test-user-token', 'test-venmo-account-token', $statusTransition);
        $this->assertNotNull($newStatusTransition);
        $this->assertEquals(array('success' => 'true'), $newStatusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), $statusTransition, array());
    }

    public function testGetVenmoAccountStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getVenmoAccountStatusTransition('', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testGetVenmoAccountStatusTransition_noVenmoAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getVenmoAccountStatusTransition('test-user-token', '', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('venmoAccountToken is required!', $e->getMessage());
        }
    }

    public function testGetVenmoAccountStatusTransition_noStatusTransitionToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getVenmoAccountStatusTransition('test-user-token', 'test-venmo-account-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('statusTransitionToken is required!', $e->getMessage());
        }
    }

    public function testGetVenmoAccountStatusTransition_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token', 'status-transition-token' => 'test-status-transition-token'), array())->thenReturn(array('success' => 'true'));

        // Run test
        $statusTransition = $client->getVenmoAccountStatusTransition('test-user-token', 'test-venmo-account-token', 'test-status-transition-token');
        $this->assertNotNull($statusTransition);
        $this->assertEquals(array('success' => 'true'), $statusTransition->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions/{status-transition-token}', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token', 'status-transition-token' => 'test-status-transition-token'), array());
    }

    public function testListVenmoAccountStatusTransitions_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listVenmoAccountStatusTransitions('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListVenmoAccountStatusTransitions_noVenmoAccountToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listVenmoAccountStatusTransitions('test-user-token', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('venmoAccountToken is required!', $e->getMessage());
        }
    }

    public function testListVenmoAccountStatusTransitions_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $statusTransitionList = $client->listVenmoAccountStatusTransitions('test-user-token', 'test-venmo-account-token');
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(0, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), array());
    }

    public function testListVenmoAccountStatusTransitions_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), array('transition' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listVenmoAccountStatusTransitions('test-user-token', 'test-venmo-account-token', array('transition' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/venmo-accounts/{venmo-account-token}/status-transitions', array('user-token' => 'test-user-token', 'venmo-account-token' => 'test-venmo-account-token'), array('transition' => 'value'));
    }

    public function testListVenmoAccountStatusTransitions_withInvalidFilter() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');

        try {
            $venmoAccountsList = $client->listVenmoAccountStatusTransitions('test-user-token', 'test-venmo-account-token', array('transition' => 'value', 'test' => 'value'));
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('Invalid filter', $e->getMessage());
        }
    }

    public function testCreateTransferRefund_noClientRefundId() {

        $userName = "test-username";
        $password = "test-password";
        $sourceAmount = 20.0;
        $notes = "notes";
        $memo = "memo";

        $transferRefund = new TransferRefund();
        $transferRefund->setSourceAmount($sourceAmount);
        $transferRefund->setNotes($notes);
        $transferRefund->setMemo($memo);
        $transferToken = "transferToken";

        $client = new Hyperwallet($userName, $password);

        try {
            $client->createTransferRefund($transferToken, $transferRefund);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('clientRefundId is required!', $e->getMessage());
        }
    }

    public function testCreateTransferRefund_noTransferRefund() {

        $userName = "test-username";
        $password = "test-password";
        $transferToken = "transferToken";
        $client = new Hyperwallet($userName, $password);

        try {
            $client->createTransferRefund($transferToken, null);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferRefund is required!', $e->getMessage());
        }
    }


    public function testCreateTransferRefund_noTransferToken() {
        // Setup
        $userName = "test-username";
        $password = "test-password";
        $clientRefundId = "6712348070812";
        $sourceAmount = 20.0;
        $notes = "notes";
        $memo = "memo";

        $transferRefund = new TransferRefund();
        $transferRefund->setClientRefundId($clientRefundId);
        $transferRefund->setSourceAmount($sourceAmount);
        $transferRefund->setNotes($notes);
        $transferRefund->setMemo($memo);

        $client = new Hyperwallet($userName, $password);

        try {
            $client->createTransferRefund(null, $transferRefund);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferToken is required!', $e->getMessage());
        }
    }

    public function testCreateTransferRefund_successful() {
        // Setup
        $userName = "test-username";
        $password = "test-password";
        $clientRefundId = "test-client-refund-id";
        $sourceToken="test-source-token";
        $sourceAmount = 2000.00;
        $sourceFeeAmount= 2.00;
        $sourceCurrency='USD';
        $destinationToken='test-destination-token';
        $destinationAmount=1000.00;
        $destinationFeeAmount=2.00;
        $destinationCurrency='USD';
        $notes = "notes";
        $memo = "memo";
        $foreignExchange1 = array('sourceAmount' => '200.00', 'sourceCurrency' => 'USD', 'destinationCurrency' => 'CAD',
            'destinationAmount' => '100.00', 'rate' => '2.3');
        $expectedArray = array('token' => 'test-token',
            'clientRefundId' => "test-client-refund-id",
            'sourceToken'=>"test-source-token",
            'sourceAmount' => 2000.00,
            'sourceFeeAmount'=> 2.00,
            'sourceCurrency'=>'USD',
            'destinationToken'=>'test-destination-token',
            'destinationAmount'=>1000.00,
            'destinationFeeAmount'=>2.00,
            'destinationCurrency'=>'USD',
            'notes' => "notes",
            'memo' => "memo",
            'foreignExchanges' =>array($foreignExchange1));
        $foreignExchanges = array($foreignExchange1);
        $transferRefund = new TransferRefund();
        $transferRefund->setClientRefundId($clientRefundId);
        $transferRefund->setSourceToken($sourceToken);
        $transferRefund->setSourceAmount($sourceAmount);
        $transferRefund->setSourceFeeAmount($sourceFeeAmount);
        $transferRefund->setSourceCurrency($sourceCurrency);
        $transferRefund->setDestinationToken($destinationToken);
        $transferRefund->setSourceAmount($destinationAmount);
        $transferRefund->setDestinationAmount($destinationFeeAmount);
        $transferRefund->setDestinationCurrency($destinationCurrency);
        $transferRefund->setNotes($notes);
        $transferRefund->setMemo($memo);
        $transferRefund->setForeignExchanges($foreignExchanges);
        $transferToken = "transferToken";
        $client = new Hyperwallet($userName, $password);
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doPost('/rest/v3/transfers/{transfer-token}/refunds',
            array('transfer-token' => $transferToken), $transferRefund, array())->thenReturn($expectedArray);

        // Run test
        $newTransferRefund = $client->createTransferRefund($transferToken, $transferRefund);
        $this->assertNotNull($newTransferRefund);
        $this->assertEquals($expectedArray, $newTransferRefund->getProperties());
        $this->assertEquals($clientRefundId,$newTransferRefund->getClientRefundId());
        $this->assertEquals($sourceToken,$newTransferRefund->getSourceToken());
        $this->assertEquals($sourceAmount,$newTransferRefund->getSourceAmount());
        $this->assertEquals($sourceAmount,$newTransferRefund->getSourceAmount());
        $this->assertEquals($sourceCurrency,$newTransferRefund->getSourceCurrency());
        $this->assertEquals($destinationToken,$newTransferRefund->getDestinationToken());
        $this->assertEquals($destinationAmount,$newTransferRefund->getDestinationAmount());
        $this->assertEquals($destinationFeeAmount,$newTransferRefund->getDestinationFeeAmount());
        $this->assertEquals($destinationCurrency,$newTransferRefund->getDestinationCurrency());
        $this->assertEquals($notes,$newTransferRefund->getNotes());
        $this->assertEquals($memo,$newTransferRefund->getMemo() );
        $foreignExchanges = $newTransferRefund->getForeignExchanges();
        $foreignExchange1 = $foreignExchanges[0];
        $this->assertEquals("200.00",$foreignExchange1['sourceAmount']);
        $this->assertEquals("USD",$foreignExchange1['sourceCurrency'] );
        $this->assertEquals("100.00",$foreignExchange1['destinationAmount'] );
        $this->assertEquals("CAD",$foreignExchange1['destinationCurrency']);
        $this->assertEquals("2.3", $foreignExchange1['rate']);

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/transfers/{transfer-token}/refunds',
            array('transfer-token' => $transferToken), $transferRefund, array());
    }

    public function testGetTransferRefund_noTransferToken() {
        $userName = "test-username";
        $password = "test-password";
        $client = new Hyperwallet($userName, $password);

        try {
            $client->getTransferRefund(null, "transferRefundToken");
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferToken is required!', $e->getMessage());
        }
    }

    public function testGetTransferRefund_noTransferRefundToken() {
        $userName = "test-username";
        $password = "test-password";
        $transferToken = "test-transfer-token";
        $client = new Hyperwallet($userName, $password);
        try {
            $client->getTransferRefund($transferToken, null);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('refundToken is required!', $e->getMessage());
        }
    }

    public function testGetTransferRefund_successful() {
        $userName = "test-username";
        $password = "test-password";
        $client = new Hyperwallet($userName, $password);
        $transferToken = "test-transfer-token";
        $refundToken = "test-refund-token";
        $uriParams = array('transfer-token' => $transferToken, 'refund-token' => $refundToken);
        $queryParams = array();

        $apiClientMock = $this->createAndInjectApiClientMock($client);
        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds/{refund-token}',
            $uriParams, $queryParams)->thenReturn(array('token' => $refundToken));

        // Run test
        try {
            $transferRefund = $client->getTransferRefund($transferToken, $refundToken);
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('You need to specify transfer token and refund Token!', $e->getMessage());
        }
        $this->assertNotNull($transferRefund);
        $this->assertEquals(array('token' => $refundToken), $transferRefund->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds/{refund-token}',
            $uriParams, $queryParams);
    }

    public function testListTransferRefunds_noParameters() {

        $userName = "test-username";
        $password = "test-password";
        $sourceCurrency = "CAD";
        $transferToken = "test-tranfer-token";
        $refundToken = "test-refund-token";
        $uriParams = array('transfer-token' => $transferToken);
        $queryParams = array();
        $client = new Hyperwallet($userName, $password);
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds',
            $uriParams, $queryParams)->thenReturn(array('count' => 1, 'data' => array(array('refundToken' => $refundToken, array('sourceCurrency' => $sourceCurrency)))));

        // Run test
        $transferRefundList = $client->listTransferRefunds($transferToken);
        $this->assertEquals(array('sourceCurrency' => $sourceCurrency), $transferRefundList[0]->getProperties()[0]);
        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds',
            $uriParams, $queryParams);
    }

    public function testListTransferRefunds_withAllParameters() {

        $userName = "test-username";
        $password = "test-password";
        $sourceCurrency = "CAD";
        $transferToken = "test-transfer-token";
        $refundToken = "test-refund-token";
        $uriParams = array('transfer-token' => $transferToken);
        $queryParams = array('clientRefundId' => "clientRefundId", 'sourceToken' => "sourceToken",
            'status' => "COMPLETED", 'sortBy' => "sortByField", 'limit' => "10",
            'createdAfter' => "2016-06-29T17:58:26Z", 'createdBefore' => "2016-06-29T17:58:26Z");
        $client = new Hyperwallet($userName, $password);
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds',
            $uriParams, $queryParams)->thenReturn(array('count' => 1, 'data' => array(array('refundToken' => $refundToken, array('sourceCurrency' => $sourceCurrency)))));

        // Run test
        $transferRefundList = $client->listTransferRefunds($transferToken, $queryParams);
        $this->assertEquals(array('sourceCurrency' => $sourceCurrency), $transferRefundList[0]->getProperties()[0]);
        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds', $uriParams, $queryParams);
    }

    public function testListTransferRefund_noTransferToken() {
        $userName = "test-username";
        $password = "test-password";
        $client = new Hyperwallet($userName, $password);

        try {
            $client->listTransferRefunds(null);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('transferToken is required!', $e->getMessage());
        }
    }

    //--------------------------------------
    // List Transfer Methods
    //--------------------------------------

    public function testListTransferMethods_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->listTransferMethods('');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
    }

    public function testListTransferMethods_noParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), array())->thenReturn(array('count' => 1, 'data' => array()));

        // Run test
        $listTransferMethods = $client->listTransferMethods('test-user-token');
        $this->assertNotNull($listTransferMethods);
        $this->assertCount(0, $listTransferMethods);
        $this->assertEquals(1, $listTransferMethods->getCount());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), array());
    }

    public function testListTransferMethods_withParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), array('type'=>TransferMethod::TYPE_PREPAID_CARD))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $listTransferMethods = $client->listTransferMethods('test-user-token', array('type'=>TransferMethod::TYPE_PREPAID_CARD));
        $this->assertNotNull($listTransferMethods);
        $this->assertCount(1, $listTransferMethods);
        $this->assertEquals(1, $listTransferMethods->getCount());
        $this->assertEquals(array('success' => 'true'), $listTransferMethods[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/transfer-methods', array('user-token' => 'test-user-token'), array('type'=>TransferMethod::TYPE_PREPAID_CARD));
    }
}
