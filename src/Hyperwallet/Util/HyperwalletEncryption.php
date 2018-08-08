<?php
namespace Hyperwallet\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\UriTemplate;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Model\BaseModel;
use Hyperwallet\Response\ErrorResponse;

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use phpseclib\Crypt\Hash;
use JOSE_URLSafeBase64;
use JOSE_JWS;
use JOSE_JWE;
use JOSE_JWK;
use JOSE_JWT;

/**
 * The encryption service for Hyperwallet client's requests/responses
 *
 * @package Hyperwallet\Util
 */
class HyperwalletEncryption {

    /**
     * String that can be a URL or path to file with client JWK set
     *
     * @var string
     */
    private $clientPrivateKeySetLocation;

    /**
     * String that can be a URL or path to file with hyperwallet JWK set
     *
     * @var string
     */
    private $hyperwalletKeySetLocation;

    /**
     * JWE encryption algorithm, by default value = RSA-OAEP-256
     *
     * @var string
     */
    private $encryptionAlgorithm;

    /**
     * JWS signature algorithm, by default value = RS256
     *
     * @var string
     */
    private $signAlgorithm;

    /**
     * JWE encryption method, by default value = A256CBC-HS512
     *
     * @var string
     */
    private $encryptionMethod;

    /**
     * Minutes when JWS signature is valid, by default value = 5
     *
     * @var integer
     */
    private $jwsExpirationMinutes;

    /**
     * JWS key id header param
     *
     * @var string
     */
    private $jwsKid;

    /**
     * JWE key id header param
     *
     * @var string
     */
    private $jweKid;

    /**
     * Creates a instance of the HyperwalletEncryption
     *
     * @param string $clientPrivateKeySetLocation String that can be a URL or path to file with client JWK set
     * @param string $hyperwalletKeySetLocation String that can be a URL or path to file with hyperwallet JWK set
     * @param string $encryptionAlgorithm JWE encryption algorithm, by default value = RSA-OAEP-256
     * @param array $signAlgorithm JWS signature algorithm, by default value = RS256
     * @param array $encryptionMethod JWE encryption method, by default value = A256CBC-HS512
     * @param array $jwsExpirationMinutes Minutes when JWS signature is valid, by default value = 5
     */
    public function __construct($clientPrivateKeySetLocation, $hyperwalletKeySetLocation,
                $encryptionAlgorithm = 'RSA-OAEP-256', $signAlgorithm = 'RS256', $encryptionMethod = 'A256CBC-HS512',
                $jwsExpirationMinutes = 5) {
        $this->clientPrivateKeySetLocation = $clientPrivateKeySetLocation;
        $this->hyperwalletKeySetLocation = $hyperwalletKeySetLocation;
        $this->encryptionAlgorithm = $encryptionAlgorithm;
        $this->signAlgorithm = $signAlgorithm;
        $this->encryptionMethod = $encryptionMethod;
        $this->jwsExpirationMinutes = $jwsExpirationMinutes;
        file_put_contents(__DIR__ . "/../../../vendor/gree/jose/src/JOSE/JWE.php", file_get_contents(__DIR__ . "/../../JWE"));
    }

    /**
     * Makes an encrypted request : 1) signs the request body; 2) encrypts payload after signature
     *
     * @param string $body The request body to be encrypted
     * @return string
     *
     * @throws HyperwalletException
     */
    public function encrypt($body) {
        $privateJwsKey = $this->getPrivateJwsKey();
        $jws = new JOSE_JWS(new JOSE_JWT($body));
        $jws->header['exp'] = $this->getSignatureExpirationTime();
        $jws->header['kid'] = $this->jwsKid;
        $jws->sign($privateJwsKey, $this->signAlgorithm);

        $publicJweKey = $this->getPublicJweKey();
        $jwe = new JOSE_JWE($jws);
        $jwe->header['kid'] = $this->jweKid;
        $jwe->encrypt($publicJweKey, $this->encryptionAlgorithm, $this->encryptionMethod);
        return $jwe->toString();
    }

