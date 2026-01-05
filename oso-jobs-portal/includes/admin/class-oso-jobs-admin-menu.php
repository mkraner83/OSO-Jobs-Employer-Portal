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
        add_action( 'admin_menu', array( $this, 'remove_duplicate_menus' ), 999 );
        add_action( 'admin_head', array( $this, 'add_admin_styles' ) );
        add_action( 'admin_footer', array( $this, 'add_admin_scripts' ) );
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

        // Note: Job Postings and Applications are automatically added by WordPress
        // because their post types have 'show_in_menu' => 'oso-jobs-dashboard'

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
        
        add_submenu_page(
            'oso-jobs-dashboard',
            __( 'Email Templates', 'oso-jobs-portal' ),
            __( 'Email Templates', 'oso-jobs-portal' ),
            'manage_options',
            'oso-jobs-email-templates',
            array( $this, 'render_email_templates' )
        );
    }

    /**
     * Remove duplicate menu items that WordPress auto-adds.
     */
    public function remove_duplicate_menus() {
        global $submenu;
        
        if ( isset( $submenu['oso-jobs-dashboard'] ) ) {
            $seen = array();
            foreach ( $submenu['oso-jobs-dashboard'] as $key => $item ) {
                // Item[2] is the slug/URL
                $slug = $item[2];
                
                // If we've seen this slug before, remove it
                if ( isset( $seen[ $slug ] ) ) {
                    unset( $submenu['oso-jobs-dashboard'][ $key ] );
                } else {
                    $seen[ $slug ] = true;
                }
            }
        }
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
    
    /**
     * Render email templates page.
     */
    public function render_email_templates() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        require_once OSO_JOBS_PORTAL_DIR . 'includes/settings/class-oso-jobs-email-templates.php';
        OSO_Jobs_Email_Templates::render_page();
    }

    /**
     * Add custom admin styles for OSO Jobs menu.
     */
    public function add_admin_styles() {
        ?>
        <style>
            /* Style OSO Jobs menu title in purple */
            #adminmenu #toplevel_page_oso-jobs-dashboard .wp-menu-name {
                color: #8051B0 !important;
                font-weight: 600;
            }
            
            #adminmenu #toplevel_page_oso-jobs-dashboard:hover .wp-menu-name,
            #adminmenu #toplevel_page_oso-jobs-dashboard.current .wp-menu-name,
            #adminmenu #toplevel_page_oso-jobs-dashboard.wp-has-current-submenu .wp-menu-name {
                color: #fff !important;
            }
            
            /* Keep menu expanded */
            #adminmenu #toplevel_page_oso-jobs-dashboard .wp-submenu {
                display: block !important;
            }
            
            #adminmenu #toplevel_page_oso-jobs-dashboard.wp-not-current-submenu .wp-submenu {
                display: block !important;
            }
        </style>
        <?php
    }

    /**
     * Add JavaScript to move OSO Jobs menu to position 2 (after Dashboard).
     */
    public function add_admin_scripts() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Move OSO Jobs menu to position 2 (right after Dashboard)
            var osoMenu = $('#toplevel_page_oso-jobs-dashboard');
            var dashboardMenu = $('#menu-dashboard');
            
            if (osoMenu.length && dashboardMenu.length) {
                osoMenu.insertAfter(dashboardMenu);
            }
            
            // Keep submenu always visible
            $('#toplevel_page_oso-jobs-dashboard').addClass('wp-has-current-submenu wp-menu-open');
            $('#toplevel_page_oso-jobs-dashboard > a').addClass('wp-has-current-submenu');
        });
        </script>
        <?php
    }
}
