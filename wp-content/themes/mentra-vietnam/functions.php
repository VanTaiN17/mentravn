<?php
if (!defined('ABSPATH')) { exit; }

// Bumped from 3.9.0 (Phase 2) - Phases 3/4/4.5/4.6 all modified mentra.css/
// mentra.js since then without ever bumping this cache-busting version
// string, so any browser that cached the old CSS/JS before those changes
// would keep serving it indefinitely (the query string never changed).
define('MENTRA_VN_THEME_VERSION', '3.29.0');
define('MENTRA_VN_THEME_DIR', get_template_directory());
define('MENTRA_VN_THEME_URI', get_template_directory_uri());

function mentra_vn_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'mentra_vn_setup');

function mentra_vn_assets() {
    // The page source uses Red Hat Display. CSS and JavaScript are local; only the OFL webfont is requested from Google Fonts.
    wp_enqueue_style('mentra-red-hat-display', 'https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;600;700;900&display=swap', [], null);
    wp_enqueue_style('mentra-utilities', MENTRA_VN_THEME_URI . '/assets/css/utilities.css', [], MENTRA_VN_THEME_VERSION);
    wp_enqueue_style('mentra-source', MENTRA_VN_THEME_URI . '/assets/css/mentra.css', ['mentra-utilities'], MENTRA_VN_THEME_VERSION);
    wp_enqueue_script('mentra-runtime', MENTRA_VN_THEME_URI . '/assets/js/mentra.js', [], MENTRA_VN_THEME_VERSION, true);
    $cart = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/gio-hang/');

    // Phase 6: the Google v2 Checkbox script only ever loads on a route that
    // genuinely renders a protected form (see mentra_vn_page_has_protected_form()),
    // and only once reCAPTCHA is actually configured - see docs/phase-6-report.md.
    $recaptcha = ['configured' => false, 'siteKey' => ''];
    if (function_exists('mentra_vn_recaptcha_enabled') && mentra_vn_recaptcha_enabled() && mentra_vn_page_has_protected_form()) {
        wp_enqueue_script(
            'mentra-google-recaptcha',
            'https://www.google.com/recaptcha/api.js?onload=mentraVnRecaptchaOnLoad&render=explicit&hl=vi',
            ['mentra-runtime'],
            null,
            true
        );
        wp_scripts()->add_data('mentra-google-recaptcha', 'async', true);
        wp_scripts()->add_data('mentra-google-recaptcha', 'defer', true);
        $recaptcha = ['configured' => true, 'siteKey' => mentra_vn_recaptcha_site_key()];
    }

    $contact_type = mentra_vn_current_contact_type();

    // Final forms hotfix: form_type/nonce replace Referer as the AJAX
    // security boundary. mentra_vn_current_form_type() covers Career (which
    // mentra_vn_current_contact_type() deliberately does not - see that
    // function's docblock), and mentra_vn_current_purchase_context()
    // resolves the Sales-only Purchase intent/product, validated against
    // the server allowlist at render time - an invalid/missing product
    // silently falls back to a normal Sales nonce/context, never a
    // Purchase one. The nonce created here is scoped to exactly this
    // combination (see contact_nonce_action() in the plugin) - a client
    // that edits form_type/intent/product before submitting can never make
    // it match a nonce issued for a different context.
    $form_type = mentra_vn_current_form_type();
    $purchase = mentra_vn_current_purchase_context($form_type);
    $form_nonce = $form_type ? mentra_vn_create_contact_nonce($form_type, $purchase['intent'], $purchase['product']) : '';
    $is_purchase = ($purchase['intent'] === 'purchase' && $purchase['product'] !== '');
    $product_name = $is_purchase ? mentra_vn_product_name($purchase['product']) : '';

    wp_localize_script('mentra-runtime', 'MENTRA_VN', [
        'home' => trailingslashit(home_url('/')),
        'theme' => MENTRA_VN_THEME_URI,
        'ajax' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mentra_vn_public'),
        'cart' => $cart,
        'recaptcha' => $recaptcha,
        'logo' => MENTRA_VN_THEME_URI . '/assets/mentra_logo.svg',
        'contactType' => $contact_type,
        'contactTypeLabel' => $contact_type ? mentra_vn_contact_type_label($contact_type) : '',
        'contactTypeHeading' => $is_purchase ? 'Liên hệ mua hàng' : ($contact_type ? mentra_vn_contact_type_heading($contact_type) : ''),
        'formType' => $form_type,
        'formNonce' => $form_nonce,
        'formIntent' => $purchase['intent'],
        'formProduct' => $purchase['product'],
        'formProductName' => $product_name,
    ]);
}
add_action('wp_enqueue_scripts', 'mentra_vn_assets');

