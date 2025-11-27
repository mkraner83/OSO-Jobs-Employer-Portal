<?php
/**
 * Plugin Name: OSO Jobs Portal - Employer Section
 * Description: Add-on plugin for employer registration, dashboards, and admin tools. Requires OSO Jobs Portal.
 * Version: 1.0.0
 * Author: Creative DBS
 */

if ( ! defined('ABSPATH') ) exit;

// Check if master plugin is active by checking its constant
if ( ! defined('OSO_JOBS_PORTAL_DIR') ) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p><strong>OSO Jobs Portal - Employer Section:</strong> The OSO Jobs Portal plugin must be installed and active.</p></div>';
    });
    return;
}

// Plugin constants
define( 'OSO_EMPLOYER_PORTAL_DIR', plugin_dir_path(__FILE__) );
define( 'OSO_EMPLOYER_PORTAL_URL', plugin_dir_url(__FILE__) );

require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/class-oso-employer-portal.php';

// Boot plugin
add_action( 'plugins_loaded', ['OSO_Employer_Portal', 'instance'] );