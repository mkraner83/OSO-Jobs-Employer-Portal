<?php
/**
 * Employer Admin Functionality
 *
 * @package OSO_Employer_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin functionality for employer post type
 */
class OSO_Employer_Admin {

    /**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return object
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'init', array( $this, 'remove_employer_editor_support' ), 15 );
        add_action( 'add_meta_boxes', array( $this, 'add_employer_meta_boxes' ), 20 );
        add_action( 'save_post_oso_employer', array( $this, 'save_employer_meta' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    }

    /**
     * Remove editor and excerpt support from employer post type.
     */
    public function remove_employer_editor_support() {
        remove_post_type_support( 'oso_employer', 'editor' );
        remove_post_type_support( 'oso_employer', 'excerpt' );
    }

    /**
     * Register employer meta box.
     */
    public function add_employer_meta_boxes() {
        remove_meta_box( 'postdivrich', 'oso_employer', 'normal' );
        remove_meta_box( 'postexcerpt', 'oso_employer', 'normal' );

        add_meta_box(
            'oso-employer-details',
            __( 'Employer Profile & Account', 'oso-employer-portal' ),
            array( $this, 'render_employer_meta_box' ),
            'oso_employer',
            'normal',
            'high'
        );
    }

    /**
     * Enqueue admin styles.
     */
    public function enqueue_admin_styles( $hook ) {
        $screen = get_current_screen();
        if ( $screen && 'oso_employer' === $screen->post_type ) {
            wp_add_inline_style( 'wp-admin', '
                .oso-employer-admin-fields .form-table th { width: 200px; }
                .oso-employer-admin-fields textarea { width: 100%; max-width: 600px; }
                .oso-employer-admin-fields .oso-employer-preview { margin-top: 10px; }
                .oso-employer-admin-fields .oso-employer-preview img { max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; }
                .oso-employer-admin-fields .oso-employer-checkboxes label { display: block; margin: 5px 0; }
                .oso-employer-admin-fields .oso-photos-preview { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; }
                .oso-employer-admin-fields .oso-photos-preview img { width: 100%; height: 150px; object-fit: cover; border: 1px solid #ddd; padding: 5px; }
            ' );
        }
    }

    /**
     * Render employer meta box UI.
     *
     * @param WP_Post $post Post object.
     */
    public function render_employer_meta_box( $post ) {
        wp_nonce_field( 'oso_employer_meta', 'oso_employer_meta_nonce' );

        $meta    = $this->get_employer_meta( $post->ID );
        $user_id = (int) get_post_meta( $post->ID, '_oso_employer_user_id', true );
        $user    = $user_id ? get_user_by( 'id', $user_id ) : false;
        ?>
        <div class="oso-employer-admin-fields">
            <h3><?php esc_html_e( 'Camp Information', 'oso-employer-portal' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="camp_name"><?php esc_html_e( 'Camp Name', 'oso-employer-portal' ); ?> <span class="required">*</span></label></th>
                        <td><input type="text" class="regular-text" id="camp_name" name="camp_name" value="<?php echo esc_attr( $meta['_oso_employer_company'] ); ?>" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email"><?php esc_html_e( 'Contact Email', 'oso-employer-portal' ); ?> <span class="required">*</span></label></th>
                        <td><input type="email" class="regular-text" id="email" name="email" value="<?php echo esc_attr( $meta['_oso_employer_email'] ); ?>" required /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="website"><?php esc_html_e( 'Website', 'oso-employer-portal' ); ?></label></th>
                        <td><input type="url" class="regular-text" id="website" name="website" value="<?php echo esc_attr( $meta['_oso_employer_website'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description"><?php esc_html_e( 'Brief Description', 'oso-employer-portal' ); ?></label></th>
                        <td><textarea class="large-text" rows="4" id="description" name="description"><?php echo esc_textarea( $meta['_oso_employer_description'] ); ?></textarea></td>
                    </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Location', 'oso-employer-portal' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="state"><?php esc_html_e( 'State', 'oso-employer-portal' ); ?></label></th>
                        <td><input type="text" class="regular-text" id="state" name="state" value="<?php echo esc_attr( $meta['_oso_employer_state'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="address"><?php esc_html_e( 'Address', 'oso-employer-portal' ); ?></label></th>
                        <td><input type="text" class="regular-text" id="address" name="address" value="<?php echo esc_attr( $meta['_oso_employer_address'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="major_city"><?php esc_html_e( 'Closest Major City', 'oso-employer-portal' ); ?></label></th>
                        <td><input type="text" class="regular-text" id="major_city" name="major_city" value="<?php echo esc_attr( $meta['_oso_employer_major_city'] ); ?>" /></td>
                    </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Additional Details', 'oso-employer-portal' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="training_start"><?php esc_html_e( 'Start of Staff Training', 'oso-employer-portal' ); ?></label></th>
                        <td><input type="text" class="regular-text" id="training_start" name="training_start" value="<?php echo esc_attr( $meta['_oso_employer_training_start'] ); ?>" placeholder="MM/DD/YYYY" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="housing"><?php esc_html_e( 'Housing Provided', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <select id="housing" name="housing">
                                <option value=""><?php esc_html_e( 'Select...', 'oso-employer-portal' ); ?></option>
                                <option value="Yes" <?php selected( $meta['_oso_employer_housing'], 'Yes' ); ?>><?php esc_html_e( 'Yes', 'oso-employer-portal' ); ?></option>
                                <option value="No" <?php selected( $meta['_oso_employer_housing'], 'No' ); ?>><?php esc_html_e( 'No', 'oso-employer-portal' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="camp_types"><?php esc_html_e( 'Type of Camp', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <?php
                            // Convert newlines to commas for display
                            $camp_types_display = ! empty( $meta['_oso_employer_camp_types'] ) ? str_replace( "\n", ', ', $meta['_oso_employer_camp_types'] ) : '';
                            ?>
                            <input type="text" class="large-text" id="camp_types" name="camp_types" value="<?php echo esc_attr( $camp_types_display ); ?>" />
                            <p class="description"><?php esc_html_e( 'Separate multiple types with commas (e.g., Day Camp, Overnight Camp, Sport Camp)', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="social_links"><?php esc_html_e( 'Social Media Links', 'oso-employer-portal' ); ?></label></th>
                        <td><textarea class="large-text" rows="3" id="social_links" name="social_links"><?php echo esc_textarea( $meta['_oso_employer_social_links'] ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'One URL per line', 'oso-employer-portal' ); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="subscription_type"><?php esc_html_e( 'Subscription Type', 'oso-employer-portal' ); ?></label></th>
                        <td><input type="text" class="regular-text" id="subscription_type" name="subscription_type" value="<?php echo esc_attr( $meta['_oso_employer_subscription_type'] ); ?>" /></td>
                    </tr>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <tr>
                        <th scope="row"><label for="subscription_ends"><?php esc_html_e( 'Subscription Ends', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <input type="date" class="regular-text" id="subscription_ends" name="subscription_ends" value="<?php echo esc_attr( $meta['_oso_employer_subscription_ends'] ); ?>" />
                            <p class="description"><?php esc_html_e( 'Set the subscription expiration date. Leave empty for no expiration. Only administrators can change this.', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="approved"><?php esc_html_e( 'Approved', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="approved" name="approved" value="1" <?php checked( $meta['_oso_employer_approved'], '1' ); ?> />
                                <?php esc_html_e( 'Allow this employer to browse jobseekers', 'oso-employer-portal' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Only administrators can change this setting', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Images', 'oso-employer-portal' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="logo_url"><?php esc_html_e( 'Logo URL', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <input type="url" class="regular-text" id="logo_url" name="logo_url" value="<?php echo esc_attr( $meta['_oso_employer_logo'] ); ?>" />
                            <?php if ( ! empty( $meta['_oso_employer_logo'] ) ) : ?>
                                <div class="oso-employer-preview">
                                    <img src="<?php echo esc_url( $meta['_oso_employer_logo'] ); ?>" alt="Logo" />
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="photos_urls"><?php esc_html_e( 'Photo URLs', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <textarea class="large-text" rows="4" id="photos_urls" name="photos_urls"><?php echo esc_textarea( $meta['_oso_employer_photos'] ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'One URL per line', 'oso-employer-portal' ); ?></p>
                            <?php
                            $photos = ! empty( $meta['_oso_employer_photos'] ) ? explode( "\n", $meta['_oso_employer_photos'] ) : array();
                            if ( ! empty( $photos ) ) :
                            ?>
                                <div class="oso-photos-preview">
                                    <?php foreach ( $photos as $photo_url ) : ?>
                                        <?php if ( ! empty( trim( $photo_url ) ) ) : ?>
                                            <img src="<?php echo esc_url( trim( $photo_url ) ); ?>" alt="Camp Photo" />
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Linked User Account', 'oso-employer-portal' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <?php if ( $user ) : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'User', 'oso-employer-portal' ); ?></th>
                            <td>
                                <?php echo esc_html( $user->display_name ); ?>
                                (<?php echo esc_html( $user->user_email ); ?>)
                                <a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>" target="_blank"><?php esc_html_e( 'Edit user', 'oso-employer-portal' ); ?></a>
                            </td>
                        </tr>
                    <?php else : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'User', 'oso-employer-portal' ); ?></th>
                            <td><?php esc_html_e( 'No WordPress user is linked. Saving with an email will create one.', 'oso-employer-portal' ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Get employer meta data.
     *
     * @param int $post_id Post ID.
     * @return array
     */
    private function get_employer_meta( $post_id ) {
        $fields = array(
            '_oso_employer_company',
            '_oso_employer_email',
            '_oso_employer_website',
            '_oso_employer_description',
            '_oso_employer_camp_types',
            '_oso_employer_state',
            '_oso_employer_address',
            '_oso_employer_major_city',
            '_oso_employer_training_start',
            '_oso_employer_housing',
            '_oso_employer_social_links',
            '_oso_employer_subscription_type',
            '_oso_employer_subscription_ends',
            '_oso_employer_logo',
            '_oso_employer_photos',
            '_oso_employer_approved',
        );

        $meta = array();
        foreach ( $fields as $field ) {
            $meta[ $field ] = get_post_meta( $post_id, $field, true );
        }

        return $meta;
    }

    /**
     * Save employer metadata.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function save_employer_meta( $post_id, $post ) {
        if ( ! isset( $_POST['oso_employer_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oso_employer_meta_nonce'] ) ), 'oso_employer_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( 'oso_employer' !== $post->post_type ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Update post title with camp name
        if ( isset( $_POST['camp_name'] ) ) {
            $camp_name = sanitize_text_field( wp_unslash( $_POST['camp_name'] ) );
            if ( $camp_name !== $post->post_title ) {
                wp_update_post( array(
                    'ID'         => $post_id,
                    'post_title' => $camp_name,
                ) );
            }
            update_post_meta( $post_id, '_oso_employer_company', $camp_name );
        }

        // Save all meta fields
        $fields_to_save = array(
            'email'             => '_oso_employer_email',
            'website'           => '_oso_employer_website',
            'description'       => '_oso_employer_description',
            'camp_types'        => '_oso_employer_camp_types',
            'state'             => '_oso_employer_state',
            'address'           => '_oso_employer_address',
            'major_city'        => '_oso_employer_major_city',
            'training_start'    => '_oso_employer_training_start',
            'housing'           => '_oso_employer_housing',
            'social_links'      => '_oso_employer_social_links',
            'subscription_type' => '_oso_employer_subscription_type',
            'logo_url'          => '_oso_employer_logo',
            'photos_urls'       => '_oso_employer_photos',
        );

        // Handle approved checkbox and subscription ends (only admins can change these)
        if ( current_user_can( 'manage_options' ) ) {
            $approved = isset( $_POST['approved'] ) ? '1' : '0';
            update_post_meta( $post_id, '_oso_employer_approved', $approved );
            
            if ( isset( $_POST['subscription_ends'] ) ) {
                $subscription_ends = sanitize_text_field( wp_unslash( $_POST['subscription_ends'] ) );
                update_post_meta( $post_id, '_oso_employer_subscription_ends', $subscription_ends );
            }
        }

        foreach ( $fields_to_save as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = wp_unslash( $_POST[ $field ] );
                
                // Sanitize based on field type
                if ( in_array( $field, array( 'description', 'social_links', 'photos_urls' ) ) ) {
                    $value = sanitize_textarea_field( $value );
                } elseif ( $field === 'camp_types' ) {
                    // Convert comma-separated list back to newlines for storage
                    $value = sanitize_text_field( $value );
                    $value = str_replace( ', ', "\n", $value );
                    $value = str_replace( ',', "\n", $value );
                } elseif ( in_array( $field, array( 'website', 'logo_url' ) ) ) {
                    $value = esc_url_raw( $value );
                } elseif ( $field === 'email' ) {
                    $value = sanitize_email( $value );
                } else {
                    $value = sanitize_text_field( $value );
                }
                
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }
}
