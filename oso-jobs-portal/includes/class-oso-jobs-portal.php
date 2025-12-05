<?php
/**
 * Core bootstrap for OSO Jobs Portal plugin.
 *
 * @package OSO_Jobs_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class.
 */
class OSO_Jobs_Portal {

    /**
     * Singleton instance.
     *
     * @var OSO_Jobs_Portal
     */
    protected static $instance = null;

    /**
     * Job post type slug.
     */
    const POST_TYPE = 'oso_job';

    /**
     * Employer post type slug.
     */
    const POST_TYPE_EMPLOYER = 'oso_employer';

    /**
     * Jobseeker post type slug.
     */
    const POST_TYPE_JOBSEEKER = 'oso_jobseeker';

    /**
     * Taxonomy slug.
     */
    const TAXONOMY_DEPARTMENT = 'oso_job_department';

    /**
     * Candidate role slug.
     */
    const ROLE_CANDIDATE = 'oso_candidate';

    /**
     * Employer role slug.
     */
    const ROLE_EMPLOYER = 'oso_employer';

    /**
     * Get singleton.
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * OSO_Jobs_Portal constructor.
     */
    protected function __construct() {
        $this->load_dependencies();
        $this->register_hooks();
    }

    /**
     * Load auxiliary classes.
     */
    protected function load_dependencies() {
        require_once OSO_JOBS_PORTAL_DIR . 'includes/helpers/class-oso-jobs-utilities.php';
        require_once OSO_JOBS_PORTAL_DIR . 'includes/helpers/class-oso-jobs-template-loader.php';
        require_once OSO_JOBS_PORTAL_DIR . 'includes/settings/class-oso-jobs-settings.php';
        require_once OSO_JOBS_PORTAL_DIR . 'includes/admin/class-oso-jobs-admin-menu.php';
        require_once OSO_JOBS_PORTAL_DIR . 'includes/shortcodes/class-oso-jobs-shortcodes.php';
        require_once OSO_JOBS_PORTAL_DIR . 'includes/wpforms/class-oso-jobs-wpforms-handler.php';
    }