/**
 * Forms UX hotfix: which of the five contact-style form types (general/
 * sales/support/partnership/media) the CURRENT request represents, purely
 * from route/query - mirrors the routing mentra_vn_get_source_key() already
 * uses to pick the static file, kept separate here since it answers a
 * different question (form type, not source file). Returns null on every
 * other page - Career always uses a fixed heading (hardcoded in mentra.js,
 * no ambiguity), and Newsletter has no locked-type UI at all.
 *
 * This is a FRONTEND/UX signal only (which badge/heading to show) - it is
 * NOT the security boundary. The server independently re-derives the
 * authoritative type from the request's Referer in
 * Mentra_Vietnam_Core_99::route_locked_type(), which a simple client-side
 * HTML edit cannot spoof (see docs/forms-hotfix-report.md).
 */
function mentra_vn_current_contact_type() {
    if (!is_page()) { return null; }
    $slug = get_post_field('post_name', get_queried_object_id());
    if ($slug === 'lien-he' || $slug === 'contact') {
        $topic = isset($_GET['topic']) ? sanitize_key(wp_unslash($_GET['topic'])) : '';
        if ($topic === 'sales') { return 'sales'; }
        if ($topic === 'support') { return 'support'; }
        return 'general';
    }
    if ($slug === 'doi-tac' || $slug === 'partnerships') { return 'partnership'; }
    if ($slug === 'truyen-thong' || $slug === 'media-inquiries') { return 'media'; }
    return null;
}

function mentra_vn_contact_type_label($type) {
    $labels = [
        'general' => 'Chung',
        'sales' => 'Kinh doanh',
        'support' => 'Hỗ trợ',
        'partnership' => 'Đối tác',
        'media' => 'Truyền thông',
    ];
    return $labels[$type] ?? '';
}

function mentra_vn_contact_type_heading($type) {
    $headings = [
        'general' => 'Liên hệ Mentra',
        'sales' => 'Liên hệ kinh doanh',
        'support' => 'Hỗ trợ Mentra',
        'partnership' => 'Hợp tác cùng Mentra',
        'media' => 'Liên hệ truyền thông',
    ];
    return $headings[$type] ?? 'Liên hệ Mentra';
}

/**
 * Final forms hotfix: the full six-type form-type resolution used ONLY to
 * scope the security nonce (see mentra_vn_assets()) - wraps
 * mentra_vn_current_contact_type() (general/sales/support/partnership/
 * media) and adds Career, which that function intentionally omits (Career
 * always uses a fixed heading with no locked-context badge, so it never
 * needed the 5-type resolver). Returns null on every page that renders
 * neither a Contact-style form nor the Career form - Newsletter keeps using
 * its own separate 'mentra_vn_public' nonce, unaffected by this.
 */
function mentra_vn_current_form_type() {
    if (is_page(['tuyen-dung', 'careers'])) { return 'career'; }
    return mentra_vn_current_contact_type();
}

/**
 * Final forms hotfix: resolves the Sales-only Purchase intent/product for
 * the CURRENT request from ?intent=&mentra_product=. Only ever non-empty
 * when $type is 'sales'. The query param is deliberately "mentra_product",
 * NOT the shorter "product" the brief's example URL used - WooCommerce
 * registers 'product' as its own public query var (for the `product` CPT's
 * /product/%postname%/ permalink), so a plain ?product=mentra-live on
 * /lien-he/ collides with it and WordPress's redirect_canonical() 301s the
 * request away to /product/mentra-live-camera-glasses/ before this code
 * ever runs (confirmed live - see docs/forms-hotfix-report.md). The
 * product query value is validated against the server allowlist
 * (mentra_vn_is_valid_product()) here, at render time - an invalid,
 * unrecognized, or missing product (or intent requested on a non-Sales
 * type) returns an empty context, which silently renders the normal Sales
 * form instead of Purchase mode. The raw query value is never echoed
 * anywhere, on this path or the AJAX path (see contact_ajax() in the
 * plugin, which independently re-validates the same way from POST).
 */
