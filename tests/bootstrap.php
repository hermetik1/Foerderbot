<?php
/**
 * PHPUnit test bootstrap file.
 *
 * @package KI_Kraft
 */

// Load WordPress test environment
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php\n";
	exit( 1 );
}

// Load test functions
require_once $_tests_dir . '/includes/functions.php';

/**
 * Load plugin for testing.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/ki-kraft.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start the WordPress test environment
require $_tests_dir . '/includes/bootstrap.php';
