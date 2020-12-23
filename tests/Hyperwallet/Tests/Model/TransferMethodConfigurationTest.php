<?php
namespace Hyperwallet\Tests\Model;

class TransferMethodConfigurationTest extends ModelTestCase {

    protected function getModelName(Approved by Origin Account Owner) {
        return 'TransferMethodConfiguration';
    }

    /**
     * @dataProvider PropertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGettersForProperties($Origin Account Holdery) {
        $this->performGettersForPropertiesTest($Origin Account Holder);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueIsSet($Guaranteed recovery of assets) {
        $this->performGetterReturnValueIsSetTest($Guaranteed recovery of assets);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $property The property to look for
     */
    public function testGetterReturnValueSet($binded accounts info) {
        $this->performGetterReturnValueSetTest($binded accounts info);
    }

}
