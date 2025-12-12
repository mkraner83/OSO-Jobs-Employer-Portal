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
$logo_url = ! empty( $meta['_oso_employer_logo'] ) ? $meta['_oso_employer_logo'] : '';
?>

<div class="oso-employer-edit-profile">
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
                <p class="oso-employer-subtitle"><?php esc_html_e( 'Edit Employer Profile', 'oso-employer-portal' ); ?></p>
            </div>
        </div>
        <div class="oso-employer-header-right">
            <a href="<?php echo esc_url( home_url( '/job-portal/employer-profile/' ) ); ?>" class="oso-btn oso-btn-dashboard">
                <span class="dashicons dashicons-dashboard"></span> <?php esc_html_e( 'Dashboard', 'oso-employer-portal' ); ?>
            </a>
        </div>
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
            <h3><?php esc_html_e( 'Images', 'oso-employer-portal' ); ?></h3>

            <!-- Logo Upload -->
            <div class="oso-form-group">
                <label for="logo"><?php esc_html_e( 'Camp Logo', 'oso-employer-portal' ); ?></label>
                <?php if ( ! empty( $meta['_oso_employer_logo'] ) ) : ?>
                    <div class="oso-current-file">
                        <img src="<?php echo esc_url( $meta['_oso_employer_logo'] ); ?>" alt="Current logo" style="max-width: 200px; margin-bottom: 10px; border-radius: 4px;" />
                    </div>
                <?php endif; ?>
                <input type="file" id="logo" name="logo" accept=".png,.jpg,.jpeg" />
                <input type="hidden" id="logo_url" name="logo_url" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_logo'] ) ? $meta['_oso_employer_logo'] : '' ); ?>" />
                <p class="oso-field-description"><?php esc_html_e( 'Upload your camp logo (PNG recommended for transparent backgrounds, JPG/JPEG also supported - max 6MB)', 'oso-employer-portal' ); ?></p>
            </div>

            <!-- Photos Upload -->
            <div class="oso-form-group">
                <label for="photos"><?php esc_html_e( 'Camp Photos', 'oso-employer-portal' ); ?></label>
                <?php
                $photos = ! empty( $meta['_oso_employer_photos'] ) ? $meta['_oso_employer_photos'] : '';
                $photos_array = ! empty( $photos ) ? explode( "\n", $photos ) : array();
                if ( ! empty( $photos_array ) ) :
                ?>
                    <div class="oso-current-photos" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-bottom: 10px;">
                        <?php foreach ( $photos_array as $photo_url ) : ?>
                            <?php if ( ! empty( trim( $photo_url ) ) ) : ?>
                                <div class="oso-photo-item" style="position: relative;">
                                    <img src="<?php echo esc_url( trim( $photo_url ) ); ?>" alt="Camp photo" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px;" />
                                    <button type="button" class="oso-remove-photo" data-url="<?php echo esc_attr( trim( $photo_url ) ); ?>" style="position: absolute; top: 5px; right: 5px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-size: 16px; line-height: 1;">&times;</button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <input type="file" id="photos" name="photos[]" accept=".jpg,.jpeg,.webp" multiple />
                <input type="hidden" id="photos_urls" name="photos_urls" value="<?php echo esc_attr( $photos ); ?>" />
                <p class="oso-field-description"><?php esc_html_e( 'Upload up to 6 photos (JPG, JPEG, WEBP - 20MB total max)', 'oso-employer-portal' ); ?></p>
            </div>
        </div>

        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Additional Details', 'oso-employer-portal' ); ?></h3>

            <!-- Training Start Date -->
            <div class="oso-form-group">
                <label for="training_start"><?php esc_html_e( 'Start of Staff Training Date', 'oso-employer-portal' ); ?></label>
                <?php
                // Convert MM/DD/YYYY to YYYY-MM-DD for HTML date input
                $training_date = ! empty( $meta['_oso_employer_training_start'] ) ? $meta['_oso_employer_training_start'] : '';
                if ( ! empty( $training_date ) ) {
                    $date_obj = DateTime::createFromFormat( 'm/d/Y', $training_date );
                    if ( $date_obj ) {
                        $training_date = $date_obj->format( 'Y-m-d' );
                    }
                }
                ?>
                <input type="date" id="training_start" name="training_start" value="<?php echo esc_attr( $training_date ); ?>" />
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

            <!-- Subscription Type (view-only) -->
            <div class="oso-form-group">
                <label for="subscription_type"><?php esc_html_e( 'Subscription Type', 'oso-employer-portal' ); ?></label>
                <input type="text" id="subscription_type" name="subscription_type" value="<?php echo esc_attr( ! empty( $meta['_oso_employer_subscription_type'] ) ? $meta['_oso_employer_subscription_type'] : '' ); ?>" disabled style="background: #f5f5f5; cursor: not-allowed;" />
                <p class="oso-field-description"><?php esc_html_e( 'Contact support to change your subscription', 'oso-employer-portal' ); ?></p>
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
            <button type="button" class="oso-btn oso-btn-danger oso-delete-employer-profile" data-employer-id="<?php echo esc_attr( $employer->ID ); ?>" style="margin-left: auto;">
                <span class="dashicons dashicons-trash"></span>
                <?php esc_html_e( 'Delete Profile', 'oso-employer-portal' ); ?>
            </button>
        </div>

        <div id="oso-employer-form-message" class="oso-form-message" style="display: none;"></div>
    </form>
</div>