function mentra_vn_current_purchase_context($type) {
    if ($type !== 'sales') { return ['intent' => '', 'product' => '']; }
    $intent = isset($_GET['intent']) ? sanitize_key(wp_unslash($_GET['intent'])) : '';
    if ($intent !== 'purchase') { return ['intent' => '', 'product' => '']; }
    $product = isset($_GET['mentra_product']) ? sanitize_key(wp_unslash($_GET['mentra_product'])) : '';
    if (!function_exists('mentra_vn_is_valid_product') || !mentra_vn_is_valid_product($product)) {
        return ['intent' => '', 'product' => ''];
    }
    return ['intent' => 'purchase', 'product' => $product];
}

/**
 * Phase 6: true only when the current front-end request actually renders a
 * protected form (Newsletter/Contact/Career) - gates loading the Google
 * reCAPTCHA script. This is a real per-page content check, not a guess from
 * the route name: the Newsletter footer form happens to appear on most
 * (not all) static-source pages plus a few hand-authored PHP templates, and
 * is genuinely absent from a handful of routes (e.g. /even-realities/,
 * /nha-phat-trien/, /trong-kinh/, /tuyen-dung/'s own footer) - see
 * docs/phase-6-report.md for the full audit.
 */
function mentra_vn_page_has_protected_form() {
    if (is_admin()) { return false; }

    // Hand-authored PHP templates known to embed a protected form inline.
    if (is_page(['mentra-live', 'mentra-os', 'mentra-live-charging-cable'])) {
        return true;
    }
    // /tin-tuc/ and every single article render the shared newsletter-cta partial.
    if (is_page('tin-tuc') || is_singular('post')) {
        return true;
    }

    $key = mentra_vn_get_source_key();
    if (!$key || !mentra_vn_source_exists($key)) { return false; }
    $html = file_get_contents(MENTRA_VN_THEME_DIR . '/templates/source/' . $key . '.html');
    if ($html === false) { return false; }
    return (strpos($html, 'data-mentra-newsletter') !== false)
        || (strpos($html, 'data-mentra-contact') !== false)
        || (strpos($html, 'data-career-form') !== false);
}

function mentra_vn_source_map() {
    return [
        'tai-ung-dung' => 'get',
        'get' => 'get',
        'mentra-os' => 'OS',
        'os' => 'OS',
        'mentra-live' => 'live',
        'live' => 'live',
        'nha-phat-trien' => 'devs',
        'devs' => 'devs',
        've-mentra' => 'about',
        'about' => 'about',
        'lien-he' => 'contact',
        'contact' => 'contact',
        'ho-tro' => 'support',
        'support' => 'support',
        'ung-dung' => 'apps',
        'apps' => 'apps',
        'so-sanh' => 'compare',
        'compare' => 'compare',
        'trong-kinh' => 'prescriptions',
        'prescriptions' => 'prescriptions',
        'phu-de' => 'captions',
        'captions' => 'captions',
        'mentra-notes' => 'notes',
        'notes' => 'notes',
        'tuyen-dung' => 'careers',
        'careers' => 'careers',
        'mang-xa-hoi' => 'socials',
        'socials' => 'socials',
        'doi-tac' => 'partnerships',
        'partnerships' => 'partnerships',
        'truyen-thong' => 'media-inquiries',
        'media-inquiries' => 'media-inquiries',
        'chinh-sach-quyen-rieng-tu' => 'privacy-policy',
        'privacy-policy' => 'privacy-policy',
        // Phase 8: 'quyen-rieng-tu' and bare 'privacy' deliberately removed -
        // both used to resolve (via mentra_vn_get_source_key()'s raw-path
        // fallback) to the 'privacy' source file, which is byte-identical to
        // the homepage in the original capture (a known capture anomaly, not
        // real privacy-policy content - see docs/site-audit.md). Neither path
        // has ever been a real published Page. Removing them here makes both
        // paths genuinely 404 instead of silently serving homepage content at
        // HTTP 200 under a "Page not found" title. The real, correct privacy
        // policy stays at chinh-sach-quyen-rieng-tu/privacy-policy above.
        'dieu-khoan-dich-vu' => 'terms-of-service',
        'terms-of-service' => 'terms-of-service',
        'chinh-sach-van-chuyen' => 'shipping-policy',
        'shipping-policy' => 'shipping-policy',
        'chinh-sach-doi-tra' => 'refund-policy',
        'refund-policy' => 'refund-policy',
        'kha-nang-tiep-can' => 'accessibility',
        'accessibility' => 'accessibility',
        'thu-hoi' => 'recalls',
        'recalls' => 'recalls',
        'tin-tuc' => 'blogs',
        'blog' => 'blogs',
        'blogs' => 'blogs',
        'nimo' => 'nimo',
        'even-realities' => 'even-realities',
        'discord' => 'discord',
        'legacy' => 'Legacy',
    ];
}

