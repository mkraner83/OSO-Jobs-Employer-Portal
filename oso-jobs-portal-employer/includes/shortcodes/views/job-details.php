<?php
/**
 * Job Details Template - Single job view with application form
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get job ID from URL
$job_id = isset( $_GET['job_id'] ) ? intval( $_GET['job_id'] ) : 0;

if ( ! $job_id ) {
    echo '<div class="oso-error-message">';
    echo '<p>' . esc_html__( 'Job not found.', 'oso-employer-portal' ) . '</p>';
    echo '<a href="' . esc_url( home_url( '/job-portal/jobs/' ) ) . '" class="oso-btn oso-btn-primary">' . esc_html__( 'Back to Jobs', 'oso-employer-portal' ) . '</a>';
    echo '</div>';
    return;
}

// Get job post
$job = get_post( $job_id );

if ( ! $job || $job->post_type !== 'oso_job_posting' || $job->post_status !== 'publish' ) {
    echo '<div class="oso-error-message">';
    echo '<p>' . esc_html__( 'Job not found.', 'oso-employer-portal' ) . '</p>';
    echo '<a href="' . esc_url( home_url( '/job-portal/jobs/' ) ) . '" class="oso-btn oso-btn-primary">' . esc_html__( 'Back to Jobs', 'oso-employer-portal' ) . '</a>';
    echo '</div>';
    return;
}

// Check if job is expired
$is_expired = OSO_Job_Manager::instance()->is_job_expired( $job_id );

// Get job meta
$job_meta = OSO_Job_Manager::instance()->get_job_meta( $job_id );
$employer_id = ! empty( $job_meta['_oso_job_employer_id'] ) ? $job_meta['_oso_job_employer_id'] : 0;

// Get employer data
$camp_name = $employer_id ? get_post_meta( $employer_id, '_oso_employer_company', true ) : '';
$employer_state = $employer_id ? get_post_meta( $employer_id, '_oso_employer_state', true ) : '';
$employer_city = $employer_id ? get_post_meta( $employer_id, '_oso_employer_city', true ) : '';
$employer_logo = $employer_id ? get_post_meta( $employer_id, '_oso_employer_logo', true ) : '';
$employer_description = $employer_id ? get_post_meta( $employer_id, '_oso_employer_description', true ) : '';
$employer_website = $employer_id ? get_post_meta( $employer_id, '_oso_employer_website', true ) : '';

// Get employer user for contact
$employer_user_id = $employer_id ? get_post_meta( $employer_id, '_oso_employer_user_id', true ) : 0;
$employer_email = $employer_user_id ? get_userdata( $employer_user_id )->user_email : '';

// Job types
$job_types_list = ! empty( $job_meta['_oso_job_type'] ) ? explode( "\n", $job_meta['_oso_job_type'] ) : array();

// Required skills
$required_skills = ! empty( $job_meta['_oso_job_required_skills'] ) ? explode( "\n", $job_meta['_oso_job_required_skills'] ) : array();

// Check if user is logged in
$is_logged_in = is_user_logged_in();
$current_user_id = get_current_user_id();

// Get jobseeker profile if logged in
$jobseeker_id = 0;
if ( $is_logged_in ) {
    $jobseeker_posts = get_posts( array(
        'post_type'      => 'oso_jobseeker',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_oso_jobseeker_user_id',
        'meta_value'     => $current_user_id,
    ) );
    if ( ! empty( $jobseeker_posts ) ) {
        $jobseeker_id = $jobseeker_posts[0]->ID;
    }
}

// Success message after application
$application_success = isset( $_GET['application'] ) && $_GET['application'] === 'success';
?>

<div class="oso-job-details">
    <div class="oso-job-details-header">
        <a href="<?php echo esc_url( home_url( '/job-portal/jobs/' ) ); ?>" class="oso-back-link">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
            <?php esc_html_e( 'Back to Jobs', 'oso-employer-portal' ); ?>
        </a>
    </div>

    <?php if ( $application_success ) : ?>
        <div class="oso-success-message">
            <span class="dashicons dashicons-yes-alt"></span>
            <p><?php esc_html_e( 'Your application has been submitted successfully! The employer will contact you if they are interested.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>

    <?php if ( $is_expired ) : ?>
        <div class="oso-warning-message">
            <span class="dashicons dashicons-warning"></span>
            <p><?php esc_html_e( 'This job posting has expired and is no longer accepting applications.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>

    <div class="oso-job-details-content">
        <!-- Left Column: Job Information -->
        <div class="oso-job-main">
            <div class="oso-job-header-section">
                <h1 class="oso-job-main-title"><?php echo esc_html( $job->post_title ); ?></h1>
                
                <?php if ( $is_expired ) : ?>
                    <span class="oso-job-status oso-status-expired"><?php esc_html_e( 'Expired', 'oso-employer-portal' ); ?></span>
                <?php else : ?>
                    <span class="oso-job-status oso-status-active"><?php esc_html_e( 'Active', 'oso-employer-portal' ); ?></span>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $job_types_list ) ) : ?>
                <div class="oso-job-types-section">
                    <h3><?php esc_html_e( 'Position Types', 'oso-employer-portal' ); ?></h3>
                    <div class="oso-job-types">
                        <?php foreach ( $job_types_list as $type ) : ?>
                            <span class="oso-job-type-badge"><?php echo esc_html( trim( $type ) ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="oso-job-info-grid">
                <?php if ( ! empty( $job_meta['_oso_job_start_date'] ) && ! empty( $job_meta['_oso_job_end_date'] ) ) : ?>
                    <div class="oso-job-info-item">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <div>
                            <strong><?php esc_html_e( 'Season Dates', 'oso-employer-portal' ); ?></strong>
                            <p>
                                <?php 
                                echo esc_html( date_i18n( 'F j, Y', strtotime( $job_meta['_oso_job_start_date'] ) ) );
                                echo ' - ';
                                echo esc_html( date_i18n( 'F j, Y', strtotime( $job_meta['_oso_job_end_date'] ) ) );
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $job_meta['_oso_job_positions'] ) ) : ?>
                    <div class="oso-job-info-item">
                        <span class="dashicons dashicons-groups"></span>
                        <div>
                            <strong><?php esc_html_e( 'Positions Available', 'oso-employer-portal' ); ?></strong>
                            <p><?php echo esc_html( $job_meta['_oso_job_positions'] ); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $job_meta['_oso_job_compensation'] ) ) : ?>
                    <div class="oso-job-info-item">
                        <span class="dashicons dashicons-money-alt"></span>
                        <div>
                            <strong><?php esc_html_e( 'Compensation', 'oso-employer-portal' ); ?></strong>
                            <p><?php echo esc_html( $job_meta['_oso_job_compensation'] ); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="oso-job-section">
                <h3><?php esc_html_e( 'Job Description', 'oso-employer-portal' ); ?></h3>
                <div class="oso-job-description">
                    <?php echo wpautop( wp_kses_post( $job->post_content ) ); ?>
                </div>
            </div>

            <?php if ( ! empty( $required_skills ) ) : ?>
                <div class="oso-job-section">
                    <h3><?php esc_html_e( 'Required Skills & Certifications', 'oso-employer-portal' ); ?></h3>
                    <ul class="oso-skills-list">
                        <?php foreach ( $required_skills as $skill ) : ?>
                            <li><span class="dashicons dashicons-yes"></span><?php echo esc_html( trim( $skill ) ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $job_meta['_oso_job_application_instructions'] ) ) : ?>
                <div class="oso-job-section">
                    <h3><?php esc_html_e( 'Application Instructions', 'oso-employer-portal' ); ?></h3>
                    <div class="oso-application-instructions">
                        <?php echo wpautop( esc_html( $job_meta['_oso_job_application_instructions'] ) ); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Employer Info & Application -->
        <div class="oso-job-sidebar">
            <!-- Employer Card -->
            <div class="oso-employer-card">
                <?php if ( $employer_logo ) : ?>
                    <div class="oso-employer-logo">
                        <img src="<?php echo esc_url( $employer_logo ); ?>" alt="<?php echo esc_attr( $camp_name ); ?>">
                    </div>
                <?php endif; ?>

                <h3><?php echo esc_html( $camp_name ); ?></h3>
                
                <?php if ( $employer_city && $employer_state ) : ?>
                    <p class="oso-employer-location">
                        <span class="dashicons dashicons-location"></span>
                        <?php echo esc_html( $employer_city . ', ' . $employer_state ); ?>
                    </p>
                <?php elseif ( $employer_state ) : ?>
                    <p class="oso-employer-location">
                        <span class="dashicons dashicons-location"></span>
                        <?php echo esc_html( $employer_state ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( $employer_description ) : ?>
                    <div class="oso-employer-description">
                        <?php echo wp_trim_words( wp_kses_post( $employer_description ), 30, '...' ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( $employer_website ) : ?>
                    <a href="<?php echo esc_url( $employer_website ); ?>" target="_blank" rel="noopener" class="oso-btn oso-btn-secondary oso-btn-full">
                        <span class="dashicons dashicons-external"></span>
                        <?php esc_html_e( 'Visit Website', 'oso-employer-portal' ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Application Form -->
            <?php if ( ! $is_expired ) : ?>
                <div class="oso-application-card">
                    <h3><?php esc_html_e( 'Apply for this Job', 'oso-employer-portal' ); ?></h3>
                    
                    <?php if ( $is_logged_in && $jobseeker_id ) : ?>
                        <!-- Logged in jobseeker - show application form -->
                        <form id="job-application-form" class="oso-job-application-form" method="post">
                            <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>">
                            <input type="hidden" name="jobseeker_id" value="<?php echo esc_attr( $jobseeker_id ); ?>">
                            
                            <div class="oso-form-group">
                                <label for="cover_letter"><?php esc_html_e( 'Cover Letter', 'oso-employer-portal' ); ?> *</label>
                                <textarea 
                                    id="cover_letter" 
                                    name="cover_letter" 
                                    rows="8" 
                                    required
                                    placeholder="<?php esc_attr_e( 'Tell the employer why you\'re a great fit for this position...', 'oso-employer-portal' ); ?>"
                                ></textarea>
                            </div>

                            <div class="oso-form-group">
                                <label>
                                    <input type="checkbox" name="consent" required>
                                    <?php esc_html_e( 'I confirm that the information in my profile is accurate and up-to-date.', 'oso-employer-portal' ); ?>
                                </label>
                            </div>

                            <button type="submit" class="oso-btn oso-btn-primary oso-btn-full" id="submit-application-btn">
                                <?php esc_html_e( 'Submit Application', 'oso-employer-portal' ); ?>
                            </button>

                            <p class="oso-form-note">
                                <span class="dashicons dashicons-info"></span>
                                <?php esc_html_e( 'Your profile information will be shared with the employer.', 'oso-employer-portal' ); ?>
                            </p>
                        </form>
                    <?php elseif ( $is_logged_in ) : ?>
                        <!-- Logged in but no jobseeker profile -->
                        <div class="oso-login-prompt">
                            <p><?php esc_html_e( 'You need to complete your jobseeker profile before applying for jobs.', 'oso-employer-portal' ); ?></p>
                            <a href="<?php echo esc_url( home_url( '/job-portal/jobseeker-profile/' ) ); ?>" class="oso-btn oso-btn-primary oso-btn-full">
                                <?php esc_html_e( 'Complete Profile', 'oso-employer-portal' ); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <!-- Not logged in -->
                        <div class="oso-login-prompt">
                            <p><?php esc_html_e( 'You must be logged in as a jobseeker to apply for this position.', 'oso-employer-portal' ); ?></p>
                            <a href="<?php echo esc_url( wp_login_url( get_permalink() . '?job_id=' . $job_id ) ); ?>" class="oso-btn oso-btn-primary oso-btn-full">
                                <?php esc_html_e( 'Log In', 'oso-employer-portal' ); ?>
                            </a>
                            <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="oso-btn oso-btn-secondary oso-btn-full">
                                <?php esc_html_e( 'Create Account', 'oso-employer-portal' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.oso-job-details {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.oso-job-details-header {
    margin-bottom: 20px;
}

.oso-back-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #8051B0;
    text-decoration: none;
    font-size: 1em;
    transition: color 0.3s;
}

.oso-back-link:hover {
    color: #5a3880;
}

.oso-success-message,
.oso-warning-message,
.oso-error-message {
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.oso-success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.oso-warning-message {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.oso-error-message {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    text-align: center;
    flex-direction: column;
    padding: 40px;
}

.oso-success-message .dashicons,
.oso-warning-message .dashicons,
.oso-error-message .dashicons {
    font-size: 24px;
}

.oso-job-details-content {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
}

.oso-job-main {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 30px;
}

.oso-job-header-section {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
}

.oso-job-main-title {
    margin: 0;
    font-size: 2em;
    color: #333;
    flex: 1;
}

.oso-job-status {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.9em;
    font-weight: 600;
    white-space: nowrap;
}

.oso-job-types-section {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid #e0e0e0;
}

.oso-job-types-section h3 {
    margin: 0 0 15px 0;
    font-size: 1.1em;
    color: #666;
}

.oso-job-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 6px;
}

.oso-job-info-item {
    display: flex;
    gap: 12px;
}

.oso-job-info-item .dashicons {
    color: #548A8F;
    font-size: 24px;
    margin-top: 2px;
}

.oso-job-info-item strong {
    display: block;
    color: #333;
    margin-bottom: 5px;
    font-size: 0.9em;
}

.oso-job-info-item p {
    margin: 0;
    color: #666;
    font-size: 1em;
}

.oso-job-section {
    margin-bottom: 30px;
}

.oso-job-section h3 {
    margin: 0 0 15px 0;
    font-size: 1.4em;
    color: #333;
}

.oso-job-description {
    color: #555;
    line-height: 1.8;
}

.oso-skills-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 10px;
}

.oso-skills-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.oso-skills-list .dashicons {
    color: #5cb85c;
    font-size: 20px;
}

.oso-application-instructions {
    padding: 15px;
    background: #f9f9f9;
    border-left: 4px solid #8051B0;
    border-radius: 4px;
}

.oso-job-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.oso-employer-card,
.oso-application-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
}

.oso-employer-logo {
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e0e0e0;
}

.oso-employer-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.oso-employer-card h3,
.oso-application-card h3 {
    margin: 0 0 15px 0;
    font-size: 1.3em;
    color: #333;
    text-align: center;
}

.oso-employer-location {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    color: #666;
    margin-bottom: 15px;
}

.oso-employer-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 0.95em;
}

.oso-job-application-form .oso-form-group {
    margin-bottom: 20px;
}

.oso-job-application-form label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
}

.oso-job-application-form textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: 1em;
    resize: vertical;
}

.oso-job-application-form input[type="checkbox"] {
    margin-right: 8px;
}

.oso-form-note {
    margin-top: 15px;
    padding: 10px;
    background: #f0f8ff;
    border-radius: 4px;
    font-size: 0.9em;
    color: #666;
    display: flex;
    align-items: center;
    gap: 8px;
}

.oso-form-note .dashicons {
    color: #548A8F;
}

.oso-login-prompt {
    text-align: center;
}

.oso-login-prompt p {
    margin-bottom: 20px;
    color: #666;
}

@media (max-width: 992px) {
    .oso-job-details-content {
        grid-template-columns: 1fr;
    }
    
    .oso-job-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .oso-job-main {
        padding: 20px;
    }
    
    .oso-job-main-title {
        font-size: 1.5em;
    }
    
    .oso-job-info-grid {
        grid-template-columns: 1fr;
    }
    
    .oso-skills-list {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#job-application-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $('#submit-application-btn');
        
        // Disable button and show loading
        $submitBtn.prop('disabled', true).text('<?php esc_html_e( 'Submitting...', 'oso-employer-portal' ); ?>');
        
        var formData = {
            action: 'oso_submit_job_application',
            nonce: osoEmployerPortal.jobNonce,
            job_id: $form.find('[name="job_id"]').val(),
            jobseeker_id: $form.find('[name="jobseeker_id"]').val(),
            cover_letter: $form.find('[name="cover_letter"]').val()
        };
        
        $.post(osoEmployerPortal.ajaxUrl, formData, function(response) {
            if (response.success) {
                // Redirect to same page with success parameter
                window.location.href = window.location.pathname + '?job_id=<?php echo esc_js( $job_id ); ?>&application=success';
            } else {
                alert(response.data.message || '<?php esc_html_e( 'Failed to submit application. Please try again.', 'oso-employer-portal' ); ?>');
                $submitBtn.prop('disabled', false).text('<?php esc_html_e( 'Submit Application', 'oso-employer-portal' ); ?>');
            }
        }).fail(function() {
            alert('<?php esc_html_e( 'An error occurred. Please try again.', 'oso-employer-portal' ); ?>');
            $submitBtn.prop('disabled', false).text('<?php esc_html_e( 'Submit Application', 'oso-employer-portal' ); ?>');
        });
    });
});
</script>
