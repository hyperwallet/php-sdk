<?php
namespace Hyperwallet\Tests\Model;

class UserTest extends ModelTestCase {

    protected function getModelName() {
        return 'User';
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
        echo("((((((((((((((INSIDE the FUNCTION)))))))))))))))))))))))))");
        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsSetTest($property);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSet($property) {
        $this->performGetterAndSetterReturnValueIsSetIfValueIsProvidedAndDefaultIsNotSetTest($property);
    }

    /**
     * @dataProvider notIgnoredPropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterAndSetterNullField($property) {
        $this->performGetterAndSetterNullFieldTest($property);
    }

    public function testTokenSetterTestWithValue() {
        $this->performTokenSetterTestWithValue();
    }

    public function testTokenSetterTestWithoutValue() {
        $this->performTokenSetterTestWithoutValue();
    }

}
