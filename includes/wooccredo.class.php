<?php 

defined('ABSPATH') || exit;

if( !class_exists('Wooccredo') ) :
    class Wooccredo {
        /**
         * Init.
         * 
         * @since   1.0.0
         */
        public static function init() {
            self::defineConstants();

            register_activation_hook(__FILE__, __CLASS__ .'::activate');
            register_deactivation_hook(__FILE__, __CLASS__ .'::deactivate');

            add_action('plugins_loaded', __CLASS__ .'::pluginsLoaded');
            add_action('init', __CLASS__ .'::registerPostTypes');
            add_action('init', __CLASS__ .'::registerTaxonomies');

            if( self::isTokenExpired() ) :
                self::saveToken();
            endif;
        }

        /**
         * Activate.
         * 
         * @since   1.0.0
         */
        public static function activate() {
            // Set sync status for all to false.
            add_option('wc_wooccredo_synced', FALSE);

            // Set sync status for invoices to false.
            add_option('wc_wooccredo_invoices_synced', FALSE);

            // Set sync status for customers to false.
            add_option('wc_wooccredo_customers_synced', FALSE);

            // Set sync status for sales persons to false.
            add_option('wc_wooccredo_sales_persons_synced', FALSE);

            // Set sync status for sales areas to false.
            add_option('wc_wooccredo_sales_areas_synced', FALSE);

            // Set sync status for locations to false.
            add_option('wc_wooccredo_locations_synced', FALSE);

            // Set sync status for branches  to false.
            add_option('wc_wooccredo_branches_synced', FALSE);

            // Set sync status for departments to false.
            add_option('wc_wooccredo_departments_synced', FALSE);

            self::registerPostTypes();
            flush_rewrite_rules();
        }

        /**
         * Deactivate.
         * 
         * @since   1.0.0
         */
        public static function deactivate() {
            // Set sync status for all to false.
            update_option('wc_wooccredo_synced', FALSE);
            
            // Set sync status for invoices to false.
            update_option('wc_wooccredo_invoices_synced', FALSE);

            // Set sync status for customers to false.
            update_option('wc_wooccredo_customers_synced', FALSE);

            // Set sync status for sales persons to false.
            update_option('wc_wooccredo_sales_persons_synced', FALSE);

            // Set sync status for sales areas to false.
            update_option('wc_wooccredo_sales_areas_synced', FALSE);

            // Set sync status for locations to false.
            update_option('wc_wooccredo_locations_synced', FALSE);

            // Set sync status for branches to false.
            update_option('wc_wooccredo_branches_synced', FALSE);

            // Set sync status for departments to false.
            update_option('wc_wooccredo_departments_synced', FALSE);
        }

        /**
         * Check if logging is enabled.
         * 
         * @since   1.0.0
         */
        public static function loggingEnabled() {
            return get_option('wooccredo_logging') ? TRUE : FALSE;
        }

        /**
         * Add log.
         * 
         * @param   string  $log        Log message.
         * @param   string  $lavel      Level
         * @since   1.0.0
         */
        public static function addLog($log, $level = 'info') {
            if( !self::loggingEnabled() ) 
                return;

            $wcLogger = new WC_Logger();
            $wcLogger->add('wooccredo', $log, $level);
        }

        /**
         * Check if synced.
         * 
         * @return  boolean
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_synced') ? TRUE : FALSE;
        }

        /**
         * Get next sync.
         * 
         * @return  int
         * @since   1.0.0
         */
        public static function getNextSync() {
            return get_option('wc_wooccredo_next_sync') ? TRUE : FALSE;
        }

        /**
         * Update sync status
         * 
         * @param   string  $type       Type.
         * @param   string  $status     Status.
         * @since   1.0.0
         */
        public static function updateSyncStatus($type, $status) {
            update_option('wc_wooccredo_'. $type .'_sync_status', $status);
        }
        
        /**
         * Get sync status
         * 
         * @param   string  $type       Type.
         * @return  string
         * @since   1.0.0
         */
        public static function getSyncStatus($type) {
            return get_option('wc_wooccredo_'. $type .'_sync_status');
        }

        /**
         * Delete unsynced.
         * 
         * @since   1.0.0
         */
        public static function deleteUnsynced() {
            $syncStarted = get_option('wc_wooccredo_sync_started');
            $metaArgs = [
                'relation'      => 'AND',
                [
                    'key'       => 'sync_started',
                    'value'     => $syncStarted,
                    'compare'   => '<'
                ]
            ];

            // Delete unsynced invoices
            $invoices = get_posts([
                'post_type'         => Wooccredo_Invoice::$postType,
                'posts_per_page'    => -1,
                'fields'            => 'ids',
                'meta_query'        => $metaArgs
            ]);
            if( $invoices ) :
                foreach( $invoices as $invoice ) :
                    wp_delete_post($invoice->ID, TRUE);
                endforeach;
                wp_reset_postdata();
            endif;

            // Delete unsynced terms
            $terms = get_terms([
                'taxonomy'      => [
                    Wooccredo_Customers::$taxonomy,
                    Wooccredo_Sales_Persons::$taxonomy,
                    Wooccredo_Sales_Areas::$taxonomy,
                    Wooccredo_Locations::$taxonomy,
                    Wooccredo_Branches::$taxonomy,
                    Wooccredo_Departments::$taxonomy
                ],
                'hide_empty'    => FALSE,
                'meta_query'    => $metaArgs
            ]);
            if( $terms ) :
                foreach( $terms as $term ) :
                    wp_delete_term($term->term_id, $term->taxonomy);
                endforeach;
            endif;

            Wooccredo::addLog('Unsynced resources deleted...');
        }

        /**
         * Register post type.
         * 
         * @since   1.0.0
         */
        public static function registerPostTypes() {
            // Invoices post type.
            $invoicesArgs = [
                'labels'             => [
                    'name'               => _x( 'Invoices', 'post type general name', WOOCCREDO_TEXT_DOMAIN ),
                    'singular_name'      => _x( 'Invoice', 'post type singular name', WOOCCREDO_TEXT_DOMAIN ),
                    'menu_name'          => _x( 'Invoices', 'admin menu', WOOCCREDO_TEXT_DOMAIN ),
                    'name_admin_bar'     => _x( 'Invoice', 'add new on admin bar', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new'            => _x( 'Add New', 'Invoice', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new_item'       => __( 'Add New Invoice', WOOCCREDO_TEXT_DOMAIN ),
                    'new_item'           => __( 'New Invoice', WOOCCREDO_TEXT_DOMAIN ),
                    'edit_item'          => __( 'Edit Invoice', WOOCCREDO_TEXT_DOMAIN ),
                    'view_item'          => __( 'View Invoice', WOOCCREDO_TEXT_DOMAIN ),
                    'all_items'          => __( 'All Invoices', WOOCCREDO_TEXT_DOMAIN ),
                    'search_items'       => __( 'Search Invoices', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item_colon'  => __( 'Parent Invoices:', WOOCCREDO_TEXT_DOMAIN ),
                    'not_found'          => __( 'No Invoices found.', WOOCCREDO_TEXT_DOMAIN ),
                    'not_found_in_trash' => __( 'No Invoices found in Trash.', WOOCCREDO_TEXT_DOMAIN )
                ],
                'description'        => __( '', WOOCCREDO_TEXT_DOMAIN ),
                'public'             => FALSE,
                'publicly_queryable' => TRUE,
                'show_ui'            => FALSE,
                'show_in_menu'       => FALSE,
                'query_var'          => FALSE,
                'rewrite'            => [ 'slug' => 'wooccredo_invoice' ],
                'capability_type'    => 'post',
                'has_archive'        => FALSE,
                'hierarchical'       => FALSE,
                'menu_position'      => NULL,
                'supports'           => [ 'title' ]
            ];
            register_post_type( 'wooccredo_invoices', $invoicesArgs );
        }

        /**
         * Register taxonomies.
         * 
         * @since   1.0.0
         */
        public static function registerTaxonomies() {
            // Customers taxonomy.
            $customerArgs = [
                'hierarchical'      => FALSE,
                'labels'            => [
                    'name'              => _x( 'Customers', 'taxonomy general name', WOOCCREDO_TEXT_DOMAIN ),
                    'singular_name'     => _x( 'Customer', 'taxonomy singular name', WOOCCREDO_TEXT_DOMAIN ),
                    'search_items'      => __( 'Search Customers', WOOCCREDO_TEXT_DOMAIN ),
                    'all_items'         => __( 'All Customers', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item'       => __( 'Parent Customer', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item_colon' => __( 'Parent Customer:', WOOCCREDO_TEXT_DOMAIN ),
                    'edit_item'         => __( 'Edit Customer', WOOCCREDO_TEXT_DOMAIN ),
                    'update_item'       => __( 'Update Customer', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new_item'      => __( 'Add New Customer', WOOCCREDO_TEXT_DOMAIN ),
                    'new_item_name'     => __( 'New Customer Name', WOOCCREDO_TEXT_DOMAIN ),
                    'menu_name'         => __( 'Customer', WOOCCREDO_TEXT_DOMAIN ),
                ],
                'show_ui'           => FALSE,
                'show_admin_column' => FALSE,
                'query_var'         => FALSE,
                'rewrite'           => [ 'slug' => 'wooccredo_customer' ],
            ];
            register_taxonomy( 'wooccredo_customers', [ 'wooccredo_invoices' ], $customerArgs );

            // Sales persons taxonomy.
            $salesPersonsArgs = [
                'hierarchical'      => FALSE,
                'labels'            => [
                    'name'              => _x( 'Sales Persons', 'taxonomy general name', WOOCCREDO_TEXT_DOMAIN ),
                    'singular_name'     => _x( 'Sales Person', 'taxonomy singular name', WOOCCREDO_TEXT_DOMAIN ),
                    'search_items'      => __( 'Search Sales Persons', WOOCCREDO_TEXT_DOMAIN ),
                    'all_items'         => __( 'All Sales Persons', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item'       => __( 'Parent Sales Person', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item_colon' => __( 'Parent Sales Person:', WOOCCREDO_TEXT_DOMAIN ),
                    'edit_item'         => __( 'Edit Sales Person', WOOCCREDO_TEXT_DOMAIN ),
                    'update_item'       => __( 'Update Sales Person', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new_item'      => __( 'Add New Sales Person', WOOCCREDO_TEXT_DOMAIN ),
                    'new_item_name'     => __( 'New Sales Person Name', WOOCCREDO_TEXT_DOMAIN ),
                    'menu_name'         => __( 'Sales Person', WOOCCREDO_TEXT_DOMAIN ),
                ],
                'show_ui'           => FALSE,
                'show_admin_column' => FALSE,
                'query_var'         => FALSE,
                'rewrite'           => [ 'slug' => 'wooccredo_sales_person' ],
            ];
            register_taxonomy( 'wooccredo_sales_persons', [ 'wooccredo_invoices' ], $salesPersonsArgs );

            // Sales areas taxonomy.
            $salesAreasArgs = [
                'hierarchical'      => FALSE,
                'labels'            => [
                    'name'              => _x( 'Sales Areas', 'taxonomy general name', WOOCCREDO_TEXT_DOMAIN ),
                    'singular_name'     => _x( 'Sales Area', 'taxonomy singular name', WOOCCREDO_TEXT_DOMAIN ),
                    'search_items'      => __( 'Search Sales Areas', WOOCCREDO_TEXT_DOMAIN ),
                    'all_items'         => __( 'All Sales Areas', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item'       => __( 'Parent Sales Area', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item_colon' => __( 'Parent Sales Area:', WOOCCREDO_TEXT_DOMAIN ),
                    'edit_item'         => __( 'Edit Sales Area', WOOCCREDO_TEXT_DOMAIN ),
                    'update_item'       => __( 'Update Sales Area', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new_item'      => __( 'Add New Sales Area', WOOCCREDO_TEXT_DOMAIN ),
                    'new_item_name'     => __( 'New Sales Area Name', WOOCCREDO_TEXT_DOMAIN ),
                    'menu_name'         => __( 'Sales Area', WOOCCREDO_TEXT_DOMAIN ),
                ],
                'show_ui'           => FALSE,
                'show_admin_column' => FALSE,
                'query_var'         => FALSE,
                'rewrite'           => [ 'slug' => 'wooccredo_sales_area' ],
            ];
            register_taxonomy( 'wooccredo_sales_areas', [ 'wooccredo_invoices' ], $salesAreasArgs );

            // Locations taxonomy.
            $locationsArgs = [
                'hierarchical'      => FALSE,
                'labels'            => [
                    'name'              => _x( 'Locations', 'taxonomy general name', WOOCCREDO_TEXT_DOMAIN ),
                    'singular_name'     => _x( 'Location', 'taxonomy singular name', WOOCCREDO_TEXT_DOMAIN ),
                    'search_items'      => __( 'Search Locations', WOOCCREDO_TEXT_DOMAIN ),
                    'all_items'         => __( 'All Locations', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item'       => __( 'Parent Location', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item_colon' => __( 'Parent Location:', WOOCCREDO_TEXT_DOMAIN ),
                    'edit_item'         => __( 'Edit Location', WOOCCREDO_TEXT_DOMAIN ),
                    'update_item'       => __( 'Update Location', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new_item'      => __( 'Add New Location', WOOCCREDO_TEXT_DOMAIN ),
                    'new_item_name'     => __( 'New Location Name', WOOCCREDO_TEXT_DOMAIN ),
                    'menu_name'         => __( 'Location', WOOCCREDO_TEXT_DOMAIN ),
                ],
                'show_ui'           => FALSE,
                'show_admin_column' => FALSE,
                'query_var'         => FALSE,
                'rewrite'           => [ 'slug' => 'wooccredo_location' ],
            ];
            register_taxonomy( 'wooccredo_locations', [ 'wooccredo_invoices' ], $locationsArgs );

            // Branches taxonomy.
            $branchesArgs = [
                'hierarchical'      => FALSE,
                'labels'            => [
                    'name'              => _x( 'Branches', 'taxonomy general name', WOOCCREDO_TEXT_DOMAIN ),
                    'singular_name'     => _x( 'Branch', 'taxonomy singular name', WOOCCREDO_TEXT_DOMAIN ),
                    'search_items'      => __( 'Search Branches', WOOCCREDO_TEXT_DOMAIN ),
                    'all_items'         => __( 'All Branches', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item'       => __( 'Parent Branch', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item_colon' => __( 'Parent Branch:', WOOCCREDO_TEXT_DOMAIN ),
                    'edit_item'         => __( 'Edit Branch', WOOCCREDO_TEXT_DOMAIN ),
                    'update_item'       => __( 'Update Branch', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new_item'      => __( 'Add New Branch', WOOCCREDO_TEXT_DOMAIN ),
                    'new_item_name'     => __( 'New Branch Name', WOOCCREDO_TEXT_DOMAIN ),
                    'menu_name'         => __( 'Branch', WOOCCREDO_TEXT_DOMAIN ),
                ],
                'show_ui'           => FALSE,
                'show_admin_column' => FALSE,
                'query_var'         => FALSE,
                'rewrite'           => [ 'slug' => 'wooccredo_branch' ],
            ];
            register_taxonomy( 'wooccredo_branches', [ 'wooccredo_invoices' ], $branchesArgs );

            // Departments taxonomy.
            $departmentsArgs = [
                'hierarchical'      => FALSE,
                'labels'            => [
                    'name'              => _x( 'Departments', 'taxonomy general name', WOOCCREDO_TEXT_DOMAIN ),
                    'singular_name'     => _x( 'Department', 'taxonomy singular name', WOOCCREDO_TEXT_DOMAIN ),
                    'search_items'      => __( 'Search Departments', WOOCCREDO_TEXT_DOMAIN ),
                    'all_items'         => __( 'All Departments', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item'       => __( 'Parent Department', WOOCCREDO_TEXT_DOMAIN ),
                    'parent_item_colon' => __( 'Parent Department:', WOOCCREDO_TEXT_DOMAIN ),
                    'edit_item'         => __( 'Edit Department', WOOCCREDO_TEXT_DOMAIN ),
                    'update_item'       => __( 'Update Department', WOOCCREDO_TEXT_DOMAIN ),
                    'add_new_item'      => __( 'Add New Department', WOOCCREDO_TEXT_DOMAIN ),
                    'new_item_name'     => __( 'New Department Name', WOOCCREDO_TEXT_DOMAIN ),
                    'menu_name'         => __( 'Department', WOOCCREDO_TEXT_DOMAIN ),
                ],
                'show_ui'           => FALSE,
                'show_admin_column' => FALSE,
                'query_var'         => FALSE,
                'rewrite'           => [ 'slug' => 'wooccredo_department' ],
            ];
            register_taxonomy( 'wooccredo_departments', [ 'wooccredo_invoices' ], $departmentsArgs );
        }

        /**
         * Define.
         * 
         * @since   1.0.0
         */
        private static function define($key, $value) {
            !defined($key) ? define($key, $value) : '';
        }

        /**
         * Define constants.
         * 
         * @since   1.0.0
         */
        private static function defineConstants() {
            self::define('WOOCCREDO_VERSION', '1.0.0');
            self::define('WOOCCREDO_ABSPATH', dirname(WOOCCREDO_PLUGIN_FILE) .'/');
            self::define('WOOCCREDO_PLUGIN_URL', plugins_url('wooccredo/'));
        }

        /**
         * Plugins loaded.
         * 
         * @since   1.0.0
         */
        public static function pluginsLoaded() {
            include_once WOOCCREDO_ABSPATH .'includes/admin/wooccredo-settings.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/admin/wooccredo-order-metaboxes.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/admin/wooccredo-order-actions.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/admin/wooccredo-invoices-list.class.php';

            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-background-process.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-product.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-customers.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-sales-persons.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-sales-areas.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-locations.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-branches.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-departments.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-invoice.class.php';
            include_once WOOCCREDO_ABSPATH .'includes/wooccredo-invoices.class.php';
        }

        /**
         * Get option.
         * 
         * @param   string  $key        Option key.
         * @return  mixed
         * @since   1.0.0
         */
        public static function getOption($key) {
            return get_option('wooccredo_'. $key);
        }

        /**
         * Set option.
         * 
         * @param   string  $key        Option key.
         * @param   string  $value      Option value.
         * @since   1.0.0
         */
        public static function setOption($key, $value) {
            update_option('wooccredo_'. $key, $value);
        }

        /**
         * Generate token.
         * 
         * @return  array/boolean
         * @since   1.0.0
         */
        public static function generateToken() {
            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http. "://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/oauth2/v1/token";
            $args = [
                'headers'   => [
                    'OData-Version: 4.0',
                    'Accept: application/json',
                    'Content-Type: application/json'
                ],
                'body'      => 'grant_type=password&client_id='. self::getOption('client_id') .'&username='. self::getOption('username') .'&password='. self::getOption('password')
            ];
            $results = wp_remote_post($url, $args);

            return !is_wp_error($results) && is_array($results) ? json_decode($results['body'], TRUE) : FALSE;
        }

        /**
         * Check if token expired.
         * 
         * @return  boolean
         * @since   1.0.0
         */
        public static function isTokenExpired() {
            $token = get_option('wc_wooccredo_settings_token');
            $now = strtotime('now');

            if( !$token ) :
                return FALSE;
            endif;

            if( isset($token['error']) ) :
                return FALSE;
            endif;

            $tokenLife = $token['timestamp'] + $token['expires_in'];

            return $now > $tokenLife ? TRUE : FALSE;
        }

        /**
         * Get token.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getToken() {
            $token = get_option('wc_wooccredo_settings_token');

            if( self::isTokenExpired() ) :
                return self::saveToken();
            endif;

            return $token;
        }

        /**
         * Save token.
         * 
         * @since   1.0.0
         */
        public static function saveToken() {
            $generatedToken = self::generateToken();

            if( !$generatedToken )
                return FALSE;

            if( !isset($generatedToken['error']) ) :
                $generatedToken['timestamp'] = strtotime('now');
                $generatedToken['refreshed'] = 1;
            endif;
            
            // Update tokens.
            update_option('wc_wooccredo_settings_token', $generatedToken);

            // Set sync status for all to false.
            update_option('wc_wooccredo_synced', FALSE);

            // Set sync status for invoices to false.
            update_option('wc_wooccredo_invoices_synced', FALSE);
            
            // Set sync status for customers to false.
            update_option('wc_wooccredo_customers_synced', FALSE);

            // Set sync status for sales persons to false.
            update_option('wc_wooccredo_sales_persons_synced', FALSE);

            // Set sync status for sales areas to false.
            update_option('wc_wooccredo_sales_areas_synced', FALSE);

            // Set sync status for locations to false.
            update_option('wc_wooccredo_locations_synced', FALSE);

            // Set sync status for branches to false.
            update_option('wc_wooccredo_branches_synced', FALSE);

            // Set sync status for departments to false.
            update_option('wc_wooccredo_departments_synced', FALSE);

            return $generatedToken;
        }
    }

    Wooccredo::init();
endif;