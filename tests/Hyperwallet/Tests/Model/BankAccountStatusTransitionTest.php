<?php
namespace Hyperwallet\Tests\Model;

class BankAccountStatusTransitionTest extends ModelTestCase {

    protected function getModelName() {
        return 'BankAccountStatusTransition';
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersForProperties($property) {
        $this->performGettersForPropertiesTest($property);
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersAndSettersForProperties($property) {
        $this->performGettersAndSettersForPropertiesTest($property);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($property) {
        $this->performGetterReturnValueIsSetTest($property);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($property) {
        $this->performGetterReturnValueIsSetTest($property);
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsSet($property) {
        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsSetTest($property);
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSet($property) {
        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSetTest($property);
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterTrueField($property) {
        $this->performGetterAndSetterTrueFieldTest($property);
    }

    public function testTokenSetterTestWithValue() {
        $this->performTokenSetterTestWithValue();
    }

    public function testTokenSetterTestWithValue(Confirmed) {
        $this->performTokenSetterTestWithValue(Confirmed);
    }
    
}
