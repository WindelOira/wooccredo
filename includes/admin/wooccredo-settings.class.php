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
            register_setting('wooccredo', 'wooccredo_company');
            register_setting('wooccredo', 'wooccredo_client_id');
            register_setting('wooccredo', 'wooccredo_username');
            register_setting('wooccredo', 'wooccredo_password');
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
            ?>
                <div class="notice notice-error">
                    <p><?php _e('Client authentication failed. Please make sure you entered correct Accredo credentials.', WOOCCREDO_TEXT_DOMAIN); ?></p>
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
            var_dump($token);
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
            $accessToken = Wooccredo::getToken();
            if( ($accessToken && !isset($accessToken['error'])) ) :
                $backgroundProcess = new Wooccredo_Background_Process();

                if( !Wooccredo_Customers::isSynced() ) :
                    update_option('wc_wooccredo_customers_synced', TRUE);

                    $customers = Wooccredo_Customers::getCustomersFromAPI();
                    if( $customers['value'] ) :
                        foreach( $customers['value'] as $customer ) :
                            $backgroundProcess->push_to_queue([
                                'task'  => 'add_update_customer',
                                'data'  => $customer
                            ]);
                        endforeach;
                    endif;
                endif;

                if( !Wooccredo_Sales_Persons::isSynced() ) :
                    update_option('wc_wooccredo_sales_persons_synced', TRUE);

                    $salesPersons = Wooccredo_Sales_Persons::getSalesPersonsFromAPI();
                    if( $salesPersons['value'] ) :
                        foreach( $salesPersons['value'] as $salesPerson ) :
                            $backgroundProcess->push_to_queue([
                                'task'  => 'add_update_sales_person',
                                'data'  => $salesPerson
                            ]);
                        endforeach;
                    endif;
                endif;
                
                if( !Wooccredo_Sales_Areas::isSynced() ) :
                    update_option('wc_wooccredo_sales_areas_synced', TRUE);

                    $salesAreas = Wooccredo_Sales_Areas::getSalesAreasFromAPI();
                    if( $salesAreas['value'] ) :
                        foreach( $salesAreas['value'] as $salesArea ) :
                            $backgroundProcess->push_to_queue([
                                'task'  => 'add_update_sales_area',
                                'data'  => $salesArea
                            ]);
                        endforeach;
                    endif;
                endif;

                if( !Wooccredo_Locations::isSynced() ) :
                    update_option('wc_wooccredo_locations_synced', TRUE);

                    $locations = Wooccredo_Locations::getLocationsFromAPI();
                    if( $locations['value'] ) :
                        foreach( $locations['value'] as $location ) :
                            $backgroundProcess->push_to_queue([
                                'task'  => 'add_update_location',
                                'data'  => $location
                            ]);
                        endforeach;
                    endif;
                endif;

                if( !Wooccredo_Branches::isSynced() ) :
                    update_option('wc_wooccredo_branches_synced', TRUE);

                    $branches = Wooccredo_Branches::getBranchesFromAPI();
                    if( $branches['value'] ) :
                        foreach( $branches['value'] as $branch ) :
                            $backgroundProcess->push_to_queue([
                                'task'  => 'add_update_branch',
                                'data'  => $branch
                            ]);
                        endforeach;
                    endif;
                endif;

                if( !Wooccredo_Departments::isSynced() ) :
                    update_option('wc_wooccredo_departments_synced', TRUE);

                    $departments = Wooccredo_Departments::getDepartmentsFromAPI();
                    if( $departments['value'] ) :
                        foreach( $departments['value'] as $department ) :
                            $backgroundProcess->push_to_queue([
                                'task'  => 'add_update_department',
                                'data'  => $department
                            ]);
                        endforeach;
                    endif;
                endif;

                if( 0 < count($backgroundProcess->tasks()) ) :
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