<?php
/**
 * Jobseeker Profile View for Employers
 * 
 * Template for displaying individual jobseeker profile details.
 * 
 * @package OSO_Employer_Portal
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get jobseeker ID from URL parameter
$jobseeker_id = isset( $_GET['jobseeker_id'] ) ? intval( $_GET['jobseeker_id'] ) : 0;

if ( ! $jobseeker_id ) {
    echo '<p class="oso-error">Invalid jobseeker ID.</p>';
    return;
}

// Get the jobseeker post
$jobseeker = get_post( $jobseeker_id );

if ( ! $jobseeker || $jobseeker->post_type !== 'oso_jobseeker' ) {
    echo '<p class="oso-error">Jobseeker not found.</p>';
    return;
}

// Get all meta data
$meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_id );

// Extract specific fields
$name = $jobseeker->post_title;
$email = isset( $meta['_oso_jobseeker_email'] ) ? $meta['_oso_jobseeker_email'] : '';
$phone = isset( $meta['_oso_jobseeker_phone'] ) ? $meta['_oso_jobseeker_phone'] : '';
$location = isset( $meta['_oso_jobseeker_location'] ) ? $meta['_oso_jobseeker_location'] : '';
$age_group = isset( $meta['_oso_jobseeker_age_group'] ) ? $meta['_oso_jobseeker_age_group'] : '';
$gender = isset( $meta['_oso_jobseeker_gender'] ) ? $meta['_oso_jobseeker_gender'] : '';
$visa_status = isset( $meta['_oso_jobseeker_visa_status'] ) ? $meta['_oso_jobseeker_visa_status'] : '';
$camp_role = isset( $meta['_oso_jobseeker_camp_role'] ) ? $meta['_oso_jobseeker_camp_role'] : '';
$availability_start = isset( $meta['_oso_jobseeker_availability_start'] ) ? $meta['_oso_jobseeker_availability_start'] : '';
$availability_end = isset( $meta['_oso_jobseeker_availability_end'] ) ? $meta['_oso_jobseeker_availability_end'] : '';
$experience_years = isset( $meta['_oso_jobseeker_experience_years'] ) ? $meta['_oso_jobseeker_experience_years'] : '';
$education_level = isset( $meta['_oso_jobseeker_education_level'] ) ? $meta['_oso_jobseeker_education_level'] : '';

// Get "Why Are You Interested" from post_content (plain text)
$why_interested = $jobseeker->post_content;

// Get job interests from meta field (should be badges)
$job_interests = isset( $meta['_oso_jobseeker_job_interests'] ) ? $meta['_oso_jobseeker_job_interests'] : array();
if ( ! is_array( $job_interests ) ) {
    $job_interests = array_filter( array_map( 'trim', explode( ',', $job_interests ) ) );
}

// Get skills (checkboxes from form)
$skills = isset( $meta['_oso_jobseeker_skills'] ) ? $meta['_oso_jobseeker_skills'] : array();
if ( ! is_array( $skills ) ) {
    $skills = array_filter( array_map( 'trim', explode( ',', $skills ) ) );
}

// Get certifications
$certifications = isset( $meta['_oso_jobseeker_certifications'] ) ? $meta['_oso_jobseeker_certifications'] : array();
if ( ! is_array( $certifications ) ) {
    $certifications = array_filter( array_map( 'trim', explode( ',', $certifications ) ) );
}

// Get languages
$languages = isset( $meta['_oso_jobseeker_languages'] ) ? $meta['_oso_jobseeker_languages'] : array();
if ( ! is_array( $languages ) ) {
    $languages = array_filter( array_map( 'trim', explode( ',', $languages ) ) );
}

?>

<div class="oso-jobseeker-profile">
    
    <!-- Back Button -->
    <div class="oso-profile-back">
        <a href="javascript:history.back()" class="oso-btn oso-btn-secondary">← Back to Results</a>
    </div>
    
    <!-- Profile Header -->
    <div class="oso-profile-header">
        <h1 class="oso-profile-name"><?php echo esc_html( $name ); ?></h1>
        <?php if ( $location ) : ?>
            <p class="oso-profile-location"><?php echo esc_html( $location ); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Two Column Layout -->
    <div class="oso-profile-layout">
        
        <!-- Main Content Column -->
        <div class="oso-profile-main">
            
            <!-- Why Interested Section (Plain Text from post_content) -->
            <?php if ( ! empty( $why_interested ) ) : ?>
                <div class="oso-profile-section">
                    <h2>Why Are You Interested in Summer Camp?</h2>
                    <div class="oso-profile-why">
                        <?php echo wpautop( wp_kses_post( $why_interested ) ); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Job Interests Section (Badges from meta field) -->
            <?php if ( ! empty( $job_interests ) ) : ?>
                <div class="oso-profile-section">
                    <h2>Job Interests</h2>
                    <div class="oso-profile-interests">
                        <?php foreach ( $job_interests as $interest ) : ?>
                            <span class="oso-interest-badge"><?php echo esc_html( $interest ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Skills Section -->
            <?php if ( ! empty( $skills ) ) : ?>
                <div class="oso-profile-section">
                    <h2>Skills</h2>
                    <div class="oso-profile-skills">
                        <?php foreach ( $skills as $skill ) : ?>
                            <span class="oso-skill-badge"><?php echo esc_html( $skill ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Certifications Section -->
            <?php if ( ! empty( $certifications ) ) : ?>
                <div class="oso-profile-section">
                    <h2>Certifications</h2>
                    <div class="oso-profile-certifications">
                        <?php foreach ( $certifications as $cert ) : ?>
                            <span class="oso-cert-badge"><?php echo esc_html( $cert ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Languages Section -->
            <?php if ( ! empty( $languages ) ) : ?>
                <div class="oso-profile-section">
                    <h2>Languages</h2>
                    <div class="oso-profile-languages">
                        <?php foreach ( $languages as $language ) : ?>
                            <span class="oso-language-badge"><?php echo esc_html( $language ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Sidebar Column -->
        <div class="oso-profile-sidebar">
            
            <!-- Contact Information -->
            <div class="oso-profile-box">
                <h3>Contact Information</h3>
                <?php if ( $email ) : ?>
                    <p><strong>Email:</strong><br><?php echo esc_html( $email ); ?></p>
                <?php endif; ?>
                <?php if ( $phone ) : ?>
                    <p><strong>Phone:</strong><br><?php echo esc_html( $phone ); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Basic Details -->
            <div class="oso-profile-box">
                <h3>Basic Details</h3>
                <?php if ( $age_group ) : ?>
                    <p><strong>Age Group:</strong><br><?php echo esc_html( $age_group ); ?></p>
                <?php endif; ?>
                <?php if ( $gender ) : ?>
                    <p><strong>Gender:</strong><br><?php echo esc_html( $gender ); ?></p>
                <?php endif; ?>
                <?php if ( $visa_status ) : ?>
                    <p><strong>Visa Status:</strong><br><?php echo esc_html( ucwords( str_replace( '_', ' ', $visa_status ) ) ); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Camp Information -->
            <div class="oso-profile-box">
                <h3>Camp Information</h3>
                <?php if ( $camp_role ) : ?>
                    <p><strong>Desired Role:</strong><br><?php echo esc_html( $camp_role ); ?></p>
                <?php endif; ?>
                <?php if ( $availability_start ) : ?>
                    <p><strong>Available From:</strong><br><?php echo esc_html( date( 'M d, Y', strtotime( $availability_start ) ) ); ?></p>
                <?php endif; ?>
                <?php if ( $availability_end ) : ?>
                    <p><strong>Available Until:</strong><br><?php echo esc_html( date( 'M d, Y', strtotime( $availability_end ) ) ); ?></p>
                <?php endif; ?>
                <?php if ( $experience_years ) : ?>
                    <p><strong>Experience:</strong><br><?php echo esc_html( $experience_years ); ?> years</p>
                <?php endif; ?>
            </div>
            
            <!-- Education -->
            <?php if ( $education_level ) : ?>
                <div class="oso-profile-box">
                    <h3>Education</h3>
                    <p><?php echo esc_html( $education_level ); ?></p>
                </div>
            <?php endif; ?>
            
        </div>
        
    </div>
    
</div>
