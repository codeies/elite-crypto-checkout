<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Woocrypto_Checkout_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		WOOCRYPTOC
 * @subpackage	Classes/Woocrypto_Checkout_Run
 * @author		Codeies
 * @since		1.0
 */
class Woocrypto_Checkout_Run{

	/**
	 * Our Woocrypto_Checkout_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0
	 */
	function __construct(){
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'plugin_action_links_' . WOOCRYPTOC_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts_and_styles' ), 20 );
		add_action( 'wp_ajax_nopriv_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );
		add_action( 'wp_ajax_my_demo_ajax_call', array( $this, 'my_demo_ajax_call_callback' ), 20 );

	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	* Adds action links to the plugin list table
	*
	* @access	public
	* @since	1.0
	*
	* @param	array	$links An array of plugin action links.
	*
	* @return	array	An array of plugin action links.
	*/
	public function add_plugin_action_link( $links ) {

		$links['our_shop'] = sprintf( '<a href="%s" title="Settings" style="font-weight:700;">%s</a>', 'admin.php?page=woocrypto-checkout', __( 'Settings', 'woocrypto-checkout' ) );

		return $links;
	}


	/**
	 * Enqueue the frontend related scripts and styles for this plugin.
	 *
	 * @access	public
	 * @since	1.0
	 *
	 * @return	void
	 */
	public function enqueue_frontend_scripts_and_styles() {
		wp_enqueue_script( 'woocryptoc-frontend-scripts', WOOCRYPTOC_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js', array(), WOOCRYPTOC_VERSION, true );
		wp_localize_script( 'woocryptoc-frontend-scripts', 'woocryptoc', array(
			'demo_var'   		=> __( 'This is some demo text coming from the backend through a variable within javascript.', 'woocrypto-checkout' ),
			'ajaxurl' 			=> admin_url( 'admin-ajax.php' ),
			'security_nonce'	=> wp_create_nonce( "your-nonce-name" ),
		));
	}


	/**
	 * The callback function for my_demo_ajax_call
	 *
	 * @access	public
	 * @since	1.0
	 *
	 * @return	void
	 */
	public function my_demo_ajax_call_callback() {
		check_ajax_referer( 'your-nonce-name', 'ajax_nonce_parameter' );

		$demo_data = isset( $_REQUEST['demo_data'] ) ? sanitize_text_field( $_REQUEST['demo_data'] ) : '';
		$response = array( 'success' => false );

		if ( ! empty( $demo_data ) ) {
			$response['success'] = true;
			$response['msg'] = __( 'The value was successfully filled.', 'woocrypto-checkout' );
		} else {
			$response['msg'] = __( 'The sent value was empty.', 'woocrypto-checkout' );
		}

		if( $response['success'] ){
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( $response );
		}

		die();
	}

}
