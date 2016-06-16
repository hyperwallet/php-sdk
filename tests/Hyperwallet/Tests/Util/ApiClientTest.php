<?php
namespace Hyperwallet\Tests\Util;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Model\BaseModel;
use Hyperwallet\Util\ApiClient;

class ApiClientTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var array
     */
    private $container;

    /**
     * @var ApiClient
     */
    private $apiClient;

    public function testDoPost_return_response_with_query() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test2' => 'value2'));

        // Execute test
        $data = $this->apiClient->doPost('/test', array(), $model, array('test' => 'true'));
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('POST', '/test', 'test=true', array('test2' => 'value2'), true);
    }

    public function testDoPost_return_response_without_query() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test2' => 'value2'));

        // Execute test
        $data = $this->apiClient->doPost('/test', array(), $model, array());
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('POST', '/test', '', array('test2' => 'value2'), true);
    }

    public function testDoPost_return_response_with_path_placeholder() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test2' => 'value2'));

        // Execute test
        $data = $this->apiClient->doPost('/test/{test}', array('test' => 'token'), $model, array());
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('POST', '/test/token', '', array('test2' => 'value2'), true);
    }

    public function testDoPost_throw_exception_connection_issue() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new ConnectException('Connection refused', new Request('POST', 'test'))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test2' => 'value2'));

        // Execute test
        try {
            $this->apiClient->doPost('/test', array(), $model, array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('Could not communicate with http://test.server', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(0, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(1, $e->getErrorResponse()->getErrors());
            $this->assertEquals('COMMUNICATION_ERROR', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('Could not communicate with http://test.server', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[0]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('POST', '/test', '', array('test2' => 'value2'), true);
    }

    public function testDoPost_throw_exception_bad_request() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(400, array(), \GuzzleHttp\json_encode(array(
                'errors' => array(
                    array(
                        'fieldName' => 'testField',
                        'code' => 'MY_CODE',
                        'message' => 'My test message'
                    ),
                    array(
                        'code' => 'MY_SECOND_CODE',
                        'message' => 'My second test message'
                    )
                )
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test2' => 'value2'));

        // Execute test
        try {
            $this->apiClient->doPost('/test', array(), $model, array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('My test message', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(400, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(2, $e->getErrorResponse()->getErrors());

            $this->assertEquals('MY_CODE', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('My test message', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertEquals('testField', $e->getErrorResponse()->getErrors()[0]->getFieldName());

            $this->assertEquals('MY_SECOND_CODE', $e->getErrorResponse()->getErrors()[1]->getCode());
            $this->assertEquals('My second test message', $e->getErrorResponse()->getErrors()[1]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[1]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('POST', '/test', '', array('test2' => 'value2'), true);
    }

    public function testDoPost_throw_exception_server_error() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(500, array(), \GuzzleHttp\json_encode(array(
                'errors' => array(
                    array(
                        'fieldName' => 'testField',
                        'code' => 'MY_CODE',
                        'message' => 'My test message'
                    ),
                    array(
                        'code' => 'MY_SECOND_CODE',
                        'message' => 'My second test message'
                    )
                )
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test2' => 'value2'));

        // Execute test
        try {
            $this->apiClient->doPost('/test', array(), $model, array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('My test message', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(500, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(2, $e->getErrorResponse()->getErrors());

            $this->assertEquals('MY_CODE', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('My test message', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertEquals('testField', $e->getErrorResponse()->getErrors()[0]->getFieldName());

            $this->assertEquals('MY_SECOND_CODE', $e->getErrorResponse()->getErrors()[1]->getCode());
            $this->assertEquals('My second test message', $e->getErrorResponse()->getErrors()[1]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[1]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('POST', '/test', '', array('test2' => 'value2'), true);
    }


    public function testDoPut_return_response_only_submit_updated_field() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test2' => 'value2'));

        // Execute test
        $data = $this->apiClient->doPut('/test', array(), $model, array('test' => 'true'));
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('PUT', '/test', 'test=true', array(), true);
    }

    public function testDoPut_return_response_with_query() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test' => 'value'));
        $model->test2 = 'value2';

        // Execute test
        $data = $this->apiClient->doPut('/test', array(), $model, array('test' => 'true'));
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('PUT', '/test', 'test=true', array('test2' => 'value2'), true);
    }

    public function testDoPut_return_response_without_query() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test' => 'value'));
        $model->test2 = 'value2';

        // Execute test
        $data = $this->apiClient->doPut('/test', array(), $model, array());
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('PUT', '/test', '', array('test2' => 'value2'), true);
    }

    public function testDoPut_return_response_with_path_placeholder() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array());
        $model->test2 = 'value2';

        // Execute test
        $data = $this->apiClient->doPut('/test/{test}', array('test' => 'token'), $model, array());
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('PUT', '/test/token', '', array('test2' => 'value2'), true);
    }

    public function testDoPut_throw_exception_connection_issue() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new ConnectException('Connection refused', new Request('POST', 'test'))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test' => 'value'));
        $model->test2 = 'value2';

        // Execute test
        try {
            $this->apiClient->doPut('/test', array(), $model, array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('Could not communicate with http://test.server', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(0, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(1, $e->getErrorResponse()->getErrors());
            $this->assertEquals('COMMUNICATION_ERROR', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('Could not communicate with http://test.server', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[0]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('PUT', '/test', '', array('test2' => 'value2'), true);
    }

    public function testDoPut_throw_exception_bad_request() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(400, array(), \GuzzleHttp\json_encode(array(
                'errors' => array(
                    array(
                        'fieldName' => 'testField',
                        'code' => 'MY_CODE',
                        'message' => 'My test message'
                    ),
                    array(
                        'code' => 'MY_SECOND_CODE',
                        'message' => 'My second test message'
                    )
                )
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test' => 'value'));
        $model->test2 = 'value2';

        // Execute test
        try {
            $this->apiClient->doPut('/test', array(), $model, array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('My test message', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(400, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(2, $e->getErrorResponse()->getErrors());

            $this->assertEquals('MY_CODE', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('My test message', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertEquals('testField', $e->getErrorResponse()->getErrors()[0]->getFieldName());

            $this->assertEquals('MY_SECOND_CODE', $e->getErrorResponse()->getErrors()[1]->getCode());
            $this->assertEquals('My second test message', $e->getErrorResponse()->getErrors()[1]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[1]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('PUT', '/test', '', array('test2' => 'value2'), true);
    }

    public function testDoPut_throw_exception_server_error() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(500, array(), \GuzzleHttp\json_encode(array(
                'errors' => array(
                    array(
                        'fieldName' => 'testField',
                        'code' => 'MY_CODE',
                        'message' => 'My test message'
                    ),
                    array(
                        'code' => 'MY_SECOND_CODE',
                        'message' => 'My second test message'
                    )
                )
            )))
        ));
        $this->createApiClient($mockHandler);

        $model = new BaseModel(array(), array('test' => 'value'));
        $model->test2 = 'value2';

        // Execute test
        try {
            $this->apiClient->doPut('/test', array(), $model, array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('My test message', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(500, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(2, $e->getErrorResponse()->getErrors());

            $this->assertEquals('MY_CODE', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('My test message', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertEquals('testField', $e->getErrorResponse()->getErrors()[0]->getFieldName());

            $this->assertEquals('MY_SECOND_CODE', $e->getErrorResponse()->getErrors()[1]->getCode());
            $this->assertEquals('My second test message', $e->getErrorResponse()->getErrors()[1]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[1]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('PUT', '/test', '', array('test2' => 'value2'), true);
    }


    public function testDoGet_return_response_with_query() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        // Execute test
        $data = $this->apiClient->doGet('/test', array(), array('test' => 'true'));
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('GET', '/test', 'test=true', array('test2' => 'value2'), false);
    }

    public function testDoGet_return_response_without_query() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        // Execute test
        $data = $this->apiClient->doGet('/test', array(), array());
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('GET', '/test', '', array('test2' => 'value2'), false);
    }

    public function testDoGet_return_response_204_status() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(204)
        ));
        $this->createApiClient($mockHandler);

        // Execute test
        $data = $this->apiClient->doGet('/test', array(), array('test' => 'true'));
        $this->assertEquals(array(), $data);

        // Validate api request
        $this->validateRequest('GET', '/test', 'test=true', array('test2' => 'value2'), false);
    }

    public function testDoGet_return_response_with_path_placeholder() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(200, array(), \GuzzleHttp\json_encode(array(
                'test' => 'value',
                'links' => 'linksValue'
            )))
        ));
        $this->createApiClient($mockHandler);

        // Execute test
        $data = $this->apiClient->doGet('/test/{test}', array('test' => 'token'), array());
        $this->assertArrayHasKey('test', $data);
        $this->assertArrayNotHasKey('links', $data);

        // Validate api request
        $this->validateRequest('GET', '/test/token', '', array('test2' => 'value2'), false);
    }

    public function testDoGet_throw_exception_connection_issue() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new ConnectException('Connection refused', new Request('POST', 'test'))
        ));
        $this->createApiClient($mockHandler);

        // Execute test
        try {
            $this->apiClient->doGet('/test', array(), array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('Could not communicate with http://test.server', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(0, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(1, $e->getErrorResponse()->getErrors());
            $this->assertEquals('COMMUNICATION_ERROR', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('Could not communicate with http://test.server', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[0]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('GET', '/test', '', array('test2' => 'value2'), false);
    }

    public function testDoGet_throw_exception_bad_request() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(400, array(), \GuzzleHttp\json_encode(array(
                'errors' => array(
                    array(
                        'fieldName' => 'testField',
                        'code' => 'MY_CODE',
                        'message' => 'My test message'
                    ),
                    array(
                        'code' => 'MY_SECOND_CODE',
                        'message' => 'My second test message'
                    )
                )
            )))
        ));
        $this->createApiClient($mockHandler);

        // Execute test
        try {
            $this->apiClient->doGet('/test', array(), array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('My test message', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(400, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(2, $e->getErrorResponse()->getErrors());

            $this->assertEquals('MY_CODE', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('My test message', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertEquals('testField', $e->getErrorResponse()->getErrors()[0]->getFieldName());

            $this->assertEquals('MY_SECOND_CODE', $e->getErrorResponse()->getErrors()[1]->getCode());
            $this->assertEquals('My second test message', $e->getErrorResponse()->getErrors()[1]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[1]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('GET', '/test', '', array('test2' => 'value2'), false);
    }

    public function testDoGet_throw_exception_server_error() {
        // Setup data
        $mockHandler = new MockHandler(array(
            new Response(500, array(), \GuzzleHttp\json_encode(array(
                'errors' => array(
                    array(
                        'fieldName' => 'testField',
                        'code' => 'MY_CODE',
                        'message' => 'My test message'
                    ),
                    array(
                        'code' => 'MY_SECOND_CODE',
                        'message' => 'My second test message'
                    )
                )
            )))
        ));
        $this->createApiClient($mockHandler);

        // Execute test
        try {
            $this->apiClient->doGet('/test', array(), array());
            $this->fail('HyperwalletApiException expected');
        } catch (HyperwalletApiException $e) {
            $this->assertEquals('My test message', $e->getMessage());
            $this->assertNotNull($e->getErrorResponse());

            $this->assertEquals(500, $e->getErrorResponse()->getStatusCode());
            $this->assertCount(2, $e->getErrorResponse()->getErrors());

            $this->assertEquals('MY_CODE', $e->getErrorResponse()->getErrors()[0]->getCode());
            $this->assertEquals('My test message', $e->getErrorResponse()->getErrors()[0]->getMessage());
            $this->assertEquals('testField', $e->getErrorResponse()->getErrors()[0]->getFieldName());

            $this->assertEquals('MY_SECOND_CODE', $e->getErrorResponse()->getErrors()[1]->getCode());
            $this->assertEquals('My second test message', $e->getErrorResponse()->getErrors()[1]->getMessage());
            $this->assertNull($e->getErrorResponse()->getErrors()[1]->getFieldName());
        }

        // Validate api request
        $this->validateRequest('GET', '/test', '', array('test2' => 'value2'), false);
    }


    private function validateRequest($method, $path, $query, array $body, $hasContentType) {
        // Validate api request
        $this->assertCount(1, $this->container);

        /** @var Request $request */
        $request = $this->container[0]['request'];
        $this->assertEquals($method, $request->getMethod());

        $this->assertCount($hasContentType ? 6 : 4, $request->getHeaders());
        $this->assertArrayHasKeyAndValue('Accept', 'application/json', $request->getHeaders());
        if ($hasContentType) {
            $this->assertArrayHasKeyAndValue('Content-Type', 'application/json', $request->getHeaders());
            $this->assertArrayHasKeyAndValue('Content-Length', $request->getBody()->getSize(), $request->getHeaders());
        }
        $this->assertArrayHasKeyAndValue('User-Agent', 'Hyperwallet PHP SDK v0.0.1', $request->getHeaders());
        $this->assertArrayHasKeyAndValue('Host', 'test.server', $request->getHeaders());
        $this->assertArrayHasKeyAndValue('Authorization', 'Basic dGVzdC11c2VybmFtZTp0ZXN0LXBhc3N3b3Jk', $request->getHeaders());

        $this->assertEquals('http', $request->getUri()->getScheme());
        $this->assertEquals('test.server', $request->getUri()->getHost());
        $this->assertEquals($path, $request->getUri()->getPath());
        $this->assertEquals($query, $request->getUri()->getQuery());

        if ($hasContentType) {
            $data = $request->getBody()->__toString();

            $this->assertJson($data);
            $this->assertJsonStringEqualsJsonString(\GuzzleHttp\json_encode($body, JSON_FORCE_OBJECT), $data);
        }
    }

    private function createApiClient(MockHandler $mockHandler) {
        $this->container = array();
        $history = Middleware::history($this->container);

        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);

        $this->apiClient = new ApiClient('test-username', 'test-password', 'http://test.server', array(
            'handler' => $stack
        ));
    }

    private function assertArrayHasKeyAndValue($expectedKey, $expectedValue, array $actual) {
        $this->assertArrayHasKey($expectedKey, $actual);
        $this->assertEquals($expectedValue, $actual[$expectedKey][0]);
    }

}
