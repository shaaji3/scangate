<?php

class URLUtils {
    private static $cipher_algo = 'AES-256-CBC';

    /**
     * Encrypts a string (e.g., an ID).
     *
     * @param string $plaintext The string to encrypt.
     * @return string|false The encrypted string (URL-safe) or false on failure.
     */
    public static function encrypt(string $plaintext) {
        $key = getenv('APP_KEY');
        if (empty($key) || mb_strlen($key, '8bit') !== 32) {
            // In a real app, this should be a fatal error during development.
            error_log("APP_KEY is not set or is not 32 bytes long.");
            return false;
        }

        $iv_length = openssl_cipher_iv_length(self::$cipher_algo);
        $iv = openssl_random_pseudo_bytes($iv_length);

        $ciphertext = openssl_encrypt($plaintext, self::$cipher_algo, $key, OPENSSL_RAW_DATA, $iv);

        // Prepend the IV to the ciphertext and base64 encode for URL safety.
        return rawurlencode(base64_encode($iv . $ciphertext));
    }

    /**
     * Decrypts a string.
     *
     * @param string $encrypted_string The encrypted string from the URL.
     * @return string|false The original plaintext string or false on failure.
     */
    public static function decrypt(string $encrypted_string) {
        $key = getenv('APP_KEY');
        if (empty($key) || mb_strlen($key, '8bit') !== 32) {
            error_log("APP_KEY is not set or is not 32 bytes long.");
            return false;
        }

        $data = base64_decode(rawurldecode($encrypted_string));
        $iv_length = openssl_cipher_iv_length(self::$cipher_algo);
        $iv = substr($data, 0, $iv_length);
        $ciphertext = substr($data, $iv_length);

        if ($iv === false || $ciphertext === false) {
            return false;
        }

        return openssl_decrypt($ciphertext, self::$cipher_algo, $key, OPENSSL_RAW_DATA, $iv);
    }
}
