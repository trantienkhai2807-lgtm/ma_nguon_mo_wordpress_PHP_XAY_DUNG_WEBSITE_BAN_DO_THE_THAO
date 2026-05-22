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

function sportzone_demo_ensure_woocommerce_pages(): void
{
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

function sportzone_demo_create_category_pages(): void
{
    $pages = [
        [
            'title'   => 'Quần áo thể thao',
            'slug'    => 'quan-ao-the-thao',
            'summary' => 'Áo tập, áo khoác, quần short và trang phục thể thao thoáng khí cho luyện tập hằng ngày.',
            'shortcode' => '[products limit="8" columns="4" category="tap-luyen,training"]',
        ],
        [
            'title'   => 'Giày thể thao',
            'slug'    => 'giay-the-thao',
            'summary' => 'Giày chạy bộ, giày sân cỏ và giày tập luyện có độ bám tốt, êm chân và dễ phối đồ.',
            'shortcode' => '[products limit="8" columns="4" category="chay-bo,running,bong-da,football"]',
        ],
        [
            'title'   => 'Dụng cụ thể thao',
            'slug'    => 'dung-cu-the-thao',
            'summary' => 'Bóng, dây kháng lực và dụng cụ hỗ trợ cho tập luyện tại nhà, phòng gym hoặc sân thi đấu.',
            'shortcode' => '[products limit="8" columns="4" category="bong-da,football,tap-luyen,training"]',
        ],
        [
            'title'   => 'Phụ kiện thể thao',
            'slug'    => 'phu-kien-the-thao',
            'summary' => 'Túi đựng vợt, phụ kiện tennis, phụ kiện chạy bộ và các món cần thiết khi tập luyện.',
            'shortcode' => '[products limit="8" columns="4" category="tennis,chay-bo,running"]',
        ],
        [
            'title'   => 'Thể thao nam',
            'slug'    => 'nam',
            'summary' => 'Sản phẩm thể thao dành cho nam: giày, áo tập, bóng đá, tennis và phụ kiện.',
            'shortcode' => '[products limit="8" columns="4"]',
        ],
        [
            'title'   => 'Thể thao nữ',
            'slug'    => 'nu',
            'summary' => 'Sản phẩm thể thao dành cho nữ: giày chạy bộ, áo tập, phụ kiện và trang phục năng động.',
            'shortcode' => '[products limit="8" columns="4"]',
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
        'Chạy bộ'   => 'Giày chạy bộ, áo khoác, túi nước và phụ kiện runner.',
        'Tập luyện' => 'Đồ tập gym, dây kháng lực, găng tay và phụ kiện tập luyện.',
        'Bóng đá'   => 'Bóng đá, giày sân cỏ, tất và bảo vệ ống đồng.',
        'Tennis'    => 'Vợt tennis, bóng tennis, túi vợt và băng cổ tay.',
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
        ['Giày chạy bộ Velocity Pro', 'Chạy bộ', 1890000, 'Giày chạy bộ đệm êm, đế cao su bám đường, phù hợp tập daily run và race 10K.', true, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80'],
        ['Áo khoác gió Runner Shield', 'Chạy bộ', 790000, 'Áo khoác nhẹ chống gió, có túi khóa kéo, dùng cho buổi chạy sáng và tối.', false, 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&w=1000&q=80'],
        ['Áo tập gym DryFit Elite', 'Tập luyện', 490000, 'Áo tập thoáng khí, nhanh khô, form gọn cho tập gym và HIIT.', true, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1000&q=80'],
        ['Dây kháng lực Power Band', 'Tập luyện', 220000, 'Dây kháng lực đa năng cho khởi động, phục hồi và tập sức mạnh.', false, 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=1000&q=80'],
        ['Bóng đá sân cỏ FIFA Quality', 'Bóng đá', 650000, 'Bóng đá size 5, độ nảy tốt, phù hợp tập luyện và thi đấu phong trào.', true, 'https://images.unsplash.com/photo-1614632537423-1e6c2e7e0aab?auto=format&fit=crop&w=1000&q=80'],
        ['Giày đá bóng Turf Control', 'Bóng đá', 1290000, 'Giày sân cỏ nhân tạo, upper bền, đế TF bám sân tốt.', false, 'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?auto=format&fit=crop&w=1000&q=80'],
        ['Vợt tennis Carbon Strike', 'Tennis', 2250000, 'Vợt tennis khung carbon, cân bằng tốt cho người chơi trung cấp.', true, 'https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=1000&q=80'],
        ['Túi đựng vợt Court Pack', 'Tennis', 890000, 'Túi đựng 2 vợt, ngăn riêng cho giày và phụ kiện cá nhân.', false, 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=1000&q=80'],
    ];

    foreach ($products as $product_data) {
        [$name, $category, $price, $description, $featured, $image_url] = $product_data;

        $existing_product = get_page_by_title($name, OBJECT, 'product');

        if (!$existing_product) {
            $existing_product = get_page_by_path(sanitize_title($name), OBJECT, 'product');
        }

        if ($existing_product) {
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
    if (has_post_thumbnail($product_id) && !sportzone_demo_has_generated_thumbnail($product_id)) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_sideload_image($image_url, $product_id, $name, 'id');

    if (is_wp_error($attachment_id)) {
        $attachment_id = sportzone_demo_create_product_placeholder($product_id, $name, $category);
    }

    if (!is_wp_error($attachment_id) && $attachment_id) {
        set_post_thumbnail($product_id, (int) $attachment_id);
    }
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
