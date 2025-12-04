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
    <!-- Search and Filter Form -->
    <div class="oso-filter-section">
        <form method="get" class="oso-filter-form" id="jobseeker-filter-form">
            <div class="oso-filter-row oso-filter-row-search">
                <div class="oso-filter-field oso-filter-search-full">
                    <label for="search"><?php esc_html_e( 'Search', 'oso-employer-portal' ); ?></label>
                    <input type="text" id="search" name="search" placeholder="<?php esc_attr_e( 'Name, location, email...', 'oso-employer-portal' ); ?>" value="<?php echo esc_attr( isset( $_GET['search'] ) ? $_GET['search'] : '' ); ?>" />
                </div>
            </div>

            <div class="oso-filter-row oso-filter-row-controls">
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
                    <label for="over_18"><?php esc_html_e( 'Over 18?', 'oso-employer-portal' ); ?></label>
                    <select id="over_18" name="over_18">
                        <option value=""><?php esc_html_e( 'Any', 'oso-employer-portal' ); ?></option>
                        <option value="yes" <?php selected( isset( $_GET['over_18'] ) ? $_GET['over_18'] : '', 'yes' ); ?>><?php esc_html_e( 'Yes', 'oso-employer-portal' ); ?></option>
                        <option value="no" <?php selected( isset( $_GET['over_18'] ) ? $_GET['over_18'] : '', 'no' ); ?>><?php esc_html_e( 'No', 'oso-employer-portal' ); ?></option>
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
                    <label>&nbsp;</label>
                    <div class="oso-action-buttons">
                        <button type="submit" class="oso-btn oso-btn-primary"><?php esc_html_e( 'Apply Filters', 'oso-employer-portal' ); ?></button>
                        <a href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>" class="oso-btn oso-btn-secondary"><?php esc_html_e( 'Clear', 'oso-employer-portal' ); ?></a>
                    </div>
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
                $why_text = get_the_content();
                $why_text = wp_strip_all_tags( $why_text );
                if ( strlen( $why_text ) > 200 ) {
                    $why_text = substr( $why_text, 0, 200 ) . '...';
                }
                
                // Get all skills as badges (excluding "Are You Over 18?")
                $all_skills = array();
                if ( class_exists( 'OSO_Jobs_Utilities' ) ) {
                    $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
                    
                    foreach ( $checkbox_groups as $key => $config ) {
                        // Skip "Are You Over 18?"
                        if ( $key === 'over_18' ) {
                            continue;
                        }
                        
                        $value_raw = ! empty( $meta[ $config['meta'] ] ) ? $meta[ $config['meta'] ] : '';
                        $values = OSO_Jobs_Utilities::meta_string_to_array( $value_raw );
                        
                        if ( ! empty( $values ) ) {
                            $all_skills = array_merge( $all_skills, $values );
                        }
                    }
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
                        
                        <?php if ( ! empty( $why_text ) ) : ?>
                            <div class="oso-card-why">
                                <strong><?php esc_html_e( 'Why Summer Camp:', 'oso-employer-portal' ); ?></strong>
                                <p><?php echo esc_html( $why_text ); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $all_skills ) ) : ?>
                            <div class="oso-card-skills">
                                <?php foreach ( array_slice( $all_skills, 0, 5 ) as $skill ) : ?>
                                    <span class="oso-skill-badge"><?php echo esc_html( $skill ); ?></span>
                                <?php endforeach; ?>
                                <?php if ( count( $all_skills ) > 5 ) : ?>
                                    <span class="oso-skill-more">+<?php echo esc_html( count( $all_skills ) - 5 ); ?> more</span>
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
