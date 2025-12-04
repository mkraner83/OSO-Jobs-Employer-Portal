<?php
/**
 * Plugin Name: OSO Jobs Portal - Employer Section
 * Description: Add-on plugin for employer registration, dashboards, and admin tools. Requires OSO Jobs Portal.
 * Version: 1.0.0
 * Author: Creative DBS
 */

if ( ! defined('ABSPATH') ) exit;

// Plugin constants
define( 'OSO_EMPLOYER_PORTAL_DIR', plugin_dir_path(__FILE__) );
define( 'OSO_EMPLOYER_PORTAL_URL', plugin_dir_url(__FILE__) );

// Initialize plugin after all plugins are loaded
add_action( 'plugins_loaded', 'oso_employer_portal_init', 20 );

function oso_employer_portal_init() {
    // Check if master plugin is active by checking its constant
    if ( ! defined('OSO_JOBS_PORTAL_DIR') ) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>OSO Jobs Portal - Employer Section:</strong> The OSO Jobs Portal plugin must be installed and active.</p></div>';
        });
        return;
    }
    
    require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/class-oso-employer-portal.php';
    
    // Boot plugin
    OSO_Employer_Portal::instance();
}