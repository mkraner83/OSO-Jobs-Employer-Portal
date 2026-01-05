<?php
/**
 * Public Jobs Template - Public job listings for unregistered users (no contact info)
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
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

// Filter by search term
if ( ! empty( $search ) ) {
    $args['s'] = $search;
}

// Filter by location
if ( ! empty( $location ) ) {
    $args['meta_query'][] = array(
        'key'     => '_oso_job_location',
        'value'   => $location,
        'compare' => 'LIKE',
    );
}

// Filter by job type
if ( ! empty( $job_type_filter ) ) {
    $args['meta_query'][] = array(
        'key'   => '_oso_job_type',
        'value' => $job_type_filter,
    );
}

// Sort options
switch ( $sort ) {
    case 'newest':
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
    case 'oldest':
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
        break;
    case 'title':
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
}

// Query jobs
$jobs_query = new WP_Query( $args );
?>

<div class="oso-public-jobs">
    <div class="oso-public-jobs-header">
        <h2><?php esc_html_e( 'Summer Camp Jobs', 'oso-employer-portal' ); ?></h2>
        <p class="oso-public-jobs-subtitle">
            <?php esc_html_e( 'Discover amazing summer camp opportunities across the country', 'oso-employer-portal' ); ?>
        </p>
    </div>

    <!-- Filters -->
    <div class="oso-public-jobs-filters">
        <form method="get" class="oso-filter-form">
            <div class="oso-filter-row">
                <div class="oso-filter-field">
                    <label for="search"><?php esc_html_e( 'Search', 'oso-employer-portal' ); ?></label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="<?php echo esc_attr( $search ); ?>" 
                        placeholder="<?php esc_attr_e( 'Search jobs...', 'oso-employer-portal' ); ?>"
                    />
                </div>

                <div class="oso-filter-field">
                    <label for="location"><?php esc_html_e( 'Location', 'oso-employer-portal' ); ?></label>
                    <select id="location" name="location">
                        <option value=""><?php esc_html_e( 'All Locations', 'oso-employer-portal' ); ?></option>
                        <?php foreach ( $states as $state ) : ?>
                            <option value="<?php echo esc_attr( $state ); ?>" <?php selected( $location, $state ); ?>>
                                <?php echo esc_html( $state ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="oso-filter-field">
                    <label for="job_type"><?php esc_html_e( 'Job Type', 'oso-employer-portal' ); ?></label>
                    <select id="job_type" name="job_type">
                        <option value=""><?php esc_html_e( 'All Types', 'oso-employer-portal' ); ?></option>
                        <?php foreach ( $job_types as $type ) : ?>
                            <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $job_type_filter, $type ); ?>>
                                <?php echo esc_html( $type ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="oso-filter-field">
                    <label for="sort"><?php esc_html_e( 'Sort By', 'oso-employer-portal' ); ?></label>
                    <select id="sort" name="sort">
                        <option value="newest" <?php selected( $sort, 'newest' ); ?>><?php esc_html_e( 'Newest First', 'oso-employer-portal' ); ?></option>
                        <option value="oldest" <?php selected( $sort, 'oldest' ); ?>><?php esc_html_e( 'Oldest First', 'oso-employer-portal' ); ?></option>
                        <option value="title" <?php selected( $sort, 'title' ); ?>><?php esc_html_e( 'Title A-Z', 'oso-employer-portal' ); ?></option>
                    </select>
                </div>

                <div class="oso-filter-actions">
                    <button type="submit" class="oso-btn oso-btn-primary">
                        <?php esc_html_e( 'Apply Filters', 'oso-employer-portal' ); ?>
                    </button>
                    <a href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>" class="oso-btn oso-btn-secondary">
                        <?php esc_html_e( 'Clear', 'oso-employer-portal' ); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Jobs Grid -->
    <?php if ( $jobs_query->have_posts() ) : ?>
        <div class="oso-public-jobs-count">
            <p><?php printf( esc_html__( 'Showing %d jobs', 'oso-employer-portal' ), $jobs_query->found_posts ); ?></p>
        </div>

        <div class="oso-public-jobs-grid">
            <?php while ( $jobs_query->have_posts() ) : $jobs_query->the_post(); ?>
                <?php
                $job_id = get_the_ID();
                $employer_id = get_post_meta( $job_id, '_oso_job_employer_id', true );
                $camp_name = $employer_id ? get_post_meta( $employer_id, '_oso_employer_company', true ) : '';
                $job_type = get_post_meta( $job_id, '_oso_job_type', true );
                $job_location = get_post_meta( $job_id, '_oso_job_location', true );
                $start_date = get_post_meta( $job_id, '_oso_job_start_date', true );
                $end_date = get_post_meta( $job_id, '_oso_job_end_date', true );
                $compensation = get_post_meta( $job_id, '_oso_job_compensation', true );
                $description = get_post_meta( $job_id, '_oso_job_description', true );
                ?>

                <div class="oso-public-job-card">
                    <div class="oso-job-card-header">
                        <h3 class="oso-job-title"><?php the_title(); ?></h3>
                        <?php if ( $camp_name ) : ?>
                            <p class="oso-job-camp"><?php echo esc_html( $camp_name ); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="oso-job-card-meta">
                        <?php if ( $job_type ) : ?>
                            <div class="oso-job-meta-item">
                                <span class="dashicons dashicons-category"></span>
                                <span><?php echo esc_html( $job_type ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ( $job_location ) : ?>
                            <div class="oso-job-meta-item">
                                <span class="dashicons dashicons-location"></span>
                                <span><?php echo esc_html( $job_location ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ( $start_date && $end_date ) : ?>
                            <div class="oso-job-meta-item">
                                <span class="dashicons dashicons-calendar"></span>
                                <span><?php echo esc_html( date( 'M j', strtotime( $start_date ) ) ); ?> - <?php echo esc_html( date( 'M j, Y', strtotime( $end_date ) ) ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ( $compensation ) : ?>
                            <div class="oso-job-meta-item">
                                <span class="dashicons dashicons-money-alt"></span>
                                <span><?php echo esc_html( $compensation ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ( $description ) : ?>
                        <div class="oso-job-card-description">
                            <?php echo wp_kses_post( wp_trim_words( $description, 30 ) ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="oso-job-card-footer">
                        <?php if ( is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'job_id', $job_id, get_permalink() ) ); ?>" class="oso-btn oso-btn-primary">
                                <?php esc_html_e( 'View Details & Apply', 'oso-employer-portal' ); ?>
                            </a>
                        <?php else : ?>
                            <div class="oso-job-login-prompt">
                                <p><?php esc_html_e( 'Register or login to apply', 'oso-employer-portal' ); ?></p>
                                <div class="oso-job-login-buttons">
                                    <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="oso-btn oso-btn-primary oso-btn-small">
                                        <?php esc_html_e( 'Login', 'oso-employer-portal' ); ?>
                                    </a>
                                    <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="oso-btn oso-btn-secondary oso-btn-small">
                                        <?php esc_html_e( 'Register', 'oso-employer-portal' ); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ( $jobs_query->max_num_pages > 1 ) : ?>
            <div class="oso-pagination">
                <?php
                echo paginate_links( array(
                    'total'     => $jobs_query->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '&laquo; ' . esc_html__( 'Previous', 'oso-employer-portal' ),
                    'next_text' => esc_html__( 'Next', 'oso-employer-portal' ) . ' &raquo;',
                ) );
                ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="oso-no-jobs">
            <div class="oso-no-jobs-icon">
                <span class="dashicons dashicons-search"></span>
            </div>
            <h3><?php esc_html_e( 'No jobs found', 'oso-employer-portal' ); ?></h3>
            <p><?php esc_html_e( 'Try adjusting your filters or check back later for new opportunities.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>
