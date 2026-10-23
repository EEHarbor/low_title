<?php

namespace Low\Title\FluxCapacitor\Conduit;

/**
 * Observe the original three-argument call while delegating unchanged to OpenSSL.
 *
 * @param string $data
 * @param string|null $encryptedData
 * @param mixed $publicKey
 * @return bool
 */
function openssl_public_encrypt($data, &$encryptedData, $publicKey)
{
    $success = \openssl_public_encrypt($data, $encryptedData, $publicKey);
    $GLOBALS['low_title_encryptions'][] = array('data' => $data, 'success' => $success, 'bytes' => is_string($encryptedData) ? strlen($encryptedData) : null);

    return $success;
}
