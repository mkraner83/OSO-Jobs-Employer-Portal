<?php
/**
 * Job Posting Admin Functionality
 *
 * @package OSO_Employer_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin functionality for job posting post type
 */
class OSO_Job_Admin {

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
        add_action( 'init', array( $this, 'remove_job_editor_support' ), 15 );
        add_action( 'add_meta_boxes', array( $this, 'add_job_meta_boxes' ), 20 );
        add_action( 'save_post_oso_job_posting', array( $this, 'save_job_meta' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    }

    /**
     * Remove editor support from job posting post type.
     */
    public function remove_job_editor_support() {
        remove_post_type_support( 'oso_job_posting', 'editor' );
    }

    /**
     * Register job posting meta boxes.
     */
    public function add_job_meta_boxes() {
        remove_meta_box( 'postdivrich', 'oso_job_posting', 'normal' );

        add_meta_box(
            'oso-job-details',
            __( 'Job Details', 'oso-employer-portal' ),
            array( $this, 'render_job_meta_box' ),
            'oso_job_posting',
            'normal',
            'high'
        );
    }

    /**
     * Enqueue admin styles.
     */
    public function enqueue_admin_styles( $hook ) {
        $screen = get_current_screen();
        if ( $screen && 'oso_job_posting' === $screen->post_type ) {
            wp_add_inline_style( 'wp-admin', '
                .oso-job-admin-fields .form-table th { width: 200px; vertical-align: top; padding-top: 15px; }
                .oso-job-admin-fields textarea { width: 100%; max-width: 600px; min-height: 100px; }
                .oso-job-admin-fields input[type="text"],
                .oso-job-admin-fields input[type="number"],
                .oso-job-admin-fields input[type="date"],
                .oso-job-admin-fields select { width: 100%; max-width: 400px; }
                .oso-job-admin-fields .description { margin-top: 5px; }
            ' );
        }
    }

    /**
     * Render job posting meta box UI.
     *
     * @param WP_Post $post Post object.
     */
    public function render_job_meta_box( $post ) {
        wp_nonce_field( 'oso_job_meta', 'oso_job_meta_nonce' );

        $employer_id = get_post_meta( $post->ID, '_oso_job_employer_id', true );
        $job_type = get_post_meta( $post->ID, '_oso_job_type', true );
        $required_skills = get_post_meta( $post->ID, '_oso_job_required_skills', true );
        $start_date = get_post_meta( $post->ID, '_oso_job_start_date', true );
        $end_date = get_post_meta( $post->ID, '_oso_job_end_date', true );
        $compensation = get_post_meta( $post->ID, '_oso_job_compensation', true );
        $positions = get_post_meta( $post->ID, '_oso_job_positions', true );
        $application_instructions = get_post_meta( $post->ID, '_oso_job_application_instructions', true );

        // Get employer info
        $employer_name = '';
        if ( $employer_id ) {
            $employer_post = get_post( $employer_id );
            if ( $employer_post ) {
                $employer_name = $employer_post->post_title;
            }
        }
        ?>
        <div class="oso-job-admin-fields">
            <table class="form-table">
                <tbody>
                    <?php if ( $employer_id ) : ?>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Employer', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <strong><?php echo esc_html( $employer_name ); ?></strong>
                            <input type="hidden" name="employer_id" value="<?php echo esc_attr( $employer_id ); ?>" />
                            <p class="description">
                                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $employer_id . '&action=edit' ) ); ?>" target="_blank">
                                    <?php esc_html_e( 'View Employer Profile', 'oso-employer-portal' ); ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <tr>
                        <th scope="row"><label for="job_description"><?php esc_html_e( 'Job Description', 'oso-employer-portal' ); ?> <span class="required">*</span></label></th>
                        <td>
                            <?php
                            wp_editor(
                                $post->post_content,
                                'content',
                                array(
                                    'textarea_name' => 'content',
                                    'textarea_rows' => 10,
                                    'media_buttons' => false,
                                    'teeny'         => false,
                                    'quicktags'     => true,
                                )
                            );
                            ?>
                            <p class="description"><?php esc_html_e( 'Detailed description of the job position, responsibilities, and requirements.', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="job_type"><?php esc_html_e( 'Job Type', 'oso-employer-portal' ); ?> <span class="required">*</span></label></th>
                        <td>
                            <select id="job_type" name="job_type" required>
                                <option value=""><?php esc_html_e( 'Select Job Type', 'oso-employer-portal' ); ?></option>
                                <option value="Full-Time" <?php selected( $job_type, 'Full-Time' ); ?>><?php esc_html_e( 'Full-Time', 'oso-employer-portal' ); ?></option>
                                <option value="Part-Time" <?php selected( $job_type, 'Part-Time' ); ?>><?php esc_html_e( 'Part-Time', 'oso-employer-portal' ); ?></option>
                                <option value="Seasonal" <?php selected( $job_type, 'Seasonal' ); ?>><?php esc_html_e( 'Seasonal', 'oso-employer-portal' ); ?></option>
                                <option value="Internship" <?php selected( $job_type, 'Internship' ); ?>><?php esc_html_e( 'Internship', 'oso-employer-portal' ); ?></option>
                                <option value="Contract" <?php selected( $job_type, 'Contract' ); ?>><?php esc_html_e( 'Contract', 'oso-employer-portal' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="required_skills"><?php esc_html_e( 'Required Skills/Qualifications', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <textarea id="required_skills" name="required_skills" rows="4"><?php echo esc_textarea( $required_skills ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'List required skills, certifications, or qualifications (one per line).', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="start_date"><?php esc_html_e( 'Start Date', 'oso-employer-portal' ); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" required />
                            <p class="description"><?php esc_html_e( 'When does this position start?', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="end_date"><?php esc_html_e( 'End Date', 'oso-employer-portal' ); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" required />
                            <p class="description"><?php esc_html_e( 'When does this position end? Job will automatically hide after this date.', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="compensation"><?php esc_html_e( 'Compensation', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <input type="text" id="compensation" name="compensation" value="<?php echo esc_attr( $compensation ); ?>" placeholder="<?php esc_attr_e( 'e.g., $15-20/hour, $45,000-55,000/year', 'oso-employer-portal' ); ?>" />
                            <p class="description"><?php esc_html_e( 'Salary, hourly rate, or compensation range.', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="positions"><?php esc_html_e( 'Number of Positions', 'oso-employer-portal' ); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="number" id="positions" name="positions" value="<?php echo esc_attr( $positions ? $positions : 1 ); ?>" min="1" required />
                            <p class="description"><?php esc_html_e( 'How many openings are available for this position?', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="application_instructions"><?php esc_html_e( 'Application Instructions', 'oso-employer-portal' ); ?></label></th>
                        <td>
                            <textarea id="application_instructions" name="application_instructions" rows="4"><?php echo esc_textarea( $application_instructions ); ?></textarea>
                            <p class="description"><?php esc_html_e( 'Any special instructions for applicants (e.g., documents to include, contact information).', 'oso-employer-portal' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Save job posting meta data.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function save_job_meta( $post_id, $post ) {
        // Security checks
        if ( ! isset( $_POST['oso_job_meta_nonce'] ) || ! wp_verify_nonce( $_POST['oso_job_meta_nonce'], 'oso_job_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save job meta fields
        $fields = array(
            '_oso_job_employer_id'               => 'absint',
            '_oso_job_type'                      => 'sanitize_text_field',
            '_oso_job_required_skills'           => 'sanitize_textarea_field',
            '_oso_job_start_date'                => 'sanitize_text_field',
            '_oso_job_end_date'                  => 'sanitize_text_field',
            '_oso_job_compensation'              => 'sanitize_text_field',
            '_oso_job_positions'                 => 'absint',
            '_oso_job_application_instructions'  => 'sanitize_textarea_field',
        );

        foreach ( $fields as $meta_key => $sanitize_callback ) {
            $form_key = str_replace( '_oso_job_', '', $meta_key );
            
            if ( isset( $_POST[ $form_key ] ) ) {
                $value = call_user_func( $sanitize_callback, $_POST[ $form_key ] );
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }
}
