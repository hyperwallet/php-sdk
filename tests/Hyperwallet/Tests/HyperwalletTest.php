<?php
namespace Hyperwallet\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Exception\HyperwalletArgumentException;
use Hyperwallet\Hyperwallet;
use Hyperwallet\Model\BankAccount;
use Hyperwallet\Model\BankAccountStatusTransition;
use Hyperwallet\Model\Payment;
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
