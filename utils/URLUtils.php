<?php

class URLUtils {
    // Methods for URL manipulation, e.g., encryption/decryption of IDs
    public static function encrypt($id) {
        // Encryption logic
        return base64_encode($id);
    }

    public static function decrypt($encrypted_id) {
        // Decryption logic
        return base64_decode($encrypted_id);
    }
}
