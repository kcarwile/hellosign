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
use HelloSign\BaseException;

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
		'type' => [ 'type' => 'text', 'length' => 12 ],
		'created' => [ 'type' => 'int', 'length' => 11 ],
		'options' => [ 'type' => 'text', 'format' => 'JSON' ],
		'request_id' => [ 'type' => 'varchar', 'length' => 255 ],
		'request_data' => [ 'type' => 'text', 'format' => 'JSON' ],
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
	 * Constructor
	 *
	 * @param	array			$options			The signature request options
	 * @return	void
	 */
	public function __construct( $options=[] )
	{
		$this->options = $options;
	}
	
	/**
	 * Finalize the signature request
	 * 
	 * @return	HelloSign\SignatureRequest
	 * @throws	HelloSign\BaseException
	 */
	public function hellosign()
	{
		if ( ! $this->request_id ) {
			if ( $this->type == 'embedded' ) {
				$signature_request = $this->getPlugin()->createEmbeddedSignatureRequest( $this->options );
			} else {
				$signature_request = $this->getPlugin()->sendSignatureRequest( $this->options );
			}
			return $this->saveRequestDetails( $signature_request );
		} else {
			return $this->getHelloSignRequest();
		}
	}
	
	/**
	 * Embed the request for signing inline
	 * 
	 * @return	HelloSign\SignatureRequest
	 * @throws	HelloSign\BaseException
	 */
	public function getEmbedUrl( $sig=0 )
	{
		$request = $this->hellosign();
		
		foreach( $request->getSignatures() as $index => $signature ) {
			if ( in_array( $sig, array( $index, $signature->getId(), $signature->getSignerEmail() ) ) ) {
				return $this->getPlugin()->getClient()->getEmbeddedSignUrl( $signature->getId() )->getSignUrl();
			}
		}
	}
	
	/**
	 * Save a signature request response object
	 *
	 * @param	HelloSign\SignatureRequest		$request			The HelloSign signature request
	 * @return	HelloSign\SignatureRequest
	 */
	public function saveRequestDetails( $request )
	{
		$this->request_id = $request->getId();
		$this->request_data = $request->toArray();
		$this->save();
		
		return $request;
	}
	
	/**
	 * Get the signature request
	 *
	 * @return	HelloSign\SignatureRequest
	 * @throws	HelloSign\BaseException
	 */
	public function getHelloSignRequest()
	{
		if ( $this->request_id ) {
			return $this->getPlugin()->getClient()->getSignatureRequest( $this->request_id );
		}
	}
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		$actions = array(
			'send' => array(
				'title' => __( 'Request Signatures Now', 'hellosign' ),
				'icon' => 'glyphicon glyphicon-thumbs-up',
				'params' => array(
					'do' => 'edit',
					'edit' => 'send',
					'id' => $this->id(),
				),
			),
			'reminder' => array(
				'title' => __( 'Send Reminders', 'hellosign' ),
				'icon' => 'glyphicon glyphicon-repeat',
				'params' => array(
					'do' => 'edit',
					'edit' => 'reminder',
					'id' => $this->id(),
				),
			),
			'edit' => array(
				'title' => $this->_getEditTitle(),
				'icon' => 'glyphicon glyphicon-pencil',
				'params' => array(
					'do' => 'edit',
					'id' => $this->id(),
				),
			),
			'view' => array(
				'title' => __( 'View Request Details', 'hellosign' ),
				'icon' => 'glyphicon glyphicon-eye-open',
				'params' => array(
					'do' => 'view',
					'id' => $this->id(),
				),
			),
			'delete' => array(
				'separator' => true,
				'title' => __( 'Cancel Request', 'hellosign' ),
				'icon' => 'glyphicon glyphicon-trash',
				'attr' => array( 
					'class' => 'text-danger',
				),
				'params' => array(
					'do' => 'delete',
					'id' => $this->id(),
				),
			)
		);
		
		if ( $this->request_id ) {
			unset( $actions['send'] );
			unset( $actions['embed'] );
			unset( $actions['edit'] );
		} else {
			unset( $actions['reminder'] );
		}
		
		if ( $this->request_data['is_complete'] ) {
			unset( $actions['reminder'] );
			unset( $actions['delete'] );
		}
		
		if ( $this->type == 'embedded' ) {
			unset( $actions['reminder'] );
		}
		
		return $actions;
	}
	
	/**
	 * Build the record editing form
	 * 
	 * @return	MWP\Framework\Helpers\Form
	 */
	public function buildEditForm()
	{
		$form = static::createForm( 'edit' );
		$templates = $this->getPlugin()->getTemplates();
		
		$form->addField( 'type', 'choice', [
			'label' => 'Signature Request Method',
			'data' => $this->type,
			'required' => true,
			'choices' => array(
				'Email' => 'standard',
				'Embedded Webpage' => 'embedded',
			),
		]);
		
		$form->addField( 'template_id', 'choice', [
			'row_attr' => [ 'id' => 'template_id' ],
			'label' => 'Choose Template',
			'expanded' => true,
			'data' => isset( $this->options['template_id'] ) ? $this->options['template_id'] : '',
			'choices' => array_merge( [ 'None (Ad-Hoc)' => '' ], array_combine( array_column( $templates, 'title' ), array_column( $templates, 'template_id' ) ) ),
		]);
		
		$form->addField( 'title', 'text', [
			'label' => __( 'Title', 'hellosign' ),
			'required' => true,
			'data' => $this->options['title'],
		]);
		
		$form->addField( 'subject', 'text', [
			'label' => __( 'Subject', 'hellosign' ),
			'required' => true,
			'data' => $this->options['subject'],
		]);
		
		$form->addField( 'message', 'textarea', [
			'label' => __( 'Message', 'hellosign' ),
			'data' => $this->options['message'],
		]);
		
		$form->addHtml( 'hr_files', '<hr>' );
		$form->addField( 'files', 'collection', [
			'label' => __( 'Files', 'hellosign' ),
			'allow_add' => true,
			'allow_delete' => true,
			'required' => true,
			'data' => $this->options['files'],
			'entry_options' => [
				'label' => 'File Details',
				'fields' => array(
					array( 
						'name' => 'file',
						'type' => 'text',
						'options' => [
							'label' => __( 'File To Sign', 'hellosign' ),
							'required' => true,
							'attr' => [ 'placeholder' => 'Input file path or http url' ],
						],
					),
				),
			],
		]);

		$form->addHtml( 'hr_signers', '<hr>' );
		$form->addField( 'signers', 'collection', [
			'label' => __( 'Signers', 'hellosign' ),
			'allow_add' => true,
			'allow_delete' => true,
			'required' => true,
			'data' => $this->options['signers'],
			'entry_options' => [
				'label' => 'Signer Details',
				'fields' => array(
					array( 
						'name' => 'email',
						'type' => 'text',
						'options' => [
							'label' => __( 'Email Address', 'hellosign' ),
							'constraints' => [ 'Email', 'NotBlank' ],
							'required' => true,
						],
					),
					array( 
						'name' => 'name',
						'type' => 'text',
						'options' => [
							'label' => __( 'Name', 'hellosign' ),
							'required' => true,
						],
					),
				),
			],
		]);
		
		$form->addField( 'submit', 'submit', [
			'label' => __( 'Save' ),
		], '');
		
		return $form;
	}
	
	/**
	 * Process the edit form
	 * 
	 * @param	array			$values			The form values
	 * @return	void
	 */
	public function processEditForm( $values )
	{
		foreach( array_keys( $values ) as $key ) {
			if ( substr( $key, 0, 3 ) == 'hr_' ) {
				unset( $values[ $key ] );
			}
		}
		
		if ( isset( $values['type'] ) ) {
			$this->type = $values['type'];
			unset( $values['type'] );
		}
		
		$this->options = $values;
		$this->save();
	}
	
	/**
	 * Send signature requests
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildSendForm()
	{
		$form = static::createForm( 'send', array( 'attr' => array( 'class' => 'container', 'style' => 'max-width: 600px; margin: 75px auto;' ) ) );
		
		$form->addHtml( 'signers_info', $this->getPlugin()->getTemplateContent( 'forms/admin/signature_request/signers', [ 'request' => $this, 'signers' => $this->options['signers'] ] ) );
		
		$form->addField( 'confirm', 'submit', array( 
			'label' => __( 'Send Signature Requests', 'mwp-framework' ), 
			'attr' => array( 'class' => 'btn btn-success' ),
			'row_attr' => array( 'class' => 'col-xs-6 text-center' ),
		));
		
		return $form;
	}
	
	/**
	 * Send the signature request
	 * 
	 * @param	array			$values			The form values
	 * @return	void
	 */
	public function processSendForm( $values )
	{
		$this->hellosign();
	}

	/**
	 * Remind signers
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildReminderForm()
	{
		$form = static::createForm( 'reminder', array( 'attr' => array( 'class' => 'container', 'style' => 'max-width: 600px; margin: 75px auto;' ) ) );
		
		$request_data = $this->request_data;
		$signers = [];
		
		foreach( $request_data['signatures'] as $signature ) {
			if ( $signature['status_code'] == 'awaiting_signature' ) {
				$signers[ $signature['signer_name'] . ' (' . $signature['signer_email_address'] . ')' ] = $signature['signer_email_address'];
			}
		}
		
		$form->addField( 'signers', 'choice', [
			'label' => 'Signers to Remind',
			'multiple' => true,
			'required' => true,
			'expanded' => true,
			'choices' => $signers,
		]);
		
		$form->addField( 'confirm', 'submit', array( 
			'label' => __( 'Send Reminders', 'mwp-framework' ), 
			'attr' => array( 'class' => 'btn btn-success' ),
			'row_attr' => array( 'class' => 'col-xs-6 text-left' ),
		));
		
		return $form;
	}
	
	/**
	 * Process the reminder form
	 * 
	 * @param	array			$values			The form values
	 * @return	void
	 */
	public function processReminderForm( $values )
	{
		if ( isset( $values['signers'] ) ) {
			foreach( $values['signers'] as $signer_email ) {
				$this->getPlugin()->getClient()->requestEmailReminder( $this->request_id, $signer_email );
			}
		}
	}
	
	/**
	 * Save a record
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		if ( ! $this->created ) {
			$this->created = time();
		}
		
		return parent::save();
	}
	
	/**
	 * Delete a record
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		if ( $this->request_id ) {
			if ( ! $this->request_data['is_complete'] ) {
				$this->getPlugin()->getClient()->cancelSignatureRequest( $this->request_id );
			}
		}
		
		return parent::delete();
	}
	
	/**
	 * Perform a bulk action on records
	 *
	 * @param	string			$action					The action to perform
	 * @param	array			$records				The records to perform the bulk action on
	 */
	public static function processBulkAction( $action, array $records )
	{
		switch( $action ) {
			case 'update':
				foreach( $records as $record ) {
					if ( $record->request_id ) {
						$record->saveRequestDetails( $record->getHelloSignRequest() );
					}
				}
				break;
			default:
				parent::processBulkAction( $action, $records );
		}
	}
		
}
