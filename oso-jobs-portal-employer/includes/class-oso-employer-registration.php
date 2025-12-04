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
        
        // Hide admin bar for employers and jobseekers
        add_action( 'after_setup_theme', [__CLASS__, 'hide_admin_bar'] );
    }
    
    /**
     * Hide WordPress admin bar for employers and jobseekers
     */
    public static function hide_admin_bar() {
        if ( ! is_user_logged_in() ) {
            return;
        }
        
        $user = wp_get_current_user();
        
        // Don't hide for administrators
        if ( in_array( 'administrator', $user->roles ) ) {
            return;
        }
        
        // Hide for employers and jobseekers
        if ( in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, $user->roles ) || 
             in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, $user->roles ) ) {
            show_admin_bar( false );
        }
    }
    
    /**
     * Block employers and jobseekers from accessing wp-admin and redirect to their dashboard/profile
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
        
        // Check if user is a jobseeker
        if ( in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, $user->roles ) ) {
            // Redirect to the jobseeker profile page
            wp_redirect( home_url( '/job-portal/jobseeker-profile/' ) );
            exit;
        }
    }

    /**
     * Redirect employers and jobseekers after login
     */
    public static function employer_login_redirect( $redirect_to, $request, $user ) {
        if ( ! isset( $user->roles ) || ! is_array( $user->roles ) ) {
            return $redirect_to;
        }

        // Check if user is an employer
        if ( in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, $user->roles ) ) {
            // Find the employer dashboard page
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
        
        // Check if user is a jobseeker
        if ( in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, $user->roles ) ) {
            // Redirect to the jobseeker profile page
            return home_url( '/job-portal/jobseeker-profile/' );
        }

        return $redirect_to;
    }

    public static function handle_employer_submission( $fields, $entry, $form_data, $entry_id ) {

        // Get email from Contact Email field
        $email = self::get_field_value( $fields, 'Contact Email' );
        if ( ! $email ) return;

        // Get camp name as the display name
        $camp_name = self::get_field_value( $fields, 'Camp Name' );
        if ( ! $camp_name ) return;

        // Generate unique username
        $username = OSO_Employer_Utils::generate_username( $camp_name, $email );

        // Create WordPress user (no password)
        $user_id = wp_insert_user([
            'user_login'   => $username,
            'user_email'   => $email,
            'display_name' => $camp_name,
            'role'         => OSO_Jobs_Portal::ROLE_EMPLOYER,
        ]);

        if ( is_wp_error( $user_id ) ) return;

        // Send password setup email
        wp_new_user_notification( $user_id, null, 'user' );

        // Create Employer CPT entry
        $post_id = wp_insert_post([
            'post_author' => $user_id,
            'post_type'   => OSO_Jobs_Portal::POST_TYPE_EMPLOYER,
            'post_title'  => $camp_name,
            'post_status' => 'publish',
        ]);

        // Link CPT to user ID and WPForms entry
        update_post_meta( $post_id, '_oso_employer_user_id', $user_id );
        update_post_meta( $post_id, '_oso_employer_wpforms_entry', $entry_id );

        // Map WPForms field names to meta keys
        $field_mapping = array(
            'Camp Name' => '_oso_employer_company',
            'Brief Description' => '_oso_employer_description',
            'Type of Camp' => '_oso_employer_camp_types',
            'State' => '_oso_employer_state',
            'Address' => '_oso_employer_address',
            'Closest Major City (optional)' => '_oso_employer_major_city',
            'Start of Staff Training Date' => '_oso_employer_training_start',
            'Housing Provided' => '_oso_employer_housing',
            'Contact Email' => '_oso_employer_email',
            'Website / URL' => '_oso_employer_website',
            'Social Media Links (optional)' => '_oso_employer_social_links',
            'Subscription Type' => '_oso_employer_subscription_type',
        );

        // Save all form fields
        foreach ( $fields as $field ) {
            if ( ! isset( $field['name'] ) || empty( $field['name'] ) ) {
                continue;
            }

            $field_name = trim( $field['name'] );
            $field_value = isset( $field['value'] ) ? $field['value'] : '';

            // Skip file uploads (logo handled separately)
            if ( isset( $field['type'] ) && $field['type'] === 'file-upload' ) {
                // Handle logo upload
                if ( stripos( $field_name, 'logo' ) !== false && ! empty( $field_value ) ) {
                    // If multiple files, take first one as logo
                    $files = is_array( $field_value ) ? $field_value : array( $field_value );
                    if ( ! empty( $files[0] ) ) {
                        update_post_meta( $post_id, '_oso_employer_logo', esc_url_raw( $files[0] ) );
                    }
                }
                continue;
            }

            // Get meta key from mapping or create generic one
            $meta_key = isset( $field_mapping[ $field_name ] ) ? $field_mapping[ $field_name ] : '_oso_employer_' . sanitize_key( strtolower( str_replace( ' ', '_', $field_name ) ) );

            // Save the field with appropriate sanitization
            if ( is_array( $field_value ) ) {
                update_post_meta( $post_id, $meta_key, implode( "\n", array_map( 'sanitize_text_field', $field_value ) ) );
            } elseif ( stripos( $field_name, 'description' ) !== false || stripos( $field_name, 'social' ) !== false ) {
                update_post_meta( $post_id, $meta_key, sanitize_textarea_field( $field_value ) );
            } elseif ( stripos( $field_name, 'website' ) !== false || stripos( $field_name, 'url' ) !== false ) {
                update_post_meta( $post_id, $meta_key, esc_url_raw( $field_value ) );
            } else {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $field_value ) );
            }
        }
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
