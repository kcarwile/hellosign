<?php
/**
 * Plugin HTML Template
 *
 * Created:  May 4, 2018
 *
 * @package  MWP Application Framework
 * @author   Kevin Carwile
 * @since    2.0.0
 *
 * @param	string												$title			The provided title
 * @param	MWP\Framework\Plugin								$plugin			The plugin associated with the active records/view
 * @param	MWP\Framework\Helpers\ActiveRecordController		$controller		The associated controller displaying this view
 * @param	MWP\Framework\Pattern\ActiveRecordController		$record			The active record to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

if ( $record instanceof HelloSign\WP\Models\SignatureRequest ) {
	echo $this->getTemplateContent( 'requests/' . $record->type, [ 'title' => $title, 'plugin' => $plugin, 'controller' => $controller, 'record' => $record ] );
}

echo MWP\Framework\Framework::instance()->getTemplateContent( 'views/management/records/view', [ 'title' => $title, 'plugin' => $plugin, 'controller' => $controller, 'record' => $record ] );

?>