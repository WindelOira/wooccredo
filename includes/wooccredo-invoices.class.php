<?php 
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Invoices') ) :
    class Wooccredo_Invoices {
        /**
         * Instance.
         * 
         * @since   1.0.0
         */
        static $instance;

        /**
         * List object
         * 
         * @var     Wooccredo_Invoices_List
         * @since   1.0.0
         */
        public static $listObject;

        /**
         * Init.
         * 
         * @since 1.0.0
         */
        public static function init() {
            self::getInstance();

            add_filter('set-screen-option', __CLASS__ .'::setScreen', 10, 3);
            add_action('admin_menu', __CLASS__ .'::adminMenu');
        }

        /**
         * Check if synced.
         * 
         * @since   1.0.0
         */
        public static function isSynced() {
            return get_option('wc_wooccredo_invoices_synced') ? TRUE : FALSE;
        }

        /**
         * Set screen.
         * 
         * @since   1.0.0
         */
        public static function setScreen($status, $option, $value) {
            return $value;
        }

        /**
         * Admin menu.
         * 
         * @since   1.0.0
         */
        public static function adminMenu() {
            $token = get_option('wc_wooccredo_settings_token');

            if( Wooccredo::getOption('configured') && 
                ($token && !isset($token['error'])) ) :
                $hook = add_submenu_page(
                    'wooccredo',
                    __('Invoices', WOOCCREDO_TEXT_DOMAIN),
                    __('Invoices', WOOCCREDO_TEXT_DOMAIN),
                    'manage_options', 
                    'wooccredo-invoices', 
                    __CLASS__ .'::invoicesPage'
                );
                // add_action("load-$hook", __CLASS__ .'::screenOption');
            endif;
        }


        /**
         * Invoices page callback
         * 
         * @since   1.0.0
         */
        public static function invoicesPage() {
            ?>
            <div class="wrap wooccredo">
                <?php 
                if( (isset($_GET['action']) && $_GET['action'] == 'view') && $_REQUEST['invoice'] ) : 
                    $invoice = Wooccredo_Invoice::getInvoice($_REQUEST['invoice']);
                ?>
                <h1><?php _e('Invoice', WOOCCREDO_TEXT_DOMAIN); ?></h1>
                <?php 
                    Wooccredo_Invoice::view();
                else : 
                    self::$listObject = new Wooccredo_Invoices_List();
                    self::$listObject->prepare_items();
                ?>
                <h1><?php _e('Invoices', WOOCCREDO_TEXT_DOMAIN); ?></h1>
                <form method="post">
                    <input type="hidden" name="page" value="wooccredo-invoices">
                    <?php self::$listObject->search_box('Search by Document No.', 'search_doc_num'); ?>
                    <?php self::$listObject->display(); ?>
                </form>
                <?php endif; ?>
            </div>
        <?php
        }

        /**
         * Screen option.
         * 
         * @since   1.0.0
         */
        public static function screenOption() {
            self::$listObject = new Wooccredo_Invoices_List();
        }

        /** 
         * Get invoices from api.
         * 
         * @since   1.0.0
         * @return  array
         */
        public static function getInvoicesFromAPI($url = '') {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = !empty($url) ? $url : $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/INInvoiceList?\$Select=DocumentID,OrderNo,CustomerCode,DocumentDate,DeliveryDate,PrintStatus,PostStatus,PackingSlipNo,DocumentNo,GrossAmount,SalesPersonCode,SalesAreaCode,DefaultLocationCode,BranchCode,DepartmentCode?access_token=". $accessToken['access_token'];
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
         * Send invoice.
         * 
         * @param   $order    \WC_Order
         * @since   1.0.0
         */
        public static function sendInvoice($order) {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $customer = @$_REQUEST['wooccredo_customer'];
            $salesPerson = @$_REQUEST['wooccredo_sales_person'];
            $salesArea = @$_REQUEST['wooccredo_sales_area'];
            $location = @$_REQUEST['wooccredo_location'];
            $branch = @$_REQUEST['wooccredo_branch'];
            $department = @$_REQUEST['wooccredo_department'];

            $lines = Wooccredo_Invoice::createInvoiceLines($order->get_id());

            if( 0 < count($lines) ) :
                foreach( $lines as $key => $value ) :
                    $lines[$key]['SalesGroupCode'] = $salesArea;
                endforeach;
            endif;

            $data = [
                'DocumentClass'             => 'I',
                'DocumentDate'              => date('Y-m-d', strtotime('now')),
                'OriginationDate'           => date('Y-m-d', strtotime($order->get_date_created())),
                'DeliveryDate'              => date('Y-m-d', strtotime($order->get_date_paid())),
                'OrderNo'                   => $order->get_id(),
                'DefaultLocationCode'       => Wooccredo_Locations::getDefaultLocation(),
                'Custom1'                   => '',
                'Custom2'                   => '',
                'Line'                      => $lines
            ];

            if( $order->has_shipping_address() ) :
                $data['DeliveryAddressCode'] = 'SHIPPING';
                $data['DeliveryAddress1'] = $order->get_shipping_address_1();
                $data['DeliveryAddress2'] = $order->get_shipping_address_2();
                $data['DeliveryCountryCode'] = $order->get_shipping_country();
                $data['DeliveryPostCode'] = $order->get_shipping_postcode();
            endif;

            if( !empty($customer) ) :
                $data['CustomerCode'] = $customer;
            endif;

            if( !empty($salesArea) ) :
                $data['SalesAreaCode'] = $salesArea;
            endif;

            if( !empty($salesPerson) ) :
                $data['SalesPersonCode'] = $salesPerson;
            endif;

            if( !empty($location) ) :
                $data['DefaultLocationCode'] = $location;
            endif;

            if( !empty($branch) ) :
                $data['BranchCode'] = $branch;
            endif;

            if( !empty($department) ) :
                $data['DepartmentCode'] = $department;
            endif;

            if( $order->get_shipping_total() ) :
                $data['Charge'] = [
                    [
                        'Description'           => 'Freight',
                        'ChargeAmount'          => $order->get_shipping_total()
                    ]
                ];
            endif;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/INInvoice?access_token=". $accessToken['access_token']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, wp_json_encode($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'OData-Version: 4.0',
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if( $error ) :
                $order->add_order_note(__('Order invoice failed to send to Accredo.', WOOCCREDO_TEXT_DOMAIN));
            else :
                $response = json_decode($result, TRUE);

                if( isset($response['error']) ) :
                    $order->add_order_note(__($response['error']['message'], WOOCCREDO_TEXT_DOMAIN));
                else :
                    update_post_meta($order->get_id(), 'wooccredo_doc_id', $response['DocumentID']);
                    update_post_meta($order->get_id(), 'wooccredo_customer', $customer);
                    update_post_meta($order->get_id(), 'wooccredo_sales_person', $salesPerson);
                    update_post_meta($order->get_id(), 'wooccredo_sales_area', $salesArea);
                    update_post_meta($order->get_id(), 'wooccredo_location', $location);
                    update_post_meta($order->get_id(), 'wooccredo_branch', $branch);
                    update_post_meta($order->get_id(), 'wooccredo_department', $department);

                    $invoiceID = Wooccredo_Invoice::updateInvoice($response);

                    Wooccredo::addLog('Invoice #'. $response['DocumentID'] .' sent to accredo.');

                    $order->add_order_note(__(sprintf('Order invoice successfully sent to Accredo. Your document ID is #%s <a href="admin.php?page=wooccredo-invoices&action=view&invoice=%s">View Invoice</a>', $response['DocumentID'], $invoiceID), WOOCCREDO_TEXT_DOMAIN));
                endif;
            endif;

            // $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            // $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/INInvoice/Post?access_token=". $accessToken['access_token'];
            // $args = [
            //     'headers'       => [
            //         'OData-Version: 4.0',
            //         // 'Authorization: Basic '. $accessToken['access_token'],
            //         'Accept: application/json',
            //         'Content-Type: application/json'
            //     ],
            //     'body'          => wp_json_encode($data)
            // ];
            // $request = wp_remote_post($url, $args);
            // $response = wp_remote_retrieve_body($request);

            // error_log(wp_json_encode($response));
            // if( !is_wp_error($response) && is_array($response) ) :
            //     if( isset($response['error']) ) :
            //         $order->add_order_note(__($response['error']['message'], WOOCCREDO_TEXT_DOMAIN));
            //     else :
            //         update_post_meta($order->get_id(), 'wooccredo_doc_id', $result['DocumentID']);
            //         update_post_meta($order->get_id(), 'wooccredo_customer', $customer);
            //         update_post_meta($order->get_id(), 'wooccredo_sales_person', $salesPerson);
            //         update_post_meta($order->get_id(), 'wooccredo_sales_area', $salesArea);
            //         update_post_meta($order->get_id(), 'wooccredo_location', $location);
            //         update_post_meta($order->get_id(), 'wooccredo_branch', $branch);
            //         update_post_meta($order->get_id(), 'wooccredo_department', $department);

            //         $invoiceID = Wooccredo_Invoice::updateInvoice($response);

            //         $order->add_order_note(__(sprintf('Order invoice successfully sent to Accredo. Your document ID is #%s <a href="admin.php?page=wooccredo-invoices&action=view&invoice=%s">View Invoice</a>', $response['DocumentID'], $invoiceID), WOOCCREDO_TEXT_DOMAIN));
            //     endif;
            // else :
            //     $order->add_order_note(__('Order invoice failed to send to Accredo.', WOOCCREDO_TEXT_DOMAIN));
            // endif;
        }

        /**
         * Get unsynced invoices.
         * 
         * @return  array
         * @since   1.0.0
         */
        public static function getUnsyncedInvoices() {
            $syncStarted = get_option('wc_wooccredo_sync_started');
            $invoices = get_posts([
                'post_type'         => Wooccredo_Invoice::$postType,
                'posts_per_page'    => -1,
                'fields'            => 'ids',
                'meta_query'        => [
                    'relation'      => 'AND',
                    [
                        'key'       => 'sync_started',
                        'value'     => $syncStarted,
                        'compare'   => '<'
                    ]
                ]
            ]);
            wp_reset_postdata();

            return $invoices;
        }

        /**
         * Get instance.
         * 
         * @return  object
         * @since   1.0.0
         */
        public static function getInstance() {
            if( !isset(self::$instance) ) :
                self::$instance = new self();
            endif;

            return self::$instance;
        }
    }

    Wooccredo_Invoices::init();
endif;