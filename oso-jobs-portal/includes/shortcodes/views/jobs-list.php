<?php
/**
 * Jobs list shortcode view.
 *
 * @var WP_Query $query
 * @var array    $settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="oso-jobs-list">
    <header class="oso-jobs-list__header">
        <h2><?php echo esc_html( $settings['jobs_page_title'] ); ?></h2>
        <p><?php echo wp_kses_post( $settings['jobs_page_content'] ); ?></p>
    </header>

    <?php if ( $query->have_posts() ) : ?>
        <ul class="oso-jobs-list__items">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <li class="oso-jobs-list__item">
                    <article>
                        <h3 class="oso-jobs-list__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <div class="oso-jobs-list__excerpt"><?php the_excerpt(); ?></div>
                        <div class="oso-jobs-list__meta">
                            <?php
                            $terms = get_the_terms( get_the_ID(), OSO_Jobs_Portal::TAXONOMY_DEPARTMENT );
                            if ( $terms && ! is_wp_error( $terms ) ) {
                                $names = wp_list_pluck( $terms, 'name' );
                                echo '<span class="oso-jobs-list__department">' . esc_html( implode( ', ', $names ) ) . '</span>';
                            }
                            ?>
                        </div>
                        <a class="oso-jobs-list__apply button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View & Apply', 'oso-jobs-portal' ); ?></a>
                    </article>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p class="oso-jobs-list__empty"><?php esc_html_e( 'No open positions at this time. Please check back soon.', 'oso-jobs-portal' ); ?></p>
    <?php endif; ?>
</div>
