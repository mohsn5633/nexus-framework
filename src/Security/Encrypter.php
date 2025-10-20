<?php

namespace Nexus\Security;

/**
 * Encrypter
 *
 * Provides secure encryption and decryption functionality using OpenSSL
 */
class Encrypter
{
    protected string $key;
    protected string $cipher = 'AES-256-CBC';

    /**
     * @param string $key Encryption key (must be 32 bytes for AES-256)
     * @param string $cipher Cipher method (default: AES-256-CBC)
     */
    public function __construct(string $key, string $cipher = 'AES-256-CBC')
    {
        if (!$this->supported($key, $cipher)) {
            throw new \RuntimeException('The given encryption key or cipher is invalid.');
        }

        $this->key = $key;
        $this->cipher = $cipher;
    }

    /**
     * Check if the given key and cipher combination is valid
     *
     * @param string $key
     * @param string $cipher
     * @return bool
     */
    public function supported(string $key, string $cipher): bool
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
               ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Generate a random encryption key
     *
     * @param string $cipher
     * @return string
     */
    public static function generateKey(string $cipher = 'AES-256-CBC'): string
    {
        return random_bytes($cipher === 'AES-128-CBC' ? 16 : 32);
    }

    /**
     * Encrypt a string value
     *
     * @param mixed $value Value to encrypt
     * @param bool $serialize Whether to serialize the value before encryption
     * @return string
     */
    public function encrypt(mixed $value, bool $serialize = true): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));

        $value = \openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        if ($value === false) {
            throw new \RuntimeException('Could not encrypt the data.');
        }

        // Create a MAC for the encrypted value
        $mac = $this->hash($iv, $value);

        $json = json_encode([
            'iv' => base64_encode($iv),
            'value' => base64_encode($value),
            'mac' => $mac,
        ], JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Encrypt a string without serialization
     *
     * @param string $value
     * @return string
     */
    public function encryptString(string $value): string
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt a string value
     *
     * @param string $payload Encrypted payload
     * @param bool $unserialize Whether to unserialize the decrypted value
     * @return mixed
     */
    public function decrypt(string $payload, bool $unserialize = true): mixed
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        // Verify MAC
        if (!$this->validMac($payload)) {
            throw new \RuntimeException('The MAC is invalid.');
        }

        $decrypted = \openssl_decrypt(
            base64_decode($payload['value']),
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Decrypt a string without unserialization
     *
     * @param string $payload
     * @return string
     */
    public function decryptString(string $payload): string
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Get the JSON payload from the given encrypted string
     *
     * @param string $payload
     * @return array
     */
    protected function getJsonPayload(string $payload): array
    {
        $payload = json_decode(base64_decode($payload), true);

        if (!$this->validPayload($payload)) {
            throw new \RuntimeException('The payload is invalid.');
        }

        return $payload;
    }

    /**
     * Verify that the encryption payload is valid
     *
     * @param mixed $payload
     * @return bool
     */
    protected function validPayload(mixed $payload): bool
    {
        return is_array($payload) &&
               isset($payload['iv'], $payload['value'], $payload['mac']) &&
               strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
    }

    /**
     * Determine if the MAC for the given payload is valid
     *
     * @param array $payload
     * @return bool
     */
    protected function validMac(array $payload): bool
    {
        return hash_equals(
            $this->hash(base64_decode($payload['iv']), base64_decode($payload['value'])),
            $payload['mac']
        );
    }

    /**
     * Create a MAC for the given value
     *
     * @param string $iv
     * @param string $value
     * @return string
     */
    protected function hash(string $iv, string $value): string
    {
        return hash_hmac('sha256', base64_encode($iv) . $value, $this->key);
    }

    /**
     * Get the encryption key
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Hash a password using bcrypt
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    public static function hashPassword(string $value, array $options = []): string
    {
        $cost = $options['cost'] ?? 10;

        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);

        if ($hash === false) {
            throw new \RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    /**
     * Verify a password against a hash
     *
     * @param string $value
     * @param string $hashedValue
     * @return bool
     */
    public static function verifyPassword(string $value, string $hashedValue): bool
    {
        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the given hash needs to be rehashed
     *
     * @param string $hashedValue
     * @param array $options
     * @return bool
     */
    public static function needsRehash(string $hashedValue, array $options = []): bool
    {
        $cost = $options['cost'] ?? 10;

        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
