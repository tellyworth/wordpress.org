<?php

namespace WordPressdotorg\Plugin_Directory\Tests;

if ( 'cli' !== php_sapi_name() ) {
	return;
}

// WP_TESTS_DOMAIN, WP_TESTS_EMAIL, WP_TESTS_TITLE, WP_PHP_BINARY

define( 'WP_TESTS_DOMAIN', 'localhost:8881' ); // FIXME: can we fetch this dynamically?
define( 'WP_TESTS_EMAIL', '' );
define( 'WP_TESTS_TITLE', 'WordPress Plugin Directory Unit Tests' );
define( 'WP_PHP_BINARY', 'php' );

// load WordPress if we're running within wp-now.
if ( false !== strpos( $_SERVER['_'], '@wp-now/wp-now' ) ) {
	require_once '/var/www/html/wp-load.php';
}

require_once( dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php' );


/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
	require_once dirname( __FILE__ ) . '/../plugin-directory.php';
}

\add_filter( 'muplugins_loaded', __NAMESPACE__ . '\manually_load_plugin' );
