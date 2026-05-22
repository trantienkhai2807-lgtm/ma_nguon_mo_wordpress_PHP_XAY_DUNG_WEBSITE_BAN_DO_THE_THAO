<?php
/**
 * Page template.
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
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
</div>
<?php
get_footer();
