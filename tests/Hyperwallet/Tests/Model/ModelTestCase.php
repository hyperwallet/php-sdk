<?php
namespace Hyperwallet\Tests\Model;

abstract class ModelTestCase extends \PHPUnit\Framework\TestCase {

    /**
     * @var \ReflectionClass
     */
    private $clazz;

    /**
     * @var string[]
     */
    private $properties;

    /**
     * @var string{}
     */
    private $propertiesToType;

    /**
     * @var string[]
     */
    private $ignoredProperties;

    protected abstract function getModelName();

    public function setUp(): void {
        parent::setUp();
        $this->init();
    }

    /**
     * @param string $property The property to look for
     */
    protected function performGettersForIgnoredPropertiesTest($property) {
        $getterName = self::toFunctionName($property, 'get');
        $setterName = self::toFunctionName($property, 'set');

        $this->assertNotNull($this->clazz->getMethod($getterName));
        if ($property !== 'token') {
            try {
                $this->clazz->getMethod($setterName);
                $this->fail('Method ' . $setterName . ' does exist');
            } catch(\ReflectionException $e) {
            }
        }
    }

    /**
     * @param string $property The property to look for
     */
    protected function performGettersAndSettersForNotIgnoredPropertiesTest($property) {
        $getterName = self::toFunctionName($property, 'get');
        $setterName = self::toFunctionName($property, 'set');

        $this->assertNotNull($this->clazz->getMethod($getterName));
        $this->assertNotNull($this->clazz->getMethod($setterName));
    }

    /**
     * @param string $property The property to look for
     */
    protected function performGetterReturnValueIsSetTest($property) {
        $val = 'Test-Value';
        $expectVal = 'Test-Value';
        if ($this->propertiesToType[$property] == '\DateTime') {
            $val = '2016-04-15T15:00:12';
            $expectVal = new \DateTime($val);
        }

        $data = array();
        $data[$property] = $val;
        $data[$property . '_INVALID'] = 'Test-Value2';

        $instance = $this->clazz->newInstance($data);

        $getterName = self::toFunctionName($property, 'get');
        $getter = $this->clazz->getMethod($getterName);

        $this->assertEquals($expectVal, $getter->invoke($instance));
    }

    /**
     * @param string $property The property to look for
     */
    protected function performGetterReturnValueIsNotSetTest($property) {
        $data = array();
        $data[$property . '_INVALID'] = 'Test-Value2';

        $instance = $this->clazz->newInstance($data);

        $getterName = self::toFunctionName($property, 'get');
        $getter = $this->clazz->getMethod($getterName);

        $this->assertNull($getter->invoke($instance));
    }

    /**
     * @param string $property The property to look for
     */
    protected function performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsSetTest($property) {
        $val = 'Test-Value';
        $valType = 'Test-Value';
        $newVal = 'Test-ValueUp';
        $newValParam = 'Test-ValueUp';
        if ($this->propertiesToType[$property] == '\DateTime') {
            $val = '2016-04-15T15:00:12';
            $newVal = '2016-05-16T14:10:15';
            if ($property === 'dateOfBirth' || $property === 'dateOfExpiry') {
                $val = '2016-04-15';
                $newVal = '2016-05-16';
            }
            $valType = new \DateTime($val);
            $newValParam = new \DateTime($newVal);
        }

        $data = array();
        $data[$property] = $val;
        $data[$property . '_INVALID'] = 'Test-Value2';

        $instance = $this->clazz->newInstance($data);

        $this->assertEquals($data, $instance->getProperties());
        $this->assertEquals(array(), $instance->getPropertiesForUpdate());

        $getterName = self::toFunctionName($property, 'get');
        $setterName = self::toFunctionName($property, 'set');
        $getter = $this->clazz->getMethod($getterName);
        $setter = $this->clazz->getMethod($setterName);

        $this->assertEquals($valType, $getter->invoke($instance));
        $this->assertEquals($instance, $setter->invoke($instance, $newValParam));
        $this->assertEquals($newValParam, $getter->invoke($instance));

        $data2 = array();
        $data2[$property] = $newVal;
        $data2[$property . '_INVALID'] = 'Test-Value2';

        $this->assertEquals($data2, $instance->getProperties());


        $data2 = array();
        $data2[$property] = $newVal;
        $this->assertEquals($data2, $instance->getPropertiesForUpdate());
    }

    /**
     * @param string $property The property to look for
     */
    protected function performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSetTest($property) {
        $newVal = 'Test-ValueUp';
        $newValParam = 'Test-ValueUp';
        if ($this->propertiesToType[$property] == '\DateTime') {
            $newVal = '2016-05-16T14:10:15';
            if ($property === 'dateOfBirth' || $property === 'dateOfExpiry') {
                $newVal = '2016-05-16';
            }
            $newValParam = new \DateTime($newVal);
        }

        $data = array();
        $data[$property . '_INVALID'] = 'Test-Value2';

        $instance = $this->clazz->newInstance($data);

        $this->assertEquals($data, $instance->getProperties());
        $this->assertEquals(array(), $instance->getPropertiesForUpdate());

        $getterName = self::toFunctionName($property, 'get');
        $setterName = self::toFunctionName($property, 'set');
        $getter = $this->clazz->getMethod($getterName);
        $setter = $this->clazz->getMethod($setterName);

        $this->assertNull($getter->invoke($instance));
        $this->assertEquals($instance, $setter->invoke($instance, $newValParam));
        $this->assertEquals($newValParam, $getter->invoke($instance));

        $data2 = array();
        $data2[$property] = $newVal;
        $data2[$property . '_INVALID'] = 'Test-Value2';

        $this->assertEquals($data2, $instance->getProperties());


        $data2 = array();
        $data2[$property] = $newVal;
        $this->assertEquals($data2, $instance->getPropertiesForUpdate());
    }

