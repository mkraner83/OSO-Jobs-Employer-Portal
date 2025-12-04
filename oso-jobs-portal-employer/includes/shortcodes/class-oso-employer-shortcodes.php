<?php
/**
 * Employer Shortcodes Handler
 * 
 * Registers and handles all employer-facing shortcodes.
 * 
 * @package OSO_Employer_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OSO_Employer_Shortcodes {
    
    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode( 'oso_employer_dashboard', array( __CLASS__, 'shortcode_employer_dashboard' ) );
        add_shortcode( 'oso_jobseeker_browser', array( __CLASS__, 'shortcode_jobseeker_browser' ) );
        add_shortcode( 'oso_jobseeker_profile', array( __CLASS__, 'shortcode_jobseeker_profile' ) );
    }
    
    /**
     * Employer Dashboard Shortcode
     * 
     * Usage: [oso_employer_dashboard]
     */
    public static function shortcode_employer_dashboard( $atts ) {
        // Check if user is logged in and is an employer
        if ( ! is_user_logged_in() ) {
            return '<p class="oso-error">You must be logged in to view this page.</p>';
        }
        
        $user = wp_get_current_user();
        if ( ! in_array( 'oso_employer', $user->roles ) ) {
            return '<p class="oso-error">Access denied. This page is for employers only.</p>';
        }
        
        ob_start();
        include OSO_EMPLOYER_PORTAL_DIR . 'includes/shortcodes/views/employer-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Jobseeker Browser Shortcode
     * 
     * Displays a searchable/filterable list of jobseekers for employers.
     * 
     * Usage: [oso_jobseeker_browser]
     */
    public static function shortcode_jobseeker_browser( $atts ) {
        // Check if user is logged in and is an employer
        if ( ! is_user_logged_in() ) {
            return '<p class="oso-error">You must be logged in to view this page.</p>';
        }
        
        $user = wp_get_current_user();
        if ( ! in_array( 'oso_employer', $user->roles ) ) {
            return '<p class="oso-error">Access denied. This page is for employers only.</p>';
        }
        
        ob_start();
        include OSO_EMPLOYER_PORTAL_DIR . 'includes/shortcodes/views/jobseeker-browser.php';
        return ob_get_clean();
    }
    
    /**
     * Jobseeker Profile View Shortcode
     * 
     * Displays individual jobseeker profile details.
     * 
     * Usage: [oso_jobseeker_profile]
     */
    public static function shortcode_jobseeker_profile( $atts ) {
        // Check if user is logged in and is an employer
        if ( ! is_user_logged_in() ) {
            return '<p class="oso-error">You must be logged in to view this page.</p>';
        }
        
        $user = wp_get_current_user();
        if ( ! in_array( 'oso_employer', $user->roles ) ) {
            return '<p class="oso-error">Access denied. This page is for employers only.</p>';
        }
        
        ob_start();
        include OSO_EMPLOYER_PORTAL_DIR . 'includes/shortcodes/views/jobseeker-profile-view.php';
        return ob_get_clean();
    }
    
}
