<?php
/**
 * Header template.
 *
 * @package SportZone
 */

if (!defined('ABSPATH')) {
    exit;
}

$shop_url = class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/?post_type=product');
$cart_url = class_exists('WooCommerce') ? wc_get_cart_url() : home_url('/cart');
$account_url = is_user_logged_in()
    ? (class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url('/my-account'))
    : home_url('/dang-nhap');
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site-shell">
    <header class="site-header market-site-header">
        <div class="market-shell">
            <section class="market-catalog" aria-label="<?php esc_attr_e('Danh mục sản phẩm', 'sportzone'); ?>">
                <nav id="market-catalog-panel" class="market-catalog__panel">
                    <div class="market-catalog__heading">
                        <strong><?php esc_html_e('DANH MỤC', 'sportzone'); ?></strong>
                        <button type="button" aria-controls="market-catalog-panel" aria-expanded="false" aria-label="<?php esc_attr_e('Đóng danh mục', 'sportzone'); ?>">
                            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                        </button>
                    </div>
                    <a href="<?php echo esc_url(sportzone_page_url('quan-ao-the-thao')); ?>">
                        <span class="dashicons dashicons-universal-access-alt" aria-hidden="true"></span>
                        <?php esc_html_e('QUẦN ÁO THỂ THAO', 'sportzone'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                    </a>
                    <a href="<?php echo esc_url(sportzone_page_url('giay-the-thao')); ?>">
                        <span class="dashicons dashicons-performance" aria-hidden="true"></span>
                        <?php esc_html_e('GIÀY THỂ THAO', 'sportzone'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                    </a>
                    <a href="<?php echo esc_url(sportzone_page_url('dung-cu-the-thao')); ?>">
                        <span class="dashicons dashicons-awards" aria-hidden="true"></span>
                        <?php esc_html_e('DỤNG CỤ THỂ THAO', 'sportzone'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                    </a>
                    <a href="<?php echo esc_url(sportzone_page_url('phu-kien-the-thao')); ?>">
                        <span class="dashicons dashicons-admin-tools" aria-hidden="true"></span>
                        <?php esc_html_e('PHỤ KIỆN THỂ THAO', 'sportzone'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                    </a>
                    <a href="<?php echo esc_url(sportzone_page_url('nam')); ?>">
                        <span class="dashicons dashicons-businessman" aria-hidden="true"></span>
                        <?php esc_html_e('NAM', 'sportzone'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                    </a>
                    <a href="<?php echo esc_url(sportzone_page_url('nu')); ?>">
                        <span class="dashicons dashicons-businesswoman" aria-hidden="true"></span>
                        <?php esc_html_e('NỮ', 'sportzone'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                    </a>
                    <a href="<?php echo esc_url(sportzone_page_url('tre-em')); ?>">
                        <span class="dashicons dashicons-groups" aria-hidden="true"></span>
                        <?php esc_html_e('TRẺ EM', 'sportzone'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                    </a>
                    <div class="market-catalog__links">
                        <a href="<?php echo esc_url(sportzone_page_url('gioi-thieu')); ?>"><?php esc_html_e('Về SportZone', 'sportzone'); ?></a>
                        <a href="<?php echo esc_url(sportzone_page_url('chinh-sach-mua-hang')); ?>"><?php esc_html_e('Chính sách mua hàng', 'sportzone'); ?></a>
                        <a href="<?php echo esc_url(sportzone_page_url('huong-dan-mua-hang')); ?>"><?php esc_html_e('Hướng dẫn mua hàng', 'sportzone'); ?></a>
                        <a href="<?php echo esc_url(sportzone_page_url('lien-he')); ?>"><?php esc_html_e('Liên hệ chúng tôi', 'sportzone'); ?></a>
                    </div>
                </nav>
            </section>

            <div class="market-main">
                <button class="market-menu-button" type="button" aria-controls="market-catalog-panel" aria-expanded="false" aria-label="<?php esc_attr_e('Mở menu danh mục', 'sportzone'); ?>">
                    <span class="dashicons dashicons-menu-alt" aria-hidden="true"></span>
                    <small><?php esc_html_e('MENU', 'sportzone'); ?></small>
                </button>

                <a class="market-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?>">
                    <span class="market-brand__mark">SZ</span>
                    <strong><?php bloginfo('name'); ?></strong>
                </a>

                <form class="market-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <label class="screen-reader-text" for="market-search-field"><?php esc_html_e('Tìm sản phẩm', 'sportzone'); ?></label>
                    <input id="market-search-field" type="search" name="s" placeholder="<?php esc_attr_e('Tìm sản phẩm...', 'sportzone'); ?>">
                    <input type="hidden" name="post_type" value="product">
                    <button type="submit" aria-label="<?php esc_attr_e('Tìm kiếm', 'sportzone'); ?>">
                        <span class="dashicons dashicons-search" aria-hidden="true"></span>
                    </button>
                </form>

                <a class="market-info" href="tel:0326033066">
                    <span class="dashicons dashicons-phone" aria-hidden="true"></span>
                    <span><?php esc_html_e('Hotline hỗ trợ', 'sportzone'); ?><strong>0326.033.066</strong></span>
                </a>

                <a class="market-info" href="<?php echo esc_url(sportzone_page_url('he-thong-cua-hang')); ?>">
                    <span class="dashicons dashicons-store" aria-hidden="true"></span>
                    <span><?php esc_html_e('Hệ thống cửa hàng', 'sportzone'); ?><strong><?php esc_html_e('3 cửa hàng', 'sportzone'); ?></strong></span>
                </a>

                <a class="market-info market-account" href="<?php echo esc_url($account_url); ?>">
                    <span class="dashicons dashicons-admin-users" aria-hidden="true"></span>
                    <span><?php esc_html_e('Thông tin', 'sportzone'); ?><strong><?php esc_html_e('Tài khoản', 'sportzone'); ?></strong></span>
                </a>

                <a class="market-cart" href="<?php echo esc_url($cart_url); ?>">
                    <span class="dashicons dashicons-cart" aria-hidden="true"></span>
                    <strong class="sportzone-cart-count"><?php echo esc_html(sportzone_cart_count()); ?></strong>
                    <span><?php esc_html_e('Giỏ hàng', 'sportzone'); ?></span>
                </a>
            </div>
        </div>
    </header>
    <main id="primary" class="site-main">
