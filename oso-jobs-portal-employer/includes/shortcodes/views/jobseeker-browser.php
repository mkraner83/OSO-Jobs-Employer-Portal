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

    <!-- Search and Filter Form -->
    <div class="oso-filter-section">
        <form method="get" class="oso-filter-form" id="jobseeker-filter-form">
            <div class="oso-filter-row">
                <div class="oso-filter-field oso-filter-search">
                    <label for="search"><?php esc_html_e( 'Search', 'oso-employer-portal' ); ?></label>
                    <input type="text" id="search" name="search" placeholder="<?php esc_attr_e( 'Name, location, email...', 'oso-employer-portal' ); ?>" value="<?php echo esc_attr( isset( $_GET['search'] ) ? $_GET['search'] : '' ); ?>" />
                </div>

                <div class="oso-filter-field">
                    <label for="location"><?php esc_html_e( 'Location', 'oso-employer-portal' ); ?></label>
                    <select id="location" name="location">
                        <option value=""><?php esc_html_e( 'All States', 'oso-employer-portal' ); ?></option>
                        <?php
                        if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
                            $states = OSO_Jobs_Utilities::get_states();
                            $selected_location = isset( $_GET['location'] ) ? $_GET['location'] : '';
                            foreach ( $states as $state ) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr( $state ),
                                    selected( $selected_location, $state, false ),
                                    esc_html( $state )
                                );
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="oso-filter-field">
                    <label for="sort"><?php esc_html_e( 'Sort By', 'oso-employer-portal' ); ?></label>
                    <select id="sort" name="sort">
                        <option value="date_desc" <?php selected( isset( $_GET['sort'] ) ? $_GET['sort'] : '', 'date_desc' ); ?>><?php esc_html_e( 'Newest First', 'oso-employer-portal' ); ?></option>
                        <option value="date_asc" <?php selected( isset( $_GET['sort'] ) ? $_GET['sort'] : '', 'date_asc' ); ?>><?php esc_html_e( 'Oldest First', 'oso-employer-portal' ); ?></option>
                        <option value="name_asc" <?php selected( isset( $_GET['sort'] ) ? $_GET['sort'] : '', 'name_asc' ); ?>><?php esc_html_e( 'Name (A-Z)', 'oso-employer-portal' ); ?></option>
                        <option value="name_desc" <?php selected( isset( $_GET['sort'] ) ? $_GET['sort'] : '', 'name_desc' ); ?>><?php esc_html_e( 'Name (Z-A)', 'oso-employer-portal' ); ?></option>
                    </select>
                </div>

                <div class="oso-filter-field oso-filter-actions">
                    <button type="submit" class="oso-btn oso-btn-primary"><?php esc_html_e( 'Apply Filters', 'oso-employer-portal' ); ?></button>
                    <a href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>" class="oso-btn oso-btn-secondary"><?php esc_html_e( 'Clear', 'oso-employer-portal' ); ?></a>
                </div>
            </div>

            <!-- Advanced Filters Toggle -->
            <div class="oso-advanced-toggle">
                <button type="button" id="toggle-advanced" class="oso-toggle-btn">
                    <span class="toggle-text"><?php esc_html_e( 'Show Advanced Filters', 'oso-employer-portal' ); ?></span>
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
            </div>

            <div class="oso-advanced-filters" id="advanced-filters" style="display: none;">
                <?php
                if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
                    $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
                    
                    foreach ( $checkbox_groups as $key => $config ) :
                        $selected_values = isset( $_GET[ $key ] ) ? (array) $_GET[ $key ] : array();
                        ?>
                        <div class="oso-filter-group">
                            <h4><?php echo esc_html( $config['label'] ); ?></h4>
                            <div class="oso-checkbox-grid">
                                <?php foreach ( $config['options'] as $option ) : ?>
                                    <label class="oso-checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            name="<?php echo esc_attr( $key ); ?>[]" 
                                            value="<?php echo esc_attr( $option ); ?>"
                                            <?php checked( in_array( $option, $selected_values, true ) ); ?>
                                        />
                                        <span><?php echo esc_html( $option ); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach;
                }
                ?>
            </div>
        </form>
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
                
                // Get "Why interested" text from post_content
                $jobseeker = get_post( $jobseeker_id );
                $why_interested = '';
                if ( ! empty( $jobseeker->post_content ) ) {
                    $why_interested = wp_trim_words( wp_strip_all_tags( $jobseeker->post_content ), 20, '...' );
                }
                
                // Get job interests for badges - from meta field, NOT post_content
                $job_interests_raw = ! empty( $meta['_oso_jobseeker_job_interests'] ) ? $meta['_oso_jobseeker_job_interests'] : '';
                $job_interests = array();
                if ( class_exists( 'OSO_Jobs_Utilities' ) && ! empty( $job_interests_raw ) ) {
                    $job_interests = OSO_Jobs_Utilities::meta_string_to_array( $job_interests_raw );
                    // Filter out any empty values
                    $job_interests = array_filter( $job_interests );
                }
                
                // Build profile URL - use dedicated profile page
                $profile_url = add_query_arg( 'jobseeker_id', $jobseeker_id, home_url( '/jobseeker-profile/' ) );
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
                        
                        <?php if ( $why_interested ) : ?>
                            <p class="oso-card-text">
                                <?php echo esc_html( $why_interested ); ?>
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
