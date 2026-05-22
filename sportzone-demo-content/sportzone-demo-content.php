<?php
/**
 * Plugin Name: SportZone Demo Content
 * Description: Creates demo pages, menu, WooCommerce categories, and sports products for the SportZone Shop theme.
 * Version: 1.0.0
 * Author: Codex
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: sportzone-demo
 */

if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(__FILE__, 'sportzone_demo_activate');

add_filter('image_downsize', 'sportzone_demo_image_downsize', 10, 3);
add_filter('wp_get_attachment_url', 'sportzone_demo_attachment_url', 10, 2);
add_action('init', 'sportzone_demo_handle_account_forms');
add_shortcode('sportzone_login_form', 'sportzone_demo_login_form_shortcode');
add_shortcode('sportzone_register_form', 'sportzone_demo_register_form_shortcode');

function sportzone_demo_activate(): void
{
    sportzone_demo_create_pages();
    sportzone_demo_create_extra_pages();
    sportzone_demo_create_category_pages();

    if (class_exists('WooCommerce')) {
        sportzone_demo_ensure_woocommerce_pages();
    }

    sportzone_demo_create_menu();

    if (class_exists('WooCommerce')) {
        sportzone_demo_create_products();
    }

    update_option('sportzone_demo_content_created', current_time('mysql'));
}

function sportzone_demo_attachment_url(string $url, int $attachment_id): string
{
    $source_url = (string) get_post_meta($attachment_id, '_sportzone_source_image', true);

    return $source_url ?: $url;
}

function sportzone_demo_image_downsize($downsize, int $attachment_id, $size)
{
    $source_url = (string) get_post_meta($attachment_id, '_sportzone_source_image', true);

    if (!$source_url) {
        return $downsize;
    }

    [$width, $height] = sportzone_demo_source_image_dimensions($source_url);

    return [$source_url, $width, $height, false];
}

function sportzone_demo_source_image_dimensions(string $source_url): array
{
    $content_url = content_url('images/');
    $relative_path = str_starts_with($source_url, $content_url) ? substr($source_url, strlen($content_url)) : '';
    $file_path = $relative_path ? wp_normalize_path(WP_CONTENT_DIR . '/images/' . ltrim($relative_path, '/')) : '';

    if ($file_path && file_exists($file_path)) {
        $dimensions = @getimagesize($file_path);

        if ($dimensions) {
            return [(int) $dimensions[0], (int) $dimensions[1]];
        }
    }

    return [900, 900];
}

function sportzone_demo_handle_account_forms(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    if (isset($_POST['sportzone_login_action'])) {
        sportzone_demo_handle_login();
    }

    if (isset($_POST['sportzone_register_action'])) {
        sportzone_demo_handle_register();
    }
}

function sportzone_demo_handle_login(): void
{
    if (!isset($_POST['sportzone_login_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sportzone_login_nonce'])), 'sportzone_login')) {
        wp_safe_redirect(add_query_arg('login_error', 'nonce', sportzone_demo_login_url()));
        exit;
    }

    $credentials = [
        'user_login'    => isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '',
        'user_password' => isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '',
        'remember'      => !empty($_POST['rememberme']),
    ];

    $user = wp_signon($credentials, is_ssl());

    if (is_wp_error($user)) {
        wp_safe_redirect(add_query_arg('login_error', 'invalid', sportzone_demo_login_url()));
        exit;
    }

    wp_safe_redirect(class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url('/'));
    exit;
}

function sportzone_demo_handle_register(): void
{
    if (!isset($_POST['sportzone_register_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sportzone_register_nonce'])), 'sportzone_register')) {
        wp_safe_redirect(add_query_arg('register_error', 'nonce', sportzone_demo_register_url()));
        exit;
    }

    $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';

    if (!$username || !$email || !$password) {
        wp_safe_redirect(add_query_arg('register_error', 'missing', sportzone_demo_register_url()));
        exit;
    }

    if (username_exists($username)) {
        wp_safe_redirect(add_query_arg('register_error', 'username_exists', sportzone_demo_register_url()));
        exit;
    }

    if (!is_email($email) || email_exists($email)) {
        wp_safe_redirect(add_query_arg('register_error', 'email_exists', sportzone_demo_register_url()));
        exit;
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_safe_redirect(add_query_arg('register_error', 'failed', sportzone_demo_register_url()));
        exit;
    }

    $user = new WP_User((int) $user_id);
    $user->set_role('customer');

    wp_safe_redirect(add_query_arg('registered', '1', sportzone_demo_login_url()));
    exit;
}

function sportzone_demo_login_form_shortcode(): string
{
    if (is_user_logged_in()) {
        return '<div class="sportzone-auth"><p>Bạn đã đăng nhập.</p><a class="button" href="' . esc_url(class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url('/')) . '">Vào tài khoản</a></div>';
    }

    $message = '';

    if (isset($_GET['registered'])) {
        $message = '<div class="woocommerce-message">Đăng ký thành công. Hãy đăng nhập bằng tài khoản vừa tạo.</div>';
    } elseif (isset($_GET['login_error'])) {
        $message = '<div class="woocommerce-error">Thông tin đăng nhập chưa đúng. Vui lòng kiểm tra lại.</div>';
    }

    ob_start();
    ?>
    <div class="sportzone-auth">
        <?php echo wp_kses_post($message); ?>
        <form class="sportzone-auth__form" method="post">
            <?php wp_nonce_field('sportzone_login', 'sportzone_login_nonce'); ?>
            <input type="hidden" name="sportzone_login_action" value="1">
            <p>
                <label for="sportzone-login-username">Tên đăng nhập</label>
                <input id="sportzone-login-username" name="username" type="text" autocomplete="username" required>
            </p>
            <p>
                <label for="sportzone-login-password">Mật khẩu</label>
                <input id="sportzone-login-password" name="password" type="password" autocomplete="current-password" required>
            </p>
            <label class="sportzone-auth__check">
                <input name="rememberme" type="checkbox" value="forever">
                <span>Ghi nhớ đăng nhập</span>
            </label>
            <button class="button" type="submit">Đăng nhập</button>
        </form>
        <p class="sportzone-auth__switch">Chưa có tài khoản? <a href="<?php echo esc_url(sportzone_demo_register_url()); ?>">Đăng ký tài khoản</a></p>
    </div>
    <?php
    return ob_get_clean();
}

