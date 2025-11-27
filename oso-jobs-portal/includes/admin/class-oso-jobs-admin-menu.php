<?php
/**
 * Admin menu and pages.
 *
 * @package OSO_Jobs_Portal\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin menu manager.
 */
class OSO_Jobs_Admin_Menu {

    /**
     * Singleton instance.
     *
     * @var OSO_Jobs_Admin_Menu
     */
    protected static $instance = null;

    /**
     * Get singleton.
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Hook menus.
     */
    protected function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
    }

    /**
     * Register menu and subpages.
     */
    public function register_menu() {
        add_menu_page(
            __( 'OSO Jobs', 'oso-jobs-portal' ),
            __( 'OSO Jobs', 'oso-jobs-portal' ),
            'manage_options',
            'oso-jobs-dashboard',
            array( $this, 'render_dashboard' ),
            'dashicons-businessman',
            26
        );

        add_submenu_page(
            'oso-jobs-dashboard',
            __( 'Form Submissions', 'oso-jobs-portal' ),
            __( 'Form Submissions', 'oso-jobs-portal' ),
            'manage_options',
            'oso-jobs-submissions',
            array( $this, 'render_submissions' )
        );

        add_submenu_page(
            'oso-jobs-dashboard',
            __( 'Employers', 'oso-jobs-portal' ),
            __( 'Employers', 'oso-jobs-portal' ),
            'edit_posts',
            'edit.php?post_type=' . OSO_Jobs_Portal::POST_TYPE_EMPLOYER,
            null
        );

        add_submenu_page(
            'oso-jobs-dashboard',
            __( 'Jobseekers', 'oso-jobs-portal' ),
            __( 'Jobseekers', 'oso-jobs-portal' ),
            'edit_posts',
            'edit.php?post_type=' . OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
            null
        );

        add_submenu_page(
            'oso-jobs-dashboard',
            __( 'Settings', 'oso-jobs-portal' ),
            __( 'Settings', 'oso-jobs-portal' ),
            'manage_options',
            'oso-jobs-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Render dashboard page.
     */
    public function render_dashboard() {
        echo OSO_Jobs_Template_Loader::render( 'includes/admin/views/dashboard.php' );
    }

    /**
     * Render submissions page.
     */
    public function render_submissions() {
        $entries = OSO_Jobs_WPForms_Handler::get_recent_entries();
        echo OSO_Jobs_Template_Loader::render(
            'includes/admin/views/submissions.php',
            array( 'entries' => $entries )
        );
    }

    /**
     * Render settings page.
     */
    public function render_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        echo OSO_Jobs_Template_Loader::render( 'includes/admin/views/settings.php' );
    }
}
