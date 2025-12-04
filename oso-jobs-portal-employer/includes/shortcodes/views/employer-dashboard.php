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
    <h2><?php esc_html_e( 'Employer Profile', 'oso-employer-portal' ); ?></h2>
    
    <!-- Quick Links at Top -->
    <div class="oso-dashboard-quick-links">
        <h3><?php esc_html_e( 'Quick Links', 'oso-employer-portal' ); ?></h3>
        <div class="oso-quick-links-grid">
            <a href="<?php echo esc_url( home_url( '/job-portal/browse-jobseekers/' ) ); ?>" class="oso-quick-link">
                <span class="dashicons dashicons-groups"></span>
                <span><?php esc_html_e( 'Browse Jobseekers', 'oso-employer-portal' ); ?></span>
            </a>
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
            // Define all employer fields to display
            $employer_fields = array(
                '_oso_employer_full_name' => array( 'label' => 'Full Name', 'required' => true ),
                '_oso_employer_email' => array( 'label' => 'Email', 'required' => true ),
                '_oso_employer_phone' => array( 'label' => 'Phone', 'required' => false ),
                '_oso_employer_company' => array( 'label' => 'Company Name', 'required' => false ),
                '_oso_employer_contact_person' => array( 'label' => 'Contact Person', 'required' => false ),
                '_oso_employer_job_title' => array( 'label' => 'Job Title/Position', 'required' => false ),
                '_oso_employer_address' => array( 'label' => 'Address', 'required' => false ),
                '_oso_employer_city' => array( 'label' => 'City', 'required' => false ),
                '_oso_employer_state' => array( 'label' => 'State', 'required' => false ),
                '_oso_employer_zip' => array( 'label' => 'Zip Code', 'required' => false ),
                '_oso_employer_website' => array( 'label' => 'Website', 'required' => false ),
                '_oso_employer_description' => array( 'label' => 'Company Description', 'required' => false, 'full_width' => true ),
            );

            foreach ( $employer_fields as $meta_key => $field_config ) :
                $value = ! empty( $meta[ $meta_key ] ) ? $meta[ $meta_key ] : '';
                
                // Skip empty optional fields
                if ( empty( $value ) && ! $field_config['required'] ) {
                    continue;
                }
                
                $display_value = ! empty( $value ) ? $value : 'Not provided';
                $field_class = ! empty( $field_config['full_width'] ) ? 'oso-profile-field-full' : 'oso-profile-field';
                ?>
                <div class="<?php echo esc_attr( $field_class ); ?>">
                    <strong><?php echo esc_html( $field_config['label'] ); ?>:</strong>
                    <?php if ( $meta_key === '_oso_employer_website' && ! empty( $value ) ) : ?>
                        <a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $value ); ?></a>
                    <?php elseif ( $meta_key === '_oso_employer_description' ) : ?>
                        <p><?php echo wp_kses_post( nl2br( $value ) ); ?></p>
                    <?php else : ?>
                        <span><?php echo esc_html( $display_value ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="oso-employer-jobs">
        <h3><?php esc_html_e( 'Your Job Postings', 'oso-employer-portal' ); ?></h3>
        
        <?php if ( $jobs->have_posts() ) : ?>
            <table class="oso-jobs-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Job Title', 'oso-employer-portal' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'oso-employer-portal' ); ?></th>
                        <th><?php esc_html_e( 'Date Posted', 'oso-employer-portal' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'oso-employer-portal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ( $jobs->have_posts() ) :
                        $jobs->the_post();
                        $status = get_post_status();
                        $status_label = ucfirst( $status );
                        if ( 'publish' === $status ) {
                            $status_label = __( 'Published', 'oso-employer-portal' );
                        } elseif ( 'draft' === $status ) {
                            $status_label = __( 'Draft', 'oso-employer-portal' );
                        } elseif ( 'pending' === $status ) {
                            $status_label = __( 'Pending Review', 'oso-employer-portal' );
                        }
                        ?>
                        <tr>
                            <td><?php the_title(); ?></td>
                            <td><span class="oso-job-status oso-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
                            <td><?php echo esc_html( get_the_date() ); ?></td>
                            <td class="oso-job-actions">
                                <a href="<?php the_permalink(); ?>" target="_blank"><?php esc_html_e( 'View', 'oso-employer-portal' ); ?></a>
                                <?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
                                    | <a href="<?php echo esc_url( get_edit_post_link( get_the_ID() ) ); ?>"><?php esc_html_e( 'Edit', 'oso-employer-portal' ); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e( 'You have not posted any jobs yet.', 'oso-employer-portal' ); ?></p>
        <?php endif; ?>
    </div>

    <div class="oso-dashboard-actions">
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="oso-btn oso-btn-secondary">
            <span class="dashicons dashicons-exit"></span>
            <?php esc_html_e( 'Logout', 'oso-employer-portal' ); ?>
        </a>
    </div>
</div>
