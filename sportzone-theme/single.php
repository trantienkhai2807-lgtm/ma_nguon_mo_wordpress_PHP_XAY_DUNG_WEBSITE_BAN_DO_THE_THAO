<?php
/**
 * Single post template.
 *
 * @package SportZone
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="content-area">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('entry-content'); ?>>
            <h1 class="page-title"><?php the_title(); ?></h1>
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large'); ?>
            <?php endif; ?>
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
</div>
<?php
get_footer();