function mentra_vn_get_source_key() {
    if (is_front_page() || is_home()) { return 'index'; }
    if (is_page()) {
        $slug = get_post_field('post_name', get_queried_object_id());
        // /lien-he/?topic=sales and /lien-he/?topic=support serve the
        // matching pre-selected static variant (both files already exist
        // on disk from the original WGET capture). Query value is
        // whitelisted against $topic_map, never used to build a path
        // directly, and any other/unrecognized value falls back to the
        // plain contact page.
        if ($slug === 'lien-he' || $slug === 'contact') {
            $topic = isset($_GET['topic']) ? sanitize_key(wp_unslash($_GET['topic'])) : '';
            $topic_map = ['sales' => 'contact@topic=sales', 'support' => 'contact@topic=support'];
            if (isset($topic_map[$topic]) && mentra_vn_source_exists($topic_map[$topic])) {
                return $topic_map[$topic];
            }
            return 'contact';
        }
        $map = mentra_vn_source_map();
        if (isset($map[$slug])) { return $map[$slug]; }
    }
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (strpos($path, 'blogs/blog/') === 0) {
        return $path;
    }
    $map = mentra_vn_source_map();
    if (isset($map[$path])) { return $map[$path]; }
    return null;
}

function mentra_vn_source_exists($key) {
    if (!$key || strpos($key, '..') !== false) { return false; }
    return is_file(MENTRA_VN_THEME_DIR . '/templates/source/' . $key . '.html');
}

function mentra_vn_render_source($key) {
    if (!mentra_vn_source_exists($key)) { return false; }
    $file = MENTRA_VN_THEME_DIR . '/templates/source/' . $key . '.html';
    $html = file_get_contents($file);
    if ($html === false) { return false; }
    $cart = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/gio-hang/');
    $html = str_replace(
        ['{{THEME_URI}}','{{HOME_URL}}','{{CART_URL}}'],
        [esc_url(MENTRA_VN_THEME_URI), esc_url(untrailingslashit(home_url('/'))), esc_url($cart)],
        $html
    );
    // WordPress admin bar changes the fixed header offset via CSS; source markup stays intact.
    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme-owned HTML generated from supplied source.
    return true;
}

function mentra_vn_asset($path) {
    return MENTRA_VN_THEME_URI . '/assets/' . ltrim($path, '/');
}

function mentra_vn_render_partial($name) {
    if (!$name || strpos($name, '..') !== false) { return false; }
    $file = MENTRA_VN_THEME_DIR . '/templates/parts/' . $name . '.html';
    if (!is_file($file)) { return false; }
    $html = file_get_contents($file);
    if ($html === false) { return false; }
    $cart = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/gio-hang/');
    $html = str_replace(
        ['{{THEME_URI}}','{{HOME_URL}}','{{CART_URL}}'],
        [esc_url(MENTRA_VN_THEME_URI), esc_url(untrailingslashit(home_url('/'))), esc_url($cart)],
        $html
    );
    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme-owned HTML partial, not user input.
    return true;
}

function mentra_vn_document_open($source_key = '') {
    ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class('mentra-vn-source mentra-vn-' . sanitize_html_class(str_replace('/', '-', $source_key))); ?>>
<?php wp_body_open(); ?>
<?php
}

function mentra_vn_document_close() {
    wp_footer();
    ?></body></html><?php
}

