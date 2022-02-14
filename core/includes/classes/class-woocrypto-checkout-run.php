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



}
