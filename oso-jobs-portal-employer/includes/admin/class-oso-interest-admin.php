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
