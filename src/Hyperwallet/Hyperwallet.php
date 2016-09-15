<?php
namespace Hyperwallet;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Exception\HyperwalletArgumentException;
use Hyperwallet\Model\Balance;
use Hyperwallet\Model\BankAccount;
use Hyperwallet\Model\BankAccountStatusTransition;
use Hyperwallet\Model\IProgramAware;
use Hyperwallet\Model\Payment;
use Hyperwallet\Model\PrepaidCard;
use Hyperwallet\Model\PrepaidCardStatusTransition;
use Hyperwallet\Model\Program;
use Hyperwallet\Model\ProgramAccount;
use Hyperwallet\Model\Receipt;
use Hyperwallet\Model\TransferMethodConfiguration;
use Hyperwallet\Model\User;
use Hyperwallet\Model\UserStatusTransition;
use Hyperwallet\Response\ListResponse;
use Hyperwallet\Util\ApiClient;

/**
 * The Hyperwallet SDK Client
 *
 * @package Hyperwallet
 */
class Hyperwallet {

    /**
     * The program token
     *
     * @var string
     */
    private $programToken;

    /**
     * The internal API client
     *
     * @var ApiClient
     */
    private $client;

    /**
     * Creates a instance of the SDK Client
     *
     * @param string $username The API username
     * @param string $password The API password
     * @param string|null $programToken The program token that is used for some API calls
     * @param string $server The API server to connect to
     *
     * @throws HyperwalletArgumentException
     */
    public function __construct($username, $password, $programToken = null, $server = 'https://api.sandbox.hyperwallet.com') {
        if (empty($username) || empty($password)) {
            throw new HyperwalletArgumentException('You need to specify your API username and password!');
        }

        $this->programToken = $programToken;
        $this->client = new ApiClient($username, $password, $server);
    }

    //--------------------------------------
    // Users
    //--------------------------------------

    /**
     * Create a user
     *
     * @param User $user The user data
     * @return User
     *
     * @throws HyperwalletApiException
     */
    public function createUser(User $user) {
        $this->addProgramToken($user);
        $body = $this->client->doPost('/rest/v3/users', array(), $user, array());
        return new User($body);
    }

    /**
     * Get a user
     *
     * @param string $userToken The user token
     * @return User
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function getUser($userToken) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/users/{user-token}', array('user-token' => $userToken), array());
        return new User($body);
    }

    /**
     * Update a user
     *
     * @param User $user The user
     * @return User
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function updateUser(User $user) {
        if (!$user->getToken()) {
            throw new HyperwalletArgumentException('token is required!');
        }
        $body = $this->client->doPut('/rest/v3/users/{user-token}', array('user-token' => $user->getToken()), $user, array());
        return new User($body);
    }

    /**
     * List all users
     *
     * @param array $options
     * @return ListResponse
     *
     * @throws HyperwalletApiException
     */
    public function listUsers($options = array()) {
        $body = $this->client->doGet('/rest/v3/users', array(), $options);
        return new ListResponse($body, function($entry) {
            return new User($entry);
        });
    }

