<?php
/**
 * SignatureRequestController [Controller]
 *
 * Created:   December 24, 2018
 *
 * @package:  HelloSign WP
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace HelloSign\WP\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Helpers\ActiveRecordController;

/**
 * SignatureRequestController Class
 */
class _SignatureRequestController extends ActiveRecordController
{
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		wp_enqueue_script( 'hellosign' );
	}

}
