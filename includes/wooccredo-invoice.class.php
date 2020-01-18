<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Invoice') ) :
    class Wooccredo_Invoice {
        static $invoice;
        protected static $token;
        protected static $company;

        /**
         * Init.
         */
        public static function init() {
            self::$token = Wooccredo::getToken();
            self::$company = Wooccredo::getOption('company');
        }

        /**
         * Get invoice.
         * 
         * @param int       Document ID
         * 
         * @return array
         */
        public static function getInvoice($documentID) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". self::$company ."')/INInvoice(". $documentID .")?\$expand=Line,Charge,Link?access_token=". self::$token['access_token']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);
            
            self::$invoice = json_decode($result, TRUE);
        }

        public static function view() {
            if( !self::$invoice )
                return;

            include_once WOOCCREDO_ABSPATH .'includes/admin/views/invoice/heading.php';
            include_once WOOCCREDO_ABSPATH .'includes/admin/views/invoice/tabs.php';
        }

        /**
         * Create invoice lines.
         * 
         * @param integer   $orderID
         * 
         * @return array
         */
        public static function createInvoiceLines($orderID) {
            $order = wc_get_order($orderID);

            if( !$order )
                return FALSE;

            $lines = [];
            $items = $order->get_items();

            if( 0 < count($items) ) :
                foreach( $items as $item ) :
                    $product = $item->get_product();
                    
                    if( empty($product->get_sku()) ) 
                        continue;

                    $line = Wooccredo_Product::createProduct($item->get_product_id());
                    
                    if( $line ) :
                        $lines[] = [
                            'LineType'              => 'P',
                            'ProductCode'           => $line['ProductCode'],
                            'Description'           => $line['Description'],
                            'QuantitySupplied'      => $item->get_quantity(),
                            'SellingPrice'          => $product->get_price(),
                            'DiscountPercentage'    => 0
                        ];
                    endif;
                endforeach;
            endif;

            return $lines;
        }
    }

    Wooccredo_Invoice::init();
endif;