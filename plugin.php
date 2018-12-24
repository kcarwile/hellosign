<?php
/**
 * Plugin Name: HelloSign WP
 * Plugin URI: 
 * Description: Insert document signing into your site workflows.
 * Author: Kevin Carwile
 * Author URI: 
 * Version: 0.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Load Only Once */
if ( class_exists( 'HelloSignWPPlugin' ) ) {
	return;
}

/* Autoloaders */
include_once 'includes/plugin-bootstrap.php';

use HelloSign\WP\Models;

/**
 * This plugin uses the MWP Application Framework to init.
 *
 * @return void
 */
add_action( 'mwp_framework_init', function() 
{
	/* Framework */
	$framework = MWP\Framework\Framework::instance();
	
	/**
	 * Plugin Core 
	 *
	 * Grab the main plugin instance and attach its annotated
	 * callbacks to WordPress core.
	 */
	$plugin	= HelloSign\WP\Plugin::instance();
	$framework->attach( $plugin );
	
	/**
	 * Plugin Settings 
	 *
	 * Register a settings storage to the plugin which can be
	 * used to get/set/save settings to the wp_options table.
	 */
	$settings = HelloSign\WP\Settings::instance();
	$plugin->addSettings( $settings );
	
	/* Register settings to a WP Admin page */
	$framework->attach( $settings );
	
	Models\SignatureRequest::createController('admin', [
		'adminPage' => [
			'menu' => 'HelloSign',
			'title' => 'HelloSign Signature Requests',
			'menu_submenu' => 'Signature Requests',
			'type' => 'menu',
		],
		'tableConfig' => [
			'columns' => [
				'title' => __( 'Title', 'hellosign' ),
				'request_id' => __( 'Request ID', 'hellosign' ),
				'type' => __( 'Request Type', 'hellosign' ),
			],
		],
	]);
	
} );
