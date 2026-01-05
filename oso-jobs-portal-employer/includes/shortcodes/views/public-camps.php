<?php
/**
 * Public Camps Template - Public camp/employer listings (contact info hidden for unregistered users)
 *
 * @package OSO_Employer_Portal\Shortcodes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
$sort = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'newest';

// Build query args
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type'      => 'oso_employer',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'meta_query'     => array(),
);

// Filter by search term
if ( ! empty( $search ) ) {
    $args['s'] = $search;
}

// Filter by location/state
if ( ! empty( $location ) ) {
    $args['meta_query'][] = array(
        'key'     => '_oso_employer_state',
        'value'   => $location,
        'compare' => '=',
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
    case 'name':
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
}

// Query employers
$employers_query = new WP_Query( $args );
$is_logged_in = is_user_logged_in();
?>

<div class="oso-public-camps">
    <div class="oso-public-camps-header">
        <h2><?php esc_html_e( 'Summer Camps', 'oso-employer-portal' ); ?></h2>
        <p class="oso-public-camps-subtitle">
            <?php esc_html_e( 'Discover amazing summer camps across the country', 'oso-employer-portal' ); ?>
        </p>
    </div>

    <!-- Filters -->
    <div class="oso-public-camps-filters">
        <form method="get" class="oso-filter-form">
            <div class="oso-filter-row">
                <div class="oso-filter-field">
                    <label for="search"><?php esc_html_e( 'Search', 'oso-employer-portal' ); ?></label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        value="<?php echo esc_attr( $search ); ?>" 
                        placeholder="<?php esc_attr_e( 'Search camps...', 'oso-employer-portal' ); ?>"
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
                    <label for="sort"><?php esc_html_e( 'Sort By', 'oso-employer-portal' ); ?></label>
                    <select id="sort" name="sort">
                        <option value="newest" <?php selected( $sort, 'newest' ); ?>><?php esc_html_e( 'Newest First', 'oso-employer-portal' ); ?></option>
                        <option value="oldest" <?php selected( $sort, 'oldest' ); ?>><?php esc_html_e( 'Oldest First', 'oso-employer-portal' ); ?></option>
                        <option value="name" <?php selected( $sort, 'name' ); ?>><?php esc_html_e( 'Name A-Z', 'oso-employer-portal' ); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="oso-filter-actions">
                <button type="submit" class="oso-btn oso-btn-primary">
                    <?php esc_html_e( 'Apply Filters', 'oso-employer-portal' ); ?>
                </button>
                <a href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>" class="oso-btn oso-btn-secondary">
                    <?php esc_html_e( 'Clear', 'oso-employer-portal' ); ?>
                </a>
            </div>
        </form>
    </div>

    <!-- Camps Grid -->
    <?php if ( $employers_query->have_posts() ) : ?>
        <div class="oso-public-camps-count">
            <p><?php printf( esc_html__( 'Showing %d camps', 'oso-employer-portal' ), $employers_query->found_posts ); ?></p>
        </div>

        <div class="oso-public-camps-grid">
            <?php while ( $employers_query->have_posts() ) : $employers_query->the_post(); ?>
                <?php
                $employer_id = get_the_ID();
                $employer_meta = get_post_meta( $employer_id );
                
                $company_name = get_the_title();
                $logo = ! empty( $employer_meta['_oso_employer_logo'][0] ) ? $employer_meta['_oso_employer_logo'][0] : '';
                $description = ! empty( $employer_meta['_oso_employer_description'][0] ) ? $employer_meta['_oso_employer_description'][0] : '';
                $city = ! empty( $employer_meta['_oso_employer_city'][0] ) ? $employer_meta['_oso_employer_city'][0] : '';
                $state = ! empty( $employer_meta['_oso_employer_state'][0] ) ? $employer_meta['_oso_employer_state'][0] : '';
                $website = ! empty( $employer_meta['_oso_employer_website'][0] ) ? $employer_meta['_oso_employer_website'][0] : '';
                $email = ! empty( $employer_meta['_oso_employer_email'][0] ) ? $employer_meta['_oso_employer_email'][0] : '';
                $phone = ! empty( $employer_meta['_oso_employer_phone'][0] ) ? $employer_meta['_oso_employer_phone'][0] : '';
                
                $location = array_filter( array( $city, $state ) );
                $location_str = implode( ', ', $location );
                
                // Count active jobs
                $active_jobs = get_posts( array(
                    'post_type' => 'oso_job_posting',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_oso_job_employer_id',
                            'value' => $employer_id,
                        ),
                        array(
                            'relation' => 'OR',
                            array(
                                'key' => '_oso_job_end_date',
                                'value' => date( 'Y-m-d' ),
                                'compare' => '>=',
                                'type' => 'DATE',
                            ),
                            array(
                                'key' => '_oso_job_end_date',
                                'compare' => 'NOT EXISTS',
                            ),
                        ),
                    ),
                ) );
                $job_count = count( $active_jobs );
                ?>

                <div class="oso-public-camp-card">
                    <div class="oso-camp-card-header">
                        <div class="oso-camp-logo">
                            <?php if ( $logo ) : ?>
                                <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" />
                            <?php else : ?>
                                <div class="oso-logo-placeholder">
                                    <span class="dashicons dashicons-building"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="oso-camp-header-text">
                            <h3 class="oso-camp-name"><?php echo esc_html( $company_name ); ?></h3>
                            <?php if ( $location_str ) : ?>
                                <p class="oso-camp-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html( $location_str ); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ( $description ) : ?>
                        <div class="oso-camp-card-description">
                            <?php echo wp_kses_post( wp_trim_words( $description, 30 ) ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="oso-camp-card-meta">
                        <div class="oso-camp-meta-item">
                            <span class="dashicons dashicons-portfolio"></span>
                            <span><?php printf( esc_html( _n( '%d Active Job', '%d Active Jobs', $job_count, 'oso-employer-portal' ) ), $job_count ); ?></span>
                        </div>
                    </div>

                    <?php if ( $is_logged_in ) : ?>
                        <!-- Contact Information (visible only to logged-in users) -->
                        <div class="oso-camp-contact-info">
                            <h4><?php esc_html_e( 'Contact Information', 'oso-employer-portal' ); ?></h4>
                            <?php if ( $email ) : ?>
                                <div class="oso-camp-contact-item">
                                    <span class="dashicons dashicons-email"></span>
                                    <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                                </div>
                            <?php endif; ?>
                            <?php if ( $phone ) : ?>
                                <div class="oso-camp-contact-item">
                                    <span class="dashicons dashicons-phone"></span>
                                    <a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
                                </div>
                            <?php endif; ?>
                            <?php if ( $website ) : ?>
                                <div class="oso-camp-contact-item">
                                    <span class="dashicons dashicons-admin-site"></span>
                                    <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener"><?php echo esc_html( parse_url( $website, PHP_URL_HOST ) ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <!-- Login prompt for unregistered users -->
                        <div class="oso-camp-login-prompt">
                            <p><?php esc_html_e( 'Register or login to view contact information', 'oso-employer-portal' ); ?></p>
                            <div class="oso-camp-login-buttons">
                                <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="oso-btn oso-btn-primary oso-btn-small">
                                    <?php esc_html_e( 'Login', 'oso-employer-portal' ); ?>
                                </a>
                                <a href="https://osojobs.com/job-portal/jobseeker-registration/" class="oso-btn oso-btn-green oso-btn-small">
                                    <?php esc_html_e( 'Register', 'oso-employer-portal' ); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $job_count > 0 ) : ?>
                        <div class="oso-camp-card-footer">
                            <a href="<?php echo esc_url( home_url( '/job-portal/all-jobs/?employer=' . $employer_id ) ); ?>" class="oso-btn oso-btn-purple-gradient">
                                <?php esc_html_e( 'View Open Positions', 'oso-employer-portal' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php if ( $employers_query->max_num_pages > 1 ) : ?>
            <div class="oso-pagination">
                <?php
                echo paginate_links( array(
                    'total'     => $employers_query->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '&laquo; ' . esc_html__( 'Previous', 'oso-employer-portal' ),
                    'next_text' => esc_html__( 'Next', 'oso-employer-portal' ) . ' &raquo;',
                ) );
                ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="oso-no-camps">
            <div class="oso-no-camps-icon">
                <span class="dashicons dashicons-search"></span>
            </div>
            <h3><?php esc_html_e( 'No camps found', 'oso-employer-portal' ); ?></h3>
            <p><?php esc_html_e( 'Try adjusting your filters or check back later.', 'oso-employer-portal' ); ?></p>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>
