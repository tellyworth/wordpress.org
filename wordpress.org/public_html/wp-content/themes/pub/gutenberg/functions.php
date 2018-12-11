<?php
/**
 * gutenbergtheme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Gutenbergtheme
 */

if ( ! defined( 'WPORGPATH' ) ) {
	define( 'WPORGPATH', get_theme_file_path( '/inc/' ) );
}


add_action( 'template_redirect', function() {
	if ( ! is_page( 'test' ) ) {
		return;
	}

	show_admin_bar( true );

	add_action( 'wp_enqueue_scripts', function() {
		wp_enqueue_script( 'postbox', admin_url( 'js/postbox.min.js' ),array( 'jquery-ui-sortable' ), false, 1 );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'common' );
		wp_enqueue_style( 'forms' );
		wp_enqueue_style( 'dashboard' );
		wp_enqueue_style( 'media' );
		wp_enqueue_style( 'admin-menu' );
		wp_enqueue_style( 'admin-bar' );
		wp_enqueue_style( 'nav-menus' );
		wp_enqueue_style( 'l10n' );
		wp_enqueue_style( 'buttons' );

		// Use a middleware provider to intercept and modify API calls. Short-circuit POST requests, bound queries, allow media, etc.
		wp_add_inline_script( 'wp-api-fetch',
			'wp.apiFetch.use( function( options, next ) {
				var isWhitelistedEndpoint = (
					lodash.startsWith( options.path, "/oembed/1.0/proxy" ) ||
					lodash.startsWith( options.path, "/gutenberg/v1/block-renderer" )
				);

				// Prevent non-whitelisted non-GET requests (ie. POST) to prevent errors
				if ( options.method && options.method !== "GET" && ! isWhitelistedEndpoint ) {
					// This works in enough cases to be the default return value.
					return Promise.resolve( options.data );
				}

				// Add limits to all GET queries which attempt unbound queries
				options.path = options.path.replace( "per_page=-1", "per_page=10" );

				// Load images with the view context, seems to work
				if ( lodash.startsWith( options.path, "/wp/v2/media/" ) ) {
					options.path = options.path.replace( "context=edit", "context=view" );
				}

				return next( options );
			} );',
			'after'
		);

		// Use a middleware preloader to handle the "types" API endpoints with minimal data needed
		wp_add_inline_script( 'wp-api-fetch',
			'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( {
				"/wp/v2/types?context=edit": { "body": {
					"page": {
						"rest_base": "pages",
						"supports": {}
					},
					"wp_block": {
						"rest_base": "blocks",
						"supports": {}
					}
				} },
				"/wp/v2/types/page?context=edit": { "body": {
					"rest_base": "pages",
					"supports": {}
				} },
				"/wp/v2/types/wp_block?context=edit": { "body": {
					"rest_base": "blocks",
					"supports": {}
				} }
			} ) );',
			'after'
		);

		// Use a middleware preloader to load the custom post content:
		$frontendburg_content = include __DIR__ . '/gutenfront-content.php';
		wp_add_inline_script( 'wp-api-fetch',
			sprintf(
				'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );',
				wp_json_encode( array(
					"/wp/v2/pages/" . get_post()->ID . "?context=edit" => [ 'body' => [
						'id' => get_post()->ID,
						'title' => [ 'raw' => $frontendburg_content['title'] ],
						'content' => [ 'block_format' => 1, 'raw' => $frontendburg_content['content'] ],
						'excerpt' => [ 'raw' => '' ],
						'date' => '', 'date_gmt' => '', 'modified' => '', 'modified_gmt' => '',
						'link' => home_url('/'), 'guid' => [],
						'parent' => 0, 'menu_order' => 0, 'author' => 0, 'featured_media' => 0,
						'comment_status' => 'closed', 'ping_status' => 'closed', 'template' => '', 'meta' => [], '_links' => [],
						'type' => 'page', 'status' => 'draft',
						'slug' => '', 'generated_slug' => '', 'permalink_template' => home_url('/'),
					] ]
				) )
			),
			'after'
		);

		// Add a middleware provider which intercepts all uploads and stores them within the browser
		wp_add_inline_script( 'wp-api-fetch',
			'wp.apiFetch.use( function( options, next ) {
				if ( options.method == "POST" && options.path == "/wp/v2/media" ) {
					var file = options.body.get("file");

					window.fakeUploadedMedia = window.fakeUploadedMedia || [];
					if ( ! window.fakeUploadedMedia.length ) {
						window.fakeUploadedMedia[9999000] = {};
					}
					var id = window.fakeUploadedMedia.length;

					window.fakeUploadedMedia[ id ] = {
						"id": id,
						"date": "", "date_gmt": "", "modified": "", "modified_gmt": "",
						"guid": {}, "title": { "rendered": file.name, "raw": file.name },
						"description": {}, "caption": {}, "alt_text": "",
						"slug": file.name, "status": "inherit", "type": "attachment", "link": "",
						"author": 0, "comment_status": "open", "ping_status": "closed",
						"media_details": {}, "media_type": file.type.split("/")[0], "mime_type": file.type,
						"source_url": "", // This gets filled below with a data uri
						"_links": {}
					};

						return new Promise( function( resolve ) {
						var a = new FileReader();
    						a.onload = function(e) {
    							window.fakeUploadedMedia[ id ].source_url = e.target.result;
    							resolve( window.fakeUploadedMedia[ id ] );
    						}
    						a.readAsDataURL( file );
    					} );
				}

				// Drag droped media of ID 9999xxx is stored within the Browser
				var path_id_match = options.path.match( "^/wp/v2/media/(9999\\\\d+)" );
				if ( path_id_match ) {
					return Promise.resolve( window.fakeUploadedMedia[ path_id_match[1] ] );
				}

				return next( options );
			} );',
			'after'
		);
		wp_add_inline_script(
			'wp-edit-post',
			'wp.data.dispatch( "core/edit-post" ).closeGeneralSidebar();' .
			'wp.data.dispatch( "core/nux" ).disableTips();' .
			'_wpLoadGutenbergEditor.then( function() { wp.blocks.unregisterBlockType( "core/shortcode" ); } );'
		);

	}, 11 );

	add_action( 'wp_enqueue_scripts', function( $hook ) {
		// Gutenberg requires the post-locking functions defined within:
		// See `show_post_locked_dialog` and `get_post_metadata` filters below.
		include_once ABSPATH . 'wp-admin/includes/post.php';

		gutenberg_editor_scripts_and_styles( $hook );
	} );

	add_action( 'enqueue_block_editor_assets', function() {
		wp_enqueue_script( 'button-readonly', get_template_directory_uri() . '/js/button-readonly.js', array( 'wp-blocks', 'wp-element' ), null );
	} );

	// Disable post locking dialogue.
	add_filter( 'show_post_locked_dialog', '__return_false' );

	// Everyone can richedit! This avoids a case where a page can be cached where a user can't richedit.
	$GLOBALS['wp_rich_edit'] = true;
	add_filter( 'user_can_richedit', '__return_true', 1000 );

	// Homepage is always locked by @wordpressdotorg
	// This prevents other logged-in users taking a lock of the post on the front-end.
	add_filter( 'get_post_metadata', function( $value, $post_id, $meta_key ) {
		if ( $meta_key !== '_edit_lock' ) {
			return $value;
		}

		// This filter is only added on a front-page view of the homepage for this site, no other checks are needed here.

		return time() . ':5911429'; // WordPressdotorg user ID
	}, 10, 3 );

	// Disable use XML-RPC
	add_filter( 'xmlrpc_enabled', '__return_false' );

	// Disable X-Pingback to header
	function disable_x_pingback( $headers ) {
		unset( $headers['X-Pingback'] );

		return $headers;
	}
	add_filter( 'wp_headers', 'disable_x_pingback' );

	function frontenberg_site_title() {
		return esc_html__( 'The new Gutenberg editing experience', 'wporg' );
	}
	add_filter( 'the_title', 'frontenberg_site_title' );
	add_filter( 'option_blogname', 'frontenberg_site_title' );

	function frontenberg_site_description() {
		return esc_html__( 'A new editing experience for WordPress is in the works, code name Gutenberg. Read more about it and test it!', 'wporg' );
	}
	add_filter( 'option_blogdescription', 'frontenberg_site_description' );
});

