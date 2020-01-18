<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Locations') ) :
    class Wooccredo_Locations {
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
            self::$taxonomy = 'wooccredo_locations';
        }

        /**
         * Check if synced.
         * 
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_locations_synced') ? TRUE : FALSE;
        }

        /**
         * Get default location.
         * 
         * @return  string
         * @since   1.0.0
         */
        public static function getDefaultLocation() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/DefaultLocationCode?access_token=". $accessToken['access_token']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            $default = json_decode($result, TRUE);
            
            return !isset($default['error']) ? $default['DefaultLocationCode'] : '';
        }

        /**
         * Get locations.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getLocations() {
            $locations = get_terms([
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE
            ]);

            return $locations ? $locations : FALSE;
        }

        /**
         * Update location
         * 
         * @param   $name   Location name.
         * @param   $code   Location code.
         * @since   1.0.0
         */
        public static function updateLocation($name, $code) {
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
                update_term_meta($term['term_id'], 'location_code', $code);
            endif;

            error_log($name .' synced');
        }

        /**
         * Get locations from api.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getLocationsFromAPI() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ICLocation?\$Select=LocationCode,LocationName,Inactive,BranchCode,DepartmentCode,DefaultDeliveryCode?access_token=". $accessToken['access_token']);
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

    Wooccredo_Locations::init();
endif;