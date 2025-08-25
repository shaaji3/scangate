<?php

class URLUtils
{
    private static $cipher_algo = 'AES-256-CBC';
    private static $allowedDomains = ['localhost', 'uhms.local'];

    /**
     * Encrypts a string (e.g., an ID).
     *
     * @param string $plaintext The string to encrypt.
     * @return string|false The encrypted string (URL-safe) or false on failure.
     */
    public static function encrypt(string $plaintext)
    {
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
    public static function decrypt(string $encrypted_string)
    {
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

    /**
     * Handles redirection logic based on the presence of a return_url parameter.
     *
     * If return_url is present in the query, redirects to its decoded value.
     * If not, redirects to the target URL with the current URL as return_url parameter (if targetUrl is provided),
     * or to the fallback URL if provided.
     *
     * @param string|null $target The URL to redirect to if return_url is not present. If null, fallbackUrl is used.
     * @param string|null $fallback The fallback URL if neither return_url nor targetUrl is provided.
     */
    public static function handleRedirect(?string $target = null, ?string $fallback = null): void
    {
        if (isset($_GET['return_url'])) {
            $returnUrl = self::extractQueryParam(self::getCurrentUrl());
            $decodedUrl = urldecode($returnUrl);
            self::executeRedirect($decodedUrl);
        } elseif ($target !== null) {
            $currentUrl = urlencode(self::getCurrentUrl());
            $redirectUrl = $target . (strpos($target, '?') === false ? '?' : '&') . "return_url=$currentUrl";
            self::executeRedirect($redirectUrl);
        } elseif ($fallback !== null) {
            self::executeRedirect($fallback);
        }
    }

    /**
     * Validates the given URL against allowed domains.
     *
     * @param string $url The URL to validate.
     * @return bool True if valid, false otherwise.
     */
    private static function isDomainAllowed(string $url): bool
    {
        $parsedUrl = parse_url(self::getCurrentUrl());
        return isset($parsedUrl['host']) && in_array($parsedUrl['host'], self::$allowedDomains);
    }

    /**
     * Extracts a query parameter value from the given URL.
     *
     * @param string $url The URL to extract the parameter from.
     * @return string|null The parameter value or null if not found.
     */
    private static function extractQueryParam(string $url): ?string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $query = parse_url($url, PHP_URL_QUERY);
        return isset($query) ? explode("=", $query, 2)[1] : null;
    }

    /**
     * Executes the HTTP redirect.
     *
     * @param string $url The URL to redirect to.
     */
    private static function executeRedirect(string $url): void
    {
        header("Location: $url");
        exit();
    }

    /**
     * Retrieves the current URL.
     *
     * @return string The current URL.
     */
    private static function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];

        return $protocol . $host . $requestUri;
    }
}
