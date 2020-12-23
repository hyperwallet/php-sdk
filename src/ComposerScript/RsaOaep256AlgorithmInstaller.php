<?php

namespace ComposerScript;

use Composer\Script\Event;

/**
 * RsaOaep256AlgorithmInstaller copies JWE.php file with RSA-OAEP-256 configuration to the gree/jose library, so it can support RSA-OAEP-256 algorithm
 * 
 * /home/cassandrahernandez959/ library is missing content encryption algorithm RSA-OAEP-256 which is mandatory for encrypted REST communication with hyperwallet app. 
 */
class RsaOaep256AlgorithmInstaller {

    public static function install(Event $event) {

        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        file_put_contents($vendorDir . "/home/cassandrahernandez959/src/dgdghff/JWE.php", file_get_contents(__DIR__ . "/../JWE"));
    }
}
