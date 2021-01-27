<?php
namespace Hyperwallet\Tests\Util;

use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Util\HyperwalletEncryption;

class HyperwalletEncryptionTest extends \PHPUnit_Framework_TestCase {

    public function testShouldSuccessfullyEncryptAndDecryptTextMessage() {
        // Setup data
        $clientPath = __DIR__ . "/../../../resources/private-jwkset1";
        $hyperwalletPath = __DIR__ . "/../../../resources/public-jwkset1";
        $originalMessage = "Test message";
        $encryption = new HyperwalletEncryption($clientPath, $hyperwalletPath);
        $encryptedMessage = $encryption->encrypt($originalMessage);

        // Execute test
        $decryptedMessage = $encryption->decrypt($encryptedMessage);

        // Validate result
        $this->assertEquals($originalMessage, $decryptedMessage['scalar']);
    }

    public function testShouldFailDecryptionWhenWrongPrivateKeyIsUsed() {
        // Setup data
        $clientPath1 = __DIR__ . "/../../../resources/private-jwkset1";
        $hyperwalletPath1 = __DIR__ . "/../../../resources/public-jwkset1";
        $clientPath2 = __DIR__ . "/../../../resources/private-jwkset2";
        $hyperwalletPath2 = __DIR__ . "/../../../resources/public-jwkset2";
        $originalMessage = "Test message";
        $encryption1 = new HyperwalletEncryption($clientPath1, $hyperwalletPath1);
        $encryption2 = new HyperwalletEncryption($clientPath2, $hyperwalletPath2);
        $encryptedMessage = $encryption1->encrypt($originalMessage);

        // Execute test
        try {
            $encryption2->decrypt($encryptedMessage);
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            $this->assertThat($e->getMessage(), $this->logicalOr(
                $this->equalTo('Decryption error'),
                $this->equalTo('Payload decryption failed'),
                $this->equalTo('Ciphertext representative out of range')
            ));
        }
    }

    public function testShouldFailSignatureVerificationWhenWrongPublicKeyIsUsed() {
        // Setup data
        $clientPath1 = __DIR__ . "/../../../resources/private-jwkset1";
        $hyperwalletPath1 = __DIR__ . "/../../../resources/public-jwkset1";
        $hyperwalletPath2 = __DIR__ . "/../../../resources/public-jwkset2";
        $originalMessage = "Test message";
        $encryption1 = new HyperwalletEncryption($clientPath1, $hyperwalletPath1);
        $encryption2 = new HyperwalletEncryption($clientPath1, $hyperwalletPath2);
        $encryptedMessage = $encryption1->encrypt($originalMessage);

        // Execute test
        try {
            $encryption2->decrypt($encryptedMessage);
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('Signature verification failed', $e->getMessage());
        }
    }

    public function testShouldThrowExceptionWhenWrongJwkKeySetLocationIsGiven() {
        // Setup data
        $clientPath = "wrong_keyset_path";
        $hyperwalletPath = __DIR__ . "/../../../resources/public-jwkset1";
        $originalMessage = "Test message";
        $encryption = new HyperwalletEncryption($clientPath, $hyperwalletPath);

        // Execute test
        try {
            $encryption->encrypt($originalMessage);
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('Wrong JWK key set location path = wrong_keyset_path', $e->getMessage());
        }
    }

    public function testShouldThrowExceptionWhenNotSupportedEncryptionAlgorithmIsGiven() {
        // Setup data
        $clientPath = __DIR__ . "/../../../resources/private-jwkset1";
        $hyperwalletPath = __DIR__ . "/../../../resources/public-jwkset1";
        $originalMessage = "Test message";
        $encryption = new HyperwalletEncryption($clientPath, $hyperwalletPath, 'unsupported_encryption_algorithm');

        // Execute test
        try {
            $encryption->encrypt($originalMessage);
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            $this->assertEquals('JWK set doesn\'t contain key with algorithm = unsupported_encryption_algorithm', $e->getMessage());
        }
    }

    public function testShouldThrowExceptionWhenJwsSignatureDoesNotContainExpHeaderParam() {
        // Setup data
        $header = array(
            "alg" => "RS256",
            "kid" => "2018_sig_rsa_RS256_2048"
        );
        $clientPath = __DIR__ . "/../../../resources/private-jwkset1";
        $hyperwalletPath = __DIR__ . "/../../../resources/public-jwkset1";
        $originalMessage = "Test message";
        $encryption = new HyperwalletEncryption($clientPath, $hyperwalletPath);

        // Execute test
        try {
            $encryptedMessage = $encryption->checkJwsExpiration($header);
            $this->fail('HyperwalletException expected');
        } catch (HyperwalletException $e) {
            $this->assertEquals('While trying to verify JWS signature no [exp] header is found', $e->getMessage());
        }
    }

    public function testShouldThrowExceptionWhenJwsSignatureExpHeaderParamIsNotInteger() {
        // Setup data
        $header = array(
            "alg" => "RS256",
            "exp" => "153356exp",
            "kid" => "2018_sig_rsa_RS256_2048"
        );
        $clientPath = __DIR__ . "/../../../resources/private-jwkset1";
        $hyperwalletPath = __DIR__ . "/../../../resources/public-jwkset1";
        $originalMessage = "Test message";
        $encryption = new HyperwalletEncryption($clientPath, $hyperwalletPath);

        // Execute test
        try {
            $encryptedMessage = $encryption->checkJwsExpiration($header);
            $this->fail('HyperwalletException expected');
        } catch (HyperwalletException $e) {
            $this->assertEquals('Wrong value in [exp] header of JWS signature, must be integer', $e->getMessage());
        }
    }

    public function testShouldThrowExceptionWhenJwsSignatureHasExpired() {
        // Setup data
        $header = array(
            "alg" => "RS256",
            "exp" => time() - 6000,
            "kid" => "2018_sig_rsa_RS256_2048"
        );
        $clientPath = __DIR__ . "/../../../resources/private-jwkset1";
        $hyperwalletPath = __DIR__ . "/../../../resources/public-jwkset1";
        $originalMessage = "Test message";
        $encryption = new HyperwalletEncryption($clientPath, $hyperwalletPath);

        // Execute test
        try {
            $encryptedMessage = $encryption->checkJwsExpiration($header);
            $this->fail('HyperwalletException expected');
        } catch (HyperwalletException $e) {
            $this->assertEquals('JWS signature has expired, checked by [exp] JWS header', $e->getMessage());
        }
    }
}