    /**
     * Register hooks.
     */
    protected function register_hooks() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( $this, 'register_employer_post_type' ) );
        add_action( 'init', array( $this, 'register_jobseeker_post_type' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_action( 'init', array( $this, 'register_roles' ) );
        add_action( 'admin_init', array( 'OSO_Jobs_Settings', 'register_settings' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_jobseeker_meta_boxes' ), 20 );
        add_action( 'save_post_' . self::POST_TYPE_JOBSEEKER, array( $this, 'save_jobseeker_meta' ), 10, 2 );
        add_action( 'admin_notices', array( $this, 'maybe_display_jobseeker_notice' ) );
        add_action( 'admin_head-post.php', array( $this, 'maybe_remove_editor' ) );
        add_filter( 'show_admin_bar', array( $this, 'maybe_hide_admin_bar' ) );
        add_filter( 'manage_edit-' . self::POST_TYPE_JOBSEEKER . '_columns', array( $this, 'jobseeker_admin_columns' ) );
        add_action( 'manage_' . self::POST_TYPE_JOBSEEKER . '_posts_custom_column', array( $this, 'render_jobseeker_admin_column' ), 10, 2 );
        add_filter( 'login_redirect', array( $this, 'redirect_candidate_login' ), 10, 3 );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        OSO_Jobs_Admin_Menu::instance();
        OSO_Jobs_Shortcodes::instance();
        OSO_Jobs_WPForms_Handler::instance();
    }

    /**
     * Activation handler.
     */
    public function activate() {
        $this->register_roles();
        $this->register_post_type();
        $this->register_employer_post_type();
        $this->register_jobseeker_post_type();
        $this->register_taxonomy();
        flush_rewrite_rules();
    }

    /**
     * Deactivation handler.
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Register custom post type.
     */
    public function register_post_type() {
        $labels = array(
            'name'               => 'Jobs',
            'singular_name'      => 'Job',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Job',
            'edit_item'          => 'Edit Job',
            'new_item'           => 'New Job',
            'all_items'          => 'Jobs',
            'view_item'          => 'View Job',
            'search_items'       => 'Search Jobs',
            'not_found'          => 'No jobs found',
            'not_found_in_trash' => 'No jobs found in Trash',
            'menu_name'          => 'Jobs',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => true,
            'rewrite'            => array( 'slug' => 'jobs' ),
            'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
            'show_in_rest'       => true,
            'show_ui'            => false, // Hidden from admin - use oso_job_posting instead
            'show_in_menu'       => false,
        );

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Register employer post type.
     */
    public function register_employer_post_type() {
        $labels = array(
            'name'               => __( 'Employers', 'oso-jobs-portal' ),
            'singular_name'      => __( 'Employer', 'oso-jobs-portal' ),
            'add_new_item'       => __( 'Add New Employer', 'oso-jobs-portal' ),
            'edit_item'          => __( 'Edit Employer', 'oso-jobs-portal' ),
            'view_item'          => __( 'View Employer', 'oso-jobs-portal' ),
            'search_items'       => __( 'Search Employers', 'oso-jobs-portal' ),
            'not_found'          => __( 'No employers found', 'oso-jobs-portal' ),
            'menu_name'          => __( 'Employers', 'oso-jobs-portal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => false,
            'rewrite'            => array( 'slug' => 'employers' ),
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest'       => true,
            'show_in_menu'       => false,
        );

        register_post_type( self::POST_TYPE_EMPLOYER, $args );
    }

    /**
     * Register jobseeker post type.
     */
    public function register_jobseeker_post_type() {
        $labels = array(
            'name'               => __( 'Jobseekers', 'oso-jobs-portal' ),
            'singular_name'      => __( 'Jobseeker', 'oso-jobs-portal' ),
            'add_new_item'       => __( 'Add New Jobseeker', 'oso-jobs-portal' ),
            'edit_item'          => __( 'Edit Jobseeker', 'oso-jobs-portal' ),
            'view_item'          => __( 'View Jobseeker', 'oso-jobs-portal' ),
            'search_items'       => __( 'Search Jobseekers', 'oso-jobs-portal' ),
            'not_found'          => __( 'No jobseekers found', 'oso-jobs-portal' ),
            'menu_name'          => __( 'Jobseekers', 'oso-jobs-portal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => false,
            'rewrite'            => array( 'slug' => 'jobseekers' ),
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'show_in_rest'       => true,
            'show_in_menu'       => false,
        );

        register_post_type( self::POST_TYPE_JOBSEEKER, $args );
    }

    /**
     * Load translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'oso-jobs-portal', false, dirname( OSO_JOBS_PORTAL_BASENAME ) . '/languages' );
    }

    /**
     * Register departments taxonomy.
     */
    public function register_taxonomy() {
        $labels = array(
            'name'          => 'Departments',
            'singular_name' => 'Department',
            'search_items'  => 'Search Departments',
            'all_items'     => 'All Departments',
            'edit_item'     => 'Edit Department',
            'update_item'   => 'Update Department',
            'add_new_item'  => 'Add New Department',
            'menu_name'     => 'Departments',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'department' ),
            'show_in_rest'      => true,
        );

        register_taxonomy( self::TAXONOMY_DEPARTMENT, self::POST_TYPE, $args );
    }

    /**
     * Enqueue admin assets.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( false === strpos( $hook, 'oso-jobs' ) ) {
            return;
        }

        wp_enqueue_style( 'oso-jobs-admin', OSO_JOBS_PORTAL_URL . 'assets/css/admin.css', array(), OSO_JOBS_PORTAL_VERSION );
        wp_enqueue_script( 'oso-jobs-admin', OSO_JOBS_PORTAL_URL . 'assets/js/admin.js', array( 'jquery' ), OSO_JOBS_PORTAL_VERSION, true );
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style( 'oso-jobs-frontend', OSO_JOBS_PORTAL_URL . 'assets/css/frontend.css', array(), OSO_JOBS_PORTAL_VERSION );
        wp_enqueue_script( 'oso-jobs-frontend', OSO_JOBS_PORTAL_URL . 'assets/js/frontend.js', array( 'jquery' ), OSO_JOBS_PORTAL_VERSION, true );
        wp_localize_script(
            'oso-jobs-frontend',
            'OSOJobsProfile',
            array(
                'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
                'nonce'          => wp_create_nonce( 'oso_jobseeker_upload' ),
                'maxResume'      => 5 * 1024 * 1024,
                'maxPhoto'       => 5 * 1024 * 1024,
                'profilePageUrl' => home_url( '/jobseeker-profile/' ),
                'resumeText'     => __( 'Download Resume', 'oso-jobs-portal' ),
            )
        );
    }

    /**
     * Ensure Candidate/Employer roles exist.
     */
    public function register_roles() {
        if ( ! get_role( self::ROLE_CANDIDATE ) ) {
            add_role( self::ROLE_CANDIDATE, __( 'Candidate', 'oso-jobs-portal' ), array( 'read' => true ) );
        }

        if ( ! get_role( self::ROLE_EMPLOYER ) ) {
            add_role( self::ROLE_EMPLOYER, __( 'Employer', 'oso-jobs-portal' ), array( 'read' => true ) );
        }
    }

    /**
     * Customize Jobseeker columns.
     *
     * @param array $columns Columns.
     * @return array
     */
    public function jobseeker_admin_columns( $columns ) {
        unset( $columns['date'] );
        unset( $columns['wpseo-score'] );
        unset( $columns['wpseo-score-readability'] );
        unset( $columns['wpseo-title'] );
        unset( $columns['wpseo-metadesc'] );

        $columns['photo']          = __( 'Photo', 'oso-jobs-portal' );
        $columns['email']          = __( 'Email', 'oso-jobs-portal' );
        $columns['resume']         = __( 'Resume', 'oso-jobs-portal' );
        $columns['job_interests']  = __( 'Job Interests', 'oso-jobs-portal' );
        $columns['availability_1'] = __( 'Earliest Start', 'oso-jobs-portal' );
        $columns['availability_2'] = __( 'Latest End', 'oso-jobs-portal' );
        $columns['date']           = __( 'Date', 'oso-jobs-portal' );
        return $columns;
    }

    /**
     * Render jobseeker column content.
     *
     * @param string $column Column name.
     * @param int    $post_id Post ID.
     */
    public function render_jobseeker_admin_column( $column, $post_id ) {
        switch ( $column ) {
            case 'email':
                $email = get_post_meta( $post_id, '_oso_jobseeker_email', true );
                if ( $email ) {
                    printf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $email ) );
                }
                break;
            case 'resume':
                $resume = get_post_meta( $post_id, '_oso_jobseeker_resume', true );
                if ( $resume ) {
                    printf( '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', esc_url( $resume ), esc_html__( 'View Resume', 'oso-jobs-portal' ) );
                }
                break;
            case 'job_interests':
                $raw      = get_post_meta( $post_id, '_oso_jobseeker_job_interests', true );
                $interests = OSO_Jobs_Utilities::meta_string_to_array( $raw );
                echo esc_html( implode( ', ', $interests ) );
                break;
            case 'availability_1':
                echo esc_html( get_post_meta( $post_id, '_oso_jobseeker_availability_start', true ) );
                break;
            case 'availability_2':
                echo esc_html( get_post_meta( $post_id, '_oso_jobseeker_availability_end', true ) );
                break;
            case 'photo':
                $photo = get_post_meta( $post_id, '_oso_jobseeker_photo', true );
                if ( $photo ) {
                    printf(
                        '<a href="%1$s" class="oso-jobseeker-thumb" target="_blank" rel="noopener noreferrer"><img src="%1$s" alt="" /></a>',
                        esc_url( $photo )
                    );
                }
                break;
        }
    }

    /**
     * Register jobseeker meta box.
     */
    public function add_jobseeker_meta_boxes() {
        remove_meta_box( 'postdivrich', self::POST_TYPE_JOBSEEKER, 'normal' );
        remove_meta_box( 'postexcerpt', self::POST_TYPE_JOBSEEKER, 'normal' );

        add_meta_box(
            'oso-jobseeker-details',
            __( 'Jobseeker Profile & Account', 'oso-jobs-portal' ),
            array( $this, 'render_jobseeker_meta_box' ),
            self::POST_TYPE_JOBSEEKER,
            'normal',
            'high'
        );
    }

    /**
     * Remove classic editor for jobseeker post type.
     */
    public function maybe_remove_editor() {
        $screen = get_current_screen();
        if ( $screen && self::POST_TYPE_JOBSEEKER === $screen->post_type ) {
            remove_post_type_support( self::POST_TYPE_JOBSEEKER, 'editor' );
        }
    }

    /**
     * Hide admin bar for candidate role on front-end.
     *
     * @param bool $show Should show admin bar.
     * @return bool
     */
    public function maybe_hide_admin_bar( $show ) {
        if ( is_admin() ) {
            return $show;
        }

        $user = wp_get_current_user();
        if ( $user && in_array( self::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
            return false;
        }

        return $show;
    }

    /**
     * Redirect candidate role logins to the front-end profile.
     *
     * @param string           $redirect_to Default redirect.
     * @param string           $request Requested redirect.
     * @param WP_User|WP_Error $user Current user.
     * @return string
     */
    public function redirect_candidate_login( $redirect_to, $request, $user ) {
        if ( $user instanceof WP_User && in_array( self::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
            $profile_url = home_url( '/jobseeker-profile/' );
            return $profile_url;
        }

        return $redirect_to;
    }

    /**
     * Render jobseeker meta box UI.
     *
     * @param WP_Post $post Post object.
     */
    public function render_jobseeker_meta_box( $post ) {
        wp_nonce_field( 'oso_jobseeker_meta', 'oso_jobseeker_meta_nonce' );

        $meta        = OSO_Jobs_Utilities::get_jobseeker_meta( $post->ID );
        $text_fields = OSO_Jobs_Utilities::get_jobseeker_text_fields();
        $textareas   = OSO_Jobs_Utilities::get_jobseeker_textareas();
        $user_id     = (int) get_post_meta( $post->ID, '_oso_jobseeker_user_id', true );
        $user        = $user_id ? get_user_by( 'id', $user_id ) : false;
        ?>
        <div class="oso-jobseeker-admin-fields">
            <h3><?php esc_html_e( 'Profile Information', 'oso-jobs-portal' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <?php foreach ( $text_fields as $key => $config ) :
                        $value = isset( $meta[ $config['meta'] ] ) ? $meta[ $config['meta'] ] : '';
                        if ( in_array( $key, array( 'availability_start', 'availability_end' ), true ) ) {
                            $value = OSO_Jobs_Utilities::format_date_for_input( $value );
                        }
                        ?>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $config['label'] ); ?></label></th>
                            <td>
                                <?php if ( 'select' === $config['type'] && ! empty( $config['options'] ) ) : ?>
                                    <select id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>">
                                        <option value=""><?php esc_html_e( 'Select...', 'oso-jobs-portal' ); ?></option>
                                        <?php foreach ( $config['options'] as $option ) : ?>
                                            <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $value, $option ); ?>><?php echo esc_html( $option ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else : ?>
                                    <input type="<?php echo esc_attr( $config['type'] ); ?>" class="regular-text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                                <?php endif; ?>
                                <?php if ( in_array( $key, array( 'resume_url', 'photo_url' ), true ) && $value ) : ?>
                                    <div class="oso-jobseeker-preview">
                                        <?php if ( 'photo_url' === $key ) : ?>
                                            <img src="<?php echo esc_url( $value ); ?>" alt="" />
                                        <?php else : ?>
                                            <span class="dashicons dashicons-media-document"></span>
                                            <a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Resume', 'oso-jobs-portal' ); ?></a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th scope="row"><label for="why"><?php esc_html_e( 'Why Are You Interested in Summer Camp?', 'oso-jobs-portal' ); ?></label></th>
                        <td><textarea class="large-text" rows="4" id="why" name="why"><?php echo esc_textarea( $post->post_content ); ?></textarea></td>
                    </tr>
                    <?php
                    $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
                    foreach ( $checkbox_groups as $key => $config ) :
                        $value      = isset( $meta[ $config['meta'] ] ) ? $meta[ $config['meta'] ] : '';
                        $selections = OSO_Jobs_Utilities::meta_string_to_array( $value );
                        ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $config['label'] ); ?></th>
                            <td class="oso-jobseeker-checkboxes">
                                <?php foreach ( $config['options'] as $option ) : ?>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $option ); ?>" <?php checked( in_array( $option, $selections, true ) ); ?> />
                                        <?php echo esc_html( $option ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3><?php esc_html_e( 'Linked User Account', 'oso-jobs-portal' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <?php if ( $user ) : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'User', 'oso-jobs-portal' ); ?></th>
                            <td>
                                <?php echo esc_html( $user->display_name ); ?>
                                (<?php echo esc_html( $user->user_email ); ?>)
                                <a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>" target="_blank"><?php esc_html_e( 'Edit user', 'oso-jobs-portal' ); ?></a>
                            </td>
                        </tr>
                    <?php else : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'User', 'oso-jobs-portal' ); ?></th>
                            <td><?php esc_html_e( 'No WordPress user is linked. Saving with an email will create one.', 'oso-jobs-portal' ); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><label for="jobseeker_username"><?php esc_html_e( 'Username', 'oso-jobs-portal' ); ?></label></th>
                        <td><input type="text" class="regular-text" id="jobseeker_username" name="jobseeker_username" value="<?php echo esc_attr( $user ? $user->user_login : '' ); ?>" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Save jobseeker metadata and linked user data.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function save_jobseeker_meta( $post_id, $post ) {
        if ( ! isset( $_POST['oso_jobseeker_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oso_jobseeker_meta_nonce'] ) ), 'oso_jobseeker_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( self::POST_TYPE_JOBSEEKER !== $post->post_type ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $text_fields = OSO_Jobs_Utilities::get_jobseeker_text_fields();
        $textareas   = OSO_Jobs_Utilities::get_jobseeker_textareas();

        foreach ( $text_fields as $key => $config ) {
            $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
            if ( 'email' === $key ) {
                $value = sanitize_email( $raw );
            } elseif ( 'resume_url' === $key || 'photo_url' === $key ) {
                $value = esc_url_raw( $raw );
            } elseif ( in_array( $key, array( 'availability_start', 'availability_end' ), true ) ) {
                $value = OSO_Jobs_Utilities::normalize_date_value( $raw );
            } else {
                $value = sanitize_text_field( $raw );
            }
            update_post_meta( $post_id, $config['meta'], $value );
        }

        foreach ( $textareas as $key => $config ) {
            $raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
            $value = sanitize_textarea_field( $raw );
            update_post_meta( $post_id, $config['meta'], $value );
        }

        $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
        foreach ( $checkbox_groups as $key => $config ) {
            $raw = isset( $_POST[ $key ] ) ? (array) $_POST[ $key ] : array();
            $raw = array_map(
                function ( $item ) {
                    return sanitize_text_field( wp_unslash( $item ) );
                },
                $raw
            );
            $value = OSO_Jobs_Utilities::array_to_meta_string( $raw );
            update_post_meta( $post_id, $config['meta'], $value );
        }

        if ( isset( $_POST['why'] ) ) {
            $content = sanitize_textarea_field( wp_unslash( $_POST['why'] ) );
            wp_update_post(
                array(
                    'ID'           => $post_id,
                    'post_content' => $content,
                )
            );
        }

        $meta  = OSO_Jobs_Utilities::get_jobseeker_meta( $post_id );
        $title = $meta['_oso_jobseeker_full_name'] ? $meta['_oso_jobseeker_full_name'] : $post->post_title;
        if ( $meta['_oso_jobseeker_full_name'] && $meta['_oso_jobseeker_location'] ) {
            $title = $meta['_oso_jobseeker_full_name'] . ' — ' . $meta['_oso_jobseeker_location'];
        }
        wp_update_post(
            array(
                'ID'         => $post_id,
                'post_title' => $title,
            )
        );

        $user_id = (int) get_post_meta( $post_id, '_oso_jobseeker_user_id', true );
        $email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $name    = isset( $_POST['full_name'] ) ? sanitize_text_field( wp_unslash( $_POST['full_name'] ) ) : '';

        $user = $user_id ? get_user_by( 'id', $user_id ) : false;
        if ( ! $user && $email ) {
            $username = OSO_Jobs_Utilities::generate_username( $name, $email );
            $password = wp_generate_password( 12, true );
            $user_id  = wp_insert_user(
                array(
                    'user_login'   => $username,
                    'user_email'   => $email,
                    'display_name' => $name,
                    'user_pass'    => $password,
                    'role'         => self::ROLE_CANDIDATE,
                )
            );

            if ( ! is_wp_error( $user_id ) ) {
                $user = get_user_by( 'id', $user_id );
                update_post_meta( $post_id, '_oso_jobseeker_user_id', $user_id );
                update_user_meta( $user_id, '_oso_jobseeker_post_id', $post_id );
                wp_new_user_notification( $user_id, null, 'both' );
                $this->add_jobseeker_notice( 'success', __( 'New user account created for this jobseeker.', 'oso-jobs-portal' ) );
            }
        }

        if ( $user ) {
            if ( ! in_array( self::ROLE_CANDIDATE, (array) $user->roles, true ) ) {
                $user->add_role( self::ROLE_CANDIDATE );
            }

            if ( $email ) {
                wp_update_user(
                    array(
                        'ID'         => $user->ID,
                        'user_email' => $email,
                    )
                );
            }

            if ( $name ) {
                wp_update_user(
                    array(
                        'ID'           => $user->ID,
                        'display_name' => $name,
                    )
                );
            }

            if ( isset( $_POST['jobseeker_username'] ) ) {
                $new_username = sanitize_user( wp_unslash( $_POST['jobseeker_username'] ), true );
                if ( $new_username && $new_username !== $user->user_login ) {
                    if ( username_exists( $new_username ) ) {
                        $this->add_jobseeker_notice( 'error', __( 'Username already exists. Choose another.', 'oso-jobs-portal' ) );
                    } else {
                        global $wpdb;
                        $wpdb->update(
                            $wpdb->users,
                            array(
                                'user_login'    => $new_username,
                                'user_nicename' => sanitize_title( $new_username ),
                            ),
                            array( 'ID' => $user->ID )
                        );
                        clean_user_cache( $user->ID );
                        $this->add_jobseeker_notice( 'success', __( 'Username updated.', 'oso-jobs-portal' ) );
                    }
                }
            }

        }
    }

    /**
     * Store jobseeker notices via transient per user.
     *
     * @param string $type Notice type.
     * @param string $message Message text.
     */
    protected function add_jobseeker_notice( $type, $message ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return;
        }

        $key     = 'oso_jobseeker_notice_' . $user_id;
        $notices = get_transient( $key );
        if ( ! is_array( $notices ) ) {
            $notices = array();
        }

        $notices[] = array(
            'type'    => $type,
            'message' => $message,
        );

        set_transient( $key, $notices, MINUTE_IN_SECONDS );
    }

    /**
     * Output jobseeker admin notices.
     */
    public function maybe_display_jobseeker_notice() {
        if ( ! function_exists( 'get_current_screen' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || self::POST_TYPE_JOBSEEKER !== $screen->post_type ) {
            return;
        }

        $user_id = get_current_user_id();
        $key     = 'oso_jobseeker_notice_' . $user_id;
        $notices = get_transient( $key );

        if ( empty( $notices ) ) {
            return;
        }

        delete_transient( $key );

        foreach ( $notices as $notice ) {
            $class = ( 'error' === $notice['type'] ) ? 'notice notice-error' : 'notice notice-success';
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $notice['message'] ) );
        }
    }
}
