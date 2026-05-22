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

function sportzone_product_category_url(string $slug): string
{
    if (taxonomy_exists('product_cat')) {
        $term = get_term_by('slug', $slug, 'product_cat');

        if ($term && !is_wp_error($term)) {
            $term_link = get_term_link($term);

            if (!is_wp_error($term_link)) {
                return $term_link;
            }
        }
    }

    $page = get_page_by_path($slug);

    if ($page) {
        return get_permalink($page);
    }

    if (function_exists('wc_get_page_permalink')) {
        return add_query_arg('product_cat', $slug, wc_get_page_permalink('shop'));
    }

    return home_url('/');
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
        'Login' => 'Đăng nhập',
        'Register' => 'Đăng ký',
        'Username or email address' => 'Tên đăng nhập hoặc email',
        'Username' => 'Tên đăng nhập',
        'Email address' => 'Địa chỉ email',
        'Password' => 'Mật khẩu',
        'Remember me' => 'Ghi nhớ đăng nhập',
        'Log in' => 'Đăng nhập',
        'Lost your password?' => 'Quên mật khẩu?',
        'Dashboard' => 'Tổng quan',
        'Orders' => 'Đơn hàng',
        'Downloads' => 'Tải xuống',
        'Addresses' => 'Địa chỉ',
        'Account details' => 'Hồ sơ cá nhân',
        'Log out' => 'Đăng xuất',
        'Hello %1$s (not %1$s? %2$sLog out%3$s)' => 'Xin chào %1$s (không phải %1$s? %2$sĐăng xuất%3$s)',
        'From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.' => 'Từ trang tài khoản, bạn có thể xem đơn hàng gần đây, quản lý địa chỉ giao hàng/thanh toán và chỉnh sửa mật khẩu hoặc hồ sơ cá nhân.',
        'The following addresses will be used on the checkout page by default.' => 'Các địa chỉ dưới đây sẽ được dùng mặc định ở trang thanh toán.',
        'Billing address' => 'Địa chỉ thanh toán',
        'Shipping address' => 'Địa chỉ giao hàng',
        'Edit' => 'Sửa',
        'Save address' => 'Lưu địa chỉ',
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

function sportzone_account_endpoint(): void
{
    add_rewrite_endpoint('the-tin-dung', EP_ROOT | EP_PAGES);
}
add_action('init', 'sportzone_account_endpoint');

function sportzone_account_query_vars(array $vars): array
{
    $vars[] = 'the-tin-dung';

    return $vars;
}
add_filter('query_vars', 'sportzone_account_query_vars');

function sportzone_account_menu_items(array $items): array
{
    $logout = $items['customer-logout'] ?? null;
    unset($items['customer-logout']);

    $items = [
        'dashboard'     => __('Tổng quan', 'sportzone'),
        'orders'        => __('Đơn hàng', 'sportzone'),
        'edit-address'  => __('Địa chỉ', 'sportzone'),
        'the-tin-dung'  => __('Thẻ tín dụng', 'sportzone'),
        'edit-account'  => __('Hồ sơ cá nhân', 'sportzone'),
    ];

    if ($logout !== null) {
        $items['customer-logout'] = __('Đăng xuất', 'sportzone');
    }

    return $items;
}
add_filter('woocommerce_account_menu_items', 'sportzone_account_menu_items', 30);

function sportzone_handle_credit_card_form(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST['sportzone_card_account_action'])) {
        return;
    }

    if (!is_user_logged_in() || !isset($_POST['sportzone_card_account_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sportzone_card_account_nonce'])), 'sportzone_card_account')) {
        return;
    }

    $number = isset($_POST['card_number']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['card_number'])) : '';
    $expiry = isset($_POST['card_expiry']) ? sanitize_text_field(wp_unslash($_POST['card_expiry'])) : '';
    $holder = isset($_POST['card_holder']) ? sanitize_text_field(wp_unslash($_POST['card_holder'])) : '';

    if (strlen($number) < 12 || $holder === '' || $expiry === '') {
        wc_add_notice(__('Vui lòng nhập đầy đủ thông tin thẻ hợp lệ.', 'sportzone'), 'error');
        return;
    }

    update_user_meta(get_current_user_id(), 'sportzone_demo_card', [
        'holder' => $holder,
        'last4'  => substr($number, -4),
        'expiry' => $expiry,
    ]);

    wc_add_notice(__('Đã lưu thẻ demo cho tài khoản.', 'sportzone'), 'success');
}
add_action('template_redirect', 'sportzone_handle_credit_card_form');

