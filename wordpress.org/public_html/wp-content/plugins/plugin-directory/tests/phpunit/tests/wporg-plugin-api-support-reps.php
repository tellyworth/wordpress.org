<?php

/**
 * @group new
 */
class TestPluginApiSupportReps extends WP_Test_REST_Controller_Testcase {

	public static $plugin_id;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {

		self::$plugin_id  = $factory->post->create(
			array(
				'post_type' => 'plugin',
				'post_modified' => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql' ),
			)
		);
	}
	public function test_register_routes() {
		// Implement test_register_routes
		$routes = rest_get_server()->get_routes();
		// 'plugins/v1', '/plugin/(?P<plugin_slug>[^/]+)/support-reps/?'
		$this->assertArrayHasKey( '/plugins/v1/plugin/(?P<plugin_slug>[^/]+)/support-reps/?', $routes );
		$this->assertArrayHasKey( '/plugins/v1/plugin/(?P<plugin_slug>[^/]+)/support-reps/(?P<support_rep>[^/]+)/?', $routes );

	}

	public function test_context_param() {
		// Implement test_context_param
	}

	public function test_get_items() {
		// Implement test_get_items
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