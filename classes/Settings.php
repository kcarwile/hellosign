<?php
/**
 * Settings Class File
 *
 * @vendor: MWP Application Framework
 * @package: HelloSign WP
 * @author: Kevin Carwile
 * @link: 
 * @since: December 23, 2018
 */
namespace HelloSign\WP;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Plugin Settings
 *
 * @MWP\WordPress\Options
 * @MWP\WordPress\Options\Section( title="HelloSign API Settings" )
 * @MWP\WordPress\Options\Field( name="api_key", type="text", title="API Key", default="" )
 * @MWP\WordPress\Options\Field( name="client_id", type="text", title="Client ID", default="" )
 * @MWP\WordPress\Options\Field( name="test_mode", type="checkbox", title="Test Mode", description="Use api test mode", default="1" )
 */
class Settings extends \MWP\Framework\Plugin\Settings
{
	/**
	 * Instance Cache - Required for singleton
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var string	Settings Access Key ( default: main )
	 */
	public $key = 'main';
	
}