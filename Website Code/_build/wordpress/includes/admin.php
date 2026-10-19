<?php
/**
 * PM Site: setup screen (admin menu → PM Site).
 *
 * 1. Pages: connects every page of the design (Dutch and English) to a WordPress
 *    page. Existing pages are reused (same URL, same Yoast settings); missing ones
 *    are created, English ones under the page "en". The Dutch home becomes the
 *    front page. "Undo" puts everything back as it was.
 * 2. Titles and descriptions: writes the SEO texts into Yoast (old ones kept).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page( 'PM Site', 'PM Site', 'manage_options', 'pm-site', 'pm_site_admin_page', 'dashicons-chart-line', 59 );
} );

/** The WordPress page a page of the design uses or will use; 0 = will be created. */
function pm_site_setup_target( $full, $spec ) {
	$connected = get_option( 'pm_site_pages', array() );
	if ( ! empty( $connected[ $full ] ) && get_post( $connected[ $full ] ) && 'trash' !== get_post_status( $connected[ $full ] ) ) {
		return (int) $connected[ $full ];
	}
	if ( 'nl:index' === $full && 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
		return (int) get_option( 'page_on_front' );
	}
	$page = get_page_by_path( $spec['path'], OBJECT, 'page' );
	return ( $page && 'trash' !== $page->post_status ) ? (int) $page->ID : 0;
}

/** Connect all pages: reuse or create, remember the previous template, set ours. */
function pm_site_setup_apply() {
	$connected = array();
	foreach ( pm_site( 'pages' ) as $full => $spec ) {
		$id = pm_site_setup_target( $full, $spec );
		if ( ! $id ) {
			$parent = 0;
			if ( '' !== $spec['parent'] && ! empty( $connected[ $spec['parent'] ] ) ) {
				$parent = (int) $connected[ $spec['parent'] ];
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $spec['title'],
					'post_name'    => $spec['slug'],
					'post_parent'  => $parent,
					'post_content' => '',
				),
				true
			);
			if ( is_wp_error( $id ) || ! $id ) {
				continue;
			}
			update_post_meta( $id, '_pm_created', 1 );
		} elseif ( get_post_meta( $id, '_pm_created', true ) && 'publish' !== get_post_status( $id ) ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
		if ( ! metadata_exists( 'post', $id, '_pm_prev_template' ) ) {
			update_post_meta( $id, '_pm_prev_template', (string) get_post_meta( $id, '_wp_page_template', true ) );
		}
		update_post_meta( $id, '_wp_page_template', $spec['template'] );
		$connected[ $full ] = (int) $id;
	}
	// The Dutch home is the front page of the site.
	if ( ! empty( $connected['nl:index'] ) && ( 'page' !== get_option( 'show_on_front' ) || (int) get_option( 'page_on_front' ) !== $connected['nl:index'] ) ) {
		if ( false === get_option( 'pm_site_prev_front', false ) ) {
			update_option( 'pm_site_prev_front', array( get_option( 'show_on_front' ), (int) get_option( 'page_on_front' ) ), false );
		}
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $connected['nl:index'] );
	}
	update_option( 'pm_site_pages', $connected, false );
}

/** Undo: previous templates back, pages we created back to draft, front page back. */
function pm_site_setup_undo() {
	pm_site_seo_undo();
	foreach ( (array) get_option( 'pm_site_pages', array() ) as $id ) {
		if ( ! get_post( $id ) ) {
			continue;
		}
		if ( metadata_exists( 'post', $id, '_pm_prev_template' ) ) {
			$previous = (string) get_post_meta( $id, '_pm_prev_template', true );
			if ( '' !== $previous ) {
				update_post_meta( $id, '_wp_page_template', $previous );
			} else {
				delete_post_meta( $id, '_wp_page_template' );
			}
			delete_post_meta( $id, '_pm_prev_template' );
		}
		if ( get_post_meta( $id, '_pm_created', true ) ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
		}
	}
	$front = get_option( 'pm_site_prev_front' );
	if ( is_array( $front ) ) {
		update_option( 'show_on_front', $front[0] );
		update_option( 'page_on_front', $front[1] );
		delete_option( 'pm_site_prev_front' );
	}
	delete_option( 'pm_site_pages' );
}

/** Yoast fields per SEO text, and where the old value is kept for Undo. */
function pm_site_seo_fields() {
	return array(
		'title'       => array( '_yoast_wpseo_title', '_pm_prev_yoast_title' ),
		'description' => array( '_yoast_wpseo_metadesc', '_pm_prev_yoast_desc' ),
	);
}

/** Write the titles and descriptions into Yoast. The old texts are kept; Undo restores them. */
function pm_site_seo_apply() {
	$texts = pm_site( 'seo' );
	foreach ( (array) get_option( 'pm_site_pages', array() ) as $id ) {
		$template = get_page_template_slug( $id );
		if ( empty( $texts[ $template ] ) ) {
			continue;
		}
		foreach ( pm_site_seo_fields() as $field => $keys ) {
			if ( ! metadata_exists( 'post', $id, $keys[1] ) ) {
				update_post_meta( $id, $keys[1], (string) get_post_meta( $id, $keys[0], true ) );
			}
			update_post_meta( $id, $keys[0], $texts[ $template ][ $field ] );
		}
		wp_update_post( array( 'ID' => $id ) ); // lets Yoast refresh its cached copy of the page
	}
	update_option( 'pm_site_seo_applied', 1, false );
}

