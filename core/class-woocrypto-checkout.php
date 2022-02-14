<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Woocrypto_Checkout' ) ) :

	/**
	 * Main Woocrypto_Checkout Class.
	 *
	 * @package		WOOCRYPTOC
	 * @subpackage	Classes/Woocrypto_Checkout
	 * @since		1.0
	 * @author		Codeies
	 */
	final class Woocrypto_Checkout {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0
		 * @var		object|Woocrypto_Checkout
		 */
		private static $instance;

		/**
		 * WOOCRYPTOC helpers object.
		 *
		 * @access	public
		 * @since	1.0
		 * @var		object|Woocrypto_Checkout_Helpers
		 */
		public $helpers;

		/**
		 * WOOCRYPTOC settings object.
		 *
		 * @access	public
		 * @since	1.0
		 * @var		object|Woocrypto_Checkout_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'woocrypto-checkout' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'woocrypto-checkout' ), '1.0' );
		}

		/**
		 * Main Woocrypto_Checkout Instance.
		 *
		 * Insures that only one instance of Woocrypto_Checkout exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0
		 * @static
		 * @return		object|Woocrypto_Checkout	The one true Woocrypto_Checkout
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Woocrypto_Checkout ) ) {
				self::$instance					= new Woocrypto_Checkout;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->settings		= new Woocrypto_Checkout_Settings();
				self::$instance->Woocommerce	= new Woocrypto_Checkout_Woocommerce();

				//Fire the plugin logic
				new Woocrypto_Checkout_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'WOOCRYPTOC/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0
		 * @return  void
		 */
		private function includes() {
			//require_once WOOCRYPTOC_PLUGIN_DIR . 'core/includes/classes/class-woocrypto-checkout-helpers.php';
			require_once WOOCRYPTOC_PLUGIN_DIR . 'core/includes/classes/class-woocrypto-checkout-settings.php';
			require_once WOOCRYPTOC_PLUGIN_DIR . 'core/includes/classes/class-woocrypto-checkout-woocommerce.php';

			require_once WOOCRYPTOC_PLUGIN_DIR . 'core/includes/classes/class-woocrypto-checkout-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'woocrypto-checkout', FALSE, dirname( plugin_basename( WOOCRYPTOC_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.