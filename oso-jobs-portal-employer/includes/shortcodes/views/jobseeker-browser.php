<?php
/**
 * Jobseeker Browser Template
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="oso-jobseeker-browser">
    <div class="oso-browser-header">
        <h2><?php esc_html_e( 'Browse Jobseekers', 'oso-employer-portal' ); ?></h2>
        <p class="oso-browser-description"><?php esc_html_e( 'View profiles of qualified candidates looking for positions.', 'oso-employer-portal' ); ?></p>
    </div>

    <?php if ( $jobseekers->have_posts() ) : ?>
        <div class="oso-jobseeker-count">
            <p><?php printf( esc_html__( 'Showing %d jobseekers', 'oso-employer-portal' ), $jobseekers->found_posts ); ?></p>
        </div>

        <div class="oso-jobseeker-grid">
            <?php
            while ( $jobseekers->have_posts() ) :
                $jobseekers->the_post();
                $jobseeker_id = get_the_ID();
                
                // Get metadata
                if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
                    $meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_id );
                } else {
                    $meta = array();
                }
                
                $photo = ! empty( $meta['_oso_jobseeker_photo'] ) ? $meta['_oso_jobseeker_photo'] : '';
                $name = ! empty( $meta['_oso_jobseeker_full_name'] ) ? $meta['_oso_jobseeker_full_name'] : get_the_title();
                $location = ! empty( $meta['_oso_jobseeker_location'] ) ? $meta['_oso_jobseeker_location'] : '';
                $email = ! empty( $meta['_oso_jobseeker_email'] ) ? $meta['_oso_jobseeker_email'] : '';
                $availability_start = ! empty( $meta['_oso_jobseeker_availability_start'] ) ? $meta['_oso_jobseeker_availability_start'] : '';
                $availability_end = ! empty( $meta['_oso_jobseeker_availability_end'] ) ? $meta['_oso_jobseeker_availability_end'] : '';
                
                // Get job interests
                $job_interests_raw = ! empty( $meta['_oso_jobseeker_job_interests'] ) ? $meta['_oso_jobseeker_job_interests'] : '';
                if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
                    $job_interests = OSO_Jobs_Utilities::meta_string_to_array( $job_interests_raw );
                } else {
                    $job_interests = array();
                }
                
                // Build profile URL
                $profile_url = add_query_arg( 'jobseeker_id', $jobseeker_id, get_permalink() );
                // If you have a dedicated profile page, use that URL instead
                // $profile_url = add_query_arg( 'jobseeker_id', $jobseeker_id, home_url( '/jobseeker-profile/' ) );
                ?>
                
                <div class="oso-jobseeker-card">
                    <div class="oso-card-photo">
                        <?php if ( $photo ) : ?>
                            <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
                        <?php else : ?>
                            <div class="oso-photo-placeholder">
                                <span class="dashicons dashicons-admin-users"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="oso-card-content">
                        <h3 class="oso-card-name"><?php echo esc_html( $name ); ?></h3>
                        
                        <?php if ( $location ) : ?>
                            <p class="oso-card-location">
                                <span class="dashicons dashicons-location"></span>
                                <?php echo esc_html( $location ); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ( $availability_start || $availability_end ) : ?>
                            <p class="oso-card-availability">
                                <strong><?php esc_html_e( 'Available:', 'oso-employer-portal' ); ?></strong>
                                <?php
                                if ( $availability_start && $availability_end ) {
                                    echo esc_html( $availability_start . ' - ' . $availability_end );
                                } elseif ( $availability_start ) {
                                    echo esc_html( 'From ' . $availability_start );
                                } elseif ( $availability_end ) {
                                    echo esc_html( 'Until ' . $availability_end );
                                }
                                ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $job_interests ) ) : ?>
                            <div class="oso-card-interests">
                                <?php foreach ( array_slice( $job_interests, 0, 3 ) as $interest ) : ?>
                                    <span class="oso-interest-badge"><?php echo esc_html( $interest ); ?></span>
                                <?php endforeach; ?>
                                <?php if ( count( $job_interests ) > 3 ) : ?>
                                    <span class="oso-interest-more">+<?php echo esc_html( count( $job_interests ) - 3 ); ?> more</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="oso-card-actions">
                        <a href="<?php echo esc_url( $profile_url ); ?>" class="oso-btn oso-btn-primary">
                            <?php esc_html_e( 'View Full Profile', 'oso-employer-portal' ); ?>
                        </a>
                    </div>
                </div>
                
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        </div>

        <?php if ( $jobseekers->max_num_pages > 1 ) : ?>
            <div class="oso-browser-pagination">
                <?php
                echo paginate_links(
                    array(
                        'total'   => $jobseekers->max_num_pages,
                        'current' => $paged,
                        'prev_text' => '&laquo; ' . __( 'Previous', 'oso-employer-portal' ),
                        'next_text' => __( 'Next', 'oso-employer-portal' ) . ' &raquo;',
                    )
                );
                ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="oso-no-jobseekers">
            <p><?php esc_html_e( 'No jobseekers found.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>
</div>
