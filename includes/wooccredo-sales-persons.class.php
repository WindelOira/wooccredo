<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Sales_Persons') ) :
    class Wooccredo_Sales_Persons {
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
            self::$taxonomy = 'wooccredo_sales_persons';
        }

        /**
         * Check if synced.
         * 
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_sales_persons_synced') ? TRUE : FALSE;
        }

        /**
         * Get sales persons.
         * 
         * @param   string  $code       Sales person code.
         * @param   string  $fields     Taxonomy fields.
         * @return  array
         * @since   1.0.0
         */
        public static function getSalesPersons($code = '', $fields = 'all') {
            $args = [
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE,
                'fields'        => $fields
            ];

            if( !empty($code) ) : 
                $args['meta_key'] = 'sales_person_code';
                $args['meta_value'] = $code;
            endif;

            $salesPersons = get_terms($args);

            return $salesPersons ? $salesPersons : FALSE;
        }

        /**
         * Update sales person.
         * 
         * @param   string  $name       Sales person name.
         * @param   string  $code       Sales person code.
         * @since   1.0.0
         */
        public static function updateSalesPerson($name, $code) {
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
                update_term_meta($term['term_id'], 'sales_person_code', $code);

                Wooccredo::addLog('Sales Person : '. $name .' synced.');
            endif;
        }

        /**
         * Get sales persons from api.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getSalesPersonsFromAPI($url = '') {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = !empty($url) ? $url : $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ARSalesPerson?\$Select=SalesPersonCode,SalesPersonName,Inactive?access_token=". $accessToken['access_token'];
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
         * Get unsynced sales persons.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getUnsyncedSalesPersons() {
            $syncStarted = get_option('wc_wooccredo_sync_started');

            $salesPersons = get_terms([
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE,
                'fields'        => 'ids',
                'meta_query'    => [
                    'relation'      => 'AND',
                    [
                        'key'       => 'sync_started',
                        'value'     => $syncStarted,
                        'compare'   => '<'
                    ]
                ]
            ]);

            return $salesPersons;
        }

        /**
         * Delete sales person.
         * 
         * @param   int     $salesPerson        Sales person ID.
         * @since   1.0.0
         */
        public static function deleteSalesPerson($salesPerson) {
            wp_delete_term($salesPerson, self::$taxonomy);

            Wooccredo::addLog('Sales Person : '. $salesPerson .' deleted.');
        }
    }

    Wooccredo_Sales_Persons::init();
endif;