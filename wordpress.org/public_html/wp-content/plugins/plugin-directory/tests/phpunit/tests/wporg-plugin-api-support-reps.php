<?php

use WordPressdotorg\Plugin_Directory\Tools;
use WordPressdotorg\Plugin_Directory\Plugin_Directory;

/**
 * @group new
 */
class TestPluginApiSupportReps extends WP_Test_REST_Controller_Testcase {

	public static $plugin_id;
	public static $plugin_slug;
	public static $support_rep_id;
	public static $other_user_id;
	public static $plugin_author_id;
	public static $admin_user_id;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {

		self::$plugin_author_id = $factory->user->create();
		self::$plugin_slug = 'test-plugin';
		self::$plugin_id  = $factory->post->create(
			array(
				'post_type' => 'plugin',
				'post_modified' => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql' ),
				'post_name' => self::$plugin_slug,
				'post_status' => 'publish',
				'post_author' => self::$plugin_author_id,
			)
		);

		self::$support_rep_id = $factory->user->create();
		Tools::add_plugin_support_rep( self::$plugin_id, self::$support_rep_id );

		self::$other_user_id = $factory->user->create();

		self::$admin_user_id = $factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function test_register_routes() {
		// Implement test_register_routes
		$routes = rest_get_server()->get_routes();
		// 'plugins/v1', '/plugin/(?P<plugin_slug>[^/]+)/support-reps/?'
		$this->assertArrayHasKey( '/plugins/v1/plugin/(?P<plugin_slug>[^/]+)/support-reps/?', $routes );
		$this->assertArrayHasKey( '/plugins/v1/plugin/(?P<plugin_slug>[^/]+)/support-reps/(?P<support_rep>[^/]+)/?', $routes );

	}

	public function test_context_param() {
		// Nothing to tost here?
	}

	public function test_get_items() {
		// Must be an admin to do this?
		wp_set_current_user( self::$admin_user_id );
		// Implement test_get_items
		$request  = new WP_REST_Request( 'GET', '/plugins/v1/plugin/' . self::$plugin_slug . '/support-reps' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$support_rep = get_user_by( 'ID', self::$support_rep_id );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 1, count( $data ) );
		$this->assertEquals( $support_rep->user_nicename, $data[0]['nicename'] );
		$this->assertEquals( $support_rep->user_email, $data[0]['email'] );
		$this->assertEquals( $support_rep->display_name, $data[0]['name'] );
	}

	public function test_get_item() {
		// Implement test_get_item
	}

	public function test_create_item() {
		// Implement test_create_item
	}

	public function test_update_item() {
		// Implement test_update_item
	}

	public function test_delete_item() {
		// Implement test_delete_item
	}

	public function test_prepare_item() {
		// Implement test_prepare_item
	}

	public function test_get_item_schema() {
		// Implement test_get_item_schema
	}


}