    /**
     * Decrypts encrypted response : 1) decrypts the request body; 2) verifies the payload signature
     *
     * @param string $body The response body to be decrypted
     * @return string
     *
     * @throws HyperwalletException
     */
    public function decrypt($body) {
        $privateJweKey = $this->getPrivateJweKey();
        $jwe = JOSE_JWT::decode($body);
        $decryptedBody = $jwe->decrypt($privateJweKey);

        $publicJwsKey = $this->getPublicJwsKey();
        $jwsToVerify = JOSE_JWT::decode($decryptedBody->plain_text);
        $this->checkJwsExpiration($jwsToVerify->header);
        $jwsVerificationResult = $jwsToVerify->verify($publicJwsKey, $this->signAlgorithm);
        return $jwsVerificationResult->claims;
    }

    /**
     * Retrieves JWS RSA private key with algorithm = $this->signAlgorithm
     *
     * @return RSA
     *
     * @throws HyperwalletException
     */
    private function getPrivateJwsKey() {
        $privateKeyData = $this->getJwk($this->clientPrivateKeySetLocation, $this->signAlgorithm);
        $this->jwsKid = $privateKeyData['kid'];
        return $this->getPrivateKey($privateKeyData);
    }

    /**
     * Retrieves JWE RSA public key with algorithm = $this->encryptionAlgorithm
     *
     * @return RSA
     *
     * @throws HyperwalletException
     */
    private function getPublicJweKey() {
        $publicKeyData = $this->getJwk($this->hyperwalletKeySetLocation, $this->encryptionAlgorithm);
        $this->jweKid = $publicKeyData['kid'];
        return $this->getPublicKey($this->convertPrivateKeyToPublic($publicKeyData));
    }

    /**
     * Retrieves JWE RSA private key with algorithm = $this->encryptionAlgorithm
     *
     * @return RSA
     *
     * @throws HyperwalletException
     */
    private function getPrivateJweKey() {
        $privateKeyData = $this->getJwk($this->clientPrivateKeySetLocation, $this->encryptionAlgorithm);
        return $this->getPrivateKey($privateKeyData);
    }

    /**
     * Retrieves JWS RSA public key with algorithm = $this->signAlgorithm
     *
     * @return RSA
     *
     * @throws HyperwalletException
     */
    private function getPublicJwsKey() {
        $publicKeyData = $this->getJwk($this->hyperwalletKeySetLocation, $this->signAlgorithm);
        return $this->getPublicKey($this->convertPrivateKeyToPublic($publicKeyData));
    }

    /**
     * Retrieves RSA private key by JWK key data
     *
     * @param array $privateKeyData The JWK key data
     * @return RSA
     */
    private function getPrivateKey($privateKeyData) {
        $n = $this->keyParamToBigInteger($privateKeyData['n']);
        $e = $this->keyParamToBigInteger($privateKeyData['e']);
        $d = $this->keyParamToBigInteger($privateKeyData['d']);
        $p = $this->keyParamToBigInteger($privateKeyData['p']);
        $q = $this->keyParamToBigInteger($privateKeyData['q']);
        $qi = $this->keyParamToBigInteger($privateKeyData['qi']);
        $dp = $this->keyParamToBigInteger($privateKeyData['dp']);
        $dq = $this->keyParamToBigInteger($privateKeyData['dq']);
        $primes = array($p, $q);
        $exponents = array($dp, $dq);
        $coefficients = array($qi, $qi);
        array_unshift($primes, "phoney");
        unset($primes[0]);
        array_unshift($exponents, "phoney");
        unset($exponents[0]);
        array_unshift($coefficients, "phoney");
        unset($coefficients[0]);

        $pemData = (new RSA())->_convertPrivateKey($n, $e, $d, $primes, $exponents, $coefficients);
        $privateKey = new RSA();
        $privateKey->loadKey($pemData);
        if ($privateKeyData['alg'] == 'RSA-OAEP-256') {
            $privateKey->setHash('sha256');
            $privateKey->setMGFHash('sha256');
        }
        return $privateKey;
    }

