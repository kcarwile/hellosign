<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 27, 2018
 *
 * @package  HelloSign WP
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'requests/embedded', ... ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="mwp-bootstrap" style="max-width: 1190px; margin: 50px auto;">
	<table class="table table-striped table-bordered" style="background-color: #fff; box-shadow: 2px 2px 8px #ddd; border-radius: 4px;">
		<thead>
			<tr>
				<th>Property</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $record::_getColumns() as $prop => $config ) : 
				if ( ! is_array( $config ) ) {
					$prop = $config;
					$config = [];
				}
				
				$value = $record->$prop;
			?>
			<tr>
				<td><?php echo ( isset( $config['title'] ) and $config['title'] ) ? __( $config['title'] ) : $record::_getPrefix() . $prop ?></td>
				<td><pre><?php $string_value = print_r( $value, true ); echo esc_html( $string_value !== '' ? $string_value : '&nbsp;' ) ?></pre></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>