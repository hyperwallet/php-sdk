<?php
namespace Hyperwallet\Tests\Model;

class PaymentTest extends ModelTestCase {

    protected function getModelName() {
        return 'Payment';
    }

    /**
     * @dataProvider ignoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersForIgnoredProperties($property) {
        $this->performGettersForIgnoredPropertiesTest($property);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersAndSettersForNotIgnoredProperties($property) {
        $this->performGettersAndSettersForNotIgnoredPropertiesTest($property);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($property) {
        if ($property === 'description') {
            // skip testing invalid and deprecated field
            $this->assertEquals($property, 'description');
            return;
        }

        $this->performGetterReturnValueIsSetTest($property);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsNotSet($property) {
        $this->performGetterReturnValueIsNotSetTest($property);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsSet($property) {
        if ($property === 'description') {
            // skip testing invalid and deprecated field
            $this->assertEquals($property, 'description');
            return;
        }

        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsSetTest($property);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSet($property) {
        if ($property === 'description') {
            // skip testing invalid and deprecated field
            $this->assertEquals($property, 'description');
            return;
        }

        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSetTest($property);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterNullField($property) {
        if ($property === 'description') {
            // skip testing invalid and deprecated field
            $this->assertEquals($property, 'description');
            return;
        }

        $this->performGetterAndSetterNullFieldTest($property);
    }

    public function testTokenSetterTestWithValue() {
        $this->performTokenSetterTestWithValue();
    }

    public function testTokenSetterTestWithoutValue() {
        $this->performTokenSetterTestWithoutValue();
    }

}
