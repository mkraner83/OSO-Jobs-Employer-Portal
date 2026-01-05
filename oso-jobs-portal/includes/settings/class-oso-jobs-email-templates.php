<?php
/**
 * Email Templates Settings Manager.
 *
 * @package OSO_Jobs_Portal\Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Email templates manager.
 */
class OSO_Jobs_Email_Templates {

    /**
     * Option name for email templates.
     */
    const OPTION_NAME = 'oso_jobs_email_templates';

    /**
     * Initialize email templates.
     */
    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
    }

    /**
     * Register settings.
     */
    public static function register_settings() {
        register_setting(
            'oso_jobs_email_templates',
            self::OPTION_NAME,
            array(
                'sanitize_callback' => array( __CLASS__, 'sanitize_templates' ),
            )
        );
    }

    /**
     * Sanitize email templates.
     *
     * @param array $input Email templates input.
     * @return array Sanitized templates.
     */
    public static function sanitize_templates( $input ) {
        $sanitized = array();
        
        if ( ! is_array( $input ) ) {
            return $sanitized;
        }
        
        foreach ( $input as $key => $template ) {
            if ( isset( $template['subject'] ) && isset( $template['body'] ) ) {
                $sanitized[ $key ] = array(
                    'subject' => sanitize_text_field( $template['subject'] ),
                    'body'    => wp_kses_post( $template['body'] ),
                );
            }
        }
        
        return $sanitized;
    }

    /**
     * Get all email templates with defaults.
     *
     * @return array Email templates.
     */
    public static function get_templates() {
        $saved = get_option( self::OPTION_NAME, array() );
        $defaults = self::get_default_templates();
        
        return wp_parse_args( $saved, $defaults );
    }

    /**
     * Get a specific email template.
     *
     * @param string $template_key Template key.
     * @return array|null Template data or null if not found.
     */
    public static function get_template( $template_key ) {
        $templates = self::get_templates();
        return isset( $templates[ $template_key ] ) ? $templates[ $template_key ] : null;
    }

    /**
     * Get default email templates.
     *
     * @return array Default templates.
     */
    public static function get_default_templates() {
        return array(
            'employer_welcome' => array(
                'subject' => 'Welcome to OSO Jobs – Your Employer Account is Ready!',
                'body'    => self::get_employer_welcome_default_body(),
                'variables' => array(
                    '{first_name}' => 'User\'s first name or display name',
                    '{username}' => 'WordPress username',
                    '{password_reset_link}' => 'Link to set password',
                    '{profile_link}' => 'Link to employer profile/login',
                ),
                'description' => 'Sent to new employers when they register their account',
            ),
            'jobseeker_welcome' => array(
                'subject' => 'Welcome to OSO Jobs – Your Summer Starts Here!',
                'body'    => self::get_jobseeker_welcome_default_body(),
                'variables' => array(
                    '{first_name}' => 'User\'s first name or display name',
                    '{username}' => 'WordPress username',
                    '{password_reset_link}' => 'Link to set password',
                    '{profile_link}' => 'Link to jobseeker profile/login',
                ),
                'description' => 'Sent to new jobseekers when they register their account',
            ),
            'jobseeker_profile_approved' => array(
                'subject' => 'Your OSO Jobs Profile Has Been Approved!',
                'body'    => self::get_jobseeker_approval_default_body(),
                'variables' => array(
                    '{jobseeker_name}' => 'Job seeker\'s full name',
                    '{dashboard_url}' => 'Link to jobseeker dashboard',
                ),
                'description' => 'Sent to jobseeker when their profile is approved by admin',
            ),
            'new_application_employer' => array(
                'subject' => 'New Application: {jobseeker_name} applied for {job_title}',
                'body'    => self::get_new_application_employer_default_body(),
                'variables' => array(
                    '{camp_name}' => 'Employer/Camp name',
                    '{job_title}' => 'Job position title',
                    '{jobseeker_name}' => 'Job seeker\'s full name',
                    '{jobseeker_email}' => 'Job seeker\'s email address',
                    '{jobseeker_phone}' => 'Job seeker\'s phone number',
                    '{message_content}' => 'Application message from jobseeker',
                    '{dashboard_url}' => 'Link to employer dashboard',
                ),
                'description' => 'Sent to employer when they receive a new job application',
            ),
            'application_approved_jobseeker' => array(
                'subject' => 'Congratulations! Your application has been approved by {camp_name}',
                'body'    => self::get_application_approved_jobseeker_default_body(),
                'variables' => array(
                    '{jobseeker_name}' => 'Job seeker\'s full name',
                    '{job_title}' => 'Job position title',
                    '{camp_name}' => 'Employer/Camp name',
                    '{contact_info}' => 'Employer contact information',
                    '{job_start_date}' => 'Job start date (if available)',
                    '{dashboard_url}' => 'Link to jobseeker dashboard',
                ),
                'description' => 'Sent to jobseeker when their application is approved by employer',
            ),
            'application_approved_admin' => array(
                'subject' => '✅ Application Approved: {jobseeker_name} hired by {camp_name}',
                'body'    => self::get_application_approved_admin_default_body(),
                'variables' => array(
                    '{jobseeker_name}' => 'Job seeker\'s full name',
                    '{camp_name}' => 'Employer/Camp name',
                    '{job_title}' => 'Job position title',
                    '{job_start_date}' => 'Job start date',
                    '{job_compensation}' => 'Job compensation',
                    '{employer_contact_name}' => 'Employer contact person name',
                    '{employer_email}' => 'Employer email',
                    '{employer_phone}' => 'Employer phone',
                    '{employer_user_email}' => 'Employer WordPress user email',
                    '{jobseeker_email}' => 'Job seeker email',
                    '{jobseeker_phone}' => 'Job seeker phone',
                    '{jobseeker_user_email}' => 'Job seeker WordPress user email',
                    '{application_date}' => 'Date application was submitted',
                    '{approval_date}' => 'Date application was approved',
                    '{message_content}' => 'Application message from jobseeker',
                    '{admin_url}' => 'WordPress admin URL for applications',
                ),
                'description' => 'Sent to site admin when an employer approves an application',
            ),
            'application_cancelled_jobseeker' => array(
                'subject' => 'Application Cancelled - {job_title}',
                'body'    => self::get_application_cancelled_jobseeker_default_body(),
                'variables' => array(
                    '{jobseeker_name}' => 'Job seeker\'s full name',
                    '{job_title}' => 'Job position title',
                    '{camp_name}' => 'Employer/Camp name',
                ),
                'description' => 'Sent to jobseeker when they cancel their application',
            ),
            'application_cancelled_employer' => array(
                'subject' => 'Application Cancelled - {job_title}',
                'body'    => self::get_application_cancelled_employer_default_body(),
                'variables' => array(
                    '{jobseeker_name}' => 'Job seeker\'s full name',
                    '{job_title}' => 'Job position title',
                ),
                'description' => 'Sent to employer when a jobseeker cancels their application',
            ),
            'employer_interest_jobseeker' => array(
                'subject' => '{camp_name} is interested in you!',
                'body'    => self::get_employer_interest_default_body(),
                'variables' => array(
                    '{jobseeker_name}' => 'Job seeker\'s full name',
                    '{camp_name}' => 'Employer/Camp name',
                    '{employer_logo}' => 'URL to employer logo (for HTML emails)',
                    '{employer_website}' => 'Employer website URL',
                    '{employer_description}' => 'Employer description',
                    '{message}' => 'Message from employer',
                    '{dashboard_url}' => 'Link to jobseeker dashboard',
                ),
                'description' => 'Sent to jobseeker when an employer expresses interest in them',
            ),
        );
    }

    /**
     * Get default jobseeker approval email body (HTML).
     *
     * @return string HTML email body.
     */
    private static function get_jobseeker_approval_default_body() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Account Approved!</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hi {jobseeker_name},
                            </p>
                            
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">
                                Great news! Your OSO Jobs profile has been <strong>approved</strong> and is now active.
                            </p>
                            
                            <p style="margin: 0 0 30px; color: #333333; font-size: 16px; line-height: 1.6;">
                                You can now:
                            </p>
                            
                            <ul style="margin: 0 0 30px; padding-left: 20px; color: #333333; font-size: 16px; line-height: 1.8;">
                                <li>Apply for summer camp jobs</li>
                                <li>Be visible to employers searching for candidates</li>
                                <li>Connect with camps across the country</li>
                            </ul>
                            
                            <table role="presentation" style="margin: 30px 0;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="{dashboard_url}" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600;">Go to Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 30px 0 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                Good luck with your job search!<br>
                                <strong>The OSO Jobs Team</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999999; font-size: 13px;">
                                This is an automated message from OSO Jobs
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Get default employer interest email body (HTML).
     *
     * @return string HTML email body.
     */
    private static function get_employer_interest_default_body() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header with Purple Gradient -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                🌟 Exciting News!
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">
                                An employer is interested in you
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hi {jobseeker_name},
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Great news! <strong>{camp_name}</strong> has expressed interest in your profile and would like to connect with you about potential opportunities.
                            </p>
                            
                            <!-- Employer Info Card -->
                            <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 0 0 25px 0; border-radius: 4px;">
                                {employer_logo_html}
                                <h3 style="margin: 0 0 10px 0; color: #667eea; font-size: 20px;">{camp_name}</h3>
                                {employer_description_html}
                                {employer_website_html}
                            </div>
                            
                            <!-- Their Message -->
                            <div style="background: #ffffff; border: 2px solid #e0e0e0; padding: 20px; margin: 0 0 30px 0; border-radius: 8px;">
                                <h4 style="margin: 0 0 12px 0; color: #333333; font-size: 16px; font-weight: 600;">Their Message:</h4>
                                <p style="margin: 0; color: #666666; font-size: 15px; line-height: 1.6; white-space: pre-wrap;">{message}</p>
                            </div>
                            
                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 0 0 25px 0;">
                                <a href="{dashboard_url}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 16px; font-weight: 600;">
                                    View in Dashboard
                                </a>
                            </div>
                            
                            <p style="margin: 0; color: #999999; font-size: 14px; line-height: 1.6; text-align: center;">
                                Log in to your dashboard to view all employer interests and manage your profile.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999999; font-size: 13px;">
                                This is an automated message from OSO Jobs
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Render the email templates settings page.
     */
    public static function render_page() {
        // Handle form submission
        if ( isset( $_POST['oso_save_email_templates'] ) && check_admin_referer( 'oso_email_templates_save' ) ) {
            $templates = isset( $_POST['email_templates'] ) ? $_POST['email_templates'] : array();
            update_option( self::OPTION_NAME, self::sanitize_templates( $templates ) );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Email templates saved successfully!', 'oso-jobs-portal' ) . '</p></div>';
        }
        
        $templates = self::get_templates();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Email Templates', 'oso-jobs-portal' ); ?></h1>
            <p><?php esc_html_e( 'Customize the automated emails sent by OSO Jobs system. Use the provided variables to personalize your emails.', 'oso-jobs-portal' ); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'oso_email_templates_save' ); ?>
                
                <div class="oso-email-templates">
                    <?php foreach ( $templates as $key => $template ) : ?>
                        <div class="oso-email-template postbox" style="margin-top: 20px;">
                            <div class="postbox-header">
                                <h2 class="hndle">
                                    <?php echo esc_html( self::get_template_title( $key ) ); ?>
                                </h2>
                            </div>
                            <div class="inside">
                                <?php if ( ! empty( $template['description'] ) ) : ?>
                                    <p class="description" style="margin-top: 0;">
                                        <?php echo esc_html( $template['description'] ); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ( ! empty( $template['variables'] ) ) : ?>
                                    <div class="oso-email-variables" style="background: #f0f0f1; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                                        <strong><?php esc_html_e( 'Available Variables:', 'oso-jobs-portal' ); ?></strong>
                                        <ul style="margin: 10px 0 0 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 5px;">
                                            <?php foreach ( $template['variables'] as $var => $desc ) : ?>
                                                <li style="list-style: none; margin: 0;">
                                                    <code style="background: #fff; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html( $var ); ?></code> - 
                                                    <?php echo esc_html( $desc ); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr( $key ); ?>_subject">
                                                <?php esc_html_e( 'Subject', 'oso-jobs-portal' ); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input 
                                                type="text" 
                                                id="<?php echo esc_attr( $key ); ?>_subject"
                                                name="email_templates[<?php echo esc_attr( $key ); ?>][subject]" 
                                                value="<?php echo esc_attr( $template['subject'] ); ?>" 
                                                class="large-text"
                                            />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr( $key ); ?>_body">
                                                <?php esc_html_e( 'Body', 'oso-jobs-portal' ); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <?php
                                            $editor_id = 'email_templates_' . $key . '_body';
                                            $editor_settings = array(
                                                'textarea_name' => 'email_templates[' . esc_attr( $key ) . '][body]',
                                                'textarea_rows' => 20,
                                                'media_buttons' => false,
                                                'teeny'         => false,
                                                'tinymce'       => array(
                                                    'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,forecolor,|,alignleft,aligncenter,alignright,|,bullist,numlist,|,link,unlink,|,undo,redo',
                                                    'toolbar2' => 'fontsizeselect,removeformat,charmap,|,outdent,indent,|,code,fullscreen',
                                                ),
                                                'quicktags'     => array(
                                                    'buttons' => 'strong,em,link,ul,ol,li,code,close',
                                                ),
                                            );
                                            wp_editor( $template['body'], $editor_id, $editor_settings );
                                            ?>
                                            <p class="description">
                                                <?php esc_html_e( 'Use the Visual tab for easy editing or the Text tab for HTML. Use variables from the list above to personalize the email.', 'oso-jobs-portal' ); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <p class="submit">
                    <button type="submit" name="oso_save_email_templates" class="button button-primary button-large">
                        <?php esc_html_e( 'Save All Email Templates', 'oso-jobs-portal' ); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <style>
            .oso-email-template {
                border: 1px solid #c3c4c7;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .oso-email-template .hndle {
                font-size: 15px;
                padding: 12px;
            }
            .oso-email-template .inside {
                padding: 12px;
            }
            .oso-email-variables code {
                font-size: 12px;
            }
            .oso-email-template .form-table th {
                width: 120px;
                vertical-align: top;
                padding-top: 20px;
            }
            .oso-email-template .wp-editor-container {
                border: 1px solid #ddd;
            }
        </style>
        <?php
    }

    /**
     * Get default new application employer email body (HTML).
     *
     * @return string HTML email body.
     */
    private static function get_new_application_employer_default_body() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                🎉 New Application!
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">
                                You have received a new job application
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hello {camp_name},
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Great news! You have received a new job application.
                            </p>
                            
                            <!-- Application Details Card -->
                            <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 0 0 25px 0; border-radius: 4px;">
                                <h3 style="margin: 0 0 15px 0; color: #667eea; font-size: 18px;">Application Details</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; font-weight: 600;">Position:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px;">{job_title}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; font-weight: 600;">Applicant:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px;">{jobseeker_name}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; font-weight: 600;">Email:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px;">{jobseeker_email}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; font-weight: 600;">Phone:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px;">{jobseeker_phone}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Message from Applicant -->
                            <div style="background: #ffffff; border: 2px solid #e0e0e0; padding: 20px; margin: 0 0 30px 0; border-radius: 8px;">
                                <h4 style="margin: 0 0 12px 0; color: #333333; font-size: 16px; font-weight: 600;">Message from Applicant:</h4>
                                <p style="margin: 0; color: #666666; font-size: 15px; line-height: 1.6; white-space: pre-wrap;">{message_content}</p>
                            </div>
                            
                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 0 0 25px 0;">
                                <a href="{dashboard_url}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 16px; font-weight: 600;">
                                    View Full Profile
                                </a>
                            </div>
                            
                            <p style="margin: 0; color: #999999; font-size: 14px; line-height: 1.6; text-align: center;">
                                Manage this application in your employer dashboard
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999999; font-size: 13px;">
                                This is an automated message from OSO Jobs
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Get default application approved jobseeker email body (HTML).
     *
     * @return string HTML email body.
     */
    private static function get_application_approved_jobseeker_default_body() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                🎉 Congratulations!
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">
                                Your application has been approved
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hello {jobseeker_name},
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Exciting news! Your application has been <strong>approved</strong>!
                            </p>
                            
                            <!-- Job Details Card -->
                            <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 0 0 25px 0; border-radius: 4px;">
                                <h3 style="margin: 0 0 15px 0; color: #667eea; font-size: 18px;">Job Details</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; font-weight: 600;">Position:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px;">{job_title}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #666666; font-size: 14px; font-weight: 600;">Employer:</td>
                                        <td style="padding: 8px 0; color: #333333; font-size: 14px;">{camp_name}</td>
                                    </tr>
                                </table>
                                <div style="margin-top: 12px; color: #333333; font-size: 14px;">{contact_info}{job_start_date}</div>
                            </div>
                            
                            <!-- Next Steps -->
                            <div style="background: #fff9e6; border: 2px solid #ffd700; padding: 20px; margin: 0 0 30px 0; border-radius: 8px;">
                                <h4 style="margin: 0 0 12px 0; color: #333333; font-size: 16px; font-weight: 600;">Next Steps</h4>
                                <p style="margin: 0; color: #666666; font-size: 15px; line-height: 1.6;">
                                    The employer will contact you directly to discuss the next steps. Please make sure to respond promptly to their communication.
                                </p>
                            </div>
                            
                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 0 0 25px 0;">
                                <a href="{dashboard_url}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 16px; font-weight: 600;">
                                    View in Dashboard
                                </a>
                            </div>
                            
                            <p style="margin: 0; color: #666666; font-size: 14px; line-height: 1.6; text-align: center;">
                                Good luck with your new position!<br>
                                <strong>The OSO Jobs Team</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999999; font-size: 13px;">
                                This is an automated message from OSO Jobs
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Get default application approved admin email body (HTML).
     *
     * @return string HTML email body.
     */
    private static function get_application_approved_admin_default_body() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                ✅ Application Approved
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px; opacity: 0.95;">
                                {jobseeker_name} hired by {camp_name}
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hello Admin,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                An employer has approved a job application on OSO Jobs Portal.
                            </p>
                            
                            <!-- Approval Details -->
                            <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 0 0 20px 0; border-radius: 4px;">
                                <h3 style="margin: 0 0 15px 0; color: #3b82f6; font-size: 18px;">Approval Details</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600; width: 140px;">Job Position:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{job_title}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Start Date:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{job_start_date}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Compensation:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{job_compensation}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Employer Information -->
                            <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 0 0 20px 0; border-radius: 4px;">
                                <h3 style="margin: 0 0 15px 0; color: #667eea; font-size: 18px;">Employer Information</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600; width: 140px;">Camp/Company:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{camp_name}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Contact Name:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{employer_contact_name}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Email:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{employer_email}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Phone:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{employer_phone}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">User Account:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{employer_user_email}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Candidate Information -->
                            <div style="background: #f0fdf4; border-left: 4px solid #22c55e; padding: 20px; margin: 0 0 20px 0; border-radius: 4px;">
                                <h3 style="margin: 0 0 15px 0; color: #22c55e; font-size: 18px;">Candidate Information</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600; width: 140px;">Name:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{jobseeker_name}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Email:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{jobseeker_email}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Phone:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{jobseeker_phone}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">User Account:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{jobseeker_user_email}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Application Info -->
                            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 0 0 30px 0; border-radius: 4px;">
                                <h3 style="margin: 0 0 15px 0; color: #f59e0b; font-size: 18px;">Application Info</h3>
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600; width: 140px;">Applied On:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{application_date}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #666666; font-size: 14px; font-weight: 600;">Approved On:</td>
                                        <td style="padding: 6px 0; color: #333333; font-size: 14px;">{approval_date}</td>
                                    </tr>
                                </table>
                                <div style="margin-top: 15px;">
                                    <p style="margin: 0 0 8px 0; color: #666666; font-size: 14px; font-weight: 600;">Candidate\'s Message:</p>
                                    <p style="margin: 0; color: #333333; font-size: 14px; line-height: 1.5;">{message_content}</p>
                                </div>
                            </div>
                            
                            <p style="margin: 0 0 25px 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                Both parties have been notified via email.
                            </p>
                            
                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 0;">
                                <a href="{admin_url}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 16px; font-weight: 600;">
                                    View in Admin
                                </a>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999999; font-size: 13px;">
                                This is an automated message from OSO Jobs System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Get default application cancelled jobseeker email body (HTML).
     *
     * @return string HTML email body.
     */
    private static function get_application_cancelled_jobseeker_default_body() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                Application Cancelled
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hi {jobseeker_name},
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Your application for the position <strong>{job_title}</strong> at <strong>{camp_name}</strong> has been successfully cancelled.
                            </p>
                            
                            <!-- Info Box -->
                            <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 0 0 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #666666; font-size: 15px; line-height: 1.6;">
                                    If you change your mind, you can apply again by visiting the job listing.
                                </p>
                            </div>
                            
                            <p style="margin: 0; color: #666666; font-size: 14px; line-height: 1.6; text-align: center;">
                                Best regards,<br>
                                <strong>OSO Jobs Team</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999999; font-size: 13px;">
                                This is an automated message from OSO Jobs
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Get default application cancelled employer email body (HTML).
     *
     * @return string HTML email body.
     */
    private static function get_application_cancelled_employer_default_body() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                Application Cancelled
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hello,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                <strong>{jobseeker_name}</strong> has cancelled their application for the position <strong>{job_title}</strong>.
                            </p>
                            
                            <!-- Info Box -->
                            <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 0 0 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #666666; font-size: 15px; line-height: 1.6;">
                                    You can view other applicants for this position in your employer dashboard.
                                </p>
                            </div>
                            
                            <p style="margin: 0; color: #666666; font-size: 14px; line-height: 1.6; text-align: center;">
                                Best regards,<br>
                                <strong>OSO Jobs Team</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999999; font-size: 13px;">
                                This is an automated message from OSO Jobs
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Get default employer welcome email body.
     *
     * @return string Default email body HTML.
     */
    private static function get_employer_welcome_default_body() {
        return '
            <p style="margin: 0 0 25px 0; color: #2d3748; font-size: 18px; line-height: 1.7; font-weight: 500;">Hi {first_name}, we are so excited you\'re here!</p>
            
            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                We\'re Josh and Caleb, the co-founders of OSO. We both started working at summer camps back in 2012 when we were 19, and it pretty much changed the whole trajectory of our lives. We found confidence, community, mentors, best friends, and career paths we didn\'t even know existed.
            </p>
            
            <p style="margin: 0 0 35px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                We built OSO so camps like yours can connect with thousands of motivated, talented job seekers who are ready to make this summer count. Our platform makes it easier than ever to post jobs, review applicants, and find the right people to join your team this summer.
            </p>
            
            <h2 style="margin: 0 0 25px 0; color: #2d3748; font-size: 26px; font-weight: 700; text-align: center;">What\'s Next?</h2>
            
            <p style="margin: 0 0 10px 0; color: #2d3748; font-size: 18px; line-height: 1.7; text-align: center; font-weight: 600;">
                Complete Your Profile. Post Jobs. Find Great Staff!
            </p>
            
            <p style="margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.7; text-align: center; font-style: italic;">
                It\'s really that simple.
            </p>
            
            <p style="margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                If you ever have questions about posting jobs, reviewing applicants, or just want advice on hiring, you can reach out to us directly. We\'re here to help you build the best team possible.
            </p>
            
            <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border: 2px solid #e2e8f0; border-radius: 10px; margin-bottom: 35px;">
                <tr>
                    <td style="padding: 30px;">
                        <h3 style="margin: 0 0 20px 0; color: #667eea; font-size: 18px; font-weight: 700;">Your Account Details</h3>
                        <p style="margin: 0 0 15px 0; color: #2d3748; font-size: 15px; line-height: 1.6;">
                            <strong style="color: #4a5568;">Username:</strong> {username}
                        </p>
                        <p style="margin: 0; color: #2d3748; font-size: 15px; line-height: 1.6;">
                            <strong style="color: #4a5568;">To set your password, visit the following address:</strong><br>
                            <a href="{password_reset_link}" style="color: #667eea; text-decoration: none; word-break: break-all; font-weight: 500;">{password_reset_link}</a>
                        </p>
                    </td>
                </tr>
            </table>
            
            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 35px;">
                <tr>
                    <td style="text-align: center;">
                        <a href="{profile_link}" style="display: inline-block; padding: 16px 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.25);">Get Started</a>
                    </td>
                </tr>
            </table>
            
            <p style="margin: 0 0 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                Thank You! We can\'t wait to help you find amazing staff this summer.
            </p>
            
            <p style="margin: 0 0 5px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                <strong>Josh & Caleb</strong><br>
                Co-Founders, OSO
            </p>';
    }

    /**
     * Get default jobseeker welcome email body.
     *
     * @return string Default email body HTML.
     */
    private static function get_jobseeker_welcome_default_body() {
        return '
            <p style="margin: 0 0 25px 0; color: #2d3748; font-size: 18px; line-height: 1.7; font-weight: 500;">Hi {first_name} we are so excited you\'re here!</p>
            
            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                We\'re Josh and Caleb, the co-founders of OSO. We both started working at summer camps back in 2012 when we were 19, and it pretty much changed the whole trajectory of our lives. We found confidence, community, mentors, best friends, and career paths we didn\'t even know existed.
            </p>
            
            <p style="margin: 0 0 35px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                We built OSO so you can have that same shot at a summer that actually means something. Now that you\'ve joined, you can instantly connect with camps across the country who are hiring for roles in sports, arts, waterfront, adventure, media, counseling, and more. Our hope is that OSO helps you find a job that feels exciting, supportive, and full of growth, not just another thing to fill your summer.
            </p>
            
            <h2 style=\"margin: 0 0 25px 0; color: #2d3748; font-size: 26px; font-weight: 700; text-align: center;\">What\\'s Next?</h2>
            
            <p style=\"margin: 0 0 10px 0; color: #2d3748; font-size: 18px; line-height: 1.7; text-align: center; font-weight: 600;\">
                Create A Profile. Search Jobs. Get Hired!
            </p>
            
            <p style=\"margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.7; text-align: center; font-style: italic;\">
                It\\'s really that simple.
            </p>
            
            <p style=\"margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.7;\">
                And you\\'re not doing this alone. If you ever have questions about getting hired, choosing a camp, interviews, or just want honest advice, you can talk to us directly. Join our WhatsApp group here:
            </p>
            
            <table role=\"presentation\" style=\"width: 100%; border-collapse: collapse; margin-bottom: 35px;\">
                <tr>
                    <td style=\"text-align: center;\">
                        <a href=\"https://chat.whatsapp.com/EQasmhOEsE92AvWyJDbWZX\" style=\"display: inline-block; padding: 16px 48px; background: #25D366; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(37, 211, 102, 0.25);\">📱 WhatsApp</a>
                    </td>
                </tr>
            </table>
            
            <table role=\"presentation\" style=\"width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border: 2px solid #e2e8f0; border-radius: 10px; margin-bottom: 35px;\">
                <tr>
                    <td style=\"padding: 30px;\">
                        <h3 style=\"margin: 0 0 20px 0; color: #667eea; font-size: 18px; font-weight: 700;\">Your Account Details</h3>
                        <p style=\"margin: 0 0 15px 0; color: #2d3748; font-size: 15px; line-height: 1.6;\">
                            <strong style=\"color: #4a5568;\">Username:</strong> {username}
                        </p>
                        <p style=\"margin: 0; color: #2d3748; font-size: 15px; line-height: 1.6;\">
                            <strong style=\"color: #4a5568;\">To set your password, visit the following address:</strong><br>
                            <a href=\"{password_reset_link}\" style=\"color: #667eea; text-decoration: none; word-break: break-all; font-weight: 500;\">{password_reset_link}</a>
                        </p>
                    </td>
                </tr>
            </table>
            
            <table role=\"presentation\" style=\"width: 100%; border-collapse: collapse; margin-bottom: 35px;\">
                <tr>
                    <td style=\"text-align: center;\">
                        <a href=\"{profile_link}\" style=\"display: inline-block; padding: 16px 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.25);\">Get Started</a>
                    </td>
                </tr>
            </table>
            
            <p style=\"margin: 0 0 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;\">
                Thank You! We can\\'t wait to see where your summer takes you.
            </p>
            
            <p style=\"margin: 0 0 5px 0; color: #4a5568; font-size: 16px; line-height: 1.7;\">
                <strong>Josh & Caleb</strong><br>
                Co-Founders, OSO
            </p>';
    }

    /**
     * Get human-readable template title.
     *
     * @param string $key Template key.
     * @return string Template title.
     */
    private static function get_template_title( $key ) {
        $titles = array(
            'employer_welcome'                => __( 'Employer Welcome Email', 'oso-jobs-portal' ),
            'jobseeker_welcome'               => __( 'Jobseeker Welcome Email', 'oso-jobs-portal' ),
            'jobseeker_profile_approved'      => __( 'Jobseeker Profile Approved', 'oso-jobs-portal' ),
            'new_application_employer'        => __( 'New Application Notification (Employer)', 'oso-jobs-portal' ),
            'application_approved_jobseeker'  => __( 'Application Approved (Jobseeker)', 'oso-jobs-portal' ),
            'application_approved_admin'      => __( 'Application Approved (Admin)', 'oso-jobs-portal' ),
            'application_cancelled_jobseeker' => __( 'Application Cancelled (Jobseeker)', 'oso-jobs-portal' ),
            'application_cancelled_employer'  => __( 'Application Cancelled (Employer)', 'oso-jobs-portal' ),
            'employer_interest_jobseeker'     => __( 'Employer Interest Notification (Jobseeker)', 'oso-jobs-portal' ),
        );
        
        return isset( $titles[ $key ] ) ? $titles[ $key ] : ucwords( str_replace( '_', ' ', $key ) );
    }
}

// Initialize email templates
OSO_Jobs_Email_Templates::init();