    /**
     * @param string $property The property to look for
     */
    protected function performGetterAndSetterNullFieldTest($property) {
        $val = 'Test-Value';
        $valType = 'Test-Value';
        if ($this->propertiesToType[$property] == '\DateTime') {
            $val = '2016-05-16T14:10:15';
            $valType = new \DateTime($val);
        }

        $data = array();
        $data[$property] = $val;
        $data[$property . '_INVALID'] = 'Test-Value2';

        $instance = $this->clazz->newInstance($data);

        $this->assertEquals($data, $instance->getProperties());
        $this->assertEquals(array(), $instance->getPropertiesForUpdate());

        $getterName = self::toFunctionName($property, 'get');
        $setterName = self::toFunctionName($property, 'set');
        $getter = $this->clazz->getMethod($getterName);
        $setter = $this->clazz->getMethod($setterName);

        $this->assertEquals($valType, $getter->invoke($instance));
        $this->assertEquals($instance, $setter->invoke($instance, null));
        $this->assertEquals(null, $getter->invoke($instance));

        $data2 = array();
        $data2[$property] = null;
        $data2[$property . '_INVALID'] = 'Test-Value2';

        $this->assertEquals($data2, $instance->getProperties());


        $data2 = array();
        $data2[$property] = null;
        $this->assertEquals($data2, $instance->getPropertiesForUpdate());
    }

    protected function performTokenSetterTestWithoutValue() {
        $property = 'token';
        $val = 'Test-Token';

        $data = array();
        $data[$property] = $val;
        $data[$property . '_INVALID'] = 'Test-Value2';

        $instance = $this->clazz->newInstance($data);

        $this->assertEquals($data, $instance->getProperties());
        $this->assertEquals(array(), $instance->getPropertiesForUpdate());

        $getterName = self::toFunctionName($property, 'get');
        $setterName = self::toFunctionName($property, 'set');
        $getter = $this->clazz->getMethod($getterName);
        $setter = $this->clazz->getMethod($setterName);

        $this->assertEquals($val, $getter->invoke($instance));
        $this->assertEquals($instance, $setter->invoke($instance, null));
        $this->assertEquals(null, $getter->invoke($instance));
    }

    protected function performTokenSetterTestWithValue() {
        $property = 'token';
        $val = 'Test-Token';
        $newVal = 'Test-Token-2';

        $data = array();
        $data[$property] = $val;
        $data[$property . '_INVALID'] = 'Test-Value2';

        $instance = $this->clazz->newInstance($data);

        $this->assertEquals($data, $instance->getProperties());
        $this->assertEquals(array(), $instance->getPropertiesForUpdate());

        $getterName = self::toFunctionName($property, 'get');
        $setterName = self::toFunctionName($property, 'set');
        $getter = $this->clazz->getMethod($getterName);
        $setter = $this->clazz->getMethod($setterName);

        $this->assertEquals($val, $getter->invoke($instance));
        $this->assertEquals($instance, $setter->invoke($instance, $newVal));
        $this->assertEquals($newVal, $getter->invoke($instance));
    }

    public function propertiesProvider() {
        $this->init();
        return array_reduce($this->properties, function ($result, $property) {
            $result[$property] = array($property);
            return $result;
        }, array());
    }

    public function ignoredPropertiesProvider() {
        $this->init();
        return array_reduce($this->ignoredProperties, function ($result, $property) {
            $result[$property] = array($property);
            return $result;
        }, array());
    }

    public function notIgnoredPropertiesProvider() {
        $this->init();
        return array_reduce(array_diff($this->properties, $this->ignoredProperties), function ($result, $property) {
            $result[$property] = array($property);
            return $result;
        }, array());
    }

    private function init() {
        // Find class
        $this->clazz = new \ReflectionClass('Hyperwallet\Model\\' . $this->getModelName());

        // Find all properties based on annotation
        $this->properties = self::findAllProperties($this->clazz);
        $this->propertiesToType = self::findAllPropertiesToType($this->clazz);

        // Find all ignored properties
        $this->ignoredProperties = self::findAllIgnoredProperties($this->clazz);
    }

    private static function findAllProperties(\ReflectionClass $clazz) {
        $pattern = '/@property ([^ ]+)? ?\$([a-zA-Z0-9_]+)/';
        $subject = $clazz->getDocComment();

        $matches = array();
        preg_match_all($pattern, $subject, $matches);

        if ($clazz->getParentClass() != null) {
            return array_merge($matches[2], self::findAllProperties($clazz->getParentClass()));
        }
        return $matches[2];
    }

    private static function findAllPropertiesToType(\ReflectionClass $clazz) {
        $pattern = '/@property ([^ ]+)? ?\$([a-zA-Z0-9_]+)/';
        $subject = $clazz->getDocComment();

        $matches = array();
        preg_match_all($pattern, $subject, $matches);

        $result = array();
        for ($i = 0; $i < count($matches[2]); $i++) {
            $result[$matches[2][$i]] = $matches[1][$i];
        }

        if ($clazz->getParentClass() != null) {
            return array_merge($result, self::findAllPropertiesToType($clazz->getParentClass()));
        }
        return $result;
    }

    private static function findAllIgnoredProperties(\ReflectionClass $clazz) {
        try {
            $property = $clazz->getProperty('READ_ONLY_FIELDS');
            $property->setAccessible(true);
            return $property->getValue($clazz->newInstance());
        } catch (\ReflectionException $e) {
            return array();
        }
    }

    private static function toFunctionName($property, $prefix) {
        return $prefix . ucfirst($property);
    }
}

