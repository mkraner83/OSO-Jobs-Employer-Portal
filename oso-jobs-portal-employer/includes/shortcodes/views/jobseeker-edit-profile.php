<?php
/**
 * Jobseeker Edit Profile Form Template
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
$profile_url = home_url( '/job-portal/jobseeker-dashboard/' );

// Debug: Uncomment to see what's in meta
// echo '<pre>'; print_r($meta); echo '</pre>';
?>

<div class="oso-jobseeker-edit-profile">
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
                <p class="oso-employer-subtitle"><?php esc_html_e( 'Edit Jobseeker Profile', 'oso-employer-portal' ); ?></p>
            </div>
        </div>
        <div class="oso-employer-header-right">
            <a href="<?php echo esc_url( $profile_url ); ?>" class="oso-btn oso-btn-dashboard">
                <span class="dashicons dashicons-dashboard"></span> <?php esc_html_e( 'Dashboard', 'oso-employer-portal' ); ?>
            </a>
        </div>
    </div>

    <form id="oso-edit-profile-form" class="oso-edit-profile-form" data-jobseeker-id="<?php echo esc_attr( $jobseeker->ID ); ?>">
        <?php wp_nonce_field( 'oso_update_jobseeker_profile', 'oso_profile_nonce' ); ?>
        
        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Basic Information', 'oso-employer-portal' ); ?></h3>
            
            <!-- Full Name -->
            <div class="oso-form-group">
                <label for="full_name"><?php echo esc_html( $text_fields['full_name']['label'] ); ?> <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo esc_attr( $name ); ?>" required />
            </div>

            <!-- Email -->
            <div class="oso-form-group">
                <label for="email"><?php echo esc_html( $text_fields['email']['label'] ); ?> <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo esc_attr( ! empty( $meta['_oso_jobseeker_email'] ) ? $meta['_oso_jobseeker_email'] : '' ); ?>" required />
            </div>

            <!-- Location -->
            <div class="oso-form-group">
                <label for="location"><?php echo esc_html( $text_fields['location']['label'] ); ?></label>
                <select id="location" name="location">
                    <option value=""><?php esc_html_e( 'Select State', 'oso-employer-portal' ); ?></option>
                    <?php 
                    $current_location = ! empty( $meta['_oso_jobseeker_location'] ) ? $meta['_oso_jobseeker_location'] : '';
                    foreach ( $text_fields['location']['options'] as $state_code => $state_name ) : 
                    ?>
                        <option value="<?php echo esc_attr( $state_name ); ?>" <?php selected( $current_location, $state_name ); ?>>
                            <?php echo esc_html( $state_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Photo Upload -->
            <div class="oso-form-group">
                <label for="photo"><?php echo esc_html( $text_fields['photo_url']['label'] ); ?></label>
                <?php if ( ! empty( $meta['_oso_jobseeker_photo'] ) ) : ?>
                    <div class="oso-current-file">
                        <img src="<?php echo esc_url( $meta['_oso_jobseeker_photo'] ); ?>" alt="Current photo" style="max-width: 150px; margin-bottom: 10px;" />
                    </div>
                <?php endif; ?>
                <input type="file" id="photo" name="photo" accept="image/*" />
                <input type="hidden" id="photo_url" name="photo_url" value="<?php echo esc_attr( ! empty( $meta['_oso_jobseeker_photo'] ) ? $meta['_oso_jobseeker_photo'] : '' ); ?>" />
                <p class="oso-field-description"><?php esc_html_e( 'Upload a new photo to replace the current one', 'oso-employer-portal' ); ?></p>
            </div>

            <!-- Resume Upload -->
            <div class="oso-form-group">
                <label for="resume"><?php echo esc_html( $text_fields['resume_url']['label'] ); ?></label>
                <?php if ( ! empty( $meta['_oso_jobseeker_resume'] ) ) : ?>
                    <div class="oso-current-file">
                        <a href="<?php echo esc_url( $meta['_oso_jobseeker_resume'] ); ?>" target="_blank">
                            <span class="dashicons dashicons-media-document"></span> <?php esc_html_e( 'Current Resume', 'oso-employer-portal' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" />
                <input type="hidden" id="resume_url" name="resume_url" value="<?php echo esc_attr( ! empty( $meta['_oso_jobseeker_resume'] ) ? $meta['_oso_jobseeker_resume'] : '' ); ?>" />
                <p class="oso-field-description"><?php esc_html_e( 'Upload a new resume (PDF, DOC, or DOCX)', 'oso-employer-portal' ); ?></p>
            </div>
        </div>

        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Availability', 'oso-employer-portal' ); ?></h3>
            
            <!-- Earliest Start -->
            <div class="oso-form-group">
                <label for="availability_start"><?php echo esc_html( $text_fields['availability_start']['label'] ); ?></label>
                <?php
                $start_date = ! empty( $meta['_oso_jobseeker_availability_start'] ) ? $meta['_oso_jobseeker_availability_start'] : '';
                // Debug: Show raw value
                // echo '<!-- Raw start date: ' . esc_html( $start_date ) . ' -->';
                // Convert to Y-m-d format if needed
                if ( $start_date && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) ) {
                    $timestamp = strtotime( $start_date );
                    if ( $timestamp ) {
                        $start_date = date( 'Y-m-d', $timestamp );
                    }
                }
                ?>
                <input type="date" id="availability_start" name="availability_start" value="<?php echo esc_attr( $start_date ); ?>" />
                <p class="oso-field-description"><?php esc_html_e( 'Current value:', 'oso-employer-portal' ); ?> <?php echo esc_html( ! empty( $meta['_oso_jobseeker_availability_start'] ) ? $meta['_oso_jobseeker_availability_start'] : 'Not set' ); ?></p>
            </div>

            <!-- Latest End -->
            <div class="oso-form-group">
                <label for="availability_end"><?php echo esc_html( $text_fields['availability_end']['label'] ); ?></label>
                <?php
                $end_date = ! empty( $meta['_oso_jobseeker_availability_end'] ) ? $meta['_oso_jobseeker_availability_end'] : '';
                // Convert to Y-m-d format if needed
                if ( $end_date && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date) ) {
                    $timestamp = strtotime( $end_date );
                    if ( $timestamp ) {
                        $end_date = date( 'Y-m-d', $timestamp );
                    }
                }
                ?>
                <input type="date" id="availability_end" name="availability_end" value="<?php echo esc_attr( $end_date ); ?>" />
                <p class="oso-field-description"><?php esc_html_e( 'Current value:', 'oso-employer-portal' ); ?> <?php echo esc_html( ! empty( $meta['_oso_jobseeker_availability_end'] ) ? $meta['_oso_jobseeker_availability_end'] : 'Not set' ); ?></p>
            </div>
        </div>

        <div class="oso-form-section">
            <h3><?php esc_html_e( 'About Me', 'oso-employer-portal' ); ?></h3>
            
            <!-- Why Interested -->
            <div class="oso-form-group">
                <label for="why_interested"><?php esc_html_e( 'Why are you interested in working at a summer camp?', 'oso-employer-portal' ); ?></label>
                <textarea id="why_interested" name="why_interested" rows="6"><?php echo esc_textarea( $jobseeker->post_content ); ?></textarea>
            </div>
        </div>

        <?php
        // Display all checkbox groups
        foreach ( $checkbox_groups as $key => $config ) :
            $value_raw = ! empty( $meta[ $config['meta'] ] ) ? $meta[ $config['meta'] ] : '';
            if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
                $current_values = OSO_Jobs_Utilities::meta_string_to_array( $value_raw );
            } else {
                $current_values = array();
            }
            
            // Trim all values for accurate comparison
            $current_values = array_map( 'trim', $current_values );
            ?>
            <div class="oso-form-section">
                <h3><?php echo esc_html( $config['label'] ); ?></h3>
                <p class="oso-field-description"><?php esc_html_e( 'Current values:', 'oso-employer-portal' ); ?> <?php echo esc_html( ! empty( $current_values ) ? implode( ', ', $current_values ) : 'None selected' ); ?></p>
                <div class="oso-checkbox-group">
                    <?php foreach ( $config['options'] as $option ) : ?>
                        <label class="oso-checkbox-label">
                            <input type="checkbox" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $option ); ?>" <?php checked( in_array( $option, $current_values ) ); ?> />
                            <?php echo esc_html( $option ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="oso-form-actions">
            <button type="submit" class="oso-btn oso-btn-primary">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e( 'Save Changes', 'oso-employer-portal' ); ?>
            </button>
            <a href="<?php echo esc_url( home_url( '/job-portal/jobseeker-dashboard/' ) ); ?>" class="oso-btn oso-btn-secondary">
                <?php esc_html_e( 'Cancel', 'oso-employer-portal' ); ?>
            </a>
        </div>

        <div id="oso-form-message" class="oso-form-message" style="display: none;"></div>
    </form>
</div>
