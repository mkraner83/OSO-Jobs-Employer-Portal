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
        </div>

        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Company Information', 'oso-employer-portal' ); ?></h3>

            <!-- Company Name -->
            <div class="oso-form-group">
                <label for="company"><?php esc_html_e( 'Company Name', 'oso-employer-portal' ); ?></label>
                <input type="text" id="company" name="company" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_company'] ) ? $meta['_oso_employer_company'] : '' ); ?>" />
            </div>

            <!-- Contact Person -->
            <div class="oso-form-group">
                <label for="contact_person"><?php esc_html_e( 'Contact Person', 'oso-employer-portal' ); ?></label>
                <input type="text" id="contact_person" name="contact_person" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_contact_person'] ) ? $meta['_oso_employer_contact_person'] : '' ); ?>" />
            </div>

            <!-- Job Title -->
            <div class="oso-form-group">
                <label for="job_title"><?php esc_html_e( 'Job Title/Position', 'oso-employer-portal' ); ?></label>
                <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_job_title'] ) ? $meta['_oso_employer_job_title'] : '' ); ?>" />
            </div>

            <!-- Website -->
            <div class="oso-form-group">
                <label for="website"><?php esc_html_e( 'Company Website', 'oso-employer-portal' ); ?></label>
                <input type="url" id="website" name="website" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_website'] ) ? $meta['_oso_employer_website'] : '' ); ?>" placeholder="https://" />
            </div>

            <!-- Company Description -->
            <div class="oso-form-group">
                <label for="description"><?php esc_html_e( 'Company Description', 'oso-employer-portal' ); ?></label>
                <textarea id="description" name="description" rows="6"><?php echo esc_textarea( ! empty( $meta['_oso_employer_description'] ) ? $meta['_oso_employer_description'] : '' ); ?></textarea>
            </div>
        </div>

        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Address', 'oso-employer-portal' ); ?></h3>

            <!-- Street Address -->
            <div class="oso-form-group">
                <label for="address"><?php esc_html_e( 'Street Address', 'oso-employer-portal' ); ?></label>
                <input type="text" id="address" name="address" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_address'] ) ? $meta['_oso_employer_address'] : '' ); ?>" />
            </div>

            <!-- City -->
            <div class="oso-form-group">
                <label for="city"><?php esc_html_e( 'City', 'oso-employer-portal' ); ?></label>
                <input type="text" id="city" name="city" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_city'] ) ? $meta['_oso_employer_city'] : '' ); ?>" />
            </div>

            <!-- State -->
            <div class="oso-form-group">
                <label for="state"><?php esc_html_e( 'State', 'oso-employer-portal' ); ?></label>
                <input type="text" id="state" name="state" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_state'] ) ? $meta['_oso_employer_state'] : '' ); ?>" />
            </div>

            <!-- Zip Code -->
            <div class="oso-form-group">
                <label for="zip"><?php esc_html_e( 'Zip Code', 'oso-employer-portal' ); ?></label>
                <input type="text" id="zip" name="zip" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_zip'] ) ? $meta['_oso_employer_zip'] : '' ); ?>" />
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
