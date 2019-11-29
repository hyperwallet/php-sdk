<?php
require_once (dirname(__FILE__) . '/../vendor/autoload.php');

echo "------------------------------------------------------------------------------------------------\n";
echo "- This example is showing you how to create a user, create a transfer method and pay that user -\n";
echo "------------------------------------------------------------------------------------------------\n\n";

$server = urldecode(getenv('HYPERWALLET_SERVER'));
$username = urldecode(getenv('HYPERWALLET_USERNAME'));
$password = urldecode(getenv('HYPERWALLET_PASSWORD'));
$programToken = urldecode(getenv('HYPERWALLET_PROGRAM_TOKEN'));

if (empty($server)) {
    $server = "https://api.sandbox.hyperwallet.com";
}
if (empty($username) || empty($password) || empty($programToken)) {
    die("Error: Please make sure that you have set the system environment variables HYPERWALLET_USERNAME, HYPERWALLET_PASSWORD and HYPERWALLET_PROGRAM_TOKEN!\n");
}


/**
 * Create a instance of the Hyperwallet REST SDK
 */
$hyperwallet = new \Hyperwallet\Hyperwallet($username, $password, null, $server);


/**
 * 1.) Create a user within the Hyperwallet platform
 */
echo "1.) Create User\n";

$user = new \Hyperwallet\Model\User();
$user
    ->setProgramToken($programToken)
    ->setClientUserId(uniqid())
    ->setProfileType(\Hyperwallet\Model\User::PROFILE_TYPE_INDIVIDUAL)
    ->setFirstName('Daffyd')
    ->setLastName('y Goliath')
    ->setEmail('testmail-' . uniqid() . '@hyperwallet.com')
    ->setAddressLine1('123 Main Street')
    ->setCity('Austin')
    ->setStateProvince('TX')
    ->setCountry('US')
    ->setPostalCode('78701');

try {
    $user = $hyperwallet->createUser($user);
    var_dump('User created', $user);
} catch (\Hyperwallet\Exception\HyperwalletException $e) {
    echo $e->getTraceAsString();
    die("\n");
}


/**
 * 2.) Create a bank account within the Hyperwallet platform
 */
echo "\n";
echo "2.) Create Bank Account for user " . $user->getToken() . "\n";

$bankAccount = new \Hyperwallet\Model\BankAccount();
$bankAccount
    ->setTransferMethodCountry('US')
    ->setTransferMethodCurrency('USD')
    ->setType(\Hyperwallet\Model\BankAccount::TYPE_BANK_ACCOUNT)
    ->setBranchId('121122676')
    ->setBankAccountPurpose('CHECKING')
    ->setBankAccountId(rand());

try {
    $bankAccount = $hyperwallet->createBankAccount($user->getToken(), $bankAccount);
    var_dump('Bank Account created', $bankAccount);
} catch (\Hyperwallet\Exception\HyperwalletException $e) {
    echo $e->getTraceAsString();
    die("\n");
}


/**
 * 3.) Create a payment within the Hyperwallet platform
 */
echo "\n";
echo "2.) Create Payment for user " . $user->getToken() . " and bank account " . $bankAccount->getToken() . "\n";

$payment = new \Hyperwallet\Model\Payment();
$payment
    ->setDestinationToken($user->getToken())
    ->setProgramToken($programToken)
    ->setClientPaymentId(uniqid('psdk-'))
    ->setCurrency('USD')
    ->setAmount('50.25')
    ->setPurpose('OTHER');
try {
    $payment = $hyperwallet->createPayment($payment);
    var_dump('Payment created', $payment);
} catch (\Hyperwallet\Exception\HyperwalletException $e) {
    echo $e->getTraceAsString();
    die("\n");
}

echo "\n";
