<?php
/**
 * Main template.
 *
 * @package SportZone
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="content-area">
    <?php if (have_posts()) : ?>
        <header class="section__head">
            <div>
                <h1 class="page-title"><?php echo esc_html(get_the_archive_title() ?: get_bloginfo('name')); ?></h1>
                <?php if (get_the_archive_description()) : ?>
                    <p><?php echo wp_kses_post(get_the_archive_description()); ?></p>
                <?php endif; ?>
            </div>
        </header>
        <div class="product-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('product-card'); ?>>
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large', ['class' => 'product-card__image']); ?></a>
                    <?php endif; ?>
                    <div class="product-card__body">
                        <span class="product-card__tag"><?php echo esc_html(get_the_date()); ?></span>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <?php the_excerpt(); ?>
                        <a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e('Đọc thêm', 'sportzone'); ?></a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <div class="entry-content">
            <h1><?php esc_html_e('Chưa có nội dung', 'sportzone'); ?></h1>
            <p><?php esc_html_e('Hãy thêm bài viết hoặc sản phẩm đầu tiên trong WordPress admin.', 'sportzone'); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php
get_footer();
