<?php
/**
 * Plugin Name: Mentra Vietnam Core 99
 * Description: Chức năng WordPress cho bản Mentra Việt Nam: tạo route/page tiếng Việt, newsletter, form liên hệ và cấu hình email.
 * Version: 3.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 */
if (!defined('ABSPATH')) { exit; }

final class Mentra_Vietnam_Core_99 {
    const OPT = 'mentra_vn_settings';

    public static function init() {
        add_action('init', [__CLASS__, 'register_subscriber_cpt']);
        add_action('init', [__CLASS__, 'maybe_create_pages'], 20);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('wp_ajax_mentra_vn_newsletter', [__CLASS__, 'newsletter']);
        add_action('wp_ajax_nopriv_mentra_vn_newsletter', [__CLASS__, 'newsletter']);
        add_action('wp_ajax_mentra_vn_contact_ajax', [__CLASS__, 'contact_ajax']);
        add_action('wp_ajax_nopriv_mentra_vn_contact_ajax', [__CLASS__, 'contact_ajax']);
    }

    public static function activate() {
        self::register_subscriber_cpt();
        self::create_pages();
        flush_rewrite_rules();
    }

    public static function register_subscriber_cpt() {
        register_post_type('mentra_subscriber', [
            'labels' => [
                'name' => 'Newsletter Mentra',
                'singular_name' => 'Người đăng ký',
                'menu_name' => 'Newsletter Mentra',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-email-alt2',
        ]);
    }

    private static function page($title, $slug) {
        if (get_page_by_path($slug, OBJECT, 'page')) { return; }
        wp_insert_post([
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
    }

    public static function create_pages() {
        $pages = [
            'Mentra Live' => 'mentra-live',
            'MentraOS' => 'mentra-os',
            'Ứng dụng' => 'ung-dung',
            'Nhà phát triển' => 'nha-phat-trien',
            'Về Mentra' => 've-mentra',
            'Hỗ trợ' => 'ho-tro',
            'Liên hệ' => 'lien-he',
            'Tin tức' => 'tin-tuc',
            'So sánh' => 'so-sanh',
            'Tròng kính độ' => 'trong-kinh',
            'Phụ đề' => 'phu-de',
            'Mentra Notes' => 'mentra-notes',
            'Tuyển dụng' => 'tuyen-dung',
            'Mạng xã hội' => 'mang-xa-hoi',
            'Đối tác' => 'doi-tac',
            'Liên hệ truyền thông' => 'truyen-thong',
            'Chính sách quyền riêng tư' => 'chinh-sach-quyen-rieng-tu',
            'Điều khoản dịch vụ' => 'dieu-khoan-dich-vu',
            'Chính sách vận chuyển' => 'chinh-sach-van-chuyen',
            'Chính sách đổi trả' => 'chinh-sach-doi-tra',
            'Khả năng tiếp cận' => 'kha-nang-tiep-can',
            'Thu hồi sản phẩm' => 'thu-hoi',
            'NIMO' => 'nimo',
            'Even Realities' => 'even-realities',
            'Discord' => 'discord',
            'Legacy MentraOS' => 'legacy',
        ];
        // Deliberately excludes 'quyen-rieng-tu' (maps to the anomalous
        // 'privacy' source key, which is byte-identical to the homepage in
        // the captured source and must not be published as real content).
        foreach ($pages as $title => $slug) { self::page($title, $slug); }
    }

    /**
     * Self-healing, idempotent page sync. Runs once (gated by an option
     * flag bumped whenever the canonical page list below changes) rather
     * than relying solely on the activation hook, which does not fire
     * again if the plugin was already active when this list changed.
     * create_pages()/page() are themselves idempotent (skip existing
     * slugs), so this is safe to trigger repeatedly.
     */
    public static function maybe_create_pages() {
        if (get_option('mentra_vn_pages_synced_v2')) { return; }
        self::create_pages();
        update_option('mentra_vn_pages_synced_v2', 1);
    }

    public static function admin_menu() {
        add_options_page('Mentra Việt Nam', 'Mentra Việt Nam', 'manage_options', 'mentra-vietnam', [__CLASS__, 'settings_page']);
    }

    public static function register_settings() {
        register_setting('mentra_vn_group', self::OPT, ['sanitize_callback' => [__CLASS__, 'sanitize_settings']]);
    }

    public static function sanitize_settings($value) {
        $value = is_array($value) ? $value : [];
        return [
            'company' => sanitize_text_field($value['company'] ?? 'Mentra Việt Nam'),
            'sales_email' => sanitize_email($value['sales_email'] ?? get_option('admin_email')),
            'support_email' => sanitize_email($value['support_email'] ?? get_option('admin_email')),
            'phone' => sanitize_text_field($value['phone'] ?? ''),
        ];
    }

    private static function settings() {
        return wp_parse_args(get_option(self::OPT, []), [
            'company' => 'Mentra Việt Nam',
            'sales_email' => get_option('admin_email'),
            'support_email' => get_option('admin_email'),
            'phone' => '',
        ]);
    }

    public static function settings_page() {
        $v = self::settings(); ?>
        <div class="wrap">
            <h1>Mentra Việt Nam</h1>
            <p>Theme v3 dùng giao diện từ bản mirror Mentra. Tại đây bạn chỉ cần cấu hình nơi nhận thông tin khách hàng.</p>
            <form method="post" action="options.php">
                <?php settings_fields('mentra_vn_group'); ?>
                <table class="form-table" role="presentation">
                    <tr><th><label for="mentra-company">Tên đơn vị</label></th><td><input id="mentra-company" class="regular-text" name="<?php echo esc_attr(self::OPT); ?>[company]" value="<?php echo esc_attr($v['company']); ?>"></td></tr>
                    <tr><th><label for="mentra-sales">Email kinh doanh</label></th><td><input id="mentra-sales" class="regular-text" type="email" name="<?php echo esc_attr(self::OPT); ?>[sales_email]" value="<?php echo esc_attr($v['sales_email']); ?>"></td></tr>
                    <tr><th><label for="mentra-support">Email hỗ trợ</label></th><td><input id="mentra-support" class="regular-text" type="email" name="<?php echo esc_attr(self::OPT); ?>[support_email]" value="<?php echo esc_attr($v['support_email']); ?>"></td></tr>
                    <tr><th><label for="mentra-phone">Điện thoại</label></th><td><input id="mentra-phone" class="regular-text" name="<?php echo esc_attr(self::OPT); ?>[phone]" value="<?php echo esc_attr($v['phone']); ?>"></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div><?php
    }

    private static function verify_public_nonce() {
        if (!check_ajax_referer('mentra_vn_public', 'nonce', false)) {
            wp_send_json_error(['message' => 'Phiên làm việc không hợp lệ. Vui lòng tải lại trang.'], 403);
        }
    }

    public static function newsletter() {
        self::verify_public_nonce();
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        if (!$email || !is_email($email)) {
            wp_send_json_error(['message' => 'Email không hợp lệ.'], 400);
        }
        $existing = get_posts([
            'post_type' => 'mentra_subscriber',
            'post_status' => 'any',
            'title' => $email,
            'numberposts' => 1,
            'fields' => 'ids',
        ]);
        if (!$existing) {
            wp_insert_post(['post_type' => 'mentra_subscriber', 'post_status' => 'publish', 'post_title' => $email]);
        }
        wp_send_json_success(['message' => 'Đăng ký thành công.']);
    }

    public static function contact_ajax() {
        self::verify_public_nonce();
        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $company = sanitize_text_field(wp_unslash($_POST['company'] ?? ''));
        $subject = sanitize_text_field(wp_unslash($_POST['subject'] ?? 'Liên hệ'));
        $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
        if (!$name || !is_email($email) || !$message) {
            wp_send_json_error(['message' => 'Vui lòng nhập đầy đủ họ tên, email và nội dung.'], 400);
        }
        $settings = self::settings();
        $support_words = ['support', 'hỗ trợ', 'technical'];
        $to = $settings['sales_email'];
        foreach ($support_words as $word) {
            if (stripos($subject, $word) !== false) { $to = $settings['support_email']; break; }
        }
        if (!$to || !is_email($to)) { $to = get_option('admin_email'); }
        $mail_subject = '[Mentra Việt Nam] ' . $subject . ' - ' . $name;
        $body = "Họ tên: {$name}\nEmail: {$email}\nCông ty: {$company}\nChủ đề: {$subject}\n\n{$message}";
        $sent = wp_mail($to, $mail_subject, $body, ['Reply-To: ' . $name . ' <' . $email . '>']);
        if (!$sent) { wp_send_json_error(['message' => 'Không thể gửi email. Vui lòng thử lại.'], 500); }
        wp_send_json_success(['message' => 'Đã gửi yêu cầu.']);
    }
}
Mentra_Vietnam_Core_99::init();
register_activation_hook(__FILE__, ['Mentra_Vietnam_Core_99', 'activate']);
