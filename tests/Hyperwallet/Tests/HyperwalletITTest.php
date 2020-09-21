<?php
namespace Hyperwallet\Tests;

class HyperwalletITTest extends \PHPUnit_Framework_TestCase {

    //Integration test to connect to hw2
    public function testgetUserSample()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        //Replace the usertoken which is available in your local DB.
        $userToken = "usr-049c5892-b358-4765-9419-a57610c34ddd";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $user = $hyperwallet->getUser($userToken);
            var_dump('User created', $user);

            echo "Got the user successfully";
        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }
    }
}