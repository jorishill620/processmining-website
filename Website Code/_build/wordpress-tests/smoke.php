<?php
/**
 * Smoke test for the PM Site plugin, with a small stand-in for WordPress.
 * Run by _build/wordpress.py after every build (when PHP is installed):
 *
 *     php _build/wordpress-tests/smoke.php "../WordPress/pm-site"
 *
 * It checks the logic, not the looks: page connecting and Undo (Dutch and English),
 * every template rendering without PHP warnings, link fallbacks, theme CSS removal,
 * SEO texts and the hreflang links.
 */
error_reporting( E_ALL );
set_error_handler( function ( $no, $str, $file, $line ) { throw new ErrorException( "$str in $file:$line" ); } );
define( 'ABSPATH', '/tmp/' );
define( 'OBJECT', 'OBJECT' );
$PLUGIN = rtrim( $argv[1], '/' ) . '/';

/* ---- a tiny WordPress ------------------------------------------------ */
$H = array(); $OPT = array( 'show_on_front' => 'page', 'page_on_front' => 45 ); $META = array(); $POSTS = array();
$Q = array(); $NEXT = 9000; $ENQ = array(); $REDIRECT = null; $GF = false; $IS_ADMIN = true;
function add_filter( $h, $cb, $p = 10, $a = 1 ) { global $H; $H[ $h ][ $p ][] = array( $cb, $a ); return true; }
function add_action( $h, $cb, $p = 10, $a = 1 ) { return add_filter( $h, $cb, $p, $a ); }
function apply_filters( $h, $v, ...$args ) { global $H; if ( empty( $H[ $h ] ) ) return $v; ksort( $H[ $h ] ); foreach ( $H[ $h ] as $cbs ) foreach ( $cbs as $c ) { $v = call_user_func_array( $c[0], array_slice( array_merge( array( $v ), $args ), 0, $c[1] ) ); } return $v; }
function do_action( $h, ...$args ) { global $H; if ( empty( $H[ $h ] ) ) return; ksort( $H[ $h ] ); foreach ( $H[ $h ] as $cbs ) foreach ( $cbs as $c ) call_user_func_array( $c[0], array_slice( $args, 0, $c[1] ) ); }
function remove_action( $h, $cb, $p = 10 ) { return true; }
function plugin_dir_path( $f ) { return dirname( $f ) . '/'; }
function plugin_dir_url( $f ) { return 'https://stage.test/wp-content/plugins/pm-site/'; }
function is_admin() { global $IS_ADMIN; return ! empty( $IS_ADMIN ); }
function get_option( $k, $d = false ) { global $OPT; return array_key_exists( $k, $OPT ) ? $OPT[ $k ] : $d; }
function update_option( $k, $v, $a = null ) { global $OPT; $OPT[ $k ] = $v; return true; }
function delete_option( $k ) { global $OPT; unset( $OPT[ $k ] ); return true; }
function is_404() { global $Q; return ! empty( $Q['404'] ); }
function is_singular( $t = '' ) { global $Q; return ! empty( $Q['id'] ); }
function is_page() { global $Q; return ! empty( $Q['id'] ); }
function get_queried_object_id() { global $Q; return isset( $Q['id'] ) ? $Q['id'] : 0; }
function get_post_meta( $id, $k, $single = false ) { global $META; return isset( $META[ $id ][ $k ] ) ? $META[ $id ][ $k ] : ''; }
function update_post_meta( $id, $k, $v ) { global $META; $META[ $id ][ $k ] = $v; return true; }
function delete_post_meta( $id, $k ) { global $META; unset( $META[ $id ][ $k ] ); return true; }
function metadata_exists( $t, $id, $k ) { global $META; return isset( $META[ $id ] ) && array_key_exists( $k, $META[ $id ] ); }
function get_page_template_slug( $id ) { return (string) get_post_meta( $id, '_wp_page_template' ); }
function get_post( $id ) { global $POSTS; return isset( $POSTS[ $id ] ) ? (object) $POSTS[ $id ] : null; }
function get_post_status( $id ) { global $POSTS; return isset( $POSTS[ $id ] ) ? $POSTS[ $id ]['post_status'] : false; }
function get_post_field( $f, $id ) { global $POSTS; return $POSTS[ $id ][ $f ]; }
function get_page_by_path( $path, $o = null, $t = 'page' ) { global $POSTS; foreach ( $POSTS as $p ) if ( ( isset( $p['path'] ) ? $p['path'] : $p['post_name'] ) === $path ) return (object) $p; return null; }
function get_permalink( $id ) { global $POSTS; $p = $POSTS[ $id ]; return 'https://stage.test/' . ( isset( $p['path'] ) ? $p['path'] : $p['post_name'] ) . '/'; }
function get_the_title( $id ) { global $POSTS; return $POSTS[ $id ]['post_title']; }
function wp_insert_post( $a, $e = false ) { global $POSTS, $NEXT; $id = ++$NEXT; $path = $a['post_name']; if ( ! empty( $a['post_parent'] ) ) $path = $POSTS[ $a['post_parent'] ]['path'] . '/' . $path; $POSTS[ $id ] = array( 'ID' => $id, 'post_name' => $a['post_name'], 'post_title' => $a['post_title'], 'post_status' => $a['post_status'], 'post_parent' => ! empty( $a['post_parent'] ) ? $a['post_parent'] : 0, 'path' => $path ); return $id; }
function wp_update_post( $a ) { global $POSTS; foreach ( $a as $k => $v ) if ( 'ID' !== $k ) $POSTS[ $a['ID'] ][ $k ] = $v; return $a['ID']; }
function is_wp_error( $x ) { return false; }
function home_url( $p = '' ) { return 'https://stage.test' . $p; }
function admin_url( $p = '' ) { return 'https://stage.test/wp-admin/' . $p; }
function esc_url( $u ) { return htmlspecialchars( (string) $u, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function absint( $n ) { return abs( (int) $n ); }
function shortcode_exists( $s ) { global $GF; return ! empty( $GF ); }
function do_shortcode( $s ) { return '<div class="gform_wrapper">' . $s . '</div>'; }
function current_user_can( $c ) { return true; }
function wp_enqueue_style( $h, ...$a ) { global $ENQ; $ENQ[] = "style:$h"; }
function wp_enqueue_script( $h, ...$a ) { global $ENQ; $ENQ[] = "script:$h"; }
function gravity_form_enqueue_scripts( $id, $ajax ) { global $ENQ; $ENQ[] = "gf:$id"; }
class Pm_Test_Deps { public $queue = array(); public $registered = array(); }
$STYLES = new Pm_Test_Deps(); $SCRIPTS = new Pm_Test_Deps();
function wp_styles() { global $STYLES; return $STYLES; }
function wp_scripts() { global $SCRIPTS; return $SCRIPTS; }
function wp_dequeue_style( $h ) { global $STYLES; $STYLES->queue = array_values( array_diff( $STYLES->queue, array( $h ) ) ); }
function wp_dequeue_script( $h ) { global $SCRIPTS; $SCRIPTS->queue = array_values( array_diff( $SCRIPTS->queue, array( $h ) ) ); }
function wp_safe_redirect( $u, $s ) { global $REDIRECT; $REDIRECT = "$s $u"; throw new Exception( 'redirect' ); }
function wp_redirect( $u, $s ) { global $REDIRECT; $REDIRECT = "$s $u"; throw new Exception( 'redirect' ); }
function status_header( $c ) { global $STATUS; $STATUS = $c; }
function add_theme_support( $f ) { global $H; $GLOBALS['THEME_SUPPORT'][] = $f; }
function bloginfo( $k ) { echo 'UTF-8'; }
function wp_head() { do_action( 'wp_head' ); }
function wp_footer() {}
function wp_body_open() {}
function body_class( $c = '' ) { echo 'class="' . esc_attr( $c ) . '"'; }
function wp_make_link_relative( $u ) { return preg_replace( '#^https?://[^/]+#', '', $u ); }
function get_edit_post_link( $id, $c = '' ) { return admin_url( "post.php?post=$id&action=edit" ); }
function add_query_arg( $a, $u ) { return $u . '?' . http_build_query( $a ); }
function check_admin_referer( $a ) { return true; }
function sanitize_key( $k ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $k ) ); }
function wp_unslash( $v ) { return $v; }
function wp_nonce_field( $a ) { echo '<input type="hidden" name="_wpnonce" value="x">'; }
function submit_button( $t, $type = 'primary', $n = 'submit', $wrap = true ) { echo '<button>' . esc_html( $t ) . '</button>'; }
function checked( $a, $b, $e = true ) { return $a === $b ? ' checked' : ''; }
function add_menu_page( ...$a ) {}
function wp_die( $m ) { throw new Exception( $m ); }
function single_post_title( $p = '', $d = true ) { global $POSTS, $Q; return $POSTS[ $Q['id'] ]['post_title']; }
function has_blocks( $c ) { return false !== strpos( (string) $c, '<!-- wp:' ); }
function do_blocks( $c ) { return preg_replace( '#<!--\s*/?wp:[^>]*-->#', '', $c ); }
function wpautop( $c ) { return '<p>' . $c . '</p>'; }
function shortcode_unautop( $c ) { return $c; }
function wp_filter_content_tags( $c ) { return $c; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function untrailingslashit( $s ) { return rtrim( $s, '/\\' ); }
function wp_strip_all_tags( $s ) { return trim( strip_tags( $s ) ); }


/* ---- existing pages, like a fresh WordPress with one old front page and a contact page */
$POSTS[45] = array( 'ID' => 45, 'post_name' => 'home-old', 'post_title' => 'Home old', 'post_status' => 'publish', 'path' => 'home-old' );
$POSTS[2]  = array( 'ID' => 2, 'post_name' => 'contact', 'post_title' => 'Contact', 'post_status' => 'publish', 'path' => 'contact' );
$META[2]['_wp_page_template'] = 'default';
$META[2]['_yoast_wpseo_metadesc'] = 'Old contact description';

require $PLUGIN . 'pm-site.php';
do_action( 'after_setup_theme' );
$ok = 0; $fail = 0;
function check( $cond, $what ) { global $ok, $fail; if ( $cond ) { $ok++; } else { $fail++; echo "FAIL: $what\n"; } }

check( in_array( 'title-tag', $THEME_SUPPORT, true ), 'title-tag support added' );

/* 1. Links before setup: nothing connected, so they fall back */
check( pm_site_url( 'aanpak', 'nl' ) === 'https://stage.test/contact/', 'unconnected nl link falls back to the contact page' );
check( pm_site_url( 'aanpak', 'en' ) === 'https://stage.test/en/', 'unconnected en link (no english contact page yet) falls back to /en/' );
check( pm_site_url( 'contact', 'nl' ) === 'https://stage.test/contact/', 'existing contact page is found by path' );

/* 2. Data */
$pages = pm_site( 'pages' ); $templates = pm_site( 'templates' ); $seo = pm_site( 'seo' );
check( count( $pages ) === 24, '24 pages (12 nl + 12 en), got ' . count( $pages ) );
check( count( $templates ) === 24, '24 page templates' );
check( count( $seo ) === 26, '26 SEO texts (24 pages + 2 x 404), got ' . count( $seo ) );
check( $pages['en:aanpak']['path'] === 'en/approach' && $pages['en:aanpak']['parent'] === 'en:index', 'english page lives under /en/' );
check( $pages['nl:index']['path'] === 'home' && $pages['en:index']['path'] === 'en', 'home paths' );
foreach ( $pages as $full => $p ) {
	check( file_exists( $PLUGIN . 'templates/' . basename( $p['template'] ) ), "template file for $full" );
}
check( in_array( 'pm-site/nl-home.php', pm_site( 'thread' ), true ) && in_array( 'pm-site/en-home.php', pm_site( 'thread' ), true ), 'thread script on both homes' );

/* 3. Connect all pages (admin) */
pm_site_setup_apply();
$connected = get_option( 'pm_site_pages' );
check( count( $connected ) === 24, 'all 24 pages connected' );
check( $connected['nl:contact'] === 2, 'existing contact page reused' );
check( $connected['nl:index'] === 45, 'existing front page reused for nl home' );
check( get_page_template_slug( 2 ) === 'pm-site/nl-contact.php', 'contact page got the template' );
check( (int) get_option( 'page_on_front' ) === 45 && get_option( 'show_on_front' ) === 'page', 'front page is the nl home' );
check( $POSTS[ $connected['en:aanpak'] ]['post_parent'] === $connected['en:index'], 'english pages are children of /en/' );
check( get_permalink( $connected['en:aanpak'] ) === 'https://stage.test/en/approach/', 'english permalink' );
check( get_permalink( $connected['nl:aanpak'] ) === 'https://stage.test/aanpak/', 'dutch permalink' );
check( get_post_meta( $connected['nl:aanpak'], '_pm_created', true ) == 1, 'created pages are marked' );
check( get_post_meta( 2, '_pm_prev_template', true ) === 'default', 'previous template remembered' );
pm_site_setup_apply(); // twice must not duplicate
$again = get_option( 'pm_site_pages' );
check( $again === $connected, 'connecting again changes nothing' );
$count = count( array_filter( $POSTS, function ( $p ) { return isset( $p['path'] ) && 'en' === $p['path']; } ) );
check( 1 === $count, 'only one /en/ page after connecting twice' );

/* 4. Links after setup (new keys: the url cache holds one value per key) */
check( pm_site_url( 'prijzen', 'nl' ) === 'https://stage.test/prijzen/', 'nl pricing link' );
check( pm_site_url( 'prijzen', 'en' ) === 'https://stage.test/en/pricing/', 'en pricing link' );
check( pm_site_url( 'index', 'nl' ) === 'https://stage.test/home-old/', 'nl home link is the front page permalink' );

/* 5. Templates render without warnings, with resolved links and assets */
$bad = 0;
foreach ( $templates as $tkey => $label ) {
	ob_start();
	try { include $PLUGIN . 'templates/' . basename( $tkey ); } catch ( Throwable $e ) { echo 'EXC ' . $e->getMessage(); $bad++; }
	$out = ob_get_clean();
	$lang = 0 === strpos( basename( $tkey ), 'en-' ) ? 'en' : 'nl';
	check( false !== strpos( $out, '<html lang="' . $lang . '">' ), "$tkey has lang $lang" );
	check( false === strpos( $out, 'pm_site_url(' ) && false === strpos( $out, '.html"' ), "$tkey has no unresolved links" );
	check( 1 === substr_count( $out, '<main id="top"' ), "$tkey has one main" );
	check( false !== strpos( $out, 'https://stage.test/wp-content/plugins/pm-site/assets/logos/' ) || false === strpos( $out, 'assets/logos' ), "$tkey assets resolve" );
}
check( 0 === $bad, 'no template threw' );
ob_start(); include $PLUGIN . 'templates/en-home.php'; $en = ob_get_clean();
check( false !== strpos( $en, 'href="https://stage.test/en/approach/"' ) || false !== strpos( $en, 'https://stage.test/en/' ), 'english home links to english pages' );
check( false !== strpos( $en, 'data-labels=' ), 'thread data kept on home' );

/* 6. Which template for which request */
$Q = array( 'id' => $connected['en:aanpak'] );
check( pm_site_current_template() === 'pm-site/en-approach.php', 'current template for an english page: ' . pm_site_current_template() );
$Q = array( 'id' => $connected['nl:aanpak'] );
check( pm_site_current_template() === 'pm-site/nl-aanpak.php', 'current template for a dutch page' );
$Q = array( '404' => 1 ); $_SERVER['REQUEST_URI'] = '/en/nothing/';
check( pm_site_current_template() === 'pm-site/en-404.php', 'english 404' );
$_SERVER['REQUEST_URI'] = '/nothing/';
check( pm_site_current_template() === 'pm-site/nl-404.php', 'dutch 404' );
check( apply_filters( 'template_include', '/theme/index.php' ) === $PLUGIN . 'templates/nl-404.php', 'template_include serves our 404' );
$Q = array( 'id' => 2 ); update_post_meta( 77777, '_wp_page_template', 'something/else.php' );
$POSTS[77777] = array( 'ID' => 77777, 'post_name' => 'other', 'post_title' => 'Other', 'post_status' => 'publish', 'path' => 'other' );
$Q = array( 'id' => 77777 );
check( pm_site_current_template() === '', 'other templates are left alone' );
check( apply_filters( 'template_include', '/theme/page.php' ) === '/theme/page.php', 'theme template untouched elsewhere' );

/* 7. Scripts and styles */
$Q = array( 'id' => $connected['nl:index'] );
$STYLES->queue = array( 'astra-theme-css', 'wp-block-library', 'elementor-frontend', 'some-plugin' );
$STYLES->registered = array( 'astra-theme-css' => (object) array( 'src' => 'https://x.test/wp-content/themes/astra/style.css' ), 'wp-block-library' => (object) array( 'src' => '/wp-includes/blocks.css' ),
	'elementor-frontend' => (object) array( 'src' => 'https://x.test/wp-content/plugins/elementor/frontend.css' ), 'some-plugin' => (object) array( 'src' => 'https://x.test/wp-content/plugins/cookie/style.css' ) );
do_action( 'wp_enqueue_scripts' );
check( in_array( 'style:pm-site', $ENQ, true ) && in_array( 'script:pm-site', $ENQ, true ), 'our css and js are enqueued' );
check( in_array( 'script:pm-site-thread', $ENQ, true ), 'thread script on the home page' );
check( $STYLES->queue === array( 'some-plugin' ), 'theme and builder css removed, other plugins kept: ' . implode( ',', $STYLES->queue ) );
$ENQ = array(); $Q = array( 'id' => $connected['nl:aanpak'] ); do_action( 'wp_enqueue_scripts' );
check( ! in_array( 'script:pm-site-thread', $ENQ, true ), 'thread script not on other pages' );

/* 8. SEO and hreflang */
$Q = array( 'id' => $connected['nl:aanpak'] );
check( apply_filters( 'wpseo_title', 'Theme title' ) === $seo['pm-site/nl-aanpak.php']['title'], 'our title replaces Yoast fallback' );
check( apply_filters( 'wpseo_metadesc', '' ) === $seo['pm-site/nl-aanpak.php']['description'], 'our description' );
$Q = array( 'id' => 2 ); // contact has its own old Yoast description
check( apply_filters( 'wpseo_metadesc', 'Old contact description' ) === 'Old contact description', 'own Yoast description wins' );
$Q = array( 'id' => $connected['nl:scan'] );
ob_start(); do_action( 'wp_head' ); $head = ob_get_clean();
check( false !== strpos( $head, 'hreflang="nl" href="https://stage.test/scan/"' ) && false !== strpos( $head, 'hreflang="en" href="https://stage.test/en/scan/"' ), 'hreflang links: ' . trim( $head ) );
check( false !== strpos( $head, 'hreflang="x-default"' ), 'x-default' );

/* 9. Yoast texts and Undo */
pm_site_seo_apply();
check( get_post_meta( $connected['nl:prijzen'], '_yoast_wpseo_title', true ) === $seo['pm-site/nl-prijzen.php']['title'], 'seo title written to yoast' );
check( get_post_meta( 2, '_yoast_wpseo_metadesc', true ) === $seo['pm-site/nl-contact.php']['description'], 'seo description replaced on reused page' );
pm_site_seo_undo();
check( get_post_meta( 2, '_yoast_wpseo_metadesc', true ) === 'Old contact description', 'old yoast text restored' );
pm_site_seo_apply();
pm_site_setup_undo();
check( get_post_meta( 2, '_wp_page_template', true ) === 'default', 'reused page back on its template' );
check( 'draft' === get_post_status( $connected['nl:aanpak'] ), 'created pages back to draft' );
check( ! metadata_exists( 'post', 2, '_pm_prev_template' ), 'undo cleans its marker' );
check( false === get_option( 'pm_site_pages', false ), 'connections forgotten' );
check( get_post_meta( 2, '_yoast_wpseo_metadesc', true ) === 'Old contact description', 'undo restores yoast too' );
check( (int) get_option( 'page_on_front' ) === 45, 'front page as it was (it was the same page)' );

/* 10. Admin screen renders */
$GLOBALS['IS_ADMIN'] = true;
ob_start(); pm_site_admin_page(); $admin = ob_get_clean();
check( false !== strpos( $admin, 'PM Site' ) && false !== strpos( $admin, 'Connect all pages' ), 'admin page renders before setup' );
pm_site_setup_apply();
ob_start(); pm_site_admin_page(); $admin = ob_get_clean();
check( false !== strpos( $admin, 'Undo' ), 'admin page renders after setup' );
check( false !== strpos( $admin, '/en/approach/' ), 'admin lists english pages' );

echo "$ok checks passed, $fail failed\n";
exit( $fail ? 1 : 0 );
