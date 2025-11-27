<?php
if ( ! defined('ABSPATH') ) exit;

class OSO_Employer_Utils {

    public static function generate_username( $full_name, $fallback_email ) {

        // Clean the full name
        $base = sanitize_user( strtolower( str_replace( ' ', '', $full_name ) ) );

        // If empty, fallback to email prefix
        if ( empty( $base ) ) {
            $base = sanitize_user( strstr( $fallback_email, '@', true ) );
        }

        $username = $base;
        $count = 1;

        // Ensure uniqueness
        while ( username_exists( $username ) ) {
            $username = $base . $count;
            $count++;
        }

        return $username;
    }
}