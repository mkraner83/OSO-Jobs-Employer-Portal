<?php
/**
 * Utilities helper for OSO Jobs Portal.
 *
 * @package OSO_Jobs_Portal\Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Collection of helper methods to keep other classes lean.
 */
class OSO_Jobs_Utilities {

    /**
     * Sanitize array recursively.
     *
     * @param array $data Data to sanitize.
     * @return array
     */
    public static function sanitize_array( $data ) {
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $data[ $key ] = self::sanitize_array( $value );
            } else {
                $data[ $key ] = sanitize_text_field( wp_unslash( $value ) );
            }
        }

        return $data;
    }

    /**
     * Get plugin settings with defaults.
     *
     * @return array
     */
    public static function get_settings() {
        $defaults = array(
            'jobs_page_title'   => 'Open Roles',
            'jobs_page_content' => 'Browse the latest openings and apply today.',
            'submission_page_id' => 0,
            'notification_email' => get_option( 'admin_email' ),
        );

        $settings = get_option( 'oso_jobs_settings', array() );

        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Return formatted department name list.
     *
     * @return array
     */
    public static function get_departments() {
        $terms = get_terms(
            array(
                'taxonomy'   => OSO_Jobs_Portal::TAXONOMY_DEPARTMENT,
                'hide_empty' => false,
            )
        );

        if ( is_wp_error( $terms ) ) {
            return array();
        }

        $choices = array();
        foreach ( $terms as $term ) {
            $choices[ $term->term_id ] = $term->name;
        }

        return $choices;
    }

    /**
     * Generate a unique username based on name/email values.
     *
     * @param string $name  Full name.
     * @param string $email Email address.
     * @return string
     */
    public static function generate_username( $name, $email ) {
        $base = $name ? sanitize_title( $name ) : sanitize_title( current( explode( '@', $email ) ) );
        $base = preg_replace( '/[^a-z0-9._-]/', '', strtolower( $base ) );

        if ( empty( $base ) ) {
            $base = 'jobseeker';
        }

        $username = $base;
        $i        = 1;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            $i ++;
        }

        return $username;
    }

    /**
     * Find the jobseeker post attached to a user.
     *
     * @param int $user_id User ID.
     * @return WP_Post|null
     */
    public static function get_jobseeker_by_user( $user_id ) {
        $posts = get_posts(
            array(
                'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
                'posts_per_page' => 1,
                'meta_key'       => '_oso_jobseeker_user_id',
                'meta_value'     => (int) $user_id,
            )
        );

        return $posts ? $posts[0] : null;
    }

    /**
     * Retrieve predefined meta fields for jobseekers.
     *
     * @param int $post_id Jobseeker post ID.
     * @return array
     */
    public static function get_jobseeker_meta( $post_id ) {
        $keys = array(
            '_oso_jobseeker_full_name',
            '_oso_jobseeker_email',
            '_oso_jobseeker_location',
            '_oso_jobseeker_over_18',
            '_oso_jobseeker_resume',
            '_oso_jobseeker_photo',
            '_oso_jobseeker_availability_start',
            '_oso_jobseeker_availability_end',
            '_oso_jobseeker_job_interests',
            '_oso_jobseeker_sports_skills',
            '_oso_jobseeker_arts_skills',
            '_oso_jobseeker_adventure_skills',
            '_oso_jobseeker_waterfront_skills',
            '_oso_jobseeker_support_skills',
            '_oso_jobseeker_certifications',
            '_oso_jobseeker_wpforms_entry',
        );

        $meta = array();
        foreach ( $keys as $key ) {
            $meta[ $key ] = get_post_meta( $post_id, $key, true );
        }

        return $meta;
    }

    /**
     * Text input field config for jobseeker records.
     *
     * @return array
     */
    public static function get_jobseeker_text_fields() {
        return array(
            'full_name'         => array(
                'label' => __( 'Full Name', 'oso-jobs-portal' ),
                'meta'  => '_oso_jobseeker_full_name',
                'type'  => 'text',
            ),
            'email'             => array(
                'label' => __( 'Email Address', 'oso-jobs-portal' ),
                'meta'  => '_oso_jobseeker_email',
                'type'  => 'email',
            ),
            'location'          => array(
                'label'   => __( 'Location', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_location',
                'type'    => 'select',
                'options' => self::get_states(),
            ),
            'availability_start'=> array(
                'label' => __( 'Availability - Earliest Start', 'oso-jobs-portal' ),
                'meta'  => '_oso_jobseeker_availability_start',
                'type'  => 'date',
            ),
            'availability_end'  => array(
                'label' => __( 'Availability - Latest End', 'oso-jobs-portal' ),
                'meta'  => '_oso_jobseeker_availability_end',
                'type'  => 'date',
            ),
            'resume_url'        => array(
                'label' => __( 'Resume URL', 'oso-jobs-portal' ),
                'meta'  => '_oso_jobseeker_resume',
                'type'  => 'url',
            ),
            'photo_url'         => array(
                'label' => __( 'Photo URL', 'oso-jobs-portal' ),
                'meta'  => '_oso_jobseeker_photo',
                'type'  => 'url',
            ),
        );
    }

    /**
     * Textareas for jobseeker metadata.
     *
     * @return array
     */
    public static function get_jobseeker_textareas() {
        return array();
    }

    /**
     * Checkbox fields mapping for jobseekers.
     *
     * @return array
     */
    public static function get_jobseeker_checkbox_groups() {
        return array(
            'over_18'          => array(
                'label'   => __( 'Are You Over 18?', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_over_18',
                'options' => array( 'Yes, I am over 18' ),
            ),
            'job_interests'    => array(
                'label'   => __( 'Job Interests', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_job_interests',
                'options' => array( 'General Counselor', 'Sports', 'Arts', 'Adventure', 'Waterfront', 'Medical', 'Kitchen / Dining', 'Maintenance / Grounds', 'Office / Administration', 'Any!' ),
            ),
            'sports_skills'    => array(
                'label'   => __( 'Sports Skills', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_sports_skills',
                'options' => array( 'Archery', 'Baseball', 'Basketball', 'Soccer', 'Tennis', 'Volleyball' ),
            ),
            'arts_skills'      => array(
                'label'   => __( 'Arts Skills', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_arts_skills',
                'options' => array( '3D Printing', 'Arts & Crafts', 'Music / Instruments', 'Photography', 'Theater' ),
            ),
            'adventure_skills' => array(
                'label'   => __( 'Adventure Skills', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_adventure_skills',
                'options' => array( 'Backpacking', 'Camping', 'Hiking', 'High Ropes', 'Kayaking' ),
            ),
            'waterfront_skills'=> array(
                'label'   => __( 'Waterfront Skills', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_waterfront_skills',
                'options' => array( 'Canoeing', 'Kayaking', 'Lifeguard', 'Swim Instructor', 'Sailing' ),
            ),
            'support_skills'   => array(
                'label'   => __( 'Support Services Skills', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_support_skills',
                'options' => array( 'Administration', 'Dining / Kitchen', 'Maintenance', 'Groundskeeping', 'Media / Communications' ),
            ),
            'certifications'   => array(
                'label'   => __( 'Certifications', 'oso-jobs-portal' ),
                'meta'    => '_oso_jobseeker_certifications',
                'options' => array( 'AED Certification', 'First Aid & CPR', 'Lifeguard Certification', 'WSI', 'Wilderness First Aid / WFR', 'Other' ),
            ),
        );
    }

    /**
     * Convert stored meta string into array for checkbox rendering.
     *
     * @param string $value Raw string.
     * @return array
     */
    public static function meta_string_to_array( $value ) {
        if ( empty( $value ) ) {
            return array();
        }

        if ( is_array( $value ) ) {
            return array_map( 'trim', $value );
        }

        $parts = preg_split( '/[\r\n,]+/', $value );
        $parts = array_filter( array_map( 'trim', $parts ) );

        return $parts;
    }

    /**
     * Convert checkbox selections back into stored string.
     *
     * @param array $values Values.
     * @return string
     */
    public static function array_to_meta_string( $values ) {
        if ( empty( $values ) ) {
            return '';
        }

        if ( ! is_array( $values ) ) {
            return sanitize_text_field( (string) $values );
        }

        $values = array_map( 'sanitize_text_field', $values );
        $values = array_filter( $values );

        return implode( "\n", $values );
    }

    /**
     * State choices for dropdown.
     *
     * @return array
     */
    public static function get_states() {
        return array(
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming',
        );
    }

    /**
     * Convert stored date string into Y-m-d for input fields.
     *
     * @param string $value Raw value.
     * @return string
     */
    public static function format_date_for_input( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        $timestamp = strtotime( $value );
        if ( ! $timestamp ) {
            return '';
        }

        return gmdate( 'Y-m-d', $timestamp );
    }

    /**
     * Normalize date value for storage (Y-m-d).
     *
     * @param string $value Input value.
     * @return string
     */
    public static function normalize_date_value( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        $timestamp = strtotime( $value );
        if ( ! $timestamp ) {
            return sanitize_text_field( $value );
        }

        return gmdate( 'Y-m-d', $timestamp );
    }
}
