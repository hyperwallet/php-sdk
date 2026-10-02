<?php
namespace Hyperwallet\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\UriTemplate\UriTemplate;
use Hyperwallet\Exception\HyperwalletApiException;
use Hyperwallet\Exception\HyperwalletException;
use Hyperwallet\Model\BaseModel;
use Hyperwallet\Response\ErrorResponse;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSCompactSerializer;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWECompactSerializer;

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
     * @param string $signAlgorithm JWS signature algorithm, by default value = RS256
     * @param string $encryptionMethod JWE encryption method, by default value = A256CBC-HS512
     * @param integer $jwsExpirationMinutes Minutes when JWS signature is valid, by default value = 5
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
        $jws = (new JWSBuilder(new AlgorithmManager([new RS256()])))->create()
            ->withPayload($body)
            ->addSignature($privateJwsKey, ['alg' => $this->signAlgorithm, 'kid' => $this->jwsKid, 'exp' => $this->getSignatureExpirationTime()])
            ->build();
        $jwsToken = (new JWSCompactSerializer())->serialize($jws);

        $publicJweKey = $this->getPublicJweKey();
        $jwe = (new JWEBuilder(new AlgorithmManager([new RSAOAEP256(), new A256CBCHS512()])))
            ->create()->withPayload($jwsToken)
            ->withSharedProtectedHeader(['alg' => $this->encryptionAlgorithm, 'enc' => $this->encryptionMethod, 'kid' => $this->jweKid])
            ->addRecipient($publicJweKey)->build();
        return (new JWECompactSerializer())->serialize($jwe, 0);
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
        $serializer = new JWECompactSerializer();
        $jwe = $serializer->unserialize($body);
        $this->checkJweHeaderAlgorithm($jwe->getSharedProtectedHeader());
        (new JWEDecrypter(new AlgorithmManager([new RSAOAEP256(), new A256CBCHS512()]), null))->decryptUsingKey($jwe, $privateJweKey, 0);

        $publicJwsKey = $this->getPublicJwsKey();
        $jwsToVerify = (new JWSCompactSerializer())->unserialize($jwe->getPayload());
        $header = $jwsToVerify->getSignature(0)->getProtectedHeader();
        $this->checkJwsExpiration($header);
        if (!(new JWSVerifier(new AlgorithmManager([new RS256()])))->verifyWithKey($jwsToVerify, $publicJwsKey, 0)) {
            throw new HyperwalletException('Signature verification failed');
        }
        return json_decode($jwsToVerify->getPayload(), true);
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
        return new JWK($privateKeyData);
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
        return new JWK($this->convertPrivateKeyToPublic($publicKeyData));
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
        return new JWK($privateKeyData);
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
        return new JWK($this->convertPrivateKeyToPublic($publicKeyData));
    }

    /**
     * Retrieves RSA private key by JWK key data
     *
     * @param array $privateKeyData The JWK key data
     * @return RSA
     */
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

    /**
     * Checks that the JWE header advertises the key-management algorithm and content-encryption method
     * this client expects, before the header-controlled algorithm is ever used to decrypt with the
     * private key. Prevents an attacker from forcing algorithm downgrade (e.g. to legacy RSA1_5) by
     * tampering with the untrusted alg/enc header fields of an intercepted response.
     *
     * @param array $header JWE header array
     *
     * @throws HyperwalletException
     */
    public function checkJweHeaderAlgorithm($header) {
        if (!isset($header['alg']) || $header['alg'] !== $this->encryptionAlgorithm) {
            throw new HyperwalletException('While trying to decrypt JWE, unexpected [alg] header found');
        }
        if (!isset($header['enc']) || $header['enc'] !== $this->encryptionMethod) {
            throw new HyperwalletException('While trying to decrypt JWE, unexpected [enc] header found');
        }
    }

}
