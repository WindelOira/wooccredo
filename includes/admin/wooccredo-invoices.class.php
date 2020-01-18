<?php 
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Invoices') ) :
    class Wooccredo_Invoices {
        static $instance;
        public static $invoices = [];
        public static $listObject;
        protected static $token;
        protected static $company;

        /**
         * Init.
         * 
         * @since 1.0.0
         */
        public static function init() {
            self::getInstance();

            self::$token = Wooccredo::getToken();
            self::$company = Wooccredo::getOption('company');

            add_filter('set-screen-option', __CLASS__ .'::setScreen', 10, 3);
            add_action('admin_menu', __CLASS__ .'::adminMenu');
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
            $search = isset($_GET['s']) && $_GET['s'] ? $_GET['s'] : @$_POST['s'];
            $customer = isset($_GET['customer']) && $_GET['customer'] ? $_GET['customer'] : @$_POST['customer'];
            $printStatus = isset($_GET['print_status']) && $_GET['print_status'] ? $_GET['print_status'] : @$_POST['print_status'];
            $postStatus = isset($_GET['post_status']) && $_GET['post_status'] ? $_GET['post_status'] : @$_POST['post_status'];
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
                    if( $customer || $printStatus || $postStatus ) :
                        $data = self::getInvoices('', [
                            'CustomerCode'  => $customer,
                            'PrintStatus'   => $printStatus,
                            'PostStatus'    => $post_status
                        ], $search);
                    else :
                        $data = self::getInvoices('', [], $search);
                    endif;

                    self::$listObject = new Wooccredo_Invoices_List();
                    self::$listObject->setData($data);
                    self::$listObject->prepare_items();
                ?>
                <h1><?php _e('Invoices', WOOCCREDO_TEXT_DOMAIN); ?></h1>
                <form method="post">
                    <input type="hidden" name="page" value="wooccredo-invoices">
                    <?php self::$listObject->search_box('Search', 'search'); ?>
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
         * Get invoices.
         * 
         * @since   1.0.0
         */
        public static function getInvoices($nextLink = '', $filters = [], $search = '') {
            $curlURL = "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". self::$company ."')/INInvoiceList";
            $curl = curl_init();

            if( empty($nextLink) ) :
                $filtersStr = '';
                if( 0 < count($filters) ) :
                    $filtersStr .= "&\$filter=";

                    if( 0 < count($filters) ) :
                        foreach( $filters as $key => $value ) :
                            if( !$value )
                                continue;

                            $filtersStr .= $key . "+eq+'". $value ."'+and+";
                        endforeach;
                    endif;

                    $filtersStr = rtrim($filtersStr, "+and+");
                endif;

                $curlURL = "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". self::$company ."')/INInvoiceList?\$select=DocumentID,RecNo,OrderNo,CustomerCode,DocumentDate,DeliveryDate,PrintStatus,PostStatus,PackingSlipNo,DocumentNo,GrossAmount". $filtersStr ."?access_token=". self::$token['access_token'];
            else :
                $curlURL = $nextLink;
            endif;

            curl_setopt($curl, CURLOPT_URL, $curlURL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            $result = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            $result = json_decode($result, TRUE);

            if( isset($result['value']) && 
                is_array($result['value']) && 
                0 < count($result['value']) ) :
                foreach( $result['value'] as $invoice ) :
                    self::$invoices[] = $invoice;
                endforeach;
            endif;
            
            if( isset($result['@odata.nextLink']) && $result['@odata.nextLink'] ) :
                return self::getInvoices($result['@odata.nextLink']);
            endif;
            
            return self::$invoices;
        }

        /**
         * Send invoice.
         * 
         * @param   $order    \WC_Order
         * @simce   1.0.0
         */
        public static function sendInvoice($order) {
            if( !self::$token ) 
                return FALSE;

            $customer = @$_POST['wooccredo_customer'];
            $salesPerson = @$_POST['wooccredo_sales_person'];
            $salesArea = @$_POST['wooccredo_sales_area'];
            $location = @$_POST['wooccredo_location'];
            $branch = @$_POST['wooccredo_branch'];
            $department = @$_POST['wooccredo_department'];

            $lines = Wooccredo_Invoice::createInvoiceLines($order->get_id());

            $data = [
                'DocumentClass'             => 'I',
                'OrderNo'                   => $order->get_id(),
                'DocumentDate'              => date('Y-m-d', strtotime('now')),
                'DefaultLocationCode'       => Wooccredo_Locations::getDefaultLocation(),
            ];

            if( !empty($customer) ) :
                $data['CustomerCode'] = $customer;
            endif;

            if( !empty($salesArea) ) :
                $data['SalesAreaCode'] = $salesArea;
            endif;

            if( !empty($SalesPersonCode) ) :
                $data['CustomerCode'] = $salesPerson;
            endif;

            if( !empty($branch) ) :
                $data['BranchCode'] = $branch;
            endif;

            if( !empty($department) ) :
                $data['DepartmentCode'] = $department;
            endif;

            if( 0 < count($lines) ) :
                $data['Line'] = $lines;
            endif;

            if( $order->get_shipping_total() ) :
                $data['Charge'] = [
                    [
                        'Description'           => 'Freight',
                        'ChargeAmount'          => $order->get_shipping_total()
                    ]
                ];
            endif;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, "https://demo.accredo.co.nz:6569/saturn/odata4/v1/Company('". self::$company ."')/INInvoice?access_token=". self::$token['access_token']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
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
                    error_log(json_encode($response['error']));
                    $order->add_order_note(__($response['error']['message'], WOOCCREDO_TEXT_DOMAIN));
                else :
                    $order->add_order_note(__(sprintf('Order invoice successfully sent to Accredo. Your document ID is #%s <a href="admin.php?page=wooccredo-invoices&action=view&invoice=%s">View Invoice</a>', $response['DocumentID'], $response['DocumentID']), WOOCCREDO_TEXT_DOMAIN));

                    update_post_meta($order->get_id(), 'wooccredo_doc_id', $response['DocumentID']);
                endif;
            endif;

            update_post_meta($order->get_id(), 'wooccredo_customer', $customer);
            update_post_meta($order->get_id(), 'wooccredo_sales_person', $salesPerson);
            update_post_meta($order->get_id(), 'wooccredo_sales_area', $salesArea);
            update_post_meta($order->get_id(), 'wooccredo_location', $location);
            update_post_meta($order->get_id(), 'wooccredo_branch', $branch);
            update_post_meta($order->get_id(), 'wooccredo_department', $department);
        }

        /**
         * Get instance.
         * 
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