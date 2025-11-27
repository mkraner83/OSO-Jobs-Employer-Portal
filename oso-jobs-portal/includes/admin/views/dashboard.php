<?php
/**
 * Dashboard view.
 *
 * @var array $stats Optional stats array.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$count             = wp_count_posts( OSO_Jobs_Portal::POST_TYPE );
$stats             = array(
    'jobs'        => isset( $count->publish ) ? (int) $count->publish : 0,
    'departments' => (int) wp_count_terms( OSO_Jobs_Portal::TAXONOMY_DEPARTMENT ),
);
$latest_jobseekers = get_posts(
    array(
        'post_type'      => OSO_Jobs_Portal::POST_TYPE_JOBSEEKER,
        'posts_per_page' => 3,
        'post_status'    => 'publish',
    )
);
$latest_employers  = get_posts(
    array(
        'post_type'      => OSO_Jobs_Portal::POST_TYPE_EMPLOYER,
        'posts_per_page' => 3,
        'post_status'    => 'publish',
    )
);
?>
<div class="wrap oso-jobs-dashboard">
    <h1><?php esc_html_e( 'OSO Jobs Portal', 'oso-jobs-portal' ); ?></h1>
    <p class="description">
        <?php esc_html_e( 'Manage your open positions, review incoming applications, and fine tune the candidate experience.', 'oso-jobs-portal' ); ?>
    </p>

    <div class="oso-jobs-stats">
        <div class="oso-jobs-card">
            <span class="oso-jobs-card-label"><?php esc_html_e( 'Published Jobs', 'oso-jobs-portal' ); ?></span>
            <strong class="oso-jobs-card-value"><?php echo esc_html( $stats['jobs'] ); ?></strong>
        </div>
        <div class="oso-jobs-card">
            <span class="oso-jobs-card-label"><?php esc_html_e( 'Departments', 'oso-jobs-portal' ); ?></span>
            <strong class="oso-jobs-card-value"><?php echo esc_html( $stats['departments'] ); ?></strong>
        </div>
    </div>

    <div class="oso-jobs-cta">
        <a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . OSO_Jobs_Portal::POST_TYPE ) ); ?>">
            <?php esc_html_e( 'Add Job', 'oso-jobs-portal' ); ?>
        </a>
        <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . OSO_Jobs_Portal::POST_TYPE ) ); ?>">
            <?php esc_html_e( 'View All Jobs', 'oso-jobs-portal' ); ?>
        </a>
    </div>

    <div class="oso-jobs-recent">
        <div class="oso-jobs-recent__column">
            <h2><?php esc_html_e( 'Latest Jobseekers', 'oso-jobs-portal' ); ?></h2>
            <?php if ( ! empty( $latest_jobseekers ) ) : ?>
                <ul class="oso-jobs-recent__list">
                    <?php foreach ( $latest_jobseekers as $jobseeker ) : ?>
                        <?php
                        $email      = get_post_meta( $jobseeker->ID, '_oso_jobseeker_email', true );
                        $location   = get_post_meta( $jobseeker->ID, '_oso_jobseeker_location', true );
                        $interests  = get_post_meta( $jobseeker->ID, '_oso_jobseeker_job_interests', true );
                        $resume     = get_post_meta( $jobseeker->ID, '_oso_jobseeker_resume', true );
                        $entry_link = '';
                        $entry_id   = get_post_meta( $jobseeker->ID, '_oso_jobseeker_wpforms_entry', true );
                        if ( $entry_id ) {
                            $entry_link = admin_url( 'admin.php?page=wpforms-entries&view=details&entry_id=' . (int) $entry_id );
                        }
                        ?>
                        <li>
                            <div class="oso-jobs-recent__details">
                                <strong><?php echo esc_html( get_the_title( $jobseeker ) ); ?></strong>
                                <?php if ( $location ) : ?>
                                    <span class="oso-jobs-recent__meta"><?php echo esc_html( $location ); ?></span>
                                <?php endif; ?>
                                <span class="oso-jobs-recent__date"><?php echo esc_html( get_the_date( '', $jobseeker ) ); ?></span>
                                <?php if ( $interests ) : ?>
                                    <div class="oso-jobs-recent__meta"><?php echo esc_html( $interests ); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="oso-jobs-recent__actions">
                                <?php if ( $email ) : ?>
                                    <a class="oso-jobs-recent__action" href="mailto:<?php echo esc_attr( $email ); ?>">
                                        <?php esc_html_e( 'Email', 'oso-jobs-portal' ); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ( $resume ) : ?>
                                    <a class="oso-jobs-recent__action" href="<?php echo esc_url( $resume ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e( 'Resume', 'oso-jobs-portal' ); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ( $entry_link ) : ?>
                                    <a class="oso-jobs-recent__action" href="<?php echo esc_url( $entry_link ); ?>">
                                        <?php esc_html_e( 'Details', 'oso-jobs-portal' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e( 'No jobseeker submissions found.', 'oso-jobs-portal' ); ?></p>
            <?php endif; ?>
        </div>

        <div class="oso-jobs-recent__column">
            <h2><?php esc_html_e( 'Latest Employers', 'oso-jobs-portal' ); ?></h2>
            <?php if ( ! empty( $latest_employers ) ) : ?>
                <ul class="oso-jobs-recent__list">
                    <?php foreach ( $latest_employers as $employer ) : ?>
                        <li>
                            <strong><?php echo esc_html( get_the_title( $employer ) ); ?></strong>
                            <span class="oso-jobs-recent__date"><?php echo esc_html( get_the_date( '', $employer ) ); ?></span>
                            <a class="oso-jobs-recent__action" href="<?php echo esc_url( get_edit_post_link( $employer->ID ) ); ?>">
                                <?php esc_html_e( 'Manage', 'oso-jobs-portal' ); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e( 'No employers have been added yet.', 'oso-jobs-portal' ); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
