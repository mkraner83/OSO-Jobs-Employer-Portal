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
        
        add_submenu_page(
            'oso-jobs-dashboard',
            __( 'Tools', 'oso-jobs-portal' ),
            __( 'Tools', 'oso-jobs-portal' ),
            'manage_options',
            'oso-jobs-tools',
            array( $this, 'render_tools' )
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
    
    /**
     * Render tools page.
     */
    public function render_tools() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Handle fix action
        if ( isset( $_POST['fix_jobseeker_data'] ) && check_admin_referer( 'oso_fix_jobseeker_data' ) ) {
            $this->fix_jobseeker_data();
        }
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'OSO Jobs Tools', 'oso-jobs-portal' ); ?></h1>
            
            <div class="card">
                <h2><?php esc_html_e( 'Fix Jobseeker Data', 'oso-jobs-portal' ); ?></h2>
                <p><?php esc_html_e( 'This tool fixes jobseeker records where the "Why interested" text was incorrectly stored as job interests (due to an earlier bug). It will move long text from job_interests to post_content and clear the corrupted meta field.', 'oso-jobs-portal' ); ?></p>
                
                <form method="post">
                    <?php wp_nonce_field( 'oso_fix_jobseeker_data' ); ?>
                    <p>
                        <button type="submit" name="fix_jobseeker_data" class="button button-primary">
                            <?php esc_html_e( 'Fix Jobseeker Data', 'oso-jobs-portal' ); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Fix existing jobseeker records
     */
    private function fix_jobseeker_data() {
        $fixed = 0;
        $jobseekers = get_posts([
            'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ]);
        
        foreach ( $jobseekers as $jobseeker ) {
            $job_interests = get_post_meta( $jobseeker->ID, '_oso_jobseeker_job_interests', true );
            
            // Check if job_interests contains long text (likely the "Why" answer)
            if ( ! empty( $job_interests ) && strlen( $job_interests ) > 100 ) {
                // Move to post_content if post_content is empty or very short
                if ( empty( $jobseeker->post_content ) || strlen( $jobseeker->post_content ) < 50 ) {
                    wp_update_post([
                        'ID'           => $jobseeker->ID,
                        'post_content' => $job_interests,
                    ]);
                }
                
                // Clear the corrupted job_interests meta
                delete_post_meta( $jobseeker->ID, '_oso_jobseeker_job_interests' );
                $fixed++;
            }
        }
        
        echo '<div class="notice notice-success"><p>';
        printf( esc_html__( 'Fixed %d jobseeker records.', 'oso-jobs-portal' ), $fixed );
        echo '</p></div>';
    }
}
