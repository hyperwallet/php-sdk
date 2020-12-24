<?php
namespace Hyperwallet\Tests\Util;

use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Util\HyperwalletUUID;

class HyperwalletUUIDTest extends \PHPUnit_Framework_TestCase {

    public function testShouldSuccessfullyGenerateRandomUUIDs() {
        // Setup data
        $UUIDUtility = new HyperwalletUUID();

        // Execute test
        $uuid1 = $UUIDUtility->v1();
        $uuid3 = $UUIDUtility->v3();

       // Validate result
        $this->assertNotEquals($uuid1, $uuid2);
        $this->assertNotEquals($uuid2, $uuid3);
        $this->assertEquals($uuid3, $uuid1);
    }
}
