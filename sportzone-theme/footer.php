<?php
/**
 * Footer template.
 *
 * @package SportZone
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
    </main>
    <footer class="site-footer">
        <div class="footer__inner">
            <section>
                <h2><?php bloginfo('name'); ?></h2>
                <p><?php esc_html_e('Cửa hàng dụng cụ, giày, quần áo và phụ kiện thể thao cho tập luyện hằng ngày đến thi đấu bán chuyên.', 'sportzone'); ?></p>
            </section>
            <section>
                <h3><?php esc_html_e('Mua sắm', 'sportzone'); ?></h3>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/shop')); ?>"><?php esc_html_e('Tất cả sản phẩm', 'sportzone'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/quan-ao-the-thao')); ?>"><?php esc_html_e('Quần áo thể thao', 'sportzone'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/giay-the-thao')); ?>"><?php esc_html_e('Giày thể thao', 'sportzone'); ?></a></li>
                </ul>
            </section>
            <section>
                <h3><?php esc_html_e('Hỗ trợ', 'sportzone'); ?></h3>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/van-chuyen')); ?>"><?php esc_html_e('Vận chuyển', 'sportzone'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/doi-tra')); ?>"><?php esc_html_e('Đổi trả', 'sportzone'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/bang-size')); ?>"><?php esc_html_e('Bảng size', 'sportzone'); ?></a></li>
                </ul>
            </section>
            <section>
                <h3><?php esc_html_e('Liên hệ', 'sportzone'); ?></h3>
                <p>1900 6868<br>support@sportzone.local<br>08:00 - 21:00</p>
            </section>
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom__inner">
                <?php echo esc_html(date_i18n('Y')); ?> &copy; <?php bloginfo('name'); ?>.
            </div>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
