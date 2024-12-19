<?php

use WordPressdotorg\Plugin_Directory\Tools;

/**
 * @group new
 */
class TestPluginApiSupportReps extends WP_Test_REST_Controller_Testcase {

	public static $plugin_id;
	public static $plugin_slug;
	public static $support_rep;
	public static $other_user;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {

		self::$plugin_slug = 'test-plugin';
		self::$plugin_id  = $factory->post->create(
			array(
				'post_type' => 'plugin',
				'post_modified' => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql' ),
				'post_name' => self::$plugin_slug,
			)
		);

		self::$support_rep = $factory->user->create();
		Tools::add_plugin_support_rep( self::$plugin_id, self::$support_rep );

		self::$other_user = $factory->user->create();
	}

	public function test_register_routes() {
		// Implement test_register_routes
		$routes = rest_get_server()->get_routes();
		// 'plugins/v1', '/plugin/(?P<plugin_slug>[^/]+)/support-reps/?'
		$this->assertArrayHasKey( '/plugins/v1/plugin/(?P<plugin_slug>[^/]+)/support-reps/?', $routes );
		$this->assertArrayHasKey( '/plugins/v1/plugin/(?P<plugin_slug>[^/]+)/support-reps/(?P<support_rep>[^/]+)/?', $routes );

	}

	public function test_context_param() {
		// Collection.
		$request  = new WP_REST_Request( 'OPTIONS', '/plugins/v1/plugin/' . self::$plugin_slug . '/support-reps' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		// TODO: what are we supposed to test here?

		$this->markTestSkipped();
	}

	public function test_get_items() {
		// Implement test_get_items
		$request  = new WP_REST_Request( 'GET', '/plugins/v1/plugin/' . self::$plugin_slug . '/support-reps' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( self::$support_rep, $data[0]['id'] );
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