/**
 * Let unauthenticated users embed media in Frontenberg.
 */
function frontenberg_enable_oembed( $all_caps ) {
	if (
		0 === strpos( $_SERVER['REQUEST_URI'], '/gutenberg/wp-json/oembed/1.0/proxy' )  ||
		0 === strpos( $_SERVER['REQUEST_URI'], '/gutenberg/wp-json/gutenberg/v1/block-renderer/core/archives' ) ||
		0 === strpos( $_SERVER['REQUEST_URI'], '/gutenberg/wp-json/gutenberg/v1/block-renderer/core/latest-comments' )
	) {
		$all_caps['edit_posts'] = true;
	}

	return $all_caps;
}
add_filter( 'user_has_cap', 'frontenberg_enable_oembed' );

/**
 * Ajax handler for querying attachments for logged-out users.
 *
 * @since 3.5.0
 */
function frontenberg_wp_ajax_nopriv_query_attachments() {
	if ( 97589 !== absint( $_REQUEST['post_id'] ) ) {
		wp_send_json_error();
	}
	$query = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
	$keys = array(
		's', 'order', 'orderby', 'posts_per_page', 'paged', 'post_mime_type',
		'post_parent', 'post__in', 'post__not_in', 'year', 'monthnum'
	);
	foreach ( get_taxonomies_for_attachments( 'objects' ) as $t ) {
		if ( $t->query_var && isset( $query[ $t->query_var ] ) ) {
			$keys[] = $t->query_var;
		}
	}

	$query = array_intersect_key( $query, array_flip( $keys ) );
	$query['post_type'] = 'attachment';
	if ( MEDIA_TRASH
		&& ! empty( $_REQUEST['query']['post_status'] )
		&& 'trash' === $_REQUEST['query']['post_status'] ) {
		$query['post_status'] = 'trash';
	} else {
		$query['post_status'] = 'inherit';
	}

	// Filter query clauses to include filenames.
	if ( isset( $query['s'] ) ) {
		add_filter( 'posts_clauses', '_filter_query_attachment_filenames' );
	}

	if ( empty( $query['post__in'] ) ) {
		$query['post__in'] = range( 97654, 97659 );
	}

	/**
	 * Filters the arguments passed to WP_Query during an Ajax
	 * call for querying attachments.
	 *
	 * @since 3.7.0
	 *
	 * @see WP_Query::parse_query()
	 *
	 * @param array $query An array of query variables.
	 */
	$query = apply_filters( 'ajax_query_attachments_args', $query );
	$query = new WP_Query( $query );

	$posts = array_map( 'wp_prepare_attachment_for_js', $query->posts );
	$posts = array_filter( $posts );

	wp_send_json_success( $posts );
}
add_action( 'wp_ajax_nopriv_query-attachments', 'frontenberg_wp_ajax_nopriv_query_attachments' );

