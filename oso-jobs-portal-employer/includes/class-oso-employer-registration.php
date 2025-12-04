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
        
        // Redirect employers to their dashboard after login
        add_filter( 'login_redirect', [__CLASS__, 'employer_login_redirect'], 10, 3 );
        
        // Block wp-admin access for employers
        add_action( 'admin_init', [__CLASS__, 'block_employer_admin_access'] );
    }
    
    /**
     * Block employers from accessing wp-admin and redirect to their dashboard
     */
    public static function block_employer_admin_access() {
        // Allow AJAX requests
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }
        
        $user = wp_get_current_user();
        if ( ! $user || ! $user->exists() ) {
            return;
        }
        
        // Don't block administrators
        if ( in_array( 'administrator', $user->roles ) ) {
            return;
        }
        
        // Check if user is an employer
        if ( in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, $user->roles ) ) {
            // Find the employer dashboard page
            $pages = get_posts([
                'post_type'   => 'page',
                'post_status' => 'publish',
                'numberposts' => -1,
            ]);
            
            $dashboard_url = home_url();
            foreach ( $pages as $page ) {
                if ( has_shortcode( $page->post_content, 'oso_employer_dashboard' ) || 
                     has_shortcode( $page->post_content, 'oso_employer_profile' ) ) {
                    $dashboard_url = get_permalink( $page->ID );
                    break;
                }
            }
            
            wp_redirect( $dashboard_url );
            exit;
        }
    }
    
    /**
     * Redirect employers to their dashboard page instead of wp-admin
     */
    public static function employer_login_redirect( $redirect_to, $request, $user ) {
        if ( isset( $user->roles ) && is_array( $user->roles ) ) {
            // Check if user is an employer
            if ( in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, $user->roles ) ) {
                // Find the page with the employer dashboard shortcode
                $pages = get_posts([
                    'post_type'   => 'page',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                ]);
                
                foreach ( $pages as $page ) {
                    if ( has_shortcode( $page->post_content, 'oso_employer_dashboard' ) || 
                         has_shortcode( $page->post_content, 'oso_employer_profile' ) ) {
                        return get_permalink( $page->ID );
                    }
                }
            }
        }
        
        return $redirect_to;
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

        // Send password setup email
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
    }

    private static function get_field_value( $fields, $label ) {
        foreach ( $fields as $field ) {
            if ( isset( $field['name'] ) && trim( strtolower( $field['name'] ) ) === trim( strtolower( $label ) ) ) {
                return $field['value'];
            }
        }
        return '';
    }
}