    /**
     * Converts base 64 encoded string to BigInteger
     *
     * @param string $param base 64 encoded string
     * @return BigInteger
     */
    private function keyParamToBigInteger($param) {
        return new BigInteger('0x' . bin2hex(JOSE_URLSafeBase64::decode($param)), 16);
    }

    /**
     * Retrieves RSA public key by JWK key data
     *
     * @param array $publicKeyData The JWK key data
     * @return RSA
     */
    private function getPublicKey($publicKeyData) {
        $publicKeyRaw = new JOSE_JWK($publicKeyData);
        $publicKey = $publicKeyRaw->toKey();
        if ($publicKeyData['alg'] == 'RSA-OAEP-256') {
            $publicKey->setHash('sha256');
            $publicKey->setMGFHash('sha256');
        }
        return $publicKey;
    }

    /**
     * Retrieves JWK key by JWK key set location and algorithm
     *
     * @param string $keySetLocation The location(URL or path to file) of JWK key set
     * @param string $alg The target algorithm
     * @return array
     *
     * @throws HyperwalletException
     */
    private function getJwk($keySetLocation, $alg) {
        if (filter_var($keySetLocation, FILTER_VALIDATE_URL) === FALSE) {
            if (!file_exists($keySetLocation)) {
                throw new HyperwalletException("Wrong JWK key set location path = " . $keySetLocation);
            }
        }
        return $this->findJwkByAlgorithm(json_decode(file_get_contents($keySetLocation), true), $alg);
    }

    /**
     * Retrieves JWK key from JWK key set by given algorithm
     *
     * @param string $jwkSetArray JWK key set
     * @param string $alg The target algorithm
     * @return array
     *
     * @throws HyperwalletException
     */
    private function findJwkByAlgorithm($jwkSetArray, $alg) {
        foreach($jwkSetArray['keys'] as $jwk) {
            if ($alg == $jwk['alg']) {
                return $jwk;
            }
        }
        throw new HyperwalletException("JWK set doesn't contain key with algorithm = " . $alg);
    }

    /**
     * Converts private key to public
     *
     * @param string $jwk JWK key
     * @return array
     */
    private function convertPrivateKeyToPublic($jwk) {
        if (isset($jwk['d'])) {
            unset($jwk['d']);
        }
        if (isset($jwk['p'])) {
            unset($jwk['p']);
        }
        if (isset($jwk['q'])) {
            unset($jwk['q']);
        }
        if (isset($jwk['qi'])) {
            unset($jwk['qi']);
        }
        if (isset($jwk['dp'])) {
            unset($jwk['dp']);
        }
        if (isset($jwk['dq'])) {
            unset($jwk['dq']);
        }
        return $jwk;
    }

    /**
     * Calculates JWS expiration time in seconds
     *
     * @return integer
     */
    private function getSignatureExpirationTime() {
        date_default_timezone_set("UTC");
        $secondsInMinute = 60;
        return time() + $this->jwsExpirationMinutes * $secondsInMinute;
    }

    /**
     * Checks if header 'exp' param has not expired value
     *
     * @param array $header JWS header array
     *
     * @throws HyperwalletException
     */
    public function checkJwsExpiration($header) {
        if(!isset($header['exp'])) {
            throw new HyperwalletException('While trying to verify JWS signature no [exp] header is found');
        }
        $exp = $header['exp'];
        if(!is_numeric($exp)) {
            throw new HyperwalletException('Wrong value in [exp] header of JWS signature, must be integer');
        }
        if((int)time() > (int)$exp) {
            throw new HyperwalletException('JWS signature has expired, checked by [exp] JWS header');
        }
    }
}
