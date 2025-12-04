<?php
/**
 * Employer Dashboard View
 * 
 * Template for the employer dashboard page.
 * 
 * @package OSO_Employer_Portal
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
$employer_name = $current_user->display_name;

?>

<div class="oso-employer-dashboard">
    
    <h1>Welcome, <?php echo esc_html( $employer_name ); ?>!</h1>
    
    <div class="oso-dashboard-grid">
        
        <div class="oso-dashboard-card">
            <h2>Browse Jobseekers</h2>
            <p>Search and filter through available jobseekers to find the perfect match for your summer camp positions.</p>
            <a href="<?php echo esc_url( home_url( '/browse-jobseekers/' ) ); ?>" class="oso-btn oso-btn-primary">View Jobseekers</a>
        </div>
        
        <div class="oso-dashboard-card">
            <h2>Your Profile</h2>
            <p>Manage your employer profile and company information.</p>
            <a href="<?php echo esc_url( home_url( '/employer-profile/' ) ); ?>" class="oso-btn oso-btn-secondary">Edit Profile</a>
        </div>
        
        <div class="oso-dashboard-card">
            <h2>Post a Job</h2>
            <p>Create and manage your summer camp job listings.</p>
            <a href="<?php echo esc_url( home_url( '/post-job/' ) ); ?>" class="oso-btn oso-btn-secondary">Post New Job</a>
        </div>
        
    </div>
    
</div>
