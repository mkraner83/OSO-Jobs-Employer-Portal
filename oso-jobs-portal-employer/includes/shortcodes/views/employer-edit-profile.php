<?php
/**
 * Employer Edit Profile Form Template
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$camp_name = ! empty( $meta['_oso_employer_company'] ) ? $meta['_oso_employer_company'] : $employer->post_title;
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
            <h3><?php esc_html_e( 'Camp Information', 'oso-employer-portal' ); ?></h3>
            
            <!-- Camp Name -->
            <div class="oso-form-group">
                <label for="camp_name"><?php esc_html_e( 'Camp Name', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                <input type="text" id="camp_name" name="camp_name" value="<?php echo esc_attr( $camp_name ); ?>" required />
            </div>

            <!-- Email -->
            <div class="oso-form-group">
                <label for="email"><?php esc_html_e( 'Contact Email', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_email'] ) ? $meta['_oso_employer_email'] : '' ); ?>" required />
            </div>

            <!-- Website -->
            <div class="oso-form-group">
                <label for="website"><?php esc_html_e( 'Website / URL', 'oso-employer-portal' ); ?></label>
                <input type="url" id="website" name="website" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_website'] ) ? $meta['_oso_employer_website'] : '' ); ?>" placeholder="https://example.com" />
            </div>

            <!-- Brief Description -->
            <div class="oso-form-group">
                <label for="description"><?php esc_html_e( 'Brief Description', 'oso-employer-portal' ); ?></label>
                <textarea id="description" name="description" rows="6"><?php echo esc_textarea( ! empty( $meta['_oso_employer_description'] ) ? $meta['_oso_employer_description'] : '' ); ?></textarea>
            </div>

            <!-- Type of Camp -->
            <div class="oso-form-group">
                <label for="camp_types"><?php esc_html_e( 'Type of Camp', 'oso-employer-portal' ); ?></label>
                <textarea id="camp_types" name="camp_types" rows="3" placeholder="e.g., Day Camp, Sleepaway Camp, Coed Camp"><?php echo esc_textarea( ! empty( $meta['_oso_employer_camp_types'] ) ? $meta['_oso_employer_camp_types'] : '' ); ?></textarea>
                <p class="oso-field-description"><?php esc_html_e( 'Enter one type per line', 'oso-employer-portal' ); ?></p>
            </div>
        </div>

        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Location', 'oso-employer-portal' ); ?></h3>

            <!-- State -->
            <div class="oso-form-group">
                <label for="state"><?php esc_html_e( 'State', 'oso-employer-portal' ); ?></label>
                <input type="text" id="state" name="state" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_state'] ) ? $meta['_oso_employer_state'] : '' ); ?>" />
            </div>

            <!-- Address -->
            <div class="oso-form-group">
                <label for="address"><?php esc_html_e( 'Address', 'oso-employer-portal' ); ?></label>
                <input type="text" id="address" name="address" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_address'] ) ? $meta['_oso_employer_address'] : '' ); ?>" />
            </div>

            <!-- Closest Major City -->
            <div class="oso-form-group">
                <label for="major_city"><?php esc_html_e( 'Closest Major City (optional)', 'oso-employer-portal' ); ?></label>
                <input type="text" id="major_city" name="major_city" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_major_city'] ) ? $meta['_oso_employer_major_city'] : '' ); ?>" />
            </div>
        </div>

        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Additional Details', 'oso-employer-portal' ); ?></h3>

            <!-- Training Start Date -->
            <div class="oso-form-group">
                <label for="training_start"><?php esc_html_e( 'Start of Staff Training Date', 'oso-employer-portal' ); ?></label>
                <input type="date" id="training_start" name="training_start" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_training_start'] ) ? $meta['_oso_employer_training_start'] : '' ); ?>" />
            </div>

            <!-- Housing Provided -->
            <div class="oso-form-group">
                <label for="housing"><?php esc_html_e( 'Housing Provided', 'oso-employer-portal' ); ?></label>
                <select id="housing" name="housing">
                    <option value="">Select...</option>
                    <option value="Yes" <?php selected( ! empty( $meta['_oso_employer_housing'] ) ? $meta['_oso_employer_housing'] : '', 'Yes' ); ?>>Yes</option>
                    <option value="No" <?php selected( ! empty( $meta['_oso_employer_housing'] ) ? $meta['_oso_employer_housing'] : '', 'No' ); ?>>No</option>
                </select>
            </div>

            <!-- Social Media Links -->
            <div class="oso-form-group">
                <label for="social_links"><?php esc_html_e( 'Social Media Links (optional)', 'oso-employer-portal' ); ?></label>
                <textarea id="social_links" name="social_links" rows="4" placeholder="Instagram, Facebook, Twitter, etc."><?php echo esc_textarea( ! empty( $meta['_oso_employer_social_links'] ) ? $meta['_oso_employer_social_links'] : '' ); ?></textarea>
            </div>

            <!-- Subscription Type (read-only) -->
            <div class="oso-form-group">
                <label for="subscription_type"><?php esc_html_e( 'Subscription Type', 'oso-employer-portal' ); ?></label>
                <input type="text" id="subscription_type" name="subscription_type" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_subscription_type'] ) ? $meta['_oso_employer_subscription_type'] : '' ); ?>" readonly />
                <p class="oso-field-description"><?php esc_html_e( 'This field cannot be edited here', 'oso-employer-portal' ); ?></p>
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
