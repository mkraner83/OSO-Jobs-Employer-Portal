<?php
/**
 * Submissions view.
 *
 * @var array $entries WPForms entries prepared by handler.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Recent Job Form Submissions', 'oso-jobs-portal' ); ?></h1>
    <?php if ( empty( $entries ) ) : ?>
        <p><?php esc_html_e( 'No submissions found. Once candidates submit the form, entries will appear here.', 'oso-jobs-portal' ); ?></p>
    <?php else : ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Applicant', 'oso-jobs-portal' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'oso-jobs-portal' ); ?></th>
                    <th><?php esc_html_e( 'Role', 'oso-jobs-portal' ); ?></th>
                    <th><?php esc_html_e( 'Submitted', 'oso-jobs-portal' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $entries as $entry ) : ?>
                    <tr>
                        <td><?php echo esc_html( $entry['name'] ); ?></td>
                        <td><a href="mailto:<?php echo esc_attr( $entry['email'] ); ?>"><?php echo esc_html( $entry['email'] ); ?></a></td>
                        <td><?php echo esc_html( $entry['job_title'] ); ?></td>
                        <td><?php echo esc_html( $entry['submitted'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
