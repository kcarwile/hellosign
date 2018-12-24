<?php
/**
 * SignatureRequest Model [ActiveRecord]
 *
 * Created:   December 24, 2018
 *
 * @package:  HelloSign WP
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace HelloSign\WP\Models;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\ActiveRecord;

/**
 * SignatureRequest Class
 */
class _SignatureRequest extends ActiveRecord
{
	/**
	 * @var	array		Multitons cache (needs to be defined in subclasses also)
	 */
	protected static $multitons = array();
	
	/**
	 * @var	string		Table name
	 */
	protected static $table = 'hellosign_requests';
	
	/**
	 * @var	array		Table columns
	 */
	protected static $columns = array(
		'id',
		'title' => [ 'type' => 'varchar', 'length' => 255 ],
		'request_id' => [ 'type' => 'varchar', 'length' => 255 ],
		'data' => [ 'type' => 'text', 'format' => 'JSON', 'create' => false, 'edit' => false ],
		'type' => [ 'type' => 'enum', 'values' => [ 'standard', 'template', 'embedded' ] ],
	);
	
	/**
	 * @var	string		Table primary key
	 */
	protected static $key = 'id';
	
	/**
	 * @var	string		Table column prefix
	 */
	protected static $prefix = '';
	
	/**
	 * @var bool		Site specific table? (for multisites)
	 */
	protected static $site_specific = TRUE;
	
	/**
	 * @var	string
	 */
	protected static $plugin_class = 'HelloSign\WP\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Request';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Requests';
	
	/**
	 * @var	string
	 */
	public static $lang_view = 'View';

	/**
	 * @var	string
	 */
	public static $lang_create = 'Create';

	/**
	 * @var	string
	 */
	public static $lang_edit = 'Edit';
	
	/**
	 * @var	string
	 */
	public static $lang_delete = 'Delete';

}
