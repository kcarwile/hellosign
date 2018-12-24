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
		
		return $this->saveRequest( $client->createEmbeddedSignatureRequest( $embedded_request ), 'embedded' );
	}
	
	/**
	 * Create a signature request given some options
	 *
	 * @param	array						$options			Signature request options
	 * @return	AbstractSignatureRequest
	 */
	public function createRequest( $options )
	{
		$request = isset( $options['template_id'] ) ? new TemplateSignatureRequest : new SignatureRequest;
		
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
			foreach( $options['signers'] as $email => $name ) {
				is_array( $name ) ? $request->addSigner( $email, $name[0], $name[1] ) : $request->addSigner( $email, $name ) ;
			}
		}
		
		/* Files */
		if ( isset( $options['files'] ) and is_array( $options['files'] ) ) {
			foreach( $options['files'] as $file ) {
				$request->addFile( $file );
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
					if ( is_array( $cc ) ) {
						$request->setCC( $cc[1], $cc[0] );
					}
				}
			}
		}
		
		/* Standard Signature Requests */
		else if ( $request instanceof SignatureRequest ) {
			
			// CC Emails
			if ( isset( $options['ccs'] ) ) {
				foreach( $options['ccs'] as $cc ) {
					is_array( $cc ) ? $request->addCC( $cc[0] ) : $request->addCC( $cc );
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
		
		$response = $request instanceof TemplateSignatureRequest ? 
			$client->sendTemplateSignatureRequest( $request ) : 
			$client->sendSignatureRequest( $request );
			
		return $this->saveRequest( $response, $this->getRequestType( $request ) );
	}
	
	/**
	 * Get a string that identifies a request type
	 *
	 * @param	AbstractSignatureRequest	$request				The signature request
	 * @return	string
	 */	
	public function getRequestType( $request )
	{
		if ( $request instanceof TemplateSignatureRequest ) {
			return 'template';
		}
		
		if ( $request instanceof SignatureRequest ) {
			return 'standard';
		}
		
		if ( $request instanceof EmbeddedSignatureRequest ) {
			return 'embedded';
		}
		
		return '';
	}
	
	/**
	 * Save a signature request response object
	 *
	 * @return	SignatureRequest
	 */
	public function saveRequest( $request, $type )
	{
		$saved_request = new Models\SignatureRequest;
		
		$saved_request->title = $request->getTitle();
		$saved_request->request_id = $request->getId();
		$saved_request->type = $type;
		$saved_request->data = $request->toArray();
		$saved_request->save();
		
		return $request;
	}
	
	
}