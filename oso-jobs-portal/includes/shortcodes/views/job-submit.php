<?php
/**
 * Job submit shortcode view.
 *
 * @var int   $form_id
 * @var array $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="oso-job-submit">
    <h2><?php esc_html_e( 'Submit Your Application', 'oso-jobs-portal' ); ?></h2>
    <p><?php esc_html_e( 'Complete the form below to apply for one of our open roles. A member of the OSO team will be in touch shortly.', 'oso-jobs-portal' ); ?></p>
    <?php echo do_shortcode( '[wpforms id="' . absint( $form_id ) . '"]' ); ?>
</div>
