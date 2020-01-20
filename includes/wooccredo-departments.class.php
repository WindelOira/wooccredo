<?php

defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Departments') ) :
    class Wooccredo_Departments {
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
            self::$taxonomy = 'wooccredo_departments';
        }

        /**
         * Check if synced.
         * 
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_departments_synced') ? TRUE : FALSE;
        }

        /**
         * Get default department.
         * 
         * @return  string
         * @since   1.0.0
         */
        public static function getDefaultDepartment() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/DefaultDepartmentCode?access_token=". $accessToken['access_token'];
            $args = [
                'headers'   => [
                    'OData-Version: 4.0',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ];
            $results = wp_remote_get($url, $args);

            return !is_wp_error($results) && ( is_array($results) && !isset($results['body']['error']) ) ? json_decode($results['body']['DefaultDepartmentCode'], TRUE) : FALSE;
        }

        /**
         * Get departments
         * 
         * @param   string  $code       Department code.
         * @param   string  $fields     Taxonomy fields.
         * @return  array
         * @since   1.0.0
         */
        public static function getDepartments($code = '', $fields = 'all') {
            $args = [
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE,
                'fields'        => $fields
            ];

            if( !empty($code) ) : 
                $args['meta_key'] = 'department_code';
                $args['meta_value'] = $code;
            endif;

            $departments = get_terms($args);

            return $departments ? $departments : FALSE;
        }

        /**
         * Update department.
         * 
         * @param   string  $name       Department name.
         * @param   string  $code       Department code.
         * @since   1.0.0
         */
        public static function updateDepartment($name, $code) {
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
                update_term_meta($term['term_id'], 'department_code', $code);
            endif;

            Wooccredo::addLog('Department : '. $name .' synced');
        }

        /**
         * Get departments from api.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getDepartmentsFromAPI($url = '') {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = !empty($url) ? $url : $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/CODepartment?\$Select=DepartmentCode,DepartmentName,Inactive?access_token=". $accessToken['access_token'];
            $args = [
                'headers'   => [
                    'OData-Version: 4.0',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ];
            $results = wp_remote_get($url, $args);

            return is_array($results) && !is_wp_error($results) ? json_decode($results['body'], TRUE) : FALSE;
        }
    }

    Wooccredo_Departments::init();
endif;