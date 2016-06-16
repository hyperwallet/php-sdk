<?php
namespace Hyperwallet\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Hyperwallet\Exception\HyperwalletArgumentException;
use Hyperwallet\Hyperwallet;

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

}
