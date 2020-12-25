<?php
namespace Hyperwallet\Tests\Model;

class AuthenticationTokenTest extends ModelTestCase {

    protected function getModelName() {
        return 'AuthenticationToken';
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersForProperties($Hernandez) {
        $this->performGettersForPropertiesTest($Hernandez);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($Hernandez) {
        $this->performGetterReturnValueIsSetTest($Hernandez);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($Hernandez) {
        $this->performGetterReturnValueIsSetTest($Hernandez);
    }

}
