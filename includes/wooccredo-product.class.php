<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Product') ) :
    class Wooccredo_Product {
        /**
         * Init.
         * 
         * @since   1.0.0
         */
        public static function init() {
        }

        /**
         * Get products.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getProducts() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ICProductList?access_token=". $accessToken['access_token'];
            $args = [
                'headers'   => [
                    'OData-Version: 4.0',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ];
            $results = wp_remote_get($url, $args);

            return !is_wp_error($results) && ( is_array($results) && !isset($results['body']['error']) ) ? json_decode($results['body'], TRUE) : FALSE;
        }

        /**
         * Get product.
         * 
         * @param   integer     $productCode        Product code.
         * @return  array
         * @since   1.0.0
         */
        public static function getProduct($productCode) {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ICProduct('". $productCode ."')?access_token=". $accessToken['access_token'];
            $args = [
                'headers'   => [
                    'OData-Version: 4.0',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ];
            $request = wp_remote_get($url, $args);
            $response = json_decode(wp_remote_retrieve_body($request), TRUE);

            return !is_wp_error($response) && !isset($response['error']) ? $response : FALSE;
        }

        /**
         * Create product.
         * 
         * @param   int     $productID      Product ID.
         * @return  array
         * @since   1.0.0
         */
        public static function createProduct($productID) {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $product = wc_get_product($productID);

            if( !$product )
                return FALSE;

            $code = $product->get_sku() ? $product->get_sku() : strtoupper(str_replace(' ', '', $product->get_name()));

            if( $accredoProduct = self::getProduct($code) )
                return $accredoProduct;

            $accredoProduct = [
                'RecNo'                     => $product->get_id(),
                'Name'                      => $product->get_name(),
                'ProductCode'               => $code,
                'Description'               => $product->get_description() ? $product->get_description() : 'No description.',
                'Weight'                    => floatval($product->get_weight()),
                'AllowInactive'             => TRUE
            ];

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ICProduct?access_token=". $accessToken['access_token'];
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($accredoProduct));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                // 'OData-Version: 4.0',
                'Accept: application/json;',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if( $error ) :
                return FALSE;
            else :
                $response = json_decode($result, TRUE);

                return isset($response['ProductCode']) && $response['ProductCode'] ? self::getProduct($response['ProductCode']) : FALSE;
            endif;

            // $args = [
            //     'headers'       => [
            //         'Authorization: Basic '. $accessToken['access_token'],
            //         'Accept: application/json',
            //         'Content-Type: application/json'
            //     ],
            //     'body'          => wc_json_encode($accredoProduct)
            // ];
            // $results = wp_remote_post($url, $args);

            // if( !is_wp_error($results) && is_array($results) ) :
            //     $result = json_decode($results['body'], TRUE);

            //     return $result;
            //     // return $result['ProductCode'] ? self::getProduct($result['ProductCode']) : FALSE;
            // else :
            //     return FALSE;
            // endif;
        }
    }

    Wooccredo_Product::init();
endif;