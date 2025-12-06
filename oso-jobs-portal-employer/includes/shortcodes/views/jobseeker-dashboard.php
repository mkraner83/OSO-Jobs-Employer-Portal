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
?>
<div class="oso-jobseeker-dashboard">
    
    <!-- Quick Action Buttons -->
    <div class="oso-quick-actions-grid">
        <a href="<?php echo esc_url( home_url( '/job-portal/all-jobs/' ) ); ?>" class="oso-quick-action-card oso-purple-card">
            <span class="dashicons dashicons-portfolio"></span>
            <h3><?php esc_html_e( 'Browse Jobs', 'oso-employer-portal' ); ?></h3>
            <p><?php esc_html_e( 'View all available job postings', 'oso-employer-portal' ); ?></p>
        </a>
        
        <a href="<?php echo esc_url( home_url( '/job-portal/jobseeker-edit-profile/' ) ); ?>" class="oso-quick-action-card oso-teal-card">
            <span class="dashicons dashicons-edit"></span>
            <h3><?php esc_html_e( 'Edit Profile', 'oso-employer-portal' ); ?></h3>
            <p><?php esc_html_e( 'Update your information and resume', 'oso-employer-portal' ); ?></p>
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
                        
                        <div class="oso-application-status">
                            <span class="oso-status-badge <?php echo esc_attr( $status_class ); ?>">
                                <?php echo esc_html( $status_label ); ?>
                            </span>
                        </div>
                        
                        <div class="oso-application-actions">
                            <a href="<?php echo esc_url( add_query_arg( 'job_id', $job_id, home_url( '/job-portal/job-details/' ) ) ); ?>" class="oso-btn oso-btn-secondary oso-btn-small">
                                <?php esc_html_e( 'View Job', 'oso-employer-portal' ); ?>
                            </a>
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

    <!-- Companies/Camps Section -->
    <div class="oso-companies-section">
        <h3><?php esc_html_e( 'Companies & Camps', 'oso-employer-portal' ); ?></h3>
        
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

    <div class="oso-dashboard-actions">
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="oso-btn oso-btn-secondary">
            <span class="dashicons dashicons-exit"></span>
            <?php esc_html_e( 'Logout', 'oso-employer-portal' ); ?>
        </a>
    </div>
</div>
