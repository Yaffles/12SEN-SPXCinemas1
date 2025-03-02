<?php

// Import encryption keys
require_once('config.php');

/**
 * static class Cipher
 * 
 * Provides encryption and decryption methods that can be used statically.
 */

class Cipher {

    /**
     * static Method encrypt
     * 
     * @param mixed $data Plaintext to be encrypted
     * @return string Ciphertext, as a string
     */

    public static function encrypt($data=null) {
        if ($data) {
            $first_key = base64_decode(FIRST_KEY);
            $second_key = base64_decode(SECOND_KEY);

            $method = "aes-256-cbc";

            // Generate random initialisation vector
            $iv_length = openssl_cipher_iv_length($method);
            $iv = openssl_random_pseudo_bytes($iv_length);

            // Encryption using AES 256 CBC
            $encrypted_data = openssl_encrypt($data, $method, $first_key, OPENSSL_RAW_DATA, $iv);

            // Hash-based message authentication code
            $encrypted_hash = hash_hmac('sha3-512', $encrypted_data, $second_key, true);
            
            // Convert binary to text using base64 for storage
            $output = base64_encode($iv.$encrypted_hash.$encrypted_data);
            return $output;
        }
        return false;
    }

    /**
     * static Method decrypt
     * 
     * @param string $input Ciphertext to be decrypted, as a string.
     * @return string Plaintext, as a string.
     */

    public static function decrypt($input=null) {
        if ($input) {
            $first_key = base64_decode(FIRST_KEY);
            $second_key = base64_decode(SECOND_KEY);
            $mix = base64_decode($input);
            
            $method = "aes-256-cbc";
           
            // Extract the initialisation vector
            $iv_length = openssl_cipher_iv_length($method);
            $iv = substr($mix, 0, $iv_length);

            // Extract the hash and encrypted data
            $encrypted_hash = substr($mix, $iv_length, 64);
            $encrypted_data = substr($mix, $iv_length + 64);

            // Decrypt the data
            $data = openssl_decrypt($encrypted_data, $method, $first_key, OPENSSL_RAW_DATA, $iv);
            $encrypted_hash_new = hash_hmac('sha3-512', $encrypted_data, $second_key, true);

            // Authenticate the data by comparing the hash
            if (hash_equals($encrypted_hash, $encrypted_hash_new)) {
                return $data;
            }
        }
        return false;
    }
}

?>