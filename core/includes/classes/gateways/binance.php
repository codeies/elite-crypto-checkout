<?php
function woocrypto_init_binancepay_class()
{

    class Woocrypto_BinancePay extends WC_Payment_Gateway
    {
        protected $order = null;
        protected $transactionErrorMessage = null;
        protected $woocrypto_checkout_options  = null;
        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct()
        {

            $this->id = 'woocrypto_binancepay'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'Binance Pay';
            $this->method_description = '<p>Webhook</p>
            <p>'.get_site_url().'/wc-api/woocrypto-binance/</p>Collect payments using binance pay checkout'; // will be displayed on the options page

            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->testmode = null; // === $this->get_option( 'testmode' );
            $this->live_secretkey = $this->testmode ? $this->get_option('test_secretkey') : $this->get_option('live_secretkey');
            $this->live_apikey = $this->testmode ? $this->get_option('test_apikey') : $this->get_option('live_apikey');

            $this->woocrypto_checkout_options = get_option( 'woocrypto_checkout_option_name' ); 
            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options'
            ));

            // We need custom JavaScript to obtain a token
          /*    add_action('wp_enqueue_scripts', array(
                $this,
                'payment_scripts'
            ));*/

            // You can also register a webhook here
             add_action( 'woocommerce_api_woocrypto-binance', array( $this, 'webhook' ) );
            
        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Binance pay',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ) ,
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default' => 'Binance Pay',
                    'desc_tip' => true,
                ) ,
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default' => 'With Binance Pay, to pay someone via QR code, simply scan their Binance Pay QR code with your Binance app.',
                ) ,
                'live_apikey' => array(
                    'title' => 'API Key',
                    'type' => 'text'
                ) ,
                'live_secretkey' => array(
                    'title' => 'Secret Key',
                    'type' => 'password'
                ),
