<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * DataEncryptionService - Centralized encryption service for the Capstone System
 * 
 * This service provides AES-256-CBC encryption for sensitive data with proper
 * error handling and key management.
 */
class DataEncryptionService
{
    private $cipher = 'AES-256-CBC';
    private $key;
    private $userDataKey;
    
    public function __construct()
    {
        // Primary encryption key for general data
        $this->key = hash('sha256', config('app.encryption_key', env('APP_ENCRYPTION_KEY', 'default-key-change-this')));
        
        // Secondary key specifically for user data
        $this->userDataKey = hash('sha256', config('app.user_data_key', env('USER_DATA_ENCRYPTION_KEY', 'user-key-change-this')));
    }

    /**
     * Encrypt data using AES-256-CBC
     * 
     * @param string $data Data to encrypt
     * @param string $keyType Type of key to use ('general' or 'user')
     * @return string|null Encrypted data or null on failure
     */
    public function encrypt($data, $keyType = 'general')
    {
        if (empty($data)) {
            return null;
        }

        try {
            $key = $keyType === 'user' ? $this->userDataKey : $this->key;
            
            // Generate a random initialization vector
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
            
            if ($iv === false) {
                throw new Exception('Failed to generate initialization vector');
            }

            // Encrypt the data
            $encrypted = openssl_encrypt($data, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
            
            if ($encrypted === false) {
                throw new Exception('Encryption failed');
            }

            // Return base64 encoded string with IV prepended
            return base64_encode($iv . $encrypted);

        } catch (Exception $e) {
            Log::error('Encryption failed: ' . $e->getMessage(), [
                'data_length' => strlen($data),
                'key_type' => $keyType
            ]);
            return null;
        }
    }

    /**
     * Decrypt data using AES-256-CBC
     * 
     * @param string $encryptedData Base64 encoded encrypted data
     * @param string $keyType Type of key to use ('general' or 'user')
     * @return string|null Decrypted data or null on failure
     */
    public function decrypt($encryptedData, $keyType = 'general')
    {
        if (empty($encryptedData)) {
            return null;
        }

        try {
            $key = $keyType === 'user' ? $this->userDataKey : $this->key;
            
            // Decode base64
            $data = base64_decode($encryptedData, true);
            
            if ($data === false) {
                throw new Exception('Invalid base64 data');
            }

            // Extract IV and encrypted data
            $ivLength = openssl_cipher_iv_length($this->cipher);
            
            if (strlen($data) < $ivLength) {
                throw new Exception('Data too short to contain valid IV');
            }

            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);

            // Decrypt the data
            $decrypted = openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
            
            if ($decrypted === false) {
                throw new Exception('Decryption failed');
            }

            return $decrypted;

        } catch (Exception $e) {
            Log::error('Decryption failed: ' . $e->getMessage(), [
                'data_length' => strlen($encryptedData),
                'key_type' => $keyType
            ]);
            return null;
        }
    }

    /**
     * Encrypt user-specific sensitive data (PII)
     * 
     * @param string $data Data to encrypt
     * @return string|null Encrypted data or null on failure
     */
    public function encryptUserData($data)
    {
        return $this->encrypt($data, 'user');
    }

    /**
     * Decrypt user-specific sensitive data (PII)
     * 
     * @param string $encryptedData Encrypted data to decrypt
     * @return string|null Decrypted data or null on failure
     */
    public function decryptUserData($encryptedData)
    {
        return $this->decrypt($encryptedData, 'user');
    }

    /**
     * Check if data appears to be encrypted (base64 encoded)
     * 
     * @param string $data Data to check
     * @return bool True if data appears encrypted
     */
    public function isEncrypted($data)
    {
        if (empty($data)) {
            return false;
        }

        // Basic check for base64 encoding pattern
        return base64_encode(base64_decode($data, true)) === $data;
    }

    /**
     * Safely encrypt data only if it's not already encrypted
     * 
     * @param string $data Data to encrypt
     * @param string $keyType Type of key to use
     * @return string|null Encrypted data or original if already encrypted
     */
    public function safeEncrypt($data, $keyType = 'general')
    {
        if ($this->isEncrypted($data)) {
            return $data; // Already encrypted
        }
        
        return $this->encrypt($data, $keyType);
    }

    /**
     * Hash data for searching encrypted fields
     * Creates a searchable hash while maintaining privacy
     * 
     * @param string $data Data to hash
     * @return string Hashed data for searching
     */
    public function createSearchHash($data)
    {
        return hash('sha256', strtolower(trim($data)) . config('app.search_salt', 'search-salt'));
    }
}