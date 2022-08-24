<?php

namespace Hyperwallet\Tests\Util;

use Hyperwallet\Util\HyperwalletUriTemplate;

class HyperwalletUriTemplateTest extends \PHPUnit\Framework\TestCase {

    public function testShouldProcessUriTemplate() {
        $uriTemplate = '/users/{user-token}/paypal-accounts/{paypal-account-token}/status-transitions';
        $userToken = "000-000-000-0";
        $payPalAccountToken = "1111-111-11-11";
        $urlParams = array(
            'user-token' => $userToken,
            'paypal-account-token' => $payPalAccountToken
        );

        $uri = new HyperwalletUriTemplate();
        $result = $uri->expand($uriTemplate, $urlParams);

        $this->assertEquals('/users/000-000-000-0/paypal-accounts/1111-111-11-11/status-transitions', $result);
    }

    public function testShouldReturnMissingVariableIdentifier() {
        $uriTemplate = '/users/{user-token}/paypal-accounts/{paypal-account-token}/status';
        $userToken = "000-000-000-0";
        $urlParams = array(
            'user-token' => $userToken
        );

        $uri = new HyperwalletUriTemplate();
        $result = $uri->expand($uriTemplate, $urlParams);

        $this->assertEquals('/users/000-000-000-0/paypal-accounts/{paypal-account-token}/status', $result);
    }

    public function testShouldReturnAllMissingVariableIdentifier() {
        $uriTemplate = '/users/{user-token}/paypal-accounts/{paypal-account-token}/status';
        $token = "000-000-000-0";
        $urlParams = array(
            'token' => $token
        );

        $uri = new HyperwalletUriTemplate();
        $result = $uri->expand($uriTemplate, $urlParams);

        $this->assertEquals('/users/{user-token}/paypal-accounts/{paypal-account-token}/status', $result);
    }

    public function testShouldSkipProcessWhenURLParamsIsEmpty() {
        $uriTemplate = '/users/{user-token}/paypal-accounts/{paypal-account-token}/status';
        $urlParams = array();

        $uri = new HyperwalletUriTemplate();
        $result = $uri->expand($uriTemplate, $urlParams);

        $this->assertEquals('/users/{user-token}/paypal-accounts/{paypal-account-token}/status', $result);
    }

    public function testShouldSkipProcessWhenUriTemplateDoesHaveStartBrace() {
        $uriTemplate = '/users/status';
        $urlParams = array();

        $uri = new HyperwalletUriTemplate();
        $result = $uri->expand($uriTemplate, $urlParams);

        $this->assertEquals('/users/status', $result);
    }

}