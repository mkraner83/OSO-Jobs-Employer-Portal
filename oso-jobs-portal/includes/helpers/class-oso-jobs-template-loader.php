<?php
/**
 * Lightweight template loader.
 *
 * @package OSO_Jobs_Portal\Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template loader responsible for locating plugin view files.
 */
class OSO_Jobs_Template_Loader {

    /**
     * Locate a template from theme override or plugin fallback.
     *
     * @param string $template Template relative path inside plugin `includes` directory.
     * @return string
     */
    public static function locate( $template ) {
        $template = ltrim( $template, '/' );
        $theme    = locate_template( 'oso-jobs-portal/' . $template );

        if ( $theme ) {
            return $theme;
        }

        $plugin_path = OSO_JOBS_PORTAL_DIR . $template;
        if ( file_exists( $plugin_path ) ) {
            return $plugin_path;
        }

        return '';
    }

    /**
     * Render template with variables.
     *
     * @param string $template Relative path.
     * @param array  $vars Variables passed to view.
     * @return string
     */
    public static function render( $template, $vars = array() ) {
        $path = self::locate( $template );
        if ( ! $path ) {
            return '';
        }

        ob_start();
        extract( $vars, EXTR_SKIP );
        include $path;
        return ob_get_clean();
    }
}
