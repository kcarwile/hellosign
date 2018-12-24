<?php


/* Load framework for tests */
if ( defined( 'DIR_TESTDATA' ) ) {
	$plugin_dir = dirname( dirname( __FILE__ ) );
	if ( ! file_exists( $plugin_dir . '/mwp-framework/plugin.php' ) ) {
		die( 'Error: MWP Framework must be present in ' . $plugin_dir . '/mwp-framework to run tests on this plugin.' );
	}
	
	require_once $plugin_dir . '/mwp-framework/plugin.php';
}

require_once 'plugin.php';