    /**
     * Pre activate a user
     *
     * @param string $userToken The user token
     * @return UserStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function preActivateUser($userToken) {
        $transition = new UserStatusTransition();
        $transition->setTransition(UserStatusTransition::TRANSITION_PRE_ACTIVATED);

        return $this->createUserStatusTransition($userToken, $transition);
    }

    /**
     * Activate a user
     *
     * @param string $userToken The user token
     * @return UserStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function activateUser($userToken) {
        $transition = new UserStatusTransition();
        $transition->setTransition(UserStatusTransition::TRANSITION_ACTIVATED);

        return $this->createUserStatusTransition($userToken, $transition);
    }

    /**
     * Lock a user
     *
     * @param string $userToken The user token
     * @return UserStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function lockUser($userToken) {
        $transition = new UserStatusTransition();
        $transition->setTransition(UserStatusTransition::TRANSITION_LOCKED);

        return $this->createUserStatusTransition($userToken, $transition);
    }

    /**
     * Freeze a user
     *
     * @param string $userToken The user token
     * @return UserStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function freezeUser($userToken) {
        $transition = new UserStatusTransition();
        $transition->setTransition(UserStatusTransition::TRANSITION_FROZEN);

        return $this->createUserStatusTransition($userToken, $transition);
    }

    /**
     * De activate a user
     *
     * @param string $userToken The user token
     * @return UserStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function deActivateUser($userToken) {
        $transition = new UserStatusTransition();
        $transition->setTransition(UserStatusTransition::TRANSITION_DE_ACTIVATED);

        return $this->createUserStatusTransition($userToken, $transition);
    }

    /**
     * Create a user status transition
     *
     * @param string $userToken The user token
     * @param UserStatusTransition $transition The status transition
     * @return UserStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function createUserStatusTransition($userToken, UserStatusTransition $transition) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }

        $body = $this->client->doPost('/rest/v3/users/{user-token}/status-transitions', array(
            'user-token' => $userToken
        ), $transition, array());
        return new UserStatusTransition($body);
    }

    /**
     * Get a user status transition
     *
     * @param string $userToken The user token
     * @param string $statusTransitionToken The status transition token
     * @return UserStatusTransition
     *
     * @throws HyperwalletArgumentException
     */
    public function getUserStatusTransition($userToken, $statusTransitionToken) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($statusTransitionToken)) {
            throw new HyperwalletArgumentException('statusTransitionToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/status-transitions/{status-transition-token}', array(
            'user-token' => $userToken,
            'status-transition-token' => $statusTransitionToken
        ), array());
        return new UserStatusTransition($body);
    }

    /**
     * List all user status transitions
     *
     * @param string $userToken The user token
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listUserStatusTransitions($userToken, array $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/status-transitions', array(
            'user-token' => $userToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new UserStatusTransition($entry);
        });
    }

    //--------------------------------------
    // Prepaid Cards
    //--------------------------------------

    /**
     * Create a prepaid card
     *
     * @param string $userToken The user token
     * @param PrepaidCard $prepaidCard The prepaid card data
     * @return PrepaidCard
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function createPrepaidCard($userToken, PrepaidCard $prepaidCard) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        $body = $this->client->doPost('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => $userToken), $prepaidCard, array());
        return new PrepaidCard($body);
    }

    /**
     * Get a prepaid card
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @return PrepaidCard
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function getPrepaidCard($userToken, $prepaidCardToken) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($prepaidCardToken)) {
            throw new HyperwalletArgumentException('prepaidCardToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}', array(
            'user-token' => $userToken,
            'prepaid-card-token' => $prepaidCardToken
        ), array());
        return new PrepaidCard($body);
    }

    /**
     * Update a prepaid card
     *
     * @param string $userToken The user token
     * @param PrepaidCard $prepaidCard The prepaid card data
     * @return PrepaidCard
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function updatePrepaidCard($userToken, PrepaidCard $prepaidCard) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (!$prepaidCard->getToken()) {
            throw new HyperwalletArgumentException('token is required!');
        }
        $body = $this->client->doPut('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}', array(
            'user-token' => $userToken,
            'prepaid-card-token' => $prepaidCard->getToken()
        ), $prepaidCard, array());
        return new PrepaidCard($body);
    }

    /**
     * List all prepaid cards
     *
     * @param string $userToken The user token
     * @param array $options The query parameters to send
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listPrepaidCards($userToken, $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/users/{user-token}/prepaid-cards', array('user-token' => $userToken), $options);
        return new ListResponse($body, function($entry) {
            return new PrepaidCard($entry);
        });
    }

    /**
     * Suspend a prepaid card
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function suspendPrepaidCard($userToken, $prepaidCardToken) {
        $transition = new PrepaidCardStatusTransition();
        $transition->setTransition(PrepaidCardStatusTransition::TRANSITION_SUSPENDED);

        return $this->createPrepaidCardStatusTransition($userToken, $prepaidCardToken, $transition);
    }

    /**
     * Unsuspend a prepaid card
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function unsuspendPrepaidCard($userToken, $prepaidCardToken) {
        $transition = new PrepaidCardStatusTransition();
        $transition->setTransition(PrepaidCardStatusTransition::TRANSITION_UNSUSPENDED);

        return $this->createPrepaidCardStatusTransition($userToken, $prepaidCardToken, $transition);
    }

    /**
     * Mark a prepaid card as lost or stolen
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function lostOrStolenPrepaidCard($userToken, $prepaidCardToken) {
        $transition = new PrepaidCardStatusTransition();
        $transition->setTransition(PrepaidCardStatusTransition::TRANSITION_LOST_OR_STOLEN);

        return $this->createPrepaidCardStatusTransition($userToken, $prepaidCardToken, $transition);
    }

    /**
     * Deactivate a prepaid card
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function deactivatePrepaidCard($userToken, $prepaidCardToken) {
        $transition = new PrepaidCardStatusTransition();
        $transition->setTransition(PrepaidCardStatusTransition::TRANSITION_DE_ACTIVATED);

        return $this->createPrepaidCardStatusTransition($userToken, $prepaidCardToken, $transition);
    }

    /**
     * Lock a prepaid card
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function lockPrepaidCard($userToken, $prepaidCardToken) {
        $transition = new PrepaidCardStatusTransition();
        $transition->setTransition(PrepaidCardStatusTransition::TRANSITION_LOCKED);

        return $this->createPrepaidCardStatusTransition($userToken, $prepaidCardToken, $transition);
    }

    /**
     * Unlock a prepaid card
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function unlockPrepaidCard($userToken, $prepaidCardToken) {
        $transition = new PrepaidCardStatusTransition();
        $transition->setTransition(PrepaidCardStatusTransition::TRANSITION_UNLOCKED);

        return $this->createPrepaidCardStatusTransition($userToken, $prepaidCardToken, $transition);
    }

    /**
     * Create a prepaid card status transition
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @param PrepaidCardStatusTransition $transition The status transition
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function createPrepaidCardStatusTransition($userToken, $prepaidCardToken, PrepaidCardStatusTransition $transition) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($prepaidCardToken)) {
            throw new HyperwalletArgumentException('prepaidCardToken is required!');
        }

        $body = $this->client->doPost('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array(
            'user-token' => $userToken,
            'prepaid-card-token' => $prepaidCardToken
        ), $transition, array());
        return new PrepaidCardStatusTransition($body);
    }

    /**
     * Get a prepaid card status transition
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @param string $statusTransitionToken The status transition token
     * @return PrepaidCardStatusTransition
     *
     * @throws HyperwalletArgumentException
     */
    public function getPrepaidCardStatusTransition($userToken, $prepaidCardToken, $statusTransitionToken) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($prepaidCardToken)) {
            throw new HyperwalletArgumentException('prepaidCardToken is required!');
        }
        if (empty($statusTransitionToken)) {
            throw new HyperwalletArgumentException('statusTransitionToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions/{status-transition-token}', array(
            'user-token' => $userToken,
            'prepaid-card-token' => $prepaidCardToken,
            'status-transition-token' => $statusTransitionToken
        ), array());
        return new PrepaidCardStatusTransition($body);
    }

    /**
     * List all prepaid card status transitions
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listPrepaidCardStatusTransitions($userToken, $prepaidCardToken, array $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($prepaidCardToken)) {
            throw new HyperwalletArgumentException('prepaidCardToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/status-transitions', array(
            'user-token' => $userToken,
            'prepaid-card-token' => $prepaidCardToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new PrepaidCardStatusTransition($entry);
        });
    }

    //--------------------------------------
    // Bank Accounts
    //--------------------------------------

    /**
     * Create a bank account
     *
     * @param string $userToken The user token
     * @param BankAccount $bankAccount The bank account data
     * @return BankAccount
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function createBankAccount($userToken, BankAccount $bankAccount) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        $body = $this->client->doPost('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => $userToken), $bankAccount, array());
        return new BankAccount($body);
    }

    /**
     * Get a bank account
     *
     * @param string $userToken The user token
     * @param string $bankAccountToken The bank account token
     * @return BankAccount
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function getBankAccount($userToken, $bankAccountToken) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($bankAccountToken)) {
            throw new HyperwalletArgumentException('bankAccountToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}', array(
            'user-token' => $userToken,
            'bank-account-token' => $bankAccountToken
        ), array());
        return new BankAccount($body);
    }

    /**
     * Update a bank account
     *
     * @param string $userToken The user token
     * @param BankAccount $bankAccount The bank account data
     * @return BankAccount
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function updateBankAccount($userToken, BankAccount $bankAccount) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (!$bankAccount->getToken()) {
            throw new HyperwalletArgumentException('token is required!');
        }
        $body = $this->client->doPut('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}', array(
            'user-token' => $userToken,
            'bank-account-token' => $bankAccount->getToken()
        ), $bankAccount, array());
        return new BankAccount($body);
    }

    /**
     * List all bank accounts
     *
     * @param string $userToken The user token
     * @param array $options The query parameters to send
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listBankAccounts($userToken, $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/users/{user-token}/bank-accounts', array('user-token' => $userToken), $options);
        return new ListResponse($body, function($entry) {
           return new BankAccount($entry);
        });
    }

    /**
     * Deactivate a bank account
     *
     * @param string $userToken The user token
     * @param string $bankAccountToken The bank account token
     * @return BankAccountStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function deactivateBankAccount($userToken, $bankAccountToken) {
        $transition = new BankAccountStatusTransition();
        $transition->setTransition(BankAccountStatusTransition::TRANSITION_DE_ACTIVATED);

        return $this->createBankAccountStatusTransition($userToken, $bankAccountToken, $transition);
    }

    /**
     * Create a bank account status transition
     *
     * @param string $userToken The user token
     * @param string $bankAccountToken The bank account token
     * @param BankAccountStatusTransition $transition The status transition
     * @return BankAccountStatusTransition
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function createBankAccountStatusTransition($userToken, $bankAccountToken, BankAccountStatusTransition $transition) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($bankAccountToken)) {
            throw new HyperwalletArgumentException('bankAccountToken is required!');
        }

        $body = $this->client->doPost('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array(
            'user-token' => $userToken,
            'bank-account-token' => $bankAccountToken
        ), $transition, array());
        return new BankAccountStatusTransition($body);
    }

    /**
     * List all bank account status transitions
     *
     * @param string $userToken The user token
     * @param string $bankAccountToken The bank account token
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listBankAccountStatusTransitions($userToken, $bankAccountToken, array $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($bankAccountToken)) {
            throw new HyperwalletArgumentException('bankAccountToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/bank-accounts/{bank-account-token}/status-transitions', array(
            'user-token' => $userToken,
            'bank-account-token' => $bankAccountToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new BankAccountStatusTransition($entry);
        });
    }

    //--------------------------------------
    // Balances
    //--------------------------------------

    /**
     * List balances for a user
     *
     * @param string $userToken The user token
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     */
    public function listBalancesForUser($userToken, $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/balances', array('user-token' => $userToken), $options);
        return new ListResponse($body, function($entry) {
            return new Balance($entry);
        });
    }

    /**
     * List balances for a prepaid card
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     */
    public function listBalancesForPrepaidCard($userToken, $prepaidCardToken, $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($prepaidCardToken)) {
            throw new HyperwalletArgumentException('prepaidCardToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/balances', array(
            'user-token' => $userToken,
            'prepaid-card-token' => $prepaidCardToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new Balance($entry);
        });
    }

    /**
     * List balances for a program account
     *
     * @param string $programToken The program token
     * @param string $accountToken The account token
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     */
    public function listBalancesForAccount($programToken, $accountToken, $options = array()) {
        if (empty($programToken)) {
            throw new HyperwalletArgumentException('programToken is required!');
        }
        if (empty($accountToken)) {
            throw new HyperwalletArgumentException('accountToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/balances', array(
            'program-token' => $programToken,
            'account-token' => $accountToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new Balance($entry);
        });
    }

    //--------------------------------------
    // Payments
    //--------------------------------------

    /**
     * Create a payment
     *
     * @param Payment $payment The payment
     * @return Payment
     *
     * @throws HyperwalletApiException
     */
    public function createPayment(Payment $payment) {
        $this->addProgramToken($payment);
        $body = $this->client->doPost('/rest/v3/payments', array(), $payment, array());
        return new Payment($body);
    }

    /**
     * Get a payment
     *
     * @param string $paymentToken The payment token
     * @return Payment
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function getPayment($paymentToken) {
        if (empty($paymentToken)) {
            throw new HyperwalletArgumentException('paymentToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/payments/{payment-token}', array('payment-token' => $paymentToken), array());
        return new Payment($body);
    }

    /**
     * List all payments
     *
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletApiException
     */
    public function listPayments($options = array()) {
        $body = $this->client->doGet('/rest/v3/payments', array(), $options);
        return new ListResponse($body, function($entry) {
            return new Payment($entry);
        });
    }

    //--------------------------------------
    // Programs
    //--------------------------------------

    /**
     * Get a program
     *
     * @param string $programToken The program token
     * @return Program
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function getProgram($programToken) {
        if (empty($programToken)) {
            throw new HyperwalletArgumentException('programToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/programs/{program-token}', array('program-token' => $programToken), array());
        return new Program($body);
    }

    //--------------------------------------
    // Program Accounts
    //--------------------------------------

    /**
     * Get a program account
     *
     * @param string $programToken The program token
     * @param string $accountToken The account token
     * @return ProgramAccount
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function getProgramAccount($programToken, $accountToken) {
        if (empty($programToken)) {
            throw new HyperwalletArgumentException('programToken is required!');
        }
        if (empty($accountToken)) {
            throw new HyperwalletArgumentException('accountToken is required!');
        }
        $body = $this->client->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}', array(
            'program-token' => $programToken,
            'account-token' => $accountToken
        ), array());
        return new ProgramAccount($body);
    }

    //--------------------------------------
    // Transfer Method Configurations
    //--------------------------------------

    /**
     * Get a transfer method configuration
     *
     * @param string $userToken The user token
     * @param string $country The transfer method country
     * @param string $currency The transfer method currency
     * @param string $type The transfer method type
     * @param string $profileType The profile type
     * @return TransferMethodConfiguration
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function getTransferMethodConfiguration($userToken, $country, $currency, $type, $profileType) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($country)) {
            throw new HyperwalletArgumentException('country is required!');
        }
        if (empty($currency)) {
            throw new HyperwalletArgumentException('currency is required!');
        }
        if (empty($type)) {
            throw new HyperwalletArgumentException('type is required!');
        }
        if (empty($profileType)) {
            throw new HyperwalletArgumentException('profileType is required!');
        }

        $body = $this->client->doGet('/rest/v3/transfer-method-configurations', array(), array(
            'userToken' => $userToken,
            'country' => $country,
            'currency' => $currency,
            'type' => $type,
            'profileType' => $profileType
        ));
        return new TransferMethodConfiguration($body);
    }

    /**
     * List all transfer method configurations
     *
     * @param string $userToken The user token
     * @param array $options The query parameters
     * @return ListResponse
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listTransferMethodConfigurations($userToken, $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/transfer-method-configurations', array(), array_merge(array(
            'userToken' => $userToken,
        ), $options));
        return new ListResponse($body, function ($entity) {
            return new TransferMethodConfiguration($entity);
        });
    }

    //--------------------------------------
    // Receipts
    //--------------------------------------

    /**
     * List all program account receipts
     *
     * @param string $programToken The program token
     * @param string $accountToken The program account token
     * @param array $options The query parameters
     * @return ListResponse of HyperwalletReceipt
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listReceiptsForProgramAccount($programToken, $accountToken, $options = array()) {
        if (empty($programToken)) {
            throw new HyperwalletArgumentException('programToken is required!');
        }
        if (empty($accountToken)) {
            throw new HyperwalletArgumentException('accountToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/programs/{program-token}/accounts/{account-token}/receipts', array(
            'program-token' => $programToken,
            'account-token' => $accountToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new Receipt($entry);
        });
    }

    /**
     * List all user receipts
     *
     * @param string $userToken The user token
     * @param array $options The query parameters
     * @return ListResponse of HyperwalletReceipt
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listReceiptsForUser($userToken, $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/receipts', array(
            'user-token' => $userToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new Receipt($entry);
        });
    }

    /**
     * List all prepaid card receipts
     *
     * @param string $userToken The user token
     * @param string $prepaidCardToken The prepaid card token
     * @param array $options The query parameters
     * @return ListResponse of HyperwalletReceipt
     *
     * @throws HyperwalletArgumentException
     * @throws HyperwalletApiException
     */
    public function listReceiptsForPrepaidCard($userToken, $prepaidCardToken, $options = array()) {
        if (empty($userToken)) {
            throw new HyperwalletArgumentException('userToken is required!');
        }
        if (empty($prepaidCardToken)) {
            throw new HyperwalletArgumentException('prepaidCardToken is required!');
        }

        $body = $this->client->doGet('/rest/v3/users/{user-token}/prepaid-cards/{prepaid-card-token}/receipts', array(
            'user-token' => $userToken,
            'prepaid-card-token' => $prepaidCardToken
        ), $options);
        return new ListResponse($body, function($entry) {
            return new Receipt($entry);
        });
    }

    //--------------------------------------
    // Internal utils
    //--------------------------------------

    /**
     * Add program token if global specified
     *
     * @param IProgramAware $model The model
     */
    private function addProgramToken(IProgramAware $model) {
        if (empty($this->programToken)) {
            return;
        }
        if ($model->getProgramToken()) {
            return;
        }
        $model->setProgramToken($this->programToken);
    }

}
