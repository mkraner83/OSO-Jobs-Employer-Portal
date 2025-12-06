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
        add_shortcode( 'oso_employer_profile', array( $this, 'shortcode_employer_dashboard' ) ); // Alias
        add_shortcode( 'oso_employer_edit_profile', array( $this, 'shortcode_employer_edit_profile' ) );
        add_shortcode( 'oso_employer_add_job', array( $this, 'shortcode_employer_add_job' ) );
        add_shortcode( 'oso_job_browser', array( $this, 'shortcode_job_browser' ) );
        add_shortcode( 'oso_job_details', array( $this, 'shortcode_job_details' ) );
        add_shortcode( 'oso_jobseeker_browser', array( $this, 'shortcode_jobseeker_browser' ) );
        add_shortcode( 'oso_jobseeker_profile', array( $this, 'shortcode_jobseeker_profile' ) );
        add_shortcode( 'oso_jobseeker_dashboard', array( $this, 'shortcode_jobseeker_dashboard' ) );
        add_shortcode( 'oso_jobseeker_edit_profile', array( $this, 'shortcode_jobseeker_edit_profile' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_oso_update_jobseeker_profile', array( $this, 'ajax_update_jobseeker_profile' ) );
        add_action( 'wp_ajax_oso_update_employer_profile', array( $this, 'ajax_update_employer_profile' ) );
        add_action( 'wp_ajax_oso_upload_profile_file', array( $this, 'ajax_upload_profile_file' ) );
        add_action( 'wp_ajax_oso_submit_job_application', array( $this, 'ajax_submit_job_application' ) );
        add_action( 'wp_ajax_oso_update_application_status', array( $this, 'ajax_update_application_status' ) );
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
        // Get ALL meta fields for this employer
        $fields = array(
            '_oso_employer_company',
            '_oso_employer_email',
            '_oso_employer_website',
            '_oso_employer_description',
            '_oso_employer_camp_types',
            '_oso_employer_state',
            '_oso_employer_address',
            '_oso_employer_major_city',
            '_oso_employer_training_start',
            '_oso_employer_housing',
            '_oso_employer_social_links',
            '_oso_employer_subscription_type',
            '_oso_employer_subscription_ends',
            '_oso_employer_logo',
            '_oso_employer_photos',
            '_oso_employer_approved',
            '_oso_employer_user_id',
            '_oso_employer_wpforms_entry',
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

        // Check if employer is approved (unless admin)
        if ( ! current_user_can( 'manage_options' ) ) {
            $employer_post = $this->get_employer_by_user( $user->ID );
            if ( $employer_post ) {
                $approved = get_post_meta( $employer_post->ID, '_oso_employer_approved', true );
                $subscription_ends = get_post_meta( $employer_post->ID, '_oso_employer_subscription_ends', true );
                
                // Check if not approved
                if ( $approved !== '1' ) {
                    return '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; margin: 20px 0;">' .
                           '<p style="margin: 0; color: #856404;"><strong>' . esc_html__( 'Account Pending Approval', 'oso-employer-portal' ) . '</strong></p>' .
                           '<p style="margin: 10px 0 0 0; color: #856404;">' . esc_html__( 'Your employer account is currently pending approval. You will be able to browse jobseekers once an administrator approves your account.', 'oso-employer-portal' ) . '</p>' .
                           '</div>';
                }
                
                // Check if subscription expired
                if ( ! empty( $subscription_ends ) ) {
                    $expiration_date = strtotime( $subscription_ends );
                    if ( $expiration_date && $expiration_date < time() ) {
                        return '<div style="padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; margin: 20px 0;">' .
                               '<p style="margin: 0; color: #721c24;"><strong>' . esc_html__( 'Subscription Expired', 'oso-employer-portal' ) . '</strong></p>' .
                               '<p style="margin: 10px 0 0 0; color: #721c24;">' . sprintf( esc_html__( 'Your subscription expired on %s. Please renew your subscription to continue browsing jobseekers.', 'oso-employer-portal' ), date_i18n( get_option( 'date_format' ), $expiration_date ) ) . '</p>' .
                               '</div>';
                    }
                }
            }
        }

        // Get pagination
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

        // Build query args
        $args = array(
            'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
            'posts_per_page' => (int) $atts['per_page'],
            'paged'          => $paged,
            'post_status'    => 'publish',
        );

        // Handle sorting
        $sort = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : 'date_desc';
        switch ( $sort ) {
            case 'date_asc':
                $args['orderby'] = 'date';
                $args['order']   = 'ASC';
                break;
            case 'name_asc':
                $args['orderby']  = 'meta_value';
                $args['meta_key'] = '_oso_jobseeker_full_name';
                $args['order']    = 'ASC';
                break;
            case 'name_desc':
                $args['orderby']  = 'meta_value';
                $args['meta_key'] = '_oso_jobseeker_full_name';
                $args['order']    = 'DESC';
                break;
            default: // date_desc
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
        }

        // Handle search
        if ( ! empty( $_GET['search'] ) ) {
            $search = sanitize_text_field( $_GET['search'] );
            $args['s'] = $search;
            
            // Also search in meta fields
            add_filter( 'posts_search', array( $this, 'extend_jobseeker_search' ), 10, 2 );
        }

        // Build meta query for filters
        $meta_query = array( 'relation' => 'AND' );

        // Location filter
        if ( ! empty( $_GET['location'] ) ) {
            $meta_query[] = array(
                'key'     => '_oso_jobseeker_location',
                'value'   => sanitize_text_field( $_GET['location'] ),
                'compare' => '=',
            );
        }

        // Over 18 filter
        if ( ! empty( $_GET['over_18'] ) ) {
            $over_18_value = sanitize_text_field( $_GET['over_18'] );
            if ( $over_18_value === 'yes' ) {
                $meta_query[] = array(
                    'key'     => '_oso_jobseeker_over_18',
                    'value'   => 'Yes',
                    'compare' => 'LIKE',
                );
            } elseif ( $over_18_value === 'no' ) {
                $meta_query[] = array(
                    'key'     => '_oso_jobseeker_over_18',
                    'value'   => 'No',
                    'compare' => 'LIKE',
                );
            }
        }

        // Checkbox filters (skills, interests, certifications, etc.)
        if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
            $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
            
            foreach ( $checkbox_groups as $key => $config ) {
                if ( ! empty( $_GET[ $key ] ) && is_array( $_GET[ $key ] ) ) {
                    $selected = array_map( 'sanitize_text_field', $_GET[ $key ] );
                    
                    $meta_query[] = array(
                        'key'     => $config['meta'],
                        'value'   => $selected,
                        'compare' => 'REGEXP',
                    );
                }
            }
        }

        if ( count( $meta_query ) > 1 ) {
            $args['meta_query'] = $meta_query;
        }

        $jobseekers = new WP_Query( $args );

        // Remove search filter
        remove_filter( 'posts_search', array( $this, 'extend_jobseeker_search' ), 10 );

        return $this->load_template(
            'jobseeker-browser.php',
            array(
                'jobseekers' => $jobseekers,
                'paged'      => $paged,
            )
        );
    }

    /**
     * Extend search to include meta fields.
     *
     * @param string   $search Search SQL.
     * @param WP_Query $query  Query object.
     * @return string
     */
    public function extend_jobseeker_search( $search, $query ) {
        global $wpdb;

        if ( empty( $search ) || ! $query->is_main_query() ) {
            return $search;
        }

        $search_term = $query->get( 's' );
        if ( empty( $search_term ) ) {
            return $search;
        }

        // Add meta fields to search
        $meta_keys = array(
            '_oso_jobseeker_full_name',
            '_oso_jobseeker_email',
            '_oso_jobseeker_location',
        );

        $meta_search = '';
        foreach ( $meta_keys as $meta_key ) {
            $meta_search .= " OR (meta.meta_key = '" . esc_sql( $meta_key ) . "' AND meta.meta_value LIKE '%" . esc_sql( $wpdb->esc_like( $search_term ) ) . "%')";
        }

        if ( ! empty( $meta_search ) ) {
            $search = preg_replace(
                '/\(\(\(/',
                "((({$wpdb->posts}.ID IN (SELECT DISTINCT post_id FROM {$wpdb->postmeta} meta WHERE 1=1 {$meta_search})) OR (",
                $search
            );
            $search .= ')';
        }

        return $search;
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
            return '<p>' . esc_html__( 'You must be logged in to view jobseeker profiles.', 'oso-employer-portal' ) . '</p>';
        }

        $user = wp_get_current_user();
        
        // Get jobseeker ID from shortcode attribute or URL parameter (for employers viewing)
        $jobseeker_id = ! empty( $atts['id'] ) ? (int) $atts['id'] : ( isset( $_GET['jobseeker_id'] ) ? (int) $_GET['jobseeker_id'] : 0 );
        
        // If no ID provided and user is a jobseeker, show their own profile
        if ( ! $jobseeker_id && in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
            // Find jobseeker post linked to this user
            $jobseeker_posts = get_posts([
                'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
                'posts_per_page' => 1,
                'meta_query'     => [
                    [
                        'key'     => '_oso_jobseeker_email',
                        'value'   => $user->user_email,
                        'compare' => '=',
                    ],
                ],
            ]);
            
            if ( ! empty( $jobseeker_posts ) ) {
                $jobseeker_id = $jobseeker_posts[0]->ID;
            }
        }
        
        // Check permissions: employers can view any profile, jobseekers can only view their own
        $is_employer = in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true );
        $is_jobseeker = in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $user->roles, true );
        $is_admin = in_array( 'administrator', (array) $user->roles, true );

        // Check if employer is approved (unless admin)
        if ( $is_employer && ! $is_admin ) {
            $employer_post = $this->get_employer_by_user( $user->ID );
            if ( $employer_post ) {
                $approved = get_post_meta( $employer_post->ID, '_oso_employer_approved', true );
                $subscription_ends = get_post_meta( $employer_post->ID, '_oso_employer_subscription_ends', true );
                
                if ( $approved !== '1' ) {
                    return '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; margin: 20px 0;">' .
                           '<p style="margin: 0; color: #856404;"><strong>' . esc_html__( 'Account Pending Approval', 'oso-employer-portal' ) . '</strong></p>' .
                           '<p style="margin: 10px 0 0 0; color: #856404;">' . esc_html__( 'Your employer account is currently pending approval. You will be able to view jobseeker profiles once an administrator approves your account.', 'oso-employer-portal' ) . '</p>' .
                           '</div>';
                }
                
                // Check if subscription expired
                if ( ! empty( $subscription_ends ) ) {
                    $expiration_date = strtotime( $subscription_ends );
                    if ( $expiration_date && $expiration_date < time() ) {
                        return '<div style="padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 8px; margin: 20px 0;">' .
                               '<p style="margin: 0; color: #721c24;"><strong>' . esc_html__( 'Subscription Expired', 'oso-employer-portal' ) . '</strong></p>' .
                               '<p style="margin: 10px 0 0 0; color: #721c24;">' . sprintf( esc_html__( 'Your subscription expired on %s. Please renew your subscription to continue viewing jobseeker profiles.', 'oso-employer-portal' ), date_i18n( get_option( 'date_format' ), $expiration_date ) ) . '</p>' .
                               '</div>';
                    }
                }
            }
        }
        
        if ( ! $is_employer && ! $is_admin && $is_jobseeker && $jobseeker_id ) {
            // Jobseekers can only view their own profile
            $jobseeker_email = get_post_meta( $jobseeker_id, '_oso_jobseeker_email', true );
            if ( $jobseeker_email !== $user->user_email ) {
                return '<p>' . esc_html__( 'You do not have permission to view this profile.', 'oso-employer-portal' ) . '</p>';
            }
        } elseif ( ! $is_employer && ! $is_admin && ! $is_jobseeker ) {
            return '<p>' . esc_html__( 'You do not have permission to view jobseeker profiles.', 'oso-employer-portal' ) . '</p>';
        }

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
     * Render jobseeker edit profile shortcode.
     */
    /**
     * Render jobseeker dashboard shortcode.
     */
    public function shortcode_jobseeker_dashboard( $atts ) {
        $atts = shortcode_atts(
            array(
                'redirect_url' => wp_login_url(),
            ),
            $atts,
            'oso_jobseeker_dashboard'
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
                'jobseeker-dashboard.php',
                array(
                    'is_logged_in' => false,
                    'login_form'   => $login_form,
                    'lost_url'     => wp_lostpassword_url( $current_url ),
                )
            );
        }

        $user = wp_get_current_user();

        // Check if user has jobseeker role
        if ( ! in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
            return '<p>' . esc_html__( 'You do not have permission to access the jobseeker dashboard.', 'oso-employer-portal' ) . '</p>';
        }

        // Find jobseeker post linked to this user
        $jobseeker_posts = get_posts( array(
            'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'     => '_oso_jobseeker_email',
                    'value'   => $user->user_email,
                    'compare' => '=',
                ),
            ),
        ) );

        if ( empty( $jobseeker_posts ) ) {
            return '<p>' . esc_html__( 'No jobseeker profile found for your account.', 'oso-employer-portal' ) . '</p>';
        }

        $jobseeker_post = $jobseeker_posts[0];

        // Get jobseeker metadata
        if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
            $meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_post->ID );
        } else {
            $meta = array();
        }

        return $this->load_template(
            'jobseeker-dashboard.php',
            array(
                'is_logged_in'   => true,
                'jobseeker_post' => $jobseeker_post,
                'meta'           => $meta,
                'user'           => $user,
            )
        );
    }

    public function shortcode_jobseeker_edit_profile( $atts ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in to edit your profile.', 'oso-employer-portal' ) . '</p>';
        }

        $user = wp_get_current_user();
        
        // Only jobseekers can edit their profile
        if ( ! in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
            return '<p>' . esc_html__( 'Only jobseekers can edit their profile.', 'oso-employer-portal' ) . '</p>';
        }
        
        // Find jobseeker post linked to this user
        $jobseeker_posts = get_posts([
            'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'     => '_oso_jobseeker_email',
                    'value'   => $user->user_email,
                    'compare' => '=',
                ],
            ],
        ]);
        
        if ( empty( $jobseeker_posts ) ) {
            return '<p>' . esc_html__( 'No jobseeker profile found for your account.', 'oso-employer-portal' ) . '</p>';
        }
        
        $jobseeker = $jobseeker_posts[0];
        
        // Get jobseeker metadata
        if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
            $meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker->ID );
        } else {
            $meta = array();
        }

        return $this->load_template(
            'jobseeker-edit-profile.php',
            array(
                'jobseeker' => $jobseeker,
                'meta'      => $meta,
            )
        );
    }

    /**
     * AJAX handler to update jobseeker profile.
     */
    public function ajax_update_jobseeker_profile() {
        check_ajax_referer( 'oso_update_jobseeker_profile', 'nonce' );
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'oso-employer-portal' ) ) );
        }
        
        $user = wp_get_current_user();
        
        // Only jobseekers can edit their profile
        if ( ! in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this profile.', 'oso-employer-portal' ) ) );
        }
        
        $jobseeker_id = isset( $_POST['jobseeker_id'] ) ? (int) $_POST['jobseeker_id'] : 0;
        
        if ( ! $jobseeker_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid jobseeker ID.', 'oso-employer-portal' ) ) );
        }
        
        // Verify this jobseeker belongs to the current user
        $jobseeker_email = get_post_meta( $jobseeker_id, '_oso_jobseeker_email', true );
        if ( $jobseeker_email !== $user->user_email ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this profile.', 'oso-employer-portal' ) ) );
        }
        
        // Update post content (why interested)
        if ( isset( $_POST['why_interested'] ) ) {
            wp_update_post( array(
                'ID'           => $jobseeker_id,
                'post_content' => sanitize_textarea_field( $_POST['why_interested'] ),
            ) );
        }
        
        // Update post title (full name)
        if ( isset( $_POST['full_name'] ) ) {
            wp_update_post( array(
                'ID'         => $jobseeker_id,
                'post_title' => sanitize_text_field( $_POST['full_name'] ),
            ) );
            update_post_meta( $jobseeker_id, '_oso_jobseeker_full_name', sanitize_text_field( $_POST['full_name'] ) );
        }
        
        // Update text fields
        $text_fields = array(
            'email'             => '_oso_jobseeker_email',
            'location'          => '_oso_jobseeker_location',
            'availability_start'=> '_oso_jobseeker_availability_start',
            'availability_end'  => '_oso_jobseeker_availability_end',
            'photo_url'         => '_oso_jobseeker_photo',
            'resume_url'        => '_oso_jobseeker_resume',
        );
        
        foreach ( $text_fields as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = sanitize_text_field( $_POST[ $field ] );
                update_post_meta( $jobseeker_id, $meta_key, $value );
            }
        }
        
        // Update checkbox groups
        $checkbox_groups = class_exists( 'OSO_Jobs_Utilities' ) ? OSO_Jobs_Utilities::get_jobseeker_checkbox_groups() : array();
        
        foreach ( $checkbox_groups as $key => $config ) {
            $posted_values = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', $_POST[ $key ] ) : array();
            $value_string = ! empty( $posted_values ) ? implode( ', ', $posted_values ) : '';
            update_post_meta( $jobseeker_id, $config['meta'], $value_string );
        }
        
        wp_send_json_success( array( 
            'message'     => __( 'Profile updated successfully!', 'oso-employer-portal' ),
            'redirect_url'=> home_url( '/job-portal/jobseeker-profile/' ),
        ) );
    }

    /**
     * AJAX handler to upload profile files (photo/resume).
     */
    public function ajax_upload_profile_file() {
        check_ajax_referer( 'oso_upload_profile_file', 'nonce' );
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'oso-employer-portal' ) ) );
        }
        
        $user = wp_get_current_user();
        
        // Allow both jobseekers and employers to upload files
        if ( ! in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $user->roles, true ) && 
             ! in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to upload files.', 'oso-employer-portal' ) ) );
        }
        
        if ( empty( $_FILES['file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'oso-employer-portal' ) ) );
        }
        
        // Check file size (16MB max)
        if ( $_FILES['file']['size'] > 16 * 1024 * 1024 ) {
            wp_send_json_error( array( 'message' => __( 'File size must be less than 16MB.', 'oso-employer-portal' ) ) );
        }
        
        $file_type = isset( $_POST['file_type'] ) ? sanitize_text_field( $_POST['file_type'] ) : '';
        
        // Set allowed file types
        $allowed_types = array();
        if ( $file_type === 'photo' || $file_type === 'logo' ) {
            $allowed_types = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
        } elseif ( $file_type === 'resume' ) {
            $allowed_types = array( 'pdf', 'doc', 'docx' );
        }
        
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        $upload_overrides = array(
            'test_form' => false,
            'mimes'     => array(),
        );
        
        // Add MIME types
        foreach ( $allowed_types as $ext ) {
            $mime = wp_check_filetype( 'file.' . $ext );
            if ( ! empty( $mime['type'] ) ) {
                $upload_overrides['mimes'][ $ext ] = $mime['type'];
            }
        }
        
        $file = wp_handle_upload( $_FILES['file'], $upload_overrides );
        
        if ( isset( $file['error'] ) ) {
            wp_send_json_error( array( 'message' => $file['error'] ) );
        }
        
        wp_send_json_success( array( 
            'url'     => $file['url'],
            'message' => __( 'File uploaded successfully!', 'oso-employer-portal' ),
        ) );
    }

    /**
     * Render employer edit profile shortcode.
     */
    public function shortcode_employer_edit_profile( $atts ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in to edit your profile.', 'oso-employer-portal' ) . '</p>';
        }

        $user = wp_get_current_user();
        
        // Only employers can edit their profile
        if ( ! in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true ) ) {
            return '<p>' . esc_html__( 'Only employers can edit their profile.', 'oso-employer-portal' ) . '</p>';
        }
        
        // Get employer post linked to this user
        $employer = $this->get_employer_by_user( $user->ID );
        
        if ( ! $employer ) {
            return '<p>' . esc_html__( 'No employer profile found for your account.', 'oso-employer-portal' ) . '</p>';
        }
        
        // Get employer metadata
        $meta = $this->get_employer_meta( $employer->ID );

        return $this->load_template(
            'employer-edit-profile.php',
            array(
                'employer' => $employer,
                'meta'     => $meta,
            )
        );
    }

    /**
     * AJAX handler to update employer profile.
     */
    public function ajax_update_employer_profile() {
        error_log( 'OSO Employer Profile Update: Starting...' );
        error_log( 'POST data: ' . print_r( $_POST, true ) );
        
        check_ajax_referer( 'oso_update_employer_profile', 'nonce' );
        
        if ( ! is_user_logged_in() ) {
            error_log( 'OSO Employer Profile Update: User not logged in' );
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'oso-employer-portal' ) ) );
        }
        
        $user = wp_get_current_user();
        
        // Only employers can edit their profile
        if ( ! in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this profile.', 'oso-employer-portal' ) ) );
        }
        
        $employer_id = isset( $_POST['employer_id'] ) ? (int) $_POST['employer_id'] : 0;
        
        if ( ! $employer_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid employer ID.', 'oso-employer-portal' ) ) );
        }
        
        // Verify this employer belongs to the current user
        $employer_user_id = get_post_meta( $employer_id, '_oso_employer_user_id', true );
        if ( (int) $employer_user_id !== $user->ID ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this profile.', 'oso-employer-portal' ) ) );
        }
        
        // Update post title (camp name)
        if ( isset( $_POST['camp_name'] ) ) {
            wp_update_post( array(
                'ID'         => $employer_id,
                'post_title' => sanitize_text_field( $_POST['camp_name'] ),
            ) );
            update_post_meta( $employer_id, '_oso_employer_company', sanitize_text_field( $_POST['camp_name'] ) );
        }
        
        // Update all employer fields (matching WPForms structure)
        $fields_to_update = array(
            'email'            => '_oso_employer_email',
            'website'          => '_oso_employer_website',
            'description'      => '_oso_employer_description',
            'camp_types'       => '_oso_employer_camp_types',
            'state'            => '_oso_employer_state',
            'address'          => '_oso_employer_address',
            'major_city'       => '_oso_employer_major_city',
            'training_start'   => '_oso_employer_training_start',
            'housing'          => '_oso_employer_housing',
            'social_links'     => '_oso_employer_social_links',
            'logo_url'         => '_oso_employer_logo',
            'photos_urls'      => '_oso_employer_photos',
        );
        
        $updated_fields = array();
        foreach ( $fields_to_update as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = $_POST[ $field ];
                
                // Sanitize based on field type
                if ( in_array( $field, array( 'description', 'camp_types', 'social_links', 'photos_urls' ) ) ) {
                    $value = sanitize_textarea_field( $value );
                } elseif ( $field === 'website' ) {
                    // Auto-add https:// if not present
                    $value = trim( $value );
                    if ( ! empty( $value ) && ! preg_match( '~^https?://~i', $value ) ) {
                        $value = 'https://' . $value;
                    }
                    $value = esc_url_raw( $value );
                } elseif ( $field === 'email' ) {
                    $value = sanitize_email( $value );
                } elseif ( $field === 'training_start' ) {
                    // Convert HTML date format (YYYY-MM-DD) to display format (MM/DD/YYYY)
                    $value = sanitize_text_field( $value );
                    if ( ! empty( $value ) ) {
                        $date_obj = DateTime::createFromFormat( 'Y-m-d', $value );
                        if ( $date_obj ) {
                            $value = $date_obj->format( 'm/d/Y' );
                        }
                    }
                } elseif ( $field === 'logo_url' ) {
                    $value = esc_url_raw( $value );
                } else {
                    $value = sanitize_text_field( $value );
                }
                
                $result = update_post_meta( $employer_id, $meta_key, $value );
                $updated_fields[ $field ] = array(
                    'meta_key' => $meta_key,
                    'value' => $value,
                    'result' => $result
                );
            }
        }
        
        error_log( 'OSO Employer Profile Update: Updated fields - ' . print_r( $updated_fields, true ) );
        
        wp_send_json_success( array( 
            'message'     => __( 'Profile updated successfully!', 'oso-employer-portal' ),
            'redirect_url'=> home_url( '/job-portal/employer-profile/' ),
            'debug' => $updated_fields
        ) );
    }

    /**
     * Render add/edit job posting form shortcode.
     */
    public function shortcode_employer_add_job( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in to post jobs.', 'oso-employer-portal' ) . '</p>';
        }

        $user = wp_get_current_user();
        if ( ! in_array( 'oso_employer', $user->roles ) && ! current_user_can( 'manage_options' ) ) {
            return '<p>' . esc_html__( 'You do not have permission to post jobs.', 'oso-employer-portal' ) . '</p>';
        }

        return $this->load_template( 'employer-add-job.php' );
    }

    /**
     * Job Browser Shortcode - Public job listings.
     *
     * @return string
     */
    public function shortcode_job_browser() {
        return $this->load_template( 'job-browser.php' );
    }

    /**
     * Job Details Shortcode - Single job view with application form.
     *
     * @return string
     */
    public function shortcode_job_details() {
        return $this->load_template( 'job-details.php' );
    }

    /**
     * AJAX handler for job application submission.
     */
    public function ajax_submit_job_application() {
        // Enable error logging for debugging
        error_log( 'OSO Job Application: AJAX handler called' );
        
        // Verify nonce
        if ( ! check_ajax_referer( 'oso-job-nonce', 'nonce', false ) ) {
            error_log( 'OSO Job Application: Nonce verification failed' );
            wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'oso-employer-portal' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            error_log( 'OSO Job Application: User not logged in' );
            wp_send_json_error( array( 'message' => __( 'You must be logged in to apply.', 'oso-employer-portal' ) ) );
        }

        $job_id = isset( $_POST['job_id'] ) ? intval( $_POST['job_id'] ) : 0;
        $jobseeker_id = isset( $_POST['jobseeker_id'] ) ? intval( $_POST['jobseeker_id'] ) : 0;
        $cover_letter = isset( $_POST['cover_letter'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cover_letter'] ) ) : '';

        error_log( sprintf( 'OSO Job Application: Job ID: %d, Jobseeker ID: %d', $job_id, $jobseeker_id ) );

        // Validate inputs
        if ( ! $job_id || ! $jobseeker_id || empty( $cover_letter ) ) {
            error_log( 'OSO Job Application: Validation failed - missing required fields' );
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'oso-employer-portal' ) ) );
        }

        // Verify job exists and is not expired
        $job = get_post( $job_id );
        if ( ! $job || $job->post_type !== 'oso_job_posting' || $job->post_status !== 'publish' ) {
            wp_send_json_error( array( 'message' => __( 'Invalid job posting.', 'oso-employer-portal' ) ) );
        }

        if ( OSO_Job_Manager::instance()->is_job_expired( $job_id ) ) {
            wp_send_json_error( array( 'message' => __( 'This job posting has expired.', 'oso-employer-portal' ) ) );
        }

        // Verify jobseeker profile belongs to current user
        $jobseeker_user_id = get_post_meta( $jobseeker_id, '_oso_jobseeker_user_id', true );
        if ( intval( $jobseeker_user_id ) !== get_current_user_id() ) {
            wp_send_json_error( array( 'message' => __( 'Invalid jobseeker profile.', 'oso-employer-portal' ) ) );
        }

        // Check for duplicate application
        $existing = get_posts( array(
            'post_type'      => 'oso_job_application',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_oso_application_job_id',
                    'value' => $job_id,
                ),
                array(
                    'key'   => '_oso_application_jobseeker_id',
                    'value' => $jobseeker_id,
                ),
            ),
        ) );

        if ( ! empty( $existing ) ) {
            wp_send_json_error( array( 'message' => __( 'You have already applied for this position.', 'oso-employer-portal' ) ) );
        }

        // Get employer info
        $employer_id = get_post_meta( $job_id, '_oso_job_employer_id', true );

        // Create application post
        $application_data = array(
            'post_type'   => 'oso_job_application',
            'post_title'  => get_the_title( $jobseeker_id ) . ' - ' . get_the_title( $job_id ),
            'post_status' => 'publish',
            'post_content' => $cover_letter,
        );

        $application_id = wp_insert_post( $application_data );

        if ( is_wp_error( $application_id ) ) {
            error_log( 'OSO Job Application: Failed to create application post - ' . $application_id->get_error_message() );
            wp_send_json_error( array( 'message' => __( 'Failed to submit application. Please try again.', 'oso-employer-portal' ) ) );
        }

        error_log( sprintf( 'OSO Job Application: Application created with ID: %d', $application_id ) );

        // Save application meta
        update_post_meta( $application_id, '_oso_application_job_id', $job_id );
        update_post_meta( $application_id, '_oso_application_jobseeker_id', $jobseeker_id );
        update_post_meta( $application_id, '_oso_application_employer_id', $employer_id );
        update_post_meta( $application_id, '_oso_application_status', 'pending' );
        update_post_meta( $application_id, '_oso_application_date', current_time( 'mysql' ) );

        // Send email notification to employer
        $this->send_application_notification( $application_id );

        error_log( 'OSO Job Application: Application submitted successfully' );
        wp_send_json_success( array( 'message' => __( 'Application submitted successfully!', 'oso-employer-portal' ) ) );
    }

    /**
     * Send email notification to employer about new application.
     *
     * @param int $application_id Application post ID.
     */
    private function send_application_notification( $application_id ) {
        $job_id = get_post_meta( $application_id, '_oso_application_job_id', true );
        $jobseeker_id = get_post_meta( $application_id, '_oso_application_jobseeker_id', true );
        $employer_id = get_post_meta( $application_id, '_oso_application_employer_id', true );

        // Get employer email
        $employer_user_id = get_post_meta( $employer_id, '_oso_employer_user_id', true );
        $employer_user = get_userdata( $employer_user_id );
        
        if ( ! $employer_user ) {
            return;
        }

        $job_title = get_the_title( $job_id );
        $jobseeker_name = get_the_title( $jobseeker_id );
        $camp_name = get_post_meta( $employer_id, '_oso_employer_company', true );
        
        // Get jobseeker contact info
        $jobseeker_email = get_post_meta( $jobseeker_id, '_oso_jobseeker_email', true );
        $jobseeker_phone = get_post_meta( $jobseeker_id, '_oso_jobseeker_phone', true );
        
        // Get application message
        $application_post = get_post( $application_id );
        $message_content = $application_post ? $application_post->post_content : '';

        $subject = sprintf( __( '🎉 New Application for %s - %s', 'oso-employer-portal' ), $job_title, $jobseeker_name );
        
        $message = sprintf(
            __( "Hello %s,\n\nGreat news! You have received a new job application.\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nAPPLICATION DETAILS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nPosition: %s\nApplicant: %s\nEmail: %s\nPhone: %s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nMESSAGE FROM APPLICANT\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n%s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nYou can view the full applicant profile and manage this application in your employer dashboard:\n%s\n\nBest regards,\nOSO Jobs Team", 'oso-employer-portal' ),
            $camp_name,
            $job_title,
            $jobseeker_name,
            $jobseeker_email ?: 'Not provided',
            $jobseeker_phone ?: 'Not provided',
            $message_content,
            home_url( '/job-portal/employer-profile/' )
        );

        wp_mail( $employer_user->user_email, $subject, $message );
    }

    /**
     * AJAX handler to update application status.
     */
    public function ajax_update_application_status() {
        check_ajax_referer( 'oso-job-nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'oso-employer-portal' ) ) );
        }

        $application_id = isset( $_POST['application_id'] ) ? intval( $_POST['application_id'] ) : 0;
        $status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';

        // Validate inputs
        if ( ! $application_id || ! in_array( $status, array( 'pending', 'approved', 'rejected' ), true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'oso-employer-portal' ) ) );
        }

        // Get application
        $application = get_post( $application_id );
        if ( ! $application || $application->post_type !== 'oso_job_application' ) {
            wp_send_json_error( array( 'message' => __( 'Application not found.', 'oso-employer-portal' ) ) );
        }

        // Verify employer owns this application
        $employer_id = get_post_meta( $application_id, '_oso_application_employer_id', true );
        $current_user_id = get_current_user_id();
        
        // Get employer post by user ID
        $employer_posts = get_posts( array(
            'post_type'      => 'oso_employer',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_key'       => '_oso_employer_user_id',
            'meta_value'     => $current_user_id,
        ) );

        if ( empty( $employer_posts ) || intval( $employer_posts[0]->ID ) !== intval( $employer_id ) ) {
            // Not the owner unless admin
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => __( 'You do not have permission to update this application.', 'oso-employer-portal' ) ) );
            }
        }

        // Update status
        $old_status = get_post_meta( $application_id, '_oso_application_status', true );
        $job_id = get_post_meta( $application_id, '_oso_application_job_id', true );
        
        update_post_meta( $application_id, '_oso_application_status', $status );

        // Handle position count changes
        if ( $job_id ) {
            // If changing TO approved from another status, decrease count
            if ( $status === 'approved' && $old_status !== 'approved' ) {
                $positions = (int) get_post_meta( $job_id, '_oso_job_positions', true );
                if ( $positions > 0 ) {
                    update_post_meta( $job_id, '_oso_job_positions', $positions - 1 );
                }
                
                // Send notifications
                $this->send_approval_notification( $application_id );
                $this->send_admin_approval_notification( $application_id );
            }
            
            // If changing FROM approved to another status, increase count back
            if ( $old_status === 'approved' && $status !== 'approved' ) {
                $positions = (int) get_post_meta( $job_id, '_oso_job_positions', true );
                update_post_meta( $job_id, '_oso_job_positions', $positions + 1 );
            }
        }

        wp_send_json_success( array( 'message' => __( 'Application status updated.', 'oso-employer-portal' ) ) );
    }

    /**
     * Send email notification to jobseeker when application is approved.
     *
     * @param int $application_id Application post ID.
     */
    private function send_approval_notification( $application_id ) {
        $job_id = get_post_meta( $application_id, '_oso_application_job_id', true );
        $jobseeker_id = get_post_meta( $application_id, '_oso_application_jobseeker_id', true );
        $employer_id = get_post_meta( $application_id, '_oso_application_employer_id', true );

        // Get jobseeker email
        $jobseeker_user_id = get_post_meta( $jobseeker_id, '_oso_jobseeker_user_id', true );
        $jobseeker_user = get_userdata( $jobseeker_user_id );
        
        if ( ! $jobseeker_user ) {
            return;
        }

        // Get data for email
        $job_title = get_the_title( $job_id );
        $jobseeker_name = get_the_title( $jobseeker_id );
        $camp_name = get_post_meta( $employer_id, '_oso_employer_company', true );
        $employer_email = get_post_meta( $employer_id, '_oso_employer_email', true );
        $employer_phone = get_post_meta( $employer_id, '_oso_employer_phone', true );
        $employer_website = get_post_meta( $employer_id, '_oso_employer_website', true );
        
        // Get job details
        $job_start_date = get_post_meta( $job_id, '_oso_job_start_date', true );
        $job_compensation = get_post_meta( $job_id, '_oso_job_compensation', true );

        $subject = sprintf( __( '🎉 Congratulations! Your Application Has Been Approved - %s', 'oso-employer-portal' ), $camp_name );
        
        $contact_info = '';
        if ( $employer_email ) {
            $contact_info .= sprintf( "\nEmail: %s", $employer_email );
        }
        if ( $employer_phone ) {
            $contact_info .= sprintf( "\nPhone: %s", $employer_phone );
        }
        if ( $employer_website ) {
            $contact_info .= sprintf( "\nWebsite: %s", $employer_website );
        }
        
        $message = sprintf(
            __( "Hello %s,\n\nExciting news! Your application has been approved!\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nJOB DETAILS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nPosition: %s\nEmployer: %s%s%s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nNEXT STEPS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nThe employer will contact you directly to discuss the next steps. Please make sure to respond promptly to their communication.\n\nYou can view this application and all your other applications in your dashboard:\n%s\n\nGood luck with your new position!\n\nBest regards,\nOSO Jobs Team", 'oso-employer-portal' ),
            $jobseeker_name,
            $job_title,
            $camp_name,
            $contact_info,
            $job_start_date ? sprintf( "\nStart Date: %s", date_i18n( 'F j, Y', strtotime( $job_start_date ) ) ) : '',
            home_url( '/job-portal/jobseeker-dashboard/' )
        );

        wp_mail( $jobseeker_user->user_email, $subject, $message );
    }

    /**
     * Send email notification to admin when employer approves application.
     *
     * @param int $application_id Application post ID.
     */
    private function send_admin_approval_notification( $application_id ) {
        $job_id = get_post_meta( $application_id, '_oso_application_job_id', true );
        $jobseeker_id = get_post_meta( $application_id, '_oso_application_jobseeker_id', true );
        $employer_id = get_post_meta( $application_id, '_oso_application_employer_id', true );

        // Get admin email
        $admin_email = get_option( 'admin_email' );
        
        if ( ! $admin_email ) {
            return;
        }

        // Get all the details
        $job_title = get_the_title( $job_id );
        $jobseeker_name = get_the_title( $jobseeker_id );
        $camp_name = get_post_meta( $employer_id, '_oso_employer_company', true );
        
        // Get employer details
        $employer_user_id = get_post_meta( $employer_id, '_oso_employer_user_id', true );
        $employer_user = get_userdata( $employer_user_id );
        $employer_email = get_post_meta( $employer_id, '_oso_employer_email', true );
        $employer_phone = get_post_meta( $employer_id, '_oso_employer_phone', true );
        
        // Get jobseeker details
        $jobseeker_user_id = get_post_meta( $jobseeker_id, '_oso_jobseeker_user_id', true );
        $jobseeker_user = get_userdata( $jobseeker_user_id );
        $jobseeker_email = get_post_meta( $jobseeker_id, '_oso_jobseeker_email', true );
        $jobseeker_phone = get_post_meta( $jobseeker_id, '_oso_jobseeker_phone', true );
        
        // Get job details
        $job_start_date = get_post_meta( $job_id, '_oso_job_start_date', true );
        $job_compensation = get_post_meta( $job_id, '_oso_job_compensation', true );
        $application_date = get_post_meta( $application_id, '_oso_application_date', true );
        
        // Get application message
        $application_post = get_post( $application_id );
        $message_content = $application_post ? wp_trim_words( $application_post->post_content, 50, '...' ) : 'No message';

        $subject = sprintf( __( '✅ Application Approved: %s hired by %s', 'oso-employer-portal' ), $jobseeker_name, $camp_name );
        
        $message = sprintf(
            __( "Hello Admin,\n\nAn employer has approved a job application on OSO Jobs Portal.\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nAPPROVAL DETAILS\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nJob Position: %s\nStart Date: %s\nCompensation: %s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nEMPLOYER INFORMATION\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nCamp/Company: %s\nContact Name: %s\nEmail: %s\nPhone: %s\nUser Account: %s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nCANDIDATE INFORMATION\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nName: %s\nEmail: %s\nPhone: %s\nUser Account: %s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nAPPLICATION INFO\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nApplied On: %s\nApproved On: %s\nCandidate's Message: %s\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\nBoth parties have been notified via email.\n\nView full details in WordPress Admin:\n%s\n\nBest regards,\nOSO Jobs System", 'oso-employer-portal' ),
            $job_title,
            $job_start_date ? date_i18n( 'F j, Y', strtotime( $job_start_date ) ) : 'Not specified',
            $job_compensation ?: 'Not specified',
            $camp_name,
            $employer_user ? $employer_user->display_name : 'Unknown',
            $employer_email ?: 'Not provided',
            $employer_phone ?: 'Not provided',
            $employer_user ? $employer_user->user_email : 'N/A',
            $jobseeker_name,
            $jobseeker_email ?: 'Not provided',
            $jobseeker_phone ?: 'Not provided',
            $jobseeker_user ? $jobseeker_user->user_email : 'N/A',
            $application_date ? date_i18n( 'F j, Y', strtotime( $application_date ) ) : 'Unknown',
            current_time( 'F j, Y' ),
            $message_content,
            admin_url( 'edit.php?post_type=oso_job_application' )
        );

        wp_mail( $admin_email, $subject, $message );
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
