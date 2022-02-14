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
            $this->testmode = 'yes'; // === $this->get_option( 'testmode' );
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
                'testmode' => array(
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
                )
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
                "env" => ["terminalType" => "MINI_PROGRAM"],
                'orderAmount'=>$this->woocrypto_checkout_options['exchange_rate'] * $this->order->get_total() ,
                'merchantTradeNo'=>$order_id,
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
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://bpay.binanceapi.com/binancepay/openapi/v2/order');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $entityBody);

            $payload = $time . "\n" . $nonce . "\n" . $entityBody . "\n";
            //echo $payload;
            $signature = strtoupper(hash_hmac('SHA512', $payload, $this->live_secretkey));
           // echo $signature;
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Binancepay-Timestamp: ' . $time . '';
            $headers[] = 'Binancepay-Nonce: ' . $nonce . '';
            $headers[] = 'Binancepay-Certificate-Sn: '.$this->live_apikey.'';
            $headers[] = 'Binancepay-Signature: ' . $signature . '';

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = json_decode(curl_exec($ch));
            if (curl_errno($ch))
            {
                wc_add_notice( $e->getMessage(), 'error' );
                $order->update_status( 'failed', $e->getMessage() );
            }
            
            curl_close($ch);
            if($result->status =='SUCCESS'){
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
        /*   function mylog($txt) {
             file_put_contents('mylog.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
            }

            mylog(print_r($_REQUEST,true));*/

           /*
            $payload = $headers['Binancepay-Timestamp'] . "\n" . $headers['Binancepay-Nonce'] . "\n" . $entityBody . "\n";
            $decodedSignature = base64_decode ( $headers['Binancepay-Signature'] );
            $ok = openssl_verify($payload, $decodedSignature, $this->live_secretkey, OPENSSL_ALGO_SHA256 );
              if ($ok == 1) {
                  $this->order = wc_get_order( $$order_id );
                  $this->order->payment_complete();
                  $this->order->reduce_order_stock();
              } elseif ($ok == 0) {
                 // Verification Failed
              } else {
                //  echo "Error whilst checking request signature.";
              }
            wp_die();*/
        }
    }
}

