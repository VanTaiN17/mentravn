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

    // Phase 5: single recipient source of truth for all six business-contact
    // form types. Deliberately one option (not a per-type split) per the
    // current business rule that all contact mail goes to one address; the
    // $type/FORM_TYPES metadata is still threaded through every submission
    // so recipients can be split by type later without any frontend change.
    const CONTACT_EMAIL_OPTION = 'mentra_vn_contact_email';
    const CONTACT_EMAIL_DEFAULT = 'contact@domain.vn';

    // Server-generated subject prefixes - never trust a client-provided prefix.
    const FORM_TYPES = [
        'general' => '[MENTRA - LIÊN HỆ]',
        'sales' => '[MENTRA - KINH DOANH]',
        'support' => '[MENTRA - HỖ TRỢ]',
        'partnership' => '[MENTRA - ĐỐI TÁC]',
        'media' => '[MENTRA - TRUYỀN THÔNG]',
        'career' => '[MENTRA - TUYỂN DỤNG]',
    ];

    // Final forms hotfix: server-side product allowlist for Purchase mode.
    // Never trust raw $_GET/$_POST['product'] text - the display name shown
    // in the UI and in mail always comes from this map, keyed by a stable,
    // publicly-known slug (never an invented value).
    const PRODUCT_ALLOWLIST = [
        'mentra-live' => 'Mentra Live',
        'mentra-live-charging-cable' => 'Infinity Cable cho Mentra Live',
    ];

    // Forms UX hotfix: Vietnamese labels for the six form types, used in the
    // locked-context UI and in both HTML email templates. Kept separate from
    // the theme layer's own copy (functions.php) deliberately - these two
    // are independent layers (frontend badge text vs. backend email body),
    // each already reads its own copy for its own purpose.
    const TYPE_LABELS_VI = [
        'general' => 'Chung',
        'sales' => 'Kinh doanh',
        'support' => 'Hỗ trợ',
        'partnership' => 'Đối tác',
        'media' => 'Truyền thông',
        'career' => 'Tuyển dụng',
    ];

    // Whitelist of the #career-expertise select's fixed option values
    // (templates/source/careers.html) - no new options invented.
    const CAREER_EXPERTISE = [
        'Software Engineering',
        'Hardware / Electrical Engineering',
        'AI / Machine Learning',
        'Product Design / UX',
        'Marketing / Growth',
        'Operations / Business',
        'Community / Content',
        'Other',
    ];

    // Phase 6: reCAPTCHA v2 Checkbox configuration. Site key is public by
    // nature (it goes into frontend markup); the secret key is server-only
    // and must never be localized into JS or echoed on the frontend.
    const RECAPTCHA_SITE_KEY_OPTION = 'mentra_vn_recaptcha_site_key';
    const RECAPTCHA_SECRET_KEY_OPTION = 'mentra_vn_recaptcha_secret_key';
    const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    // Phase 6: server-side rate limiting, WP transients only (no custom DB
    // table). Same policy for all seven public form types (six business
    // types + newsletter) - see docs/phase-6-report.md for the exact
    // semantics (sliding window, not a strict fixed calendar window).
    const RATE_LIMIT_MAX = 5;
    const RATE_LIMIT_WINDOW = 600; // 10 minutes

    public static function init() {
        add_action('init', [__CLASS__, 'maybe_set_site_locale'], 5);
        add_action('init', [__CLASS__, 'register_subscriber_cpt']);
        add_action('init', [__CLASS__, 'maybe_create_pages'], 20);
        add_action('init', [__CLASS__, 'maybe_noindex_duplicate_pages'], 21);
        add_action('init', [__CLASS__, 'maybe_noindex_product_archive'], 21);
        add_action('init', [__CLASS__, 'maybe_noindex_product_category_archive'], 21);
        add_action('init', [__CLASS__, 'maybe_set_site_identity'], 5);
        add_action('init', [__CLASS__, 'maybe_set_page_meta_descriptions'], 22);
        add_action('init', [__CLASS__, 'maybe_create_mentra_live_product'], 25);
        add_action('init', [__CLASS__, 'maybe_create_infinity_cable_product'], 25);
        add_action('init', [__CLASS__, 'maybe_create_news_category'], 26);
        add_action('init', [__CLASS__, 'maybe_import_mentra_articles'], 30);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_notices', [__CLASS__, 'recaptcha_admin_notice']);
        add_action('wp_ajax_mentra_vn_newsletter', [__CLASS__, 'newsletter']);
        add_action('wp_ajax_nopriv_mentra_vn_newsletter', [__CLASS__, 'newsletter']);
        add_action('wp_ajax_mentra_vn_contact_ajax', [__CLASS__, 'contact_ajax']);
        add_action('wp_ajax_nopriv_mentra_vn_contact_ajax', [__CLASS__, 'contact_ajax']);
        add_action('wp_ajax_mentra_vn_career_ajax', [__CLASS__, 'career_ajax']);
        add_action('wp_ajax_nopriv_mentra_vn_career_ajax', [__CLASS__, 'career_ajax']);
    }

    public static function activate() {
        self::register_subscriber_cpt();
        self::create_pages();
        flush_rewrite_rules();
    }

    /**
     * Phase 8: root-cause fix for a long-standing SEO defect - the frontend
     * always rendered `<html lang="vi-VN">` (functions.php regex-patches
     * language_attributes() output), but the site's actual WordPress locale
     * (the 'WPLANG' option, empty by default = 'en_US') was never changed,
     * so anything that reads get_locale() directly instead of the patched
     * HTML - Yoast's og:locale, its JSON-LD inLanguage - kept reporting
     * en_US/en-US. Confirmed live: get_locale() was 'en_US' despite
     * lang="vi-VN" on every page. Setting the real WordPress site locale
     * (equivalent to an admin choosing "Tiếng Việt" at Settings → General)
     * fixes it at the source for every locale-derived output, not just the
     * one the old regex patch happened to target - verified live afterward
     * that Yoast's og:locale/inLanguage both switched to vi_VN/vi correctly.
     * Idempotent (checks the current value first) and self-healing on a
     * fresh production DB/install, matching this plugin's existing
     * maybe_create_pages()-style pattern - not a one-off manual DB edit.
     * No 'vi' translation files need to be installed for this to be
     * correct: every user-facing string on this site is custom
     * theme/plugin-rendered Vietnamese content, not core WP i18n strings,
     * so there is nothing for WordPress to "translate" - only the
     * locale-derived metadata (html lang, Yoast/OG/schema locale) changes.
     */
    public static function maybe_set_site_locale() {
        if (get_option('WPLANG') !== 'vi') {
            update_option('WPLANG', 'vi');
        }
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
            // Phase 8: was previously only reachable via the 404.php soft-render
            // fallback (mentra_vn_get_source_key()'s raw-path branch), never a
            // real published Page - meaning it served HTTP 200 content with a
            // stale "Page not found" <title> and no Yoast robots/canonical meta
            // at all (is_404() stays true even after 404.php's status_header(200)
            // override). Publishing it as a real Page fixes both; rendering is
            // unchanged (still mentra_vn_render_source('get') via the normal
            // source-map path.php route). See docs/phase-8-report.md.
            'Tải ứng dụng' => 'tai-ung-dung',
            // Phase 4.6: real Shopify product page (gid://shopify/Product/9286483280124,
            // handle mentra-live-charging-cable), not an anchor/section inside Mentra
            // Live - see docs/product-classification.md. Rendered by
            // page-mentra-live-charging-cable.php; canonical URL is rewritten to
            // /products/mentra-live-charging-cable/ (see functions.php).
            'Infinity Cable cho Mentra Live' => 'mentra-live-charging-cable',
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
        // Bumped to v4 for Phase 8's tai-ung-dung page (see create_pages()) -
        // create_pages()/page() are idempotent per-slug, so this only creates
        // the one new page, it does not touch or recreate any of the existing
        // ones.
        if (get_option('mentra_vn_pages_synced_v4')) { return; }
        self::create_pages();
        update_option('mentra_vn_pages_synced_v4', 1);
    }

    /**
     * Phase 8: Yoast's page-sitemap.xml was found listing eight duplicate/
     * system pages that each only ever exist to immediately redirect
     * somewhere else at runtime: the four legal stub slugs
     * (mentra_vn_legal_slug_redirects() in functions.php) and WooCommerce's
     * own default shop/cart/checkout/my-account pages
     * (mentra_vn_disable_woocommerce_public_routes()). The runtime redirect
     * is already correct (a real visitor or crawler following the link is
     * never shown duplicate content), but each is still a real published
     * WP Page, so Yoast has no way to know not to list it - it only
     * respects Yoast's own noindex meta. Same fix already used for the
     * WooCommerce product duplicate URLs (see
     * '_yoast_wpseo_meta-robots-noindex' in create_mentra_live_product()/
     * create_infinity_cable_product()), applied here too, so this is
     * consistent with the existing pattern rather than a new one. Cannot
     * simply unpublish these pages: the legal stub pages need to stay
     * is_page()-resolvable for their own redirect to fire, and
     * shop/cart/checkout/my-account are WooCommerce's own required anchor
     * pages (referenced by its own page-id options). Idempotent
     * (checks/skips already-set meta) and safe to run on every 'init'.
     */
    public static function maybe_noindex_duplicate_pages() {
        $slugs = ['doi-tra', 'van-chuyen', 'dieu-khoan', 'chinh-sach-bao-mat', 'shop', 'cart', 'checkout', 'my-account'];
        foreach ($slugs as $slug) {
            $page = get_page_by_path($slug, OBJECT, 'page');
            if (!$page) { continue; }
            if (get_post_meta($page->ID, '_yoast_wpseo_meta-robots-noindex', true) !== '1') {
                update_post_meta($page->ID, '_yoast_wpseo_meta-robots-noindex', '1');
            }
        }
    }

    /**
     * Phase 8: real SEO metadata fix - confirmed live that the WordPress
     * site title ('blogname' option) was still the literal install slug
     * "mentra-vn" (never set to a real name), which Yoast uses as the
     * %%sitename%% token in every page's <title> tag - so every single
     * page's browser-tab/search-result title ended in "- mentra-vn", and
     * the homepage's own title (built from
     * "%%sitename%% %%page%% %%sep%% %%sitedesc%%" with both %%page%% and
     * %%sitedesc%% empty) rendered as the literally broken "mentra-vn -".
     * 'blogdescription' (the tagline, also feeds Yoast's homepage fallback
     * description) was empty too. Both values are real, accurate, existing
     * facts about this project (the name from CLAUDE.md/every doc in this
     * repo; the tagline is the homepage's own real hero line, not invented
     * copy) - this is a root-cause site-configuration fix, not new content.
     */
    public static function maybe_set_site_identity() {
        if (get_option('blogname') === 'mentra-vn') {
            update_option('blogname', 'Mentra Việt Nam');
        }
        if (get_option('blogdescription') === '') {
            update_option('blogdescription', 'Kính thông minh mã nguồn mở dành cho đội ngũ hiện trường và nhà phát triển xây dựng quy trình AI tùy chỉnh.');
        }
        // Homepage isn't a real WP Page (show_on_front is 'posts', but
        // front-page.php overrides that at render time - see functions.php)
        // so there's no post to attach a per-page Yoast description to;
        // this is Yoast's own site-wide home-description option instead.
        // Same real homepage tagline as blogdescription above.
        if (class_exists('WPSEO_Options') && WPSEO_Options::get('metadesc-home-wpseo', '') === '') {
            WPSEO_Options::set('metadesc-home-wpseo', 'Kính thông minh mã nguồn mở dành cho đội ngũ hiện trường và nhà phát triển xây dựng quy trình AI tùy chỉnh.');
        }
    }

    /**
     * Phase 8: real SEO metadata fix, companion to
     * maybe_set_site_identity(). Several priority pages had NO meta
     * description at all, or leaked broken placeholder text into one -
     * confirmed live. Root cause: this site's real visible content is
     * rendered from templates/source/*.html (mentra_vn_render_source()),
     * completely separate from each WP Page's own post_content field -
     * Yoast's automatic description fallback reads that disconnected
     * post_content directly, which for these pages is either empty or a
     * stale stub (e.g. /lien-he/'s post_content still contains a literal,
     * never-registered "[mentra_contact_form]" shortcode remnant from
     * before this architecture existed - that string was leaking straight
     * into og:description). Fixing the actual post_content wholesale is
     * out of scope (risks the visual/content rules), so this sets a real
     * Yoast per-page description directly instead - each string below is
     * copied verbatim from that exact page's own already-published,
     * approved copy (the page's real intro paragraph/heading), never
     * invented. /ve-mentra/ already has a correct one (confirmed live) and
     * is intentionally not touched; the 16 real WordPress Posts already
     * get a correct auto-generated description from their own real
     * post_content and don't need this either.
     */
    public static function maybe_set_page_meta_descriptions() {
        $descriptions = [
            'lien-he' => 'Liên hệ Mentra Việt Nam. Trao đổi về đặt hàng, pilot doanh nghiệp, hợp tác, truyền thông hoặc hỗ trợ kỹ thuật.',
            'tin-tuc' => 'Tin tức và bài viết về kính thông minh.',
            'mentra-live' => 'Kính thông minh tích hợp camera, loa, micro và SDK mở cho các quy trình AI tùy chỉnh. Được thiết kế cho nhà phát triển và triển khai doanh nghiệp.',
            'mentra-live-charging-cable' => 'Sạc Mentra Live ngay cả khi đang di chuyển với Infinity Cable. Kéo dài thời gian sử dụng kính thông minh gần như không giới hạn.',
            'mentra-os' => 'MentraOS mang hệ sinh thái ứng dụng, SDK dành cho nhà phát triển và trải nghiệm liền mạch đến kính thông minh của bạn — 100% mã nguồn mở.',
            'even-realities' => 'Kết nối Even Realities G1 hoặc G2 với Mentra để dùng phụ đề, ghi chú, dịch thuật và nhiều tính năng khác trong một ứng dụng mã nguồn mở.',
        ];
        foreach ($descriptions as $slug => $description) {
            $page = get_page_by_path($slug, OBJECT, 'page');
            if (!$page) { continue; }
            if (get_post_meta($page->ID, '_yoast_wpseo_metadesc', true) === '') {
                update_post_meta($page->ID, '_yoast_wpseo_metadesc', $description);
            }
        }
    }

    /**
     * Phase 8: companion to maybe_noindex_duplicate_pages(). That method
     * correctly removed the shop/cart/checkout/my-account PAGES from
     * page-sitemap.xml, but product-sitemap.xml separately listed
     * '/shop/' again anyway - confirmed live. Root cause: Yoast represents
     * WooCommerce's product post type ARCHIVE (which the 'shop' page IS)
     * as its own dedicated sitemap entry, built from
     * get_post_type_archive_link()/'noindex-ptarchive-product' Yoast
     * option - a completely separate code path from the per-post
     * '_yoast_wpseo_meta-robots-noindex' postmeta used for individual
     * pages/products, so setting that meta on the shop page alone can
     * never affect this. This is Yoast's own native "don't index this post
     * type's archive" setting (the same one exposed in Search Appearance ->
     * Content Types -> Products -> Show Products in search results, set to
     * No) - using it here instead of a URL-matching sitemap filter is the
     * root-cause fix, not a workaround.
     */
    public static function maybe_noindex_product_archive() {
        if (!class_exists('WPSEO_Options')) { return; }
        if (WPSEO_Options::get('noindex-ptarchive-product', false)) { return; }
        WPSEO_Options::set('noindex-ptarchive-product', true);
    }

    /**
     * Phase 8: same problem, one taxonomy level down. Both real products
     * are filed under WooCommerce's default "Uncategorized" product_cat
     * term (no real category taxonomy exists on this catalog-only site),
     * so /product-category/uncategorized/ was publicly reachable serving
     * WooCommerce's stock English "Coming Soon" placeholder - confirmed
     * live. That route now redirects (see
     * mentra_vn_disable_woocommerce_public_routes() in functions.php), but
     * the term archive was still separately listed in
     * product_cat-sitemap.xml (a taxonomy-archive sitemap entry, a
     * different code path than the post-type-archive one
     * maybe_noindex_product_archive() fixes). This site has no plan for a
     * public product-category browsing UI at all (catalog-only, no
     * "shop by category" flow), so noindexing the entire product_cat
     * taxonomy archive - not just this one term - is the correct, durable
     * fix, not a one-term patch.
     */
    public static function maybe_noindex_product_category_archive() {
        if (!class_exists('WPSEO_Options')) { return; }
        if (WPSEO_Options::get('noindex-tax-product_cat', false)) { return; }
        WPSEO_Options::set('noindex-tax-product_cat', true);
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
     * Phase 4.6: Infinity Cable is a real Mentra product page
     * (gid://shopify/Product/9286483280124, handle mentra-live-charging-cable),
     * not an anchor/section inside Mentra Live - see
     * docs/product-classification.md for the re-opened classification. Same
     * idempotent-lock pattern as maybe_create_mentra_live_product(), for the
     * same reason (a single all-or-nothing item, not a resumable list).
     */
    public static function maybe_create_infinity_cable_product() {
        if (get_option('mentra_vn_product_infinity_cable_v1')) { return; }
        if (!class_exists('WC_Product_Simple')) { return; }
        if (!add_option('mentra_vn_product_infinity_cable_lock', 1, '', 'no')) { return; }
        self::create_infinity_cable_product();
        update_option('mentra_vn_product_infinity_cable_v1', 1);
    }

    /**
     * Idempotent by SKU 'MENTRA-INFINITY-CABLE': the source Shopify product
     * has no literal SKU string (only a GID), so this is a documented stable
     * internal SKU, not an invented variant. Simple (non-variable) product -
     * the source has exactly one variant, always available. No price is
     * ever set - see catalog-only hardening in functions.php.
     */
    public static function create_infinity_cable_product($force = false) {
        if (!class_exists('WC_Product_Simple')) { return 0; }
        $existing = get_posts([
            'post_type' => 'product',
            'post_status' => 'any',
            'meta_key' => '_sku',
            'meta_value' => 'MENTRA-INFINITY-CABLE',
            'numberposts' => 1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);
        $existing_id = $existing ? (int) $existing[0] : 0;
        if ($existing_id && !$force) { return $existing_id; }

        $product = new WC_Product_Simple();
        $product->set_name('Infinity Cable cho Mentra Live');
        $product->set_slug('infinity-cable-mentra-live');
        $product->set_sku('MENTRA-INFINITY-CABLE');
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');
        $product->set_description(
            "Cáp Infinity dùng để sạc, đồng bộ dữ liệu và lập trình cho Mentra Live.\n\n" .
            "Sạc kính ngay khi đang đeo, kết nối trực tiếp với Mentra Live để mở rộng thời gian sử dụng."
        );
        $product->set_short_description('Dùng để sạc, đồng bộ dữ liệu và lập trình cho Mentra Live.');

        $featured_id = self::sideload_theme_asset('infinitycable.png', 'Infinity Cable cho Mentra Live');
        if ($featured_id) { $product->set_image_id($featured_id); }
        $gallery_ids = array_filter([
            self::sideload_theme_asset('micro_charge_cable_mentra_live.png', 'Infinity Cable cho Mentra Live - đang kết nối'),
        ]);
        if ($gallery_ids) { $product->set_gallery_image_ids(array_values($gallery_ids)); }

        // Canonical frontend URL - see mentra_vn_product_canonical_redirect()
        // in functions.php, and mentra_vn_product_page_permalink() for the
        // /products/mentra-live-charging-cable/ rewrite on the WP Page itself.
        $product->update_meta_data('_mentra_vn_canonical_url', '/products/mentra-live-charging-cable/');
        $product_id = $product->save();
        if (!$product_id) { return 0; }

        update_post_meta($product_id, '_yoast_wpseo_meta-robots-noindex', '1');
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
        register_setting('mentra_vn_group', self::CONTACT_EMAIL_OPTION, [
            'type' => 'string',
            'sanitize_callback' => [__CLASS__, 'sanitize_contact_email'],
            'default' => self::CONTACT_EMAIL_DEFAULT,
        ]);
        register_setting('mentra_vn_group', self::RECAPTCHA_SITE_KEY_OPTION, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('mentra_vn_group', self::RECAPTCHA_SECRET_KEY_OPTION, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
            // Never exposed via the REST API - server-side only.
            'show_in_rest' => false,
        ]);
    }

    public static function sanitize_settings($value) {
        $value = is_array($value) ? $value : [];
        return [
            'company' => sanitize_text_field($value['company'] ?? 'Mentra Việt Nam'),
            'phone' => sanitize_text_field($value['phone'] ?? ''),
        ];
    }

    public static function sanitize_contact_email($value) {
        $value = sanitize_email((string) $value);
        return ($value && is_email($value)) ? $value : self::CONTACT_EMAIL_DEFAULT;
    }

    private static function settings() {
        return wp_parse_args(get_option(self::OPT, []), [
            'company' => 'Mentra Việt Nam',
            'phone' => '',
        ]);
    }

    /**
     * Current recipient for all six business-contact form types (General,
     * Sales, Support, Partnership, Media, Career). One address, editable in
     * wp-admin - see CONTACT_EMAIL_OPTION.
     */
    private static function contact_email() {
        $value = sanitize_email((string) get_option(self::CONTACT_EMAIL_OPTION, self::CONTACT_EMAIL_DEFAULT));
        if (!$value || !is_email($value)) { $value = get_option('admin_email'); }
        return $value;
    }

    public static function settings_page() {
        $v = self::settings();
        $contact_email = get_option(self::CONTACT_EMAIL_OPTION, self::CONTACT_EMAIL_DEFAULT);
        $site_key = get_option(self::RECAPTCHA_SITE_KEY_OPTION, '');
        $secret_key = get_option(self::RECAPTCHA_SECRET_KEY_OPTION, '');
        ?>
        <div class="wrap">
            <h1>Mentra Việt Nam</h1>
            <p>Theme v3 dùng giao diện từ bản mirror Mentra. Tại đây bạn chỉ cần cấu hình nơi nhận thông tin khách hàng.</p>
            <?php if (!self::recaptcha_enabled()) : ?>
                <div class="notice notice-warning"><p><strong>reCAPTCHA chưa được cấu hình.</strong> Các form công khai (Liên hệ, Đối tác, Truyền thông, Tuyển dụng, Đăng ký nhận tin) hiện <strong>không</strong> được bảo vệ khỏi spam/bot. Nhập Site Key và Secret Key bên dưới để bật bảo vệ.</p></div>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php settings_fields('mentra_vn_group'); ?>
                <table class="form-table" role="presentation">
                    <tr><th><label for="mentra-company">Tên đơn vị</label></th><td><input id="mentra-company" class="regular-text" name="<?php echo esc_attr(self::OPT); ?>[company]" value="<?php echo esc_attr($v['company']); ?>"></td></tr>
                    <tr><th><label for="mentra-contact-email">Email nhận liên hệ</label></th><td><input id="mentra-contact-email" class="regular-text" type="email" name="<?php echo esc_attr(self::CONTACT_EMAIL_OPTION); ?>" value="<?php echo esc_attr($contact_email); ?>"><p class="description">Áp dụng cho cả 6 loại form: Chung, Kinh doanh, Hỗ trợ, Đối tác, Truyền thông, Tuyển dụng.</p></td></tr>
                    <tr><th><label for="mentra-phone">Điện thoại</label></th><td><input id="mentra-phone" class="regular-text" name="<?php echo esc_attr(self::OPT); ?>[phone]" value="<?php echo esc_attr($v['phone']); ?>"></td></tr>
                    <tr><th><label for="mentra-recaptcha-site">reCAPTCHA Site Key</label></th><td><input id="mentra-recaptcha-site" class="regular-text" type="text" autocomplete="off" name="<?php echo esc_attr(self::RECAPTCHA_SITE_KEY_OPTION); ?>" value="<?php echo esc_attr($site_key); ?>"><p class="description">reCAPTCHA v2 "Hộp kiểm" (Checkbox) - không dùng v3/Invisible/Enterprise. Khóa này công khai, được nhúng vào HTML frontend.</p></td></tr>
                    <tr><th><label for="mentra-recaptcha-secret">reCAPTCHA Secret Key</label></th><td><input id="mentra-recaptcha-secret" class="regular-text" type="password" autocomplete="off" name="<?php echo esc_attr(self::RECAPTCHA_SECRET_KEY_OPTION); ?>" value="<?php echo esc_attr($secret_key); ?>"><p class="description">Chỉ dùng ở server để xác minh - không bao giờ xuất hiện ở frontend.</p></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div><?php
    }

    /**
     * wp-admin-only visibility for the unconfigured state - per Phase 6
     * scope, missing keys must not silently pretend protection exists.
     * Shown on every admin screen (not just the settings page) so it can't
     * be missed; deliberately not shown to frontend visitors.
     */
    public static function recaptcha_admin_notice() {
        if (self::recaptcha_enabled()) { return; }
        if (!current_user_can('manage_options')) { return; }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen && $screen->id === 'settings_page_mentra-vietnam') { return; } // already shown inline there
        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
            esc_html__('Mentra Việt Nam: reCAPTCHA chưa được cấu hình - các form công khai hiện không được bảo vệ khỏi spam.', 'mentra-vietnam'),
            esc_url(admin_url('options-general.php?page=mentra-vietnam')),
            esc_html__('Cấu hình ngay', 'mentra-vietnam')
        );
    }

    /**
     * Final forms hotfix: normalizes a client-submitted form type against
     * the FORM_TYPES whitelist. Never returns a value that isn't one of
     * those six keys.
     */
    private static function normalize_type($type) {
        $type = sanitize_key((string) $type);
        return isset(self::FORM_TYPES[$type]) ? $type : null;
    }

    public static function is_valid_product($key) {
        return is_string($key) && $key !== '' && isset(self::PRODUCT_ALLOWLIST[$key]);
    }

    public static function product_name($key) {
        return self::PRODUCT_ALLOWLIST[$key] ?? '';
    }

    /**
     * Final forms hotfix: replaces the old Referer-based route_locked_type().
     * This is the scoped-nonce action string for a given (already-whitelisted)
     * form type + Purchase context, used BOTH when a page renders (to create
     * the nonce for the exact context being rendered - see
     * mentra_vn_assets() in functions.php) and when the AJAX handler runs
     * (recomputed here from the client-submitted context after it has been
     * normalized/whitelisted, then verified against the client-submitted
     * nonce via verify_scoped_nonce()). Because the action string changes
     * whenever type/intent/product changes, a nonce issued for one context
     * can never verify against a different one - a client that edits
     * form_type/intent/product in devtools while replaying an old nonce
     * always fails check_ajax_referer(), regardless of what the (untrusted)
     * Referer header says. Referer is no longer read anywhere in this
     * class.
     */
    public static function contact_nonce_action($type, $intent = '', $product = '') {
        $type = self::normalize_type($type);
        if ($type === null) { return 'mentra_vn_contact_invalid'; }
        if ($type === 'sales' && $intent === 'purchase' && self::is_valid_product($product)) {
            return 'mentra_vn_contact_sales_purchase_' . $product;
        }
        return 'mentra_vn_contact_' . $type;
    }

    public static function create_contact_nonce($type, $intent = '', $product = '') {
        return wp_create_nonce(self::contact_nonce_action($type, $intent, $product));
    }

    private static function verify_public_nonce() {
        if (!check_ajax_referer('mentra_vn_public', 'nonce', false)) {
            wp_send_json_error(['message' => 'Phiên làm việc không hợp lệ. Vui lòng tải lại trang.'], 403);
        }
    }

    /**
     * Same failure shape as verify_public_nonce(), scoped to an arbitrary
     * action string - see contact_nonce_action().
     */
    private static function verify_scoped_nonce($action) {
        if (!check_ajax_referer($action, 'nonce', false)) {
            wp_send_json_error(['message' => 'Phiên làm việc không hợp lệ. Vui lòng tải lại trang.'], 403);
        }
    }

    /**
     * Purchase mode only. Accepts practical international phone formats
     * (digits, spaces, +, -, parentheses) rather than a fragile Vietnam-only
     * pattern that could reject a legitimate international customer.
     * Returns null (caller must reject) for anything empty, over-length,
     * containing disallowed characters, or with fewer than 6 digits.
     */
    private static function sanitize_phone($value) {
        $value = trim((string) $value);
        if ($value === '' || mb_strlen($value) > 30) { return null; }
        if (!preg_match('/^[0-9+\-\s()]+$/', $value)) { return null; }
        $digits = preg_replace('/\D/', '', $value);
        if (strlen($digits) < 6) { return null; }
        return $value;
    }

    /**
     * True only when both keys are non-empty. This is the single switch
     * that decides whether reCAPTCHA is enforced at all - see
     * enforce_recaptcha() for the fail-closed behavior once it is.
     */
    public static function recaptcha_enabled() {
        $site = trim((string) get_option(self::RECAPTCHA_SITE_KEY_OPTION, ''));
        $secret = trim((string) get_option(self::RECAPTCHA_SECRET_KEY_OPTION, ''));
        return $site !== '' && $secret !== '';
    }

    /**
     * Public by nature - safe to echo into frontend HTML/localized JS.
     */
    public static function recaptcha_site_key() {
        return trim((string) get_option(self::RECAPTCHA_SITE_KEY_OPTION, ''));
    }

    /**
     * REMOTE_ADDR only - deliberately ignores X-Forwarded-For/CF-Connecting-IP/
     * X-Real-IP, since this site has no configured trusted-proxy in front of
     * it and those headers are trivially spoofable without one. Returns ''
     * for anything that doesn't parse as a valid IP (never trust the raw
     * superglobal value further than that).
     */
    private static function client_ip() {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    /**
     * The raw IP is never persisted anywhere (not in transients, options, or
     * logs) - only this salted hash (via wp_hash(), which is keyed off the
     * site's own AUTH salts) is ever used as rate-limit key material.
     */
    private static function client_hash() {
        return substr(wp_hash('mentra_vn_client|' . self::client_ip()), 0, 24);
    }

    /**
     * Rate-limit transient key, scoped by client identity hash + a
     * whitelisted form type string (never raw user input - callers always
     * pass a value from FORM_TYPES or the literal 'newsletter').
     */
    private static function rate_limit_key($type) {
        return 'mentra_rl_' . substr(wp_hash('mentra_vn_rate|' . $type . '|' . self::client_hash()), 0, 20);
    }

    /**
     * Server-side rate limit: RATE_LIMIT_MAX attempts per RATE_LIMIT_WINDOW
     * seconds, per client-hash + form-type. Implemented as a WP transient
     * counter (no custom DB table). This is a sliding window - each
     * accepted attempt within the window refreshes the transient's expiry,
     * so a client must go quiet for the full window to reset, rather than a
     * strict fixed calendar window; see docs/phase-6-report.md. Returns
     * false (caller must reject) once the count reaches the max.
     */
    private static function check_rate_limit($type) {
        $key = self::rate_limit_key($type);
        $count = get_transient($key);
        $count = ($count === false) ? 0 : (int) $count;
        if ($count >= self::RATE_LIMIT_MAX) {
            return false;
        }
        set_transient($key, $count + 1, self::RATE_LIMIT_WINDOW);
        return true;
    }

    /**
     * Raw call to Google's siteverify endpoint. Assumes the caller has
     * already confirmed reCAPTCHA is enabled and the token is non-empty.
     * Fails closed (returns false) on every abnormal condition: HTTP
     * transport error, non-200 response, malformed/non-JSON body, or a
     * JSON body that isn't success===true. Never returns true on anything
     * ambiguous.
     */
    private static function verify_recaptcha_token($token) {
        $secret = trim((string) get_option(self::RECAPTCHA_SECRET_KEY_OPTION, ''));
        if ($secret === '') { return false; }

        $args = ['secret' => $secret, 'response' => $token];
        $ip = self::client_ip();
        if ($ip !== '') { $args['remoteip'] = $ip; }

        $response = wp_remote_post(self::RECAPTCHA_VERIFY_URL, [
            'timeout' => 8,
            'body' => $args,
        ]);
        if (is_wp_error($response)) { return false; }
        if ((int) wp_remote_retrieve_response_code($response) !== 200) { return false; }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) { return false; }

        return isset($data['success']) && $data['success'] === true;
    }

    /**
     * Centralized CAPTCHA gate. No-op (returns normally) when reCAPTCHA is
     * not configured - see recaptcha_admin_notice() for the admin-facing
     * warning in that state; this function never pretends a check happened
     * when it didn't. Once configured, this fails closed: a missing token,
     * a failed Google verification, an HTTP error, or a malformed response
     * all end the request the same way (wp_send_json_error), never falling
     * through to the caller.
     */
    private static function enforce_recaptcha($token) {
        if (!self::recaptcha_enabled()) { return; }
        $token = trim((string) $token);
        if ($token === '' || !self::verify_recaptcha_token($token)) {
            $message = ($token === '')
                ? 'Vui lòng xác nhận bạn không phải là robot.'
                : 'Không thể xác minh reCAPTCHA. Vui lòng thử lại.';
            wp_send_json_error(['message' => $message], 400);
        }
    }

    /**
     * Single call site every handler uses, in pipeline order (rate limit,
     * then CAPTCHA) - see docs/form-security.md. $type must already be a
     * normalized/whitelisted value (one of FORM_TYPES or 'newsletter'),
     * never raw user input, so it can't be used to construct an arbitrary
     * transient key.
     */
    private static function enforce_security($type, $token) {
        if (!self::check_rate_limit($type)) {
            wp_send_json_error(['message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ít phút.'], 429);
        }
        self::enforce_recaptcha($token);
    }

    public static function newsletter() {
        self::verify_public_nonce();
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        if (!$email || !is_email($email)) {
            wp_send_json_error(['message' => 'Email không hợp lệ.'], 400);
        }

        self::enforce_security('newsletter', wp_unslash($_POST['g_recaptcha_response'] ?? ''));

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

    /**
     * Strips CR/LF/NUL so no user-supplied value can inject extra mail
     * headers or split the subject line.
     */
    private static function safe_header_value($value) {
        return trim(str_replace(["\r", "\n", "\0"], '', (string) $value));
    }

    private static function build_reply_to($name, $email) {
        return 'Reply-To: ' . self::safe_header_value($name) . ' <' . $email . '>';
    }

    /**
     * Short, human-readable ID shared between the admin notification and
     * the customer acknowledgement for the SAME submission, so the two can
     * be matched up later (e.g. a reply referencing it). Not a secret, not
     * used for lookup/storage anywhere - purely a display/correlation aid.
     */
    private static function generate_reference_id() {
        return 'MT-' . strtoupper(base_convert((string) time(), 10, 36)) . '-' . strtoupper(wp_generate_password(4, false, false));
    }

    /**
     * Minimal, table-based HTML email shell (email-client-safe: no
     * flexbox/grid, inline styles only) shared by both the admin
     * notification and the customer acknowledgement. $rows is an
     * associative label => value array, values are escaped and
     * newline-preserved. The logo is referenced as "cid:mentra-logo" - a
     * Content-ID that only resolves because mail_with_logo() embeds the
     * matching image as part of the same MIME message (see that method's
     * docblock for why a remote <img src> URL doesn't work here). Degrades
     * gracefully if the image itself is ever blocked by the recipient's
     * mail client - the heading text (which always contains "MENTRA")
     * still identifies the sender even with images off.
     */
    private static function render_html_email($heading, $intro_html, array $rows, $reference_id) {
        $rows_html = '';
        foreach ($rows as $label => $value) {
            $rows_html .= '<tr>'
                . '<td style="padding:6px 12px 6px 0;color:#6b7280;font-size:13px;width:170px;vertical-align:top;white-space:nowrap">' . esc_html($label) . '</td>'
                . '<td style="padding:6px 0;color:#111827;font-size:14px;line-height:1.6">' . nl2br(esc_html($value)) . '</td>'
                . '</tr>';
        }
        return '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:24px;background:#f4f4f2;font-family:Arial,Helvetica,sans-serif">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;border:1px solid #e5e7eb">'
            . '<tr><td style="padding:28px 32px 0 32px">'
            . '<img src="cid:mentra-logo" alt="Mentra" width="120" style="display:block;height:auto;max-width:120px;margin-bottom:14px;border:0">'
            . '<h1 style="margin:0 0 10px 0;font-size:20px;line-height:1.3;color:#111827;font-family:Arial,Helvetica,sans-serif">' . esc_html($heading) . '</h1>'
            . '<div style="color:#374151;font-size:14px;line-height:1.6">' . $intro_html . '</div>'
            . '</td></tr>'
            . '<tr><td style="padding:18px 32px 0 32px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rows_html . '</table></td></tr>'
            . '<tr><td style="padding:22px 32px 28px 32px;color:#9ca3af;font-size:12px;border-top:1px solid #f3f4f6;margin-top:18px">'
            . 'Mentra Việt Nam &middot; Mã tham chiếu: ' . esc_html($reference_id)
            . '</td></tr>'
            . '</table></td></tr></table>'
            . '</body></html>';
    }

    /**
     * Final forms hotfix: sends a Mentra HTML email with the local logo
     * embedded as a CID attachment rather than a remote <img src> URL -
     * render_html_email() always references "cid:mentra-logo", and this is
     * the only place that can make that CID actually resolve.
     * get_template_directory_uri() (the previous approach) resolves to an
     * unreachable http://mentra-vn.local/... on this local environment, and
     * even in production would depend on the recipient's mail client
     * fetching a remote URL at all (many clients block that by default) -
     * CID embedding has neither problem. The phpmailer_init hook is added
     * immediately before wp_mail() and removed immediately after, so it can
     * never attach the logo to an unrelated WordPress email (Newsletter
     * does not call wp_mail() at all, but any other plugin/core mail sent
     * outside this exact call is never touched). wp_mail() remains the only
     * transport - this hook only embeds an image, never configures
     * SMTP/mailer settings (WP Mail SMTP still owns that, per Phase 7). A
     * failure while embedding the image is swallowed so the textual email
     * still sends (section 24 of the hotfix brief).
     */
    private static function mail_with_logo($to, $subject, $html, array $headers) {
        $logo_path = get_template_directory() . '/assets/mentra_logo_email.png';
        $embed = static function ($phpmailer) use ($logo_path) {
            if (!is_file($logo_path)) { return; }
            try {
                $phpmailer->addEmbeddedImage($logo_path, 'mentra-logo', 'mentra_logo_email.png');
            } catch (\Throwable $e) {
                // Logo embedding must never block the textual email from sending.
            }
        };
        add_action('phpmailer_init', $embed);
        $sent = wp_mail($to, $subject, $html, $headers);
        remove_action('phpmailer_init', $embed);
        return $sent;
    }

    /**
     * Best-effort customer acknowledgement (section 16 of the hotfix
     * brief). Never blocks or affects the AJAX response - the frontend
     * already reported success once the ADMIN mail (below) succeeded; this
     * runs after that and its own wp_mail() result is intentionally
     * ignored. Uses the SAME $reference_id as the admin notification.
     * $context carries Purchase-mode extras (intent/product_name/phone/
     * address) - empty for the five non-Purchase form types and Career.
     */
    private static function send_customer_acknowledgement($type, $name, $email, $reference_id, array $context = []) {
        if (($context['intent'] ?? '') === 'purchase') {
            $product_name = $context['product_name'] ?? '';
            $heading = 'Yêu cầu mua hàng đã được ghi nhận';
            $intro = '<p style="margin:0 0 6px 0">Xin chào ' . esc_html($name) . ',</p>'
                . '<p style="margin:0 0 6px 0">Cảm ơn bạn đã quan tâm đến ' . esc_html($product_name) . '.</p>'
                . '<p style="margin:0">Mentra đã nhận được yêu cầu mua hàng của bạn. Đội ngũ của chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.</p>';
            $rows = [
                'Sản phẩm' => $product_name,
                'Số điện thoại' => $context['phone'] ?? '',
                'Địa chỉ' => $context['address'] ?? '',
            ];
            $html = self::render_html_email($heading, $intro, $rows, $reference_id);
            self::mail_with_logo($email, '[MENTRA - KINH DOANH] Chúng tôi đã nhận yêu cầu mua hàng của bạn', $html, ['Content-Type: text/html; charset=UTF-8']);
            return;
        }
        $heading = 'Cảm ơn bạn đã liên hệ với Mentra';
        $intro = '<p style="margin:0 0 6px 0">Xin chào ' . esc_html($name) . ',</p>'
            . '<p style="margin:0">Chúng tôi đã nhận được yêu cầu của bạn và sẽ phản hồi trong thời gian sớm nhất.</p>';
        $rows = ['Loại yêu cầu' => self::TYPE_LABELS_VI[$type] ?? self::FORM_TYPES[$type]];
        $html = self::render_html_email($heading, $intro, $rows, $reference_id);
        self::mail_with_logo($email, '[MENTRA] Đã nhận được yêu cầu của bạn', $html, ['Content-Type: text/html; charset=UTF-8']);
    }

    /**
     * Shared tail of the form pipeline for every whitelisted form type:
     * compose a server-generated subject (never a client-supplied prefix),
     * send an HTML admin notification via wp_mail() (the only transport -
     * no SMTP/PHPMailer config here beyond the CID logo hook, see
     * mail_with_logo()), then best-effort a customer HTML acknowledgement
     * sharing the same reference ID, and return a standardized JSON
     * response. The frontend success state only ever happens once the
     * ADMIN mail succeeds - the customer acknowledgement's own outcome
     * never affects it (section 16). Rate limiting and reCAPTCHA
     * verification (Phase 6) already ran in the caller via
     * enforce_security() before this is reached - see contact_ajax(),
     * career_ajax(). $context is Purchase-mode metadata, forwarded
     * unchanged to send_customer_acknowledgement(); empty for every other
     * form type.
     */
    private static function send_form_mail($type, $subject_line, array $fields, $reply_name, $reply_email, array $context = []) {
        if (!isset(self::FORM_TYPES[$type])) {
            wp_send_json_error(['message' => 'Loại biểu mẫu không hợp lệ.'], 400);
        }
        $reference_id = self::generate_reference_id();
        $to = self::contact_email();
        $subject = self::safe_header_value(self::FORM_TYPES[$type] . ' - ' . $subject_line);
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            self::build_reply_to($reply_name, $reply_email),
        ];
        $intro = '<p style="margin:0">Có một yêu cầu mới từ website Mentra Việt Nam.</p>';
        $html = self::render_html_email(self::FORM_TYPES[$type] . ' - Yêu cầu mới', $intro, $fields, $reference_id);

        $sent = self::mail_with_logo($to, $subject, $html, $headers);
        if (!$sent) {
            wp_send_json_error(['message' => 'Không thể gửi email. Vui lòng thử lại.'], 500);
        }

        self::send_customer_acknowledgement($type, $reply_name, $reply_email, $reference_id, $context);

        wp_send_json_success(['message' => 'Đã gửi yêu cầu.']);
    }

    /**
     * Centralized handler for the five business-contact form types
     * (General/Sales/Support/Partnership/Media), plus the Sales-only
     * Purchase variant, sharing the single #contact-subject markup
     * (templates/source/contact.html, contact@topic=sales.html,
     * contact@topic=support.html, partnerships.html,
     * media-inquiries.html). Final forms hotfix: the form type (and, for
     * Sales, the Purchase intent/product) is authoritative ONLY once its
     * nonce verifies - see contact_nonce_action()/verify_scoped_nonce().
     * The client submits form_type/intent/product itself, but those values
     * are worthless on their own: they are normalized/whitelisted here,
     * then used to recompute the exact nonce action the page must have
     * been rendered with, and the submitted nonce must match that. A
     * client that edits form_type (or intent/product) in devtools while
     * replaying the original page's nonce always fails verification. The
     * Referer header is not read anywhere in this method.
     */
    public static function contact_ajax() {
        $type = self::normalize_type(wp_unslash($_POST['form_type'] ?? ''));
        if ($type === null || $type === 'career') {
            wp_send_json_error(['message' => 'Loại yêu cầu không hợp lệ.'], 400);
        }

        // Purchase mode only ever applies to Sales, and only with a
        // server-approved product key. Any other combination (missing
        // product, unrecognized product, intent requested on a non-Sales
        // type) silently degrades to a normal submission of $type - it is
        // never trusted enough to unlock Purchase-only fields/wording, and
        // the raw value is never echoed anywhere.
        $intent = '';
        $product = '';
        if ($type === 'sales') {
            $intent_raw = sanitize_key(wp_unslash($_POST['intent'] ?? ''));
            // 'mentra_product', not 'product' - see mentra_vn_current_purchase_context()
            // in functions.php for why the plain name collides with WooCommerce's
            // own registered 'product' query var on the frontend GET side; kept
            // consistent here on the POST side too.
            $product_raw = sanitize_key(wp_unslash($_POST['mentra_product'] ?? ''));
            if ($intent_raw === 'purchase' && self::is_valid_product($product_raw)) {
                $intent = 'purchase';
                $product = $product_raw;
            }
        }

        self::verify_scoped_nonce(self::contact_nonce_action($type, $intent, $product));

        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

        if ($name === '' || mb_strlen($name) > 150) {
            wp_send_json_error(['message' => 'Vui lòng nhập họ tên hợp lệ.'], 400);
        }
        if (!$email || !is_email($email) || strlen($email) > 254) {
            wp_send_json_error(['message' => 'Vui lòng nhập email hợp lệ.'], 400);
        }
        if ($message === '' || mb_strlen($message) > 5000) {
            wp_send_json_error(['message' => 'Vui lòng nhập nội dung (tối đa 5000 ký tự).'], 400);
        }

        $company = '';
        $phone = '';
        $address = '';
        if ($intent === 'purchase') {
            $phone = self::sanitize_phone(wp_unslash($_POST['phone'] ?? ''));
            if ($phone === null) {
                wp_send_json_error(['message' => 'Vui lòng nhập số điện thoại hợp lệ.'], 400);
            }
            $address = sanitize_textarea_field(wp_unslash($_POST['address'] ?? ''));
            if ($address === '' || mb_strlen($address) > 500) {
                wp_send_json_error(['message' => 'Vui lòng nhập địa chỉ hợp lệ.'], 400);
            }
        } else {
            $company = sanitize_text_field(wp_unslash($_POST['company'] ?? ''));
            if (mb_strlen($company) > 150) {
                wp_send_json_error(['message' => 'Tên công ty quá dài.'], 400);
            }
        }

        // Purchase submissions share Sales' rate-limit bucket ($type is
        // always 'sales' here, never a per-product key) - a client cannot
        // get a fresh quota just by varying the product/intent query
        // values (section 26 of the hotfix brief).
        self::enforce_security($type, wp_unslash($_POST['g_recaptcha_response'] ?? ''));

        if ($intent === 'purchase') {
            $product_name = self::product_name($product);
            $fields = [
                'Loại' => 'Yêu cầu mua hàng',
                'Sản phẩm' => $product_name,
                'Họ tên' => $name,
                'Email' => $email,
                'Số điện thoại' => $phone,
                'Địa chỉ' => $address,
                'Nội dung' => $message,
            ];
            $subject_line = 'Yêu cầu mua ' . $product_name . ' - ' . $name;
            self::send_form_mail($type, $subject_line, $fields, $name, $email, [
                'intent' => 'purchase',
                'product_name' => $product_name,
                'phone' => $phone,
                'address' => $address,
            ]);
            return;
        }

        $fields = [
            'Loại' => self::FORM_TYPES[$type],
            'Họ tên' => $name,
            'Email' => $email,
            'Công ty' => ($company !== '' ? $company : '(không cung cấp)'),
            'Nội dung' => $message,
        ];

        self::send_form_mail($type, $name, $fields, $name, $email);
    }

    /**
     * Career form (templates/source/careers.html, #career-name/-email/
     * -expertise/-position/-portfolio/-why) - fields match the existing
     * markup exactly, no fields invented. This page previously had no
     * backend at all. Final forms hotfix: also requires form_type=career
     * bound to a scoped nonce (mentra_vn_contact_career), same mechanism as
     * contact_ajax() - Career's type was never actually ambiguous (there is
     * only ever one form on /tuyen-dung/), but this keeps every
     * business-contact endpoint under the same nonce-scoping guarantee.
     */
    public static function career_ajax() {
        $type = self::normalize_type(wp_unslash($_POST['form_type'] ?? ''));
        if ($type !== 'career') {
            wp_send_json_error(['message' => 'Loại yêu cầu không hợp lệ.'], 400);
        }
        self::verify_scoped_nonce(self::contact_nonce_action('career'));

        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $expertise = sanitize_text_field(wp_unslash($_POST['expertise'] ?? ''));
        $position = sanitize_text_field(wp_unslash($_POST['position'] ?? ''));
        $portfolio = esc_url_raw(wp_unslash($_POST['portfolio'] ?? ''));
        $why = sanitize_textarea_field(wp_unslash($_POST['why'] ?? ''));

        if ($name === '' || mb_strlen($name) > 150) {
            wp_send_json_error(['message' => 'Vui lòng nhập họ tên hợp lệ.'], 400);
        }
        if (!$email || !is_email($email) || strlen($email) > 254) {
            wp_send_json_error(['message' => 'Vui lòng nhập email hợp lệ.'], 400);
        }
        if (!in_array($expertise, self::CAREER_EXPERTISE, true)) {
            wp_send_json_error(['message' => 'Vui lòng chọn lĩnh vực chuyên môn hợp lệ.'], 400);
        }
        if (mb_strlen($position) > 150) {
            wp_send_json_error(['message' => 'Vị trí quan tâm quá dài.'], 400);
        }
        if ($portfolio !== '' && (strlen($portfolio) > 500 || !preg_match('#^https?://#i', $portfolio))) {
            wp_send_json_error(['message' => 'Đường dẫn portfolio không hợp lệ.'], 400);
        }
        if ($why === '' || mb_strlen($why) > 5000) {
            wp_send_json_error(['message' => 'Vui lòng chia sẻ lý do (tối đa 5000 ký tự).'], 400);
        }

        self::enforce_security('career', wp_unslash($_POST['g_recaptcha_response'] ?? ''));

        $fields = [
            'Họ tên' => $name,
            'Email' => $email,
            'Lĩnh vực chuyên môn' => $expertise,
            'Vị trí quan tâm' => ($position !== '' ? $position : '(không cung cấp)'),
            'Portfolio / LinkedIn' => ($portfolio !== '' ? $portfolio : '(không cung cấp)'),
            'Lý do ứng tuyển' => $why,
        ];

        self::send_form_mail('career', $name, $fields, $name, $email);
    }
}
Mentra_Vietnam_Core_99::init();
register_activation_hook(__FILE__, ['Mentra_Vietnam_Core_99', 'activate']);

/**
 * Thin bridge functions so the theme layer (functions.php - script enqueue,
 * page-has-protected-form detection) can read reCAPTCHA config without
 * reaching into the plugin's class internals directly.
 */
function mentra_vn_recaptcha_enabled() {
    return Mentra_Vietnam_Core_99::recaptcha_enabled();
}
function mentra_vn_recaptcha_site_key() {
    return Mentra_Vietnam_Core_99::recaptcha_site_key();
}

/**
 * Final forms hotfix: bridge functions so the theme layer can create the
 * scoped nonce for the form it's about to render, and resolve a Purchase
 * product key to its server-approved display name, without reaching into
 * the plugin's class internals directly.
 */
function mentra_vn_is_valid_product($key) {
    return Mentra_Vietnam_Core_99::is_valid_product($key);
}
function mentra_vn_product_name($key) {
    return Mentra_Vietnam_Core_99::product_name($key);
}
function mentra_vn_create_contact_nonce($type, $intent = '', $product = '') {
    return Mentra_Vietnam_Core_99::create_contact_nonce($type, $intent, $product);
}
