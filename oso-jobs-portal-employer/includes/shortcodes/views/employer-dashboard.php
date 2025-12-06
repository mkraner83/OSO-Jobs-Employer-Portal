<?php
/**
 * Employer Dashboard Template
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! $is_logged_in ) :
    ?>
    <div class="oso-employer-dashboard oso-login-required">
        <div class="oso-login-box">
            <div class="oso-login-header">
                <h3><?php esc_html_e( 'Employer Login', 'oso-employer-portal' ); ?></h3>
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
<div class="oso-employer-dashboard">
    <?php 
    // Check if employer is approved (admins always see the button)
    $is_approved = current_user_can( 'manage_options' ) || ( isset( $meta['_oso_employer_approved'] ) && $meta['_oso_employer_approved'] === '1' );
    
    // Check if subscription is expired
    $is_expired = false;
    if ( ! current_user_can( 'manage_options' ) && ! empty( $meta['_oso_employer_subscription_ends'] ) ) {
        $expiration_date = strtotime( $meta['_oso_employer_subscription_ends'] );
        if ( $expiration_date && $expiration_date < time() ) {
            $is_expired = true;
        }
    }
    ?>
    
    <?php if ( $is_approved && ! $is_expired ) : ?>
        <!-- Full-Width Quick Link Banner -->
        <div class="oso-quick-link-banner">
            <a href="<?php echo esc_url( home_url( '/job-portal/browse-jobseekers/' ) ); ?>" class="oso-quick-link">
                <span class="dashicons dashicons-groups"></span>
                <span><?php esc_html_e( 'Browse Jobseekers', 'oso-employer-portal' ); ?></span>
            </a>
        </div>
    <?php elseif ( $is_expired ) : ?>
        <!-- Subscription Expired Message -->
        <div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; margin: 0 0 30px 0;">
            <p style="margin: 0; color: #721c24;"><strong><?php esc_html_e( 'Subscription Expired', 'oso-employer-portal' ); ?></strong></p>
            <p style="margin: 10px 0 0 0; color: #721c24;">
                <?php 
                printf( 
                    esc_html__( 'Your subscription expired on %s. Please renew your subscription to continue browsing jobseeker profiles.', 'oso-employer-portal' ),
                    '<strong>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $meta['_oso_employer_subscription_ends'] ) ) ) . '</strong>'
                );
                ?>
            </p>
        </div>
    <?php else : ?>
        <!-- Pending Approval Message -->
        <div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; margin: 0 0 30px 0;">
            <p style="margin: 0; color: #856404;"><strong><?php esc_html_e( 'Account Pending Approval', 'oso-employer-portal' ); ?></strong></p>
            <p style="margin: 10px 0 0 0; color: #856404;"><?php esc_html_e( 'Your employer account is currently pending approval. You will be able to browse jobseekers once an administrator approves your account.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>

    <!-- Job Postings Section -->
    <div class="oso-employer-jobs">
        <div class="oso-jobs-header">
            <h3><?php esc_html_e( 'Your Job Postings', 'oso-employer-portal' ); ?></h3>
            <?php
            // Get job manager and employer jobs
            $job_manager = class_exists( 'OSO_Job_Manager' ) ? OSO_Job_Manager::instance() : null;
            $jobs = $job_manager ? $job_manager->get_employer_jobs( $employer_post->ID ) : array();
            $can_post = $job_manager ? $job_manager->can_post_job( $employer_post->ID ) : false;
            $job_limit = $job_manager ? $job_manager->get_job_limit( $employer_post->ID ) : 5;
            
            // Count active jobs
            $active_count = 0;
            foreach ( $jobs as $job ) {
                if ( $job->post_status === 'publish' && ! $job_manager->is_job_expired( $job->ID ) ) {
                    $active_count++;
                }
            }
            ?>
            <div class="oso-jobs-actions">
                <?php if ( $can_post ) : ?>
                    <a href="<?php echo esc_url( home_url( '/job-portal/add-job/' ) ); ?>" class="oso-btn oso-btn-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e( 'Add New Job', 'oso-employer-portal' ); ?>
                    </a>
                <?php else : ?>
                    <span class="oso-job-limit-reached">
                        <?php 
                        printf( 
                            esc_html__( 'Job limit reached (%1$d / %2$s)', 'oso-employer-portal' ),
                            $active_count,
                            $job_limit == 0 ? '∞' : $job_limit
                        );
                        ?>
                    </span>
                <?php endif; ?>
                <span class="oso-job-count">
                    <?php 
                    printf( 
                        esc_html__( '%1$d / %2$s jobs posted', 'oso-employer-portal' ),
                        $active_count,
                        $job_limit == 0 ? '∞' : $job_limit
                    );
                    ?>
                </span>
            </div>
        </div>
        
        <?php if ( ! empty( $jobs ) ) : ?>
            <div class="oso-jobs-grid">
                <?php foreach ( $jobs as $job ) : 
                    $job_meta = $job_manager->get_job_meta( $job->ID );
                    $is_expired = $job_manager->is_job_expired( $job->ID );
                    $job_types = ! empty( $job_meta['_oso_job_type'] ) ? explode( "\n", $job_meta['_oso_job_type'] ) : array();
                    ?>
                    <div class="oso-job-card <?php echo $is_expired ? 'oso-job-expired' : ''; ?>">
                        <div class="oso-job-card-header">
                            <h4><?php echo esc_html( $job->post_title ); ?></h4>
                            <?php if ( $is_expired ) : ?>
                                <span class="oso-job-status oso-status-expired"><?php esc_html_e( 'Expired', 'oso-employer-portal' ); ?></span>
                            <?php else : ?>
                                <span class="oso-job-status oso-status-active"><?php esc_html_e( 'Active', 'oso-employer-portal' ); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ( ! empty( $job_types ) ) : ?>
                            <div class="oso-job-types">
                                <?php foreach ( array_slice( $job_types, 0, 3 ) as $type ) : ?>
                                    <span class="oso-job-type-badge"><?php echo esc_html( trim( $type ) ); ?></span>
                                <?php endforeach; ?>
                                <?php if ( count( $job_types ) > 3 ) : ?>
                                    <span class="oso-job-type-badge">+<?php echo count( $job_types ) - 3; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="oso-job-meta">
                            <?php if ( ! empty( $job_meta['_oso_job_start_date'] ) && ! empty( $job_meta['_oso_job_end_date'] ) ) : ?>
                                <span class="oso-job-dates">
                                    <span class="dashicons dashicons-calendar-alt"></span>
                                    <?php 
                                    echo esc_html( date_i18n( 'M j', strtotime( $job_meta['_oso_job_start_date'] ) ) );
                                    echo ' - ';
                                    echo esc_html( date_i18n( 'M j, Y', strtotime( $job_meta['_oso_job_end_date'] ) ) );
                                    ?>
                                </span>
                            <?php endif; ?>
                            <?php if ( ! empty( $job_meta['_oso_job_positions'] ) ) : ?>
                                <span class="oso-job-positions">
                                    <span class="dashicons dashicons-groups"></span>
                                    <?php 
                                    printf( 
                                        esc_html( _n( '%d position', '%d positions', $job_meta['_oso_job_positions'], 'oso-employer-portal' ) ),
                                        $job_meta['_oso_job_positions']
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oso-job-actions">
                            <a href="<?php echo esc_url( home_url( '/job-portal/add-job/?job_id=' . $job->ID ) ); ?>" class="oso-btn oso-btn-sm oso-btn-secondary">
                                <span class="dashicons dashicons-edit"></span>
                                <?php esc_html_e( 'Edit', 'oso-employer-portal' ); ?>
                            </a>
                            <button type="button" class="oso-btn oso-btn-sm oso-btn-danger oso-delete-job" data-job-id="<?php echo esc_attr( $job->ID ); ?>" data-job-title="<?php echo esc_attr( $job->post_title ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e( 'Delete', 'oso-employer-portal' ); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="oso-no-jobs">
                <p><?php esc_html_e( 'You have not posted any jobs yet.', 'oso-employer-portal' ); ?></p>
                <?php if ( $can_post ) : ?>
                    <a href="<?php echo esc_url( home_url( '/job-portal/add-job/' ) ); ?>" class="oso-btn oso-btn-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e( 'Post Your First Job', 'oso-employer-portal' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Job Applications Section -->
    <div class="oso-job-applications-section">
        <h3><?php esc_html_e( 'Job Applications', 'oso-employer-portal' ); ?></h3>
        
        <?php
        // Get all applications for this employer's jobs
        $applications = get_posts( array(
            'post_type'      => 'oso_job_application',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_oso_application_employer_id',
            'meta_value'     => $employer_id,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        if ( ! empty( $applications ) ) :
            // Count by status
            $pending_count = 0;
            $approved_count = 0;
            $rejected_count = 0;
            foreach ( $applications as $app ) {
                $status = get_post_meta( $app->ID, '_oso_application_status', true );
                if ( $status === 'approved' ) {
                    $approved_count++;
                } elseif ( $status === 'rejected' ) {
                    $rejected_count++;
                } else {
                    $pending_count++;
                }
            }
            ?>
            
            <div class="oso-applications-stats">
                <div class="oso-stat-card">
                    <span class="oso-stat-number"><?php echo esc_html( $pending_count ); ?></span>
                    <span class="oso-stat-label"><?php esc_html_e( 'Pending', 'oso-employer-portal' ); ?></span>
                </div>
                <div class="oso-stat-card">
                    <span class="oso-stat-number"><?php echo esc_html( $approved_count ); ?></span>
                    <span class="oso-stat-label"><?php esc_html_e( 'Approved', 'oso-employer-portal' ); ?></span>
                </div>
                <div class="oso-stat-card">
                    <span class="oso-stat-number"><?php echo esc_html( $rejected_count ); ?></span>
                    <span class="oso-stat-label"><?php esc_html_e( 'Rejected', 'oso-employer-portal' ); ?></span>
                </div>
                <div class="oso-stat-card">
                    <span class="oso-stat-number"><?php echo esc_html( count( $applications ) ); ?></span>
                    <span class="oso-stat-label"><?php esc_html_e( 'Total', 'oso-employer-portal' ); ?></span>
                </div>
            </div>

            <div class="oso-applications-table-wrapper">
                <table class="oso-applications-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Applicant', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Job Position', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Applied', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'oso-employer-portal' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $applications as $application ) : 
                            $app_id = $application->ID;
                            $job_id = get_post_meta( $app_id, '_oso_application_job_id', true );
                            $jobseeker_id = get_post_meta( $app_id, '_oso_application_jobseeker_id', true );
                            $status = get_post_meta( $app_id, '_oso_application_status', true );
                            $app_date = get_post_meta( $app_id, '_oso_application_date', true );
                            
                            $job_title = get_the_title( $job_id );
                            $jobseeker_name = get_the_title( $jobseeker_id );
                            $cover_letter = $application->post_content;
                            
                            // Get jobseeker profile URL
                            $jobseeker_url = add_query_arg( 'jobseeker_id', $jobseeker_id, home_url( '/job-portal/jobseeker-profile/' ) );
                            
                            // Status badge class
                            $status_class = 'oso-status-' . esc_attr( $status );
                            $status_text = ucfirst( $status );
                            ?>
                            <tr data-application-id="<?php echo esc_attr( $app_id ); ?>">
                                <td>
                                    <a href="<?php echo esc_url( $jobseeker_url ); ?>" target="_blank" class="oso-applicant-link">
                                        <?php echo esc_html( $jobseeker_name ); ?>
                                        <span class="dashicons dashicons-external"></span>
                                    </a>
                                </td>
                                <td><?php echo esc_html( $job_title ); ?></td>
                                <td><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $app_date ) ) ); ?></td>
                                <td>
                                    <span class="oso-application-status <?php echo esc_attr( $status_class ); ?>">
                                        <?php echo esc_html( $status_text ); ?>
                                    </span>
                                </td>
                                <td class="oso-actions-cell">
                                    <button 
                                        class="oso-btn oso-btn-small oso-view-cover-letter" 
                                        data-application-id="<?php echo esc_attr( $app_id ); ?>"
                                        data-applicant="<?php echo esc_attr( $jobseeker_name ); ?>"
                                        data-job="<?php echo esc_attr( $job_title ); ?>"
                                        data-cover-letter="<?php echo esc_attr( wp_kses_post( $cover_letter ) ); ?>"
                                    >
                                        <span class="dashicons dashicons-visibility"></span>
                                        <?php esc_html_e( 'View', 'oso-employer-portal' ); ?>
                                    </button>
                                    
                                    <?php if ( $status === 'pending' ) : ?>
                                        <button 
                                            class="oso-btn oso-btn-small oso-btn-success oso-approve-application" 
                                            data-application-id="<?php echo esc_attr( $app_id ); ?>"
                                        >
                                            <span class="dashicons dashicons-yes"></span>
                                            <?php esc_html_e( 'Approve', 'oso-employer-portal' ); ?>
                                        </button>
                                        <button 
                                            class="oso-btn oso-btn-small oso-btn-danger oso-reject-application" 
                                            data-application-id="<?php echo esc_attr( $app_id ); ?>"
                                        >
                                            <span class="dashicons dashicons-no"></span>
                                            <?php esc_html_e( 'Reject', 'oso-employer-portal' ); ?>
                                        </button>
                                    <?php elseif ( $status === 'approved' ) : ?>
                                        <button 
                                            class="oso-btn oso-btn-small oso-btn-secondary oso-reset-application" 
                                            data-application-id="<?php echo esc_attr( $app_id ); ?>"
                                        >
                                            <?php esc_html_e( 'Reset', 'oso-employer-portal' ); ?>
                                        </button>
                                    <?php elseif ( $status === 'rejected' ) : ?>
                                        <button 
                                            class="oso-btn oso-btn-small oso-btn-secondary oso-reset-application" 
                                            data-application-id="<?php echo esc_attr( $app_id ); ?>"
                                        >
                                            <?php esc_html_e( 'Reset', 'oso-employer-portal' ); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="oso-no-applications">
                <p><?php esc_html_e( 'No applications received yet.', 'oso-employer-portal' ); ?></p>
                <p><?php esc_html_e( 'Applications will appear here when jobseekers apply for your posted jobs.', 'oso-employer-portal' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cover Letter Modal -->
    <div id="oso-cover-letter-modal" class="oso-modal" style="display: none;">
        <div class="oso-modal-overlay"></div>
        <div class="oso-modal-content">
            <button class="oso-modal-close">&times;</button>
            <h3 id="oso-modal-title"></h3>
            <div id="oso-modal-body"></div>
        </div>
    </div>

    <!-- Employer Profile Information -->
    <div class="oso-employer-profile">
        <div class="oso-profile-header-row">
            <h3><?php esc_html_e( 'Your Profile', 'oso-employer-portal' ); ?></h3>
            <a href="<?php echo esc_url( home_url( '/job-portal/edit-employer-profile/' ) ); ?>" class="oso-btn oso-btn-primary">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e( 'Edit Profile', 'oso-employer-portal' ); ?>
            </a>
        </div>
        
        <div class="oso-profile-info-grid">
            <?php
            // Define all employer fields to display (matching WPForms)
            $employer_fields = array(
                '_oso_employer_company' => array( 'label' => 'Camp Name', 'required' => true ),
                '_oso_employer_email' => array( 'label' => 'Contact Email', 'required' => true ),
                '_oso_employer_website' => array( 'label' => 'Website', 'required' => false, 'type' => 'url' ),
                '_oso_employer_state' => array( 'label' => 'State', 'required' => false ),
                '_oso_employer_address' => array( 'label' => 'Address', 'required' => false ),
                '_oso_employer_major_city' => array( 'label' => 'Closest Major City', 'required' => false ),
                '_oso_employer_training_start' => array( 'label' => 'Start of Staff Training', 'required' => false, 'type' => 'date' ),
                '_oso_employer_housing' => array( 'label' => 'Housing Provided', 'required' => false ),
                '_oso_employer_subscription_type' => array( 'label' => 'Subscription Type', 'required' => false ),
                '_oso_employer_subscription_ends' => array( 'label' => 'Subscription Ends', 'required' => false, 'type' => 'expiration_date' ),
                '_oso_employer_camp_types' => array( 'label' => 'Type of Camp', 'required' => false, 'type' => 'list' ),
                '_oso_employer_description' => array( 'label' => 'Brief Description', 'required' => false, 'full_width' => true, 'type' => 'textarea' ),
                '_oso_employer_social_links' => array( 'label' => 'Social Media Links', 'required' => false, 'full_width' => true, 'type' => 'textarea' ),
            );

            foreach ( $employer_fields as $meta_key => $field_config ) :
                $value = ! empty( $meta[ $meta_key ] ) ? $meta[ $meta_key ] : '';
                
                // Skip empty optional fields
                if ( empty( $value ) && ! $field_config['required'] ) {
                    continue;
                }
                
                $display_value = ! empty( $value ) ? $value : 'Not provided';
                $field_class = ! empty( $field_config['full_width'] ) ? 'oso-profile-field-full' : 'oso-profile-field';
                $field_type = isset( $field_config['type'] ) ? $field_config['type'] : 'text';
                
                // Add special class for subscription fields
                if ( $meta_key === '_oso_employer_subscription_type' || $meta_key === '_oso_employer_subscription_ends' ) {
                    $field_class .= ' oso-subscription-field';
                }
                ?>
                <div class="<?php echo esc_attr( $field_class ); ?>">
                    <strong><?php echo esc_html( $field_config['label'] ); ?>:</strong>
                    <?php if ( $field_type === 'url' && ! empty( $value ) ) : ?>
                        <a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $value ); ?></a>
                    <?php elseif ( $field_type === 'textarea' ) : ?>
                        <p><?php echo wp_kses_post( nl2br( $value ) ); ?></p>
                    <?php elseif ( $field_type === 'list' ) : ?>
                        <span><?php echo esc_html( str_replace( "\n", ', ', $value ) ); ?></span>
                    <?php elseif ( $field_type === 'expiration_date' && ! empty( $value ) ) : ?>
                        <?php
                        $formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $value ) );
                        $expiration_timestamp = strtotime( $value );
                        $is_expired_check = $expiration_timestamp && $expiration_timestamp < time();
                        if ( $is_expired_check && ! current_user_can( 'manage_options' ) ) {
                            echo '<span style="color: #d9534f; font-weight: bold;">' . esc_html( $formatted_date ) . ' (' . esc_html__( 'Expired', 'oso-employer-portal' ) . ')</span>';
                        } else {
                            echo '<span>' . esc_html( $formatted_date ) . '</span>';
                        }
                        ?>
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
