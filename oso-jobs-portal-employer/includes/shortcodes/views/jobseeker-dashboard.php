<?php
/**
 * Jobseeker Dashboard Template
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! $is_logged_in ) :
    ?>
    <div class="oso-jobseeker-dashboard oso-login-required">
        <div class="oso-login-box">
            <div class="oso-login-header">
                <h3><?php esc_html_e( 'Jobseeker Login', 'oso-employer-portal' ); ?></h3>
                <p><?php esc_html_e( 'Please log in to access your dashboard', 'oso-employer-portal' ); ?></p>
            </div>
            
            <div class="oso-login-form">
                <?php echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            
            <p class="oso-lost-password">
                <a href="<?php echo esc_url( $lost_url ); ?>"><?php esc_html_e( 'Lost your password?', 'oso-employer-portal' ); ?></a>
            </p>
        </div>
    </div>
    <?php
    return;
endif;

// User is logged in
$photo = ! empty( $meta['_oso_jobseeker_photo'] ) ? $meta['_oso_jobseeker_photo'] : '';
$name = ! empty( $meta['_oso_jobseeker_full_name'] ) ? $meta['_oso_jobseeker_full_name'] : $jobseeker->post_title;
$is_approved = get_post_meta( $jobseeker_post->ID, '_oso_jobseeker_approved', true );
?>
<div class="oso-jobseeker-dashboard">
    <!-- Jobseeker Header -->
    <div class="oso-employer-header">
        <div class="oso-employer-header-left">
            <?php if ( $photo ) : ?>
                <div class="oso-employer-logo">
                    <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                </div>
            <?php endif; ?>
            <div class="oso-employer-info">
                <h1><?php echo esc_html( $name ); ?></h1>
                <p class="oso-employer-subtitle"><?php esc_html_e( 'Jobseeker Dashboard', 'oso-employer-portal' ); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Pending Approval Warning -->
    <?php if ( $is_approved !== '1' ) : ?>
        <div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; margin: 0 0 30px 0;">
            <p style="margin: 0; color: #856404;"><strong><?php esc_html_e( 'Account Pending Approval', 'oso-employer-portal' ); ?></strong></p>
            <p style="margin: 10px 0 0 0; color: #856404;"><?php esc_html_e( 'Your account is currently pending approval. You can browse jobs and view your profile, but you will not be able to apply for jobs until an administrator approves your account. You will receive an email notification once approved.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Full-Width Browse Jobs Button -->
    <div class="oso-quick-link-banner">
        <a href="<?php echo esc_url( home_url( '/job-portal/all-jobs/' ) ); ?>" class="oso-quick-link">
            <span class="dashicons dashicons-portfolio"></span>
            <span><?php esc_html_e( 'Browse All Jobs', 'oso-employer-portal' ); ?></span>
        </a>
    </div>

    <!-- My Applications Section -->
    <div class="oso-jobseeker-applications">
        <h3><?php esc_html_e( 'My Applications', 'oso-employer-portal' ); ?></h3>
        
        <?php
        // Get jobseeker's applications
        $applications = get_posts( array(
            'post_type'      => 'oso_job_application',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_query'     => array(
                array(
                    'key'   => '_oso_application_jobseeker_id',
                    'value' => $jobseeker_post->ID,
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        if ( ! empty( $applications ) ) :
            ?>
            <div class="oso-applications-grid">
                <?php foreach ( $applications as $application ) :
                    $job_id = get_post_meta( $application->ID, '_oso_application_job_id', true );
                    $employer_id = get_post_meta( $application->ID, '_oso_application_employer_id', true );
                    $status = get_post_meta( $application->ID, '_oso_application_status', true );
                    
                    $job = get_post( $job_id );
                    $employer = get_post( $employer_id );
                    
                    if ( ! $job || ! $employer ) {
                        continue;
                    }
                    
                    $employer_meta = get_post_meta( $employer_id );
                    $employer_logo = ! empty( $employer_meta['_oso_employer_logo'][0] ) ? $employer_meta['_oso_employer_logo'][0] : '';
                    $employer_state = ! empty( $employer_meta['_oso_employer_state'][0] ) ? $employer_meta['_oso_employer_state'][0] : '';
                    
                    $status_class = '';
                    $status_label = '';
                    switch ( $status ) {
                        case 'approved':
                            $status_class = 'status-approved';
                            $status_label = __( 'Approved', 'oso-employer-portal' );
                            break;
                        case 'rejected':
                            $status_class = 'status-rejected';
                            $status_label = __( 'Rejected', 'oso-employer-portal' );
                            break;
                        default:
                            $status_class = 'status-pending';
                            $status_label = __( 'Pending', 'oso-employer-portal' );
                    }
                    ?>
                    <div class="oso-application-card">
                        <div class="oso-application-header">
                            <?php if ( $employer_logo ) : ?>
                                <img src="<?php echo esc_url( $employer_logo ); ?>" alt="<?php echo esc_attr( $employer->post_title ); ?>" class="oso-employer-logo-small">
                            <?php else : ?>
                                <div class="oso-employer-logo-placeholder-small">
                                    <span class="dashicons dashicons-building"></span>
                                </div>
                            <?php endif; ?>
                            <div class="oso-application-title">
                                <h4><?php echo esc_html( $job->post_title ); ?></h4>
                                <p class="oso-employer-name"><?php echo esc_html( $employer->post_title ); ?></p>
                            </div>
                        </div>
                        
                        <div class="oso-application-meta">
                            <?php if ( $employer_state ) : ?>
                                <span class="oso-meta-item">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html( $employer_state ); ?>
                                </span>
                            <?php endif; ?>
                            <span class="oso-meta-item">
                                <span class="dashicons dashicons-calendar"></span>
                                <?php echo esc_html( get_the_date( '', $application->ID ) ); ?>
                            </span>
                        </div>
                        
                        <div class="oso-application-actions">
                            <span class="oso-status-badge <?php echo esc_attr( $status_class ); ?>">
                                <?php echo esc_html( $status_label ); ?>
                            </span>
                            <a href="<?php echo esc_url( add_query_arg( 'job_id', $job_id, home_url( '/job-portal/job-details/' ) ) ); ?>" class="oso-btn oso-btn-secondary oso-btn-small">
                                <?php esc_html_e( 'View Job', 'oso-employer-portal' ); ?>
                            </a>
                            <?php if ( $status === 'pending' ) : ?>
                                <button type="button" class="oso-btn oso-btn-danger oso-btn-small oso-cancel-application" data-application-id="<?php echo esc_attr( $application->ID ); ?>">
                                    <?php esc_html_e( 'Cancel Application', 'oso-employer-portal' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="oso-no-applications">
                <span class="dashicons dashicons-portfolio"></span>
                <p><?php esc_html_e( 'You haven\'t applied to any jobs yet.', 'oso-employer-portal' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/job-portal/all-jobs/' ) ); ?>" class="oso-btn oso-btn-primary">
                    <?php esc_html_e( 'Browse Jobs', 'oso-employer-portal' ); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Employer Interests Section -->
    <div class="oso-jobseeker-interests">
        <h3><?php esc_html_e( 'Employer Interest', 'oso-employer-portal' ); ?></h3>
        
        <?php
        // Get interests received for this jobseeker - using WP_Query for better debugging
        $interest_query = new WP_Query( array(
            'post_type'      => 'oso_emp_interest',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
            'meta_query'     => array(
                array(
                    'key'     => '_oso_jobseeker_id',
                    'value'   => $jobseeker_post->ID,
                    'compare' => '=',
                    'type'    => 'NUMERIC'
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
        
        $interests = $interest_query->posts;
        
        // Debug output - check all interests in database
        $all_interests = get_posts( array(
            'post_type' => 'oso_emp_interest',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ) );
        
        echo '<!-- DEBUG START -->';
        echo '<!-- Jobseeker Post ID: ' . esc_html( $jobseeker_post->ID ) . ' -->';
        echo '<!-- Found ' . count($interests) . ' interests for this jobseeker -->';
        echo '<!-- Total interests in DB: ' . count($all_interests) . ' -->';
        
        if ( ! empty( $all_interests ) ) {
            echo '<!-- All Interest IDs and their jobseeker_id meta: -->';
            foreach ( $all_interests as $int ) {
                $js_id = get_post_meta( $int->ID, '_oso_jobseeker_id', true );
                echo '<!-- Interest ID ' . $int->ID . ' -> Jobseeker ID: ' . $js_id . ' (Type: ' . gettype($js_id) . ') -->';
            }
        }
        echo '<!-- SQL Query: ' . esc_html( $interest_query->request ) . ' -->';
        echo '<!-- DEBUG END -->';

        if ( ! empty( $interests ) ) :
            ?>
            <div class="oso-interests-grid">
                <?php foreach ( $interests as $interest ) :
                    $employer_id = get_post_meta( $interest->ID, '_oso_employer_id', true );
                    $message = $interest->post_content;
                    $interest_date = get_post_meta( $interest->ID, '_oso_interest_date', true );
                    
                    $employer = get_post( $employer_id );
                    
                    if ( ! $employer ) {
                        continue;
                    }
                    
                    $employer_meta = get_post_meta( $employer_id );
                    $employer_logo = ! empty( $employer_meta['_oso_employer_logo'][0] ) ? $employer_meta['_oso_employer_logo'][0] : '';
                    $employer_email = ! empty( $employer_meta['_oso_employer_email'][0] ) ? $employer_meta['_oso_employer_email'][0] : '';
                    $employer_phone = ! empty( $employer_meta['_oso_employer_phone'][0] ) ? $employer_meta['_oso_employer_phone'][0] : '';
                    $employer_state = ! empty( $employer_meta['_oso_employer_state'][0] ) ? $employer_meta['_oso_employer_state'][0] : '';
                    $employer_city = ! empty( $employer_meta['_oso_employer_city'][0] ) ? $employer_meta['_oso_employer_city'][0] : '';
                    
                    $location = array_filter( array( $employer_city, $employer_state ) );
                    $location_str = implode( ', ', $location );
                    ?>
                    <div class="oso-interest-card">
                        <div class="oso-interest-header">
                            <?php if ( $employer_logo ) : ?>
                                <img src="<?php echo esc_url( $employer_logo ); ?>" alt="<?php echo esc_attr( $employer->post_title ); ?>" class="oso-employer-logo-small">
                            <?php else : ?>
                                <div class="oso-employer-logo-placeholder-small">
                                    <span class="dashicons dashicons-building"></span>
                                </div>
                            <?php endif; ?>
                            <div class="oso-interest-title">
                                <h4><?php echo esc_html( $employer->post_title ); ?></h4>
                                <?php if ( $location_str ) : ?>
                                    <p class="oso-interest-location">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php echo esc_html( $location_str ); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="oso-interest-date">
                            <span class="dashicons dashicons-calendar"></span>
                            <?php
                            if ( $interest_date ) {
                                echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $interest_date ) ) );
                            } else {
                                echo esc_html( get_the_date( '', $interest->ID ) );
                            }
                            ?>
                        </div>
                        
                        <?php if ( $message ) : ?>
                            <div class="oso-interest-message">
                                <strong><?php esc_html_e( 'Message:', 'oso-employer-portal' ); ?></strong>
                                <p><?php echo esc_html( $message ); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="oso-interest-contact">
                            <?php if ( $employer_email ) : ?>
                                <a href="mailto:<?php echo esc_attr( $employer_email ); ?>" class="oso-contact-item">
                                    <span class="dashicons dashicons-email"></span>
                                    <?php echo esc_html( $employer_email ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( $employer_phone ) : ?>
                                <a href="tel:<?php echo esc_attr( $employer_phone ); ?>" class="oso-contact-item">
                                    <span class="dashicons dashicons-phone"></span>
                                    <?php echo esc_html( $employer_phone ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oso-interest-actions">
                            <a href="<?php echo esc_url( add_query_arg( 'employer_id', $employer_id, home_url( '/job-portal/employer-profile/' ) ) ); ?>" class="oso-btn oso-btn-secondary oso-btn-small">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php esc_html_e( 'View Profile', 'oso-employer-portal' ); ?>
                            </a>
                            <?php if ( $employer_email ) : ?>
                                <a href="mailto:<?php echo esc_attr( $employer_email ); ?>" class="oso-btn oso-btn-purple-gradient oso-btn-small">
                                    <span class="dashicons dashicons-email-alt"></span>
                                    <?php esc_html_e( 'Reply', 'oso-employer-portal' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="oso-no-interests">
                <span class="dashicons dashicons-heart"></span>
                <p><?php esc_html_e( 'No employers have expressed interest yet.', 'oso-employer-portal' ); ?></p>
                <p><?php esc_html_e( 'Keep your profile updated and check back later!', 'oso-employer-portal' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- All Camps Section -->
    <div class="oso-companies-section">
        <div class="oso-section-header">
            <h3><?php esc_html_e( 'All Camps', 'oso-employer-portal' ); ?></h3>
            <a href="<?php echo esc_url( home_url( '/job-portal/all-jobs/' ) ); ?>" class="oso-btn oso-btn-purple-gradient">
                <span class="dashicons dashicons-search"></span>
                <?php esc_html_e( 'Browse All Jobs', 'oso-employer-portal' ); ?>
            </a>
        </div>
        
        <?php
        // Get all approved employers
        $employers = get_posts( array(
            'post_type'      => 'oso_employer',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'   => '_oso_employer_approved',
                    'value' => '1',
                ),
            ),
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( ! empty( $employers ) ) :
            ?>
            <div class="oso-companies-grid">
                <?php foreach ( $employers as $employer ) :
                    $employer_meta = get_post_meta( $employer->ID );
                    $logo = ! empty( $employer_meta['_oso_employer_logo'][0] ) ? $employer_meta['_oso_employer_logo'][0] : '';
                    $state = ! empty( $employer_meta['_oso_employer_state'][0] ) ? $employer_meta['_oso_employer_state'][0] : '';
                    $email = ! empty( $employer_meta['_oso_employer_email'][0] ) ? $employer_meta['_oso_employer_email'][0] : '';
                    $website = ! empty( $employer_meta['_oso_employer_website'][0] ) ? $employer_meta['_oso_employer_website'][0] : '';
                    
                    // Count active jobs for this employer
                    $active_jobs = get_posts( array(
                        'post_type'      => 'oso_job_posting',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'meta_query'     => array(
                            array(
                                'key'   => '_oso_job_employer_id',
                                'value' => $employer->ID,
                            ),
                        ),
                        'fields'         => 'ids',
                    ) );
                    
                    // Filter out expired jobs
                    $active_count = 0;
                    if ( class_exists( 'OSO_Job_Manager' ) ) {
                        $job_manager = OSO_Job_Manager::instance();
                        foreach ( $active_jobs as $job_id ) {
                            if ( ! $job_manager->is_job_expired( $job_id ) ) {
                                $active_count++;
                            }
                        }
                    } else {
                        $active_count = count( $active_jobs );
                    }
                    ?>
                    <div class="oso-company-card">
                        <div class="oso-company-logo">
                            <?php if ( $logo ) : ?>
                                <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $employer->post_title ); ?>">
                            <?php else : ?>
                                <div class="oso-company-logo-placeholder">
                                    <span class="dashicons dashicons-building"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oso-company-info">
                            <h4><?php echo esc_html( $employer->post_title ); ?></h4>
                            
                            <?php if ( $state ) : ?>
                                <p class="oso-company-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html( $state ); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ( $active_count > 0 ) : ?>
                                <p class="oso-company-jobs">
                                    <span class="dashicons dashicons-portfolio"></span>
                                    <?php
                                    printf(
                                        _n( '%d open position', '%d open positions', $active_count, 'oso-employer-portal' ),
                                        $active_count
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oso-company-actions">
                            <?php if ( $website ) : ?>
                                <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener" class="oso-btn oso-btn-secondary oso-btn-small">
                                    <?php esc_html_e( 'Visit Website', 'oso-employer-portal' ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( $active_count > 0 ) : ?>
                                <a href="<?php echo esc_url( add_query_arg( 'employer', $employer->ID, home_url( '/job-portal/all-jobs/' ) ) ); ?>" class="oso-btn oso-btn-primary oso-btn-small">
                                    <?php esc_html_e( 'View Jobs', 'oso-employer-portal' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="oso-no-companies">
                <span class="dashicons dashicons-building"></span>
                <p><?php esc_html_e( 'No companies available at this time.', 'oso-employer-portal' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Jobseeker Profile Section -->
    <div class="oso-profile-section">
        <div class="oso-section-header">
            <h3><?php esc_html_e( 'My Profile', 'oso-employer-portal' ); ?></h3>
            <a href="<?php echo esc_url( home_url( '/job-portal/edit-jobseeker-profile/' ) ); ?>" class="oso-btn oso-btn-primary">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e( 'Edit Profile', 'oso-employer-portal' ); ?>
            </a>
        </div>
        
        <div class="oso-profile-info-grid">
            <?php
            // Define jobseeker fields to display
            $jobseeker_fields = array(
                '_oso_jobseeker_full_name'          => array( 'label' => 'Full Name', 'required' => true ),
                '_oso_jobseeker_email'              => array( 'label' => 'Email', 'required' => true, 'type' => 'email' ),
                '_oso_jobseeker_phone'              => array( 'label' => 'Phone', 'required' => false ),
                '_oso_jobseeker_location'           => array( 'label' => 'Location', 'required' => false ),
                '_oso_jobseeker_over_18'            => array( 'label' => 'Over 18', 'required' => false ),
                '_oso_jobseeker_availability_start' => array( 'label' => 'Available From', 'required' => false, 'type' => 'date' ),
                '_oso_jobseeker_availability_end'   => array( 'label' => 'Available Until', 'required' => false, 'type' => 'date' ),
                '_oso_jobseeker_why_interested'     => array( 'label' => 'Why Interested in Summer Camp', 'required' => false, 'full_width' => true, 'type' => 'textarea' ),
                '_oso_jobseeker_job_interests'      => array( 'label' => 'Job Interests', 'required' => false, 'full_width' => true, 'type' => 'list' ),
                '_oso_jobseeker_sports_skills'      => array( 'label' => 'Sports Skills', 'required' => false, 'full_width' => true, 'type' => 'list' ),
                '_oso_jobseeker_arts_skills'        => array( 'label' => 'Arts Skills', 'required' => false, 'full_width' => true, 'type' => 'list' ),
                '_oso_jobseeker_adventure_skills'   => array( 'label' => 'Adventure Skills', 'required' => false, 'full_width' => true, 'type' => 'list' ),
                '_oso_jobseeker_waterfront_skills'  => array( 'label' => 'Waterfront Skills', 'required' => false, 'full_width' => true, 'type' => 'list' ),
                '_oso_jobseeker_support_skills'     => array( 'label' => 'Support Services Skills', 'required' => false, 'full_width' => true, 'type' => 'list' ),
                '_oso_jobseeker_certifications'     => array( 'label' => 'Certifications', 'required' => false, 'full_width' => true, 'type' => 'list' ),
            );

            foreach ( $jobseeker_fields as $meta_key => $field_config ) :
                $value = ! empty( $meta[ $meta_key ] ) ? $meta[ $meta_key ] : '';
                
                // Skip empty optional fields
                if ( empty( $value ) && ! $field_config['required'] ) {
                    continue;
                }
                
                $display_value = ! empty( $value ) ? $value : 'Not provided';
                $field_class = ! empty( $field_config['full_width'] ) ? 'oso-profile-field-full' : 'oso-profile-field';
                $field_type = isset( $field_config['type'] ) ? $field_config['type'] : 'text';
                ?>
                <div class="<?php echo esc_attr( $field_class ); ?>">
                    <strong><?php echo esc_html( $field_config['label'] ); ?>:</strong>
                    <?php if ( $field_type === 'email' && ! empty( $value ) ) : ?>
                        <a href="mailto:<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></a>
                    <?php elseif ( $field_type === 'textarea' ) : ?>
                        <p><?php echo wp_kses_post( nl2br( $value ) ); ?></p>
                    <?php elseif ( $field_type === 'list' ) : ?>
                        <span><?php echo esc_html( str_replace( "\n", ', ', $value ) ); ?></span>
                    <?php elseif ( $field_type === 'date' && ! empty( $value ) ) : ?>
                        <span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $value ) ) ); ?></span>
                    <?php else : ?>
                        <span><?php echo esc_html( $display_value ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="oso-dashboard-actions">
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="oso-btn oso-btn-secondary">
            <span class="dashicons dashicons-exit"></span>
            <?php esc_html_e( 'Logout', 'oso-employer-portal' ); ?>
        </a>
    </div>
</div>
