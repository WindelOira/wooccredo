<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Branches') ) :
    class Wooccredo_Branches {
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
            self::$taxonomy = 'wooccredo_branches';
        }

        /**
         * Check if synced.
         * 
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_branches_synced') ? TRUE : FALSE;
        }

        /**
         * Get default branch.
         * 
         * @return  string
         * @since   1.0.0
         */
        public static function getDefaultBranch() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/DefaultBranchCode?access_token=". $accessToken['access_token'];
            $args = [
                'headers'   => [
                    'OData-Version: 4.0',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ];
            $results = wp_remote_get($url, $args);

            return !is_wp_error($results) && ( is_array($results) && !isset($results['body']['error']) ) ? json_decode($results['body']['DefaultBranchCode'], TRUE) : FALSE;
        }

        /**
         * Get branches.
         * 
         * @param   string  $code       Branch code.
         * @param   string  $fields     Taxonomy fields.
         * @return  array
         * @since   1.0.0
         */
        public static function getBranches($code = '', $fields = 'all') {
            $args = [
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE,
                'fields'        => $fields
            ];

            if( !empty($code) ) : 
                $args['meta_key'] = 'branch_code';
                $args['meta_value'] = $code;
            endif;

            $branches = get_terms($args);

            return $branches ? $branches : FALSE;
        }

        /**
         * Update branch
         * 
         * @param   string  $name       Branch name.
         * @param   string  $code       Branch code.
         * @since   1.0.0
         */
        public static function updateBranch($name, $code) {
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
                update_term_meta($term['term_id'], 'branch_code', $code);
            endif;

            Wooccredo::addLog('Branch : '. $name .' synced');
        }

        /**
         * Get branches from api.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getBranchesFromAPI($url = '') {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = !empty($url) ? $url : $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/COBranch?\$Select=BranchCode,BranchName,Inactive,Address1,Address2,Address3,Address4,Address5,PostCode?access_token=". $accessToken['access_token'];
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

    Wooccredo_Branches::init();
endif;