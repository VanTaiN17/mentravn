<?php
if (!defined('ABSPATH')) { exit; }

// Bumped from 3.9.0 (Phase 2) - Phases 3/4/4.5/4.6 all modified mentra.css/
// mentra.js since then without ever bumping this cache-busting version
// string, so any browser that cached the old CSS/JS before those changes
// would keep serving it indefinitely (the query string never changed).
define('MENTRA_VN_THEME_VERSION', '3.13.0');
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
        wp_safe_redirect(home_url('/tai-ung-dung/'), 301);
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
