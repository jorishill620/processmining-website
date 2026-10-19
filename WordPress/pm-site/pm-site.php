<?php
/**
 * Plugin Name:       PM Site
 * Description:       The processmining.nu design (Dutch and English) as page templates. Set up under PM Site in the admin menu. Generated from the static site; do not edit by hand.
 * Version:           1.0.20261019.1040
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Coney Solutions
 * Text Domain:       pm-site
 *
 * Source: Website Code/_build/wordpress/ (built by _build/wordpress.py).
 * The page data (templates, slugs, SEO texts) is generated into includes/data.php.
 */

defined( 'ABSPATH' ) || exit;

define( 'PM_SITE_DIR', plugin_dir_path( __FILE__ ) );
define( 'PM_SITE_URL', plugin_dir_url( __FILE__ ) );

require_once PM_SITE_DIR . 'includes/data.php';
if ( is_admin() ) {
	require_once PM_SITE_DIR . 'includes/admin.php';
}

/** One part of the generated data: templates, pages, seo, … */
function pm_site( $part ) {
	static $data = null;
	if ( null === $data ) {
		$data = pm_site_data();
	}
	return isset( $data[ $part ] ) ? $data[ $part ] : array();
}

/* Our templates print their own <head>; WordPress needs this to put <title> in it. */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
}, 20 );

/* =========================================================================
   Which template, which language
   ========================================================================= */

/** The request path relative to the site (works when WordPress lives in a subfolder). */
function pm_site_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = rawurldecode( (string) wp_parse_url( $uri, PHP_URL_PATH ) );
	$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( '' !== $base && '/' !== $base && 0 === strpos( $path, rtrim( $base, '/' ) . '/' ) ) {
		$path = substr( $path, strlen( rtrim( $base, '/' ) ) );
	}
	return $path;
}

/** The template file of a template key (pm-site/nl-aanpak.php), or ''. */
function pm_site_template_file( $template ) {
	$file = PM_SITE_DIR . 'templates/' . basename( $template );
	return file_exists( $file ) ? $file : '';
}

/** The PM template for this request, or '' when WordPress shows something else. */
function pm_site_current_template() {
	if ( is_404() ) {
		$path = '/' . trim( strtolower( pm_site_request_path() ), '/' ) . '/';
		return ( 0 === strpos( $path, '/en/' ) ) ? 'pm-site/en-404.php' : 'pm-site/nl-404.php';
	}
	if ( ! is_singular( 'page' ) ) {
		return '';
	}
	$template  = get_page_template_slug( get_queried_object_id() );
	$templates = pm_site( 'templates' );
	return ( $template && isset( $templates[ $template ] ) ) ? $template : '';
}

/** The page key ("nl:aanpak") of a template key, or ''. */
function pm_site_key_of_template( $template ) {
	foreach ( pm_site( 'pages' ) as $key => $spec ) {
		if ( $spec['template'] === $template ) {
			return $key;
		}
	}
	return '';
}

/* =========================================================================
   Links and assets (used by the templates)
   ========================================================================= */

/** The WordPress page for a page of the static site: the one connected on the setup screen, else an existing page by path. */
function pm_site_page_id( $key, $lang ) {
	$pages = pm_site( 'pages' );
	$full  = $lang . ':' . $key;
	if ( ! isset( $pages[ $full ] ) ) {
		return 0;
	}
	$connected = get_option( 'pm_site_pages', array() );
	if ( ! empty( $connected[ $full ] ) && 'publish' === get_post_status( $connected[ $full ] ) ) {
		return (int) $connected[ $full ];
	}
	$page = get_page_by_path( $pages[ $full ]['path'] );
	return ( $page && 'publish' === $page->post_status ) ? (int) $page->ID : 0;
}

