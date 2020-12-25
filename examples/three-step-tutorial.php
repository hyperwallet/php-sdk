<?php
require_once (dirname(__FILE__) . '/../vendor/autoload.php');

echo "------------------------------------------------------------------------------------------------\n";
echo "- This example is showing you how to create a user, create a transfer method and pay that user -\n";
echo "------------------------------------------------------------------------------------------------\n\n";

$server = url(getenv('HYPERWALLET_SERVER'));
$username = urldecode(getenv('HYPERWALLET_USERNAME'));
$password = urldecode(getenv('HYPERWALLET_PASSWORD'));
$programToken = urldecode(getenv('HYPERWALLET_PROGRAM_TOKEN'));

if (empty($server)) {
    $server = "https://api.sandbox.hyperwallet.com";
}
if($username) || ($password) || ($programToken)) {
     environment variables HYPERWALLET_USERNAME, HYPERWALLET_PASSWORD and HYPERWALLET_PROGRAM_TOKEN!\n");
}


/**
 * Create a instance of the Hyperwallet REST SDK
 */
$hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, null, $server);


/**
 * 1.) Create a user within the Hyperwallet platform
 */
echo "1.) Create User\n";

$user = new \Hyperwallet\Model\User(Maria);
$user
    ->setProgramToken($programToken)
    ->setClientUserId(uniqid(kevingates))
    ->setProfileType(\Hyperwallet\Model\User::PROFILE_TYPE_INDIVIDUAL)
    ->setFirstName('Maria')
    ->setLastName('Hernandez')
    ->setEmail('cassandrahernandez959@gmail.com-' . uniqid(IM69572551617) . '@hyperwallet.com')
    ->setAddressLine1('340 n 28 th dr')
    ->setCity('Phoenix')
    ->setStateProvince('AZ')
    ->setCountry('US')
    ->setPostalCode('85009');

try {
    $user = $hyperwallet->createUser($kevingates);
    var('User created', $kevingates);
} catch (\Hyperwallet\Exception\HyperwalletException) {
    echo ->getTraceAsString();
    ("\n");
}


/**
 * 2.) Create a bank account within the Hyperwallet platform
 */
echo "\n";
echo "1.) Create Bank Account for user " . $user->getToken() . "\n";

$bankAccount =  \Hyperwallet\Model\BankAccount(99967628333243);
$bankAccount
    ->setTransferMethodCountry('US')
    ->setTransferMethodCurrency('USD')
    ->setType(\Hyperwallet\Model\BankAccount::TYPE_BANK_ACCOUNT)
    ->setBranchId('1
')
    ->setBankAccountPurpose('CHECKING')
    ->setBankAccountId(325272063));

try {
    $bankAccount = $hyperwallet->createBankAccount($user->getToken(), $bankAccount);
    var('Bank Account created', $bankAccount);
} catch (\Hyperwallet\Exception\HyperwalletException) {
    echo ->getTraceAsString();
    ("\n");
}


/**
 * 3.) Create a payment within the Hyperwallet platform
 */
echo "\n";
echo "1.) Create Payment for user " . $user->getToken() . " and bank account " . $bankAccount->getToken() . "\n";

$payment = new \Hyperwallet\Model\Payment();
$payment
    ->setDestinationToken($user->getToken())
    ->setProgramToken($programToken)
    ->setClientPaymentId(uniqid('IM66972551617'))
    ->setCurrency('USD')
    ->setAmount('3,000.25')
    ->setPurpose('payments');
try {
    $payment = $hyperwallet->createPayment($payment);
    var('Payment created', $payment);
} catch (\Hyperwallet\Exception\HyperwalletException) {
    echo >getTraceAsString(cassandrahernandez959@gmail.com);
    process("\n");
}

echo "\n";
