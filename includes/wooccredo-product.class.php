<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Product') ) :
    class Wooccredo_Product {
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
         * Get products.
         * 
         * @return array
         */
        public static function getProducts() {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". self::$company ."')/ICProductList?access_token=". self::$token['access_token']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);
            
            return json_decode($result, TRUE);
        }

        /**
         * Get product.
         * 
         * @param integer   Product code.
         * 
         * @return array
         */
        public static function getProduct($productCode) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". self::$company ."')/ICProduct('". $productCode ."')?access_token=". self::$token['access_token']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if( $error ) :
                return FALSE;
            else :
                $result = json_decode($result, TRUE);

                return isset($result['ProductCode']) ? $result : FALSE;
            endif;
        }

        /**
         * Create product.
         * 
         * @param integer   Product ID.
         * 
         * @return
         */
        public static function createProduct($productID) {
            $accredoProduct = [];
            $product = wc_get_product($productID);

            if( !$product )
                return FALSE;

            $code = strtoupper(str_replace(' ', '', $product->get_name()));
            $accredoProduct = self::getProduct($code);
            // $accredoProduct = self::getProduct('1.8MWARDROBE');

            if( $accredoProduct ) :
                return $accredoProduct;
            endif;

            $accredoProduct = [
                'RecNo'                     => $product->get_id(),
                'Name'                      => $product->get_name(),
                'ProductCode'               => strtoupper(str_replace(' ', '', $product->get_name())),
                'Description'               => $product->get_description(),
                'Weight'                    => floatval($product->get_weight()),
                'AllowInactive'             => TRUE
            ];

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". self::$company ."')/ICProduct?access_token=". self::$token['access_token']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($accredoProduct));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'OData-Version: 4.0',
                'Accept: application/json;odata.metadata=minimal',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if( $error ) :
                return FALSE;
            else :
                $response = json_decode($result, TRUE);

                if( $response['ProductCode'] ) :
                    return self::getProduct($response['ProductCode']);
                else :
                    return FALSE;
                endif;
            endif;
        }
    }

    Wooccredo_Product::init();
endif;