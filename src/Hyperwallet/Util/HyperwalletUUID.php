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
use Composer\Autoload\ClassLoader;
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
class HyperwalletUUID {

    /**
     * Generates UUID
     *
     * @return string
     *
     * @throws HyperwalletException
     */
    public static function v4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
