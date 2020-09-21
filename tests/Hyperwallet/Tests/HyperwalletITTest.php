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

    //--------------------------------------
    // Venmo Accounts
    //--------------------------------------

    public function testcreateVenmoAccount()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        $userToken = "usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $response = $hyperwallet->createVenmoAccount("usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a", (new \Hyperwallet\Model\VenmoAccount())
                ->setType("VENMO_ACCOUNT")
                ->setTransferMethodCountry("US")
                ->setTransferMethodCurrency("USD")
                ->setAccountId("9620766696")
            );
            var_dump('Venmo Account is created for the user', $response);
            echo "Venmo Account is created for the user";
        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }
    }

    public function testGetVenmoAccount()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        $userToken = "usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $response = $hyperwallet->getVenmoAccount("usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a", "trm-85d33a86-dd0b-4d6f-832b-b90753676d1b");
            var_dump('Venmo Account Retrieve', $response);
            echo "Venmo Account Retrieved for the user";
        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }
    }

    public function testUpdateVenmoAccount()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        $userToken = "usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $response = $hyperwallet->updateVenmoAccount("usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a", (new \Hyperwallet\Model\VenmoAccount())
                ->setToken("trm-85d33a86-dd0b-4d6f-832b-b90753676d1b")
                ->setAccountId("9908950581")
            );
            var_dump('Venmo Account updated successfully', $response);
            echo "Venmo Account updated successfully";

        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }

    }

    public function testListVenmoAccount()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        $userToken = "usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $response = $hyperwallet->listVenmoAccounts("usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a", $options = array());
            //var_dump('List Venmo Accounts', $response);
            print_r($response);
            echo "Venmo Account listed for the user";
        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }

    }

    public function testdeactivateVenmoAccount()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        $userToken = "usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $transition = new VenmoAccountStatusTransition();
            $transition->setTransition(VenmoAccountStatusTransition::TRANSITION_DE_ACTIVATED);
            $response = $hyperwallet->createVenmoAccountStatusTransition("usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a", "trm-0d7131bc-fcf5-463c-a65d-a81a89937b03", $transition);
            print_r($response);
            echo "Venmo Account is Deactivated ";
        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }
    }

    public function testGetVenmoAccountStatusTransition()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        $userToken = "usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $response = $hyperwallet->getVenmoAccountStatusTransition("usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a", "trm-0d7131bc-fcf5-463c-a65d-a81a89937b03", "sts-77d26d95-fa2e-45ef-9ccc-0bbd13786c4d");
            print_r($response);
            echo "StatusTransition for Venmo Account User";
        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }

    }

    public function testListVenmoAccountStatusTransitions()
    {
        $username = "selrestuser@330068";
        $password = "Password1!";
        $programToken = "prg-d7e7e14c-74ac-4fc6-ba02-ab40769a7ab4";
        $server = "https://localhost:8181";
        $user = new \Hyperwallet\Model\User();
        $userToken = "usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a";
        $hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, $programToken, $server);
        try {
            $response = $hyperwallet->listVenmoAccountStatusTransitions("usr-ac977ac7-2ad9-42d2-8ae0-fd915d3a953a", "trm-0d7131bc-fcf5-463c-a65d-a81a89937b03", $options = array());
            print_r($response);
            echo "StatusTransition for Venmo Account User";
        } catch (\Hyperwallet\Exception\HyperwalletException $e) {
            echo $e->getMessage();
            die("\n");
        }
    }
}
