<?php
if ( ! defined('ABSPATH') ) exit;

class OSO_Employer_Portal {

    private static $instance = null;

    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {

        // Load dependencies
        require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/class-oso-employer-registration.php';
        require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/helpers/class-oso-employer-utils.php';
        require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/shortcodes/class-oso-employer-shortcodes.php';

        // Initialize employer registration handler
        OSO_Employer_Registration::init();
        
        // Initialize shortcodes
        OSO_Employer_Shortcodes::instance();
    }
}