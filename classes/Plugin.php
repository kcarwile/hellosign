<?php
/**
 * Plugin Class File
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

use HelloSign\Client;
use HelloSign\SignatureRequest;
use HelloSign\TemplateSignatureRequest;
use HelloSign\EmbeddedSignatureRequest;
use HelloSign\AbstractSignatureRequest;

/**
 * Plugin Class
 */
class Plugin extends \MWP\Framework\Plugin
{
	/**
	 * Instance Cache - Required
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var	Client
	 */
	protected $_client;
	
	/**
	 * @var string		Plugin Name
	 */
	public $name = 'HelloSign WP';
	
	/**
	 * WordPress Initialize
	 *
	 * @MWP\WordPress\Action( for="init" )
	 */
	public function initialize()
	{
		wp_register_script( 'hellosign', 'https://s3.amazonaws.com/cdn.hellosign.com/public/js/hellosign-embedded.LATEST.min.js' );
	}
	
	/**
	 * Get the signing api client
	 * 
	 * @return	Client
	 */
	public function getClient()
	{
		if ( isset( $this->_client ) ) {
			return $this->_client;
		}
		
		$this->_client = new Client( $this->getSetting('api_key') );
		return $this->_client;
	}
	
	/**
	 * Create and send a signature request according to provided options
	 * 
	 * @param	array					$options			Request configuration options
	 * @return	SignatureRequest
	 * @throws	HelloSign\BaseException
	 */
	public function sendSignatureRequest( $options )
	{
		return $this->sendRequest( $this->createRequest( $options ) );
	}
	
	/**
	 * Get a signature request status
	 *
	 * @param	string			$request_id				The signature request id to get
	 * @return	SignatureRequest
	 * @throws	HelloSign\BaseException
	 */
	public function getSignatureRequest( $request_id )
	{
		return $this->getClient()->getSignatureRequest( $request_id );
	}
	
	/**
	 * Get templates (with caching)
	 *
	 * @param	bool			$recache			Set to TRUE to force cache refresh
	 * @return	array
	 */
	public function getTemplates( $recache=FALSE )
	{
		$templates = $this->getCache('templates');
		
		if ( $recache or ! $templates ) {
			$_templates = array_map( function( $t ) { return $t->toArray(); }, iterator_to_array( $this->getClient()->getTemplates() ) );
			$templates = array_combine( array_column( $_templates, 'template_id' ), $_templates );
			$this->setCache( 'templates', $templates, FALSE, 60 * 60 );
		}
		
		return $templates;
	}
	
	/**
	 * Create an embeddable signature request
	 *
	 * @param	array					$options			Request configuration options
	 * @return	SignatureRequest
	 * @throws	HelloSign\BaseException
	 */
	public function createEmbeddedSignatureRequest( $options )
	{
		$client = $this->getClient();
		$request = $this->createRequest( $options );
		$embedded_request = new EmbeddedSignatureRequest( $request, $this->getSetting('client_id') );
		
		return $client->createEmbeddedSignatureRequest( $embedded_request );
	}
	
	/**
	 * Create a signature request given some options
	 *
	 * @param	array						$options			Signature request options
	 * @return	AbstractSignatureRequest
	 */
	public function createRequest( $options )
	{
		$request = ( isset( $options['template_id'] ) && $options['template_id'] ) ? new TemplateSignatureRequest : new SignatureRequest;
		
		/* Enable Test Mode */
		if ( $this->getSetting('test_mode') ) {
			$request->enableTestMode();
		}

		return $this->configRequest( $request, $options );
	}
	
	/**
	 * Configure a signature request using an options array
	 *
	 * @param	AbstractSignatureRequest	$request				The signature request
	 * @param	array						$options				Configuration options
	 * @return	AbstractSignatureRequest
	 */
	public function configRequest( $request, $options )
	{
		/* Title */
		if ( isset( $options['title'] ) ) {
			$request->setTitle( $options['title'] );
		}
		
		/* Subject */
		if ( isset( $options['subject'] ) ) {
			$request->setSubject( $options['subject']  );
		}
		
		/* Message */
		if ( isset( $options['message'] ) ) {
			$request->setMessage( $options['message'] );
		}
		
		/* Signers */
		if ( isset( $options['signers'] ) and is_array( $options['signers'] ) ) {
			foreach( $options['signers'] as $signer ) {
				$request->addSigner( $signer['email'], $signer['name'], @$signer['role'] ?: NULL );
			}
		}
		
		/* Files */
		if ( isset( $options['files'] ) and is_array( $options['files'] ) ) {
			foreach( $options['files'] as $file ) {
				if ( filter_var( $file['file'], FILTER_VALIDATE_URL ) ) {
					$request->addFileUrl( $file['file'] );
				} else {
					$request->addFile( $file['file'] );
				}
			}
		}
		
		/* Template Signature Requests */
		if ( $request instanceof TemplateSignatureRequest ) {
			
			// Template ID
			if ( isset( $options['template_id'] ) ) {
				$request->setTemplateId( $options['template_id'] );
			}
			
			// CC Emails
			if ( isset( $options['ccs'] ) ) {
				foreach( $options['ccs'] as $cc ) {
					$request->setCC( $cc['name'], $cc['email'] );
				}
			}
			
			// Custom Fields
			if ( isset( $options['fields'] ) ) {
				foreach( $options['fields'] as $field ) {
					$request->setCustomFieldValue( $field['name'], $field['value'] );
				}
			}
		}
		
		/* Standard Signature Requests */
		else if ( $request instanceof SignatureRequest ) {
			
			// CC Emails
			if ( isset( $options['ccs'] ) ) {
				foreach( $options['ccs'] as $cc ) {
					$request->addCC( $cc['email'] );
				}
			}
		}
		
		return $request;
	}
	
	/**
	 * Send a signature request
	 *
	 * @param	AbstractSignatureRequest	$request				The signature request
	 * @return	SignatureRequest
	 * @throws	HelloSign\BaseException
	 */
	public function sendRequest( $request )
	{
		$client = $this->getClient();
		
		return $request instanceof TemplateSignatureRequest ? 
			$client->sendTemplateSignatureRequest( $request ) : 
			$client->sendSignatureRequest( $request );
	}
	
}