<?php
/**
 * Job Manager - Handles job posting CRUD operations
 *
 * @package OSO_Employer_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Job Manager class
 */
class OSO_Job_Manager {

    /**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return object
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // AJAX handlers for job management
        add_action( 'wp_ajax_oso_save_job_posting', array( $this, 'ajax_save_job_posting' ) );
        add_action( 'wp_ajax_oso_delete_job_posting', array( $this, 'ajax_delete_job_posting' ) );
        
        // Add custom columns to job listing in admin
        add_filter( 'manage_oso_job_posting_posts_columns', array( $this, 'add_job_columns' ) );
        add_action( 'manage_oso_job_posting_posts_custom_column', array( $this, 'render_job_columns' ), 10, 2 );
        
        // Add custom columns to application listing in admin
        add_filter( 'manage_oso_job_application_posts_columns', array( $this, 'add_application_columns' ) );
        add_action( 'manage_oso_job_application_posts_custom_column', array( $this, 'render_application_columns' ), 10, 2 );
        
        // Hide expired jobs from public queries
        add_action( 'pre_get_posts', array( $this, 'hide_expired_jobs' ) );
    }

    /**
     * Get jobs for a specific employer.
     *
     * @param int $employer_id Employer post ID.
     * @return array Array of job post objects.
     */
    public function get_employer_jobs( $employer_id ) {
        $args = array(
            'post_type'      => 'oso_job_posting',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
            'meta_query'     => array(
                array(
                    'key'   => '_oso_job_employer_id',
                    'value' => $employer_id,
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        return get_posts( $args );
    }

    /**
     * Get job posting limit for employer.
     *
     * @param int $employer_id Employer post ID.
     * @return int Job posting limit (0 = unlimited).
     */
    public function get_job_limit( $employer_id ) {
        $limit = get_post_meta( $employer_id, '_oso_employer_job_limit', true );
        return ! empty( $limit ) ? absint( $limit ) : 5; // Default 5
    }

    /**
     * Check if employer can post more jobs.
     *
     * @param int $employer_id Employer post ID.
     * @return bool True if can post more jobs.
     */
    public function can_post_job( $employer_id ) {
        $limit = $this->get_job_limit( $employer_id );
        
        if ( $limit === 0 ) {
            return true; // Unlimited
        }

        $current_jobs = $this->get_employer_jobs( $employer_id );
        $active_count = 0;

        foreach ( $current_jobs as $job ) {
            if ( $job->post_status === 'publish' && ! $this->is_job_expired( $job->ID ) ) {
                $active_count++;
            }
        }

        return $active_count < $limit;
    }

    /**
     * Check if job is expired based on end date.
     *
     * @param int $job_id Job post ID.
     * @return bool True if expired.
     */
    public function is_job_expired( $job_id ) {
        $end_date = get_post_meta( $job_id, '_oso_job_end_date', true );
        
        if ( empty( $end_date ) ) {
            return false;
        }

        $end_timestamp = strtotime( $end_date );
        return $end_timestamp && $end_timestamp < time();
    }

    /**
     * Get job meta data.
     *
     * @param int $job_id Job post ID.
     * @return array Job meta data.
     */
    public function get_job_meta( $job_id ) {
        $fields = array(
            '_oso_job_employer_id',
            '_oso_job_type',
            '_oso_job_required_skills',
            '_oso_job_start_date',
            '_oso_job_end_date',
            '_oso_job_compensation',
            '_oso_job_positions',
            '_oso_job_application_instructions',
        );

        $meta = array();
        foreach ( $fields as $field ) {
            $meta[ $field ] = get_post_meta( $job_id, $field, true );
        }

        return $meta;
    }

    /**
     * AJAX handler to save job posting.
     */
    public function ajax_save_job_posting() {
        check_ajax_referer( 'oso-job-nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'oso-employer-portal' ) ) );
        }

        $user = wp_get_current_user();
        if ( ! in_array( 'oso_employer', $user->roles ) && ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Access denied.', 'oso-employer-portal' ) ) );
        }

        // Get employer post
        $employer_post = $this->get_employer_by_user( $user->ID );
        if ( ! $employer_post ) {
            wp_send_json_error( array( 'message' => __( 'Employer profile not found.', 'oso-employer-portal' ) ) );
        }

        // Check if can post job
        $job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
        if ( ! $job_id && ! $this->can_post_job( $employer_post->ID ) ) {
            wp_send_json_error( array( 'message' => __( 'You have reached your job posting limit.', 'oso-employer-portal' ) ) );
        }

        // Sanitize and validate input
        $job_title = isset( $_POST['job_title'] ) ? sanitize_text_field( wp_unslash( $_POST['job_title'] ) ) : '';
        $job_description = isset( $_POST['job_description'] ) ? wp_kses_post( wp_unslash( $_POST['job_description'] ) ) : '';
        
        if ( empty( $job_title ) || empty( $job_description ) ) {
            wp_send_json_error( array( 'message' => __( 'Job title and description are required.', 'oso-employer-portal' ) ) );
        }

        // Create or update job post
        $post_data = array(
            'post_title'   => $job_title,
            'post_content' => $job_description,
            'post_type'    => 'oso_job_posting',
            'post_status'  => 'publish',
        );

        if ( $job_id ) {
            $post_data['ID'] = $job_id;
            $result = wp_update_post( $post_data );
        } else {
            $result = wp_insert_post( $post_data );
        }

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $job_id = $job_id ? $job_id : $result;

        // Save meta fields
        update_post_meta( $job_id, '_oso_job_employer_id', $employer_post->ID );
        
        if ( isset( $_POST['job_type'] ) ) {
            $job_type = is_array( $_POST['job_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['job_type'] ) ) : array();
            update_post_meta( $job_id, '_oso_job_type', implode( "\n", $job_type ) );
        }
        
        if ( isset( $_POST['required_skills'] ) ) {
            $skills = is_array( $_POST['required_skills'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['required_skills'] ) ) : array();
            update_post_meta( $job_id, '_oso_job_required_skills', implode( "\n", $skills ) );
        }
        
        if ( isset( $_POST['start_date'] ) ) {
            update_post_meta( $job_id, '_oso_job_start_date', sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) );
        }
        
        if ( isset( $_POST['end_date'] ) ) {
            update_post_meta( $job_id, '_oso_job_end_date', sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) );
        }
        
        if ( isset( $_POST['compensation'] ) ) {
            update_post_meta( $job_id, '_oso_job_compensation', sanitize_text_field( wp_unslash( $_POST['compensation'] ) ) );
        }
        
        if ( isset( $_POST['positions'] ) ) {
            $total_positions = absint( $_POST['positions'] );
            update_post_meta( $job_id, '_oso_job_positions', $total_positions );
            
            // Initialize available positions only if this is a new job or if available doesn't exist
            $available = get_post_meta( $job_id, '_oso_job_positions_available', true );
            if ( $available === '' ) {
                update_post_meta( $job_id, '_oso_job_positions_available', $total_positions );
            }
        }
        
        if ( isset( $_POST['application_instructions'] ) ) {
            update_post_meta( $job_id, '_oso_job_application_instructions', wp_kses_post( wp_unslash( $_POST['application_instructions'] ) ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Job posting saved successfully!', 'oso-employer-portal' ),
            'job_id'  => $job_id,
        ) );
    }

    /**
     * AJAX handler to delete job posting.
     */
    public function ajax_delete_job_posting() {
        check_ajax_referer( 'oso-job-nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'oso-employer-portal' ) ) );
        }

        $job_id = isset( $_POST['job_id'] ) ? absint( $_POST['job_id'] ) : 0;
        if ( ! $job_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid job ID.', 'oso-employer-portal' ) ) );
        }

        $user = wp_get_current_user();
        $employer_post = $this->get_employer_by_user( $user->ID );
        
        // Verify ownership
        $job_employer_id = get_post_meta( $job_id, '_oso_job_employer_id', true );
        if ( $job_employer_id != $employer_post->ID && ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to delete this job.', 'oso-employer-portal' ) ) );
        }

        $result = wp_delete_post( $job_id, true );
        
        if ( ! $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to delete job posting.', 'oso-employer-portal' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Job posting deleted.', 'oso-employer-portal' ) ) );
    }

    /**
     * Get employer post by user ID.
     *
     * @param int $user_id User ID.
     * @return WP_Post|false Employer post or false.
     */
    private function get_employer_by_user( $user_id ) {
        $args = array(
            'post_type'      => 'oso_employer',
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'meta_query'     => array(
                array(
                    'key'   => '_oso_employer_user_id',
                    'value' => $user_id,
                ),
            ),
        );

        $posts = get_posts( $args );
        return ! empty( $posts ) ? $posts[0] : false;
    }

    /**
     * Add custom columns to job posting list in admin.
     *
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function add_job_columns( $columns ) {
        $new_columns = array();
        
        if ( isset( $columns['cb'] ) ) {
            $new_columns['cb'] = $columns['cb'];
        }
        
        $new_columns['title']      = __( 'Job Title', 'oso-employer-portal' );
        $new_columns['employer']   = __( 'Employer', 'oso-employer-portal' );
        $new_columns['job_type']   = __( 'Job Type', 'oso-employer-portal' );
        $new_columns['dates']      = __( 'Start - End', 'oso-employer-portal' );
        $new_columns['positions']  = __( 'Positions', 'oso-employer-portal' );
        $new_columns['status']     = __( 'Status', 'oso-employer-portal' );
        
        if ( isset( $columns['date'] ) ) {
            $new_columns['date'] = $columns['date'];
        }
        
        return $new_columns;
    }

    /**
     * Render custom column content.
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function render_job_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'employer':
                $employer_id = get_post_meta( $post_id, '_oso_job_employer_id', true );
                if ( $employer_id ) {
                    $employer = get_post( $employer_id );
                    $camp_name = get_post_meta( $employer_id, '_oso_employer_company', true );
                    if ( $camp_name ) {
                        echo '<a href="' . esc_url( get_edit_post_link( $employer_id ) ) . '">' . esc_html( $camp_name ) . '</a>';
                    } else {
                        echo '—';
                    }
                } else {
                    echo '—';
                }
                break;

            case 'job_type':
                $job_type = get_post_meta( $post_id, '_oso_job_type', true );
                if ( $job_type ) {
                    $types = explode( "\n", $job_type );
                    echo esc_html( implode( ', ', array_slice( $types, 0, 3 ) ) );
                    if ( count( $types ) > 3 ) {
                        echo ' <span style="color: #999;">+' . ( count( $types ) - 3 ) . '</span>';
                    }
                } else {
                    echo '—';
                }
                break;

            case 'dates':
                $start_date = get_post_meta( $post_id, '_oso_job_start_date', true );
                $end_date = get_post_meta( $post_id, '_oso_job_end_date', true );
                
                if ( $start_date && $end_date ) {
                    echo esc_html( date_i18n( 'M j, Y', strtotime( $start_date ) ) ) . '<br>';
                    echo esc_html( date_i18n( 'M j, Y', strtotime( $end_date ) ) );
                } else {
                    echo '—';
                }
                break;

            case 'positions':
                $positions = get_post_meta( $post_id, '_oso_job_positions', true );
                echo $positions ? esc_html( $positions ) : '—';
                break;

            case 'status':
                if ( $this->is_job_expired( $post_id ) ) {
                    echo '<span style="color: #d9534f; font-weight: bold;">Expired</span>';
                } else {
                    echo '<span style="color: #5cb85c; font-weight: bold;">Active</span>';
                }
                break;
        }
    }

    /**
     * Hide expired jobs from public queries.
     *
     * @param WP_Query $query The WP_Query instance.
     */
    public function hide_expired_jobs( $query ) {
        // Only affect public job queries, not admin
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( $query->get( 'post_type' ) === 'oso_job_posting' ) {
            $meta_query = $query->get( 'meta_query' ) ? $query->get( 'meta_query' ) : array();
            
            // Add condition to hide expired jobs
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_oso_job_end_date',
                    'value'   => date( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
                array(
                    'key'     => '_oso_job_end_date',
                    'compare' => 'NOT EXISTS',
                ),
            );
            
            $query->set( 'meta_query', $meta_query );
        }
    }

    /**
     * Add custom columns to application listing.
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public function add_application_columns( $columns ) {
        $new_columns = array();
        
        foreach ( $columns as $key => $title ) {
            if ( $key === 'title' ) {
                $new_columns['applicant'] = __( 'Applicant', 'oso-employer-portal' );
                $new_columns['job'] = __( 'Job Position', 'oso-employer-portal' );
                $new_columns['employer'] = __( 'Employer', 'oso-employer-portal' );
            } elseif ( $key === 'date' ) {
                $new_columns['status'] = __( 'Status', 'oso-employer-portal' );
                $new_columns[$key] = $title;
            } else {
                $new_columns[$key] = $title;
            }
        }
        
        return $new_columns;
    }

    /**
     * Render custom columns for application listing.
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function render_application_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'applicant':
                $jobseeker_id = get_post_meta( $post_id, '_oso_application_jobseeker_id', true );
                if ( $jobseeker_id ) {
                    $jobseeker_name = get_the_title( $jobseeker_id );
                    $jobseeker_url = add_query_arg( array(
                        'post' => $jobseeker_id,
                        'action' => 'edit',
                    ), admin_url( 'post.php' ) );
                    echo '<a href="' . esc_url( $jobseeker_url ) . '">' . esc_html( $jobseeker_name ) . '</a>';
                } else {
                    echo '—';
                }
                break;

            case 'job':
                $job_id = get_post_meta( $post_id, '_oso_application_job_id', true );
                if ( $job_id ) {
                    $job_title = get_the_title( $job_id );
                    $job_url = add_query_arg( array(
                        'post' => $job_id,
                        'action' => 'edit',
                    ), admin_url( 'post.php' ) );
                    echo '<a href="' . esc_url( $job_url ) . '">' . esc_html( $job_title ) . '</a>';
                } else {
                    echo '—';
                }
                break;

            case 'employer':
                $employer_id = get_post_meta( $post_id, '_oso_application_employer_id', true );
                if ( $employer_id ) {
                    $employer_name = get_post_meta( $employer_id, '_oso_employer_company', true );
                    $employer_url = add_query_arg( array(
                        'post' => $employer_id,
                        'action' => 'edit',
                    ), admin_url( 'post.php' ) );
                    echo '<a href="' . esc_url( $employer_url ) . '">' . esc_html( $employer_name ) . '</a>';
                } else {
                    echo '—';
                }
                break;

            case 'status':
                $status = get_post_meta( $post_id, '_oso_application_status', true );
                $status_labels = array(
                    'pending'  => __( 'Pending', 'oso-employer-portal' ),
                    'approved' => __( 'Approved', 'oso-employer-portal' ),
                    'rejected' => __( 'Rejected', 'oso-employer-portal' ),
                );
                $status_colors = array(
                    'pending'  => '#ffc107',
                    'approved' => '#28a745',
                    'rejected' => '#dc3545',
                );
                $label = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : ucfirst( $status );
                $color = isset( $status_colors[ $status ] ) ? $status_colors[ $status ] : '#999';
                echo '<span style="display:inline-block;padding:4px 10px;background:' . esc_attr( $color ) . ';color:#fff;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;">' . esc_html( $label ) . '</span>';
                break;
        }
    }
}