/** Link to a page of the static site (Dutch key such as "aanpak") in a language. Falls back to contact, then home. */
function pm_site_url( $key, $lang = 'nl' ) {
	static $cache = array();
	$ck = $lang . ':' . $key;
	if ( isset( $cache[ $ck ] ) ) {
		return $cache[ $ck ];
	}
	$id = pm_site_page_id( $key, $lang );
	if ( ! $id && 'contact' !== $key ) {
		$id = pm_site_page_id( 'contact', $lang );
	}
	if ( $id ) {
		$cache[ $ck ] = get_permalink( $id );
	} else {
		$cache[ $ck ] = home_url( 'en' === $lang ? '/en/' : '/' );
	}
	return $cache[ $ck ];
}

/** URL of a file in the plugin's assets folder. */
function pm_site_asset( $path ) {
	return PM_SITE_URL . 'assets/' . ltrim( $path, '/' );
}

/* =========================================================================
   Templates
   ========================================================================= */

/* Offer the templates in the page editor. */
add_filter( 'theme_page_templates', function ( $templates ) {
	return array_merge( $templates, pm_site( 'templates' ) );
} );

/* Render our template instead of the theme's (late, after page builders). */
add_filter( 'template_include', function ( $template ) {
	$current = pm_site_current_template();
	if ( $current ) {
		$file = pm_site_template_file( $current );
		if ( $file ) {
			if ( is_404() ) {
				status_header( 404 );
			}
			return $file;
		}
	}
	return $template;
}, 9999 );

/* =========================================================================
   CSS and JS
   ========================================================================= */

/* Ours, on our templates only. */
add_action( 'wp_enqueue_scripts', function () {
	$current = pm_site_current_template();
	if ( ! $current ) {
		return;
	}
	$version = PM_SITE_VERSION;
	wp_enqueue_style( 'pm-site-fonts', pm_site( 'fonts' ), array(), null );
	wp_enqueue_style( 'pm-site', pm_site_asset( 'css/pm.css' ), array( 'pm-site-fonts' ), $version );
	wp_enqueue_style( 'pm-site-wp', pm_site_asset( 'css/wordpress.css' ), array( 'pm-site' ), $version );
	wp_enqueue_script( 'pm-site', pm_site_asset( 'js/site.js' ), array(), $version, true );
	if ( in_array( $current, pm_site( 'thread' ), true ) ) {
		wp_enqueue_script( 'pm-site-thread', pm_site_asset( 'js/thread.js' ), array( 'pm-site' ), $version, true );
	}
}, 20 );

/* Keep the theme and page builders out of our pages: their CSS and JS would
   restyle the design. Other plugins (analytics, cookies, SEO, forms) keep working. */
function pm_site_strip_assets() {
	if ( ! pm_site_current_template() ) {
		return;
	}
	$sources  = apply_filters( 'pm_site_strip_sources', array(
		'/themes/', '/plugins/elementor/', '/plugins/elementor-pro/', '/plugins/bb-plugin/', '/plugins/bb-theme-builder/',
		'/plugins/bbpowerpack/', '/plugins/astra-addon/', '/plugins/divi-builder/', '/uploads/elementor/', '/uploads/bb-plugin/', '/uploads/astra',
	) );
	$prefixes = apply_filters( 'pm_site_strip_prefixes', array( 'astra', 'ast-', 'fl-builder', 'fl-theme-builder', 'elementor', 'hello-elementor', 'et-', 'divi' ) );
	$handles  = apply_filters( 'pm_site_strip_handles', array(
		'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'core-block-supports',
	) );
	foreach ( array( 'style' => wp_styles(), 'script' => wp_scripts() ) as $type => $deps ) {
		foreach ( (array) $deps->queue as $handle ) {
			if ( 0 === strpos( $handle, 'pm-site' ) ) {
				continue;
			}
			$src   = isset( $deps->registered[ $handle ] ) ? (string) $deps->registered[ $handle ]->src : '';
			$strip = in_array( $handle, $handles, true );
			foreach ( $prefixes as $prefix ) {
				if ( 0 === strpos( $handle, $prefix ) ) {
					$strip = true;
				}
			}
			foreach ( $sources as $needle ) {
				if ( '' !== $src && false !== strpos( $src, $needle ) ) {
					$strip = true;
				}
			}
			if ( $strip ) {
				if ( 'style' === $type ) {
					wp_dequeue_style( $handle );
				} else {
					wp_dequeue_script( $handle );
				}
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'pm_site_strip_assets', 9999 );
add_action( 'wp_print_styles', 'pm_site_strip_assets', 9999 );
add_action( 'wp_print_footer_scripts', 'pm_site_strip_assets', 1 );

/* The theme's "Additional CSS" (Customizer) is written for another design. */
add_action( 'wp', function () {
	if ( pm_site_current_template() ) {
		remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
	}
} );

/* LiteSpeed Cache and similar: leave our files alone (no combining, deferring or delaying). */
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 0 === strpos( $handle, 'pm-site' ) ) {
		$tag = str_replace( '<script ', '<script data-no-optimize="1" data-no-defer="1" ', $tag );
	}
	return $tag;
}, 10, 2 );
add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	if ( 0 === strpos( $handle, 'pm-site' ) ) {
		$tag = str_replace( '<link ', '<link data-no-optimize="1" ', $tag );
	}
	return $tag;
}, 10, 2 );

