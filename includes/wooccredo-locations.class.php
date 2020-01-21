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

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/DefaultLocationCode?access_token=". $accessToken['access_token'];
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
         * Get locations.
         * 
         * @param   string  $code       Location code.
         * @param   string  $fields     Taxonomy fields.
         * @return  array
         * @since   1.0.0
         */
        public static function getLocations($code = '', $fields = 'all') {
            $args = [
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE,
                'fields'        => $fields
            ];

            if( !empty($code) ) : 
                $args['meta_key'] = 'location_code';
                $args['meta_value'] = $code;
            endif;

            $locations = get_terms($args);

            return $locations ? $locations : FALSE;
        }

        /**
         * Update location
         * 
         * @param   string  $name       Location name.
         * @param   string  $code       Location code.
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
                update_term_meta($term['term_id'], 'sync_started', get_option('wc_wooccredo_sync_started'));
                update_term_meta($term['term_id'], 'location_code', $code);

                Wooccredo::addLog('Location : '. $name .' synced.');
            endif;
        }

        /**
         * Get locations from api.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getLocationsFromAPI($url = '') {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = !empty($url) ? $url : $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/ICLocation?\$Select=LocationCode,LocationName,Inactive,BranchCode,DepartmentCode,DefaultDeliveryCode?access_token=". $accessToken['access_token'];
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
         * Get unsynced locations.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getUnsyncedLocations() {
            $syncStarted = get_option('wc_wooccredo_sync_started');

            $locations = get_terms([
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

            return $locations;
        }

        /**
         * Delete location.
         * 
         * @param   int     $location       Location ID.
         * @since   1.0.0
         */
        public static function deleteLocation($location) {
            wp_delete_term($location, self::$taxonomy);

            Wooccredo::addLog('Location : '. $location .' deleted.');
        }
    }

    Wooccredo_Locations::init();
endif;