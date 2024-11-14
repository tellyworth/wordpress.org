<?php
namespace WordPressdotorg\Plugin_Directory;

/**
 * The Plugin Release class encapsulates the plugin release CPT and related code.
 * Used for storing and interacting with plugin releases; ie versions of a plugin that are made available for download.
 *
 * @package WordPressdotorg\Plugin_Directory
 */
class Plugin_Release {
	/**
	 * Fetch the instance of the Plugin_Release class.
	 *
	 * @static
	 */
	public static function instance() {
		static $instance = null;

		return ! is_null( $instance ) ? $instance : $instance = new Plugin_Release();
	}

	/**
	 * Plugin_Release constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize the Plugin_Release class.
	 */
	public function init() {
		register_post_type( 'plugin_release', array(
			'labels'              => array(
				'name'          => __( 'Releases', 'wporg-plugins' ),
				'singular_name' => __( 'Release', 'wporg-plugins' ),
			),
			'public'              => false,
			'show_ui'             => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_in_rest'        => true, // FIXME: maybe?
			'supports'            => array( 'title', 'editor' ), // TBD
			'rewrite'             => false,
			'query_var'           => false,
			'hierarchical'        => false, // Disappointingly, this doesn't help us make a Post -> Release hierarchy.
		) );
	}

	// Starting point for an internal API, mostly copilot-generated.

	/**
	 * Get all releases for a plugin.
	 */
	public function get_releases( $plugin ) {
		$plugin_id = ( get_post( $plugin ) )->ID;

		$releases = get_posts( array(
			'post_type'      => 'plugin_release',
			'posts_per_page' => -1,
			'post_parent'    => $plugin_id,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		return $releases;
	}

	/**
	 * Add release info for a plugin.
	 */
	public function add_release( $plugin, $release ) {
		$plugin_id = ( get_post( $plugin ) )->ID;

		// Make sure we don't accidentally add junk from a sandbox while tinkering.
		die( "Not yet ready for use" );

		$release_id = wp_insert_post( array(
			'post_type'   => 'plugin_release',
			'post_title'  => $release['version'],
			'post_parent' => $plugin_id,
			'post_status' => 'publish',
		) );

		if ( $release_id ) {
			update_post_meta( $release_id, 'release_svn_revision', $release['revision'] );
		}

		return $release_id;
	}

	/**
	 * Get a specific plugin release.
	 */
	public function get_release( $plugin, $version ) {
		$plugin_id = ( get_post( $plugin ) )->ID;

		$release = get_posts( array(
			'post_type'      => 'plugin_release',
			'posts_per_page' => 1,
			'post_parent'    => $plugin_id,
			'post_title'     => $version,
		) );

		return $release ? $release[0] : null;
	}

}