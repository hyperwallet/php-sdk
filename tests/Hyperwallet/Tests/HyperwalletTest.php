<?php
namespace Hyperwallet\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Hyperwallet\Exception\HyperwalletArgumentException;
use Hyperwallet\Hyperwallet;
use Hyperwallet\Model\PrepaidCard;
use Hyperwallet\Model\PrepaidCardStatusTransition;
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
