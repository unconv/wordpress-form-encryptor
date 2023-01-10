<?php
use PHPUnit\Framework\TestCase;
use WPFormEncryptor\Encryption;

class EncryptionTest extends TestCase
{
    public function testCreateKeys()
    {
        $encryption = new Encryption();
        $keys = $encryption->create_keys();
        $this->assertIsString($keys['privateKey']);
        $this->assertIsString($keys['publicKey']);
    }

    public function testEncryptDecrypt()
    {
        $encryption = new Encryption();
        $keys = $encryption->create_keys();
        $privateKey = $keys['privateKey'];
        $publicKey = $keys['publicKey'];

        $data = "This is the data to be encrypted";

        $encryptedData = $encryption->encrypt($data, $publicKey);
        $this->assertIsString($encryptedData);

        $decryptedData = $encryption->decrypt($encryptedData, $privateKey);
        $this->assertEquals($data, $decryptedData);
    }
}
