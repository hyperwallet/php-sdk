<?php

namespace Services\Jose;

use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;
use phpseclib3\Crypt\Hash;
use Services\Jose\Exception\InvalidFormat;
use Services\Jose\Exception\UnexpectedAlgorithm;

class JOSE_JWK
{
    var $components = array();

    function __construct($components = array())
    {
        if (!array_key_exists('kty', $components)) {
            throw new InvalidFormat('"kty" is required');
        }
        $this->components = $components;
        if (!array_key_exists('kid', $this->components)) {
            $this->components['kid'] = $this->thumbprint();
        }
    }

    private function keyParamToBigInteger($param)
    {
        return new BigInteger('0x' . bin2hex(URLSafeBase64::decode($param)), 16);
    }

    function toKey()
    {
        switch ($this->components['kty']) {
            case 'RSA':
                $pemData = RSA::load([
                    'e' => $this->keyParamToBigInteger($this->components['e']),
                    'n' => $this->keyParamToBigInteger($this->components['n']),
                ]);

                if (array_key_exists('d', $this->components)) {
                    throw new UnexpectedAlgorithm('RSA private key isn\'t supported');
                } else {
                    $pem_string = RSA::loadPublicKey($pemData->toString('PKCS1'));
                }
                return RSA::load($pem_string);
            default:
                throw new UnexpectedAlgorithm('Unknown key type');
        }
    }

    function thumbprint($hash_algorithm = 'sha256')
    {
        $hash = new Hash($hash_algorithm);
        return URLSafeBase64::encode(
            $hash->hash(
                json_encode($this->normalized())
            )
        );
    }

    private function normalized()
    {
        switch ($this->components['kty']) {
            case 'RSA':
                return array(
                    'e' => $this->components['e'],
                    'kty' => $this->components['kty'],
                    'n' => $this->components['n']
                );
            default:
                throw new UnexpectedAlgorithm('Unknown key type');
        }
    }

    function toString()
    {
        return json_encode($this->components);
    }

    function __toString()
    {
        return $this->toString();
    }

    static function encode($key, $extra_components = array())
    {
        switch (get_class($key)) {
            case 'phpseclib\Crypt\RSA':
                $components = array(
                    'kty' => 'RSA',
                    'e' => URLSafeBase64::encode($key->publicExponent->toBytes()),
                    'n' => URLSafeBase64::encode($key->modulus->toBytes())
                );
                if ($key->exponent != $key->publicExponent) {
                    $components = array_merge($components, array(
                        'd' => URLSafeBase64::encode($key->exponent->toBytes())
                    ));
                }
                return new self(array_merge($components, $extra_components));
            default:
                throw new UnexpectedAlgorithm('Unknown key type');
        }
    }

    static function decode($components)
    {
        $jwk = new self($components);
        return $jwk->toKey();
    }
}