// Legal pages previously existed at two competing slugs: an unlinked
// placeholder-stub page, and the canonical slug used by nav/footer + the
// static-source map. Redirect the stub slugs to the canonical ones so only
// one page is publicly reachable per policy, per Phase 2 legal routing fix.
function mentra_vn_legal_slug_redirects() {
    if (!is_page()) { return; }
    $map = [
        'chinh-sach-bao-mat' => 'chinh-sach-quyen-rieng-tu',
        'dieu-khoan' => 'dieu-khoan-dich-vu',
        'van-chuyen' => 'chinh-sach-van-chuyen',
        'doi-tra' => 'chinh-sach-doi-tra',
    ];
    $slug = get_post_field('post_name', get_queried_object_id());
    if (isset($map[$slug])) {
        wp_safe_redirect(home_url('/' . $map[$slug] . '/'), 301);
        exit;
    }
}
add_action('template_redirect', 'mentra_vn_legal_slug_redirects');

// /get-mentra was the old Shopify-era purchase/download CTA target and has
// no WordPress route (true 404). All on-page CTAs were updated to link
// directly to /lien-he/?topic=sales (there is no ecommerce checkout), but
// redirect the bare path too in case of stray inbound links/bookmarks.
function mentra_vn_get_mentra_redirect() {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path === 'get-mentra') {
        wp_safe_redirect(home_url('/tai-ung-dung/'), 301);
        exit;
    }
}
add_action('template_redirect', 'mentra_vn_get_mentra_redirect');

/**
 * Phase 8: every route below has a real, canonical Vietnamese-slug WP Page
 * (created in mentra-vietnam-core.php's create_pages()), but its English
 * source-file alias (mentra_vn_source_map()'s bare-English keys, e.g.
 * 'support'/'contact'/'about') is ALSO reachable directly - not through a
 * real Page, but through 404.php's raw-path fallback
 * (mentra_vn_get_source_key()'s final branch), which renders the exact same
 * static-source HTML a second time at HTTP 200. Because that fallback path
 * never runs through a real WP_Query page result, WordPress's own is_404()
 * conditional stays true even after 404.php's manual status_header(200) -
 * so Yoast (which correctly suppresses meta on real 404s) never outputs
 * ANY robots/canonical/OG tags on these pages, and the <title> falls back to
 * a literal "Page not found" - while the body renders full, real duplicate
 * content. Confirmed live during Phase 8 audit (docs/phase-8-report.md).
 * 301-redirecting the English alias to its canonical Vietnamese Page fixes
 * this: no more duplicate-content page with no SEO signal, and any stray
 * inbound link to the old English path still lands somewhere real instead
 * of a dead end. This does not affect brand/product-name slugs that are
 * identical in both languages (nimo, even-realities, discord, legacy) -
 * those only ever have one path form and are already real Pages.
 */
function mentra_vn_source_alias_redirects() {
    static $aliases = [
        'os' => 'mentra-os',
        'live' => 'mentra-live',
        'devs' => 'nha-phat-trien',
        'about' => 've-mentra',
        'contact' => 'lien-he',
        'support' => 'ho-tro',
        'apps' => 'ung-dung',
        'compare' => 'so-sanh',
        'prescriptions' => 'trong-kinh',
        'captions' => 'phu-de',
        'notes' => 'mentra-notes',
        'careers' => 'tuyen-dung',
        'socials' => 'mang-xa-hoi',
        'partnerships' => 'doi-tac',
        'media-inquiries' => 'truyen-thong',
        'privacy-policy' => 'chinh-sach-quyen-rieng-tu',
        'terms-of-service' => 'dieu-khoan-dich-vu',
        'shipping-policy' => 'chinh-sach-van-chuyen',
        'refund-policy' => 'chinh-sach-doi-tra',
        'accessibility' => 'kha-nang-tiep-can',
        'recalls' => 'thu-hoi',
        'blog' => 'tin-tuc',
        'blogs' => 'tin-tuc',
        'get' => 'tai-ung-dung',
    ];
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (isset($aliases[$path])) {
        wp_safe_redirect(home_url('/' . $aliases[$path] . '/'), 301);
        exit;
    }
}
add_action('template_redirect', 'mentra_vn_source_alias_redirects');

