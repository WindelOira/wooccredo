<?php

defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Customers') ) :
    class Wooccredo_Customers {
        /**
         * Taxonomy name.
         * 
         * @since   1.0.0
         */
        public static $taxonomy;

        /**
         * Init.
         * 
         * @since   1.0.0
         */
        public static function init() {
            self::$taxonomy = 'wooccredo_customers';
        }

        /**
         * Check if synced.
         * 
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_customers_synced') ? TRUE : FALSE;
        }

        /**
         * Get customers.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getCustomers() {
            $customers = get_terms([
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE
            ]);

            return $customers ? $customers : FALSE;
        }

        /**
         * Update customer.
         * 
         * @param   $name   Customer name.
         * @param   $code   Customer code.
         * @since   1.0.0
         */
        public static function updateCustomer($name, $code) {
            $slug = sanitize_title(!empty($code) ? $code : $name);

            if( $term = term_exists($slug, self::$taxonomy) ) :
                $term = wp_update_term($term['term_id'], self::$taxonomy, [
                    'name'  => $name,
                    'slug'  => $slug
                ]);
            else :
                $term = wp_insert_term($name, self::$taxonomy, [
                    'slug'  => $slug
                ]);
            endif;

            if( !is_wp_error($term) ) :
                update_term_meta($term['term_id'], 'customer_code', $code);
            endif;

            error_log($name .' synced');
        }

        /**
         * Get customers from api.
         * 
         * @since   1.0.0
         */
        public static function getCustomersFromAPI() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ARCustomerList?\$Select=CustomerCode,CustomerName?access_token=". $accessToken['access_token']);
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
                $results = json_decode($result, TRUE);
                return isset($results['error']) ? FALSE : $results;
            endif;
        }
    }

    Wooccredo_Customers::init();
endif;