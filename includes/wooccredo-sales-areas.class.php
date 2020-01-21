<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Sales_Areas') ) :
    class Wooccredo_Sales_Areas {
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
            self::$taxonomy = 'wooccredo_sales_areas';
        }

        /**
         * Check if synced.
         * 
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_sales_areas_synced') ? TRUE : FALSE;
        }

        /**
         * Get sales areas.
         * 
         * @param   string  $ccode      Sales area code.
         * @param   string  $fields     Taxonomy fields.
         * @return  array
         * @since   1.0.0
         */
        public static function getSalesAreas($code = '', $fields = 'all') {
            $args = [
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE,
                'fields'        => $fields
            ];

            if( !empty($code) ) : 
                $args['meta_key'] = 'sales_area_code';
                $args['meta_value'] = $code;
            endif;

            $salesAreas = get_terms($args);

            return $salesAreas ? $salesAreas : FALSE;
        }

        /**
         * Update sales area.
         * 
         * @param   string  $name       Sales area name.
         * @param   string  $code       Sales area code.
         * @since   1.0.0
         */
        public static function updateSalesArea($name, $code) {
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
                update_term_meta($term['term_id'], 'sales_area_code', $code);

                Wooccredo::addLog('Sales Area : '. $name .' synced.');
            endif;
        }

        /**
         * Get sales areas from api.
         * 
         * @since   1.0.0
         * @return  array
         */
        public static function getSalesAreasFromAPI($url = '') {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = !empty($url) ? $url : $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ARSalesArea?\$Select=SalesAreaCode,SalesAreaName,Inactive?access_token=". $accessToken['access_token'];
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
         * Get unsynced ales areas.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getUnsyncedSalesAreas() {
            $syncStarted = get_option('wc_wooccredo_sync_started');

            $salesAreas = get_terms([
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

            return $salesAreas;
        }

        /**
         * Delete sales area.
         * 
         * @param   int     $salesArea      Sales area ID.
         * @since   1.0.0
         */
        public static function deleteSalesArea($salesArea) {
            wp_delete_term($salesArea, self::$taxonomy);

            Wooccredo::addLog('Sales Area : '. $salesArea .' deleted.');
        }
    }

    Wooccredo_Sales_Areas::init();
endif;