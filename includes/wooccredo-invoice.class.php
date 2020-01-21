<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Invoice') ) :
    class Wooccredo_Invoice {
        /**
         * Post type.
         * 
         * @since   1.0.0
         */
        public static $postType;

        /**
         * @var     Invoice.
         * @since   1.0.0
         */
        static $invoice;

        /**
         * Init.
         * 
         * @since   1.0.0
         */
        public static function init() {
            self::$postType = 'wooccredo_invoices';
        }

        /**
         * View.
         * 
         * @since   1.0.0
         */
        public static function view() {
            if( !self::$invoice )
                return;

            include_once WOOCCREDO_ABSPATH .'includes/admin/views/invoice/heading.php';
            include_once WOOCCREDO_ABSPATH .'includes/admin/views/invoice/tabs.php';
        }

        /**
         * Get customer.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  string
         * @since   1.0.0
         */
        public static function getCustomer($invoice = FALSE) {
            $invoice = $invoice === FALSE ? self::$invoice : get_post($invoice);

            if( !$invoice )
                return;

            $customers = get_the_terms($invoice, Wooccredo_Customers::$taxonomy);
            $customers = $customers ? wp_list_pluck($customers, 'name') : FALSE;

            return $customers ? implode(' ', $customers) : '';
        }

        /**
         * Get sales person.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  string
         * @since   1.0.0
         */
        public static function getSalesPerson($invoice = FALSE) {
            $invoice = $invoice === FALSE ? self::$invoice : get_post($invoice);

            if( !$invoice )
                return;

            $salesPersons = get_the_terms($invoice, Wooccredo_Sales_Persons::$taxonomy);
            $salesPersons = $salesPersons ? wp_list_pluck($salesPersons, 'name') : FALSE;

            return $salesPersons ? implode(' ', $salesPersons) : '';
        }

        /**
         * Get sales area.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  string
         * @since   1.0.0
         */
        public static function getSalesArea($invoice = FALSE) {
            $invoice = $invoice === FALSE ? self::$invoice : get_post($invoice);

            if( !$invoice )
                return;

            $salesAreas = get_the_terms($invoice, Wooccredo_Sales_Areas::$taxonomy);
            $salesAreas = $salesAreas ? wp_list_pluck($salesAreas, 'name') : FALSE;

            return $salesAreas ? implode(' ', $salesAreas) : '';
        }

        /**
         * Get location.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  string
         * @since   1.0.0
         */
        public static function getLocation($invoice = FALSE) {
            $invoice = $invoice === FALSE ? self::$invoice : get_post($invoice);

            if( !$invoice )
                return;

            $locations = get_the_terms($invoice, Wooccredo_Locations::$taxonomy);
            $locations = $locations ? wp_list_pluck($locations, 'name') : FALSE;

            return $locations ? implode(' ', $locations) : '';
        }

        /**
         * Get branch.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  string
         * @since   1.0.0
         */
        public static function getBranch($invoice = FALSE) {
            $invoice = $invoice === FALSE ? self::$invoice : get_post($invoice);

            if( !$invoice )
                return;

            $branches = get_the_terms($invoice, Wooccredo_Branches::$taxonomy);
            $branches = $branches ? wp_list_pluck($branches, 'name') : FALSE;

            return $branches ? implode(' ', $branches) : '';
        }

        /**
         * Get department.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  string
         * @since   1.0.0
         */
        public static function getDepartment($invoice = FALSE) {
            $invoice = $invoice === FALSE ? self::$invoice : get_post($invoice);

            if( !$invoice )
                return;

            $departments = get_the_terms($invoice, Wooccredo_Departments::$taxonomy);
            $departments = $departments ? wp_list_pluck($departments, 'name') : FALSE;

            return $departments ? implode(' ', $departments) : '';
        }

        /**
         * Update invoice
         * 
         * @param   array   $data       Invoice data.
         * @since   1.0.0
         */
        public static function updateInvoice($data) {
            if( !isset($data['DocumentID']) ) 
                return;

            $invoices = new WP_Query([
                'post_type'         => self::$postType,
                'posts_per_page'    => 1,
                'fields'            => 'ids',
                'meta_query'        => [
                    [
                        'key'       => 'document_id',
                        'value'     => $data['DocumentID']
                    ]
                ]
            ]);
            
            $args = [
                'post_type'     => self::$postType,
                'post_title'    => esc_html('Wooccredo Invoice #'. @$data['DocumentID']),
                'post_status'   => 'publish',
                'tax_input'     => [],
                'meta_input'    => [
                    'document_id'           => @$data['DocumentID'],
                    'order_number'          => @$data['OrderNo'],
                    'document_date'         => @$data['DocumentDate'],
                    'delivery_date'         => @$data['DeliveryDate'],
                    'print_status'          => @$data['PrintStatus'],
                    'post_status'           => @$data['PostStatus'],
                    'packing_slip_number'   => @$data['PackingSlipNo'],
                    'document_number'       => @$data['DocumentNo'],
                    'gross_amount'          => @$data['GrossAmount']
                ]
            ];

            // Add/update invoice
            if( $invoices->get_posts() ) :
                $args['ID'] = $invoices->get_posts()[0];

                $invoiceID = wp_update_post($args);
            else :
                $invoiceID = wp_insert_post($args);
            endif;

            // Customer taxonomy
            if( isset($data['CustomerCode']) && $data['CustomerCode'] ) :
                wp_set_post_terms($invoiceID, Wooccredo_Customers::getCustomers(@$data['CustomerCode'], 'ids'), Wooccredo_Customers::$taxonomy);
            endif;

            // Sales person taxonomy
            if( isset($data['SalesPersonCode']) && $data['SalesPersonCode'] ) :
                wp_set_post_terms($invoiceID, Wooccredo_Sales_Persons::getSalesPersons(@$data['SalesPersonCode'], 'ids'), Wooccredo_Sales_Persons::$taxonomy);
            endif;

            // Sales area taxonomy
            if( isset($data['SalesAreaCode']) && $data['SalesAreaCode'] ) :
                wp_set_post_terms($invoiceID, Wooccredo_Sales_Areas::getSalesAreas(@$data['SalesAreaCode'], 'ids'), Wooccredo_Sales_Areas::$taxonomy);
            endif;

            // Location taxonomy
            if( isset($data['DefaultLocationCode']) && $data['DefaultLocationCode'] ) :
                wp_set_post_terms($invoiceID, Wooccredo_Locations::getLocations(@$data['DefaultLocationCode'], 'ids'), Wooccredo_Locations::$taxonomy);
            endif;

            // Branch taxonomy
            if( isset($data['BranchCode']) && $data['BranchCode'] ) :
                wp_set_post_terms($invoiceID, Wooccredo_Branches::getBranches(@$data['BranchCode'], 'ids'), Wooccredo_Branches::$taxonomy);
            endif;

            // Department taxonomy
            if( isset($data['DepartmentCode']) && $data['DepartmentCode'] ) :
                wp_set_post_terms($invoiceID, Wooccredo_Departments::getDepartments(@$data['DepartmentCode'], 'ids'), Wooccredo_Departments::$taxonomy);
            endif;

            Wooccredo::addLog('Invoice #'. @$data['DocumentID'] .' synced.');

            return $invoiceID;
        }

        /**
         * Get invoice.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  array
         * @since   1.0.0
         */
        public static function getInvoice($invoiceID) {
            $invoice = get_post($invoiceID);

            if( !$invoice )
                return FALSE;

            if( !get_post_meta($invoiceID, 'document_id', TRUE) ) 
                return FALSE;

            if( !get_post_meta($invoiceID, 'synced', TRUE) ) :
                $invoice = self::syncInvoice($invoiceID);
            endif;
            
            self::$invoice = $invoice;
        }

        /**
         * Sync invoice from api.
         * 
         * @param   int     $invoiceID      Invoice ID.
         * @return  array
         * @since   1.0.0
         */
        public static function syncInvoice($invoiceID) {
            $accessToken = Wooccredo::getToken();

            if( !isset($accessToken['access_token']) ) 
                return FALSE;

            $invoice = get_post($invoiceID);

            if( !$invoice )
                return FALSE;

            if( !get_post_meta($invoiceID, 'document_id', TRUE) ) 
                return FALSE;

            $http = Wooccredo::getOption('ssl') ? 'https' : 'http';
            $url = $http ."://". Wooccredo::getOption('host') .":". Wooccredo::getOption('port') ."/saturn/odata4/v1/Company('". Wooccredo::getOption('company') ."')/INInvoice(". get_post_meta($invoiceID, 'document_id', TRUE) .")?\$expand=Line,Charge,Link?access_token=". $accessToken['access_token'];
            $args = [
                'headers'   => [
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ];
            $results = wp_remote_get($url, $args);

            if( !is_wp_error($results) && ( is_array($results) && !isset($results['body']['error']) ) ) :
                $result = json_decode($results['body'], TRUE);

                update_post_meta($invoice->ID, 'sync_started', get_option('wc_wooccredo_sync_started'));
                update_post_meta($invoice->ID, 'lines', $result['Line']);
                update_post_meta($invoice->ID, 'charges', $result['Charge']);
                update_post_meta($invoice->ID, 'links', $result['Link']);
                update_post_meta($invoice->ID, 'rate_type', $result['RateType']);
                update_post_meta($invoice->ID, 'origination_date', $result['OriginationDate']);
                update_post_meta($invoice->ID, 'exchange_rate', $result['ExchangeRate']);
                update_post_meta($invoice->ID, 'category_2', $result['Category2']);
                update_post_meta($invoice->ID, 'custom_1', $result['Custom1']);
                update_post_meta($invoice->ID, 'custom_2', $result['Custom2']);
                update_post_meta($invoice->ID, 'job', $result['DefaultJobCode']);
                update_post_meta($invoice->ID, 'quotation_reference', $result['QuotationReference']);
                update_post_meta($invoice->ID, 'internal_ref', $result['InternalReference']);
                update_post_meta($invoice->ID, 'price_code', $result['PriceCode']);
                update_post_meta($invoice->ID, 'sell_price_basis', $result['SellPriceBasis']);
                update_post_meta($invoice->ID, 'discount_code', $result['DiscountScheduleCode']);
                update_post_meta($invoice->ID, 'comment', $result['Comment']);
                update_post_meta($invoice->ID, 'contact_email', $result['ContactEmail']);
                update_post_meta($invoice->ID, 'synced', TRUE);
            endif;

            return $invoice;
        }

        /**
         * Create invoice lines.
         * 
         * @param   integer   $orderID
         * @return  array
         * @since   1.0.0
         */
        public static function createInvoiceLines($orderID) {
            $order = wc_get_order($orderID);

            if( !$order )
                return FALSE;

            $lines = [];
            $items = $order->get_items();

            if( 0 < count($items) ) :
                foreach( $items as $item ) :
                    $product = wc_get_product($item->get_product_id());
                    
                    if( $line = Wooccredo_Product::createProduct($item->get_product_id()) ) :
                        $lines[] = [
                            'LineType'              => 'P',
                            'ProductCode'           => $line['ProductCode'],
                            'Description'           => $line['Description'],
                            'QuantitySupplied'      => $item->get_quantity(),
                            'SellingPrice'          => (float) $product->get_price(),
                            'DiscountPercentage'    => 0
                        ];
                    endif;
                endforeach;
            endif;

            return $lines;
        }

        /**
         * Delete invoice.
         * 
         * @param   int     $invoice        Invoice ID.
         * @since   1.0.0
         */
        public static function deleteInvoice($invoice) {
            wp_delete_post($invoice, TRUE);

            Wooccredo::addLog('Invoice : '. $invoice .' deleted.');
        }
    }

    Wooccredo_Invoice::init();
endif;