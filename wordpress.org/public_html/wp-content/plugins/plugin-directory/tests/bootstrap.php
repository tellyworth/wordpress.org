<?php

namespace WordPressdotorg\Plugin_Directory\Tests;

if ( 'cli' !== php_sapi_name() ) {
	return;
}

// load WordPress if we're running within wp-now.
if ( false !== strpos( $_SERVER['_'], '@wp-now/wp-now' ) ) {
	require_once '/var/www/html/wp-load.php';
}

/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
	require_once dirname( __FILE__ ) . '/../plugin-directory.php';
}

\add_filter( 'muplugins_loaded', __NAMESPACE__ . '\manually_load_plugin' );
