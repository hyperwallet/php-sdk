[![Build Status](https://travis-ci.org/hyperwallet/php-sdk.png?branch=master)](https://travis-ci.org/hyperwallet/php-sdk)
[![Coverage Status](https://coveralls.io/repos/github/hyperwallet/php-sdk/badge.svg?branch=master)](https://coveralls.io/github/hyperwallet/php-sdk?branch=master)
[![Latest Stable Version](https://poser.pugx.org/hyperwallet/sdk/version)](https://packagist.org/packages/hyperwallet/sdk)

Hyperwallet REST SDK (Beta)
===========================

A library to manage users, transfer methods and payments through the Hyperwallet v4 API.

For Hyperwallet v3 API calls, please use the latest SDK version 1.x.x. See [here|https://docs.hyperwallet.com/content/updates/v1/rest-api-v4] to learn about the differences between versions and the update process required to use REST API v4.


Prerequisites
------------

Hyperwallet's PHP server SDK requires at minimum PHP 5.6 and above.

Installation
------------

```bash
$ composer require hyperwallet/sdk
```

Documentation
-------------

Documentation is available at http://hyperwallet.github.io/php-sdk.


API Overview
------------

To write an app using the SDK

* Register for a sandbox account and get your username, password and program token at the [Hyperwallet Program Portal](https://portal.hyperwallet.com).
* Add dependency `hyperwallet/sdk` to your `composer.json`.

* Create a instance of the Hyperwallet Client (with username, password and program token)
  ```php
  $client = new \Hyperwallet\Hyperwallet("restapiuser@4917301618", "mySecurePassword!", "prg-645fc30d-83ed-476c-a412-32c82738a20e");
  ```
* Start making API calls (e.g. create a user)
  ```php
  $user = new \Hyperwallet\Model\User();
  $user
    ->setClientUserId('test-client-id-1')
    ->setProfileType(\Hyperwallet\Model\User::PROFILE_TYPE_INDIVIDUAL)
    ->setFirstName('Daffyd')
    ->setLastName('y Goliath')
    ->setEmail('testmail-1@hyperwallet.com')
    ->setAddressLine1('123 Main Street')
    ->setCity('Austin')
    ->setStateProvince('TX')
    ->setCountry('US')
    ->setPostalCode('78701');

  try {
      $createdUser = $client->createUser($user);
  } catch (\Hyperwallet\Exception\HyperwalletException $e) {
      // Add error handling here
  }
  ```
* Error Handling
The `HyperwalletException` has an array of errors with `code`, `message` and `fielName` properties to represent a error.  
  ```php 
    try {
      ... 
    } catch (\Hyperwallet\Exception\HyperwalletException $e) {
      // var_dump($e->getErrorResponse());
      // var_dump($e->getErrorResponse()->getErrors());
      foreach ($e->getErrorResponse()->getErrors() as $error) {
          echo "\n------\n";
          echo $error->getFieldName()."\n";
          echo $error->getCode()."\n";
          echo $error->getMessage()."\n";
      }
    }
  ```

Development
-----------

Run the tests using [`phpunit`](https://phpunit.de/):

```bash
$ composer install
$ ./vendor/bin/phpunit -v
```


Reference
---------

[REST API Reference](https://sandbox.hyperwallet.com/developer-portal/#/docs)


License
-------

[MIT](https://raw.githubusercontent.com/hyperwallet/php-sdk/master/LICENSE)
