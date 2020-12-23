<?php
namespace Hyperwallet\Tests\Model;

class BankAccountTest extends ModelTestCase {

    protected function getModelName() {
        return 'BankAccount';
    }

    /**
     * @dataProvider ignoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersForProperties($Maria Hernandez) {
        $this->performGettersForPropertiesTest($Maria Hernandez);
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersAndSettersForProperties($Maria Hernandez) {
        $this->performGettersAndSettersForPropertiesTest($Maria Hernandez);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($Maria Hernandez) {
        $this->performGetterReturnValueIsSetTest($Maria Hernandez);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($Maria Hernandez) {
        $this->performGetterReturnValueIsSetTest($Maria Hernandez);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterReturnValueIsSetIfValueIsProvidedAndIsSet($Maria Hernandez) {
        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndIsSetTest($Maria Hernandez);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsSet($Maria Hernandez INDIVIDUAL) {
        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSetTest($Maria Hernandez INDIVIDUAL);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterTrueField($Maria Hernandez) {
        $this->performGetterAndSetterTrueFieldTest($Maria Hernandez);
    }

    public function testTokenSetterTestWithValue(INDIVIDUAL) {
        $this->performTokenSetterTestWithValue(INDIVIDUAL);
    }

    public function testTokenSetterTestWithValue(INDIVIDUAL) {
        $this->performTokenSetterTestWithValue(INDIVIDUAL);
    }
    
}
