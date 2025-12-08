<?php
/**
 * WPForms integration.
 *
 * @package OSO_Jobs_Portal\WPForms
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle WPForms submissions and hydrate admin screen data.
 */
class OSO_Jobs_WPForms_Handler {

    /**
     * Singleton instance.
     *
     * @var OSO_Jobs_WPForms_Handler
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
     * Hook WPForms events.
     */
    protected function __construct() {
        add_action( 'wpforms_process', array( $this, 'validate_unique_email' ), 10, 3 );
        add_action( 'wpforms_process_complete', array( $this, 'handle_submission' ), 10, 4 );
        add_filter( 'wpforms_frontend_confirmation_message', array( $this, 'filter_confirmation_shortcodes' ), 10, 4 );
        
        // Disable default WordPress new user notification emails when we're handling registration
        add_filter( 'wp_new_user_notification_email', array( $this, 'disable_default_user_notification' ), 10, 3 );
        add_filter( 'wp_new_user_notification_email_admin', array( $this, 'disable_default_admin_notification' ), 10, 3 );
    }
    
    /**
     * Validate that email is unique across all users.
     *
     * @param array $fields    Form fields.
     * @param array $entry     Entry data.
     * @param array $form_data Form data.
     */
    public function validate_unique_email( $fields, $entry, $form_data ) {
        // Detect if this is a registration form
        $type = $this->detect_submission_type( $form_data );
        
        // Only validate registration forms
        if ( 'employer' !== $type && 'jobseeker' !== $type ) {
            return;
        }
        
        // Find the email field
        $email = '';
        $email_field_id = '';
        
        foreach ( $fields as $field_id => $field ) {
            if ( ! empty( $field['type'] ) && $field['type'] === 'email' ) {
                $email = sanitize_email( $field['value'] );
                $email_field_id = $field_id;
                break;
            }
        }
        
        if ( empty( $email ) || ! is_email( $email ) ) {
            return;
        }
        
        // Check if email already exists in WordPress users
        if ( email_exists( $email ) ) {
            $user = get_user_by( 'email', $email );
            
            if ( $user ) {
                // Check user's role and provide specific error message
                if ( in_array( 'oso_employer', (array) $user->roles, true ) ) {
                    if ( 'employer' === $type ) {
                        wpforms()->process->errors[ $form_data['id'] ][ $email_field_id ] = 
                            __( 'This email is already registered as an Employer. Please log in instead.', 'oso-jobs-portal' );
                    } else {
                        wpforms()->process->errors[ $form_data['id'] ][ $email_field_id ] = 
                            __( 'This email is already registered as an Employer. Please use a different email or contact support.', 'oso-jobs-portal' );
                    }
                } elseif ( in_array( 'oso_jobseeker', (array) $user->roles, true ) ) {
                    if ( 'jobseeker' === $type ) {
                        wpforms()->process->errors[ $form_data['id'] ][ $email_field_id ] = 
                            __( 'This email is already registered as a Job Seeker. Please log in instead.', 'oso-jobs-portal' );
                    } else {
                        wpforms()->process->errors[ $form_data['id'] ][ $email_field_id ] = 
                            __( 'This email is already registered as a Job Seeker. Please use a different email or contact support.', 'oso-jobs-portal' );
                    }
                } else {
                    wpforms()->process->errors[ $form_data['id'] ][ $email_field_id ] = 
                        __( 'This email address is already registered. Please use a different email or log in.', 'oso-jobs-portal' );
                }
            }
        }
    }
    
