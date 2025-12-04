<?php
/**
 * Shortcodes for OSO Employer Portal.
 *
 * @package OSO_Employer_Portal\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register and render shortcodes for employers.
 */
class OSO_Employer_Shortcodes {

    /**
     * Singleton.
     *
     * @var OSO_Employer_Shortcodes
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
     * Hook shortcodes.
     */
    protected function __construct() {
        add_shortcode( 'oso_employer_dashboard', array( $this, 'shortcode_employer_dashboard' ) );
        add_shortcode( 'oso_jobseeker_browser', array( $this, 'shortcode_jobseeker_browser' ) );
        add_shortcode( 'oso_jobseeker_profile', array( $this, 'shortcode_jobseeker_profile' ) );
    }

    /**
     * Render employer dashboard shortcode.
     */
    public function shortcode_employer_dashboard( $atts ) {
        $atts = shortcode_atts(
            array(
                'redirect_url' => wp_login_url(),
            ),
            $atts,
            'oso_employer_dashboard'
        );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            $current_url = home_url();
            if ( isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
                $current_url = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
            }

            $login_form = wp_login_form(
                array(
                    'echo'     => false,
                    'redirect' => esc_url( $current_url ),
                )
            );

            return $this->load_template(
                'employer-dashboard.php',
                array(
                    'is_logged_in' => false,
                    'login_form'   => $login_form,
                    'lost_url'     => wp_lostpassword_url( $current_url ),
                )
            );
        }

        $user_id = get_current_user_id();
        $user    = wp_get_current_user();

        // Check if user has employer role
        if ( ! in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true ) ) {
            return '<p>' . esc_html__( 'You do not have permission to access the employer dashboard.', 'oso-employer-portal' ) . '</p>';
        }

        // Get employer profile post
        $employer_post = $this->get_employer_by_user( $user_id );

        if ( ! $employer_post ) {
            return '<p>' . esc_html__( 'No employer profile is associated with your account.', 'oso-employer-portal' ) . '</p>';
        }

        $meta = $this->get_employer_meta( $employer_post->ID );

        // Get employer's posted jobs
        $jobs_query = new WP_Query(
            array(
                'post_type'      => OSO_Jobs_Portal::POST_TYPE,
                'author'         => $user_id,
                'posts_per_page' => -1,
                'post_status'    => array( 'publish', 'draft', 'pending' ),
            )
        );

        return $this->load_template(
            'employer-dashboard.php',
            array(
                'is_logged_in'  => true,
                'user'          => $user,
                'employer_post' => $employer_post,
                'meta'          => $meta,
                'jobs'          => $jobs_query,
            )
        );
    }

    /**
     * Get employer post by user ID.
     *
     * @param int $user_id User ID.
     * @return WP_Post|null
     */
    protected function get_employer_by_user( $user_id ) {
        $query = new WP_Query(
            array(
                'post_type'      => OSO_Jobs_Portal::POST_TYPE_EMPLOYER,
                'meta_key'       => '_oso_employer_user_id',
                'meta_value'     => $user_id,
                'posts_per_page' => 1,
                'post_status'    => 'any',
            )
        );

        if ( $query->have_posts() ) {
            return $query->posts[0];
        }

        return null;
    }

    /**
     * Get employer metadata.
     *
     * @param int $post_id Post ID.
     * @return array
     */
    protected function get_employer_meta( $post_id ) {
        $fields = array(
            '_oso_employer_full_name',
            '_oso_employer_email',
            '_oso_employer_phone',
            '_oso_employer_company',
        );

        $meta = array();
        foreach ( $fields as $field ) {
            $meta[ $field ] = get_post_meta( $post_id, $field, true );
        }

        return $meta;
    }

    /**
     * Render jobseeker browser shortcode.
     */
    public function shortcode_jobseeker_browser( $atts ) {
        $atts = shortcode_atts(
            array(
                'per_page' => 12,
            ),
            $atts,
            'oso_jobseeker_browser'
        );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in as an employer to browse jobseekers.', 'oso-employer-portal' ) . '</p>';
        }

        $user    = wp_get_current_user();
        
        // Check if user has employer role
        if ( ! in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true ) ) {
            return '<p>' . esc_html__( 'You do not have permission to browse jobseekers.', 'oso-employer-portal' ) . '</p>';
        }

        // Get pagination
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

        // Query jobseekers
        $args = array(
            'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
            'posts_per_page' => (int) $atts['per_page'],
            'paged'          => $paged,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $jobseekers = new WP_Query( $args );

        return $this->load_template(
            'jobseeker-browser.php',
            array(
                'jobseekers' => $jobseekers,
                'paged'      => $paged,
            )
        );
    }

    /**
     * Render single jobseeker profile shortcode.
     */
    public function shortcode_jobseeker_profile( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'oso_jobseeker_profile'
        );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in as an employer to view jobseeker profiles.', 'oso-employer-portal' ) . '</p>';
        }

        $user = wp_get_current_user();
        
        // Check if user has employer role
        if ( ! in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true ) ) {
            return '<p>' . esc_html__( 'You do not have permission to view jobseeker profiles.', 'oso-employer-portal' ) . '</p>';
        }

        // Get jobseeker ID from URL or shortcode attribute
        $jobseeker_id = ! empty( $atts['id'] ) ? (int) $atts['id'] : ( isset( $_GET['jobseeker_id'] ) ? (int) $_GET['jobseeker_id'] : 0 );

        if ( ! $jobseeker_id ) {
            return '<p>' . esc_html__( 'No jobseeker specified.', 'oso-employer-portal' ) . '</p>';
        }

        // Get jobseeker post
        $jobseeker = get_post( $jobseeker_id );

        if ( ! $jobseeker || OSO_Jobs_Portal::POST_TYPE_JOBSEEKER !== $jobseeker->post_type ) {
            return '<p>' . esc_html__( 'Jobseeker not found.', 'oso-employer-portal' ) . '</p>';
        }

        // Get jobseeker metadata
        if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
            $meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_id );
        } else {
            $meta = array();
        }

        return $this->load_template(
            'jobseeker-profile-view.php',
            array(
                'jobseeker' => $jobseeker,
                'meta'      => $meta,
            )
        );
    }

    /**
     * Load template file.
     *
     * @param string $template Template filename.
     * @param array  $data     Data to pass to template.
     * @return string
     */
    protected function load_template( $template, $data = array() ) {
        $template_path = OSO_EMPLOYER_PORTAL_DIR . 'includes/shortcodes/views/' . $template;

        if ( ! file_exists( $template_path ) ) {
            return '<p>' . esc_html__( 'Template not found.', 'oso-employer-portal' ) . '</p>';
        }

        ob_start();
        extract( $data ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        include $template_path;
        return ob_get_clean();
    }
}
