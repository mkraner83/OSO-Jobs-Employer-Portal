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
?>

<div class="oso-jobseeker-profile-view">
    <div class="oso-profile-header">
        <a href="javascript:history.back()" class="oso-back-link">
            &laquo; <?php esc_html_e( 'Back to Search', 'oso-employer-portal' ); ?>
        </a>
        <h2><?php esc_html_e( 'Jobseeker Profile', 'oso-employer-portal' ); ?></h2>
    </div>

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
                    // Find edit profile page
                    $edit_url = '#';
                    $pages = get_posts([
                        'post_type'   => 'page',
                        'post_status' => 'publish',
                        'numberposts' => -1,
                    ]);
                    foreach ( $pages as $page ) {
                        if ( has_shortcode( $page->post_content, 'oso_jobseeker_edit_profile' ) ) {
                            $edit_url = get_permalink( $page->ID );
                            break;
                        }
                    }
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
                <a href="javascript:history.back()" class="oso-btn oso-btn-secondary">
                    <?php esc_html_e( 'Back to Search', 'oso-employer-portal' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>