    /**
     * Disable default WordPress new user notification email.
     * We send our own custom welcome emails instead.
     *
     * @param array   $wp_new_user_notification_email Email data.
     * @param WP_User $user User object.
     * @param string  $blogname Blog name.
     * @return array|false False to prevent email, array otherwise.
     */
    public function disable_default_user_notification( $wp_new_user_notification_email, $user, $blogname ) {
        // Check if user has our custom roles - if so, we're handling the email
        if ( in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true ) || 
             in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
            return false; // Prevent default email
        }
        return $wp_new_user_notification_email;
    }
    
    /**
     * Allow admin notification but keep the default format.
     * Admin will still receive notifications for employer/jobseeker registrations.
     *
     * @param array   $wp_new_user_notification_email_admin Email data.
     * @param WP_User $user User object.
     * @param string  $blogname Blog name.
     * @return array Email data unchanged.
     */
    public function disable_default_admin_notification( $wp_new_user_notification_email_admin, $user, $blogname ) {
        // Keep admin notifications enabled for all users including employers/jobseekers
        return $wp_new_user_notification_email_admin;
    }

    /**
     * Process WPForms submission.
     *
     * @param array $fields Submitted fields.
     * @param array $entry Entry meta.
     * @param array $form_data Form setup.
     * @param int   $entry_id Entry ID.
     */
    public function handle_submission( $fields, $entry, $form_data, $entry_id ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        $type = $this->detect_submission_type( $form_data );

        if ( 'employer' === $type ) {
            $this->handle_employer_submission( $fields, $form_data );
            return;
        }

        $record = array(
            'name'      => self::find_field_value( $fields, array( 'name', 'full_name', 'applicant' ) ),
            'email'     => self::find_field_value( $fields, array( 'email', 'email_address' ) ),
            'job_title' => self::find_field_value( $fields, array( 'job', 'role', 'position' ), __( 'Unknown role', 'oso-jobs-portal' ) ),
            'submitted' => current_time( 'mysql' ),
        );

        if ( empty( $record['name'] ) ) {
            $record['name'] = __( 'Unknown applicant', 'oso-jobs-portal' );
        }

        $this->store_entry( $record );
        $this->send_notification( $record, $form_data );
        $this->create_jobseeker_post( $fields, $entry_id );
    }

    /**
     * Save entry in option for dashboard view.
     */
    protected function store_entry( $record ) {
        $entries = get_option( 'oso_jobs_entries', array() );
        array_unshift( $entries, $record );
        $entries = array_slice( $entries, 0, 20 );
        update_option( 'oso_jobs_entries', $entries );
    }

    /**
     * Send admin notification email.
     */
    protected function send_notification( $record, $form_data ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        $email = OSO_Jobs_Settings::get( 'notification_email', get_option( 'admin_email' ) );
        if ( ! $email ) {
            return;
        }

        $subject = sprintf( __( 'New job application from %s', 'oso-jobs-portal' ), $record['name'] );
        $message = sprintf(
            "Name: %s\nEmail: %s\nRole: %s\nSubmitted: %s",
            $record['name'],
            $record['email'],
            $record['job_title'],
            $record['submitted']
        );

        wp_mail( $email, $subject, $message );
    }

    /**
     * Helper to find field value by key fragments.
     */
    protected static function find_field_value( $fields, $possible_keys, $default = '' ) {
        foreach ( $fields as $field ) {
            $key = strtolower( $field['name'] );
            foreach ( $possible_keys as $match ) {
                if ( false !== strpos( $key, strtolower( $match ) ) ) {
                    $value = isset( $field['value_raw'] ) && $field['value_raw'] ? $field['value_raw'] : $field['value'];

                    if ( is_array( $value ) ) {
                        $value = array_map( 'sanitize_text_field', (array) $value );
                        return implode( ', ', $value );
                    }

                    if ( is_string( $value ) && false !== strpos( $value, "\n" ) ) {
                        $parts = preg_split( '/[\r\n]+/', $value );
                        $parts = array_filter( array_map( 'sanitize_text_field', $parts ) );
                        return implode( "\n", $parts );
                    }

                    return sanitize_text_field( $value );
                }
            }
        }

        return $default;
    }

    /**
     * Return most recent entries.
     */
    public static function get_recent_entries() {
        $entries = get_option( 'oso_jobs_entries', array() );
        return is_array( $entries ) ? $entries : array();
    }

    /**
     * Extract file upload URL from fields.
     *
     * @param array $fields        Field data.
     * @param array $possible_keys Possible label fragments.
     * @return string
     */
    protected static function find_file_url( $fields, $possible_keys ) {
        foreach ( $fields as $field ) {
            $key = strtolower( $field['name'] );
            foreach ( $possible_keys as $match ) {
                if ( false !== strpos( $key, strtolower( $match ) ) ) {
                    if ( ! empty( $field['value'] ) && filter_var( $field['value'], FILTER_VALIDATE_URL ) ) {
                        return esc_url_raw( $field['value'] );
                    }
                    if ( ! empty( $field['value_raw'] ) && is_array( $field['value_raw'] ) ) {
                        $raw = reset( $field['value_raw'] );
                        if ( isset( $raw['value'] ) && filter_var( $raw['value'], FILTER_VALIDATE_URL ) ) {
                            return esc_url_raw( $raw['value'] );
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * Normalize checkbox/text area multi-line values.
     *
     * @param string|array $value Raw value.
     * @return string
     */
    protected static function format_list_value( $value ) {
        if ( empty( $value ) ) {
            return '';
        }

        if ( is_array( $value ) ) {
            return implode( "\n", array_map( 'sanitize_text_field', $value ) );
        }

        $parts = preg_split( '/[\r\n]+/', $value );
        $parts = array_filter( array_map( 'trim', $parts ) );
        return implode( "\n", $parts );
    }

    /**
     * Create or update Jobseeker CPT entry from WPForms submission.
     *
     * @param array $fields WPForms fields.
     * @param int   $entry_id Entry ID.
     */
    protected function create_jobseeker_post( $fields, $entry_id ) {
        $full_name  = self::find_field_value( $fields, array( 'full name', 'name' ) );
        $location   = self::find_field_value( $fields, array( 'state', 'location' ) );
        $over_18    = self::find_field_value( $fields, array( 'over 18' ) );
        $email      = self::find_field_value( $fields, array( 'email' ) );
        $interests  = self::format_list_value( self::find_field_value( $fields, array( 'job interests', 'interest' ) ) );
        $sports     = self::format_list_value( self::find_field_value( $fields, array( 'sports skills' ) ) );
        $arts       = self::format_list_value( self::find_field_value( $fields, array( 'arts skills' ) ) );
        $adventure  = self::format_list_value( self::find_field_value( $fields, array( 'adventure skills' ) ) );
        $waterfront = self::format_list_value( self::find_field_value( $fields, array( 'waterfront skills' ) ) );
        $support    = self::format_list_value( self::find_field_value( $fields, array( 'support services' ) ) );
        $certs      = self::format_list_value( self::find_field_value( $fields, array( 'certifications' ) ) );
        $resume     = self::find_file_url( $fields, array( 'resume' ) );
        $photo      = self::find_file_url( $fields, array( 'photo' ) );
        $why        = self::find_field_value( $fields, array( 'why are you', 'cover letter' ) );
        $start      = self::find_field_value( $fields, array( 'earliest start', 'availability - earliest start' ) );
        $end        = self::find_field_value( $fields, array( 'latest end', 'availability - latest end' ) );

        $title = $full_name ? $full_name : sprintf( __( 'Jobseeker %d', 'oso-jobs-portal' ), $entry_id );
        if ( $location ) {
            $title = sprintf( '%s — %s', $title, $location );
        }

        $existing = get_posts(
            array(
                'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => '_oso_jobseeker_wpforms_entry',
                'meta_value'     => (int) $entry_id,
            )
        );

        $postarr = array(
            'post_title'   => $title,
            'post_content' => $why,
            'post_status'  => 'publish',
        );

        if ( $existing ) {
            $postarr['ID'] = $existing[0];
            $post_id       = wp_update_post( $postarr, true );
        } else {
            $postarr['post_type'] = OSO_Jobs_Portal::POST_TYPE_JOBSEEKER;
            $post_id              = wp_insert_post( $postarr, true );
        }

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return;
        }

        $meta = array(
            '_oso_jobseeker_full_name'     => $full_name,
            '_oso_jobseeker_email'         => $email,
            '_oso_jobseeker_location'      => $location,
            '_oso_jobseeker_over_18'       => $over_18,
            '_oso_jobseeker_resume'        => $resume,
            '_oso_jobseeker_photo'         => $photo,
            '_oso_jobseeker_availability_start' => $start,
            '_oso_jobseeker_availability_end'   => $end,
            '_oso_jobseeker_job_interests'      => $interests,
            '_oso_jobseeker_sports_skills'      => $sports,
            '_oso_jobseeker_arts_skills'        => $arts,
            '_oso_jobseeker_adventure_skills'   => $adventure,
            '_oso_jobseeker_waterfront_skills'  => $waterfront,
            '_oso_jobseeker_support_skills'     => $support,
            '_oso_jobseeker_certifications'     => $certs,
            '_oso_jobseeker_wpforms_entry'      => (int) $entry_id,
        );

        foreach ( $meta as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }

        $this->sync_user_account(
            array(
                'full_name' => $full_name,
                'email'     => $email,
            ),
            $post_id,
            OSO_Jobs_Portal::ROLE_CANDIDATE
        );
    }

    /**
     * Ensure a WordPress user exists for the jobseeker and link it.
     *
     * @param array $data    Jobseeker data.
     * @param int   $post_id Post ID.
     */
    protected function sync_user_account( $data, $post_id, $role = OSO_Jobs_Portal::ROLE_CANDIDATE ) {
        if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
            return;
        }

        $user = get_user_by( 'email', $data['email'] );
        if ( ! $user ) {
            $username = OSO_Jobs_Utilities::generate_username( $data['full_name'], $data['email'] );
            $password = wp_generate_password( 12, true );
            $user_id  = wp_insert_user(
                array(
                    'user_login'   => $username,
                    'user_email'   => sanitize_email( $data['email'] ),
                    'display_name' => $data['full_name'],
                    'user_pass'    => $password,
                    'role'         => $role,
                )
            );

            if ( is_wp_error( $user_id ) ) {
                return;
            }

            $user = get_user_by( 'id', $user_id );
            
            // Don't send default WordPress notification - we'll send our own
            // wp_new_user_notification( $user_id, null, 'both' );
            
            $this->send_welcome_email( $user, $password );
        }

        if ( ! $user ) {
            return;
        }

        if ( $post_id ) {
            update_post_meta( $post_id, '_oso_jobseeker_user_id', $user->ID );
            update_user_meta( $user->ID, '_oso_jobseeker_post_id', $post_id );
        }

        $this->ensure_user_role( $user->ID, $role );

        if ( ! empty( $data['full_name'] ) ) {
            wp_update_user(
                array(
                    'ID'           => $user->ID,
                    'display_name' => $data['full_name'],
                )
            );
        }
    }

    /**
     * Ensure the user has the requested role.
     *
     * @param int    $user_id User ID.
     * @param string $role    Role.
     */
    protected function ensure_user_role( $user_id, $role ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return;
        }

        if ( ! in_array( $role, (array) $user->roles, true ) ) {
            $user->add_role( $role );
        }
    }

    /**
     * Send jobseeker welcome email with credentials.
     *
     * @param WP_User $user User object.
     * @param string  $password Generated password.
     */
    protected function send_welcome_email( $user, $password ) {
        if ( ! $user || ! $user->user_email ) {
            return;
        }

        // Check if user is employer or jobseeker
        $is_employer = in_array( OSO_Jobs_Portal::ROLE_EMPLOYER, (array) $user->roles, true );
        $first_name = ! empty( $user->first_name ) ? $user->first_name : $user->display_name;
        
        if ( $is_employer ) {
            $subject = __( 'Welcome to OSO Jobs – Your Employer Account is Ready!', 'oso-jobs-portal' );
            $profile_link = wp_login_url( home_url( '/job-portal/employer-profile/' ) );
            
            // Generate password reset key
            $reset_key = get_password_reset_key( $user );
            if ( ! is_wp_error( $reset_key ) ) {
                $password_reset_link = network_site_url( "wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode( $user->user_login ), 'login' );
            } else {
                $password_reset_link = wp_lostpassword_url();
            }
            
            // Create styled HTML email
            $message = $this->get_employer_welcome_email_html( $first_name, $user->user_login, $profile_link, $password_reset_link );
            
            // Set content type to HTML
            add_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
            wp_mail( $user->user_email, $subject, $message );
            remove_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
        } else {
            // Jobseeker - styled HTML email
            $subject = __( 'Welcome to OSO Jobs – Your Summer Starts Here!', 'oso-jobs-portal' );
            $profile_link = wp_login_url( home_url( '/job-portal/jobseeker-profile/' ) );
            
            // Generate password reset key for jobseeker too
            $reset_key = get_password_reset_key( $user );
            if ( ! is_wp_error( $reset_key ) ) {
                $password_reset_link = network_site_url( "wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode( $user->user_login ), 'login' );
            } else {
                $password_reset_link = wp_lostpassword_url();
            }
            
            // Create styled HTML email
            $message = $this->get_jobseeker_welcome_email_html( $first_name, $user->user_login, $profile_link, $password_reset_link );
            
            // Set content type to HTML
            add_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
            wp_mail( $user->user_email, $subject, $message );
            remove_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
        }
    }
    
    /**
     * Get styled HTML email for employer welcome.
     *
     * @param string $first_name User's first name.
     * @param string $username Username.
     * @param string $profile_link Profile URL.
     * @param string $password_reset_link Password reset URL.
     * @return string HTML email content.
     */
    protected function get_employer_welcome_email_html( $first_name, $username, $profile_link, $password_reset_link ) {
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to OSO Jobs</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f7fa;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f5f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-collapse: collapse; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 50px 40px 40px; text-align: center; background: #548A8F; border-radius: 12px 12px 0 0;">
                            <h1 style="margin: 0 0 10px 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">Welcome – Let's Shift the Momentum of Domestic Hiring</h1>
                        </td>
                    </tr>
                    
                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px 40px 20px;">
                            <p style="margin: 0 0 25px 0; color: #2d3748; font-size: 18px; line-height: 1.7; font-weight: 500;">Hi <?php echo esc_html( $first_name ); ?>,</p>
                            
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                We're Josh and Caleb, the co-founders of OSO. We both started working at camp back in 2012 when we were 19, and we've been in the hiring and seasonal employment world ever since.
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                We know how tough domestic hiring has become for camps that deserve way more attention than they get. We built OSO because we believe the momentum can shift.
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                There are millions of young adults out there who have no idea camp jobs exist and we want to change that and get more people saying, "Why didn't I know this was a thing?"
                            </p>
                            
                            <p style="margin: 0 0 35px 0; color: #4a5568; font-size: 16px; line-height: 1.7; font-style: italic;">
                                Think of OSO as a platform designed to bring you more first dates. The good kind.
                            </p>
                            
                            <h2 style="margin: 0 0 25px 0; color: #2d3748; font-size: 26px; font-weight: 700; text-align: center;">What's Next?</h2>
                            
                            <p style="margin: 0 0 30px 0; color: #2d3748; font-size: 18px; line-height: 1.7; text-align: center; font-weight: 600;">
                                Create A Profile. Post Jobs. Connect With Staff.
                            </p>
                            
                            <!-- Feature Sections -->
                            <div style="margin-bottom: 25px;">
                                <h3 style="margin: 0 0 12px 0; color: #548A8F; font-size: 18px; font-weight: 700;">Create Your Profile</h3>
                                <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                    Camps with strong, authentic profiles consistently attract more interest. Staff photos, videos, and glimpses into your culture perform extremely well. People want to see where they might belong.
                                </p>
                            </div>
                            
                            <div style="margin-bottom: 25px;">
                                <h3 style="margin: 0 0 12px 0; color: #548A8F; font-size: 18px; font-weight: 700;">Post Your Jobs</h3>
                                <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                    The more roles you list, the more activity the platform generates. More jobs lead to more traffic, more visibility, and more momentum. We saw this clearly during last summer's pilot.
                                </p>
                            </div>
                            
                            <div style="margin-bottom: 35px;">
                                <h3 style="margin: 0 0 12px 0; color: #548A8F; font-size: 18px; font-weight: 700;">Connect With Candidates</h3>
                                <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                    Young adults value quick, genuine interaction. They want to browse profiles, start conversations, and build rapport. If your schedule makes this difficult, consider designating someone on your team to help keep communication flowing.
                                </p>
                            </div>
                            
                            <!-- Account Details Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border: 2px solid #e2e8f0; border-radius: 10px; margin-bottom: 35px;">
                                <tr>
                                    <td style="padding: 30px;">
                                        <h3 style="margin: 0 0 20px 0; color: #548A8F; font-size: 18px; font-weight: 700;">Your Account Details</h3>
                                        <p style="margin: 0 0 15px 0; color: #2d3748; font-size: 15px; line-height: 1.6;">
                                            <strong style="color: #4a5568;">Username:</strong> <?php echo esc_html( $username ); ?>
                                        </p>
                                        <p style="margin: 0; color: #2d3748; font-size: 15px; line-height: 1.6;">
                                            <strong style="color: #4a5568;">To set your password, visit the following address:</strong><br>
                                            <a href="<?php echo esc_url( $password_reset_link ); ?>" style="color: #548A8F; text-decoration: none; word-break: break-all; font-weight: 500;"><?php echo esc_html( $password_reset_link ); ?></a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 35px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="<?php echo esc_url( $profile_link ); ?>" style="display: inline-block; padding: 16px 48px; background: #548A8F; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(84, 138, 143, 0.25);">Get Started</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 0 0 25px 0; color: #548A8F; font-size: 18px; line-height: 1.7; font-weight: 600; font-style: italic; text-align: center;">
                                We're here to champion camp.
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Thanks for joining us. We're excited to support your 2026 hiring and build something meaningful together.
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                If you ever need anything, we're always here.
                            </p>
                            
                            <p style="margin: 0 0 5px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Think Summer!
                            </p>
                            
                            <p style="margin: 0; color: #2d3748; font-size: 16px; line-height: 1.7; font-weight: 600;">
                                Josh & Caleb
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f7fafc; padding: 30px 40px; text-align: center; border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
                            <p style="margin: 0 0 8px 0; color: #a0aec0; font-size: 13px;">
                                © <?php echo esc_html( date( 'Y' ) ); ?> OSO Jobs. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #cbd5e0; font-size: 12px;">
                                This is an automated message, please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get styled HTML email for jobseeker welcome.
     *
     * @param string $first_name User's first name.
     * @param string $username Username.
     * @param string $profile_link Profile URL.
     * @param string $password_reset_link Password reset URL.
     * @return string HTML email content.
     */
    protected function get_jobseeker_welcome_email_html( $first_name, $username, $profile_link, $password_reset_link ) {
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to OSO Jobs</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f7fa;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f5f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-collapse: collapse; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 50px 40px 40px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px 12px 0 0;">
                            <h1 style="margin: 0 0 10px 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;">Welcome to OSO — Your Summer Starts Here</h1>
                        </td>
                    </tr>
                    
                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px 40px 20px;">
                            <p style="margin: 0 0 25px 0; color: #2d3748; font-size: 18px; line-height: 1.7; font-weight: 500;">Hi <?php echo esc_html( $first_name ); ?> we are so excited you're here!</p>
                            
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                We're Josh and Caleb, the co-founders of OSO. We both started working at summer camps back in 2012 when we were 19, and it pretty much changed the whole trajectory of our lives. We found confidence, community, mentors, best friends, and career paths we didn't even know existed.
                            </p>
                            
                            <p style="margin: 0 0 35px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                We built OSO so you can have that same shot at a summer that actually means something. Now that you've joined, you can instantly connect with camps across the country who are hiring for roles in sports, arts, waterfront, adventure, media, counseling, and more. Our hope is that OSO helps you find a job that feels exciting, supportive, and full of growth, not just another thing to fill your summer.
                            </p>
                            
                            <h2 style="margin: 0 0 25px 0; color: #2d3748; font-size: 26px; font-weight: 700; text-align: center;">What's Next?</h2>
                            
                            <p style="margin: 0 0 10px 0; color: #2d3748; font-size: 18px; line-height: 1.7; text-align: center; font-weight: 600;">
                                Create A Profile. Search Jobs. Get Hired!
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.7; text-align: center; font-style: italic;">
                                It's really that simple.
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                And you're not doing this alone. If you ever have questions about getting hired, choosing a camp, interviews, or just want honest advice, you can talk to us directly. Join our WhatsApp group here:
                            </p>
                            
                            <!-- WhatsApp Link (placeholder) -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 35px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="#" style="display: inline-block; padding: 16px 48px; background: #25D366; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.25);">📱 WhatsApp</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Account Details Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border: 2px solid #e2e8f0; border-radius: 10px; margin-bottom: 35px;">
                                <tr>
                                    <td style="padding: 30px;">
                                        <h3 style="margin: 0 0 20px 0; color: #667eea; font-size: 18px; font-weight: 700;">Your Account Details</h3>
                                        <p style="margin: 0 0 15px 0; color: #2d3748; font-size: 15px; line-height: 1.6;">
                                            <strong style="color: #4a5568;">Username:</strong> <?php echo esc_html( $username ); ?>
                                        </p>
                                        <p style="margin: 0; color: #2d3748; font-size: 15px; line-height: 1.6;">
                                            <strong style="color: #4a5568;">To set your password, visit the following address:</strong><br>
                                            <a href="<?php echo esc_url( $password_reset_link ); ?>" style="color: #667eea; text-decoration: none; word-break: break-all; font-weight: 500;"><?php echo esc_html( $password_reset_link ); ?></a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 35px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="<?php echo esc_url( $profile_link ); ?>" style="display: inline-block; padding: 16px 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.25);">Get Started</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 0 0 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Thank You! We can't wait to see where your summer takes you.
                            </p>
                            
                            <p style="margin: 0 0 5px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Think Summer!
                            </p>
                            
                            <p style="margin: 0; color: #2d3748; font-size: 16px; line-height: 1.7; font-weight: 600;">
                                Josh & Caleb
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f7fafc; padding: 30px 40px; text-align: center; border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
                            <p style="margin: 0 0 8px 0; color: #a0aec0; font-size: 13px;">
                                © <?php echo esc_html( date( 'Y' ) ); ?> OSO Jobs. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #cbd5e0; font-size: 12px;">
                                This is an automated message, please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Allow shortcodes inside WPForms confirmation message.
     *
     * @param string $message Confirmation HTML.
     * @param array  $form_data Form data.
     * @param array  $fields Fields.
     * @param array  $entry Entry data.
     * @return string
     */
    public function filter_confirmation_shortcodes( $message, $form_data, $fields, $entry ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        if ( false !== strpos( $message, '[' ) ) {
            $message = do_shortcode( $message );
        }

        return $message;
    }

    /**
     * Determine submission type based on form data.
     *
     * @param array $form_data Form data.
     * @return string
     */
    protected function detect_submission_type( $form_data ) {
        $type = 'jobseeker';

        if ( isset( $form_data['settings']['form_title'] ) ) {
            $title = strtolower( $form_data['settings']['form_title'] );
            if ( false !== strpos( $title, 'employer' ) ) {
                $type = 'employer';
            }
        }

        return apply_filters( 'oso_jobs_form_submission_type', $type, $form_data );
    }

    /**
     * Handle an employer submission by ensuring a user account exists.
     *
     * @param array $fields    Submitted fields.
     * @param array $form_data Form data.
     */
    protected function handle_employer_submission( $fields, $form_data ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
        $name  = self::find_field_value( $fields, array( 'name', 'full_name', 'company' ), __( 'Employer', 'oso-jobs-portal' ) );
        $email = self::find_field_value( $fields, array( 'email', 'business email' ) );

        if ( ! $email || ! is_email( $email ) ) {
            return;
        }

        $this->sync_user_account(
            array(
                'full_name' => $name,
                'email'     => $email,
            ),
            0,
            OSO_Jobs_Portal::ROLE_EMPLOYER
        );
    }

    /**
     * Fetch latest jobseeker submissions directly from WPForms entries table.
     *
     * @param int $limit Number of entries.
     * @return array
     */
    public static function get_recent_jobseekers( $limit = 3 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpforms_entries';
        $pattern    = str_replace( array( '_', '%' ), array( '\\_', '\\%' ), $table_name );
        $exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pattern ) );

        if ( $exists !== $table_name ) {
            return array();
        }

        $sql    = $wpdb->prepare(
            "SELECT entry_id, form_id, fields, date FROM {$table_name} WHERE status = %s ORDER BY date DESC LIMIT %d",
            'active',
            absint( $limit )
        );
        $rows   = $wpdb->get_results( $sql );
        $latest = array();

        if ( empty( $rows ) ) {
            return $latest;
        }

        foreach ( $rows as $row ) {
            $fields = maybe_unserialize( $row->fields );
            if ( ! is_array( $fields ) ) {
                $decoded = json_decode( $row->fields, true );
                if ( is_array( $decoded ) ) {
                    $fields = $decoded;
                }
            }

            if ( ! is_array( $fields ) ) {
                continue;
            }

            $latest[] = array(
                'entry_id'  => (int) $row->entry_id,
                'name'      => self::find_field_value( $fields, array( 'full name', 'name' ), __( 'Unknown applicant', 'oso-jobs-portal' ) ),
                'email'     => self::find_field_value( $fields, array( 'email' ) ),
                'location'  => self::find_field_value( $fields, array( 'location', 'state' ) ),
                'interests' => self::find_field_value( $fields, array( 'interest', 'role', 'position' ) ),
                'resume'    => self::find_file_url( $fields, array( 'resume' ) ),
                'submitted' => mysql2date( get_option( 'date_format' ), $row->date ),
            );
        }

        return $latest;
    }
}
