<?php
/**
 * Diagnostic Tool: Check Jobseeker Meta Data Format
 * 
 * Purpose: Verify how checkbox and textarea data is stored in the database
 * Usage: Access via browser: /wp-content/plugins/oso-jobs-portal/debug-jobseeker-meta.php
 * 
 * IMPORTANT: Delete this file after debugging!
 */

// Load WordPress
require_once( '../../../wp-load.php' );

// Security check
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied. Admin only.' );
}

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html>
<head>
    <title>OSO Jobseeker Meta Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f0f0f0; }
        h1 { color: #667eea; }
        .jobseeker { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .meta-row { padding: 8px; border-bottom: 1px solid #eee; }
        .meta-key { font-weight: bold; color: #764ba2; width: 300px; display: inline-block; }
        .meta-value { color: #333; }
        .type { color: #999; font-size: 0.9em; }
        .empty { color: #999; font-style: italic; }
        pre { background: #f5f5f5; padding: 10px; border-left: 3px solid #667eea; }
    </style>
</head>
<body>
    <h1>🔍 OSO Jobseeker Meta Data Diagnostic</h1>
    <p>Checking how checkbox and textarea fields are stored in the database...</p>

<?php
// Get recent jobseekers
$jobseekers = get_posts( array(
    'post_type'      => 'oso_jobseeker',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
) );

if ( empty( $jobseekers ) ) {
    echo '<p class="empty">No jobseekers found.</p>';
} else {
    foreach ( $jobseekers as $jobseeker ) {
        $all_meta = get_post_meta( $jobseeker->ID );
        
        echo '<div class="jobseeker">';
        echo '<h2>' . esc_html( get_the_title( $jobseeker->ID ) ) . ' (ID: ' . $jobseeker->ID . ')</h2>';
        echo '<p>Created: ' . get_the_date( 'Y-m-d H:i:s', $jobseeker->ID ) . '</p>';
        
        // Focus on problematic fields
        $focus_fields = array(
            '_oso_jobseeker_over_18',
            '_oso_jobseeker_job_interests',
            '_oso_jobseeker_why_interested',
            '_oso_jobseeker_sports_skills',
            '_oso_jobseeker_arts_skills',
        );
        
        echo '<h3>Problematic Fields:</h3>';
        foreach ( $focus_fields as $key ) {
            $value = get_post_meta( $jobseeker->ID, $key, true );
            
            echo '<div class="meta-row">';
            echo '<span class="meta-key">' . esc_html( $key ) . '</span>';
            echo '<span class="type">(' . gettype( $value ) . ')</span>';
            echo '<br>';
            
            if ( empty( $value ) ) {
                echo '<span class="empty">EMPTY</span>';
            } else {
                if ( is_array( $value ) ) {
                    echo '<pre>' . print_r( $value, true ) . '</pre>';
                } else {
                    // Show with visible newlines
                    $display = str_replace( "\n", "\\n\n", $value );
                    echo '<span class="meta-value">' . esc_html( $display ) . '</span>';
                }
            }
            echo '</div>';
        }
        
        echo '<h3>All Meta Keys:</h3>';
        echo '<pre>';
        foreach ( $all_meta as $key => $values ) {
            if ( strpos( $key, '_oso_jobseeker' ) === 0 ) {
                echo esc_html( $key ) . "\n";
            }
        }
        echo '</pre>';
        
        echo '</div>';
    }
}
?>

    <hr>
    <h2>✅ Next Steps:</h2>
    <ol>
        <li>Check the format of existing working data (Sports Skills, Arts Skills)</li>
        <li>Compare with new data (Over 18, Job Interests)</li>
        <li>Look for differences in:
            <ul>
                <li>Data type (string vs array vs serialized)</li>
                <li>Separator (newline \n vs comma , vs both)</li>
                <li>Empty values (empty string vs null)</li>
            </ul>
        </li>
        <li>Submit a new test registration and check immediately</li>
        <li><strong>DELETE THIS FILE after debugging!</strong></li>
    </ol>

    <hr>
    <p><em>Debug file created: <?php echo __FILE__; ?></em></p>
</body>
</html>
