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
        <h2><?php esc_html_e( 'Employer Dashboard', 'oso-employer-portal' ); ?></h2>
        <p><?php esc_html_e( 'Please log in to access your employer dashboard.', 'oso-employer-portal' ); ?></p>
        
        <div class="oso-login-form">
            <?php echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        
        <p class="oso-lost-password">
            <a href="<?php echo esc_url( $lost_url ); ?>"><?php esc_html_e( 'Lost your password?', 'oso-employer-portal' ); ?></a>
        </p>
    </div>
    <?php
    return;
endif;

// User is logged in
?>
<div class="oso-employer-dashboard">
    <h2><?php esc_html_e( 'Employer Dashboard', 'oso-employer-portal' ); ?></h2>
    
    <div class="oso-dashboard-welcome">
        <p><?php printf( esc_html__( 'Welcome, %s!', 'oso-employer-portal' ), esc_html( $user->display_name ) ); ?></p>
    </div>

    <div class="oso-employer-profile">
        <h3><?php esc_html_e( 'Your Profile', 'oso-employer-portal' ); ?></h3>
        <div class="oso-profile-info">
            <p><strong><?php esc_html_e( 'Full Name:', 'oso-employer-portal' ); ?></strong> <?php echo esc_html( $meta['_oso_employer_full_name'] ); ?></p>
            <p><strong><?php esc_html_e( 'Email:', 'oso-employer-portal' ); ?></strong> <?php echo esc_html( $meta['_oso_employer_email'] ); ?></p>
            <?php if ( ! empty( $meta['_oso_employer_phone'] ) ) : ?>
                <p><strong><?php esc_html_e( 'Phone:', 'oso-employer-portal' ); ?></strong> <?php echo esc_html( $meta['_oso_employer_phone'] ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $meta['_oso_employer_company'] ) ) : ?>
                <p><strong><?php esc_html_e( 'Company:', 'oso-employer-portal' ); ?></strong> <?php echo esc_html( $meta['_oso_employer_company'] ); ?></p>
            <?php endif; ?>
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
        <p>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="button"><?php esc_html_e( 'Logout', 'oso-employer-portal' ); ?></a>
        </p>
    </div>
</div>
