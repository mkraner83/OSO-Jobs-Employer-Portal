<?php
/**
 * Shortcodes for OSO Jobs Portal.
 *
 * @package OSO_Jobs_Portal\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register and render shortcodes.
 */
class OSO_Jobs_Shortcodes {

    /**
     * Singleton.
     *
     * @var OSO_Jobs_Shortcodes
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
        add_shortcode( 'oso_jobs_list', array( $this, 'shortcode_jobs_list' ) );
        add_shortcode( 'oso_job_submit', array( $this, 'shortcode_job_submit' ) );
        add_shortcode( 'oso_jobseeker_password_link', array( $this, 'shortcode_jobseeker_password_link' ) );
        add_shortcode( 'oso_jobseeker_profile', array( $this, 'shortcode_jobseeker_profile' ) );
        add_action( 'wp_ajax_oso_jobs_upload_file', array( $this, 'handle_profile_upload' ) );
        add_action( 'wp_ajax_oso_jobs_delete_file', array( $this, 'handle_profile_delete' ) );
    }

    /**
     * Render job listings shortcode.
     */
    public function shortcode_jobs_list( $atts ) {
        $atts = shortcode_atts(
            array(
                'department' => '',
                'per_page'   => 10,
            ),
            $atts,
            'oso_jobs_list'
        );

        $args = array(
            'post_type'      => OSO_Jobs_Portal::POST_TYPE,
            'posts_per_page' => (int) $atts['per_page'],
            'post_status'    => 'publish',
        );

        if ( ! empty( $atts['department'] ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => OSO_Jobs_Portal::TAXONOMY_DEPARTMENT,
                    'field'    => 'slug',
                    'terms'    => sanitize_title( $atts['department'] ),
                ),
            );
        }

        $query = new WP_Query( $args );

        $html = OSO_Jobs_Template_Loader::render(
            'includes/shortcodes/views/jobs-list.php',
            array(
                'query'    => $query,
                'settings' => OSO_Jobs_Utilities::get_settings(),
            )
        );

        wp_reset_postdata();