/** Put the old Yoast texts back. */
function pm_site_seo_undo() {
	foreach ( (array) get_option( 'pm_site_pages', array() ) as $id ) {
		$changed = false;
		foreach ( pm_site_seo_fields() as $keys ) {
			if ( metadata_exists( 'post', $id, $keys[1] ) ) {
				$previous = (string) get_post_meta( $id, $keys[1], true );
				if ( '' !== $previous ) {
					update_post_meta( $id, $keys[0], $previous );
				} else {
					delete_post_meta( $id, $keys[0] );
				}
				delete_post_meta( $id, $keys[1] );
				$changed = true;
			}
		}
		if ( $changed ) {
			wp_update_post( array( 'ID' => $id ) );
		}
	}
	delete_option( 'pm_site_seo_applied' );
}

/* One handler for all buttons. */
add_action( 'admin_post_pm_site', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You are not allowed to do this.' );
	}
	check_admin_referer( 'pm_site' );
	$do = isset( $_POST['pm_do'] ) ? sanitize_key( wp_unslash( $_POST['pm_do'] ) ) : '';
	if ( 'apply' === $do ) {
		pm_site_setup_apply();
	} elseif ( 'undo' === $do ) {
		pm_site_setup_undo();
	} elseif ( 'seo' === $do ) {
		pm_site_seo_apply();
	} elseif ( 'seo-undo' === $do ) {
		pm_site_seo_undo();
	}
	do_action( 'litespeed_purge_all' ); // LiteSpeed Cache: show the change straight away.
	wp_safe_redirect( add_query_arg( array( 'page' => 'pm-site', 'pm_done' => $do ), admin_url( 'admin.php' ) ) );
	exit;
} );

/** Opening tag of a form that posts to the handler above. */
function pm_site_admin_form( $do ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin-right:8px">';
	echo '<input type="hidden" name="action" value="pm_site"><input type="hidden" name="pm_do" value="' . esc_attr( $do ) . '">';
	wp_nonce_field( 'pm_site' );
}

function pm_site_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$templates = pm_site( 'templates' );
	$connected = get_option( 'pm_site_pages', array() );
	$done      = isset( $_GET['pm_done'] ) ? sanitize_key( wp_unslash( $_GET['pm_done'] ) ) : '';
	$messages  = array(
		'apply'    => 'All pages are connected to the design.',
		'undo'     => 'Undone: every page is back on its previous template and the front page is as it was.',
		'seo'      => 'The titles and descriptions are in Yoast.',
		'seo-undo' => 'The old Yoast titles and descriptions are back.',
	);

	echo '<div class="wrap"><h1>PM Site</h1>';
	echo '<p>Plugin version ' . esc_html( PM_SITE_VERSION ) . '. The processmining.nu design (Dutch and English), generated from the static site.</p>';
	if ( isset( $messages[ $done ] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $messages[ $done ] ) . '</p></div>';
	}
	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		echo '<div class="notice notice-warning"><p><strong>Permalinks:</strong> the English pages live under /en/, which needs pretty permalinks. '
			. 'Choose <em>Berichtnaam</em> (post name) under Instellingen &rarr; Permalinks.</p></div>';
	}

	/* 1. Pages ------------------------------------------------------------ */
	echo '<h2>1. Pages</h2>';
	echo '<p>Existing pages keep their URL and Yoast settings. Missing pages are created (English pages under <code>/en/</code>). The Dutch home becomes the front page.</p>';
	echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>Design</th><th>WordPress page</th><th>Status</th><th>Template now</th></tr></thead><tbody>';
	foreach ( pm_site( 'pages' ) as $full => $spec ) {
		$id = pm_site_setup_target( $full, $spec );
		echo '<tr><td><strong>' . esc_html( $templates[ $spec['template'] ] ) . '</strong></td>';
		if ( $id ) {
			$current = get_page_template_slug( $id );
			$link    = get_permalink( $id );
			echo '<td><a href="' . esc_url( get_edit_post_link( $id, 'raw' ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a>';
			echo ' &middot; <a href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . esc_html( wp_make_link_relative( $link ) ) . '</a></td>';
			echo '<td>' . esc_html( get_post_status( $id ) ) . '</td>';
			if ( $current === $spec['template'] ) {
				echo '<td><span class="dashicons dashicons-yes" style="color:#1a7f37"></span> design</td>';
			} else {
				echo '<td>' . esc_html( $current ? $current : 'default' ) . '</td>';
			}
		} else {
			echo '<td><em>Will be created at /' . esc_html( $spec['path'] ) . '/</em></td><td>&ndash;</td><td>&ndash;</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table><p>';
	pm_site_admin_form( 'apply' );
	submit_button( empty( $connected ) ? 'Connect all pages' : 'Connect again', 'primary', 'submit', false );
	echo '</form>';
	if ( ! empty( $connected ) ) {
		pm_site_admin_form( 'undo' );
		submit_button( 'Undo', 'secondary', 'submit', false );
		echo '</form>';
	}
	echo '</p>';

	/* 2. SEO texts -------------------------------------------------------- */
	echo '<h2>2. Titles and descriptions (Yoast)</h2>';
	if ( empty( $connected ) ) {
		echo '<p>Connect the pages first.</p></div>';
		return;
	}
	echo '<p>Pages without their own Yoast text already use ours. This button writes them into each page\'s Yoast fields so they stay editable there. ';
	echo 'The old texts are kept and come back with "Restore" or Undo.</p><p>';
	pm_site_admin_form( 'seo' );
	submit_button( 'Use the titles and descriptions', 'secondary', 'submit', false );
	echo '</form>';
	if ( get_option( 'pm_site_seo_applied' ) ) {
		pm_site_admin_form( 'seo-undo' );
		submit_button( 'Restore the old ones', 'secondary', 'submit', false );
		echo '</form>';
	}
	echo '</p></div>';
}
