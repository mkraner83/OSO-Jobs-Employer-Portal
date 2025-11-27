<?php
/**
 * Settings page view.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'OSO Jobs Settings', 'oso-jobs-portal' ); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'oso_jobs_settings_group' );
        do_settings_sections( 'oso_jobs_settings' );
        submit_button();
        ?>
    </form>
</div>