function sportzone_demo_register_form_shortcode(): string
{
    if (is_user_logged_in()) {
        return '<div class="sportzone-auth"><p>Bạn đã đăng nhập.</p><a class="button" href="' . esc_url(class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url('/')) . '">Vào tài khoản</a></div>';
    }

    $message = '';

    if (isset($_GET['register_error'])) {
        $messages = [
            'missing' => 'Vui lòng nhập đầy đủ thông tin.',
            'username_exists' => 'Tên đăng nhập đã tồn tại.',
            'email_exists' => 'Email không hợp lệ hoặc đã được sử dụng.',
            'failed' => 'Không thể tạo tài khoản. Vui lòng thử lại.',
            'nonce' => 'Phiên đăng ký đã hết hạn. Vui lòng thử lại.',
        ];
        $error_key = sanitize_key(wp_unslash($_GET['register_error']));
        $message = '<div class="woocommerce-error">' . esc_html($messages[$error_key] ?? $messages['failed']) . '</div>';
    }

    ob_start();
    ?>
    <div class="sportzone-auth">
        <?php echo wp_kses_post($message); ?>
        <form class="sportzone-auth__form" method="post">
            <?php wp_nonce_field('sportzone_register', 'sportzone_register_nonce'); ?>
            <input type="hidden" name="sportzone_register_action" value="1">
            <p>
                <label for="sportzone-register-username">Tên đăng nhập</label>
                <input id="sportzone-register-username" name="username" type="text" autocomplete="username" required>
            </p>
            <p>
                <label for="sportzone-register-email">Email</label>
                <input id="sportzone-register-email" name="email" type="email" autocomplete="email" required>
            </p>
            <p>
                <label for="sportzone-register-password">Mật khẩu</label>
                <input id="sportzone-register-password" name="password" type="password" autocomplete="new-password" required>
            </p>
            <button class="button" type="submit">Đăng ký</button>
        </form>
        <p class="sportzone-auth__switch">Đã có tài khoản? <a href="<?php echo esc_url(sportzone_demo_login_url()); ?>">Đăng nhập</a></p>
    </div>
    <?php
    return ob_get_clean();
}

function sportzone_demo_login_url(): string
{
    return home_url('/dang-nhap');
}

function sportzone_demo_register_url(): string
{
    return home_url('/dang-ky');
}

