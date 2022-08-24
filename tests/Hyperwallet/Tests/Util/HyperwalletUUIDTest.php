<?php
namespace Hyperwallet\Tests\Util;

use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Util\HyperwalletUUID;

class HyperwalletUUIDTest extends \PHPUnit\Framework\TestCase {

    public function testShouldSuccessfullyGenerateRandomUUIDs() {
        // Setup data
        $UUIDUtility = new HyperwalletUUID();

        // Execute test
        $uuid1 = $UUIDUtility->v4();
        $uuid2 = $UUIDUtility->v4();
        $uuid3 = $UUIDUtility->v4();

       // Validate result
        $this->assertNotEquals($uuid1, $uuid2);
        $this->assertNotEquals($uuid2, $uuid3);
        $this->assertNotEquals($uuid3, $uuid1);
    }
}
