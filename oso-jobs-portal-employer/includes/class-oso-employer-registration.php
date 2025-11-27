<?php
if ( ! defined('ABSPATH') ) exit;

class OSO_Employer_Registration {

    // WPForms employer registration form ID
    const FORM_ID = 1917;

    public static function init() {
        add_action(
            'wpforms_process_complete_' . self::FORM_ID,
            [__CLASS__, 'handle_employer_submission'],
            10,
            4
        );
        
        // Add custom smart tag for password setup link
        add_filter( 'wpforms_smart_tags', [__CLASS__, 'register_smart_tag'] );
        add_filter( 'wpforms_process_smart_tags', [__CLASS__, 'process_smart_tag'], 10, 4 );
    }

    public static function handle_employer_submission( $fields, $entry, $form_data, $entry_id ) {

        $full_name = self::get_field_value( $fields, 'Full Name' );
        $email     = self::get_field_value( $fields, 'Email' );
        $phone     = self::get_field_value( $fields, 'Phone' );
        $company   = self::get_field_value( $fields, 'Company' );

        if ( ! $email ) return;

        // Generate unique username
        $username = OSO_Employer_Utils::generate_username( $full_name, $email );

        // Create WordPress user (no password)
        $user_id = wp_insert_user([
            'user_login'   => $username,
            'user_email'   => $email,
            'display_name' => $full_name,
            'role'         => OSO_Jobs_Portal::ROLE_EMPLOYER,
        ]);

        if ( is_wp_error( $user_id ) ) return;

        // Send password setup email (Option 1)
        wp_new_user_notification( $user_id, null, 'user' );

        // Create Employer CPT entry
        $post_id = wp_insert_post([
            'post_author' => $user_id,
            'post_type'   => OSO_Jobs_Portal::POST_TYPE_EMPLOYER,
            'post_title'  => $full_name,
            'post_status' => 'publish',
        ]);

        // Link CPT to user ID
        update_post_meta( $post_id, '_oso_employer_user_id', $user_id );

        // Save employer profile data
        update_post_meta( $post_id, '_oso_employer_full_name', $full_name );
        update_post_meta( $post_id, '_oso_employer_email', $email );
        update_post_meta( $post_id, '_oso_employer_phone', $phone );
        update_post_meta( $post_id, '_oso_employer_company', $company );
        
        // Store user ID for smart tag (Option 2)
        wpforms()->process->fields = $fields;
        wpforms()->process->entry_id = $entry_id;
        set_transient( 'oso_employer_registration_' . $entry_id, $user_id, HOUR_IN_SECONDS );
    }

    private static function get_field_value( $fields, $label ) {
        foreach ( $fields as $field ) {
            if ( isset( $field['name'] ) && trim( strtolower( $field['name'] ) ) === trim( strtolower( $label ) ) ) {
                return $field['value'];
            }
        }
        return '';
    }
    
    /**
     * Register custom smart tag for password setup link.
     */
    public static function register_smart_tag( $tags ) {
        $tags['employer_password_link'] = __( 'Employer Password Setup Link', 'oso-employer-portal' );
        return $tags;
    }
    
    /**
     * Process the password setup link smart tag.
     */
    public static function process_smart_tag( $content, $tag, $form_data, $fields ) {
        // Only process our custom tag
        if ( 'employer_password_link' !== $tag ) {
            return $content;
        }
        
        // Only for our specific form
        if ( empty( $form_data['id'] ) || (int) $form_data['id'] !== self::FORM_ID ) {
            return $content;
        }
        
        // Get the user ID from transient
        $entry_id = wpforms()->process->entry_id ?? 0;
        $user_id = get_transient( 'oso_employer_registration_' . $entry_id );
        
        if ( ! $user_id ) {
            return __( 'Please check your email for password setup instructions.', 'oso-employer-portal' );
        }
        
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return __( 'Please check your email for password setup instructions.', 'oso-employer-portal' );
        }
        
        // Generate password reset key
        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            return __( 'Please check your email for password setup instructions.', 'oso-employer-portal' );
        }
        
        // Build the password setup URL
        $url = network_site_url(
            'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user->user_login ),
            'login'
        );
        
        // Return the clickable link
        return '<a href="' . esc_url( $url ) . '" class="oso-password-setup-link" target="_blank">' . 
               __( 'Click here to set up your password', 'oso-employer-portal' ) . 
               '</a>';
    }
}