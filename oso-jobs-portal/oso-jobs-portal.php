<?php
/**
 * Plugin Name:       OSO Jobs Portal
 * Plugin URI:        https://example.com/oso-jobs-portal
 * Description:       Complete jobs portal toolkit with job listings, front-end submissions, and WPForms integration.
 * Version:           1.0.12
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            OSO
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       oso-jobs-portal
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OSO_JOBS_PORTAL_VERSION', '1.0.12' );
define( 'OSO_JOBS_PORTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'OSO_JOBS_PORTAL_URL', plugin_dir_url( __FILE__ ) );
define( 'OSO_JOBS_PORTAL_BASENAME', plugin_basename( __FILE__ ) );

oso_jobs_portal_autoload();

register_activation_hook( __FILE__, 'oso_jobs_portal_activate' );
register_deactivation_hook( __FILE__, 'oso_jobs_portal_deactivate' );

/**
 * Simple autoloader for plugin classes.
 */
function oso_jobs_portal_autoload() {
    spl_autoload_register(
        function ( $class ) {
            if ( 0 !== strpos( $class, 'OSO_' ) ) {
                return;
            }

            $filename = 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
            $path     = OSO_JOBS_PORTAL_DIR . 'includes/' . $filename;

            if ( file_exists( $path ) ) {
                require_once $path;
                return;
            }

            $subpaths = array( 'helpers', 'admin', 'shortcodes', 'wpforms', 'settings' );
            foreach ( $subpaths as $subpath ) {
                $path = OSO_JOBS_PORTAL_DIR . 'includes/' . $subpath . '/' . $filename;
                if ( file_exists( $path ) ) {
                    require_once $path;
                    return;
                }
            }
        }
    );
}

/**
 * Initialize plugin.
 */
function oso_jobs_portal() {
    return OSO_Jobs_Portal::instance();
}
add_action( 'plugins_loaded', 'oso_jobs_portal' );

/**
 * Plugin activation hook.
 */
function oso_jobs_portal_activate() {
    $plugin = oso_jobs_portal();
    $plugin->activate();
}

/**
 * Plugin deactivation hook.
 */
function oso_jobs_portal_deactivate() {
    $plugin = oso_jobs_portal();
    $plugin->deactivate();
}