function sportzone_demo_ensure_woocommerce_pages(): void
{
    sportzone_demo_enable_account_registration();

    $pages = [
        'cart' => [
            'title' => 'Giỏ hàng',
            'slug' => 'gio-hang',
            'content' => '[woocommerce_cart]',
            'option' => 'woocommerce_cart_page_id',
        ],
        'checkout' => [
            'title' => 'Thanh toán',
            'slug' => 'thanh-toan',
            'content' => '[woocommerce_checkout]',
            'option' => 'woocommerce_checkout_page_id',
        ],
        'myaccount' => [
            'title' => 'Tài khoản',
            'slug' => 'tai-khoan',
            'content' => '[woocommerce_my_account]',
            'option' => 'woocommerce_myaccount_page_id',
        ],
    ];

    foreach ($pages as $page) {
        $page_id = (int) get_option($page['option']);

        if ($page_id && get_post($page_id)) {
            continue;
        }

        $existing = get_page_by_path($page['slug']);

        if ($existing) {
            $page_id = (int) $existing->ID;
            wp_update_post([
                'ID' => $page_id,
                'post_title' => $page['title'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
            ]);
        } else {
            $page_id = wp_insert_post([
                'post_title' => $page['title'],
                'post_name' => $page['slug'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
            ]);
        }

        if (!is_wp_error($page_id)) {
            update_option($page['option'], (int) $page_id);
        }
    }
}

function sportzone_demo_enable_account_registration(): void
{
    update_option('users_can_register', 1);
    update_option('woocommerce_enable_myaccount_registration', 'no');
    update_option('woocommerce_registration_generate_username', 'no');
    update_option('woocommerce_registration_generate_password', 'no');
}

function sportzone_demo_create_category_pages(): void
{
    $pages = [
        [
            'title'   => 'Quần áo thể thao',
            'slug'    => 'quan-ao-the-thao',
            'summary' => 'Áo tập, áo khoác, quần short và trang phục thể thao thoáng khí cho luyện tập hằng ngày.',
            'shortcode' => '[products limit="12" columns="4" category="quan-ao-the-thao"]',
        ],
        [
            'title'   => 'Giày thể thao',
            'slug'    => 'giay-the-thao',
            'summary' => 'Giày chạy bộ, giày sân cỏ và giày tập luyện có độ bám tốt, êm chân và dễ phối đồ.',
            'shortcode' => '[products limit="12" columns="4" category="giay-the-thao"]',
        ],
        [
            'title'   => 'Dụng cụ thể thao',
            'slug'    => 'dung-cu-the-thao',
            'summary' => 'Bóng, dây kháng lực và dụng cụ hỗ trợ cho tập luyện tại nhà, phòng gym hoặc sân thi đấu.',
            'shortcode' => '[products limit="12" columns="4" category="dung-cu-the-thao"]',
        ],
        [
            'title'   => 'Phụ kiện thể thao',
            'slug'    => 'phu-kien-the-thao',
            'summary' => 'Túi đựng vợt, phụ kiện tennis, phụ kiện chạy bộ và các món cần thiết khi tập luyện.',
            'shortcode' => '[products limit="12" columns="4" category="phu-kien-the-thao"]',
        ],
        [
            'title'   => 'Thể thao nam',
            'slug'    => 'nam',
            'summary' => 'Sản phẩm thể thao dành cho nam: giày, áo tập, bóng đá, tennis và phụ kiện.',
            'shortcode' => '[products limit="12" columns="4" category="nam"]',
        ],
        [
            'title'   => 'Thể thao nữ',
            'slug'    => 'nu',
            'summary' => 'Sản phẩm thể thao dành cho nữ: giày chạy bộ, áo tập, phụ kiện và trang phục năng động.',
            'shortcode' => '[products limit="12" columns="4" category="nu"]',
        ],
        [
            'title'   => 'Thể thao trẻ em',
            'slug'    => 'tre-em',
            'summary' => 'Sản phẩm thể thao cho trẻ em với thiết kế dễ dùng, nhẹ và phù hợp hoạt động hằng ngày.',
            'shortcode' => '[products limit="8" columns="4"]',
        ],
    ];

    foreach ($pages as $page) {
        $content = sprintf(
            '<div class="sportzone-category-page"><section class="sportzone-category-hero"><h2>%s</h2><p>%s</p><div class="sportzone-category-actions"><a class="button" href="%s">Xem tất cả sản phẩm</a><a class="button button--light" href="%s">Tư vấn chọn size</a></div></section>%s</div>',
            esc_html($page['title']),
            esc_html($page['summary']),
            esc_url(class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop')),
            esc_url(home_url('/bang-size')),
            $page['shortcode']
        );

        $existing = get_page_by_path($page['slug']);

        if ($existing) {
            wp_update_post([
                'ID'           => $existing->ID,
                'post_title'   => $page['title'],
                'post_content' => $content,
            ]);
            continue;
        }

        wp_insert_post([
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }
}

function sportzone_demo_create_extra_pages(): void
{
    $pages = [
        [
            'title'   => 'Giới thiệu',
            'slug'    => 'gioi-thieu',
            'content' => '<h2>Về SportZone</h2><p>SportZone là cửa hàng thể thao chuyên cung cấp giày, quần áo, dụng cụ và phụ kiện cho chạy bộ, tập luyện, bóng đá và tennis.</p><p>Chúng tôi tập trung vào sản phẩm dễ chọn, giá rõ ràng và hỗ trợ khách hàng trước lẫn sau khi mua.</p>',
        ],
        [
            'title'   => 'Hệ thống cửa hàng',
            'slug'    => 'he-thong-cua-hang',
            'content' => '<h2>Hệ thống cửa hàng</h2><p><strong>Chi nhánh 1:</strong> 123 Nguyễn Trãi, Quận 1, TP.HCM</p><p><strong>Chi nhánh 2:</strong> 45 Cầu Giấy, Hà Nội</p><p><strong>Chi nhánh 3:</strong> 88 Bạch Đằng, Đà Nẵng</p><p>Thời gian mở cửa: 08:00 - 21:00 hằng ngày.</p>',
        ],
        [
            'title'   => 'Chính sách mua hàng',
            'slug'    => 'chinh-sach-mua-hang',
            'content' => '<h2>Chính sách mua hàng</h2><p>Khách hàng có thể đặt hàng trực tuyến, thanh toán khi nhận hàng hoặc dùng phương thức thẻ ảo cho mục đích demo.</p><p>Sản phẩm được kiểm tra trước khi giao và hỗ trợ đổi size theo chính sách đổi trả.</p>',
        ],
        [
            'title'   => 'Hướng dẫn mua hàng',
            'slug'    => 'huong-dan-mua-hang',
            'content' => '<h2>Hướng dẫn mua hàng</h2><ol><li>Chọn sản phẩm và bấm thêm vào giỏ.</li><li>Kiểm tra giỏ hàng và tiến hành thanh toán.</li><li>Nhập thông tin nhận hàng.</li><li>Xác nhận đơn hàng và chờ nhân viên xử lý.</li></ol>',
        ],
    ];

    foreach ($pages as $page) {
        if (get_page_by_path($page['slug'])) {
            continue;
        }

        wp_insert_post([
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_content' => $page['content'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }
}

function sportzone_demo_create_pages(): void
{
    $pages = [
        [
            'title'   => 'Trang chủ',
            'slug'    => 'trang-chu',
            'content' => 'Chào mừng đến SportZone - cửa hàng đồ thể thao cho chạy bộ, tập luyện, bóng đá và tennis.',
            'home'    => true,
        ],
        [
            'title'   => 'Liên hệ',
            'slug'    => 'lien-he',
            'content' => '<h2>Liên hệ SportZone</h2><p>Hotline: 1900 6868</p><p>Email: support@sportzone.local</p><p>Địa chỉ: 123 Nguyễn Trãi, Quận 1, TP.HCM</p>',
        ],
        [
            'title'   => 'Đăng nhập',
            'slug'    => 'dang-nhap',
            'content' => '[sportzone_login_form]',
        ],
        [
            'title'   => 'Đăng ký',
            'slug'    => 'dang-ky',
            'content' => '[sportzone_register_form]',
        ],
        [
            'title'   => 'Bảng size',
            'slug'    => 'bang-size',
            'content' => '<h2>Bảng size tham khảo</h2><p>Giày chạy bộ: đo chiều dài bàn chân và cộng thêm 0.5 cm. Quần áo tập luyện: chọn theo vòng ngực, eo và phong cách tập.</p>',
        ],
        [
            'title'   => 'Vận chuyển',
            'slug'    => 'van-chuyen',
            'content' => '<h2>Chính sách vận chuyển</h2><p>Giao hàng toàn quốc. Miễn phí vận chuyển cho đơn từ 499.000 đ.</p>',
        ],
        [
            'title'   => 'Đổi trả',
            'slug'    => 'doi-tra',
            'content' => '<h2>Chính sách đổi trả</h2><p>Đổi size hoặc màu trong 7 ngày nếu sản phẩm còn tem mác và chưa qua sử dụng.</p>',
        ],
    ];

    foreach ($pages as $page) {
        $existing = get_page_by_path($page['slug']);

        if (!$existing) {
            $page_id = wp_insert_post([
                'post_title'   => $page['title'],
                'post_name'    => $page['slug'],
                'post_content' => $page['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
        } else {
            $page_id = $existing->ID;
        }

        if (!empty($page['home']) && !is_wp_error($page_id)) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', (int) $page_id);
        }
    }
}

function sportzone_demo_create_menu(): void
{
    $menu_name = 'SportZone Main Menu';
    $menu = wp_get_nav_menu_object($menu_name);

    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
    } else {
        $menu_id = (int) $menu->term_id;
    }

    if (is_wp_error($menu_id)) {
        return;
    }

    $items = [
        ['title' => 'Trang chủ', 'url' => home_url('/')],
        ['title' => 'Cửa hàng', 'url' => class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop')],
        ['title' => 'Bảng size', 'url' => home_url('/bang-size')],
        ['title' => 'Liên hệ', 'url' => home_url('/lien-he')],
    ];

    if (class_exists('WooCommerce')) {
        $items[] = ['title' => 'Giỏ hàng', 'url' => wc_get_cart_url()];
        $items[] = ['title' => 'Thanh toán', 'url' => wc_get_checkout_url()];
    }

    $existing_items = wp_get_nav_menu_items($menu_id);

    if ($existing_items) {
        foreach ($existing_items as $existing_item) {
            wp_delete_post((int) $existing_item->ID, true);
        }
    }

    foreach ($items as $item) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => $item['title'],
            'menu-item-url'    => $item['url'],
            'menu-item-status' => 'publish',
        ]);
    }

    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

function sportzone_demo_create_products(): void
{
    $categories = [
        'Quần áo thể thao' => 'Áo tập, áo polo, đồ bóng đá, đồ bóng chuyền và trang phục vận động.',
        'Giày thể thao'    => 'Giày thể thao nam, nữ và các mẫu giày tập luyện hằng ngày.',
        'Dụng cụ thể thao' => 'Dụng cụ tập luyện, thiết bị hỗ trợ và sản phẩm thể thao tại nhà.',
        'Phụ kiện thể thao' => 'Phụ kiện tập luyện, bảo hộ, túi và các món đi kèm.',
        'Nam'              => 'Sản phẩm thể thao dành cho nam.',
        'Nữ'               => 'Sản phẩm thể thao dành cho nữ.',
        'Bão deal'         => 'Sản phẩm ưu đãi nổi bật.',
    ];

    foreach ($categories as $name => $description) {
        if (!term_exists($name, 'product_cat')) {
            wp_insert_term($name, 'product_cat', [
                'description' => $description,
                'slug'        => sanitize_title($name),
            ]);
        }
    }

    $products = [
        ['Giày thể thao Wano 228', 'Giày thể thao', 1180000, 'Giày thể thao nam kiểu dáng năng động, phù hợp đi chơi và tập luyện hằng ngày.', true, 'bao-deal/giaythethaonamwano228.jpg'],
        ['Giày thể thao nữ Wanni 2230', 'Giày thể thao', 1250000, 'Giày thể thao nữ nhẹ chân, phối màu dễ mặc cho nhiều hoạt động.', true, 'bao-deal/giaythethaonuwanni2230.jpg'],
        ['Giày thể thao nữ Wanno 231', 'Giày thể thao', 1190000, 'Giày thể thao nữ form gọn, đế êm và bám tốt.', false, 'bao-deal/giaythethaonuwanno231.jpg'],
        ['Giày thể thao nữ Wano 221', 'Giày thể thao', 1090000, 'Mẫu giày thể thao nữ cơ bản cho đi bộ, tập nhẹ và sử dụng hằng ngày.', false, 'bao-deal/giaythethaonuwano221.jpg'],
        ['Áo thể thao nam Wanno', 'Quần áo thể thao', 390000, 'Áo thể thao nam chất liệu thoáng, dễ phối với quần tập.', true, 'bao-deal/aothethaonamwanno.jpg'],
        ['Giày chạy bộ Velocity Pro', 'Giày thể thao', 1890000, 'Giày chạy bộ đệm êm, đế cao su bám đường, phù hợp tập daily run và race 10K.', true, 'giay-thethao/giaythethao1.jpg'],
        ['Áo khoác gió Runner Shield', 'Quần áo thể thao', 790000, 'Áo khoác nhẹ chống gió, có túi khóa kéo, dùng cho buổi chạy sáng và tối.', false, 'quanao-thethao/aococtaynma.jpg'],
        ['Áo tập gym DryFit Elite', 'Quần áo thể thao', 490000, 'Áo tập thoáng khí, nhanh khô, form gọn cho tập gym và HIIT.', true, 'quanao-thethao/aotapyoganudaitay.jpg'],
        ['Dây kháng lực Power Band', 'Dụng cụ thể thao', 220000, 'Dây kháng lực đa năng cho khởi động, phục hồi và tập sức mạnh.', false, 'dungcu-thethao/dungcu1.jpg'],
        ['Bóng đá sân cỏ FIFA Quality', 'Dụng cụ thể thao', 650000, 'Bóng đá size 5, độ nảy tốt, phù hợp tập luyện và thi đấu phong trào.', true, 'dungcu-thethao/dungcu5.jpg'],
        ['Giày đá bóng Turf Control', 'Giày thể thao', 1290000, 'Giày sân cỏ nhân tạo, upper bền, đế TF bám sân tốt.', false, 'giay-thethao/giaythethao7.jpg'],
        ['Vợt tennis Carbon Strike', 'Dụng cụ thể thao', 2250000, 'Vợt tennis khung carbon, cân bằng tốt cho người chơi trung cấp.', true, 'dungcu-thethao/dungcu8.jpg'],
        ['Túi đựng vợt Court Pack', 'Phụ kiện thể thao', 890000, 'Túi đựng 2 vợt, ngăn riêng cho giày và phụ kiện cá nhân.', false, 'phukien-thethao/phukien6.jpg'],
        ['Dụng cụ thể thao 01', 'Dụng cụ thể thao', 250000, 'Dụng cụ hỗ trợ luyện tập tại nhà và phòng gym.', true, 'dungcu-thethao/dungcu1.jpg'],
        ['Dụng cụ thể thao 02', 'Dụng cụ thể thao', 320000, 'Thiết bị tập luyện đa năng cho người mới bắt đầu.', true, 'dungcu-thethao/dungcu2.jpg'],
        ['Dụng cụ thể thao 03', 'Dụng cụ thể thao', 280000, 'Dụng cụ tập gọn nhẹ, dễ cất giữ và sử dụng.', false, 'dungcu-thethao/dungcu3.jpg'],
        ['Dụng cụ thể thao 04', 'Dụng cụ thể thao', 350000, 'Sản phẩm hỗ trợ tăng cường vận động hằng ngày.', false, 'dungcu-thethao/dungcu4.jpg'],
        ['Dụng cụ thể thao 05', 'Dụng cụ thể thao', 420000, 'Dụng cụ tập luyện bền bỉ cho nhiều bài tập.', false, 'dungcu-thethao/dungcu5.jpg'],
        ['Dụng cụ thể thao 06', 'Dụng cụ thể thao', 390000, 'Thiết kế chắc chắn, phù hợp tập luyện cá nhân.', false, 'dungcu-thethao/dungcu6.jpg'],
        ['Dụng cụ thể thao 07', 'Dụng cụ thể thao', 220000, 'Dụng cụ nhỏ gọn cho bài tập bổ trợ.', false, 'dungcu-thethao/dungcu7.jpg'],
        ['Dụng cụ thể thao 08', 'Dụng cụ thể thao', 460000, 'Dụng cụ tập luyện cho gia đình và phòng tập.', false, 'dungcu-thethao/dungcu8.jpg'],
        ['Dụng cụ thể thao 09', 'Dụng cụ thể thao', 180000, 'Phụ trợ luyện tập cơ bản, dễ sử dụng.', false, 'dungcu-thethao/dungcu9.jpg'],
        ['Dụng cụ thể thao 10', 'Dụng cụ thể thao', 510000, 'Sản phẩm hỗ trợ bài tập cường độ vừa và cao.', false, 'dungcu-thethao/dungcu10.jpg'],
        ['Giày thể thao SZ 01', 'Giày thể thao', 890000, 'Giày thể thao êm chân, dùng tốt cho đi bộ và vận động nhẹ.', true, 'giay-thethao/giaythethao1.jpg'],
        ['Giày thể thao SZ 02', 'Giày thể thao', 940000, 'Mẫu giày thể thao linh hoạt cho lịch tập hằng tuần.', false, 'giay-thethao/giaythethao2.jpg'],
        ['Giày thể thao SZ 03', 'Giày thể thao', 790000, 'Giày thể thao thiết kế trẻ trung, dễ phối đồ.', false, 'giay-thethao/giaythethao3.jpg'],
        ['Giày thể thao SZ 04', 'Giày thể thao', 860000, 'Đế bám ổn định, phù hợp nhiều bề mặt.', false, 'giay-thethao/giaythethao4.jpg'],
        ['Giày thể thao SZ 05', 'Giày thể thao', 990000, 'Form ôm chân, chất liệu thoáng khí.', false, 'giay-thethao/giaythethao5.jpg'],
        ['Giày thể thao SZ 06', 'Giày thể thao', 760000, 'Lựa chọn gọn nhẹ cho đi học, đi làm và tập luyện.', false, 'giay-thethao/giaythethao6.jpg'],
        ['Giày thể thao SZ 07', 'Giày thể thao', 1120000, 'Giày thể thao phong cách năng động, đế êm.', false, 'giay-thethao/giaythethao7.jpg'],
        ['Giày thể thao SZ 08', 'Giày thể thao', 820000, 'Mẫu giày cơ bản, dễ dùng trong nhiều hoạt động.', false, 'giay-thethao/giaythethao8.jpg'],
        ['Giày thể thao SZ 09', 'Giày thể thao', 1290000, 'Giày thể thao nổi bật với chất liệu bền và đệm tốt.', true, 'giay-thethao/giaythethao9.jpg'],
        ['Giày thể thao SZ 10', 'Giày thể thao', 970000, 'Giày tập luyện đa dụng cho nam và nữ.', false, 'giay-thethao/giaythethao10.jpg'],
        ['Đồ thể thao nam 01', 'Nam', 520000, 'Trang phục thể thao nam thoáng mát, dễ vận động.', true, 'Nam/donam1.jpg'],
        ['Đồ thể thao nam 02', 'Nam', 470000, 'Sản phẩm nam phù hợp tập luyện hằng ngày.', false, 'Nam/donam2.jpg'],
        ['Đồ thể thao nam 03', 'Nam', 490000, 'Thiết kế nam tính, chất liệu dễ chịu.', false, 'Nam/donam3.jpg'],
        ['Đồ thể thao nam 04', 'Nam', 590000, 'Bộ đồ thể thao nam cho tập luyện và dạo phố.', false, 'Nam/donam4.jpg'],
        ['Đồ thể thao nam 05', 'Nam', 430000, 'Form mặc thoải mái, dễ phối phụ kiện.', false, 'Nam/donam5.jpg'],
        ['Đồ thể thao nam 06', 'Nam', 450000, 'Chất liệu nhẹ, phù hợp khí hậu nóng.', false, 'Nam/donam6.jpg'],
        ['Đồ thể thao nam 07', 'Nam', 510000, 'Trang phục thể thao nam năng động.', false, 'Nam/donam7.jpg'],
        ['Đồ thể thao nam 08', 'Nam', 530000, 'Sản phẩm thể thao nam bền đẹp.', false, 'Nam/donam8.jpg'],
        ['Đồ thể thao nam 09', 'Nam', 390000, 'Lựa chọn cơ bản cho vận động hằng ngày.', false, 'Nam/donam9.jpg'],
        ['Đồ thể thao nam 10', 'Nam', 410000, 'Trang phục nam gọn nhẹ, dễ giặt nhanh khô.', false, 'Nam/donam10.jpg'],
        ['Đồ thể thao nữ 01', 'Nữ', 420000, 'Trang phục thể thao nữ nhẹ, co giãn và thoải mái.', true, 'Nu/donu1.jpg'],
        ['Đồ thể thao nữ 02', 'Nữ', 450000, 'Sản phẩm nữ phù hợp yoga, gym và đi bộ.', false, 'Nu/donu2.jpg'],
        ['Đồ thể thao nữ 03', 'Nữ', 460000, 'Thiết kế gọn gàng, dễ phối nhiều lớp.', false, 'Nu/donu3.jpg'],
        ['Đồ thể thao nữ 04', 'Nữ', 390000, 'Trang phục thể thao nữ cho hoạt động hằng ngày.', false, 'Nu/donu4.jpg'],
        ['Đồ thể thao nữ 05', 'Nữ', 560000, 'Mẫu đồ nữ nổi bật, chất liệu thoáng.', false, 'Nu/donu5.jpg'],
        ['Đồ thể thao nữ 06', 'Nữ', 480000, 'Form vận động linh hoạt cho nhiều bài tập.', false, 'Nu/donu6.jpg'],
        ['Đồ thể thao nữ 07', 'Nữ', 500000, 'Sản phẩm nữ trẻ trung, dễ mặc.', false, 'Nu/donu7.jpg'],
        ['Đồ thể thao nữ 08', 'Nữ', 620000, 'Trang phục nữ chất liệu bền và êm da.', false, 'Nu/donu8.jpg'],
        ['Đồ thể thao nữ 09', 'Nữ', 350000, 'Lựa chọn tiết kiệm cho tập luyện nhẹ.', false, 'Nu/donu9.jpg'],
        ['Đồ thể thao nữ 10', 'Nữ', 530000, 'Trang phục nữ gọn nhẹ, thoải mái khi vận động.', false, 'Nu/donu10.jpg'],
        ['Phụ kiện thể thao 01', 'Phụ kiện thể thao', 190000, 'Phụ kiện hỗ trợ tập luyện và thi đấu.', true, 'phukien-thethao/phukien1.jpg'],
        ['Phụ kiện thể thao 02', 'Phụ kiện thể thao', 230000, 'Món phụ kiện tiện dụng cho người tập thể thao.', false, 'phukien-thethao/phukien2.jpg'],
        ['Phụ kiện thể thao 03', 'Phụ kiện thể thao', 270000, 'Phụ kiện chất lượng, dễ mang theo.', false, 'phukien-thethao/phukien3.jpg'],
        ['Phụ kiện thể thao 04', 'Phụ kiện thể thao', 210000, 'Phụ kiện cơ bản cho luyện tập hằng ngày.', false, 'phukien-thethao/phukien4.jpg'],
        ['Phụ kiện thể thao 05', 'Phụ kiện thể thao', 240000, 'Sản phẩm phụ kiện bền và gọn.', false, 'phukien-thethao/phukien5.jpg'],
        ['Phụ kiện thể thao 06', 'Phụ kiện thể thao', 290000, 'Phụ kiện thể thao đa năng.', false, 'phukien-thethao/phukien6.jpg'],
        ['Phụ kiện thể thao 07', 'Phụ kiện thể thao', 260000, 'Thiết kế nhỏ gọn, phù hợp mang theo khi tập.', false, 'phukien-thethao/phukien7.jpg'],
        ['Phụ kiện thể thao 08', 'Phụ kiện thể thao', 180000, 'Phụ kiện giá tốt cho người mới bắt đầu.', false, 'phukien-thethao/phukien8.jpg'],
        ['Phụ kiện thể thao 09', 'Phụ kiện thể thao', 310000, 'Phụ kiện nổi bật cho tập luyện ngoài trời.', false, 'phukien-thethao/phukien9.jpg'],
        ['Phụ kiện thể thao 10', 'Phụ kiện thể thao', 220000, 'Phụ kiện tiện lợi cho nhiều môn thể thao.', false, 'phukien-thethao/phukien10.jpg'],
        ['Áo cộc tay thể thao nam', 'Quần áo thể thao', 360000, 'Áo cộc tay thể thao nam thoáng mát, phù hợp tập luyện.', true, 'quanao-thethao/aococtaynma.jpg'],
        ['Áo polo thể thao nam', 'Quần áo thể thao', 420000, 'Áo polo thể thao nam lịch sự, dễ mặc hằng ngày.', false, 'quanao-thethao/aopolotethaonam.jpg'],
        ['Áo polo thể thao Riki', 'Quần áo thể thao', 450000, 'Áo polo thể thao chất liệu thoáng và bền màu.', false, 'quanao-thethao/aopolothethaoriki.jpg'],
        ['Áo tập yoga nữ dài tay', 'Quần áo thể thao', 390000, 'Áo tập yoga nữ dài tay, co giãn tốt.', false, 'quanao-thethao/aotapyoganudaitay.jpg'],
        ['Áo thể thao bra nữ', 'Quần áo thể thao', 320000, 'Áo bra thể thao nữ hỗ trợ vận động ổn định.', false, 'quanao-thethao/aothethaobranu.jpg'],
        ['Quần áo bóng chuyền nam', 'Quần áo thể thao', 520000, 'Bộ quần áo bóng chuyền nam thoáng khí.', false, 'quanao-thethao/quanaobongchuyennam.jpg'],
        ['Quần áo bóng chuyền nữ Beyini Heronii', 'Quần áo thể thao', 540000, 'Bộ đồ bóng chuyền nữ nổi bật, dễ vận động.', false, 'quanao-thethao/quanaobongchuyennubeyiniheronii.jpg'],
        ['Quần áo bóng đá Beyyono Rage', 'Quần áo thể thao', 560000, 'Bộ quần áo bóng đá chất liệu nhanh khô.', false, 'quanao-thethao/quanaobongdabeyyonorage.jpg'],
        ['Quần áo bóng đá Just Play Raine', 'Quần áo thể thao', 530000, 'Bộ đồ bóng đá form thể thao năng động.', false, 'quanao-thethao/quanaobongdajustplayraine.jpg'],
        ['Quần áo bóng đá Riki Star Sweep', 'Quần áo thể thao', 580000, 'Bộ quần áo bóng đá Riki cho tập luyện và thi đấu.', false, 'quanao-thethao/quanaobongdarikistarsweep.jpg'],
        ['Quần đùi chạy bộ siêu nhẹ', 'Quần áo thể thao', 280000, 'Quần đùi chạy bộ nhẹ, thoáng và nhanh khô.', false, 'quanao-thethao/quanduichaybosieunhe.jpg'],
    ];

    foreach ($products as $product_data) {
        [$name, $category, $price, $description, $featured, $image_url] = $product_data;

        $existing_product = get_page_by_title($name, OBJECT, 'product');

        if (!$existing_product) {
            $existing_product = get_page_by_path(sanitize_title($name), OBJECT, 'product');
        }

        if ($existing_product) {
            $product = wc_get_product((int) $existing_product->ID);

            if ($product instanceof WC_Product) {
                $product->set_description($description);
                $product->set_short_description($description);
                $product->set_regular_price((string) $price);
                $product->set_featured($featured);
                $product->save();
            }

            wp_set_object_terms((int) $existing_product->ID, $category, 'product_cat');
            sportzone_demo_set_product_image((int) $existing_product->ID, $image_url, $name, $category);
            continue;
        }

        $product = new WC_Product_Simple();
        $product->set_name($name);
        $product->set_slug(sanitize_title($name));
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_description($description);
        $product->set_short_description($description);
        $product->set_regular_price((string) $price);
        $product->set_manage_stock(true);
        $product->set_stock_quantity(25);
        $product->set_stock_status('instock');
        $product->set_featured($featured);
        $product_id = $product->save();

        if ($product_id) {
            wp_set_object_terms($product_id, $category, 'product_cat');
            sportzone_demo_set_product_image((int) $product_id, $image_url, $name, $category);
        }
    }
}

function sportzone_demo_set_product_image(int $product_id, string $image_url, string $name, string $category = ''): void
{
    $image_path = $image_url;
    $image_url = sportzone_demo_image_url($image_url);

    if (has_post_thumbnail($product_id) && !sportzone_demo_has_generated_thumbnail($product_id) && sportzone_demo_thumbnail_source($product_id) === $image_url) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = sportzone_demo_find_image_attachment($image_url);

    if (!$attachment_id) {
        $attachment_id = sportzone_demo_create_local_image_attachment($image_path, $image_url, $name, $product_id);
    }

    if (!$attachment_id) {
        $attachment_id = media_sideload_image($image_url, $product_id, $name, 'id');

        if (!is_wp_error($attachment_id) && $attachment_id) {
            update_post_meta((int) $attachment_id, '_sportzone_source_image', $image_url);
        }
    }

    if (is_wp_error($attachment_id)) {
        $attachment_id = sportzone_demo_create_product_placeholder($product_id, $name, $category);
    }

    if (!is_wp_error($attachment_id) && $attachment_id) {
        set_post_thumbnail($product_id, (int) $attachment_id);
        update_post_meta($product_id, '_thumbnail_id', (int) $attachment_id);
    }
}

function sportzone_demo_image_url(string $path): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return content_url('images/' . ltrim($path, '/'));
}

function sportzone_demo_create_local_image_attachment(string $path, string $image_url, string $name, int $product_id): int
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return 0;
    }

    $relative_path = ltrim(str_replace('\\', '/', $path), '/');
    $file_path = wp_normalize_path(WP_CONTENT_DIR . '/images/' . $relative_path);

    if (!file_exists($file_path)) {
        return 0;
    }

    $filetype = wp_check_filetype(basename($file_path));
    $attachment_id = wp_insert_attachment([
        'guid'           => $image_url,
        'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
        'post_title'     => $name,
        'post_content'   => '',
        'post_status'    => 'inherit',
    ], '', $product_id);

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    update_post_meta((int) $attachment_id, '_sportzone_source_image', $image_url);

    return (int) $attachment_id;
}

function sportzone_demo_find_image_attachment(string $image_url): int
{
    $attachments = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_key'       => '_sportzone_source_image',
        'meta_value'     => $image_url,
    ]);

    return $attachments ? (int) $attachments[0] : 0;
}

function sportzone_demo_thumbnail_source(int $product_id): string
{
    $thumbnail_id = get_post_thumbnail_id($product_id);

    if (!$thumbnail_id) {
        return '';
    }

    return (string) get_post_meta($thumbnail_id, '_sportzone_source_image', true);
}

function sportzone_demo_has_generated_thumbnail(int $product_id): bool
{
    $thumbnail_id = get_post_thumbnail_id($product_id);

    if (!$thumbnail_id) {
        return false;
    }

    $file = (string) get_attached_file($thumbnail_id);

    return basename($file) !== '' && str_starts_with(basename($file), 'sportzone-');
}

function sportzone_demo_create_product_placeholder(int $product_id, string $name, string $category = '')
{
    $upload = wp_upload_dir();

    if (!empty($upload['error'])) {
        return new WP_Error('sportzone_upload_error', $upload['error']);
    }

    $safe_name = sanitize_title($name);
    $use_png = function_exists('imagecreatetruecolor') && function_exists('imagepng');
    $filename = 'sportzone-v2-' . $safe_name . ($use_png ? '.png' : '.svg');
    $filepath = trailingslashit($upload['path']) . $filename;
    $fileurl = trailingslashit($upload['url']) . $filename;
    $mime_type = $use_png ? 'image/png' : 'image/svg+xml';

    if (!file_exists($filepath)) {
        if ($use_png) {
            sportzone_demo_write_png_placeholder($filepath, $name, $category);
        } else {
            $label = esc_html($name);
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="900" height="900" viewBox="0 0 900 900">
  <rect width="900" height="900" fill="#f4f6f8"/>
  <rect x="46" y="46" width="808" height="808" rx="34" fill="#ffffff" stroke="#d7dde6" stroke-width="8"/>
  <circle cx="450" cy="340" r="150" fill="#d9291c"/>
  <path d="M260 580c105-72 246-95 380-43 39 15 76 37 110 65" fill="none" stroke="#111827" stroke-width="38" stroke-linecap="round"/>
  <path d="M325 333c72 39 157 39 250 0" fill="none" stroke="#ffffff" stroke-width="34" stroke-linecap="round"/>
  <text x="450" y="720" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="44" font-weight="700" fill="#111827">{$label}</text>
  <text x="450" y="778" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="700" fill="#007f7a">SPORTZONE</text>
</svg>
SVG;
            $written = file_put_contents($filepath, $svg);

            if (!$written) {
                return new WP_Error('sportzone_file_error', 'Cannot create demo product image.');
            }
        }
    }

    $attachment_id = wp_insert_attachment([
        'guid'           => $fileurl,
        'post_mime_type' => $mime_type,
        'post_title'     => $name,
        'post_content'   => '',
        'post_status'    => 'inherit',
    ], $filepath, $product_id);

    if (is_wp_error($attachment_id)) {
        return $attachment_id;
    }

    update_post_meta($attachment_id, '_wp_attached_file', trailingslashit($upload['subdir']) . $filename);

    if ($use_png) {
        $metadata = wp_generate_attachment_metadata($attachment_id, $filepath);
        wp_update_attachment_metadata($attachment_id, $metadata);
    }

    return $attachment_id;
}

function sportzone_demo_write_png_placeholder(string $filepath, string $name, string $category = ''): void
{
    $image = imagecreatetruecolor(900, 900);

    $bg = imagecolorallocate($image, 244, 246, 248);
    $panel = imagecolorallocate($image, 255, 255, 255);
    $line = imagecolorallocate($image, 215, 221, 230);
    $ink = imagecolorallocate($image, 17, 24, 39);
    $white = imagecolorallocate($image, 255, 255, 255);
    $teal = imagecolorallocate($image, 0, 127, 122);
    $red = imagecolorallocate($image, 217, 41, 28);
    $yellow = imagecolorallocate($image, 246, 183, 47);
    $blue = imagecolorallocate($image, 45, 99, 210);
    $green = imagecolorallocate($image, 32, 140, 90);
    $purple = imagecolorallocate($image, 112, 79, 201);
    $orange = imagecolorallocate($image, 232, 113, 33);

    $type = sportzone_demo_product_visual_type($name, $category);
    $accent = match ($type) {
        'shirt' => $teal,
        'ball' => $green,
        'racket' => $purple,
        'bag' => $orange,
        'band' => $blue,
        default => $red,
    };

    imagefill($image, 0, 0, $bg);
    imagefilledrectangle($image, 54, 54, 846, 846, $panel);
    imagerectangle($image, 54, 54, 846, 846, $line);

    imagefilledrectangle($image, 92, 92, 808, 555, imagecolorallocate($image, 250, 252, 253));
    imagefilledellipse($image, 740, 150, 90, 90, $yellow);

    sportzone_demo_draw_product_icon($image, $type, $accent, $ink, $white, $line);

    $short_name = function_exists('mb_substr') ? mb_substr($name, 0, 28) : substr($name, 0, 28);
    $title = strtoupper($short_name);
    imagestring($image, 5, max(40, (900 - strlen($title) * 10) / 2), 668, $title, $ink);
    imagestring($image, 5, 397, 728, 'SPORTZONE', $teal);
    imagestring($image, 3, 380, 770, strtoupper($category ?: 'THE THAO'), $accent);

    imagepng($image, $filepath);
    imagedestroy($image);
}

function sportzone_demo_product_visual_type(string $name, string $category): string
{
    $text = strtolower(remove_accents($name . ' ' . $category));

    if (str_contains($text, 'ao')) {
        return 'shirt';
    }

    if (str_contains($text, 'bong')) {
        return 'ball';
    }

    if (str_contains($text, 'vot')) {
        return 'racket';
    }

    if (str_contains($text, 'tui')) {
        return 'bag';
    }

    if (str_contains($text, 'day')) {
        return 'band';
    }

    return 'shoe';
}

function sportzone_demo_draw_product_icon($image, string $type, int $accent, int $ink, int $white, int $line): void
{
    imagesetthickness($image, 18);

    switch ($type) {
        case 'shirt':
            imagefilledpolygon($image, [330, 210, 405, 165, 450, 205, 495, 165, 570, 210, 620, 335, 540, 365, 520, 510, 380, 510, 360, 365, 280, 335], 10, $accent);
            imagefilledrectangle($image, 385, 250, 515, 510, $accent);
            imagearc($image, 450, 210, 110, 88, 0, 180, $white);
            imageline($image, 390, 255, 510, 255, $white);
            break;

        case 'ball':
            imagefilledellipse($image, 450, 350, 300, 300, $accent);
            imagearc($image, 450, 350, 250, 250, 0, 360, $white);
            imageline($image, 300, 350, 600, 350, $white);
            imageline($image, 450, 200, 450, 500, $white);
            imagearc($image, 360, 350, 130, 285, 270, 90, $white);
            imagearc($image, 540, 350, 130, 285, 90, 270, $white);
            break;

        case 'racket':
            imageellipse($image, 410, 300, 190, 260, $accent);
            imageellipse($image, 410, 300, 145, 210, $line);
            imageline($image, 475, 405, 600, 535, $ink);
            imageline($image, 600, 535, 650, 585, $ink);
            imagefilledellipse($image, 585, 245, 80, 80, $accent);
            imagesetthickness($image, 6);
            for ($x = 360; $x <= 460; $x += 25) {
                imageline($image, $x, 185, $x, 415, $line);
            }
            for ($y = 220; $y <= 380; $y += 30) {
                imageline($image, 325, $y, 495, $y, $line);
            }
            break;

        case 'bag':
            imagefilledrectangle($image, 300, 285, 600, 510, $accent);
            imagearc($image, 450, 295, 190, 180, 180, 360, $ink);
            imagefilledrectangle($image, 335, 330, 565, 375, $white);
            imageline($image, 355, 430, 545, 430, $ink);
            break;

        case 'band':
            imagearc($image, 450, 330, 420, 220, 205, 335, $accent);
            imagearc($image, 450, 410, 420, 220, 25, 155, $accent);
            imagefilledrectangle($image, 240, 338, 330, 398, $ink);
            imagefilledrectangle($image, 570, 338, 660, 398, $ink);
            imagefilledellipse($image, 450, 368, 110, 110, $accent);
            break;

        default:
            imagefilledpolygon($image, [245, 420, 375, 315, 575, 350, 665, 430, 615, 500, 360, 500], 6, $accent);
            imagefilledrectangle($image, 315, 455, 640, 525, $ink);
            imagearc($image, 435, 360, 260, 155, 200, 345, $white);
            imageline($image, 390, 395, 520, 410, $white);
            break;
    }
}

add_action('admin_notices', 'sportzone_demo_admin_notice');

function sportzone_demo_admin_notice(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!get_option('sportzone_demo_content_created')) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p>';
    echo esc_html__('Dữ liệu mẫu SportZone đã được tạo. Bạn có thể tắt plugin SportZone Demo Content sau khi kiểm tra xong.', 'sportzone-demo');
    echo '</p></div>';
}
