<?php
/**
 * WooCommerce template wrapper.
 *
 * @package SportZone
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
$is_catalog = function_exists('is_shop') && is_shop();
$is_catalog = $is_catalog || (function_exists('is_product_category') && is_product_category());
$is_catalog = $is_catalog || (function_exists('is_product_tag') && is_product_tag());
$is_catalog = $is_catalog || (function_exists('is_product_taxonomy') && is_product_taxonomy());
?>
<?php if ($is_catalog) : ?>
    <section class="shop-hero">
        <div class="section__inner">
            <span class="shop-hero__label"><?php esc_html_e('Cửa hàng SportZone', 'sportzone'); ?></span>
            <h1><?php woocommerce_page_title(); ?></h1>
            <p><?php esc_html_e('Chọn nhanh giày, quần áo và phụ kiện thể thao cho tập luyện mỗi ngày.', 'sportzone'); ?></p>
        </div>
    </section>

    <div class="content-area content-area--shop">
        <aside class="shop-sidebar" aria-label="<?php esc_attr_e('Danh mục sản phẩm', 'sportzone'); ?>">
            <h2><?php esc_html_e('Danh mục', 'sportzone'); ?></h2>
            <ul>
                <li><a href="<?php echo esc_url(class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop')); ?>"><?php esc_html_e('Tất cả sản phẩm', 'sportzone'); ?></a></li>
                <?php
                $product_categories = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                    'parent'     => 0,
                ]);

                if (!is_wp_error($product_categories)) :
                    foreach ($product_categories as $product_category) :
                        ?>
                        <li>
                            <a href="<?php echo esc_url(get_term_link($product_category)); ?>">
                                <?php echo esc_html($product_category->name); ?>
                                <span><?php echo esc_html((string) $product_category->count); ?></span>
                            </a>
                        </li>
                        <?php
                    endforeach;
                endif;
                ?>
            </ul>
        </aside>
        <section class="shop-content">
            <?php woocommerce_content(); ?>
        </section>
    </div>
<?php else : ?>
    <div class="content-area content-area--product">
        <?php woocommerce_content(); ?>
    </div>
<?php endif; ?>
<?php
get_footer();
