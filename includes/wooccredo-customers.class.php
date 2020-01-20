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
         * @param   string  $code       Customer code.
         * @param   string  $fields     Taxonomy fields.
         * @return  array
         * @since   1.0.0
         */
        public static function getCustomers($code = '', $fields = 'all') {
            $args = [
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE,
                'fields'        => $fields
            ];

            if( !empty($code) ) : 
                $args['meta_key'] = 'customer_code';
                $args['meta_value'] = $code;
            endif;

            $customers = get_terms($args);

            return $customers ? $customers : FALSE;
        }

        /**
         * Update customer.
         * 
         * @param   string  $name       Customer name.
         * @param   string  $code       Customer code.
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
                update_term_meta($term['term_id'], 'sync_started', get_option('wc_wooccredo_sync_started'));
                update_term_meta($term['term_id'], 'customer_code', $code);
            endif;

            Wooccredo::addLog('Customer : '. $name .' synced');
        }

        /**
         * Get customers from api.
         * 
         * @since   1.0.0
         */
        public static function getCustomersFromAPI($url = '') {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = !empty($url) ? $url : $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ARCustomerList?\$Select=CustomerCode,CustomerName?access_token=". $accessToken['access_token'];
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
    }

    Wooccredo_Customers::init();
endif;