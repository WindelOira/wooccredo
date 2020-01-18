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
         * @return array
         */
        public static function getSalesAreas() {
            $salesAreas = get_terms([
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE
            ]);

            return $salesAreas ? $salesAreas : FALSE;
        }

        /**
         * Update sales area.
         * 
         * @param   $name   Sales area name.
         * @param   $code   Sales area code.
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
                update_term_meta($term['term_id'], 'sales_area_code', $code);
            endif;

            error_log($name .' synced');
        }

        /**
         * Get sales areas from api.
         * 
         * @since   1.0.0
         * @return  array
         */
        public static function getSalesAreasFromAPI() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ARSalesArea?\$Select=SalesAreaCode,SalesAreaName,Inactive?access_token=". $accessToken['access_token']);
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

    Wooccredo_Sales_Areas::init();
endif;