function sportzone_credit_cards_endpoint_content(): void
{
    $card = get_user_meta(get_current_user_id(), 'sportzone_demo_card', true);
    $card = is_array($card) ? $card : [];
    ?>
    <section class="sportzone-account-panel">
        <div class="sportzone-account-panel__head">
            <div>
                <span><?php esc_html_e('Ví thanh toán', 'sportzone'); ?></span>
                <h2><?php esc_html_e('Thẻ tín dụng', 'sportzone'); ?></h2>
            </div>
            <strong><?php esc_html_e('Demo', 'sportzone'); ?></strong>
        </div>

        <?php if (!empty($card['last4'])) : ?>
            <div class="sportzone-saved-card">
                <span><?php esc_html_e('SportZone Card', 'sportzone'); ?></span>
                <strong>•••• •••• •••• <?php echo esc_html($card['last4']); ?></strong>
                <small><?php echo esc_html(($card['holder'] ?? '') . ' · ' . ($card['expiry'] ?? '')); ?></small>
            </div>
        <?php endif; ?>

        <form class="sportzone-card-form" method="post">
            <?php wp_nonce_field('sportzone_card_account', 'sportzone_card_account_nonce'); ?>
            <input type="hidden" name="sportzone_card_account_action" value="1">
            <p>
                <label for="sportzone-card-holder"><?php esc_html_e('Tên chủ thẻ', 'sportzone'); ?></label>
                <input id="sportzone-card-holder" name="card_holder" type="text" autocomplete="cc-name" placeholder="NGUYEN VAN A" value="<?php echo esc_attr($card['holder'] ?? ''); ?>" required>
            </p>
            <p>
                <label for="sportzone-card-number"><?php esc_html_e('Số thẻ', 'sportzone'); ?></label>
                <input id="sportzone-card-number" name="card_number" type="text" inputmode="numeric" autocomplete="cc-number" placeholder="4242 4242 4242 4242" required>
            </p>
            <div class="sportzone-card-form__row">
                <p>
                    <label for="sportzone-card-expiry"><?php esc_html_e('Ngày hết hạn', 'sportzone'); ?></label>
                    <input id="sportzone-card-expiry" name="card_expiry" type="text" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY" value="<?php echo esc_attr($card['expiry'] ?? ''); ?>" required>
                </p>
                <p>
                    <label for="sportzone-card-cvv"><?php esc_html_e('CVV', 'sportzone'); ?></label>
                    <input id="sportzone-card-cvv" name="card_cvv" type="password" inputmode="numeric" autocomplete="cc-csc" placeholder="123">
                </p>
            </div>
            <button class="button" type="submit"><?php esc_html_e('Lưu thẻ demo', 'sportzone'); ?></button>
        </form>
    </section>
    <?php
}
add_action('woocommerce_account_the-tin-dung_endpoint', 'sportzone_credit_cards_endpoint_content');

function sportzone_account_dashboard_cards(): void
{
    $orders_url = wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'));
    $address_url = wc_get_endpoint_url('edit-address', '', wc_get_page_permalink('myaccount'));
    $card_url = wc_get_endpoint_url('the-tin-dung', '', wc_get_page_permalink('myaccount'));
    ?>
    <div class="sportzone-account-grid">
        <a href="<?php echo esc_url($orders_url); ?>">
            <span class="dashicons dashicons-clipboard"></span>
            <strong><?php esc_html_e('Đơn hàng', 'sportzone'); ?></strong>
            <small><?php esc_html_e('Theo dõi đơn đã mua', 'sportzone'); ?></small>
        </a>
        <a href="<?php echo esc_url($address_url); ?>">
            <span class="dashicons dashicons-location-alt"></span>
            <strong><?php esc_html_e('Địa chỉ', 'sportzone'); ?></strong>
            <small><?php esc_html_e('Cập nhật giao hàng và thanh toán', 'sportzone'); ?></small>
        </a>
        <a href="<?php echo esc_url($card_url); ?>">
            <span class="dashicons dashicons-money-alt"></span>
            <strong><?php esc_html_e('Thẻ tín dụng', 'sportzone'); ?></strong>
            <small><?php esc_html_e('Lưu thẻ demo cho thanh toán', 'sportzone'); ?></small>
        </a>
    </div>
    <?php
}
add_action('woocommerce_account_dashboard', 'sportzone_account_dashboard_cards', 20);

function sportzone_cart_count(): int
{
    if (!class_exists('WooCommerce') || !WC()->cart) {
        return 0;
    }

    return WC()->cart->get_cart_contents_count();
}

function sportzone_cart_count_fragment(array $fragments): array
{
    $fragments['.sportzone-cart-count'] = '<strong class="sportzone-cart-count">' . esc_html((string) sportzone_cart_count()) . '</strong>';

    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'sportzone_cart_count_fragment');

function sportzone_image_url(string $path): string
{
    return content_url('images/' . ltrim($path, '/'));
}

function sportzone_demo_products(): array
{
    return [
        [
            'name'  => __('Giày thể thao Wano 228', 'sportzone'),
            'cat'   => __('Giày thể thao', 'sportzone'),
            'price' => '1.890.000 đ',
            'image' => sportzone_image_url('bao-deal/giaythethaonamwano228.jpg'),
        ],
        [
            'name'  => __('Áo thể thao nam Wanno', 'sportzone'),
            'cat'   => __('Quần áo thể thao', 'sportzone'),
            'price' => '490.000 đ',
            'image' => sportzone_image_url('bao-deal/aothethaonamwanno.jpg'),
        ],
        [
            'name'  => __('Bộ dụng cụ tập luyện đa năng', 'sportzone'),
            'cat'   => __('Dụng cụ thể thao', 'sportzone'),
            'price' => '650.000 đ',
            'image' => sportzone_image_url('dungcu-thethao/dungcu2.jpg'),
        ],
        [
            'name'  => __('Phụ kiện thể thao tiện dụng', 'sportzone'),
            'cat'   => __('Phụ kiện thể thao', 'sportzone'),
            'price' => '2.250.000 đ',
            'image' => sportzone_image_url('phukien-thethao/phukien3.jpg'),
        ],
    ];
}
