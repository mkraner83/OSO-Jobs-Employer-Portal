<?php
/**
 * Add/Edit Job Posting Form Template
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$job_id = isset( $_GET['job_id'] ) ? absint( $_GET['job_id'] ) : 0;
$is_edit = $job_id > 0;

// Get job data if editing
$job_title = '';
$job_description = '';
$job_meta = array();

if ( $is_edit ) {
    $job = get_post( $job_id );
    if ( $job && $job->post_type === 'oso_job_posting' ) {
        $job_title = $job->post_title;
        $job_description = $job->post_content;
        $job_meta = OSO_Job_Manager::instance()->get_job_meta( $job_id );
        
        // Verify ownership
        $employer_post = OSO_Employer_Shortcodes::instance()->get_employer_by_user( get_current_user_id() );
        if ( ! $employer_post || $job_meta['_oso_job_employer_id'] != $employer_post->ID ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                echo '<p>' . esc_html__( 'You do not have permission to edit this job.', 'oso-employer-portal' ) . '</p>';
                return;
            }
        }
    } else {
        echo '<p>' . esc_html__( 'Job not found.', 'oso-employer-portal' ) . '</p>';
        return;
    }
}

// Get job types from utilities
$job_types = array(
    'Counselor',
    'Lifeguard',
    'Arts Instructor',
    'Sports Coach',
    'Waterfront Staff',
    'Kitchen Staff',
    'Maintenance',
    'Administrative',
    'Medical Staff',
    'Program Director'
);

// Get skills (reuse from jobseeker fields)
$all_skills = array();
if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
    $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
    foreach ( $checkbox_groups as $group_key => $group_config ) {
        if ( in_array( $group_key, array( 'sports_skills', 'arts_skills', 'adventure_skills', 'waterfront_skills', 'certifications' ) ) ) {
            foreach ( $group_config['options'] as $option ) {
                $all_skills[] = $option;
            }
        }
    }
}

$selected_types = $is_edit && ! empty( $job_meta['_oso_job_type'] ) ? explode( "\n", $job_meta['_oso_job_type'] ) : array();
$selected_skills = $is_edit && ! empty( $job_meta['_oso_job_required_skills'] ) ? explode( "\n", $job_meta['_oso_job_required_skills'] ) : array();
?>

<div class="oso-employer-add-job">
    <h2><?php echo $is_edit ? esc_html__( 'Edit Job Posting', 'oso-employer-portal' ) : esc_html__( 'Add New Job Posting', 'oso-employer-portal' ); ?></h2>
    
    <form id="oso-job-form" class="oso-job-form">
        <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>">
        
        <!-- Job Title -->
        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Job Information', 'oso-employer-portal' ); ?></h3>
            
            <div class="oso-form-row">
                <label for="job_title"><?php esc_html_e( 'Job Title', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr( $job_title ); ?>" required class="oso-input-full" placeholder="e.g., Summer Camp Counselor">
            </div>
            
            <!-- Job Type -->
            <div class="oso-form-row">
                <label><?php esc_html_e( 'Job Type', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                <p class="description"><?php esc_html_e( 'Select all that apply', 'oso-employer-portal' ); ?></p>
                <div class="oso-checkbox-grid">
                    <?php foreach ( $job_types as $type ) : ?>
                        <label class="oso-checkbox-label">
                            <input type="checkbox" name="job_type[]" value="<?php echo esc_attr( $type ); ?>" <?php checked( in_array( $type, $selected_types ) ); ?>>
                            <?php echo esc_html( $type ); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Job Description -->
            <div class="oso-form-row">
                <label for="job_description"><?php esc_html_e( 'Job Description', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                <p class="description"><?php esc_html_e( 'Detailed description of the role, responsibilities, and requirements', 'oso-employer-portal' ); ?></p>
                <textarea id="job_description" name="job_description" rows="8" required class="oso-textarea-full"><?php echo esc_textarea( $job_description ); ?></textarea>
            </div>
        </div>
        
        <!-- Required Skills -->
        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Required Skills', 'oso-employer-portal' ); ?></h3>
            <p class="description"><?php esc_html_e( 'Select skills that are required or preferred for this position', 'oso-employer-portal' ); ?></p>
            
            <div class="oso-checkbox-grid">
                <?php foreach ( $all_skills as $skill ) : ?>
                    <label class="oso-checkbox-label">
                        <input type="checkbox" name="required_skills[]" value="<?php echo esc_attr( $skill ); ?>" <?php checked( in_array( $skill, $selected_skills ) ); ?>>
                        <?php echo esc_html( $skill ); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Dates & Details -->
        <div class="oso-form-section">
            <h3><?php esc_html_e( 'Position Details', 'oso-employer-portal' ); ?></h3>
            
            <div class="oso-form-row-group">
                <div class="oso-form-row">
                    <label for="start_date"><?php esc_html_e( 'Start Date', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr( ! empty( $job_meta['_oso_job_start_date'] ) ? $job_meta['_oso_job_start_date'] : '' ); ?>" required>
                </div>
                
                <div class="oso-form-row">
                    <label for="end_date"><?php esc_html_e( 'End Date', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr( ! empty( $job_meta['_oso_job_end_date'] ) ? $job_meta['_oso_job_end_date'] : '' ); ?>" required>
                </div>
            </div>
            
            <div class="oso-form-row-group">
                <div class="oso-form-row">
                    <label for="positions"><?php esc_html_e( 'Number of Positions', 'oso-employer-portal' ); ?> <span class="required">*</span></label>
                    <input type="number" id="positions" name="positions" value="<?php echo esc_attr( ! empty( $job_meta['_oso_job_positions'] ) ? $job_meta['_oso_job_positions'] : '1' ); ?>" min="1" required>
                </div>
                
                <div class="oso-form-row">
                    <label for="compensation"><?php esc_html_e( 'Compensation', 'oso-employer-portal' ); ?></label>
                    <input type="text" id="compensation" name="compensation" value="<?php echo esc_attr( ! empty( $job_meta['_oso_job_compensation'] ) ? $job_meta['_oso_job_compensation'] : '' ); ?>" placeholder="e.g., $500/week + housing">
                    <p class="description"><?php esc_html_e( 'Optional - Salary, hourly rate, or other compensation details', 'oso-employer-portal' ); ?></p>
                </div>
            </div>
            
            <!-- Application Instructions -->
            <div class="oso-form-row">
                <label for="application_instructions"><?php esc_html_e( 'Application Instructions', 'oso-employer-portal' ); ?></label>
                <p class="description"><?php esc_html_e( 'How should candidates apply? (Optional - candidates can also use the built-in application form)', 'oso-employer-portal' ); ?></p>
                <textarea id="application_instructions" name="application_instructions" rows="4" class="oso-textarea-full"><?php echo esc_textarea( ! empty( $job_meta['_oso_job_application_instructions'] ) ? $job_meta['_oso_job_application_instructions'] : '' ); ?></textarea>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="oso-form-actions">
            <button type="submit" class="oso-btn oso-btn-primary" id="oso-save-job-btn">
                <span class="dashicons dashicons-yes"></span>
                <?php echo $is_edit ? esc_html__( 'Update Job', 'oso-employer-portal' ) : esc_html__( 'Post Job', 'oso-employer-portal' ); ?>
            </button>
            <a href="<?php echo esc_url( home_url( '/job-portal/employer-dashboard/' ) ); ?>" class="oso-btn oso-btn-secondary">
                <?php esc_html_e( 'Cancel', 'oso-employer-portal' ); ?>
            </a>
            <span class="oso-form-status" id="oso-job-status"></span>
        </div>
    </form>
</div>

<style>
.oso-employer-add-job {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.oso-job-form {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.oso-form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.oso-form-section:last-of-type {
    border-bottom: none;
}

.oso-form-section h3 {
    margin: 0 0 20px 0;
    color: #548A8F;
    font-size: 1.3em;
}

.oso-form-row {
    margin-bottom: 20px;
}

.oso-form-row label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.oso-form-row .required {
    color: #d9534f;
}

.oso-form-row .description {
    margin: 5px 0;
    font-size: 0.9em;
    color: #666;
}

.oso-input-full,
.oso-textarea-full {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
    font-family: inherit;
}

.oso-input-full:focus,
.oso-textarea-full:focus {
    outline: none;
    border-color: #548A8F;
    box-shadow: 0 0 0 3px rgba(84, 138, 143, 0.1);
}

.oso-textarea-full {
    resize: vertical;
}

.oso-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.oso-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.oso-checkbox-label:hover {
    background: #f0f0f0;
}

.oso-checkbox-label input[type="checkbox"] {
    margin: 0;
}

.oso-form-row-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.oso-form-actions {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #e0e0e0;
}

.oso-form-status {
    margin-left: 15px;
    font-size: 0.95em;
}

.oso-form-status.success {
    color: #5cb85c;
}

.oso-form-status.error {
    color: #d9534f;
}

.oso-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 1em;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s;
}

.oso-btn-primary {
    background: #548A8F;
    color: #fff;
}

.oso-btn-primary:hover {
    background: #466f73;
}

.oso-btn-secondary {
    background: #f5f5f5;
    color: #333;
}

.oso-btn-secondary:hover {
    background: #e0e0e0;
}

.oso-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#oso-job-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#oso-save-job-btn');
        var $status = $('#oso-job-status');
        
        // Disable button
        $btn.prop('disabled', true).find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-update').css('animation', 'rotation 1s infinite linear');
        $status.removeClass('success error').text('<?php esc_html_e( 'Saving...', 'oso-employer-portal' ); ?>');
        
        $.ajax({
            url: osoEmployerPortal.ajaxUrl,
            type: 'POST',
            data: $form.serialize() + '&action=oso_save_job_posting&nonce=' + osoEmployerPortal.jobNonce,
            success: function(response) {
                if (response.success) {
                    $status.addClass('success').text(response.data.message);
                    $btn.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-yes').css('animation', '');
                    
                    // Redirect to dashboard after 1 second
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url( home_url( '/job-portal/employer-dashboard/' ) ); ?>';
                    }, 1000);
                } else {
                    $status.addClass('error').text(response.data.message || '<?php esc_html_e( 'Error saving job.', 'oso-employer-portal' ); ?>');
                    $btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update').addClass('dashicons-yes').css('animation', '');
                }
            },
            error: function() {
                $status.addClass('error').text('<?php esc_html_e( 'Network error. Please try again.', 'oso-employer-portal' ); ?>');
                $btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update').addClass('dashicons-yes').css('animation', '');
            }
        });
    });
});
</script>
