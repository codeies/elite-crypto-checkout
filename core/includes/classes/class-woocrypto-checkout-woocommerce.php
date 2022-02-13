<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Woocrypto_Checkout_Woocommerce
 *
 * This class contains all Woocommerce actions and hooks
 *
 * @package		WOOCRYPTOC
 * @subpackage	Classes/Woocrypto_Checkout_Woocommerce
 * @author		Codeies
 * @since		1.0
 */
class Woocrypto_Checkout_Woocommerce{
    /**
     * Our Woocrypto_Checkout_Run constructor 
     * to run the plugin logic.
     *
     * @since 1.0
     */
    function __construct(){
        require_once('gateways/binance.php');
        $this->add_hooks();
    }
    /**
     * ######################
     * ###
     * #### Woocommerce HOOKS
     * ###
     * ######################
     */

    /**
     * Registers all WordPress and plugin related hooks
     *
     * @access  private
     * @since   1.0
     * @return  void
     */
    private function add_hooks(){
    
        //Filters 
        add_filter( 'woocommerce_payment_gateways', array( $this, 'woocommerce_payment_gateways' ), 20 );
        add_action( 'plugins_loaded', 'woocrypto_init_binancepay_class' );
    
    }
    /**
     * This Filter add list of payment gateways
     *
     * @access  public
     * @since   1.0
     *
     * @return  void
     */
    public function woocommerce_payment_gateways($methods){
            $methods[] = 'Woocrypto_BinancePay'; 
            return $methods;
    }
    
}
