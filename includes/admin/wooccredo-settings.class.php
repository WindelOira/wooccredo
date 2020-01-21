<?php

defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Admin_Settings') ) :
    class Wooccredo_Admin_Settings {
        /**
         * Init.
         * 
         * @since   1.0.0
         */
        public static function init() {
            add_action('admin_menu', __CLASS__ .'::adminMenu');
            add_action('admin_init', __CLASS__ .'::adminInit');
            add_action('admin_enqueue_scripts', __CLASS__ .'::adminEnqueueScripts');
            add_action('admin_notices', __CLASS__ .'::adminNotices');
            add_action('updated_option', __CLASS__ .'::optionUpdated', 10, 3);
            add_action('init', __CLASS__ .'::processHandlers');
        }

        /**
         * Admin menu.
         * 
         * @since   1.0.0
         */
        public static function adminMenu() {
            add_menu_page(
                __('Wooccredo', WOOCCREDO_TEXT_DOMAIN),
                __('Wooccredo', WOOCCREDO_TEXT_DOMAIN), 
                'manage_options', 
                'wooccredo', 
                __CLASS__ .'::settingsPage', 
                WOOCCREDO_PLUGIN_URL .'/assets/images/accredo.png', 
                58
            );
        }

        /**
         * Admin init.
         * 
         * @since   1.0.0
         */
        public static function adminInit() {
            register_setting('wooccredo', 'wooccredo_configured');
            register_setting('wooccredo', 'wooccredo_ssl');
            register_setting('wooccredo', 'wooccredo_host');
            register_setting('wooccredo', 'wooccredo_port');
            register_setting('wooccredo', 'wooccredo_company');
            register_setting('wooccredo', 'wooccredo_client_id');
            register_setting('wooccredo', 'wooccredo_username');
            register_setting('wooccredo', 'wooccredo_password');
            register_setting('wooccredo', 'wooccredo_logging');
        }

        /**
         * Admin notices.
         * 
         * @since   1.0.0
         */
        public static function adminNotices() {
            $token = get_option('wc_wooccredo_settings_token');

            // Notice if settings updated.
            if( isset($_GET['settings-updated']) && 
                $_GET['settings-updated'] == 'true' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Wooccredo settings updated.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if settings was configured and invalid authentication.
            if( Wooccredo::getOption('configured') && 
                ($token && isset($token['error'])) ) :
                Wooccredo::updateSyncStatus('invoices', '');
                Wooccredo::updateSyncStatus('customers', '');
                Wooccredo::updateSyncStatus('sales_persons', '');
                Wooccredo::updateSyncStatus('sales_areas', '');
                Wooccredo::updateSyncStatus('locations', '');
                Wooccredo::updateSyncStatus('branches', '');
                Wooccredo::updateSyncStatus('departments', '');
            ?>
                <div class="notice notice-error">
                    <p><?php _e('Client authentication failed. Please make sure you entered correct Accredo credentials.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if invoices sync is processing in background.
            if( Wooccredo::getSyncStatus('invoices') == 'processing' ) :
            ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e('Invoices sync is in process.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            elseif( Wooccredo::getSyncStatus('invoices') == 'done' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Invoices sync done.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if customers sync is processing in background.
            if( Wooccredo::getSyncStatus('customers') == 'processing' ) :
            ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e('Customers sync is in process.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            elseif( Wooccredo::getSyncStatus('customers') == 'done' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Customers sync done.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if customers sync is processing in background.
            if( Wooccredo::getSyncStatus('sales_persons') == 'processing' ) :
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e('Sales persons sync is in process.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            elseif( Wooccredo::getSyncStatus('sales_persons') == 'done' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Sales persons sync done.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if customers sync is processing in background.
            if( Wooccredo::getSyncStatus('sales_areas') == 'processing' ) :
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e('Sales areas sync is in process.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            elseif( Wooccredo::getSyncStatus('sales_areas') == 'done' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Sales areas sync done.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if customers sync is processing in background.
            if( Wooccredo::getSyncStatus('locations') == 'processing' ) :
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e('Locations sync is in process.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            elseif( Wooccredo::getSyncStatus('locations') == 'done' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Locations sync done.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if customers sync is processing in background.
            if( Wooccredo::getSyncStatus('branches') == 'processing' ) :
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e('Branches sync is in process.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            elseif( Wooccredo::getSyncStatus('branches') == 'done' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Branches sync done.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;

            // Notice if customers sync is processing in background.
            if( Wooccredo::getSyncStatus('departments') == 'processing' ) :
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e('Departments sync is in process.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            elseif( Wooccredo::getSyncStatus('departments') == 'done' ) :
            ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Departments sync done.', WOOCCREDO_TEXT_DOMAIN); ?></p>
                </div>
            <?php
            endif;
        }

        /**
         * Settings page.
         * 
         * @since   1.0.0
         */
        public static function settingsPage() {
            $token = get_option('wc_wooccredo_settings_token');
        ?>
            <div class="wrap wooccredo">
                <h1><?php _e('Wooccredo', WOOCCREDO_TEXT_DOMAIN); ?></h1>
                <p><?php _e(sprintf('Settings for your Accredo account including client ID and account informations. Here is the <a href="%s" target="_blank">guide</a> on how you can get your account credentials. NOTE: All fields are required.', 'https://accredo.co.nz/webhelp/WebClients.htm'), WOOCCREDO_TEXT_DOMAIN); ?></p>

                <form method="post" action="options.php">
                    <?php settings_fields('wooccredo'); ?>
                    <?php do_settings_sections('wooccredo'); ?>
                    <input type="hidden" name="wooccredo_configured" value="1">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Use SSL', WOOCCREDO_TEXT_DOMAIN); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wooccredo_ssl" value="1" <?php checked(1, esc_attr(get_option('wooccredo_ssl')), TRUE); ?>/> 
                                </label>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Hostname', WOOCCREDO_TEXT_DOMAIN); ?>
                            </th>
                            <td>
                                <input type="text" name="wooccredo_host" value="<?php echo esc_attr(get_option('wooccredo_host')); ?>" required/>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Port', WOOCCREDO_TEXT_DOMAIN); ?>
                            </th>
                            <td>
                                <input type="text" name="wooccredo_port" value="<?php echo esc_attr(get_option('wooccredo_port')); ?>" required/>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Company', WOOCCREDO_TEXT_DOMAIN); ?>
                            </th>
                            <td>
                                <input type="text" name="wooccredo_company" value="<?php echo esc_attr(get_option('wooccredo_company')); ?>" required/>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Client ID', WOOCCREDO_TEXT_DOMAIN); ?>
                            </th>
                            <td>
                                <input type="text" name="wooccredo_client_id" value="<?php echo esc_attr(get_option('wooccredo_client_id')); ?>" required/>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Username', WOOCCREDO_TEXT_DOMAIN); ?>
                            </th>
                            <td>
                                <input type="text" name="wooccredo_username" value="<?php echo esc_attr(get_option('wooccredo_username')); ?>" required/>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Password', WOOCCREDO_TEXT_DOMAIN); ?>
                            </th>
                            <td>
                                <input type="password" name="wooccredo_password" value="<?php echo esc_attr(get_option('wooccredo_password')); ?>" required/>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="wooccredo_logging" value="1" <?php checked(1, esc_attr(get_option('wooccredo_logging')), TRUE); ?>/> 
                                    <?php _e('Enable Logging?', WOOCCREDO_TEXT_DOMAIN); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button(); ?>
                </form>
            </div>
        <?php
        }

        /**
         * Options updated.
         * 
         * @param   $option     Option name.
         * @param   $oldValue   Old option value.
         * @param   $value      New option value.
         * @since   1.0.0
         */
        public static function optionUpdated($option, $oldValue, $value) {
            $settings = ['wooccredo_client_id', 'wooccredo_username', 'wooccredo_password'];
            $token = get_option('wc_wooccredo_settings_token');

            if( in_array($option, $settings) && 
                $value != $oldValue ) :
                Wooccredo::saveToken();
            endif;
        }

        /**
         * Background process handlers.
         * 
         * @since   1.0.0
         */
        public static function processHandlers() {
            $now = time();
            $token = Wooccredo::getToken();
            $tokenRefreshed = isset($token['refreshed']) ? $token['refreshed'] = TRUE : FALSE;
            $backgroundProcess = new Wooccredo_Background_Process();            

            if( !Wooccredo::isSynced() && 
                ( ( $token && $tokenRefreshed && empty($token['old_token']) ) || 
                  ( $token && !empty($token['old_token']) && $now > Wooccredo::getNextSync() ) ) ) :
                // Update tokens.
                $token['refreshed'] = '';
                update_option('wc_wooccredo_settings_token', $token);

                // Update sync status.
                update_option('wc_wooccredo_sync_started', time());
                update_option('wc_wooccredo_next_sync', strtotime('tomorrow'));
                update_option('wc_wooccredo_synced', TRUE);

                // Update invoices sync status
                Wooccredo::updateSyncStatus('invoices', 'processing', 'processing');
                // Update customers sync status
                Wooccredo::updateSyncStatus('customers', 'processing', 'processing');
                // Update sales persons sync status
                Wooccredo::updateSyncStatus('sales_persons', 'processing', 'processing');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('sales_areas', 'processing', 'processing');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('locations', 'processing', 'processing');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('branches', 'processing', 'processing');
                // Update invoices sync status
                Wooccredo::updateSyncStatus('departments', 'processing', 'processing');
                
                if( !Wooccredo_Invoices::isSynced() ) :
                    update_option('wc_wooccredo_invoices_synced', TRUE);
                    $invoices = Wooccredo_Invoices::getInvoicesFromAPI();

                    $invoicesCounter = 0;
                    do {
                        $invoicesCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'add_update_invoice',
                            'data'  => $invoices['value'][($invoicesCounter - 1)]
                        ]);

                        if( $invoicesCounter == count($invoices['value']) && 
                            isset($invoices['@odata.nextLink']) ) :
                            $invoices = Wooccredo_Invoices::getInvoicesFromApi($invoices['@odata.nextLink']);

                            $invoicesCounter = 0;
                        endif;
                    } while( !is_wp_error($invoices) && $invoicesCounter < count($invoices['value']) );
                endif;

                if( !Wooccredo_Customers::isSynced() ) :
                    update_option('wc_wooccredo_customers_synced', TRUE);

                    $customers = Wooccredo_Customers::getCustomersFromAPI();

                    $customersCounter = 0;
                    do {
                        $customersCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'add_update_customer',
                            'data'  => $customers['value'][($customersCounter - 1)]
                        ]);

                        if( $customersCounter == count($customers['value']) && 
                            isset($customers['@odata.nextLink']) ) :
                            $customers = Wooccredo_Invoices::getCustomersFromAPI($customers['@odata.nextLink']);

                            $customersCounter = 0;
                        endif;
                    } while( !is_wp_error($customers) && $customersCounter < count($customers['value']) );
                endif;

                if( !Wooccredo_Sales_Persons::isSynced() ) :
                    update_option('wc_wooccredo_sales_persons_synced', TRUE);

                    $salesPersons = Wooccredo_Sales_Persons::getSalesPersonsFromAPI();

                    $salesPersonsCounter = 0;
                    do {
                        $salesPersonsCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'add_update_sales_person',
                            'data'  => $salesPersons['value'][($salesPersonsCounter - 1)]
                        ]);

                        if( $salesPersonsCounter == count($salesPersons['value']) && 
                            isset($salesPersons['@odata.nextLink']) ) :
                            $salesPersons = Wooccredo_Invoices::getSalesPersonsFromAPI($salesPersons['@odata.nextLink']);

                            $salesPersonsCounter = 0;
                        endif;
                    } while( !is_wp_error($salesPersons) && $salesPersonsCounter < count($salesPersons['value']) );
                endif;
                
                if( !Wooccredo_Sales_Areas::isSynced() ) :
                    update_option('wc_wooccredo_sales_areas_synced', TRUE);

                    $salesAreas = Wooccredo_Sales_Areas::getSalesAreasFromAPI();

                    $salesAreasCounter = 0;
                    do {
                        $salesAreasCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'add_update_sales_area',
                            'data'  => $salesAreas['value'][($salesAreasCounter - 1)]
                        ]);

                        if( $salesAreasCounter == count($salesAreas['value']) && 
                            isset($salesAreas['@odata.nextLink']) ) :
                            $salesAreas = Wooccredo_Invoices::getSalesAreasFromAPI($salesAreas['@odata.nextLink']);

                            $salesAreasCounter = 0;
                        endif;
                    } while( !is_wp_error($salesAreas) && $salesAreasCounter < count($salesAreas['value']) );
                endif;

                if( !Wooccredo_Locations::isSynced() ) :
                    update_option('wc_wooccredo_locations_synced', TRUE);

                    $locations = Wooccredo_Locations::getLocationsFromAPI();
                    
                    $locationsCounter = 0;
                    do {
                        $locationsCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'add_update_location',
                            'data'  => $locations['value'][($locationsCounter - 1)]
                        ]);

                        if( $locationsCounter == count($locations['value']) && 
                            isset($locations['@odata.nextLink']) ) :
                            $locations = Wooccredo_Invoices::getLocationsFromAPI($locations['@odata.nextLink']);

                            $locationsCounter = 0;
                        endif;
                    } while( !is_wp_error($locations) && $locationsCounter < count($locations['value']) );
                endif;

                if( !Wooccredo_Branches::isSynced() ) :
                    update_option('wc_wooccredo_branches_synced', TRUE);

                    $branches = Wooccredo_Branches::getBranchesFromAPI();

                    $branchesCounter = 0;
                    do {
                        $branchesCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'add_update_branch',
                            'data'  => $branches['value'][($branchesCounter - 1)]
                        ]);

                        if( $branchesCounter == count($branches['value']) && 
                            isset($branches['@odata.nextLink']) ) :
                            $branches = Wooccredo_Invoices::getDepartmentsFromAPI($branches['@odata.nextLink']);

                            $branchesCounter = 0;
                        endif;
                    } while( !is_wp_error($branches) && $branchesCounter < count($branches['value']) );
                endif;

                if( !Wooccredo_Departments::isSynced() ) :
                    update_option('wc_wooccredo_departments_synced', TRUE);

                    $departments = Wooccredo_Departments::getDepartmentsFromAPI();

                    $departmentsCounter = 0;
                    do {
                        $departmentsCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'add_update_department',
                            'data'  => $departments['value'][($departmentsCounter - 1)]
                        ]);

                        if( $departmentsCounter == count($departments['value']) && 
                            isset($departments['@odata.nextLink']) ) :
                            $departments = Wooccredo_Invoices::getDepartmentsFromAPI($departments['@odata.nextLink']);

                            $departmentsCounter = 0;
                        endif;
                    } while( !is_wp_error($departments) && $departmentsCounter < count($departments['value']) );
                endif;

                if( 0 < count($backgroundProcess->tasks()) ) :
                    Wooccredo::addLog('Sync started...');

                    $backgroundProcess->save()->dispatch();
                endif;
            endif;

            // Delete unsynced invoices
            if( Wooccredo::getSyncStatus('invoices') == 'done' ) :
                Wooccredo::updateSyncStatus('invoices', '', 'deleted');
                $unsyncedInvoices = Wooccredo_Invoices::getUnsyncedInvoices();

                if( is_array($unsyncedInvoices) && 0 < count($unsyncedInvoices) ) :
                    $unsyncedInvoicesCounter = 0;
                    do {
                        $unsyncedInvoicesCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'delete_invoice',
                            'data'  => $unsyncedInvoices[($unsyncedInvoicesCounter - 1)]
                        ]);

                    } while( $unsyncedInvoicesCounter < count($unsyncedInvoices) );
                    $backgroundProcess->save()->dispatch();
                endif;
            endif;

            // Delete unsynced customers
            if( Wooccredo::getSyncStatus('customers') == 'done' ) :
                Wooccredo::updateSyncStatus('customers', '', 'deleted');
                $unsyncedCustomers = Wooccredo_Customers::getUnsyncedCustomers();

                if( is_array($unsyncedCustomers) && 0 < count($unsyncedCustomers) ) :
                    $unsyncedCustomersCounter = 0;
                    do {
                        $unsyncedCustomersCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'delete_customer',
                            'data'  => $unsyncedCustomers[($unsyncedCustomersCounter - 1)]
                        ]);

                    } while( $unsyncedCustomersCounter < count($unsyncedCustomers) );
                    $backgroundProcess->save()->dispatch();
                endif;
            endif;

            // Delete unsynced sales persons
            if( Wooccredo::getSyncStatus('sales_persons') == 'done' ) :
                Wooccredo::updateSyncStatus('sales_persons', '', 'deleted');
                $unsyncedSalesPersons = Wooccredo_Sales_Persons::getUnsyncedSalesPersons();

                if( is_array($unsyncedSalesPersons) && 0 < count($unsyncedSalesPersons) ) :
                    $unsyncedSalesPersonsCounter = 0;
                    do {
                        $unsyncedSalesPersonsCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'delete_sales_person',
                            'data'  => $unsyncedSalesPersons[($unsyncedSalesPersonsCounter - 1)]
                        ]);

                    } while( $unsyncedSalesPersonsCounter < count($unsyncedSalesPersons) );
                    $backgroundProcess->save()->dispatch();
                endif;
            endif;

            // Delete unsynced sales areas
            if( Wooccredo::getSyncStatus('sales_areas') == 'done' ) :
                Wooccredo::updateSyncStatus('sales_areas', '', 'deleted');
                $unsyncedSalesAreas = Wooccredo_Sales_Areas::getUnsyncedSalesAreas();

                if( is_array($unsyncedSalesAreas) && 0 < count($unsyncedSalesAreas) ) :
                    $unsyncedSalesAreasCounter = 0;
                    do {
                        $unsyncedSalesAreasCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'delete_sales_area',
                            'data'  => $unsyncedSalesAreas[($unsyncedSalesAreasCounter - 1)]
                        ]);

                    } while( $unsyncedSalesAreasCounter < count($unsyncedSalesAreas) );
                    $backgroundProcess->save()->dispatch();
                endif;
            endif;

            // Delete unsynced locations
            if( Wooccredo::getSyncStatus('locations') == 'done' ) :
                Wooccredo::updateSyncStatus('locations', '', 'deleted');
                $unsyncedLocations = Wooccredo_Locations::getUnsyncedLocations();

                if( is_array($unsyncedLocations) && 0 < count($unsyncedLocations) ) :
                    $unsyncedLocationsCounter = 0;
                    do {
                        $unsyncedLocationsCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'delete_location',
                            'data'  => $unsyncedLocations[($unsyncedLocationsCounter - 1)]
                        ]);

                    } while( $unsyncedLocationsCounter < count($unsyncedLocations) );
                    $backgroundProcess->save()->dispatch();
                endif;
            endif;

            // Delete unsynced branches
            if( Wooccredo::getSyncStatus('branches') == 'done' ) :
                Wooccredo::updateSyncStatus('branches', '', 'deleted');
                $unsyncedBranches = Wooccredo_Branches::getUnsyncedBranches();

                if( is_array($unsyncedBranches) && 0 < count($unsyncedBranches) ) :
                    $unsyncedBranchesCounter = 0;
                    do {
                        $unsyncedBranchesCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'delete_branch',
                            'data'  => $unsyncedBranches[($unsyncedBranchesCounter - 1)]
                        ]);

                    } while( $unsyncedBranchesCounter < count($unsyncedBranches) );
                    $backgroundProcess->save()->dispatch();
                endif;
            endif;

            // Delete unsynced departments
            if( Wooccredo::getSyncStatus('departments') == 'done' ) :
                Wooccredo::updateSyncStatus('departments', '', 'deleted');
                $unsyncedDepartments = Wooccredo_Departments::getUnsyncedDepartments();

                if( is_array($unsyncedDepartments) && 0 < count($unsyncedDepartments) ) :
                    $unsyncedDepartmentsCounter = 0;
                    do {
                        $unsyncedDepartmentsCounter++;

                        $backgroundProcess->push_to_queue([
                            'task'  => 'delete_department',
                            'data'  => $unsyncedDepartments[($unsyncedDepartmentsCounter - 1)]
                        ]);

                    } while( $unsyncedDepartmentsCounter < count($unsyncedDepartments) );
                    $backgroundProcess->save()->dispatch();
                endif;
            endif;
        }

        /**
         * Enqueue admin scripts & styles.
         * 
         * @param   $hook       Hook.
         * @since   1.0.0
         */
        public static function adminEnqueueScripts($hook) {
            wp_enqueue_style('wooccredo-admin', WOOCCREDO_PLUGIN_URL .'assets/css/admin.css', []);

            wp_enqueue_script('wooccredo-admin', WOOCCREDO_PLUGIN_URL .'assets/js/admin.js', ['jquery', 'jquery-ui-tabs'], WOOCCREDO_VERSION, TRUE);
        }
    }

    Wooccredo_Admin_Settings::init();
endif;