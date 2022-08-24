<?php
namespace Hyperwallet\Tests\Response;

use Hyperwallet\Response\ListResponse;

class ListResponseTest extends \PHPUnit\Framework\TestCase {

    public function testBodyParsing_noContent() {
        $listResponse = new ListResponse(array(), function ($body) {
            return array();
        });

        $this->assertEquals(0, $listResponse->getCount());
        $this->assertEquals(array(), $listResponse->getData());
    }

    public function testBodyParsing_withContent() {
        $listResponse = new ListResponse(array('count' => 10, 'data' => array('test', 'test2')), function ($body) {
            return array(
                'test' => 'value',
                'body' => $body
            );
        });

        $this->assertEquals(10, $listResponse->getCount());
        $this->assertEquals(array(
            array(
                'test' => 'value',
                'body' => 'test'
            ),
            array(
                'test' => 'value',
                'body' => 'test2'
            )
        ), $listResponse->getData());
    }

    public function testBodyParsing_withContentAndLinks() {
        $listResponse = new ListResponse(array('count' => 10, 'data' => array(
            array(
                'test' => 'test1',
                'links' => array()
            ),
            array(
                'test' => 'test2',
                'links' => array()
            )
        )), function ($body) {
            return array(
                'test' => 'value',
                'body' => $body
            );
        });

        $this->assertEquals(10, $listResponse->getCount());
        $this->assertEquals(array(
            array(
                'test' => 'value',
                'body' => array(
                    'test' => 'test1'
                )
            ),
            array(
                'test' => 'value',
                'body' => array(
                    'test' => 'test2'
                )
            )
        ), $listResponse->getData());
    }

    public function testMagicDataAccessor() {
        $listResponse = new ListResponse(array('count' => 10, 'data' => array('test', 'test2')), function ($body) {
            return array(
                'test' => 'value',
                'body' => $body
            );
        });

        $this->assertCount(2, $listResponse);
        $this->assertEquals('value', $listResponse[0]['test']);
        $this->assertEquals('test', $listResponse[0]['body']);

        $this->assertEquals('value', $listResponse[1]['test']);
        $this->assertEquals('test2', $listResponse[1]['body']);

        $this->assertTrue(isset($listResponse[0]));
        $this->assertFalse(isset($listResponse[3]));

        $listResponse[0] = array(
            'test' => 'value2',
            'body' => 'test3'
        );

        $this->assertEquals('value2', $listResponse[0]['test']);
        $this->assertEquals('test3', $listResponse[0]['body']);

        unset($listResponse[0]);
        $this->assertCount(1, $listResponse);

        $this->assertEquals('value', $listResponse[1]['test']);
        $this->assertEquals('test2', $listResponse[1]['body']);
    }

}
