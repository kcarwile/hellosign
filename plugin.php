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
use HelloSign\WP\Controllers;

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
	
	Models\SignatureRequest::setControllerClass( Controllers\SignatureRequestController::class );
	Models\SignatureRequest::createController('default', [
		'adminPage' => [
			'menu' => 'HelloSign',
			'title' => 'HelloSign Signature Requests',
			'menu_submenu' => 'Signature Requests',
			'type' => 'menu',
			'icon' => $plugin->fileUrl( 'assets/img/hellosign.png' ),
		],
		'tableConfig' => [
			'bulkActions' => [
				'update' => 'Update Requests'
			],
			'columns' => [
				'title' => __( 'Title', 'hellosign' ),
				'status' => __( 'Request Status', 'hellosign' ),
				'type' => __( 'Signing Method', 'hellosign' ),
			],
			'handlers' => [
				'title' => function( $row ) {
					$request = Models\SignatureRequest::load( $row['id'] );
					return $request->options['title'] ?: $request->title;
				},
				'status' => function( $row ) {
					$request = Models\SignatureRequest::load( $row['id'] );
					if ( ! $request->request_id ) {
						return 'Draft';
					}
					
					$signers = $request->request_data['signatures'] ?: array();
					$signed_count = array_filter( $signers, function($s) { return $s['status_code'] == 'signed'; } );
					return ( $request->request_data['is_complete'] ? 'Complete' : 'In Progress' ) . " - " . count( $signed_count ) . " of " . count( $signers ) . " signed";
				},
				'type' => function( $row ) {
					return ( $row['type'] == 'embedded' ? 'By Embedded Page' : 'By Email' );
				},
			],
		],
	]);
	
} );
