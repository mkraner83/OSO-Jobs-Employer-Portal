<?php
/**
 * Employer Edit Profile Form Template
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$name = ! empty( $meta['_oso_employer_full_name'] ) ? $meta['_oso_employer_full_name'] : $employer->post_title;
?>

<div class="oso-employer-edit-profile">
    <div class="oso-profile-header">
        <a href="<?php echo esc_url( home_url( '/job-portal/employer-profile/' ) ); ?>" class="oso-back-link">
            &laquo; <?php esc_html_e( 'Back to Profile', 'oso-employer-portal' ); ?>
        </a>
        <h2><?php esc_html_e( 'Edit My Profile', 'oso-employer-portal' ); ?></h2>
    </div>

    <form id="oso-edit-employer-profile-form" class="oso-edit-profile-form" data-employer-id="<?php echo esc_attr( $employer->ID ); ?>">
        <?php wp_nonce_field( 'oso_update_employer_profile', 'oso_employer_profile_nonce' ); ?>
        
        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Basic Information', 'oso-employer-portal' ); ?></h3>
            
            <!-- Full Name -->
            <div class="oso-form-group">
                <label for="full_name"><?php esc_html_e( 'Full Name', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo esc_attr( $name ); ?>" required />
            </div>

            <!-- Email -->
            <div class="oso-form-group">
                <label for="email"><?php esc_html_e( 'Email Address', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_email'] ) ? $meta['_oso_employer_email'] : '' ); ?>" required />
            </div>

            <!-- Phone -->
            <div class="oso-form-group">
                <label for="phone"><?php esc_html_e( 'Phone Number', 'oso-employer-portal' ); ?></label>
                <input type="tel" id="phone" name="phone" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_phone'] ) ? $meta['_oso_employer_phone'] : '' ); ?>" />
            </div>

            <!-- Company -->
            <div class="oso-form-group">
                <label for="company"><?php esc_html_e( 'Company Name', 'oso-employer-portal' ); ?></label>
                <input type="text" id="company" name="company" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_company'] ) ? $meta['_oso_employer_company'] : '' ); ?>" />
            </div>
        </div>

        <div class="oso-form-actions">
            <button type="submit" class="oso-btn oso-btn-primary">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save Changes', 'oso-employer-portal' ); ?>
            </button>
            <a href="<?php echo esc_url( home_url( '/job-portal/employer-profile/' ) ); ?>" class="oso-btn oso-btn-secondary">
                <?php esc_html_e( 'Cancel', 'oso-employer-portal' ); ?>
            </a>
        </div>

        <div id="oso-employer-form-message" class="oso-form-message" style="display: none;"></div>
    </form>
</div>
