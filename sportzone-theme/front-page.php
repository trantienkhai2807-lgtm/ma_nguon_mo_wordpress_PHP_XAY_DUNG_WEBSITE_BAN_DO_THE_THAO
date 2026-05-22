<?php
/**
 * Front page template.
 *
 * @package SportZone
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<section class="hero">
    <div class="hero__content">
        <span class="hero__eyebrow"><?php esc_html_e('Bộ sưu tập mới 2026', 'sportzone'); ?></span>
        <h1><?php esc_html_e('Dụng cụ thể thao cho mọi mục tiêu tập luyện', 'sportzone'); ?></h1>
        <p><?php esc_html_e('Giày chạy bộ, đồ gym, bóng đá, vợt tennis và phụ kiện chọn lọc cho người tập nghiêm túc.', 'sportzone'); ?></p>
        <div class="hero__actions">
            <a class="button button--light" href="<?php echo esc_url(class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop')); ?>">
                <?php esc_html_e('Xem sản phẩm', 'sportzone'); ?>
            </a>
            <a class="button button--ghost" href="#featured-products">
                <?php esc_html_e('Sản phẩm nổi bật', 'sportzone'); ?>
            </a>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="section__inner">
        <div class="section__head">
            <div>
                <h2><?php esc_html_e('Danh mục phổ biến', 'sportzone'); ?></h2>
                <p><?php esc_html_e('Sắp xếp theo môn tập để khách hàng tìm đúng sản phẩm nhanh hơn.', 'sportzone'); ?></p>
            </div>
        </div>
        <div class="category-grid">
            <?php
            $categories = [
                ['Quần áo thể thao', 'Áo tập, áo polo, quần short', sportzone_image_url('quanao-thethao/aococtaynma.jpg'), 'quan-ao-the-thao'],
                ['Giày thể thao', 'Giày nam, giày nữ, giày tập luyện', sportzone_image_url('giay-thethao/giaythethao9.jpg'), 'giay-the-thao'],
                ['Dụng cụ thể thao', 'Bóng, tạ, dây tập và thiết bị', sportzone_image_url('dungcu-thethao/dungcu2.jpg'), 'dung-cu-the-thao'],
                ['Phụ kiện thể thao', 'Túi, bảo hộ và phụ kiện tập', sportzone_image_url('phukien-thethao/phukien3.jpg'), 'phu-kien-the-thao'],
            ];

            foreach ($categories as $category) :
                ?>
                <a class="category-tile" href="<?php echo esc_url(sportzone_product_category_url($category[3])); ?>">
                    <img src="<?php echo esc_url($category[2]); ?>" alt="<?php echo esc_attr($category[0]); ?>">
                    <div class="category-tile__body">
                        <h3><?php echo esc_html($category[0]); ?></h3>
                        <span><?php echo esc_html($category[1]); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="featured-products" class="section">
    <div class="section__inner">
        <div class="section__head">
            <div>
                <h2><?php esc_html_e('Sản phẩm nổi bật', 'sportzone'); ?></h2>
                <p><?php esc_html_e('Những sản phẩm được chọn lọc cho tập luyện hằng ngày và thi đấu phong trào.', 'sportzone'); ?></p>
            </div>
            <a class="button" href="<?php echo esc_url(class_exists('WooCommerce') ? wc_get_page_permalink('shop') : home_url('/shop')); ?>">
                <?php esc_html_e('Xem tất cả', 'sportzone'); ?>
            </a>
        </div>

        <?php if (class_exists('WooCommerce')) : ?>
            <?php echo do_shortcode('[products limit="4" columns="4" visibility="featured"]'); ?>
        <?php else : ?>
            <div class="product-grid">
                <?php foreach (sportzone_demo_products() as $product) : ?>
                    <article class="product-card">
                        <img class="product-card__image" src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>">
                        <div class="product-card__body">
                            <span class="product-card__tag"><?php echo esc_html($product['cat']); ?></span>
                            <h3><?php echo esc_html($product['name']); ?></h3>
                            <span class="price"><?php echo esc_html($product['price']); ?></span>
                            <a class="button" href="<?php echo esc_url(home_url('/shop')); ?>"><?php esc_html_e('Thêm vào giỏ', 'sportzone'); ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section feature-band">
    <div class="section__inner">
        <div class="feature-list">
            <div class="feature">
                <strong><?php esc_html_e('Chính hãng', 'sportzone'); ?></strong>
                <p><?php esc_html_e('Nguồn hàng rõ ràng, bảo hành theo từng thương hiệu.', 'sportzone'); ?></p>
            </div>
            <div class="feature">
                <strong><?php esc_html_e('Tư vấn size', 'sportzone'); ?></strong>
                <p><?php esc_html_e('Hướng dẫn chọn size giày và quần áo theo từng môn tập.', 'sportzone'); ?></p>
            </div>
            <div class="feature">
                <strong><?php esc_html_e('Giao nhanh', 'sportzone'); ?></strong>
                <p><?php esc_html_e('Xử lý đơn trong ngày tại các thành phố lớn.', 'sportzone'); ?></p>
            </div>
            <div class="feature">
                <strong><?php esc_html_e('Đổi trả 7 ngày', 'sportzone'); ?></strong>
                <p><?php esc_html_e('Đổi size hoặc màu khi sản phẩm còn tem mác.', 'sportzone'); ?></p>
            </div>
        </div>
    </div>
</section>
<?php
get_footer();
