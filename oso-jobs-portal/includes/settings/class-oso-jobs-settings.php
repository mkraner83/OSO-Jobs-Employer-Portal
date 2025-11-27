<?php
/**
 * Settings handler.
 *
 * @package OSO_Jobs_Portal\Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings registration and helpers.
 */
class OSO_Jobs_Settings {

    /**
     * Register settings and fields.
     */
    public static function register_settings() {
        register_setting( 'oso_jobs_settings_group', 'oso_jobs_settings', array( self::class, 'sanitize' ) );

        add_settings_section(
            'oso_jobs_general_section',
            __( 'General Settings', 'oso-jobs-portal' ),
            '__return_false',
            'oso_jobs_settings'
        );

        add_settings_field(
            'jobs_page_title',
            __( 'Jobs Page Title', 'oso-jobs-portal' ),
            array( self::class, 'render_text_field' ),
            'oso_jobs_settings',
            'oso_jobs_general_section',
            array(
                'label_for' => 'jobs_page_title',
                'description' => __( 'Heading displayed above the job listings shortcode output.', 'oso-jobs-portal' ),
            )
        );

        add_settings_field(
            'jobs_page_content',
            __( 'Jobs Page Intro', 'oso-jobs-portal' ),
            array( self::class, 'render_textarea_field' ),
            'oso_jobs_settings',
            'oso_jobs_general_section',
            array(
                'label_for' => 'jobs_page_content',
                'description' => __( 'Intro text that appears before the list of open positions.', 'oso-jobs-portal' ),
            )
        );

        add_settings_field(
            'submission_page_id',
            __( 'Submission Page', 'oso-jobs-portal' ),
            array( self::class, 'render_page_dropdown' ),
            'oso_jobs_settings',
            'oso_jobs_general_section',
            array(
                'label_for' => 'submission_page_id',
                'description' => __( 'Select the page that hosts the [oso_job_submit] shortcode.', 'oso-jobs-portal' ),
            )
        );

        add_settings_field(
            'notification_email',
            __( 'Notification Email', 'oso-jobs-portal' ),
            array( self::class, 'render_text_field' ),
            'oso_jobs_settings',
            'oso_jobs_general_section',
            array(
                'label_for' => 'notification_email',
                'description' => __( 'Email address that receives new submission alerts.', 'oso-jobs-portal' ),
                'type'        => 'email',
            )
        );
    }

    /**
     * Sanitize input.
     *
     * @param array $input Raw input.
     * @return array
     */
    public static function sanitize( $input ) {
        $input = is_array( $input ) ? $input : array();
        $clean = array();
        foreach ( $input as $key => $value ) {
            if ( 'jobs_page_content' === $key ) {
                $clean[ $key ] = wp_kses_post( $value );
            } elseif ( 'submission_page_id' === $key ) {
                $clean[ $key ] = absint( $value );
            } elseif ( 'notification_email' === $key ) {
                $clean[ $key ] = sanitize_email( $value );
            } else {
                $clean[ $key ] = sanitize_text_field( $value );
            }
        }

        return $clean;
    }

    /**
     * Render text field.
     */
    public static function render_text_field( $args ) {
        $settings = OSO_Jobs_Utilities::get_settings();
        $value    = isset( $settings[ $args['label_for'] ] ) ? $settings[ $args['label_for'] ] : '';
        $type     = isset( $args['type'] ) ? $args['type'] : 'text';
        printf(
            '<input type="%1$s" id="%2$s" name="oso_jobs_settings[%2$s]" class="regular-text" value="%3$s" />',
            esc_attr( $type ),
            esc_attr( $args['label_for'] ),
            esc_attr( $value )
        );
        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    /**
     * Render textarea field.
     */
    public static function render_textarea_field( $args ) {
        $settings = OSO_Jobs_Utilities::get_settings();
        $value    = isset( $settings[ $args['label_for'] ] ) ? $settings[ $args['label_for'] ] : '';
        printf(
            '<textarea id="%1$s" name="oso_jobs_settings[%1$s]" class="large-text" rows="4">%2$s</textarea>',
            esc_attr( $args['label_for'] ),
            esc_textarea( $value )
        );
        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    /**
     * Render page dropdown.
     */
    public static function render_page_dropdown( $args ) {
        $settings = OSO_Jobs_Utilities::get_settings();
        $value    = isset( $settings[ $args['label_for'] ] ) ? (int) $settings[ $args['label_for'] ] : 0;

        wp_dropdown_pages(
            array(
                'name'             => 'oso_jobs_settings[' . esc_attr( $args['label_for'] ) . ']',
                'id'               => esc_attr( $args['label_for'] ),
                'show_option_none' => __( '— Select —', 'oso-jobs-portal' ),
                'option_none_value' => 0,
                'selected'         => $value,
            )
        );

        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    /**
     * Helper to read single option.
     */
    public static function get( $key, $default = '' ) {
        $settings = OSO_Jobs_Utilities::get_settings();
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }
}
