<?php
if (!defined('ABSPATH')) { exit; }

define('MENTRA_VN_THEME_VERSION', '3.9.0');
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
    wp_localize_script('mentra-runtime', 'MENTRA_VN', [
        'home' => trailingslashit(home_url('/')),
        'theme' => MENTRA_VN_THEME_URI,
        'ajax' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mentra_vn_public'),
        'cart' => $cart,
    ]);
}
add_action('wp_enqueue_scripts', 'mentra_vn_assets');

function mentra_vn_source_map() {
    return [
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
        'quyen-rieng-tu' => 'privacy',
        'privacy' => 'privacy',
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
        wp_safe_redirect(home_url('/lien-he/?topic=sales'), 301);
        exit;
    }
}
add_action('template_redirect', 'mentra_vn_get_mentra_redirect');

// No ecommerce checkout flow is part of this site's scope (WooCommerce is
// installed only as a future catalog-only CMS). Keep its default public
// shop/cart/checkout/account routes out of the public user flow. Using a
// temporary (302) redirect rather than 301, since /shop/ in particular may
// become a real public catalog listing route in a later product phase.
function mentra_vn_disable_woocommerce_public_routes() {
    if (!function_exists('is_shop')) { return; }
    if (is_shop() || is_cart() || is_checkout() || is_account_page()) {
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

// Frontend content is Vietnamese, but the site's WP locale (and thus
// language_attributes()) is still en_US. Force the correct document
// language on the public frontend only, without touching wp-admin.
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

// Cart fragments (the AJAX mini-cart refresh script) serve no purpose with
// no cart flow — drop the extra request/JS.
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_script('wc-cart-fragments');
}, 100);

// Remove default block styles on the source-mirror templates so they cannot alter the supplied design.
add_action('wp_enqueue_scripts', function(){
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
}, 100);