        return $html;
    }

    /**
     * Render job submission shortcode.
     */
    public function shortcode_job_submit( $atts ) {
        $atts = shortcode_atts(
            array(
                'form_id' => '',
            ),
            $atts,
            'oso_job_submit'
        );

        if ( empty( $atts['form_id'] ) ) {
            return '<p>' . esc_html__( 'Please provide a WPForms form_id attribute.', 'oso-jobs-portal' ) . '</p>';
        }

        $content = OSO_Jobs_Template_Loader::render(
            'includes/shortcodes/views/job-submit.php',
            array(
                'form_id'  => (int) $atts['form_id'],
                'settings' => OSO_Jobs_Utilities::get_settings(),
            )
        );

        return $content;
    }

    /**
     * Generate a password reset link for the supplied email.
     */
    public function shortcode_jobseeker_password_link( $atts ) {
        $atts = shortcode_atts(
            array(
                'email' => '',
                'label' => __( 'Set/Change Password', 'oso-jobs-portal' ),
            ),
            $atts,
            'oso_jobseeker_password_link'
        );

        $email = sanitize_email( $atts['email'] );
        if ( ! $email ) {
            return '';
        }

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            return esc_html__( 'Please check your email for password instructions.', 'oso-jobs-portal' );
        }

        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            return esc_html__( 'Unable to generate password link. Use the forgot password form.', 'oso-jobs-portal' );
        }

        $url = network_site_url(
            'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user->user_login ),
            'login'
        );

        return '<a href="' . esc_url( $url ) . '">' . esc_html( $atts['label'] ) . '</a>';
    }

    /**
     * Render a front-end profile editor for jobseekers.
     */
    public function shortcode_jobseeker_profile() {
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

            return OSO_Jobs_Template_Loader::render(
                'includes/shortcodes/views/jobseeker-profile.php',
                array(
                    'is_logged_in' => false,
                    'login_form'   => $login_form,
                    'lost_url'     => wp_lostpassword_url( home_url() ),
                )
            );
        }

        $user_id  = get_current_user_id();
        $post     = OSO_Jobs_Utilities::get_jobseeker_by_user( $user_id );
        $messages = array();

        if ( ! $post ) {
            return '<p>' . esc_html__( 'No jobseeker profile is associated with your account.', 'oso-jobs-portal' ) . '</p>';
        }

        $meta = OSO_Jobs_Utilities::get_jobseeker_meta( $post->ID );

        if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['oso_jobseeker_profile_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oso_jobseeker_profile_nonce'] ) ), 'oso_jobseeker_profile' ) ) {
            foreach ( $text_fields as $key => $config ) {
                $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
                if ( in_array( $key, array( 'resume_url', 'photo_url' ), true ) ) {
                    $value = esc_url_raw( $raw );
                } elseif ( 'email' === $key ) {
                    $value = sanitize_email( $raw );
                } elseif ( in_array( $key, array( 'availability_start', 'availability_end' ), true ) ) {
                    $value = OSO_Jobs_Utilities::normalize_date_value( $raw );
                } else {
                    $value = sanitize_text_field( $raw );
                }

                update_post_meta( $post->ID, $config['meta'], $value );
                $meta[ $config['meta'] ] = $value;

                if ( 'full_name' === $key && ! empty( $value ) ) {
                    wp_update_user(
                        array(
                            'ID'           => $user_id,
                            'display_name' => $value,
                        )
                    );
                }

                if ( 'email' === $key && ! empty( $value ) ) {
                    wp_update_user(
                        array(
                            'ID'         => $user_id,
                            'user_email' => $value,
                        )
                    );
                }
            }

            if ( isset( $_POST['why'] ) ) {
                $why = sanitize_textarea_field( wp_unslash( $_POST['why'] ) );
                $post->post_content = $why;
                wp_update_post(
                    array(
                        'ID'           => $post->ID,
                        'post_content' => $why,
                    )
                );
            }

            $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
            foreach ( $checkbox_groups as $key => $config ) {
                $raw = isset( $_POST[ $key ] ) ? (array) $_POST[ $key ] : array();
                $raw = array_map(
                    function ( $item ) {
                        return sanitize_text_field( wp_unslash( $item ) );
                    },
                    $raw
                );
                $value = OSO_Jobs_Utilities::array_to_meta_string( $raw );
                update_post_meta( $post->ID, $config['meta'], $value );
                $meta[ $config['meta'] ] = $value;
            }

            $meta = OSO_Jobs_Utilities::get_jobseeker_meta( $post->ID );
            $title = $meta['_oso_jobseeker_full_name'] ? $meta['_oso_jobseeker_full_name'] : $post->post_title;
            if ( $meta['_oso_jobseeker_full_name'] && $meta['_oso_jobseeker_location'] ) {
                $title = $meta['_oso_jobseeker_full_name'] . ' — ' . $meta['_oso_jobseeker_location'];
            }

            wp_update_post(
                array(
                    'ID'         => $post->ID,
                    'post_title' => $title,
                )
            );

            $messages[] = __( 'Profile updated.', 'oso-jobs-portal' );
        }

        return OSO_Jobs_Template_Loader::render(
            'includes/shortcodes/views/jobseeker-profile.php',
            array(
                'is_logged_in' => true,
                'jobseeker'    => $post,
                'meta'         => $meta,
                'messages'     => $messages,
            )
        );
    }

    /**
     * AJAX upload handler for resume/photo.
     */
    public function handle_profile_upload() {
        check_ajax_referer( 'oso_jobseeker_upload', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( __( 'Not allowed', 'oso-jobs-portal' ), 403 );
        }

        $field = isset( $_POST['field'] ) ? sanitize_key( $_POST['field'] ) : '';
        $allowed_fields = array(
            'resume_url' => array( 'mime' => array( 'application/pdf' ), 'size' => 5 * 1024 * 1024 ),
            'photo_url'  => array( 'mime' => array( 'image/jpeg' ), 'size' => 5 * 1024 * 1024 ),
        );

        if ( ! isset( $allowed_fields[ $field ], $_FILES['file'] ) ) {
            wp_send_json_error( __( 'Invalid upload', 'oso-jobs-portal' ), 400 );
        }

        $limits = $allowed_fields[ $field ];
        $file   = $_FILES['file'];

        if ( $file['size'] > $limits['size'] ) {
            wp_send_json_error( __( 'File too large.', 'oso-jobs-portal' ), 400 );
        }

        $mime = mime_content_type( $file['tmp_name'] );
        if ( ! in_array( $mime, $limits['mime'], true ) ) {
            wp_send_json_error( __( 'File type not allowed.', 'oso-jobs-portal' ), 400 );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        $uploaded = wp_handle_upload(
            $file,
            array(
                'test_form' => false,
            )
        );

        if ( isset( $uploaded['error'] ) ) {
            wp_send_json_error( $uploaded['error'], 400 );
        }

        wp_send_json_success( $uploaded['url'] );
    }

    /**
     * AJAX delete handler for resume/photo.
     */
    public function handle_profile_delete() {
        check_ajax_referer( 'oso_jobseeker_upload', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( __( 'Not allowed', 'oso-jobs-portal' ), 403 );
        }

        $field = isset( $_POST['field'] ) ? sanitize_key( $_POST['field'] ) : '';
        if ( ! in_array( $field, array( 'resume_url', 'photo_url' ), true ) ) {
            wp_send_json_error( __( 'Invalid request', 'oso-jobs-portal' ), 400 );
        }

        wp_send_json_success();
    }
}