// No ecommerce checkout flow is part of this site's scope (WooCommerce is
// installed only as a future catalog-only CMS). Keep its default public
// shop/cart/checkout/account routes out of the public user flow. Using a
// temporary (302) redirect rather than 301, since /shop/ in particular may
// become a real public catalog listing route in a later product phase.
// Phase 8: also covers product-category taxonomy archives - both real
// products are filed under WooCommerce's default "Uncategorized" term (no
// real category taxonomy exists yet), so /product-category/uncategorized/
// was publicly reachable at HTTP 200 serving WooCommerce's stock English
// "Coming Soon" placeholder block, unindexed by nothing (no noindex, no
// redirect) - confirmed live during the Phase 8 audit.
function mentra_vn_disable_woocommerce_public_routes() {
    if (!function_exists('is_shop')) { return; }
    if (is_shop() || is_cart() || is_checkout() || is_account_page() || is_product_taxonomy()) {
        wp_safe_redirect(home_url('/'), 302);
        exit;
    }
}
add_action('template_redirect', 'mentra_vn_disable_woocommerce_public_routes');

// Default WordPress archive routes with no real content for this site
// (the demo "Uncategorized" category, and any author archive — which also
// avoids publicly exposing usernames via /author/<username>/).
function mentra_vn_disable_default_wp_archives() {
    if (is_author() || is_category('uncategorized')) {
        wp_safe_redirect(home_url('/'), 302);
        exit;
    }
}
add_action('template_redirect', 'mentra_vn_disable_default_wp_archives');

// Phase 8: the real WordPress site locale is now 'vi' (see
// Mentra_Vietnam_Core_99::maybe_set_site_locale() - fixed the root cause of
// a long-standing SEO defect where Yoast's og:locale/inLanguage still said
// en_US/en-US despite this html lang already showing vi-VN). WordPress's
// own locale 'vi' renders language_attributes() as lang="vi" (no region);
// this filter refines that to the more specific "vi-VN" BCP-47 tag on the
// public frontend only, without touching wp-admin.
function mentra_vn_frontend_lang_attributes($output) {
    if (is_admin()) { return $output; }
    return preg_replace('/lang="[^"]*"/', 'lang="vi-VN"', $output, 1);
}
add_filter('language_attributes', 'mentra_vn_frontend_lang_attributes');

// ==========================================================================
// Phase 3: WooCommerce catalog-only data layer.
// WooCommerce is used ONLY as a product-data CMS (SKU, images, attributes,
// stock) here — never as an ecommerce transaction system. Every hook below
// either (a) maps WC stock data to a Vietnamese label for the theme to
// display, (b) keeps a product's own WC-generated URL from being a second,
// duplicate-indexable copy of its real theme page, or (c) strips
// price/cart/checkout affordances so WooCommerce can never expose a
// purchase flow on the frontend.
// ==========================================================================

// Reusable WC stock_status -> Vietnamese label mapping. 'onbackorder' is
// mapped in case a future catalog item genuinely needs a pre-launch state,
// per business rule - not currently used by any real product/variation.
function mentra_vn_stock_label($stock_status) {
    switch ($stock_status) {
        case 'instock': return 'Còn hàng';
        case 'outofstock': return 'Hết hàng';
        case 'onbackorder': return 'Sắp ra mắt';
        default: return '';
    }
}

// Fetch a WC product by SKU, returning null (not false) when WooCommerce
// isn't active or the SKU isn't found, so callers can use ?? safely.
function mentra_vn_get_product_by_sku($sku) {
    if (!function_exists('wc_get_product_id_by_sku')) { return null; }
    $id = wc_get_product_id_by_sku($sku);
    if (!$id) { return null; }
    $product = wc_get_product($id);
    return $product ?: null;
}

// A WC product's own frontend URL (/product/<slug>/) must never be a second
// indexable copy of a page that already has a real theme URL (e.g.
// /mentra-live/). Redirect to the canonical URL stored in product meta.
function mentra_vn_product_canonical_redirect() {
    if (!function_exists('is_product') || !is_product()) { return; }
    global $product;
    $wc_product = $product instanceof WC_Product ? $product : wc_get_product(get_queried_object_id());
    if (!$wc_product) { return; }
    $canonical = $wc_product->get_meta('_mentra_vn_canonical_url', true);
    if ($canonical) {
        wp_safe_redirect(home_url($canonical), 301);
        exit;
    }
}
add_action('template_redirect', 'mentra_vn_product_canonical_redirect', 5);

