<?php
/**
 * Employer Interest Admin Management
 *
 * @package OSO_Employer_Portal\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class OSO_Interest_Admin
 */
class OSO_Interest_Admin {

    /**
     * Initialize the admin menu.
     */
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ], 20 );
        
        // Add custom columns to the interests list
        add_filter( 'manage_oso_emp_interest_posts_columns', [ __CLASS__, 'add_custom_columns' ], 10 );
        add_action( 'manage_oso_emp_interest_posts_custom_column', [ __CLASS__, 'render_custom_columns' ], 10, 2 );
        add_filter( 'manage_edit-oso_emp_interest_sortable_columns', [ __CLASS__, 'make_columns_sortable' ] );
        
        // Add meta box to edit page
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
        
        // Remove default editor for this post type
        add_action( 'init', [ __CLASS__, 'remove_post_type_support' ] );
    }
    
    /**
     * Remove post type support for editor and title.
     */
    public static function remove_post_type_support() {
        remove_post_type_support( 'oso_emp_interest', 'editor' );
    }
    
    /**
     * Add custom columns to the interests list.
     */
    public static function add_custom_columns( $columns ) {
        $new_columns = array();
        
        // Keep checkbox
        if ( isset( $columns['cb'] ) ) {
            $new_columns['cb'] = $columns['cb'];
        }
        
        // Add custom columns
        $new_columns['employer'] = __( 'From Employer', 'oso-employer-portal' );
        $new_columns['jobseeker'] = __( 'To Jobseeker', 'oso-employer-portal' );
        $new_columns['message'] = __( 'Message', 'oso-employer-portal' );
        $new_columns['date'] = __( 'Date Sent', 'oso-employer-portal' );
        
        return $new_columns;
    }
    
    /**
     * Render custom column content.
     */
    public static function render_custom_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'employer':
                $employer_id = get_post_meta( $post_id, '_oso_employer_id', true );
                if ( $employer_id ) {
                    $employer = get_post( $employer_id );
                    if ( $employer ) {
                        $employer_meta = get_post_meta( $employer_id );
                        $employer_name = ! empty( $employer_meta['_oso_employer_company'][0] ) ? $employer_meta['_oso_employer_company'][0] : $employer->post_title;
                        $employer_email = ! empty( $employer_meta['_oso_employer_email'][0] ) ? $employer_meta['_oso_employer_email'][0] : '';
                        
                        echo '<strong><a href="' . esc_url( get_edit_post_link( $employer_id ) ) . '">' . esc_html( $employer_name ) . '</a></strong><br>';
                        if ( $employer_email ) {
                            echo '<a href="mailto:' . esc_attr( $employer_email ) . '">' . esc_html( $employer_email ) . '</a>';
                        }
                    }
                }
                break;
                
            case 'jobseeker':
                $jobseeker_id = get_post_meta( $post_id, '_oso_jobseeker_id', true );
                if ( $jobseeker_id ) {
                    $jobseeker = get_post( $jobseeker_id );
                    if ( $jobseeker ) {
                        $jobseeker_meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_id );
                        $jobseeker_name = ! empty( $jobseeker_meta['_oso_jobseeker_full_name'] ) ? $jobseeker_meta['_oso_jobseeker_full_name'] : $jobseeker->post_title;
                        $jobseeker_email = ! empty( $jobseeker_meta['_oso_jobseeker_email'] ) ? $jobseeker_meta['_oso_jobseeker_email'] : '';
                        
                        echo '<strong><a href="' . esc_url( get_edit_post_link( $jobseeker_id ) ) . '">' . esc_html( $jobseeker_name ) . '</a></strong><br>';
                        if ( $jobseeker_email ) {
                            echo '<a href="mailto:' . esc_attr( $jobseeker_email ) . '">' . esc_html( $jobseeker_email ) . '</a>';
                        }
                    }
                }
                break;
                
            case 'message':
                $post = get_post( $post_id );
                if ( $post && $post->post_content ) {
                    echo '<div style="max-width: 300px;">' . esc_html( wp_trim_words( $post->post_content, 15, '...' ) ) . '</div>';
                }
                break;
                
            case 'date':
                $interest_date = get_post_meta( $post_id, '_oso_interest_date', true );
                if ( $interest_date ) {
                    echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $interest_date ) ) );
                } else {
                    $post = get_post( $post_id );
                    echo esc_html( get_the_date( '', $post ) );
                }
                break;
        }
    }
    
    /**
     * Make custom columns sortable.
     */
    public static function make_columns_sortable( $columns ) {
        $columns['date'] = 'date';
        return $columns;
    }
    
    /**
     * Add meta boxes to the edit page.
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'oso_interest_details',
            __( 'Interest Details', 'oso-employer-portal' ),
            [ __CLASS__, 'render_interest_details_meta_box' ],
            'oso_emp_interest',
            'normal',
            'high'
        );
        
        // Add CSS to hide title field
        add_action( 'admin_head', [ __CLASS__, 'add_admin_css' ] );
    }
    
    /**
     * Add admin CSS to hide title field for interests.
     */
    public static function add_admin_css() {
        global $post_type;
        if ( 'oso_emp_interest' === $post_type ) {
            ?>
            <style>
                #titlediv {
                    display: none;
                }
                #oso_interest_details {
                    margin-top: 20px;
                }
                #oso_interest_details .inside {
                    margin: 0;
                    padding: 0;
                }
            </style>
            <?php
        }
    }
    
    /**
     * Render interest details meta box.
     */
    public static function render_interest_details_meta_box( $post ) {
        $employer_id = get_post_meta( $post->ID, '_oso_employer_id', true );
        $jobseeker_id = get_post_meta( $post->ID, '_oso_jobseeker_id', true );
        $interest_date = get_post_meta( $post->ID, '_oso_interest_date', true );
        $interest_status = get_post_meta( $post->ID, '_oso_interest_status', true );
        
        $employer = get_post( $employer_id );
        $jobseeker = get_post( $jobseeker_id );
        
        ?>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'From Employer:', 'oso-employer-portal' ); ?></th>
                <td>
                    <?php if ( $employer ) :
                        $employer_meta = get_post_meta( $employer_id );
                        $employer_name = ! empty( $employer_meta['_oso_employer_company'][0] ) ? $employer_meta['_oso_employer_company'][0] : $employer->post_title;
                        $employer_email = ! empty( $employer_meta['_oso_employer_email'][0] ) ? $employer_meta['_oso_employer_email'][0] : '';
                        $employer_phone = ! empty( $employer_meta['_oso_employer_phone'][0] ) ? $employer_meta['_oso_employer_phone'][0] : '';
                        ?>
                        <strong><?php echo esc_html( $employer_name ); ?></strong><br>
                        <?php if ( $employer_email ) : ?>
                            Email: <a href="mailto:<?php echo esc_attr( $employer_email ); ?>"><?php echo esc_html( $employer_email ); ?></a><br>
                        <?php endif; ?>
                        <?php if ( $employer_phone ) : ?>
                            Phone: <?php echo esc_html( $employer_phone ); ?><br>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( get_edit_post_link( $employer_id ) ); ?>" class="button button-small">
                            <?php esc_html_e( 'View Employer Profile', 'oso-employer-portal' ); ?>
                        </a>
                    <?php else : ?>
                        <em><?php esc_html_e( 'Employer not found', 'oso-employer-portal' ); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'To Jobseeker:', 'oso-employer-portal' ); ?></th>
                <td>
                    <?php if ( $jobseeker ) :
                        $jobseeker_meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_id );
                        $jobseeker_name = ! empty( $jobseeker_meta['_oso_jobseeker_full_name'] ) ? $jobseeker_meta['_oso_jobseeker_full_name'] : $jobseeker->post_title;
                        $jobseeker_email = ! empty( $jobseeker_meta['_oso_jobseeker_email'] ) ? $jobseeker_meta['_oso_jobseeker_email'] : '';
                        $jobseeker_location = ! empty( $jobseeker_meta['_oso_jobseeker_location'] ) ? $jobseeker_meta['_oso_jobseeker_location'] : '';
                        ?>
                        <strong><?php echo esc_html( $jobseeker_name ); ?></strong><br>
                        <?php if ( $jobseeker_email ) : ?>
                            Email: <a href="mailto:<?php echo esc_attr( $jobseeker_email ); ?>"><?php echo esc_html( $jobseeker_email ); ?></a><br>
                        <?php endif; ?>
                        <?php if ( $jobseeker_location ) : ?>
                            Location: <?php echo esc_html( $jobseeker_location ); ?><br>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( get_edit_post_link( $jobseeker_id ) ); ?>" class="button button-small">
                            <?php esc_html_e( 'View Jobseeker Profile', 'oso-employer-portal' ); ?>
                        </a>
                    <?php else : ?>
                        <em><?php esc_html_e( 'Jobseeker not found', 'oso-employer-portal' ); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Date Sent:', 'oso-employer-portal' ); ?></th>
                <td>
                    <?php
                    if ( $interest_date ) {
                        echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $interest_date ) ) );
                    } else {
                        echo esc_html( get_the_date( '', $post ) );
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Status:', 'oso-employer-portal' ); ?></th>
                <td>
                    <?php echo esc_html( ucfirst( $interest_status ? $interest_status : 'sent' ) ); ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Message:', 'oso-employer-portal' ); ?></th>
                <td>
                    <div style="padding: 15px; background: #f9f9f9; border-left: 3px solid #4A7477; line-height: 1.6;">
                        <?php echo esc_html( $post->post_content ); ?>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Add admin menu items.
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'oso-jobs-dashboard',
            __( 'Express Interests', 'oso-employer-portal' ),
            __( 'Express Interests', 'oso-employer-portal' ),
            'manage_options',
            'oso-employer-interests',
            [ __CLASS__, 'render_interests_page' ]
        );
    }

    /**
     * Render the interests management page.
     */
    public static function render_interests_page() {
        // Get all interests
        $interests = get_posts( array(
            'post_type'      => 'oso_emp_interest',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Express Interests', 'oso-employer-portal' ); ?></h1>
            <p><?php esc_html_e( 'View all employer interests expressed to jobseekers.', 'oso-employer-portal' ); ?></p>

            <?php if ( ! empty( $interests ) ) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Date', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Employer', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Jobseeker', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Message', 'oso-employer-portal' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'oso-employer-portal' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $interests as $interest ) :
                            $employer_id = get_post_meta( $interest->ID, '_oso_employer_id', true );
                            $jobseeker_id = get_post_meta( $interest->ID, '_oso_jobseeker_id', true );
                            $interest_date = get_post_meta( $interest->ID, '_oso_interest_date', true );
                            $message = $interest->post_content;

                            $employer = get_post( $employer_id );
                            $jobseeker = get_post( $jobseeker_id );

                            if ( ! $employer || ! $jobseeker ) {
                                continue;
                            }

                            $employer_meta = get_post_meta( $employer_id );
                            $jobseeker_meta = OSO_Jobs_Utilities::get_jobseeker_meta( $jobseeker_id );

                            $employer_name = ! empty( $employer_meta['_oso_employer_company'][0] ) ? $employer_meta['_oso_employer_company'][0] : $employer->post_title;
                            $jobseeker_name = ! empty( $jobseeker_meta['_oso_jobseeker_full_name'] ) ? $jobseeker_meta['_oso_jobseeker_full_name'] : $jobseeker->post_title;
                            $employer_email = ! empty( $employer_meta['_oso_employer_email'][0] ) ? $employer_meta['_oso_employer_email'][0] : '';
                            $jobseeker_email = ! empty( $jobseeker_meta['_oso_jobseeker_email'] ) ? $jobseeker_meta['_oso_jobseeker_email'] : '';
                            ?>
                            <tr>
                                <td>
                                    <?php
                                    if ( $interest_date ) {
                                        echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $interest_date ) ) );
                                    } else {
                                        echo esc_html( get_the_date( '', $interest->ID ) );
                                    }
                                    ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $employer_name ); ?></strong><br>
                                    <?php if ( $employer_email ) : ?>
                                        <a href="mailto:<?php echo esc_attr( $employer_email ); ?>"><?php echo esc_html( $employer_email ); ?></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html( $jobseeker_name ); ?></strong><br>
                                    <?php if ( $jobseeker_email ) : ?>
                                        <a href="mailto:<?php echo esc_attr( $jobseeker_email ); ?>"><?php echo esc_html( $jobseeker_email ); ?></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html( wp_trim_words( $message, 20, '...' ) ); ?>
                                    <?php if ( str_word_count( $message ) > 20 ) : ?>
                                        <button type="button" class="button button-small" onclick="alert('<?php echo esc_js( $message ); ?>')">
                                            <?php esc_html_e( 'Read Full', 'oso-employer-portal' ); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( get_edit_post_link( $employer_id ) ); ?>" class="button button-small">
                                        <?php esc_html_e( 'View Employer', 'oso-employer-portal' ); ?>
                                    </a>
                                    <a href="<?php echo esc_url( get_edit_post_link( $jobseeker_id ) ); ?>" class="button button-small">
                                        <?php esc_html_e( 'View Jobseeker', 'oso-employer-portal' ); ?>
                                    </a>
                                    <a href="<?php echo esc_url( get_delete_post_link( $interest->ID ) ); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this interest?', 'oso-employer-portal' ); ?>')">
                                        <?php esc_html_e( 'Delete', 'oso-employer-portal' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e( 'No interests have been expressed yet.', 'oso-employer-portal' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}
