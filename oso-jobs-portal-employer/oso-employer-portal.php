<?php
/**
 * Plugin Name: OSO Jobs Portal - Employer Section
 * Description: Add-on plugin for employer registration, dashboards, and admin tools. Requires OSO Jobs Portal.
 * Version: 1.0.10
 * Author: Creative DBS
 */

if ( ! defined('ABSPATH') ) exit;

// Plugin constants
define( 'OSO_EMPLOYER_PORTAL_DIR', plugin_dir_path(__FILE__) );
define( 'OSO_EMPLOYER_PORTAL_URL', plugin_dir_url(__FILE__) );

/**
 * Check if main plugin is active.
 */
function oso_employer_check_main_plugin() {
    // Check multiple ways to ensure main plugin is loaded
    $checks = array(
        'constant' => defined('OSO_JOBS_PORTAL_DIR'),
        'class'    => class_exists('OSO_Jobs_Portal'),
        'function' => function_exists('oso_jobs_portal')
    );
    
    return $checks['constant'] || $checks['class'] || $checks['function'];
}

/**
 * Initialize employer portal plugin.
 */
function oso_employer_portal_init() {
    // Check if master plugin is active
    if ( ! oso_employer_check_main_plugin() ) {
        add_action('admin_notices', function() {
            $active_plugins = get_option('active_plugins', array());
            $plugin_list = is_array($active_plugins) ? implode(', ', $active_plugins) : 'none';
            
            echo '<div class="notice notice-error">';
            echo '<p><strong>OSO Jobs Portal - Employer Section:</strong> The OSO Jobs Portal plugin must be installed and active.</p>';
            echo '<p><small>Looking for: OSO_JOBS_PORTAL_DIR constant, OSO_Jobs_Portal class, or oso_jobs_portal function.</small></p>';
            echo '<p><small>Active plugins: ' . esc_html($plugin_list) . '</small></p>';
            echo '</div>';
        });
        return;
    }

    // Load employer portal classes
    $class_file = OSO_EMPLOYER_PORTAL_DIR . 'includes/class-oso-employer-portal.php';
    if ( ! file_exists( $class_file ) ) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>OSO Jobs Portal - Employer Section:</strong> Plugin files are missing. Expected: ' . esc_html($class_file) . '</p></div>';
        });
        return;
    }

    require_once $class_file;
    
    // Boot plugin
    OSO_Employer_Portal::instance();
}

// Initialize after all plugins are loaded
add_action( 'plugins_loaded', 'oso_employer_portal_init', 20 );