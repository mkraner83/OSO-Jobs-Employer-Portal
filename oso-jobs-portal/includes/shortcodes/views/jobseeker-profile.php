<?php
/**
 * Jobseeker profile management view.
 *
 * @var bool   $is_logged_in
 * @var array  $meta
 * @var array  $messages
 * @var string $login_form
 * @var string $lost_url
 * @var WP_Post $jobseeker
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( empty( $is_logged_in ) ) : ?>
    <div class="oso-jobseeker-login">
        <p><?php esc_html_e( 'Please log in to manage your jobseeker profile.', 'oso-jobs-portal' ); ?></p>
        <?php echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <p>
            <a href="<?php echo esc_url( $lost_url ); ?>"><?php esc_html_e( 'Forgot your password?', 'oso-jobs-portal' ); ?></a>
        </p>
    </div>
<?php else :
    $fields    = OSO_Jobs_Utilities::get_jobseeker_text_fields();
    $textareas = OSO_Jobs_Utilities::get_jobseeker_textareas();
    ?>
    <div class="oso-jobseeker-profile">
        <?php if ( ! empty( $messages ) ) : ?>
            <div class="oso-jobseeker-profile__notices">
                <?php foreach ( $messages as $message ) : ?>
                    <div class="oso-jobseeker-profile__notice"><?php echo esc_html( $message ); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="oso-jobseeker-profile__form">
            <?php wp_nonce_field( 'oso_jobseeker_profile', 'oso_jobseeker_profile_nonce' ); ?>

            <?php foreach ( $fields as $key => $config ) :
                $value = isset( $meta[ $config['meta'] ] ) ? $meta[ $config['meta'] ] : '';
                if ( in_array( $key, array( 'availability_start', 'availability_end' ), true ) ) {
                    $value = OSO_Jobs_Utilities::format_date_for_input( $value );
                }

                if ( in_array( $key, array( 'resume_url', 'photo_url' ), true ) ) :
                    ?>
                    <div class="oso-jobseeker-field">
                        <label><?php echo esc_html( $config['label'] ); ?></label>
                        <input type="hidden" class="oso-jobseeker-file-url" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                        <div class="oso-jobseeker-preview">
                            <?php if ( 'photo_url' === $key && $value ) : ?>
                                <img src="<?php echo esc_url( $value ); ?>" alt="" />
                            <?php elseif ( 'resume_url' === $key && $value ) : ?>
                                <a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Download Resume', 'oso-jobs-portal' ); ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="oso-jobseeker-file">
                            <input type="file" class="oso-jobseeker-file-input" data-field="<?php echo esc_attr( $key ); ?>" accept="<?php echo 'resume_url' === $key ? 'application/pdf' : 'image/jpeg,image/jpg'; ?>" />
                            <button type="button" class="button oso-jobseeker-delete-file" data-field="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Delete File', 'oso-jobs-portal' ); ?></button>
                        </div>
                        <p class="description"><em><?php echo 'resume_url' === $key ? esc_html__( 'Upload PDF (max 5MB).', 'oso-jobs-portal' ) : esc_html__( 'Upload JPG/JPEG (max 5MB).', 'oso-jobs-portal' ); ?></em></p>
                    </div>
                    <?php
                    continue;
                endif;
                ?>
                <div class="oso-jobseeker-field">
                    <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $config['label'] ); ?></label>
                    <?php if ( isset( $config['type'] ) && 'select' === $config['type'] && ! empty( $config['options'] ) ) : ?>
                        <select id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>">
                            <option value=""><?php esc_html_e( 'Select...', 'oso-jobs-portal' ); ?></option>
                            <?php foreach ( $config['options'] as $option ) : ?>
                                <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $value, $option ); ?>><?php echo esc_html( $option ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <input type="<?php echo esc_attr( isset( $config['type'] ) ? $config['type'] : 'text' ); ?>" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="oso-jobseeker-field">
                <label for="why"><?php esc_html_e( 'Why Are You Interested in Summer Camp?', 'oso-jobs-portal' ); ?></label>
                <textarea id="why" name="why" rows="5" class="large-text"><?php echo esc_textarea( isset( $jobseeker->post_content ) ? $jobseeker->post_content : '' ); ?></textarea>
            </div>

            <?php
            $checkbox_groups = OSO_Jobs_Utilities::get_jobseeker_checkbox_groups();
            foreach ( $checkbox_groups as $key => $config ) :
                $value      = isset( $meta[ $config['meta'] ] ) ? $meta[ $config['meta'] ] : '';
                $selections = OSO_Jobs_Utilities::meta_string_to_array( $value );
                ?>
                <fieldset class="oso-jobseeker-checkbox-group">
                    <legend><?php echo esc_html( $config['label'] ); ?></legend>
                    <?php foreach ( $config['options'] as $option ) : ?>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $option ); ?>" <?php checked( in_array( $option, $selections, true ) ); ?> />
                            <?php echo esc_html( $option ); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>

            <p>
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Update Profile', 'oso-jobs-portal' ); ?></button>
            </p>
        </form>
    </div>
<?php endif; ?>
