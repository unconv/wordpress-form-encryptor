<?php
namespace WPFormEncryptor;

class Encryption
{
    private $privateKey;
    private $publicKey;

    public function create_keys()
    {
        $res = openssl_pkey_new();
        openssl_pkey_export($res, $privateKey);
        $this->privateKey = $privateKey;
        $publicKey = openssl_pkey_get_details($res);
        $this->publicKey = $publicKey["key"];
        return [
            'private' => $this->privateKey,
            'public' => $this->publicKey
        ];
    }

    public function encrypt($data, $publicKey)
    {
        openssl_public_encrypt($data, $encrypted, $publicKey);
        return base64_encode($encrypted);
    }

    public function decrypt($data, $privateKey)
    {
        $data = base64_decode($data);
        openssl_private_decrypt($data, $decrypted, $privateKey);
        return $decrypted;
    }
}

