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
            // Define all employer fields to display (matching WPForms)
            $employer_fields = array(
                '_oso_employer_company' => array( 'label' => 'Camp Name', 'required' => true ),
                '_oso_employer_email' => array( 'label' => 'Contact Email', 'required' => true ),
                '_oso_employer_website' => array( 'label' => 'Website', 'required' => false, 'type' => 'url' ),
                '_oso_employer_state' => array( 'label' => 'State', 'required' => false ),
                '_oso_employer_address' => array( 'label' => 'Address', 'required' => false ),
                '_oso_employer_major_city' => array( 'label' => 'Closest Major City', 'required' => false ),
                '_oso_employer_training_start' => array( 'label' => 'Start of Staff Training', 'required' => false, 'type' => 'date' ),
                '_oso_employer_housing' => array( 'label' => 'Housing Provided', 'required' => false ),
                '_oso_employer_subscription_type' => array( 'label' => 'Subscription Type', 'required' => false ),
                '_oso_employer_camp_types' => array( 'label' => 'Type of Camp', 'required' => false, 'type' => 'list' ),
                '_oso_employer_description' => array( 'label' => 'Brief Description', 'required' => false, 'full_width' => true, 'type' => 'textarea' ),
                '_oso_employer_social_links' => array( 'label' => 'Social Media Links', 'required' => false, 'full_width' => true, 'type' => 'textarea' ),
            );

            foreach ( $employer_fields as $meta_key => $field_config ) :
                $value = ! empty( $meta[ $meta_key ] ) ? $meta[ $meta_key ] : '';
                
                // Skip empty optional fields
                if ( empty( $value ) && ! $field_config['required'] ) {
                    continue;
                }
                
                $display_value = ! empty( $value ) ? $value : 'Not provided';
                $field_class = ! empty( $field_config['full_width'] ) ? 'oso-profile-field-full' : 'oso-profile-field';
                $field_type = isset( $field_config['type'] ) ? $field_config['type'] : 'text';
                ?>
                <div class="<?php echo esc_attr( $field_class ); ?>">
                    <strong><?php echo esc_html( $field_config['label'] ); ?>:</strong>
                    <?php if ( $field_type === 'url' && ! empty( $value ) ) : ?>
                        <a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $value ); ?></a>
                    <?php elseif ( $field_type === 'textarea' ) : ?>
                        <p><?php echo wp_kses_post( nl2br( $value ) ); ?></p>
                    <?php elseif ( $field_type === 'list' ) : ?>
                        <span><?php echo esc_html( str_replace( "\n", ', ', $value ) ); ?></span>
                    <?php elseif ( $field_type === 'date' && ! empty( $value ) ) : ?>
                        <span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $value ) ) ); ?></span>
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
