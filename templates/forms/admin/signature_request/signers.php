<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 26, 2018
 *
 * @package  HelloSign WP
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	array		$signers	A list of signers
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<p>You are about to create signature requests for the following signers:</p>
<ul style="list-style: disc; padding: 20px; margin-bottom: 50px;">
	<li><?php echo implode( '</li><li>', array_map( function($s) { return $s['name'] . ' (' . $s['email'] . ')'; }, $signers ) ) ?></li>
</ul>
