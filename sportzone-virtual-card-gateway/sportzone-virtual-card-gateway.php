<?php
/**
 * Plugin Name: SportZone Virtual Card Gateway
 * Description: Thêm phương thức thanh toán bằng thẻ ảo cho WooCommerce, chỉ dùng cho demo/đồ án.
 * Version: 1.0.0
 * Author: Codex
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: sportzone-card
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'sportzone_virtual_card_init');

function sportzone_virtual_card_init(): void
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class SportZone_Virtual_Card_Gateway extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'sportzone_virtual_card';
            $this->method_title = __('Thẻ ảo SportZone', 'sportzone-card');
            $this->method_description = __('Thanh toán bằng thẻ ảo chỉ dùng cho demo/đồ án. Không dùng cho thanh toán thật.', 'sportzone-card');
            $this->has_fields = true;
            $this->supports = ['products'];

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->test_card = preg_replace('/\D+/', '', (string) $this->get_option('test_card'));
            $this->test_cvv = preg_replace('/\D+/', '', (string) $this->get_option('test_cvv'));

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public function init_form_fields(): void
        {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __('Bật/Tắt', 'sportzone-card'),
                    'type'    => 'checkbox',
                    'label'   => __('Bật thanh toán bằng thẻ ảo', 'sportzone-card'),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => __('Tiêu đề', 'sportzone-card'),
                    'type'        => 'text',
                    'description' => __('Tên hiển thị ở trang thanh toán.', 'sportzone-card'),
                    'default'     => __('Thanh toán bằng thẻ ảo', 'sportzone-card'),
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'   => __('Mô tả', 'sportzone-card'),
                    'type'    => 'textarea',
                    'default' => __('Dùng thẻ demo: 4242 4242 4242 4242, CVV 123, ngày hết hạn bất kỳ trong tương lai.', 'sportzone-card'),
                ],
                'test_card' => [
                    'title'       => __('Số thẻ demo', 'sportzone-card'),
                    'type'        => 'text',
                    'default'     => '4242424242424242',
                    'description' => __('Chỉ nhận đúng số thẻ này.', 'sportzone-card'),
                    'desc_tip'    => true,
                ],
                'test_cvv' => [
                    'title'       => __('CVV demo', 'sportzone-card'),
                    'type'        => 'text',
                    'default'     => '123',
                    'description' => __('Chỉ nhận đúng CVV này.', 'sportzone-card'),
                    'desc_tip'    => true,
                ],
            ];
        }

        public function payment_fields(): void
        {
            if ($this->description) {
                echo wpautop(wp_kses_post($this->description));
            }
            ?>
            <fieldset class="sportzone-card-fields">
                <p class="form-row form-row-wide">
                    <label for="sportzone_card_name"><?php esc_html_e('Tên chủ thẻ', 'sportzone-card'); ?> <span class="required">*</span></label>
                    <input id="sportzone_card_name" name="sportzone_card_name" type="text" autocomplete="cc-name" placeholder="NGUYEN VAN A">
                </p>
                <p class="form-row form-row-wide">
                    <label for="sportzone_card_number"><?php esc_html_e('Số thẻ ảo', 'sportzone-card'); ?> <span class="required">*</span></label>
                    <input id="sportzone_card_number" name="sportzone_card_number" type="text" inputmode="numeric" autocomplete="cc-number" placeholder="4242 4242 4242 4242">
                </p>
                <p class="form-row form-row-first">
                    <label for="sportzone_card_expiry"><?php esc_html_e('Ngày hết hạn', 'sportzone-card'); ?> <span class="required">*</span></label>
                    <input id="sportzone_card_expiry" name="sportzone_card_expiry" type="text" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY">
                </p>
                <p class="form-row form-row-last">
                    <label for="sportzone_card_cvv"><?php esc_html_e('CVV', 'sportzone-card'); ?> <span class="required">*</span></label>
                    <input id="sportzone_card_cvv" name="sportzone_card_cvv" type="password" inputmode="numeric" autocomplete="cc-csc" placeholder="123">
                </p>
                <div class="clear"></div>
            </fieldset>
            <?php
        }

        public function validate_fields(): bool
        {
            $name = isset($_POST['sportzone_card_name']) ? sanitize_text_field(wp_unslash($_POST['sportzone_card_name'])) : '';
            $number = isset($_POST['sportzone_card_number']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['sportzone_card_number'])) : '';
            $expiry = isset($_POST['sportzone_card_expiry']) ? sanitize_text_field(wp_unslash($_POST['sportzone_card_expiry'])) : '';
            $cvv = isset($_POST['sportzone_card_cvv']) ? preg_replace('/\D+/', '', (string) wp_unslash($_POST['sportzone_card_cvv'])) : '';

            if ($name === '' || $number === '' || $expiry === '' || $cvv === '') {
                wc_add_notice(__('Vui lòng nhập đầy đủ thông tin thẻ ảo.', 'sportzone-card'), 'error');
                return false;
            }

            if ($number !== $this->test_card || $cvv !== $this->test_cvv) {
                wc_add_notice(__('Thẻ ảo không hợp lệ. Hãy dùng số thẻ 4242 4242 4242 4242 và CVV 123.', 'sportzone-card'), 'error');
                return false;
            }

            if (!$this->expiry_is_valid($expiry)) {
                wc_add_notice(__('Ngày hết hạn không hợp lệ hoặc đã quá hạn. Định dạng đúng là MM/YY.', 'sportzone-card'), 'error');
                return false;
            }

            return true;
        }

        public function process_payment($order_id): array
        {
            $order = wc_get_order($order_id);

            if (!$order) {
                wc_add_notice(__('Không tìm thấy đơn hàng.', 'sportzone-card'), 'error');
                return ['result' => 'failure'];
            }

            $order->payment_complete();
            $order->add_order_note(__('Đã thanh toán bằng thẻ ảo SportZone. Không có giao dịch ngân hàng thật.', 'sportzone-card'));

            if (WC()->cart) {
                WC()->cart->empty_cart();
            }

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }

        private function expiry_is_valid(string $expiry): bool
        {
            if (!preg_match('/^\s*(0[1-9]|1[0-2])\s*\/\s*(\d{2}|\d{4})\s*$/', $expiry, $matches)) {
                return false;
            }

            $month = (int) $matches[1];
            $year = (int) $matches[2];

            if ($year < 100) {
                $year += 2000;
            }

            $expiry_time = strtotime(sprintf('%04d-%02d-01 23:59:59', $year, $month) . ' last day of this month');

            return $expiry_time !== false && $expiry_time >= current_time('timestamp');
        }
    }

    add_filter('woocommerce_payment_gateways', 'sportzone_add_virtual_card_gateway');
}

function sportzone_add_virtual_card_gateway(array $gateways): array
{
    $gateways[] = 'SportZone_Virtual_Card_Gateway';

    return $gateways;
}
