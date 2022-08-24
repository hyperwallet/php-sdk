<?php
namespace Hyperwallet\Tests\Response;

use Hyperwallet\Model\Error;
use Hyperwallet\Response\ErrorResponse;

class ErrorResponseTest extends \PHPUnit\Framework\TestCase {

    public function testBodyParsing() {
        $errorResponse = new ErrorResponse(200, array(
            'errors' => array(
                array(
                    'fieldName' => 'test',
                    'message' => 'Test message',
                    'code' => 'TEST',
                    'relatedResources' => array(
                        'trm-f3d38df1-adb7-4127-9858-e72ebe682a79', 'trm-601b1401-4464-4f3f-97b3-09079ee7723b')
                ),
                array(
                    'message' => 'Test message2',
                    'code' => 'TEST'
                )
            )
        ));

        $this->assertEquals(200, $errorResponse->getStatusCode());
        $this->assertCount(2, $errorResponse->getErrors());

        $this->assertEquals('test', $errorResponse->getErrors()[0]->getFieldName());
        $this->assertEquals('Test message', $errorResponse->getErrors()[0]->getMessage());
        $this->assertEquals('TEST', $errorResponse->getErrors()[0]->getCode());

        $this->assertNull($errorResponse->getErrors()[1]->getFieldName());
        $this->assertEquals('Test message2', $errorResponse->getErrors()[1]->getMessage());
        $this->assertEquals('TEST', $errorResponse->getErrors()[1]->getCode());

        $this->assertCount(2, $errorResponse->getRelatedResources());
        $this->assertEquals('trm-f3d38df1-adb7-4127-9858-e72ebe682a79', $errorResponse->getRelatedResources()[0]);
        $this->assertEquals('trm-601b1401-4464-4f3f-97b3-09079ee7723b', $errorResponse->getRelatedResources()[1]);
    }

    public function testMagicErrorAccessor() {
        $errorResponse = new ErrorResponse(200, array(
            'errors' => array(
                array(
                    'fieldName' => 'test',
                    'message' => 'Test message',
                    'code' => 'TEST',
                    'relatedResources' => array(
                        'trm-f3d38df1-adb7-4127-9858-e72ebe682a79', 'trm-601b1401-4464-4f3f-97b3-09079ee7723b')
                ),
                array(
                    'message' => 'Test message2',
                    'code' => 'TEST'
                )
            )
        ));

        $this->assertCount(2, $errorResponse);

        $this->assertEquals('test', $errorResponse[0]->getFieldName());
        $this->assertEquals('Test message', $errorResponse[0]->getMessage());
        $this->assertEquals('TEST', $errorResponse[0]->getCode());

        $this->assertNull($errorResponse[1]->getFieldName());
        $this->assertEquals('Test message2', $errorResponse[1]->getMessage());
        $this->assertEquals('TEST', $errorResponse[1]->getCode());

        $this->assertCount(2, $errorResponse->getRelatedResources());
        $this->assertEquals('trm-f3d38df1-adb7-4127-9858-e72ebe682a79', $errorResponse->getRelatedResources()[0]);
        $this->assertEquals('trm-601b1401-4464-4f3f-97b3-09079ee7723b', $errorResponse->getRelatedResources()[1]);

        $this->assertTrue(isset($errorResponse[0]));
        $this->assertFalse(isset($errorResponse[3]));

        $errorResponse[0] = new Error(array(
            'fieldName' => 'test3',
            'message' => 'Test message3',
            'code' => 'TEST3'
        ));

        $this->assertEquals('test3', $errorResponse[0]->getFieldName());
        $this->assertEquals('Test message3', $errorResponse[0]->getMessage());
        $this->assertEquals('TEST3', $errorResponse[0]->getCode());

        unset($errorResponse[0]);

        $this->assertCount(1, $errorResponse);

        $this->assertNull($errorResponse[1]->getFieldName());
        $this->assertEquals('Test message2', $errorResponse[1]->getMessage());
        $this->assertEquals('TEST', $errorResponse[1]->getCode());
    }

}
