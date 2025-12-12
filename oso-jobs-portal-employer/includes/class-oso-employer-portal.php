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
        require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/admin/class-oso-employer-admin.php';
        require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/admin/class-oso-job-admin.php';
        require_once OSO_EMPLOYER_PORTAL_DIR . 'includes/class-oso-job-manager.php';

        // Register custom post types
        add_action( 'init', [ $this, 'register_job_post_type' ] );
        add_action( 'init', [ $this, 'register_job_application_post_type' ] );
        add_action( 'init', [ $this, 'register_employer_interest_post_type' ] );

        // Initialize employer registration handler
        OSO_Employer_Registration::init();
        
        // Initialize job manager
        OSO_Job_Manager::instance();
        
        // Initialize shortcodes
        OSO_Employer_Shortcodes::instance();
        
        // Initialize admin functionality
        if ( is_admin() ) {
            OSO_Employer_Admin::instance();
            OSO_Job_Admin::instance();
        }
        
        // Enqueue frontend assets
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    /**
     * Register job posting custom post type.
     */
    public function register_job_post_type() {
        $labels = array(
            'name'                  => _x( 'Job Postings', 'Post type general name', 'oso-employer-portal' ),
            'singular_name'         => _x( 'Job Posting', 'Post type singular name', 'oso-employer-portal' ),
            'menu_name'             => _x( 'Job Postings', 'Admin Menu text', 'oso-employer-portal' ),
            'name_admin_bar'        => _x( 'Job Posting', 'Add New on Toolbar', 'oso-employer-portal' ),
            'add_new'               => __( 'Add New', 'oso-employer-portal' ),
            'add_new_item'          => __( 'Add New Job Posting', 'oso-employer-portal' ),
            'new_item'              => __( 'New Job Posting', 'oso-employer-portal' ),
            'edit_item'             => __( 'Edit Job Posting', 'oso-employer-portal' ),
            'view_item'             => __( 'View Job Posting', 'oso-employer-portal' ),
            'all_items'             => __( 'All Job Postings', 'oso-employer-portal' ),
            'search_items'          => __( 'Search Job Postings', 'oso-employer-portal' ),
            'not_found'             => __( 'No job postings found.', 'oso-employer-portal' ),
            'not_found_in_trash'    => __( 'No job postings found in Trash.', 'oso-employer-portal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'oso-jobs-dashboard',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'job-posting' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-portfolio',
            'supports'           => array( 'title', 'editor' ),
            'show_in_rest'       => false,
        );

        register_post_type( 'oso_job_posting', $args );
    }

    /**
     * Register job application custom post type.
     */
    public function register_job_application_post_type() {
        $labels = array(
            'name'                  => _x( 'Job Applications', 'Post type general name', 'oso-employer-portal' ),
            'singular_name'         => _x( 'Job Application', 'Post type singular name', 'oso-employer-portal' ),
            'menu_name'             => _x( 'Applications', 'Admin Menu text', 'oso-employer-portal' ),
            'name_admin_bar'        => _x( 'Job Application', 'Add New on Toolbar', 'oso-employer-portal' ),
            'add_new'               => __( 'Add New', 'oso-employer-portal' ),
            'add_new_item'          => __( 'Add New Application', 'oso-employer-portal' ),
            'new_item'              => __( 'New Application', 'oso-employer-portal' ),
            'edit_item'             => __( 'Edit Application', 'oso-employer-portal' ),
            'view_item'             => __( 'View Application', 'oso-employer-portal' ),
            'all_items'             => __( 'All Applications', 'oso-employer-portal' ),
            'search_items'          => __( 'Search Applications', 'oso-employer-portal' ),
            'not_found'             => __( 'No applications found.', 'oso-employer-portal' ),
            'not_found_in_trash'    => __( 'No applications found in Trash.', 'oso-employer-portal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'oso-jobs-dashboard',
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-businessman',
            'supports'           => array( 'title', 'editor' ),
            'show_in_rest'       => false,
        );

        register_post_type( 'oso_job_application', $args );
    }

    /**
     * Register employer interest custom post type.
     */
    public function register_employer_interest_post_type() {
        $labels = array(
            'name'                  => _x( 'Employer Interests', 'Post type general name', 'oso-employer-portal' ),
            'singular_name'         => _x( 'Employer Interest', 'Post type singular name', 'oso-employer-portal' ),
            'menu_name'             => _x( 'Interests', 'Admin Menu text', 'oso-employer-portal' ),
            'name_admin_bar'        => _x( 'Employer Interest', 'Add New on Toolbar', 'oso-employer-portal' ),
            'add_new'               => __( 'Add New', 'oso-employer-portal' ),
            'add_new_item'          => __( 'Add New Interest', 'oso-employer-portal' ),
            'new_item'              => __( 'New Interest', 'oso-employer-portal' ),
            'edit_item'             => __( 'View Interest', 'oso-employer-portal' ),
            'view_item'             => __( 'View Interest', 'oso-employer-portal' ),
            'all_items'             => __( 'All Interests', 'oso-employer-portal' ),
            'search_items'          => __( 'Search Interests', 'oso-employer-portal' ),
            'not_found'             => __( 'No interests found.', 'oso-employer-portal' ),
            'not_found_in_trash'    => __( 'No interests found in Trash.', 'oso-employer-portal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'oso-jobs-dashboard',
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-heart',
            'supports'           => array( 'title' ),
            'show_in_rest'       => false,
        );

        register_post_type( 'oso_employer_interest', $args );
    }
    
    /**
     * Enqueue frontend assets.
     */
    public function enqueue_frontend_assets() {
        // Enqueue Dashicons for button icons
        wp_enqueue_style( 'dashicons' );
        
        wp_enqueue_style(
            'oso-employer-portal',
            OSO_EMPLOYER_PORTAL_URL . 'assets/css/employer-portal.css',
            array( 'dashicons' ),
            '1.0.25'
        );
        
        // Deregister conflicting lightbox scripts that might cause duplicates
        wp_deregister_script( 'lightbox' );
        wp_deregister_script( 'simple-lightbox' );
        
        wp_enqueue_script(
            'oso-employer-portal',
            OSO_EMPLOYER_PORTAL_URL . 'assets/js/employer-portal.js',
            array( 'jquery' ),
            '1.0.19',
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'oso-employer-portal',
            'osoEmployerPortal',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'oso_upload_profile_file' ),
                'jobNonce' => wp_create_nonce( 'oso-job-nonce' ),
            )
        );
    }
}