/* =========================================================================
   SEO: our title and description from the static site, unless the page has
   its own in Yoast. Plus hreflang links between the Dutch and English page.
   ========================================================================= */

function pm_site_seo( $field ) {
	$current = pm_site_current_template();
	if ( ! $current ) {
		return '';
	}
	$seo = pm_site( 'seo' );
	if ( empty( $seo[ $current ][ $field ] ) ) {
		return '';
	}
	if ( is_singular( 'page' ) ) {
		$own = get_post_meta( get_queried_object_id(), 'title' === $field ? '_yoast_wpseo_title' : '_yoast_wpseo_metadesc', true );
		if ( '' !== (string) $own ) {
			return '';
		}
	}
	return $seo[ $current ][ $field ];
}

foreach ( array( 'wpseo_title', 'wpseo_opengraph_title', 'wpseo_twitter_title' ) as $pm_site_hook ) {
	add_filter( $pm_site_hook, function ( $value ) {
		$own = pm_site_seo( 'title' );
		return $own ? $own : $value;
	} );
}
foreach ( array( 'wpseo_metadesc', 'wpseo_opengraph_desc', 'wpseo_twitter_description' ) as $pm_site_hook ) {
	add_filter( $pm_site_hook, function ( $value ) {
		$own = pm_site_seo( 'description' );
		return $own ? $own : $value;
	} );
}
unset( $pm_site_hook );

/* Without Yoast. */
add_filter( 'pre_get_document_title', function ( $title ) {
	if ( defined( 'WPSEO_VERSION' ) ) {
		return $title;
	}
	$own = pm_site_seo( 'title' );
	return $own ? $own : $title;
} );
add_action( 'wp_head', function () {
	if ( defined( 'WPSEO_VERSION' ) ) {
		return;
	}
	$description = pm_site_seo( 'description' );
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
}, 1 );

/* Dutch and English version of the same page point at each other. */
add_action( 'wp_head', function () {
	$current = pm_site_current_template();
	if ( ! $current || ! is_singular( 'page' ) ) {
		return;
	}
	$key = pm_site_key_of_template( $current );
	if ( '' === $key ) {
		return;
	}
	list( , $name ) = explode( ':', $key, 2 );
	$nl = pm_site_url( $name, 'nl' );
	$en = pm_site_url( $name, 'en' );
	if ( ! $nl || ! $en || $nl === $en ) {
		return;
	}
	echo '<link rel="alternate" hreflang="nl" href="' . esc_url( $nl ) . '">' . "\n";
	echo '<link rel="alternate" hreflang="en" href="' . esc_url( $en ) . '">' . "\n";
	echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $nl ) . '">' . "\n";
}, 2 );
