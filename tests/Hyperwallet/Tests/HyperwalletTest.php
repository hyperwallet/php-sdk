<?php
namespace Hyperwallet\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Exception\HyperwalletArgumentException;
use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Response\ErrorResponse;
use Hyperwallet\Hyperwallet;
use Hyperwallet\Model\BankAccount;
use Hyperwallet\Model\BankAccountStatusTransition;
use Hyperwallet\Model\BankCard;
use Hyperwallet\Model\BankCardStatusTransition;
use Hyperwallet\Model\PaperCheck;
use Hyperwallet\Model\PaperCheckStatusTransition;
use Hyperwallet\Model\Transfer;
use Hyperwallet\Model\TransferStatusTransition;
use Hyperwallet\Model\PayPalAccount;
use Hyperwallet\Model\Payment;
use Hyperwallet\Model\PaymentStatusTransition;
use Hyperwallet\Model\PrepaidCard;
use Hyperwallet\Model\PrepaidCardStatusTransition;
use Hyperwallet\Model\TransferMethod;
use Hyperwallet\Model\User;
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
        $this->validateGuzzleClientSettings($client, 'https://sandbox.hyperwallet.com', 'test-username', 'test-password');
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
        $user = new User(array('programToken' => 'test-program-token2'));

        \Phake::when($apiClientMock)->doPost('/rest/v3/users', array(), $user, array())->thenReturn(array('success' => 'true'));

        // Run test
        $this->assertEquals('test-program-token2', $user->getProgramToken());

        $newUser = $client->createUser($user);
        $this->assertNotNull($newUser);
        $this->assertEquals('test-program-token2', $user->getProgramToken());
        $this->assertEquals(array('success' => 'true'), $newUser->getProperties());

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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users', array(), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $userList = $client->listUsers(array('test' => 'value'));
        $this->assertNotNull($userList);
        $this->assertCount(1, $userList);
        $this->assertEquals(1, $userList->getCount());

        $this->assertEquals(array('success' => 'true'), $userList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users', array(), array('test' => 'value'));
    }

    public function testGetUserStatusTransition_noUserToken() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');

        // Run test
        try {
            $client->getUserStatusTransition('', '');
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('userToken is required!', $e->getMessage());
        }
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
            $client->listUserStatusTransitions( '');
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listUserStatusTransitions('test-user-token', array('test' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/status-transitions', array('user-token' => 'test-user-token'), array('test' => 'value'));
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
            $this->assertEquals('token is required!', $e->getMessage());
        }
    }

    public function testUpdatePaperCheck_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $paperCheck = new PaperCheck(array('token' => 'test-paper-check-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), $paperCheck, array())->thenReturn(array('postalCode' => 'ABCD'));

        // Run test
        $newPaperCheck = $client->updatePaperCheck('test-user-token', $paperCheck);
        $this->assertNotNull($newPaperCheck);
        $this->assertEquals(array('postalCode' => 'ABCD'), $newPaperCheck->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), $paperCheck, array());
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('postalCode' => 'ABCD'))));

        // Run test
        $paperCheckList = $client->listPaperChecks('test-user-token', array('test' => 'value'));
        $this->assertNotNull($paperCheckList);
        $this->assertCount(1, $paperCheckList);
        $this->assertEquals(1, $paperCheckList->getCount());

        $this->assertEquals(array('postalCode' => 'ABCD'), $paperCheckList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks', array('user-token' => 'test-user-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('postalCode' => 'ABCD'))));

        // Run test
        $statusTransitionList = $client->listPaperCheckStatusTransitions('test-user-token', 'test-paper-check-token', array('test' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('postalCode' => 'ABCD'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paper-checks/{paper-check-token}/status-transitions', array('user-token' => 'test-user-token', 'paper-check-token' => 'test-paper-check-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers', array(), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('token' => 'test-token'))));

        // Run test
        $transferList = $client->listTransfers(array('test' => 'value'));
        $this->assertNotNull($transferList);
        $this->assertCount(1, $transferList);
        $this->assertEquals(1, $transferList->getCount());

        $this->assertEquals(array('token' => 'test-token'), $transferList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers', array(), array('test' => 'value'));
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
            $this->assertEquals('payPalAccountToken is required!', $e->getMessage());
        }
    }

    public function testUpdatePayPalAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password', 'test-program-token');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $payPalAcc = new PayPalAccount(array('token' => 'test-paypal-account-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/paypal-accounts/{paypal-account-token}', array('user-token' => 'test-user-token', 'paypal-account-token' => 'test-paypal-account-token'), $payPalAcc, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $payPalAccount = $client->updatePayPalAccount('test-user-token', $payPalAcc);
        $this->assertNotNull($payPalAccount);
        $this->assertEquals(array('token' => 'test-token'), $payPalAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/paypal-accounts/{paypal-account-token}', array('user-token' => 'test-user-token', 'paypal-account-token' => 'test-paypal-account-token'), $payPalAcc, array());
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('token' => 'test-token'))));

        // Run test
        $payPalAccountsList = $client->listPayPalAccounts('test-user-token', array('test' => 'value'));
        $this->assertNotNull($payPalAccountsList);
        $this->assertCount(1, $payPalAccountsList);
        $this->assertEquals(1, $payPalAccountsList->getCount());

        $this->assertEquals(array('token' => 'test-token'), $payPalAccountsList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/paypal-accounts', array('user-token' => 'test-user-token'), array('test' => 'value'));
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
            $this->assertEquals('token is required!', $e->getMessage());
        }
    }

    public function testUpdatePrepaidCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $prepaidCard = new PrepaidCard(array('token' => 'test-prepaid-card-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), $prepaidCard, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newPrepaidCard = $client->updatePrepaidCard('test-user-token', $prepaidCard);
        $this->assertNotNull($newPrepaidCard);
        $this->assertEquals(array('success' => 'true'), $newPrepaidCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), $prepaidCard, array());
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $prepaidCardList = $client->listPrepaidCards('test-user-token', array('test' => 'value'));
        $this->assertNotNull($prepaidCardList);
        $this->assertCount(1, $prepaidCardList);
        $this->assertEquals(1, $prepaidCardList->getCount());

        $this->assertEquals(array('success' => 'true'), $prepaidCardList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => 'test-user-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listPrepaidCardStatusTransitions('test-user-token', 'test-prepaid-card-token', array('test' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('test' => 'value'));
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
            $this->assertEquals('token is required!', $e->getMessage());
        }
    }

    public function testUpdateBankAccount_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $bankAccount = new BankAccount(array('token' => 'test-bank-account-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), $bankAccount, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newBankAccount = $client->updateBankAccount('test-user-token', $bankAccount);
        $this->assertNotNull($newBankAccount);
        $this->assertEquals(array('success' => 'true'), $newBankAccount->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), $bankAccount, array());
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $bankAccountList = $client->listBankAccounts('test-user-token', array('test' => 'value'));
        $this->assertNotNull($bankAccountList);
        $this->assertCount(1, $bankAccountList);
        $this->assertEquals(1, $bankAccountList->getCount());

        $this->assertEquals(array('success' => 'true'), $bankAccountList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => 'test-user-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listBankAccountStatusTransitions('test-user-token', 'test-bank-account-token', array('test' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-account-token' => 'test-bank-account-token'), array('test' => 'value'));
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
            $this->assertEquals('token is required!', $e->getMessage());
        }
    }

    public function testUpdateBankCard_allParameters() {
        // Setup
        $client = new Hyperwallet('test-username', 'test-password');
        $apiClientMock = $this->createAndInjectApiClientMock($client);
        $bankCard = new BankCard(array('token' => 'test-bank-card-token'));

        \Phake::when($apiClientMock)->doPut('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), $bankCard, array())->thenReturn(array('success' => 'true'));

        // Run test
        $newBankCard = $client->updateBankCard('test-user-token', $bankCard);
        $this->assertNotNull($newBankCard);
        $this->assertEquals(array('success' => 'true'), $newBankCard->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doPut('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), $bankCard, array());
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $bankCardList = $client->listBankCards('test-user-token', array('test' => 'value'));
        $this->assertNotNull($bankCardList);
        $this->assertCount(1, $bankCardList);
        $this->assertEquals(1, $bankCardList->getCount());

        $this->assertEquals(array('success' => 'true'), $bankCardList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards', array('user-token' => 'test-user-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listBankCardStatusTransitions('test-user-token', 'test-bank-card-token', array('test' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/bank-cards/{bank-card-token}/status-transitions', array('user-token' => 'test-user-token', 'bank-card-token' => 'test-bank-card-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/balances', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listBalancesForUser('test-user-token', array('test' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/balances', array('user-token' => 'test-user-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/balances', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listBalancesForPrepaidCard('test-user-token', 'test-prepaid-card-token', array('test' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/balances', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/balances', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listBalancesForAccount('test-program-token', 'test-account-token', array('test' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/balances', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments', array(), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $userList = $client->listPayments(array('test' => 'value'));
        $this->assertNotNull($userList);
        $this->assertCount(1, $userList);
        $this->assertEquals(1, $userList->getCount());

        $this->assertEquals(array('success' => 'true'), $userList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments', array(), array('test' => 'value'));
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
            $client->listPaymentStatusTransitions( '');
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $statusTransitionList = $client->listPaymentStatusTransitions('test-payment-token', array('test' => 'value'));
        $this->assertNotNull($statusTransitionList);
        $this->assertCount(1, $statusTransitionList);
        $this->assertEquals(1, $statusTransitionList->getCount());

        $this->assertEquals(array('success' => 'true'), $statusTransitionList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/payments/{payment-token}/status-transitions', array('payment-token' => 'test-payment-token'), array('test' => 'value'));
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
            'test' => 'value'
        ))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $tmcList = $client->listTransferMethodConfigurations('test-user-token', array('test' => 'value'));
        $this->assertNotNull($tmcList);
        $this->assertCount(1, $tmcList);
        $this->assertEquals(1, $tmcList->getCount());

        $this->assertEquals(array('success' => 'true'), $tmcList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfer-method-configurations', array(), array(
            'userToken' => 'test-user-token',
            'test' => 'value'
        ));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/receipts', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listReceiptsForProgramAccount('test-program-token', 'test-account-token', array('test' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/receipts', array('program-token' => 'test-program-token', 'account-token' => 'test-account-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/receipts', array('user-token' => 'test-user-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listReceiptsForUser('test-user-token', array('test' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/receipts', array('user-token' => 'test-user-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/receipts', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $balanceList = $client->listReceiptsForPrepaidCard('test-user-token', 'test-prepaid-card-token', array('test' => 'value'));
        $this->assertNotNull($balanceList);
        $this->assertCount(1, $balanceList);
        $this->assertEquals(1, $balanceList->getCount());

        $this->assertEquals(array('success' => 'true'), $balanceList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/receipts', array('user-token' => 'test-user-token', 'prepaid-card-token' => 'test-prepaid-card-token'), array('test' => 'value'));
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

        \Phake::when($apiClientMock)->doGet('/rest/v3/webhook-notifications', array(), array('test' => 'value'))->thenReturn(array('count' => 1, 'data' => array(array('success' => 'true'))));

        // Run test
        $webhookNotificationList = $client->listWebhookNotifications(array('test' => 'value'));
        $this->assertNotNull($webhookNotificationList);
        $this->assertCount(1, $webhookNotificationList);
        $this->assertEquals(1, $webhookNotificationList->getCount());

        $this->assertEquals(array('success' => 'true'), $webhookNotificationList[0]->getProperties());

        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/webhook-notifications', array(), array('test' => 'value'));
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
}
