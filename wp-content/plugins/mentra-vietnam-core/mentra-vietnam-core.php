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
        add_action('init', [__CLASS__, 'maybe_create_mentra_live_product'], 25);
        add_action('init', [__CLASS__, 'maybe_create_news_category'], 26);
        add_action('init', [__CLASS__, 'maybe_import_mentra_articles'], 30);
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

    /**
     * Catalog-only product seed for Mentra Live, Phase 3. Self-healing and
     * idempotent like maybe_create_pages(): gated by an option flag so it
     * only does work once, but also re-checks by SKU before creating
     * anything, so it is always safe to run again (e.g. after the option
     * is cleared, or on a fresh staging copy of the DB) without ever
     * producing a duplicate product. Never overwrites an existing product
     * - if one is already found by SKU, this is a no-op.
     */
    public static function maybe_create_mentra_live_product() {
        if (get_option('mentra_vn_product_mentra_live_v1')) { return; }
        if (!class_exists('WC_Product_Variable')) { return; }
        // add_option() fails (returns false) if the option row already
        // exists - this is an atomic "insert if not exists" at the DB
        // level, used as a lock so two near-simultaneous requests can't
        // both pass the get_option() check above and both create a
        // product before either finishes. Needed because
        // wc_get_product_id_by_sku() reads a lookup table that can lag
        // behind a same-request insert, so the SKU check inside
        // create_mentra_live_product() alone is not always fast enough.
        if (!add_option('mentra_vn_product_mentra_live_lock', 1, '', 'no')) { return; }
        self::create_mentra_live_product();
        update_option('mentra_vn_product_mentra_live_v1', 1);
    }

    /**
     * Idempotent by SKU 'MENTRA-LIVE': if a product with that SKU already
     * exists (created by a prior run, or manually in wp-admin), this does
     * nothing and returns its ID. No price is ever set - this catalog is
     * explicitly non-transactional (see functions.php catalog-only
     * hardening). Source data (name, images, color/stock variants) is
     * taken from the Shopify product object captured in WGET_REFERENCE
     * live.html (gid://shopify/Product/8963292102908), NOT the price.
     */
    public static function create_mentra_live_product($force = false) {
        if (!class_exists('WC_Product_Variable')) { return 0; }
        // Query postmeta directly rather than wc_get_product_id_by_sku(),
        // which reads a lookup table that can lag behind a same-request
        // insert (see maybe_create_mentra_live_product()).
        $existing = get_posts([
            'post_type' => 'product',
            'post_status' => 'any',
            'meta_key' => '_sku',
            'meta_value' => 'MENTRA-LIVE',
            'numberposts' => 1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);
        $existing_id = $existing ? (int) $existing[0] : 0;
        if ($existing_id && !$force) { return $existing_id; }

        $product = new WC_Product_Variable();
        $product->set_name('Mentra Live Camera Glasses');
        $product->set_slug('mentra-live-camera-glasses');
        $product->set_sku('MENTRA-LIVE');
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_description(
            "Kính thông minh mở cho AI và công việc thực tế.\n\n" .
            "Camera HD, loa stereo, ba microphone và SDK mở để xây dựng quy trình AI tùy chỉnh cho nhà phát triển và doanh nghiệp.\n\n" .
            "Khối lượng 43g, camera 1080p (FOV 119°), 3 microphone, loa stereo, pin kính 260 mAh, hộp sạc 2.200 mAh, thời lượng hỗn hợp 12+ giờ theo công bố. Nền tảng: MentraOS."
        );
        $product->set_short_description('Kính thông minh mở cho AI và công việc thực tế, xây dựng trên nền tảng MentraOS.');

        $attribute = new WC_Product_Attribute();
        $attribute->set_id(0);
        $attribute->set_name('Màu sắc');
        $attribute->set_options(['Đen', 'Trong suốt']);
        $attribute->set_position(0);
        $attribute->set_visible(true);
        $attribute->set_variation(true);
        $product->set_attributes([$attribute]);

        $featured_id = self::sideload_theme_asset('closed_mentra_live.webp', 'Mentra Live');
        if ($featured_id) { $product->set_image_id($featured_id); }
        $gallery_ids = array_filter([
            self::sideload_theme_asset('product_photos/frame.png', 'Mentra Live - khung kính'),
            self::sideload_theme_asset('product_photos/frame2.png', 'Mentra Live - khung kính (2)'),
            self::sideload_theme_asset('micro_charge_cable_mentra_live.png', 'Mentra Live - cáp sạc'),
            self::sideload_theme_asset('product_photos/chargingcase.webp', 'Mentra Live - hộp sạc'),
        ]);
        if ($gallery_ids) { $product->set_gallery_image_ids(array_values($gallery_ids)); }

        // Canonical frontend URL for redirecting this product's own
        // /product/mentra-live-camera-glasses/ page away from itself -
        // see mentra_vn_product_canonical_redirect() in functions.php.
        $product->update_meta_data('_mentra_vn_canonical_url', '/mentra-live/');
        $product_id = $product->save();
        if (!$product_id) { return 0; }

        update_post_meta($product_id, '_yoast_wpseo_meta-robots-noindex', '1');

        $variations = [
            ['color' => 'Đen', 'sku' => 'MENTRA-LIVE-DEN', 'stock' => 'instock'],
            ['color' => 'Trong suốt', 'sku' => 'MENTRA-LIVE-TRONG-SUOT', 'stock' => 'outofstock'],
        ];
        foreach ($variations as $v) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($product_id);
            $variation->set_attributes(['mau-sac' => $v['color']]);
            $variation->set_sku($v['sku']);
            $variation->set_manage_stock(false);
            $variation->set_stock_status($v['stock']);
            $variation->set_status('publish');
            $variation->save();
        }

        if (class_exists('WC_Product_Variable')) {
            WC_Product_Variable::sync($product_id);
        }
        wc_delete_product_transients($product_id);

        return $product_id;
    }

    /**
     * Phase 4 news category for the 16 owned articles. Idempotent: checks
     * for the term by slug before creating, and is gated by an option flag
     * so normal operation only ever attempts creation once. Press items are
     * explicitly NOT a WordPress taxonomy - they are static data (see
     * wp-content/themes/mentra-vietnam/data/press.php) and never get a
     * category or any other taxonomy term.
     */
    public static function maybe_create_news_category() {
        if (get_option('mentra_vn_news_category_v1')) { return; }
        if (!term_exists('bai-viet', 'category')) {
            wp_insert_term('Bài viết', 'category', ['slug' => 'bai-viet']);
        }
        update_option('mentra_vn_news_category_v1', 1);
    }

    /**
     * Idempotent, self-healing, resumable import of the 16 owned Mentra
     * articles as real post_type=post entries. Deliberately follows the
     * same no-lock pattern as maybe_create_pages() rather than the
     * single-item add_option() lock used by
     * maybe_create_mentra_live_product(): this imports a *list* of items,
     * and create_mentra_article() itself is idempotent per-item (checks by
     * the '_mentra_vn_legacy_slug' postmeta before ever inserting), so a
     * request-level lock isn't needed for correctness here and would
     * actively hurt self-healing - if a batch run partially completes
     * (e.g. a transient failure importing one article), a lock would
     * permanently freeze the migration at "partially done" since the flag
     * never gets set but the lock blocks every retry. Without a lock, the
     * next request that fires 'init' just re-checks the (cheap) 16
     * existence queries and imports whatever is still missing, and only
     * sets the "done" option once all 16 are confirmed present - after
     * that, every request is a single get_option() no-op. Never deletes or
     * overwrites an existing post - if an admin has since edited an
     * imported article's title/body/excerpt/thumbnail/category, this
     * routine will never touch it again once that post exists.
     */
    public static function maybe_import_mentra_articles() {
        if (get_option('mentra_vn_news_migration_v1')) { return; }
        self::maybe_create_news_category();
        require_once __DIR__ . '/data/mentra-articles.php';
        $all_present = true;
        foreach (mentra_vn_articles() as $article) {
            if (!self::create_mentra_article($article)) { $all_present = false; }
        }
        if ($all_present) {
            update_option('mentra_vn_news_migration_v1', 1);
        }
    }

    /**
     * Idempotent by '_mentra_vn_legacy_slug' postmeta: if an article with
     * that source slug already exists, this is a no-op and returns its ID.
     * $data comes from mentra_vn_articles() in data/mentra-articles.php -
     * see docs/news-migration-manifest.md for the full source/verification
     * table. {{THEME_URI}}/{{HOME_URL}} placeholders in the body match the
     * same convention mentra_vn_render_source() uses for static pages.
     */
    public static function create_mentra_article($data, $force = false) {
        $existing = get_posts([
            'post_type' => 'post',
            'post_status' => 'any',
            'meta_key' => '_mentra_vn_legacy_slug',
            'meta_value' => $data['slug'],
            'numberposts' => 1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);
        $existing_id = $existing ? (int) $existing[0] : 0;
        if ($existing_id && !$force) { return $existing_id; }

        $category = get_term_by('slug', 'bai-viet', 'category');
        $body = str_replace(
            ['{{THEME_URI}}', '{{HOME_URL}}'],
            [MENTRA_VN_THEME_URI, untrailingslashit(home_url('/'))],
            $data['body']
        );

        $post_id = wp_insert_post([
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => $data['title'],
            'post_name' => $data['slug'],
            'post_content' => $body,
            'post_excerpt' => $data['excerpt'],
            'post_date' => $data['date'],
            'post_date_gmt' => $data['date'],
            'post_category' => $category ? [$category->term_id] : [],
        ]);
        if (!$post_id || is_wp_error($post_id)) { return 0; }

        update_post_meta($post_id, '_mentra_vn_article', 1);
        update_post_meta($post_id, '_mentra_vn_legacy_slug', $data['slug']);
        update_post_meta($post_id, '_mentra_vn_article_authors', $data['authors']);

        if (!empty($data['image'])) {
            $featured_id = self::sideload_theme_asset($data['image'], $data['title']);
            if ($featured_id) { set_post_thumbnail($post_id, $featured_id); }
        }

        return $post_id;
    }

    /**
     * Copies a theme asset into the Media Library, idempotently (tracked
     * by the '_mentra_vn_source_asset' meta key so re-running never
     * creates duplicate attachments for the same source file).
     */
    private static function sideload_theme_asset($relative_path, $title) {
        $existing = get_posts([
            'post_type' => 'attachment',
            'meta_key' => '_mentra_vn_source_asset',
            'meta_value' => $relative_path,
            'numberposts' => 1,
            'fields' => 'ids',
        ]);
        if ($existing) { return (int) $existing[0]; }

        $file_path = get_template_directory() . '/assets/' . $relative_path;
        if (!is_file($file_path)) { return 0; }

        $filetype = wp_check_filetype(basename($file_path));
        if (empty($filetype['type'])) { return 0; }

        $contents = file_get_contents($file_path);
        if ($contents === false) { return 0; }

        $upload = wp_upload_bits(basename($file_path), null, $contents);
        if (!empty($upload['error'])) { return 0; }

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $filetype['type'],
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit',
        ], $upload['file']);
        if (!$attachment_id) { return 0; }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        update_post_meta($attachment_id, '_mentra_vn_source_asset', $relative_path);

        return $attachment_id;
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
