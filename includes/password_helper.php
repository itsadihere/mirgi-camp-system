<?php

if (!function_exists('hash_user_password')) {
    function hash_user_password($plainPassword)
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }
}

if (!function_exists('is_legacy_md5_hash')) {
    function is_legacy_md5_hash($storedHash)
    {
        return is_string($storedHash) && preg_match('/^[a-f0-9]{32}$/i', $storedHash) === 1;
    }
}

if (!function_exists('verify_user_password')) {
    function verify_user_password($plainPassword, $storedHash)
    {
        if (!is_string($storedHash) || $storedHash === '') {
            return false;
        }

        if (is_legacy_md5_hash($storedHash)) {
            return hash_equals(strtolower($storedHash), md5($plainPassword));
        }

        return password_verify($plainPassword, $storedHash);
    }
}

if (!function_exists('password_needs_upgrade')) {
    function password_needs_upgrade($storedHash)
    {
        if (is_legacy_md5_hash($storedHash)) {
            return true;
        }

        return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
    }
}
?>
