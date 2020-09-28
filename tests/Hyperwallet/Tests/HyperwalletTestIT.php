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
use Hyperwallet\Model\Transfer;
use Hyperwallet\Model\TransferRefund;
use Hyperwallet\Model\TransferStatusTransition;
use Hyperwallet\Model\PayPalAccount;
use Hyperwallet\Model\PayPalAccountStatusTransition;
use Hyperwallet\Model\Payment;
use Hyperwallet\Model\PaymentStatusTransition;
use Hyperwallet\Model\PrepaidCard;
use Hyperwallet\Model\PrepaidCardStatusTransition;
use Hyperwallet\Model\TransferMethod;
use Hyperwallet\Model\User;
use Hyperwallet\Model\VenmoAccount;
use Hyperwallet\Model\VenmoAccountStatusTransition;
use Hyperwallet\Response\ErrorResponse;
use Hyperwallet\Util\ApiClient;

class HyperwalletTestIT extends \PHPUnit_Framework_TestCase
{

    public function testConstructor_throwErrorIfUsernameIsEmpty()
    {
        try {
            new Hyperwallet('', 'test-password');
            $this->fail('Expect HyperwalletArgumentException');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('You need to specify your API username and password!', $e->getMessage());
        }
    }

    public function testConstructor_throwErrorIfPasswordIsEmpty()
    {
        try {
            new Hyperwallet('test-username', '');
            $this->fail('Expect HyperwalletArgumentException');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('You need to specify your API username and password!', $e->getMessage());
        }
    }

    public function testConstructor_defaultServer()
    {
        $client = new Hyperwallet('test-username', 'test-password');
        $this->validateGuzzleClientSettings($client, 'https://api.sandbox.hyperwallet.com', 'test-username', 'test-password');
    }

    public function testConstructor_changedServer()
    {
        $client = new Hyperwallet('test-username', 'test-password', null, 'https://test.test');
        $this->validateGuzzleClientSettings($client, 'https://test.test', 'test-username', 'test-password');
    }

    //--------------------------------------
    // TLS verification
    //--------------------------------------

    public function testListUser_noTLSIssues()
    {
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


    public function testCreateTransferRefund_noClientRefundId(){

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

    public function testCreateTransferRefund_noTransferRefund(){

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


    public function testCreateTransferRefund_noTransferToken(){

        $userName = "test-username";
        $password = "test-password";
        $clientRefundId = 6712348070812;
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


    public function testCreateTransferRefund_successful(){
        // Setup
        $userName = "test-username";
        $password = "test-password";
        $clientRefundId = 6712348070812;
        $sourceAmount = 20.0;
        $notes = "notes";
        $memo = "memo";

        $transferRefund = new TransferRefund();
        $transferRefund->setClientRefundId($clientRefundId);
        $transferRefund->setSourceAmount($sourceAmount);
        $transferRefund->setNotes($notes);
        $transferRefund->setMemo($memo);
        $transferToken = "transferToken";
        $client = new Hyperwallet($userName, $password);
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doPost('/rest/v3/transfers/{transfer-token}/refunds',
            array('transfer-token' => $transferToken), $transferRefund, array())->thenReturn(array('token' => 'test-token'));

        // Run test
        $newTransferRefund = $client->createTransferRefund($transferToken, $transferRefund);
        $this->assertNotNull($newTransferRefund);
        $this->assertEquals(array('token' => 'test-token'), $newTransferRefund->getProperties());
        $this->assertEquals($transferRefund->getClientRefundId(), $clientRefundId);
        $this->assertEquals($transferRefund->getSourceAmount(), $sourceAmount);
        $this->assertEquals($transferRefund->getNotes(), $notes);
        $this->assertEquals($transferRefund->getMemo(), $memo);

        // Validate mock
        \Phake::verify($apiClientMock)->doPost('/rest/v3/transfers/{transfer-token}/refunds',
            array('transfer-token' => $transferToken), $transferRefund, array());
    }


    public function testGetTransferRefund_noTransferToken()
    {
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

    public function testGetTransferRefund_noTransferRefundToken()
    {
        $userName = "test-username";
        $password = "test-password";
        $transferToken = "trf-85182390-0d3d-41a2-a749-cb9e9927b3af";
        $client = new Hyperwallet($userName, $password);
        try {
            $client->getTransferRefund($transferToken, null);
            $this->fail('HyperwalletArgumentException expected');
        } catch (HyperwalletArgumentException $e) {
            $this->assertEquals('refundToken is required!', $e->getMessage());
        }

    }

    public function testGetTransferRefund_successful()
    {
        $userName = "test-username";
        $password = "test-password";
        $client = new Hyperwallet($userName, $password);
        $transferToken = "trf-85182390-0d3d-41a2-a749-cb9e9927b3af";
        $refundToken = "trd-3be1107e-54dc-415c-a252-c41d0adcb10a";
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


    public function testListTransferRefunds_noParameters()
    {

        $userName = "test-username";
        $password = "test-password";
        $sourceCurrency = "CAD";
        $transferToken = "trf-85182390-0d3d-41a2-a749-cb9e9927b3af";
        $refundToken = "trd-3be1107e-54dc-415c-a252-c41d0adcb10a";
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


    public function testListTransferRefunds_withAllParameters()
    {

        $userName = "test-username";
        $password = "test-password";
        $sourceCurrency = "CAD";
        $transferToken = "trf-85182390-0d3d-41a2-a749-cb9e9927b3af";
        $refundToken = "trd-3be1107e-54dc-415c-a252-c41d0adcb10a";
        $uriParams = array('transfer-token' => $transferToken);
        $queryParams = array('clientRefundId' => "clientRefundId", 'sourceToken' => "sourceToken",
            'status' => "COMPLETED", 'sortBy' => "sortByField", 'limit' => "10",
            'createdAfter'=>"2016-06-29T17:58:26Z",'createdBefore'=>"2016-06-29T17:58:26Z");
        $client = new Hyperwallet($userName, $password);
        $apiClientMock = $this->createAndInjectApiClientMock($client);

        \Phake::when($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds',
            $uriParams, $queryParams)->thenReturn(array('count' => 1, 'data' => array(array('refundToken' => $refundToken, array('sourceCurrency' => $sourceCurrency)))));

        // Run test
        $transferRefundList = $client->listTransferRefunds($transferToken,$queryParams);
        $this->assertEquals(array('sourceCurrency' => $sourceCurrency), $transferRefundList[0]->getProperties()[0]);
        // Validate mock
        \Phake::verify($apiClientMock)->doGet('/rest/v3/transfers/{transfer-token}/refunds', $uriParams, $queryParams);
    }

    public function testListTransferRefund_noTransferToken()
    {
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
}
