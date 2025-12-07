<?php
/**
 * Job Browser Template - Public job listings (requires login for jobseekers)
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if user is logged in
if ( ! is_user_logged_in() ) {
    $current_url = home_url();
    if ( isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
    }
    ?>
    <div class="oso-job-browser oso-login-required">
        <div class="oso-login-box">
            <div class="oso-login-header">
                <span class="dashicons dashicons-lock"></span>
                <h3><?php esc_html_e( 'Login Required', 'oso-employer-portal' ); ?></h3>
                <p><?php esc_html_e( 'Please log in to browse available job postings', 'oso-employer-portal' ); ?></p>
            </div>
            
            <div class="oso-login-form">
                <?php
                $login_form = wp_login_form(
                    array(
                        'echo'     => false,
                        'redirect' => esc_url( $current_url ),
                    )
                );
                echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>
            
            <p class="oso-lost-password">
                <a href="<?php echo esc_url( wp_lostpassword_url( $current_url ) ); ?>"><?php esc_html_e( 'Lost your password?', 'oso-employer-portal' ); ?></a>
            </p>
        </div>
    </div>
    <?php
    return;
}

// Get job types for filter
$job_types = array(
    'Counselor',
    'Lifeguard',
    'Arts Instructor',
    'Sports Coach',
    'Waterfront Staff',
    'Kitchen Staff',
    'Maintenance',
    'Administrative',
    'Medical Staff',
    'Program Director'
);

// Get all US states for location filter
$states = array(
    'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware',
    'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky',
    'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi',
    'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico',
    'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania',
    'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
    'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
);

// Get filter parameters
$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
$location = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
$job_type_filter = isset( $_GET['job_type'] ) ? sanitize_text_field( wp_unslash( $_GET['job_type'] ) ) : '';
$sort = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'newest';

// Build query args
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type'      => 'oso_job_posting',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'meta_query'     => array(),
);

// Hide expired jobs
$args['meta_query'][] = array(
    'relation' => 'OR',
    array(
        'key'     => '_oso_job_end_date',
        'value'   => date( 'Y-m-d' ),
        'compare' => '>=',
        'type'    => 'DATE',
    ),
    array(
        'key'     => '_oso_job_end_date',
        'compare' => 'NOT EXISTS',
    ),
);

// Search filter
if ( ! empty( $search ) ) {
    $args['s'] = $search;
}

// Location filter (employer location) - need to get employer IDs by state first
if ( ! empty( $location ) ) {
    $employer_args = array(
        'post_type'      => 'oso_employer',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_oso_employer_state',
                'value'   => $location,
                'compare' => '=',
            ),
        ),
    );
    $employer_ids = get_posts( $employer_args );
    
    if ( ! empty( $employer_ids ) ) {
        $args['meta_query'][] = array(
            'key'     => '_oso_job_employer_id',
            'value'   => $employer_ids,
            'compare' => 'IN',
        );
    } else {
        // No employers in this state, force no results
        $args['post__in'] = array( 0 );
    }
}

// Job type filter
if ( ! empty( $job_type_filter ) ) {
    $args['meta_query'][] = array(
        'key'     => '_oso_job_type',
        'value'   => $job_type_filter,
        'compare' => 'LIKE',
    );
}

// Sorting
switch ( $sort ) {
    case 'oldest':
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
        break;
    case 'title_asc':
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
    case 'title_desc':
        $args['orderby'] = 'title';
        $args['order'] = 'DESC';
        break;
    default: // newest
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
}

$job_query = new WP_Query( $args );

// Get jobseeker info for header
$jobseeker_id = 0;
$current_user_id = get_current_user_id();
if ( $current_user_id ) {
    $jobseeker_posts = get_posts( array(
        'post_type'      => 'oso_jobseeker',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_oso_jobseeker_user_id',
        'meta_value'     => $current_user_id,
    ) );
    if ( ! empty( $jobseeker_posts ) ) {
        $jobseeker_id = $jobseeker_posts[0]->ID;
    }
}

$jobseeker_photo = '';
$jobseeker_name = '';
if ( $jobseeker_id ) {
    $jobseeker_photo = get_post_meta( $jobseeker_id, '_oso_jobseeker_photo', true );
    $jobseeker_name = get_post_meta( $jobseeker_id, '_oso_jobseeker_full_name', true );
    if ( empty( $jobseeker_name ) ) {
        $jobseeker_name = get_the_title( $jobseeker_id );
    }
}
?>

<div class="oso-job-browser">
    <?php if ( $jobseeker_id ) : ?>
    <!-- Jobseeker Header -->
    <div class="oso-employer-header">
        <div class="oso-employer-header-left">
            <?php if ( $jobseeker_photo ) : ?>
                <div class="oso-employer-logo">
                    <img src="<?php echo esc_url( $jobseeker_photo ); ?>" alt="<?php echo esc_attr( $jobseeker_name ); ?>" />
                </div>
            <?php endif; ?>
            <div class="oso-employer-info">
                <h1><?php echo esc_html( $jobseeker_name ); ?></h1>
                <p class="oso-employer-subtitle"><?php esc_html_e( 'Browse All Jobs', 'oso-employer-portal' ); ?></p>
            </div>
        </div>
        <div class="oso-employer-header-right">
            <a href="<?php echo esc_url( home_url( '/job-portal/jobseeker-dashboard/' ) ); ?>" class="oso-btn oso-btn-dashboard">
                <span class="dashicons dashicons-dashboard"></span> <?php esc_html_e( 'Dashboard', 'oso-employer-portal' ); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="oso-job-count-banner">
        <p><?php printf( esc_html__( '%d jobs available', 'oso-employer-portal' ), $job_query->found_posts ); ?></p>
    </div>

    <!-- Filters -->
    <form method="get" class="oso-job-filters" id="job-filter-form">
        <!-- Search Bar - Full Width -->
        <div class="oso-filter-search-row">
            <input 
                type="text" 
                name="search" 
                id="job-search" 
                class="oso-search-input-full"
                placeholder="<?php esc_attr_e( 'Search jobs by keyword...', 'oso-employer-portal' ); ?>"
                value="<?php echo esc_attr( $search ); ?>"
            >
        </div>
        
        <!-- Filter Controls - Spread Across Full Width -->
        <div class="oso-filter-controls-row">
            <!-- Location Filter -->
            <select name="location" id="location-filter" class="oso-filter-select">
                <option value=""><?php esc_html_e( 'All Locations', 'oso-employer-portal' ); ?></option>
                <?php foreach ( $states as $state ) : ?>
                    <option value="<?php echo esc_attr( $state ); ?>" <?php selected( $location, $state ); ?>>
                        <?php echo esc_html( $state ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Job Type Filter -->
            <select name="job_type" id="job-type-filter" class="oso-filter-select">
                <option value=""><?php esc_html_e( 'All Job Types', 'oso-employer-portal' ); ?></option>
                <?php foreach ( $job_types as $type ) : ?>
                    <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $job_type_filter, $type ); ?>>
                        <?php echo esc_html( $type ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Sort -->
            <select name="sort" id="sort-filter" class="oso-filter-select">
                <option value="newest" <?php selected( $sort, 'newest' ); ?>><?php esc_html_e( 'Newest First', 'oso-employer-portal' ); ?></option>
                <option value="oldest" <?php selected( $sort, 'oldest' ); ?>><?php esc_html_e( 'Oldest First', 'oso-employer-portal' ); ?></option>
                <option value="title_asc" <?php selected( $sort, 'title_asc' ); ?>><?php esc_html_e( 'Title (A-Z)', 'oso-employer-portal' ); ?></option>
                <option value="title_desc" <?php selected( $sort, 'title_desc' ); ?>><?php esc_html_e( 'Title (Z-A)', 'oso-employer-portal' ); ?></option>
            </select>

            <button type="submit" class="oso-btn oso-btn-primary"><?php esc_html_e( 'Apply Filters', 'oso-employer-portal' ); ?></button>
            
            <?php if ( ! empty( $search ) || ! empty( $location ) || ! empty( $job_type_filter ) ) : ?>
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="oso-btn oso-btn-secondary"><?php esc_html_e( 'Clear', 'oso-employer-portal' ); ?></a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Job Results -->
    <?php if ( $job_query->have_posts() ) : ?>
        <div class="oso-jobs-grid">
            <?php 
            while ( $job_query->have_posts() ) : 
                $job_query->the_post();
                $job_id = get_the_ID();
                $job_meta = OSO_Job_Manager::instance()->get_job_meta( $job_id );
                $employer_id = ! empty( $job_meta['_oso_job_employer_id'] ) ? $job_meta['_oso_job_employer_id'] : 0;
                
                // Get employer data
                $camp_name = $employer_id ? get_post_meta( $employer_id, '_oso_employer_company', true ) : '';
                $employer_state = $employer_id ? get_post_meta( $employer_id, '_oso_employer_state', true ) : '';
                $employer_logo = $employer_id ? get_post_meta( $employer_id, '_oso_employer_logo', true ) : '';
                
                // Job types
                $job_types_list = ! empty( $job_meta['_oso_job_type'] ) ? explode( "\n", $job_meta['_oso_job_type'] ) : array();
                ?>
                <div class="oso-job-card">
                    <?php if ( $employer_logo ) : ?>
                        <div class="oso-job-logo">
                            <img src="<?php echo esc_url( $employer_logo ); ?>" alt="<?php echo esc_attr( $camp_name ); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="oso-job-title">
                        <a href="<?php echo esc_url( add_query_arg( 'job_id', $job_id, home_url( '/job-portal/job-details/' ) ) ); ?>">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <?php if ( $camp_name ) : ?>
                        <p class="oso-job-employer">
                            <span class="dashicons dashicons-building"></span>
                            <?php echo esc_html( $camp_name ); ?>
                            <?php if ( $employer_state ) : ?>
                                <span class="oso-job-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html( $employer_state ); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ( ! empty( $job_types_list ) ) : ?>
                        <div class="oso-job-types">
                            <?php foreach ( array_slice( $job_types_list, 0, 3 ) as $type ) : ?>
                                <span class="oso-job-type-badge"><?php echo esc_html( trim( $type ) ); ?></span>
                            <?php endforeach; ?>
                            <?php if ( count( $job_types_list ) > 3 ) : ?>
                                <span class="oso-job-type-badge">+<?php echo count( $job_types_list ) - 3; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="oso-job-excerpt">
                        <?php echo wp_trim_words( get_the_content(), 20, '...' ); ?>
                    </div>
                    
                    <div class="oso-job-meta">
                        <?php if ( ! empty( $job_meta['_oso_job_start_date'] ) && ! empty( $job_meta['_oso_job_end_date'] ) ) : ?>
                            <span class="oso-job-dates">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <?php 
                                echo esc_html( date_i18n( 'M j', strtotime( $job_meta['_oso_job_start_date'] ) ) );
                                echo ' - ';
                                echo esc_html( date_i18n( 'M j, Y', strtotime( $job_meta['_oso_job_end_date'] ) ) );
                                ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $job_meta['_oso_job_positions'] ) ) : ?>
                            <span class="oso-job-positions">
                                <span class="dashicons dashicons-groups"></span>
                                <?php 
                                $total = (int) $job_meta['_oso_job_positions'];
                                $available = get_post_meta( $job_id, '_oso_job_positions_available', true );
                                $available = ( $available !== '' ) ? (int) $available : $total;
                                
                                echo esc_html( $total ) . '/' . esc_html( $available ) . ' ';
                                echo esc_html( _n( 'position', 'positions', $total, 'oso-employer-portal' ) );
                                echo ' <span style="color: #28a745;">(' . esc_html( $available ) . ' ' . esc_html__( 'available', 'oso-employer-portal' ) . ')</span>';
                                ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $job_meta['_oso_job_compensation'] ) ) : ?>
                            <span class="oso-job-compensation">
                                <span class="dashicons dashicons-money-alt"></span>
                                <?php echo esc_html( $job_meta['_oso_job_compensation'] ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <a href="<?php echo esc_url( add_query_arg( 'job_id', $job_id, home_url( '/job-portal/job-details/' ) ) ); ?>" class="oso-btn oso-btn-primary oso-btn-full">
                        <?php esc_html_e( 'View Details & Apply', 'oso-employer-portal' ); ?>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ( $job_query->max_num_pages > 1 ) : ?>
            <div class="oso-pagination">
                <?php
                echo paginate_links( array(
                    'base'      => get_pagenum_link( 1 ) . '%_%',
                    'format'    => '?paged=%#%',
                    'current'   => max( 1, $paged ),
                    'total'     => $job_query->max_num_pages,
                    'prev_text' => '&laquo; ' . __( 'Previous', 'oso-employer-portal' ),
                    'next_text' => __( 'Next', 'oso-employer-portal' ) . ' &raquo;',
                ) );
                ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="oso-no-results">
            <p><?php esc_html_e( 'No jobs found matching your criteria.', 'oso-employer-portal' ); ?></p>
            <?php if ( ! empty( $search ) || ! empty( $location ) || ! empty( $job_type_filter ) ) : ?>
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="oso-btn oso-btn-primary">
                    <?php esc_html_e( 'View All Jobs', 'oso-employer-portal' ); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>

<style>
.oso-job-browser {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.oso-browser-header {
    margin-bottom: 30px;
}

.oso-browser-header h2 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 2em;
}

.oso-browser-header p {
    margin: 0;
    color: #666;
    font-size: 1.1em;
}

.oso-job-filters {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    margin-bottom: 30px;
}

.oso-filter-row {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.oso-search-wrapper {
    flex: 1;
}

.oso-search-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
}

.oso-filter-controls {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.oso-filter-select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.95em;
    min-width: 150px;
}

.oso-jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.oso-job-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
}

.oso-job-card:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
}

.oso-job-logo {
    width: 80px;
    height: 80px;
    margin-bottom: 15px;
    border-radius: 4px;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.oso-job-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.oso-job-title {
    margin: 0 0 10px 0;
    font-size: 1.2em;
    line-height: 1.4;
}

.oso-job-title a {
    color: #333;
    text-decoration: none;
}

.oso-job-title a:hover {
    color: #8051B0;
}

.oso-job-employer {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 10px 0;
    font-size: 0.95em;
    color: #666;
}

.oso-job-location {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
}

.oso-job-excerpt {
    margin: 10px 0 15px 0;
    color: #666;
    font-size: 0.9em;
    line-height: 1.6;
    flex-grow: 1;
}

.oso-btn-full {
    width: 100%;
    justify-content: center;
    margin-top: auto;
}

.oso-no-results {
    text-align: center;
    padding: 60px 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.oso-no-results p {
    margin: 0 0 20px 0;
    font-size: 1.1em;
    color: #666;
}

.oso-pagination {
    text-align: center;
    padding: 20px 0;
}

.oso-pagination .page-numbers {
    display: inline-block;
    padding: 8px 15px;
    margin: 0 5px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s;
}

.oso-pagination .page-numbers:hover,
.oso-pagination .page-numbers.current {
    background: #8051B0;
    color: #fff;
    border-color: #8051B0;
}

@media (max-width: 768px) {
    .oso-filter-controls {
        flex-direction: column;
    }
    
    .oso-filter-select,
    .oso-btn {
        width: 100%;
    }
}
</style>