/**
 * Removes tagline, which is used more as a description on this site.
 *
 * @param array $title {
 *     The document title parts.
 *
 *     @type string $title   Title of the viewed page.
 *     @type string $page    Optional. Page number if paginated.
 *     @type string $tagline Optional. Site description when on home page.
 *     @type string $site    Optional. Site title when not on home page.
 * }
 */
function gutenberg_title_parts( $title ) {
	unset( $title['tagline'] );

	return $title;
}
add_filter( 'document_title_parts', 'gutenberg_title_parts' );

if ( ! function_exists( 'gutenbergtheme_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function gutenbergtheme_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on gutenbergtheme, use a find and replace
		 * to change 'gutenbergtheme' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'gutenbergtheme', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary', 'gutenbergtheme' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		add_theme_support( 'align-wide' );

		add_theme_support( 'editor-color-palette', [
			[
				'name'  => esc_html__( 'Dark Blue', 'gutenbergtheme' ),
				'slug' => 'dark-blue',
				'color' => '#0073aa',
			],
			[

				'name'  => esc_html__( 'Light Blue', 'gutenbergtheme' ),
				'slug' => 'light-blue',
				'color' => '#229fd8',
			],
			[

				'name'  => esc_html__( 'Dark Gray', 'gutenbergtheme' ),
				'slug' => 'dark-gray',
				'color' => '#444',
			],
			[

				'name'  => esc_html__( 'Light Gray', 'gutenbergtheme' ),
				'slug' => 'light-gray',
				'color' => '#eee',
			],
		] );
	}
endif;
add_action( 'after_setup_theme', 'gutenbergtheme_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function gutenbergtheme_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'gutenbergtheme_content_width', 640 );
}
add_action( 'after_setup_theme', 'gutenbergtheme_content_width', 0 );

/**
 * Register Google Fonts
 */
function gutenbergtheme_fonts_url() {
    $fonts_url = '';

    /* Translators: If there are characters in your language that are not
	 * supported by Karla, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$notoserif = esc_html_x( 'on', 'Noto Serif font: on or off', 'gutenbergtheme' );

	if ( 'off' !== $notoserif ) {
		$font_families = array();
		$font_families[] = 'Noto Serif:400,400italic,700,700italic';

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return $fonts_url;

}

/**
 * Enqueue scripts and styles.
 */
function gutenbergtheme_scripts() {
	wp_enqueue_style( 'gutenbergtheme-style', get_stylesheet_uri(), [], 10 );

	wp_enqueue_style( 'gutenbergthemeblocks-style', get_template_directory_uri() . '/blocks.css');

	wp_enqueue_style( 'gutenbergtheme-fonts', gutenbergtheme_fonts_url(), array(), null );

	wp_enqueue_script( 'gutenbergtheme-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20181209', true );

	wp_enqueue_script( 'gutenbergtheme-prism', get_template_directory_uri() . '/js/prism.js', array(), '1.15.0' );

	wp_enqueue_script( 'gutenbergtheme-handbook', get_template_directory_uri() . '/js/handbook.js', array( 'jquery' ), null );

	wp_enqueue_script( 'gutenbergtheme-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'gutenbergtheme_scripts' );

function gutenbergtheme_adjacent_post_order( $order_by, $post, $order ) {
	if ( 'handbook' !== $post->post_type ) {
		return $order_by;
	}

	return "ORDER BY p.menu_order $order LIMIT 1";
}
add_filter( 'get_previous_post_sort', 'gutenbergtheme_adjacent_post_order', 10, 3 );
add_filter( 'get_next_post_sort', 'gutenbergtheme_adjacent_post_order', 10, 3 );

function gutenbergtheme_previous_post_where( $where, $in_same_term, $excluded_term, $taxonomy, $post ) {
	if ( 'handbook' !== $post->post_type ) {
		return $where;
	}

	return "WHERE p.post_type='handbook' AND p.post_status='publish' AND p.menu_order < {$post->menu_order}";
}
add_filter( 'get_previous_post_where', 'gutenbergtheme_previous_post_where', 10, 5 );

function gutenbergtheme_next_post_where( $where, $in_same_term, $excluded_term, $taxonomy, $post ) {
	if ( 'handbook' !== $post->post_type ) {
		return $where;
	}

	return "WHERE p.post_type='handbook' AND p.post_status='publish' AND p.menu_order > {$post->menu_order}";
}
add_filter( 'get_next_post_where', 'gutenbergtheme_next_post_where', 10, 5 );

/**
 * Add redirects for any handbook pages that have been renamed.
 */
function gutenbergtheme_handbook_redirects() {
	if ( ! is_404() || ! get_query_var( 'handbook' ) ) {
		return;
	}

	// Any handbook pages where the filename changes should be listed here.
	// If only the parent path changes, it's not needed and WordPress will handle it automatically.
	$redirects = [
		'reference/deprecated'           => 'designers-developers/developers/backwards-compatibility/deprecations/',
		'extensibility/annotations'      => 'designers-developers/developers/block-api/block-annotations/',
		'block-api/attributes'           => 'designers-developers/developers/block-api/block-attributes/',
		'block-api/deprecated-blocks'    => 'designers-developers/developers/block-api/block-deprecation/',
		'block-api'                      => 'designers-developers/developers/block-api/block-registration/',
		'templates'                      => 'designers-developers/developers/block-api/block-templates/',
		'extensibility/autocomplete'     => 'designers-developers/developers/filters/autocomplete-filters/',
		'extensibility/extending-blocks' => 'designers-developers/developers/filters/block-filters/',
		'extensibility/parser'           => 'designers-developers/developers/filters/parser-filters/',
		'blocks'                         => 'designers-developers/developers/tutorials/block-tutorial/intro/',
		'language'                       => 'designers-developers/key-concepts/',

		// Redirects for index pages where needed
		// These next two look like an infinite redirect, but one will exist so the `is_404()` check prevents it above. This is for an inpending changes
		'designers-developers/developers/tutorials/block-tutorial'       => 'designers-developers/developers/tutorials/block-tutorial/intro/',
		'designers-developers/developers/tutorials/block-tutorial/intro' => 'designers-developers/developers/tutorials/block-tutorial/',
	];

	if ( isset( $redirects[ get_query_var( 'handbook' ) ] ) ) {
		wp_redirect(
			home_url(
				'/handbook/' .
				$redirects[ get_query_var( 'handbook' ) ]
			)
		);
		exit;
	}
}
add_action( 'template_redirect', 'gutenbergtheme_handbook_redirects' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Gutenberg documentation
 */
require __DIR__ . '/inc/docs-importer.php';
require __DIR__ . '/inc/class-gutenberg-handbook.php';

add_action( 'load-post.php', array( 'Import_Gutendocs', 'action_load_post_php' ) );
add_action( 'edit_form_after_title', array( 'Import_Gutendocs', 'action_edit_form_after_title' ) );
add_action( 'save_post', array( 'Import_Gutendocs', 'action_save_post' ) );
add_filter( 'cron_schedules', array( 'Import_Gutendocs', 'filter_cron_schedules' ) );
add_action( 'init', array( 'Import_Gutendocs', 'action_init' ) );
add_action( 'wporg_gutenberg_manifest_import', array( 'Import_Gutendocs', 'action_wporg_gutenberg_manifest_import' ) );
add_action( 'wporg_gutenberg_markdown_import', array( 'Import_Gutendocs', 'action_wporg_gutenberg_markdown_import' ) );

add_filter( 'the_title', array( 'Gutenberg_Handbook', 'filter_the_title_edit_link' ), 10, 2 );
add_filter( 'get_edit_post_link', array( 'Gutenberg_Handbook', 'redirect_edit_link_to_github' ), 10, 3 );
add_filter( 'o2_filter_post_actions', array( 'Gutenberg_Handbook', 'redirect_o2_edit_link_to_github' ), 11, 2 );

add_filter( 'handbook_display_toc', '__return_false' );
