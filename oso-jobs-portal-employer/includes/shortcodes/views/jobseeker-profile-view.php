<?php
/**
 * Single Jobseeker Profile View Template
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get field configurations
$text_fields = class_exists( 'OSO_Jobs_Utilities' ) ? OSO_Jobs_Utilities::get_jobseeker_text_fields() : array();
$checkbox_groups = class_exists( 'OSO_Jobs_Utilities' ) ? OSO_Jobs_Utilities::get_jobseeker_checkbox_groups() : array();

$name = ! empty( $meta['_oso_jobseeker_full_name'] ) ? $meta['_oso_jobseeker_full_name'] : $jobseeker->post_title;
$photo = ! empty( $meta['_oso_jobseeker_photo'] ) ? $meta['_oso_jobseeker_photo'] : '';
$resume = ! empty( $meta['_oso_jobseeker_resume'] ) ? $meta['_oso_jobseeker_resume'] : '';

// Get employer info for header if viewing as employer
$employer_post = null;
$employer_meta = array();
$logo_url = '';
$camp_name = '';
$is_employer = current_user_can( 'oso_employer' ) || current_user_can( 'manage_options' );
$is_jobseeker = current_user_can( 'oso_jobseeker' );

if ( $is_employer ) {
    $employer_post = OSO_Employer_Shortcodes::instance()->get_employer_by_user( get_current_user_id() );
    $employer_meta = $employer_post ? OSO_Employer_Shortcodes::instance()->get_employer_meta( $employer_post->ID ) : array();
    $logo_url = ! empty( $employer_meta['_oso_employer_logo'] ) ? $employer_meta['_oso_employer_logo'] : '';
    $camp_name = ! empty( $employer_meta['_oso_employer_company'] ) ? $employer_meta['_oso_employer_company'] : ( $employer_post ? $employer_post->post_title : '' );
}
?>

<div class="oso-jobseeker-profile-view">
    <?php if ( $is_employer && $employer_post ) : ?>
    <!-- Employer Header -->
    <div class="oso-employer-header">
        <div class="oso-employer-header-left">
            <?php if ( $logo_url ) : ?>
                <div class="oso-employer-logo">
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $camp_name ); ?>" />
                </div>
            <?php endif; ?>
            <div class="oso-employer-info">
                <h1><?php echo esc_html( $camp_name ); ?></h1>
                <p class="oso-employer-subtitle"><?php esc_html_e( 'Jobseeker Profile', 'oso-employer-portal' ); ?></p>
            </div>
        </div>
        <div class="oso-employer-header-right">
            <a href="javascript:history.back()" class="oso-btn oso-btn-secondary">
                <span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Back', 'oso-employer-portal' ); ?>
            </a>
            <a href="<?php echo esc_url( home_url( '/job-portal/employer-profile/' ) ); ?>" class="oso-btn oso-btn-dashboard">
                <span class="dashicons dashicons-dashboard"></span> <?php esc_html_e( 'Dashboard', 'oso-employer-portal' ); ?>
            </a>
        </div>
    </div>
    <?php elseif ( $is_jobseeker ) : ?>
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
                <p class="oso-employer-subtitle"><?php esc_html_e( 'My Profile', 'oso-employer-portal' ); ?></p>
            </div>
        </div>
        <div class="oso-employer-header-right">
            <a href="<?php echo esc_url( home_url( '/job-portal/jobseeker-dashboard/' ) ); ?>" class="oso-btn oso-btn-dashboard">
                <span class="dashicons dashicons-dashboard"></span> <?php esc_html_e( 'Dashboard', 'oso-employer-portal' ); ?>
            </a>
        </div>
    </div>
    <?php else : ?>
    <div class="oso-profile-header">
        <h2><?php esc_html_e( 'Jobseeker Profile', 'oso-employer-portal' ); ?></h2>
    </div>
    <?php endif; ?>

    <div class="oso-profile-main">
        <div class="oso-profile-sidebar">
            <?php if ( $photo ) : ?>
                <div class="oso-profile-photo">
                    <a href="<?php echo esc_url( $photo ); ?>" class="oso-photo-lightbox" data-lightbox="jobseeker-photo">
                        <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                    </a>
                </div>
            <?php else : ?>
                <div class="oso-profile-photo-placeholder">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
            <?php endif; ?>
            
            <h3 class="oso-profile-name"><?php echo esc_html( $name ); ?></h3>
            
            <?php if ( ! empty( $meta['_oso_jobseeker_location'] ) ) : ?>
                <p class="oso-profile-location">
                    <span class="dashicons dashicons-location"></span>
                    <?php echo esc_html( $meta['_oso_jobseeker_location'] ); ?>
                </p>
            <?php endif; ?>
            
            <?php if ( ! empty( $meta['_oso_jobseeker_email'] ) ) : ?>
                <p class="oso-profile-email">
                    <span class="dashicons dashicons-email"></span>
                    <a href="mailto:<?php echo esc_attr( $meta['_oso_jobseeker_email'] ); ?>">
                        <?php echo esc_html( $meta['_oso_jobseeker_email'] ); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <?php if ( $resume ) : ?>
                <div class="oso-profile-resume">
                    <a href="<?php echo esc_url( $resume ); ?>" class="oso-btn oso-btn-secondary" target="_blank" rel="noopener noreferrer">
                        <span class="dashicons dashicons-media-document"></span>
                        <?php esc_html_e( 'Download Resume', 'oso-employer-portal' ); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if ( $is_employer && $employer_post ) : 
                // Check if interest already expressed
                $existing_interest = get_posts( array(
                    'post_type' => 'oso_employer_interest',
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        array(
                            'key' => '_oso_employer_id',
                            'value' => $employer_post->ID,
                        ),
                        array(
                            'key' => '_oso_jobseeker_id',
                            'value' => $jobseeker->ID,
                        ),
                    ),
                ) );
                ?>
                <div class="oso-profile-express-interest">
                    <?php if ( empty( $existing_interest ) ) : ?>
                        <button class="oso-btn oso-btn-purple-gradient oso-express-interest-btn" data-jobseeker-id="<?php echo esc_attr( $jobseeker->ID ); ?>" data-employer-id="<?php echo esc_attr( $employer_post->ID ); ?>">
                            <span class="dashicons dashicons-heart"></span>
                            <?php esc_html_e( 'Express Interest', 'oso-employer-portal' ); ?>
                        </button>
                    <?php else : ?>
                        <div class="oso-interest-sent">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e( 'Interest Sent', 'oso-employer-portal' ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="oso-profile-content">
            <div class="oso-profile-section">
                <h4><?php esc_html_e( 'Availability', 'oso-employer-portal' ); ?></h4>
                <div class="oso-profile-availability">
                    <?php if ( ! empty( $meta['_oso_jobseeker_availability_start'] ) ) : ?>
                        <p><strong><?php esc_html_e( 'Earliest Start:', 'oso-employer-portal' ); ?></strong> <?php echo esc_html( $meta['_oso_jobseeker_availability_start'] ); ?></p>
                    <?php endif; ?>
                    <?php if ( ! empty( $meta['_oso_jobseeker_availability_end'] ) ) : ?>
                        <p><strong><?php esc_html_e( 'Latest End:', 'oso-employer-portal' ); ?></strong> <?php echo esc_html( $meta['_oso_jobseeker_availability_end'] ); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ( ! empty( $jobseeker->post_content ) ) : ?>
                <div class="oso-profile-section">
                    <h4><?php esc_html_e( 'Why Interested in Summer Camp?', 'oso-employer-portal' ); ?></h4>
                    <div class="oso-profile-why">
                        <?php echo wp_kses_post( wpautop( $jobseeker->post_content ) ); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            // Display "Are You Over 18?" as plain text
            if ( ! empty( $meta['_oso_jobseeker_over_18'] ) ) :
                ?>
                <div class="oso-profile-section">
                    <h4><?php esc_html_e( 'Age Verification', 'oso-employer-portal' ); ?></h4>
                    <p><?php echo esc_html( $meta['_oso_jobseeker_over_18'] ); ?></p>
                </div>
            <?php endif; ?>
            
            <?php
            // Display all checkbox groups (excluding over_18)
            foreach ( $checkbox_groups as $key => $config ) :
                // Skip "Are You Over 18?" - it's shown above as plain text
                if ( $key === 'over_18' ) {
                    continue;
                }
                
                $value_raw = ! empty( $meta[ $config['meta'] ] ) ? $meta[ $config['meta'] ] : '';
                if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
                    $values = OSO_Jobs_Utilities::meta_string_to_array( $value_raw );
                } else {
                    $values = array();
                }
                
                if ( empty( $values ) ) {
                    continue;
                }
                ?>
                <div class="oso-profile-section">
                    <h4><?php echo esc_html( $config['label'] ); ?></h4>
                    <div class="oso-profile-skills">
                        <?php foreach ( $values as $value ) : ?>
                            <span class="oso-skill-badge"><?php echo esc_html( $value ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="oso-profile-actions">
                <?php
                // Show edit button only if user is viewing their own profile
                $current_user = wp_get_current_user();
                $is_own_profile = false;
                if ( is_user_logged_in() && in_array( OSO_Jobs_Portal::ROLE_CANDIDATE, (array) $current_user->roles, true ) ) {
                    $profile_email = ! empty( $meta['_oso_jobseeker_email'] ) ? $meta['_oso_jobseeker_email'] : '';
                    if ( $profile_email === $current_user->user_email ) {
                        $is_own_profile = true;
                    }
                }
                
                if ( $is_own_profile ) :
                    // Use direct URL for edit profile page
                    $edit_url = home_url( '/job-portal/edit-jobseeker-profile/' );
                    ?>
                    <a href="<?php echo esc_url( $edit_url ); ?>" class="oso-btn oso-btn-primary">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e( 'Edit Profile', 'oso-employer-portal' ); ?>
                    </a>
                <?php else : ?>
                    <a href="mailto:<?php echo esc_attr( ! empty( $meta['_oso_jobseeker_email'] ) ? $meta['_oso_jobseeker_email'] : '' ); ?>" class="oso-btn oso-btn-primary">
                        <span class="dashicons dashicons-email"></span>
                        <?php esc_html_e( 'Contact Candidate', 'oso-employer-portal' ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( current_user_can( 'oso_employer' ) || current_user_can( 'manage_options' ) ) : ?>
                <a href="javascript:history.back()" class="oso-btn oso-btn-secondary">
                    <?php esc_html_e( 'Back to Search', 'oso-employer-portal' ); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Express Interest Modal -->
<?php if ( $is_employer && $employer_post ) : ?>
<div id="oso-express-interest-modal" class="oso-modal" style="display: none;">
    <div class="oso-modal-overlay"></div>
    <div class="oso-modal-content">
        <div class="oso-modal-header">
            <h3><?php esc_html_e( 'Express Interest in Candidate', 'oso-employer-portal' ); ?></h3>
            <button class="oso-modal-close">&times;</button>
        </div>
        <div class="oso-modal-body">
            <div class="oso-candidate-preview">
                <?php if ( $photo ) : ?>
                    <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                <?php endif; ?>
                <div class="oso-candidate-info">
                    <h4><?php echo esc_html( $name ); ?></h4>
                    <?php if ( ! empty( $meta['_oso_jobseeker_location'] ) ) : ?>
                        <p><span class="dashicons dashicons-location"></span> <?php echo esc_html( $meta['_oso_jobseeker_location'] ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <form id="oso-express-interest-form">
                <input type="hidden" name="jobseeker_id" value="<?php echo esc_attr( $jobseeker->ID ); ?>" />
                <input type="hidden" name="employer_id" value="<?php echo esc_attr( $employer_post->ID ); ?>" />
                
                <div class="oso-form-group">
                    <label for="oso-interest-message"><?php esc_html_e( 'Your Message', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                    <textarea id="oso-interest-message" name="message" rows="8" maxlength="1000" required placeholder="<?php esc_attr_e( 'Tell this candidate why you\'re interested and what opportunity you have...', 'oso-employer-portal' ); ?>"></textarea>
                    <div class="oso-char-count">
                        <span class="oso-char-current">0</span> / <span class="oso-char-max">1000</span>
                    </div>
                </div>
                
                <div class="oso-modal-footer">
                    <button type="button" class="oso-btn oso-btn-secondary oso-modal-cancel"><?php esc_html_e( 'Cancel', 'oso-employer-portal' ); ?></button>
                    <button type="submit" class="oso-btn oso-btn-purple-gradient">
                        <span class="dashicons dashicons-heart"></span>
                        <?php esc_html_e( 'Send Interest', 'oso-employer-portal' ); ?>
                    </button>
                </div>
            </form>
            
            <div class="oso-interest-success" style="display: none;">
                <div class="oso-success-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <h4><?php esc_html_e( 'Interest Sent Successfully!', 'oso-employer-portal' ); ?></h4>
                <p><?php esc_html_e( 'The candidate has been notified via email and will receive your message.', 'oso-employer-portal' ); ?></p>
                <button class="oso-btn oso-btn-purple-gradient oso-modal-close">&times;</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
