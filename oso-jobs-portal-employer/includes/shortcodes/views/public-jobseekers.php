<?php
/**
 * Public Jobseekers Listing Template (for unregistered users)
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get filters
$search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
$location = isset( $_GET['location'] ) ? sanitize_text_field( $_GET['location'] ) : '';
$over_18 = isset( $_GET['over_18'] ) ? sanitize_text_field( $_GET['over_18'] ) : '';
$sort = isset( $_GET['sort'] ) ? sanitize_text_field( $_GET['sort'] ) : 'date_desc';
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

// Build query args
$args = array(
    'post_type'      => 'oso_jobseeker',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
);

// Meta query for filters
$meta_query = array( 'relation' => 'AND' );

if ( $location ) {
    $meta_query[] = array(
        'key'     => '_oso_jobseeker_location',
        'value'   => $location,
        'compare' => '=',
    );
}

if ( $over_18 ) {
    $meta_query[] = array(
        'key'     => '_oso_jobseeker_over_18',
        'value'   => $over_18 === 'yes' ? 'Yes' : 'No',
        'compare' => '=',
    );
}

if ( ! empty( $meta_query ) && count( $meta_query ) > 1 ) {
    $args['meta_query'] = $meta_query;
}

// Search
if ( $search ) {
    $args['s'] = $search;
}

// Sorting
switch ( $sort ) {
    case 'date_asc':
        $args['orderby'] = 'date';
        $args['order']   = 'ASC';
        break;
    case 'name_asc':
        $args['orderby'] = 'title';
        $args['order']   = 'ASC';
        break;
    case 'name_desc':
        $args['orderby'] = 'title';
        $args['order']   = 'DESC';
        break;
    case 'date_desc':
    default:
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
        break;
}

$jobseekers = new WP_Query( $args );
?>

<div class="oso-public-jobseekers">
    <!-- Page Header -->
    <div class="oso-public-header">
        <h1><?php esc_html_e( 'Browse Jobseekers', 'oso-employer-portal' ); ?></h1>
        <p class="oso-public-subtitle"><?php esc_html_e( 'Find qualified candidates for your summer camp', 'oso-employer-portal' ); ?></p>
    </div>

    <!-- Search and Filter Form -->
    <div class="oso-filter-section">
        <form method="get" class="oso-filter-form">
            <div class="oso-filter-row oso-filter-row-search">
                <div class="oso-filter-field oso-filter-search-full">
                    <label for="search"><?php esc_html_e( 'Search', 'oso-employer-portal' ); ?></label>
                    <input type="text" id="search" name="search" placeholder="<?php esc_attr_e( 'Search by location...', 'oso-employer-portal' ); ?>" value="<?php echo esc_attr( $search ); ?>" />
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
                            foreach ( $states as $state ) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr( $state ),
                                    selected( $location, $state, false ),
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
                        <option value="yes" <?php selected( $over_18, 'yes' ); ?>><?php esc_html_e( 'Yes', 'oso-employer-portal' ); ?></option>
                        <option value="no" <?php selected( $over_18, 'no' ); ?>><?php esc_html_e( 'No', 'oso-employer-portal' ); ?></option>
                    </select>
                </div>

                <div class="oso-filter-field">
                    <label for="sort"><?php esc_html_e( 'Sort By', 'oso-employer-portal' ); ?></label>
                    <select id="sort" name="sort">
                        <option value="date_desc" <?php selected( $sort, 'date_desc' ); ?>><?php esc_html_e( 'Newest First', 'oso-employer-portal' ); ?></option>
                        <option value="date_asc" <?php selected( $sort, 'date_asc' ); ?>><?php esc_html_e( 'Oldest First', 'oso-employer-portal' ); ?></option>
                        <option value="name_asc" <?php selected( $sort, 'name_asc' ); ?>><?php esc_html_e( 'Name (A-Z)', 'oso-employer-portal' ); ?></option>
                        <option value="name_desc" <?php selected( $sort, 'name_desc' ); ?>><?php esc_html_e( 'Name (Z-A)', 'oso-employer-portal' ); ?></option>
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
        </form>
    </div>

    <!-- Jobseekers Grid -->
    <?php if ( $jobseekers->have_posts() ) : ?>
        <div class="oso-jobseeker-count">
            <p><?php printf( esc_html__( 'Showing %d jobseekers', 'oso-employer-portal' ), $jobseekers->found_posts ); ?></p>
        </div>

        <div class="oso-public-jobseekers-grid">
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
                
                // Get full name and extract first name only
                $full_name = ! empty( $meta['_oso_jobseeker_full_name'] ) ? $meta['_oso_jobseeker_full_name'] : get_the_title();
                $name_parts = explode( ' ', $full_name );
                $first_name = $name_parts[0];
                $display_name = $first_name . ' ***';
                
                $location = ! empty( $meta['_oso_jobseeker_location'] ) ? $meta['_oso_jobseeker_location'] : '';
                $availability_start = ! empty( $meta['_oso_jobseeker_availability_start'] ) ? $meta['_oso_jobseeker_availability_start'] : '';
                $availability_end = ! empty( $meta['_oso_jobseeker_availability_end'] ) ? $meta['_oso_jobseeker_availability_end'] : '';
                
                // Get "Why interested" text from post_content (limited preview)
                $why_text = get_the_content();
                $why_text = wp_strip_all_tags( $why_text );
                if ( strlen( $why_text ) > 100 ) {
                    $why_text = substr( $why_text, 0, 100 ) . '...';
                }
                
                // Get limited skills (first 3 only)
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
                
                // Limit to first 3 skills
                $display_skills = array_slice( $all_skills, 0, 3 );
                $more_skills_count = count( $all_skills ) - 3;
                ?>
                
                <div class="oso-jobseeker-card">
                    <div class="oso-card-photo">
                        <div class="oso-photo-placeholder">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                    </div>
                    
                    <div class="oso-card-content">
                        <h3 class="oso-card-name"><?php echo esc_html( $display_name ); ?></h3>
                        
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
                        
                        <?php if ( $why_text ) : ?>
                            <p class="oso-card-why"><?php echo esc_html( $why_text ); ?></p>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $display_skills ) ) : ?>
                            <div class="oso-card-skills">
                                <?php foreach ( $display_skills as $skill ) : ?>
                                    <span class="oso-skill-badge"><?php echo esc_html( $skill ); ?></span>
                                <?php endforeach; ?>
                                <?php if ( $more_skills_count > 0 ) : ?>
                                    <span class="oso-skill-badge oso-skill-more">+<?php echo esc_html( $more_skills_count ); ?> more</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="oso-card-login-prompt">
                            <p><?php esc_html_e( 'Register or login to view full profile', 'oso-employer-portal' ); ?></p>
                            <div class="oso-login-buttons">
                                <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="oso-btn oso-btn-primary oso-btn-small">
                                    <?php esc_html_e( 'Login', 'oso-employer-portal' ); ?>
                                </a>
                                <a href="https://osojobs.com/job-portal/employer-registration/" class="oso-btn oso-btn-green oso-btn-small">
                                    <?php esc_html_e( 'Register', 'oso-employer-portal' ); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ( $jobseekers->max_num_pages > 1 ) : ?>
            <div class="oso-pagination">
                <?php
                echo paginate_links( array(
                    'total'     => $jobseekers->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '&laquo; ' . __( 'Previous', 'oso-employer-portal' ),
                    'next_text' => __( 'Next', 'oso-employer-portal' ) . ' &raquo;',
                ) );
                ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="oso-no-results">
            <p><?php esc_html_e( 'No jobseekers found matching your criteria.', 'oso-employer-portal' ); ?></p>
            <a href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>" class="oso-btn oso-btn-primary">
                <?php esc_html_e( 'Clear Filters', 'oso-employer-portal' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>
