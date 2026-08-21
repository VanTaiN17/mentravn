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

// Remove default block styles on the source-mirror templates so they cannot alter the supplied design.
add_action('wp_enqueue_scripts', function(){
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
}, 100);
