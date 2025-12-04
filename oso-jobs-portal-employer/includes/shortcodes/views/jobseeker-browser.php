<?php
/**
 * Jobseeker Browser View for Employers
 * 
 * Template for displaying a searchable/filterable list of jobseekers.
 * 
 * @package OSO_Employer_Portal
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get search and filter parameters
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$location = isset( $_GET['location'] ) ? sanitize_text_field( $_GET['location'] ) : '';
$age_group = isset( $_GET['age_group'] ) ? sanitize_text_field( $_GET['age_group'] ) : '';
$camp_role = isset( $_GET['camp_role'] ) ? sanitize_text_field( $_GET['camp_role'] ) : '';
$visa_status = isset( $_GET['visa_status'] ) ? sanitize_text_field( $_GET['visa_status'] ) : '';
$availability_start = isset( $_GET['availability_start'] ) ? sanitize_text_field( $_GET['availability_start'] ) : '';
$availability_end = isset( $_GET['availability_end'] ) ? sanitize_text_field( $_GET['availability_end'] ) : '';
$sort_by = isset( $_GET['sort_by'] ) ? sanitize_text_field( $_GET['sort_by'] ) : 'date_desc';

// Build WP_Query args
$args = array(
    'post_type' => 'oso_jobseeker',
    'post_status' => 'publish',
    'posts_per_page' => 20,
    'meta_query' => array(
        'relation' => 'AND',
    ),
);

// Search by name (post_title)
if ( ! empty( $search ) ) {
    $args['s'] = $search;
}

// Filter by location
if ( ! empty( $location ) ) {
    $args['meta_query'][] = array(
        'key' => '_oso_jobseeker_location',
        'value' => $location,
        'compare' => 'LIKE',
    );
}

// Filter by age group
if ( ! empty( $age_group ) ) {
    $args['meta_query'][] = array(
        'key' => '_oso_jobseeker_age_group',
        'value' => $age_group,
        'compare' => '=',
    );
}

// Filter by camp role
if ( ! empty( $camp_role ) ) {
    $args['meta_query'][] = array(
        'key' => '_oso_jobseeker_camp_role',
        'value' => $camp_role,
        'compare' => 'LIKE',
    );
}

// Filter by visa status
if ( ! empty( $visa_status ) ) {
    $args['meta_query'][] = array(
        'key' => '_oso_jobseeker_visa_status',
        'value' => $visa_status,
        'compare' => '=',
    );
}

// Filter by availability start date
if ( ! empty( $availability_start ) ) {
    $args['meta_query'][] = array(
        'key' => '_oso_jobseeker_availability_start',
        'value' => $availability_start,
        'compare' => '<=',
        'type' => 'DATE',
    );
}

// Filter by availability end date
if ( ! empty( $availability_end ) ) {
    $args['meta_query'][] = array(
        'key' => '_oso_jobseeker_availability_end',
        'value' => $availability_end,
        'compare' => '>=',
        'type' => 'DATE',
    );
}

// Sorting
switch ( $sort_by ) {
    case 'name_asc':
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
    case 'name_desc':
        $args['orderby'] = 'title';
        $args['order'] = 'DESC';
        break;
    case 'date_asc':
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
        break;
    case 'date_desc':
    default:
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
}

// Execute query
$jobseekers = new WP_Query( $args );

?>

<div class="oso-jobseeker-browser">
    
    <!-- Search and Filter Form -->
    <form class="oso-filter-form" method="get" action="">
        
        <div class="oso-filter-basic">
            <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search by name..." class="oso-search-input" />
            
            <select name="sort_by" class="oso-sort-select">
                <option value="date_desc" <?php selected( $sort_by, 'date_desc' ); ?>>Newest First</option>
                <option value="date_asc" <?php selected( $sort_by, 'date_asc' ); ?>>Oldest First</option>
                <option value="name_asc" <?php selected( $sort_by, 'name_asc' ); ?>>Name (A-Z)</option>
                <option value="name_desc" <?php selected( $sort_by, 'name_desc' ); ?>>Name (Z-A)</option>
            </select>
            
            <button type="submit" class="oso-btn oso-btn-primary">Search</button>
            <button type="button" class="oso-btn oso-btn-secondary oso-toggle-filters">Advanced Filters</button>
        </div>
        
        <div class="oso-filter-advanced" style="display: none;">
            
            <div class="oso-filter-row">
                <div class="oso-filter-field">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo esc_attr( $location ); ?>" placeholder="City, State, or Country" />
                </div>
                
                <div class="oso-filter-field">
                    <label>Age Group</label>
                    <select name="age_group">
                        <option value="">All Ages</option>
                        <option value="18-25" <?php selected( $age_group, '18-25' ); ?>>18-25</option>
                        <option value="26-35" <?php selected( $age_group, '26-35' ); ?>>26-35</option>
                        <option value="36-45" <?php selected( $age_group, '36-45' ); ?>>36-45</option>
                        <option value="46+" <?php selected( $age_group, '46+' ); ?>>46+</option>
                    </select>
                </div>
            </div>
            
            <div class="oso-filter-row">
                <div class="oso-filter-field">
                    <label>Camp Role</label>
                    <input type="text" name="camp_role" value="<?php echo esc_attr( $camp_role ); ?>" placeholder="e.g., Counselor, Instructor" />
                </div>
                
                <div class="oso-filter-field">
                    <label>Visa Status</label>
                    <select name="visa_status">
                        <option value="">All</option>
                        <option value="us_citizen" <?php selected( $visa_status, 'us_citizen' ); ?>>US Citizen</option>
                        <option value="work_visa" <?php selected( $visa_status, 'work_visa' ); ?>>Work Visa</option>
                        <option value="need_sponsorship" <?php selected( $visa_status, 'need_sponsorship' ); ?>>Need Sponsorship</option>
                    </select>
                </div>
            </div>
            
            <div class="oso-filter-row">
                <div class="oso-filter-field">
                    <label>Available From</label>
                    <input type="date" name="availability_start" value="<?php echo esc_attr( $availability_start ); ?>" />
                </div>
                
                <div class="oso-filter-field">
                    <label>Available Until</label>
                    <input type="date" name="availability_end" value="<?php echo esc_attr( $availability_end ); ?>" />
                </div>
            </div>
            
        </div>
        
    </form>
    
    <!-- Results -->
    <div class="oso-jobseeker-results">
        
        <?php if ( $jobseekers->have_posts() ) : ?>
            
            <p class="oso-results-count">Found <?php echo $jobseekers->found_posts; ?> jobseeker(s)</p>
            
            <div class="oso-jobseeker-grid">
                
                <?php while ( $jobseekers->have_posts() ) : $jobseekers->the_post(); 
                    $jobseeker_id = get_the_ID();
                    $jobseeker_post = get_post( $jobseeker_id );
                    $meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_id );
                    
                    // Get specific fields
                    $name = get_the_title();
                    $age_group = isset( $meta['_oso_jobseeker_age_group'] ) ? $meta['_oso_jobseeker_age_group'] : '';
                    $location = isset( $meta['_oso_jobseeker_location'] ) ? $meta['_oso_jobseeker_location'] : '';
                    $camp_role = isset( $meta['_oso_jobseeker_camp_role'] ) ? $meta['_oso_jobseeker_camp_role'] : '';
                    
                    // Get job interests from meta (these should be badges)
                    $job_interests = isset( $meta['_oso_jobseeker_job_interests'] ) ? $meta['_oso_jobseeker_job_interests'] : array();
                    if ( ! is_array( $job_interests ) ) {
                        $job_interests = array_filter( array_map( 'trim', explode( ',', $job_interests ) ) );
                    }
                    
                    // Get "why interested" from post_content (this should be text)
                    $why_interested = ! empty( $jobseeker_post->post_content ) ? wp_trim_words( $jobseeker_post->post_content, 30 ) : '';
                    
                    // Get profile URL
                    $profile_url = add_query_arg( 'jobseeker_id', $jobseeker_id, get_permalink() );
                    ?>
                    
                    <div class="oso-jobseeker-card">
                        
                        <h3 class="oso-card-name"><?php echo esc_html( $name ); ?></h3>
                        
                        <?php if ( $age_group || $location ) : ?>
                            <div class="oso-card-meta">
                                <?php if ( $age_group ) : ?>
                                    <span class="oso-meta-age"><?php echo esc_html( $age_group ); ?> years</span>
                                <?php endif; ?>
                                <?php if ( $location ) : ?>
                                    <span class="oso-meta-location"><?php echo esc_html( $location ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $camp_role ) : ?>
                            <p class="oso-card-role"><strong>Desired Role:</strong> <?php echo esc_html( $camp_role ); ?></p>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $job_interests ) ) : ?>
                            <div class="oso-card-interests">
                                <strong>Job Interests:</strong>
                                <div class="oso-interest-badges">
                                    <?php foreach ( $job_interests as $interest ) : ?>
                                        <span class="oso-interest-badge"><?php echo esc_html( $interest ); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( $why_interested ) : ?>
                            <div class="oso-card-why">
                                <strong>Why Summer Camp:</strong>
                                <p><?php echo esc_html( $why_interested ); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url( $profile_url ); ?>" class="oso-btn oso-btn-primary oso-btn-block">View Full Profile</a>
                        
                    </div>
                    
                <?php endwhile; ?>
                
            </div>
            
        <?php else : ?>
            
            <p class="oso-no-results">No jobseekers found matching your criteria. Try adjusting your filters.</p>
            
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
        
    </div>
    
</div>
