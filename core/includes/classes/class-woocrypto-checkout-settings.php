<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Woocrypto_Checkout_Settings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package		WOOCRYPTOC
 * @subpackage	Classes/Woocrypto_Checkout_Settings
 * @author		Codeies
 * @since		1.0
 */
class Woocrypto_Checkout_Settings{

	/**
	 * The plugin name
	 *
	 * @var		string
	 * @since   1.0
	 */
	private $plugin_name;
	private $woocrypto_checkout_options;

	/**
	 * Our Woocrypto_Checkout_Settings constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0
	 */
	function __construct(){
		$this->plugin_name = WOOCRYPTOC_NAME;
		add_action( 'admin_menu', array( $this, 'woocrypto_checkout_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'woocrypto_checkout_page_init' ) );
	}

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	/**
	 * Return the plugin name
	 *
	 * @access	public
	 * @since	1.0
	 * @return	string The plugin name
	 */
	public function get_plugin_name(){
		return apply_filters( 'WOOCRYPTOC/settings/get_plugin_name', $this->plugin_name );
	}
		public function woocrypto_checkout_add_plugin_page() {
		add_submenu_page(
			'woocommerce',
			'WooCrypto Checkout', // page_title
			'WooCrypto Checkout', // menu_title
			'manage_options', // capability
			'woocrypto-checkout', // menu_slug
			array( $this, 'woocrypto_checkout_create_admin_page' ) // function
		);
	}

	public function woocrypto_checkout_create_admin_page() {
		$this->woocrypto_checkout_options = get_option( 'woocrypto_checkout_option_name' ); ?>

		<div class="wrap">
			<h2>WooCrypto Checkout</h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'woocrypto_checkout_option_group' );
					do_settings_sections( 'woocrypto-checkout-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function woocrypto_checkout_page_init() {
		register_setting(
			'woocrypto_checkout_option_group', // option_group
			'woocrypto_checkout_option_name', // option_name
			array( $this, 'woocrypto_checkout_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'woocrypto_checkout_setting_section', // id
			'Settings', // title
			array( $this, 'woocrypto_checkout_section_info' ), // callback
			'woocrypto-checkout-admin' // page
		);

		add_settings_field(
			'checkout_currency', // id
			'Checkout Currency', // title
			array( $this, 'checkout_currency_callback' ), // callback
			'woocrypto-checkout-admin', // page
			'woocrypto_checkout_setting_section' // section
		);

		add_settings_field(
			'exchange_rate', // id
			'Exchange Rate', // title
			array( $this, 'exchange_rate_callback' ), // callback
			'woocrypto-checkout-admin', // page
			'woocrypto_checkout_setting_section' // section
		);
	}

	public function woocrypto_checkout_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['checkout_currency'] ) ) {
			$sanitary_values['checkout_currency'] = $input['checkout_currency'];
		}

		if ( isset( $input['exchange_rate'] ) ) {
			$sanitary_values['exchange_rate'] = sanitize_text_field( $input['exchange_rate'] );
		}

		return $sanitary_values;
	}

	public function woocrypto_checkout_section_info() {
		
	}

	public function checkout_currency_callback() {
		?> <select name="woocrypto_checkout_option_name[checkout_currency]" id="checkout_currency">
			<?php 
			$currencies = array('ADA','ATOM','AVA','BCH','BNB','BTC','BUSD','CTSI','DASH','DOGE','DOT','EGLD','EOS','ETC','ETH','FIL','FRONT','FTM','GRS','HBAR','IOTX','LINK','LTC','MANA','MATIC','NEO','OM','ONE','PAX','QTUM','STRAX','SXP','TRX','TUSD','UNI','USDC','USDT','VAI','VET','WRX','XLM','XMR','XRP','XTZ','XVS','ZEC','ZIL'); ?>
			<?php foreach ($currencies as  $currency) { 
				$selected = (isset( $this->woocrypto_checkout_options['checkout_currency'] ) && $this->woocrypto_checkout_options['checkout_currency'] === $currency) ? 'selected' : '' ; 
				?>
				<option value="<?php echo $currency; ?>" <?php echo $selected; ?>><?php echo $currency; ?></option>
			<?php } ?>
			<?php ?>
		</select> 
		<script>
			jQuery(document).ready(function() {
				 jQuery("#checkout_currency_span").text(jQuery('#checkout_currency').val());
			  jQuery('#checkout_currency').change(function(event) {
			    jQuery("#checkout_currency_span").text(jQuery(this).val());
			  });
			});
		</script>

		<?php
	}

	public function exchange_rate_callback() {
		printf(
			'1 '.get_option('woocommerce_currency').' = <input class="regular-text" type="text" name="woocrypto_checkout_option_name[exchange_rate]" id="exchange_rate" value="%s"> <span id="checkout_currency_span"></span>',
			isset( $this->woocrypto_checkout_options['exchange_rate'] ) ? esc_attr( $this->woocrypto_checkout_options['exchange_rate']) : ''
		);
	}
}

/* 
 * Retrieve this value with:
 * $woocrypto_checkout_options = get_option( 'woocrypto_checkout_option_name' ); // Array of All Options
 * $checkout_currency_0 = $woocrypto_checkout_options['checkout_currency']; // Checkout Currency
 * $exchange_rate_1 = $woocrypto_checkout_options['exchange_rate_1']; // Exchange Rate
 */