// Catalog-only hardening: no product on this site is ever purchasable,
// site-wide and forever, regardless of how many products/variations exist.
add_filter('woocommerce_is_purchasable', '__return_false');
add_filter('woocommerce_variation_is_purchasable', '__return_false');

// No price is ever shown, anywhere WooCommerce would normally render one
// (shop loops, single product summary, structured data helpers that pull
// from get_price_html()).
add_filter('woocommerce_get_price_html', '__return_empty_string');

// Cart fragments (the AJAX mini-cart refresh script) and the add-to-cart
// handler serve no purpose with woocommerce_is_purchasable filtered to
// false everywhere — drop the extra requests/JS.
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_script('wc-cart-fragments');
    wp_dequeue_script('wc-add-to-cart');
}, 100);

// Remove default block styles on the source-mirror templates so they cannot alter the supplied design.
add_action('wp_enqueue_scripts', function(){
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
}, 100);

// Phase 4.5: WooCommerce's own frontend CSS/JS was loading on every public
// route (verified: homepage, /tin-tuc/, /mentra-live/ all requested
// woocommerce-general/layout/smallscreen + wc-blocks-style, plus the
// sourcebuster/order-attribution marketing-tracking scripts) even though
// this site never renders a WooCommerce shop/cart/checkout template - it's
// pure dead weight and a source of unintended style interference on the
// theme's own custom pages. Scoped to !is_admin() only, so wp-admin's
// Products screens (and any WC data/API calls, which don't enqueue
// frontend assets in the first place) are unaffected.
add_action('wp_enqueue_scripts', function () {
    if (is_admin()) { return; }
    foreach (['woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style', 'coming-soon'] as $handle) {
        wp_dequeue_style($handle);
    }
    foreach (['sourcebuster-js', 'wc-order-attribution'] as $handle) {
        wp_dequeue_script($handle);
    }
}, 100);

// wc-blocks-style specifically is re-enqueued unconditionally by
// Automattic\WooCommerce\Blocks\Domain\Services\Notices::enqueue_notice_styles()
// on wp_head (every request, block theme or not - it's how WC preps its
// notice banner styling even though this site uses classic notice
// templates), which runs after wp_enqueue_scripts, so the dequeue above
// alone doesn't catch it. Remove it again right before styles print.
add_action('wp_head', function () {
    if (is_admin()) { return; }
    wp_dequeue_style('wc-blocks-style');
}, 20);

// ==========================================================================
// Phase 4: WordPress-native news article routing.
// The 16 owned articles are real post_type=post entries (imported by the
// plugin - see Mentra_Vietnam_Core_99::maybe_import_mentra_articles()) with
// _mentra_vn_article=1 postmeta. Canonical URL is /tin-tuc/{slug}/, scoped
// only to those posts via a dedicated rewrite rule + post_type_link filter
// so this never affects any other/future WordPress Post's permalink.
// ==========================================================================

// Rewrite rule registered on every 'init' (cheap, required so WP recognizes
// the route); the flush itself only runs once, gated the same way
// maybe_create_pages()/maybe_import_mentra_articles() gate their one-time
// work, since flush_rewrite_rules() is expensive and must not run every request.
function mentra_vn_register_news_rewrite() {
    add_rewrite_rule('^tin-tuc/([^/]+)/?$', 'index.php?post_type=post&name=$matches[1]', 'top');
}
add_action('init', 'mentra_vn_register_news_rewrite', 10);

function mentra_vn_maybe_flush_news_rewrite() {
    if (get_option('mentra_vn_news_rewrite_v1')) { return; }
    flush_rewrite_rules();
    update_option('mentra_vn_news_rewrite_v1', 1);
}
add_action('init', 'mentra_vn_maybe_flush_news_rewrite', 11);

