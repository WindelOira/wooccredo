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

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/DefaultDepartmentCode?access_token=". $accessToken['access_token']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            $default = json_decode($result, TRUE);
            
            return !isset($default['error']) ? $default['DefaultDepartmentCode'] : '';
        }

        /**
         * Get departments
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getDepartments() {
            $departments = get_terms([
                'taxonomy'      => self::$taxonomy,
                'hide_empty'    => FALSE
            ]);

            return $departments ? $departments : FALSE;
        }

        /**
         * Update department.
         * 
         * @param   $name   Department name.
         * @param   $code   Department code.
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
                update_term_meta($term['term_id'], 'department_code', $code);
            endif;

            error_log($name .' synced');
        }

        /**
         * Get departments from api.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getDepartmentsFromAPI() {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/CODepartment?\$Select=DepartmentCode,DepartmentName,Inactive?access_token=". $accessToken['access_token']);
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

    Wooccredo_Departments::init();
endif;