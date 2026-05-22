<?php
/**
 * Theme setup and WooCommerce integration.
 *
 * @package SportZone
 */

if (!defined('ABSPATH')) {
    exit;
}

function sportzone_setup(): void
{
    load_theme_textdomain('sportzone', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Menu chính', 'sportzone'),
        'footer'  => __('Menu chân trang', 'sportzone'),
    ]);
}
add_action('after_setup_theme', 'sportzone_setup');

function sportzone_assets(): void
{
    wp_enqueue_style('dashicons');

    wp_enqueue_style(
        'sportzone-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap',
        [],
        null
    );

    wp_enqueue_style('sportzone-style', get_stylesheet_uri(), ['sportzone-fonts'], wp_get_theme()->get('Version'));

    wp_enqueue_script(
        'sportzone-header',
        get_template_directory_uri() . '/assets/js/header.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'sportzone_assets');

function sportzone_page_url(string $slug): string
{
    $page = get_page_by_path($slug);

    if ($page) {
        return get_permalink($page);
    }

    return add_query_arg('pagename', $slug, home_url('/'));
}

function sportzone_excerpt_length(int $length): int
{
    return is_admin() ? $length : 22;
}
add_filter('excerpt_length', 'sportzone_excerpt_length');

function sportzone_woocommerce_columns(): int
{
    return 4;
}
add_filter('loop_shop_columns', 'sportzone_woocommerce_columns');

function sportzone_products_per_page(): int
{
    return 12;
}
add_filter('loop_shop_per_page', 'sportzone_products_per_page');

function sportzone_product_category_badge(): void
{
    if (!function_exists('wc_get_product_category_list')) {
        return;
    }

    global $product;

    if (!$product instanceof WC_Product) {
        return;
    }

    $terms = get_the_terms($product->get_id(), 'product_cat');

    if (empty($terms) || is_wp_error($terms)) {
        return;
    }

    echo '<span class="product-card__badge">' . esc_html($terms[0]->name) . '</span>';
}
add_action('woocommerce_shop_loop_item_title', 'sportzone_product_category_badge', 5);

function sportzone_add_to_cart_text(): string
{
    return __('Thêm vào giỏ', 'sportzone');
}
add_filter('woocommerce_product_add_to_cart_text', 'sportzone_add_to_cart_text');
add_filter('woocommerce_product_single_add_to_cart_text', 'sportzone_add_to_cart_text');

function sportzone_translate_common_frontend_text(string $translated, string $text, string $domain): string
{
    if (is_admin()) {
        return $translated;
    }

    $translations = [
        'Shop' => 'Cửa hàng',
        'Cart' => 'Giỏ hàng',
        'Checkout' => 'Thanh toán',
        'My account' => 'Tài khoản của tôi',
        'Add to cart' => 'Thêm vào giỏ',
        'Read more' => 'Xem chi tiết',
        'Select options' => 'Chọn tùy chọn',
        'View cart' => 'Xem giỏ hàng',
        'Proceed to checkout' => 'Tiến hành thanh toán',
        'Place order' => 'Đặt hàng',
        'Update cart' => 'Cập nhật giỏ hàng',
        'Apply coupon' => 'Áp dụng mã giảm giá',
        'Coupon code' => 'Mã giảm giá',
        'Product' => 'Sản phẩm',
        'Price' => 'Giá',
        'Quantity' => 'Số lượng',
        'Subtotal' => 'Tạm tính',
        'Total' => 'Tổng cộng',
        'Billing details' => 'Thông tin thanh toán',
        'Additional information' => 'Thông tin bổ sung',
        'Your order' => 'Đơn hàng của bạn',
        'Default sorting' => 'Sắp xếp mặc định',
        'Sort by popularity' => 'Sắp xếp theo độ phổ biến',
        'Sort by average rating' => 'Sắp xếp theo đánh giá',
        'Sort by latest' => 'Sắp xếp mới nhất',
        'Sort by price: low to high' => 'Giá từ thấp đến cao',
        'Sort by price: high to low' => 'Giá từ cao đến thấp',
        'Showing all %d results' => 'Hiển thị tất cả %d sản phẩm',
        'Showing the single result' => 'Hiển thị 1 sản phẩm',
        'No products were found matching your selection.' => 'Không tìm thấy sản phẩm phù hợp.',
        'Related products' => 'Sản phẩm liên quan',
        'Description' => 'Mô tả',
        'Reviews' => 'Đánh giá',
        'There are no reviews yet.' => 'Chưa có đánh giá nào.',
        'Be the first to review' => 'Hãy là người đầu tiên đánh giá',
    ];

    return $translations[$text] ?? $translated;
}
add_filter('gettext', 'sportzone_translate_common_frontend_text', 20, 3);
add_filter('ngettext', 'sportzone_translate_common_frontend_text', 20, 3);

function sportzone_cart_count(): int
{
    if (!class_exists('WooCommerce') || !WC()->cart) {
        return 0;
    }

    return WC()->cart->get_cart_contents_count();
}

function sportzone_demo_products(): array
{
    return [
        [
            'name'  => __('Giày chạy bộ Velocity Pro', 'sportzone'),
            'cat'   => __('Chạy bộ', 'sportzone'),
            'price' => '1.890.000 đ',
            'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
        ],
        [
            'name'  => __('Áo tập gym DryFit Elite', 'sportzone'),
            'cat'   => __('Tập luyện', 'sportzone'),
            'price' => '490.000 đ',
            'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=900&q=80',
        ],
        [
            'name'  => __('Bóng đá sân cỏ FIFA Quality', 'sportzone'),
            'cat'   => __('Bóng đá', 'sportzone'),
            'price' => '650.000 đ',
            'image' => 'https://images.unsplash.com/photo-1614632537423-1e6c2e7e0aab?auto=format&fit=crop&w=900&q=80',
        ],
        [
            'name'  => __('Vợt tennis Carbon Strike', 'sportzone'),
            'cat'   => __('Tennis', 'sportzone'),
            'price' => '2.250.000 đ',
            'image' => 'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=900&q=80',
        ],
    ];
}
