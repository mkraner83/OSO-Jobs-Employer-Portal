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
