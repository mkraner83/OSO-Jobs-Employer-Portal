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
    <?php 
    // Check if employer is approved (admins always see the button)
    $is_approved = current_user_can( 'manage_options' ) || ( isset( $meta['_oso_employer_approved'] ) && $meta['_oso_employer_approved'] === '1' );
    
    // Check if subscription is expired
    $is_expired = false;
    if ( ! current_user_can( 'manage_options' ) && ! empty( $meta['_oso_employer_subscription_ends'] ) ) {
        $expiration_date = strtotime( $meta['_oso_employer_subscription_ends'] );
        if ( $expiration_date && $expiration_date < time() ) {
            $is_expired = true;
        }
    }
    ?>
    
    <?php if ( $is_approved && ! $is_expired ) : ?>
        <!-- Full-Width Quick Link Banner -->
        <div class="oso-quick-link-banner">
            <a href="<?php echo esc_url( home_url( '/job-portal/browse-jobseekers/' ) ); ?>" class="oso-quick-link">
                <span class="dashicons dashicons-groups"></span>
                <span><?php esc_html_e( 'Browse Jobseekers', 'oso-employer-portal' ); ?></span>
            </a>
        </div>
    <?php elseif ( $is_expired ) : ?>
        <!-- Subscription Expired Message -->
        <div style="padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; margin: 0 0 30px 0;">
            <p style="margin: 0; color: #721c24;"><strong><?php esc_html_e( 'Subscription Expired', 'oso-employer-portal' ); ?></strong></p>
            <p style="margin: 10px 0 0 0; color: #721c24;">
                <?php 
                printf( 
                    esc_html__( 'Your subscription expired on %s. Please renew your subscription to continue browsing jobseeker profiles.', 'oso-employer-portal' ),
                    '<strong>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $meta['_oso_employer_subscription_ends'] ) ) ) . '</strong>'
                );
                ?>
            </p>
        </div>
    <?php else : ?>
        <!-- Pending Approval Message -->
        <div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; margin: 0 0 30px 0;">
            <p style="margin: 0; color: #856404;"><strong><?php esc_html_e( 'Account Pending Approval', 'oso-employer-portal' ); ?></strong></p>
            <p style="margin: 10px 0 0 0; color: #856404;"><?php esc_html_e( 'Your employer account is currently pending approval. You will be able to browse jobseekers once an administrator approves your account.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>

    <!-- Job Postings Section -->
    <div class="oso-employer-jobs">
        <h3><?php esc_html_e( 'Your Job Postings', 'oso-employer-portal' ); ?></h3>
        <p><?php esc_html_e( 'You have not posted any jobs yet.', 'oso-employer-portal' ); ?></p>
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
                '_oso_employer_subscription_ends' => array( 'label' => 'Subscription Ends', 'required' => false, 'type' => 'expiration_date' ),
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
                    <?php elseif ( $field_type === 'expiration_date' && ! empty( $value ) ) : ?>
                        <?php
                        $formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $value ) );
                        $expiration_timestamp = strtotime( $value );
                        $is_expired_check = $expiration_timestamp && $expiration_timestamp < time();
                        if ( $is_expired_check && ! current_user_can( 'manage_options' ) ) {
                            echo '<span style="color: #d9534f; font-weight: bold;">' . esc_html( $formatted_date ) . ' (' . esc_html__( 'Expired', 'oso-employer-portal' ) . ')</span>';
                        } else {
                            echo '<span>' . esc_html( $formatted_date ) . '</span>';
                        }
                        ?>
                    <?php elseif ( $field_type === 'date' && ! empty( $value ) ) : ?>
                        <span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $value ) ) ); ?></span>
                    <?php else : ?>
                        <span><?php echo esc_html( $display_value ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="oso-dashboard-actions">
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="oso-btn oso-btn-secondary">
            <span class="dashicons dashicons-exit"></span>
            <?php esc_html_e( 'Logout', 'oso-employer-portal' ); ?>
        </a>
    </div>
</div>