// Only posts carrying _mentra_vn_article=1 get the /tin-tuc/{slug}/
// permalink; every other post type/post keeps WordPress's own default
// permalink structure untouched.
function mentra_vn_article_permalink($post_link, $post) {
    if (!$post || $post->post_type !== 'post') { return $post_link; }
    if (!get_post_meta($post->ID, '_mentra_vn_article', true)) { return $post_link; }
    return home_url('/tin-tuc/' . $post->post_name . '/');
}
add_filter('post_type_link', 'mentra_vn_article_permalink', 10, 2);
add_filter('post_link', 'mentra_vn_article_permalink', 10, 2);

// Legacy /blogs/blog/{slug}/ URLs (the old Shopify-era article paths, and
// the same paths the static-source mirror used to soft-404 on before
// Phase 4) permanently redirect to the new canonical /tin-tuc/{slug}/
// route. Runs on template_redirect - same hook/priority pattern as the
// other redirects in this file - so it takes effect before page.php/404.php
// would otherwise try to render templates/source/blogs/blog/*.html, which
// after this phase is reference/dormant content only (see Phase 4 report).
function mentra_vn_legacy_article_redirect() {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (strpos($path, 'blogs/blog/') !== 0) { return; }
    $slug = trim(substr($path, strlen('blogs/blog/')), '/');
    if (!$slug || strpos($slug, '/') !== false) { return; }
    $existing = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'meta_key' => '_mentra_vn_legacy_slug',
        'meta_value' => $slug,
        'numberposts' => 1,
        'fields' => 'ids',
    ]);
    if (!$existing) { return; }
    wp_safe_redirect(home_url('/tin-tuc/' . $slug . '/'), 301);
    exit;
}
add_action('template_redirect', 'mentra_vn_legacy_article_redirect', 4);

// ==========================================================================
// Phase 4.6: Infinity Cable product page.
// The owner corrected a Phase 4.5 mistake: Infinity Cable is a real Mentra
// product page (https://mentraglass.com/products/mentra-live-charging-cable,
// gid://shopify/Product/9286483280124), not an anchor/section inside Mentra
// Live. It's a normal WP Page (slug mentra-live-charging-cable, created by
// maybe_create_pages()) rendered by page-mentra-live-charging-cable.php via
// WordPress's own template hierarchy - only its URL needs a rewrite, to
// match the real site's /products/{handle}/ convention instead of
// WordPress's default flat /mentra-live-charging-cable/ page URL.
// ==========================================================================

function mentra_vn_register_product_page_rewrite() {
    add_rewrite_rule('^products/mentra-live-charging-cable/?$', 'index.php?pagename=mentra-live-charging-cable', 'top');
}
add_action('init', 'mentra_vn_register_product_page_rewrite', 10);

function mentra_vn_maybe_flush_product_page_rewrite() {
    if (get_option('mentra_vn_product_page_rewrite_v1')) { return; }
    flush_rewrite_rules();
    update_option('mentra_vn_product_page_rewrite_v1', 1);
}
add_action('init', 'mentra_vn_maybe_flush_product_page_rewrite', 11);

// Scoped to this one page only (by post_type + post_name), not a general
// Page-permalink rewrite - every other Page keeps its default WordPress URL.
// Both 'page_link' and '_get_page_link' pass the post ID (int), not a
// WP_Post object, as their second argument - unlike Phase 4's
// post_type_link/post_link filters, which do receive a WP_Post.
function mentra_vn_product_page_permalink($link, $post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'page' || $post->post_name !== 'mentra-live-charging-cable') { return $link; }
    return home_url('/products/mentra-live-charging-cable/');
}
add_filter('page_link', 'mentra_vn_product_page_permalink', 10, 2);
add_filter('_get_page_link', 'mentra_vn_product_page_permalink', 10, 2);

// The default WordPress /mentra-live-charging-cable/ page URL must not
// remain a second, non-canonical route to the same content once the real
// canonical /products/mentra-live-charging-cable/ URL exists (same
// canonical-URL principle Phase 3 applied to the WooCommerce product URL).
function mentra_vn_infinity_cable_canonical_redirect() {
    if (!is_page('mentra-live-charging-cable')) { return; }
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path === 'products/mentra-live-charging-cable') { return; }
    wp_safe_redirect(home_url('/products/mentra-live-charging-cable/'), 301);
    exit;
}
add_action('template_redirect', 'mentra_vn_infinity_cable_canonical_redirect', 5);