/*                'testmode' => array(
                    'title' => 'Enable/Disable',
                    'label' => 'Enable Sandbox',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ) ,
                'test_apikey' => array(
                    'title' => 'Sandbox API Key',
                    'type' => 'text'
                ) ,
                'test_secretkey' => array(
                    'title' => 'Sandbox Secret Key',
                    'type' => 'password'
                )*/
            );

        }

        /**
         * You will need it if you want your custom credit card form, Step 4 is about it
         */
        public function payment_fields()
        {

            //
            
        }

        /*
         * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
        */
        public function payment_scripts()
        {

            // we need JavaScript to process a token only on cart/checkout pages, right?
            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order']))
            {
                return;
            }

            // if our payment gateway is disabled, we do not have to enqueue JS too
            if ('no' === $this->enabled)
            {
                return;
            }

            // no reason to enqueue JavaScript if API keys are not set
            if (empty($this->live_secretkey) || empty($this->live_apikey))
            {
                return;
            }

            // do not work with card detailes without SSL unless your website is in a test mode
            /*if ( ! $this->testmode && ! is_ssl() ) {
                        return;
                    }*/

            // let's suppose it is our payment processor JavaScript that allows to obtain a token
            // wp_enqueue_script( 'misha_js', 'https://www.mishapayments.com/api/token.js' );
            // and this is our custom JS in your plugin directory that works with token.js
            wp_register_script('woocrypto_binancepay', plugins_url('binance.js', __FILE__) , array(
                'jquery'
            ));

            // in most payment processors you have to use PUBLIC KEY to obtain a token
            wp_localize_script('woocrypto_binancepay', 'params', array(
                'live_apikey' => $this->live_apikey
            ));

            wp_enqueue_script('woocrypto_binancepay');

        }

        /*
         * Fields validation, more in Step 5
        */
        public function validate_fields()
        {

            //
            
        }

        /*
         * We're processing the payments here, everything about it is in Step 5
        */
        public function process_payment($order_id)
        {
    
            if(!$this->woocrypto_checkout_options){
                 wc_add_notice(__('Please select your checkout currency from WooCrypto settings', 'woocrypto-checkout'),'error');
            }
 
            

            $time = round(microtime(true) * 1000);
            $nonce = substr(sha1(rand()) , 0, 32);

            $this->order  = wc_get_order( $order_id );
            
            $entityBody = [
                "env" => ["terminalType" => "WEB"],
                'orderAmount'=>$this->woocrypto_checkout_options['exchange_rate'] * $this->order->get_total() ,
                'merchantTradeNo'=>$order_id,
                "prepayId"=>$order_id,
                "currency" => $this->woocrypto_checkout_options['checkout_currency'],
                "goods" => [
                    "goodsType" => "01", 
                    "goodsCategory" => "0000", 
                    "referenceGoodsId" => $order_id, 
                    "goodsName" => 'Order ID - '.$order_id , 
                ],
                "returnUrl"=>$this->order->get_checkout_order_received_url(),
                "cancelUrl"=>$this->order->get_cancel_order_url()
            ];
            $entityBody = json_encode($entityBody);
            $payload = $time . "\n" . $nonce . "\n" . $entityBody . "\n";
            $signature = strtoupper(hash_hmac('SHA512', $payload, $this->live_secretkey));

            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json; charset=utf-8' ,
                    'Binancepay-Timestamp' =>  $time  ,
                    'Binancepay-Nonce' =>  $nonce  ,
                    'BinancePay-Certificate-SN' =>  $this->live_apikey  ,
                    'Binancepay-Signature' =>  $signature  ,
                ),
                'body' => $entityBody
            );
            $response = wp_remote_post( 'https://bpay.binanceapi.com/binancepay/openapi/v2/order', $args );

            $result     = json_decode( wp_remote_retrieve_body( $response ) );

            if($result->status =='SUCCESS'){
                 // $order->update_status( 'on-hold', __( 'Awaiting binance payment', 'wc-gateway-offline' ) );
                            
                $url = $result->data->checkoutUrl;
                     return array(
                        'result' => 'success',
                        'redirect' => $url
                 );
            }else{
                $this->transactionErrorMessage = $result->errorMessage;
                $this->mark_as_failed_payment();
                wc_add_notice(__('(Transaction Error) something is wrong.', 'woocrypto-checkout'),'error');
            }

        }
        protected function mark_as_failed_payment() {
             $this->order->add_order_note(sprintf("Payment Failed with message: '%s'", $this->transactionErrorMessage));
        }

        /*
         * In case you need a webhook, like PayPal IPN etc
        */
        public function webhook()
        {
            $headers = getallheaders();
            if(!isset($headers['binancepay-signature']))
                return;

            //$payload = $headers['binancepay-timestamp'] . "\n" . $headers['binancepay-nonce'] . "\n" . $entityBody . "\n";
           // $decodedSignature = base64_decode ( $headers['binancepay-signature'] );

           // $result = openssl_verify($payload, $decodedSignature, $this->live_apikey, OPENSSL_ALGO_SHA256 );


            $request_body = @file_get_contents('php://input');

            $request_body = json_decode($request_body,true);


            //if ($result == 1) {
               // Verified Payment
                if(isset($request_body['data'])){
                    $request_data = json_decode($txt['data'],true);
                    $order_id = $request_data['merchantTradeNo'];
                    $amount = $request_data['totalFee'];
                    $currency = $request_data['currency'];
                    if($request_body['bizStatus'] == 'PAY_SUCCESS' || $request_body['bizStatus'] =='PAY_CLOSED'){
                         $order = wc_get_order( $order_id );
                         $order->update_status( 'completed' );
                         $order->add_meta_data('Binance Amount '.$currency.' ', wc_clean($posted['totalFee']));
                    }
                   
                }

           // } elseif ($result == 0) {
             //   error_log( "signature is invalid for given data.");
            //} else {
             //   error_log( "error: ".openssl_error_string());
           // }
        }
    }
}

