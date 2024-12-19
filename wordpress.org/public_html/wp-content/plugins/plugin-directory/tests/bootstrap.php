<?php

namespace WordPressdotorg\Plugin_Directory\Tests;

if ( 'cli' !== php_sapi_name() ) {
	return;
}

// Required by wp-phpunit.
define( 'WP_TESTS_DOMAIN', 'localhost:8881' ); // FIXME: can we fetch this dynamically?
define( 'WP_TESTS_EMAIL', '' );
define( 'WP_TESTS_TITLE', 'WordPress Plugin Directory Unit Tests' );
define( 'WP_PHP_BINARY', 'php' );
putenv( 'WP_TESTS_SKIP_INSTALL=1' );

// Required by plugin-directory
define( 'PLUGINS_TABLE_PREFIX', 'wporg_unit_tests_' );

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


// Set up some custom tables needed for tests
// FIXME: Find a better place for this.
global $wpdb;
if ( !$wpdb->get_row( "SHOW TABLES LIKE '" . PLUGINS_TABLE_PREFIX . "svn_access'" ) ) {
	$wpdb->query( "CREATE TABLE IF NOT EXISTS " . PLUGINS_TABLE_PREFIX . "svn_access (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`path` varchar(100) NOT NULL,
		`user` varchar(100) NOT NULL,
		`access` varchar(10) NOT NULL,
		PRIMARY KEY (`id`),
		KEY `path` (`path`),
		KEY `user` (`user`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" );
}
