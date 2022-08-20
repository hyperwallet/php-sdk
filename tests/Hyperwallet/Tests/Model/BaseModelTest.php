<?php
namespace Hyperwallet\Tests\Model;

use Hyperwallet\Model\BaseModel;

class BaseModelTest extends \PHPUnit\Framework\TestCase {

    public function testMagicGetter() {
        $data = array(
            'test' => 'value',
            'test2' => 'value2'
        );

        $model = new BaseModel(array(), $data);

        $this->assertEquals('value', $model->test);
        $this->assertNull($model->test3);
    }

    /**
     * @depends testMagicGetter
     */
    public function testMagicSetter() {
        $data = array(
            'test' => 'value',
            'test2' => 'value2'
        );

        $model = new BaseModel(array(), $data);


        $model->test = 'value3';
        $model->test3 = 'value4';

        $this->assertEquals('value3', $model->test);
        $this->assertEquals('value4', $model->test3);
        $this->assertEquals('value2', $model->test2);
    }

    public function testIsset() {
        $data = array(
            'test' => 'value',
            'test2' => 'value2'
        );

        $model = new BaseModel(array(), $data);

        $this->assertTrue(isset($model->test));
        $this->assertFalse(isset($model->test3));
    }

    public function testGetPropertiesForCreate() {
        $data = array(
            'test' => 'value',
            'test2' => 'value2'
        );

        $model = new BaseModel(array(), $data);
        $model->test2 = 'value2_1';
        $model->test3 = 'value3';

        $this->assertEquals(array(
            'test' => 'value',
            'test2' => 'value2_1',
            'test3' => 'value3'
        ), $model->getPropertiesForCreate());
    }

    public function testGetPropertiesForUpdate_noUpdate() {
        $data = array(
            'test' => 'value',
            'test2' => 'value2'
        );

        $model = new BaseModel(array(), $data);

        $this->assertEquals(array(), $model->getPropertiesForUpdate());
    }

    public function testGetPropertiesForUpdate_withUpdate() {
        $data = array(
            'test' => 'value',
            'test2' => 'value2'
        );

        $model = new BaseModel(array(), $data);
        $model->test2 = 'value2_1';
        $model->test3 = 'value3';

        $this->assertEquals(array(
            'test2' => 'value2_1',
            'test3' => 'value3'
        ), $model->getPropertiesForUpdate());
    }

    public function testUnset() {
        $data = array(
            'test' => 'value',
            'test2' => 'value2',
            'test3' => 'value3',
        );

        $model = new BaseModel(array(), $data);
        $model->test2 = 'value2_1';
        unset($model->test);
        unset($model->test2);

        $this->assertEquals(array(
            'test3' => 'value3'
        ), $model->getPropertiesForCreate());

        $this->assertEquals(array(), $model->getPropertiesForUpdate());
    }

}
