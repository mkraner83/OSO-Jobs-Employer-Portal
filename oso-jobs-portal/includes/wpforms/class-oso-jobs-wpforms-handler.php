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
            wp_new_user_notification( $user_id, null, 'both' );
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
            $password_reset_link = wp_lostpassword_url();
            
            // Create styled HTML email
            $message = $this->get_employer_welcome_email_html( $first_name, $user->user_login, $profile_link, $password_reset_link );
            
            // Set content type to HTML
            add_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
            wp_mail( $user->user_email, $subject, $message );
            remove_filter( 'wp_mail_content_type', function() { return 'text/html'; } );
        } else {
            // Jobseeker - keep simple text email
            $subject = __( 'Your OSO Jobs account', 'oso-jobs-portal' );
            $message = sprintf(
                "%s\n\n%s: %s\n\n%s: %s\n\n%s:\n%s",
                __( 'Welcome! Your candidate profile has been created.', 'oso-jobs-portal' ),
                __( 'Username', 'oso-jobs-portal' ),
                $user->user_login,
                __( 'Profile link', 'oso-jobs-portal' ),
                wp_login_url( home_url( '/job-portal/jobseeker-profile/' ) ),
                __( 'To set your password, visit the following address', 'oso-jobs-portal' ),
                wp_lostpassword_url()
            );
            
            wp_mail( $user->user_email, $subject, $message );
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
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 20px 10px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-collapse: collapse;">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4A7477 0%, #3A5C5F 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">Welcome to OSO Jobs!</h1>
                        </td>
                    </tr>
                    
                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px;">Hi <?php echo esc_html( $first_name ); ?>,</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #666666; font-size: 16px; line-height: 1.6;">
                                Thank you for registering with OSO Jobs! Your employer account is now active, and you're ready to start posting job opportunities and connecting with talented candidates.
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #666666; font-size: 16px; line-height: 1.6;">
                                Here's what you can do next:
                            </p>
                            
                            <!-- Features List -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #4A7477; margin-bottom: 10px;">
                                        <strong style="color: #333333; font-size: 16px;">✓ Post Job Listings</strong>
                                        <p style="margin: 5px 0 0 0; color: #666666; font-size: 14px;">Create and publish job opportunities to attract the right candidates.</p>
                                    </td>
                                </tr>
                                <tr><td style="height: 10px;"></td></tr>
                                <tr>
                                    <td style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #4A7477;">
                                        <strong style="color: #333333; font-size: 16px;">✓ Browse Candidates</strong>
                                        <p style="margin: 5px 0 0 0; color: #666666; font-size: 14px;">Search through qualified job seekers and find your perfect match.</p>
                                    </td>
                                </tr>
                                <tr><td style="height: 10px;"></td></tr>
                                <tr>
                                    <td style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #4A7477;">
                                        <strong style="color: #333333; font-size: 16px;">✓ Manage Applications</strong>
                                        <p style="margin: 5px 0 0 0; color: #666666; font-size: 14px;">Review, approve, or reject applications efficiently from your dashboard.</p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Account Details Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f0f7f8; border: 1px solid #d0e4e6; border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h3 style="margin: 0 0 15px 0; color: #4A7477; font-size: 18px;">Your Account Details</h3>
                                        <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                            <strong>Username:</strong> <?php echo esc_html( $username ); ?>
                                        </p>
                                        <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                            <strong>Profile Link:</strong><br>
                                            <a href="<?php echo esc_url( $profile_link ); ?>" style="color: #4A7477; text-decoration: none; word-break: break-all;"><?php echo esc_html( $profile_link ); ?></a>
                                        </p>
                                        <p style="margin: 0; color: #666666; font-size: 14px;">
                                            <strong>Set Your Password:</strong><br>
                                            <a href="<?php echo esc_url( $password_reset_link ); ?>" style="color: #4A7477; text-decoration: none; word-break: break-all;"><?php echo esc_html( $password_reset_link ); ?></a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="<?php echo esc_url( $profile_link ); ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #4A7477 0%, #3A5C5F 100%); color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold;">Access Your Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 0 0 20px 0; color: #666666; font-size: 16px; line-height: 1.6;">
                                If you have any questions or need assistance, feel free to reach out to our support team.
                            </p>
                            
                            <p style="margin: 0; color: #666666; font-size: 16px; line-height: 1.6;">
                                Best regards,<br>
                                <strong>The OSO Jobs Team</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #999999; font-size: 14px;">
                                © <?php echo esc_html( date( 'Y' ) ); ?> OSO Jobs. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">
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
