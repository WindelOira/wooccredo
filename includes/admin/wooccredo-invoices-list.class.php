<?php
defined('ABSPATH') || exit;

if( !class_exists('WP_List_Table') ) :
    require_once(ABSPATH .'wp-admin/includes/class-wp-list-table.php');
endif;

if( !class_exists('Wooccredo_Invoices_List') ) :
    class Wooccredo_Invoices_List extends WP_List_Table {
        static $data;

        /**
         * Setup class.
         */
        public function __construct() {
            parent::__construct([
                'singular'  => __('Invoice', WOOCCREDO_TEXT_DOMAIN),
                'plural'    => __('Invoices', WOOCCREDO_TEXT_DOMAIN),
                'ajax'      => FALSE
            ]);
        }

        /**
         * Set data.
         * 
         * @param array     Data.
         */
        public function setData($data) {
            if( !count($data) )
                return FALSE;

            foreach( $data as $key => $value ) :
                self::$data[] = [
                    'DocumentID'            => $value['DocumentID'],
                    'RecNo'                 => $value['RecNo'],
                    'OrderNo'               => $value['OrderNo'],
                    'CustomerCode'          => $value['CustomerCode'],
                    'DocumentDate'          => $value['DocumentDate'],
                    'DeliveryDate'          => $value['DeliveryDate'],
                    'PrintStatus'           => $value['PrintStatus'],
                    'PostStatus'            => $value['PostStatus'],
                    'PackingSlipNo'         => $value['PackingSlipNo'],
                    'DocumentNo'            => $value['DocumentNo'],
                    'GrossAmount'           => $value['GrossAmount'],
                ];
            endforeach;
        }

        /**
         * Sortable columns.
         * 
         * @return array
         */
        public static function getSortableColumns() {
            $sortableColumns = [
                'DocumentID'            => ['DocumentID', TRUE],
                'OrderNo'               => ['OrderNo', TRUE],
                'CustomerCode'          => ['CustomerCode', TRUE],
                'DocumentDate'          => ['DocumentDate', TRUE],
                'DeliveryDate'          => ['DeliveryDate', TRUE],
                'PrintStatus'           => ['PrintStatus', FALSE],
                'PostStatus'            => ['PostStatus', FALSE],
                'PackingSlipNo'         => ['PackingSlipNo', TRUE],
                'DocumentNo'            => ['DocumentNo', TRUE],
                'GrossAmount'           => ['GrossAmount', TRUE],
            ];
            
            return $sortableColumns;
        }

        /**
         * Usort reorder
         */
        public function usortReorder($a, $b) {
            $orderBy = !empty($_GET['orderby']) ? $_GET['orderby'] : 'RecNo';

            $order = !empty($_GET['order']) ? $_GET['order'] : 'asc';

            $result = strcmp($a[$orderBy], $b[$orderBy]);

            return $order === 'asc' ? $result : -$result;
        }

        /**
         * Recored number column
         * 
         * @return string
         */
        public function column_DocumentID($item) {
            $actions = [
                'view'  => sprintf('<a href="?page=%s&action=%s&invoice=%s">View</a>', $_REQUEST['page'], 'view', $item['DocumentID']),
            ];

            return sprintf('%1$s %2$s', $item['DocumentID'], $this->row_actions($actions) );
        }

        /**
         * No items text.
         */
        public function no_items() {
            _e( 'No invoices found.', WOOCCREDO_TEXT_DOMAIN);
        }

        /**
         * Get columns
         * 
         * @return array
         */
        public function get_columns() {
            $columns = [
                'DocumentID'            => __('Document ID', WOOCCREDO_TEXT_DOMAIN),
                'OrderNo'               => __('Order No.', WOOCCREDO_TEXT_DOMAIN),
                'CustomerCode'          => __('Customer', WOOCCREDO_TEXT_DOMAIN),
                'DocumentDate'          => __('Date', WOOCCREDO_TEXT_DOMAIN),
                'DeliveryDate'          => __('Delivery Date', WOOCCREDO_TEXT_DOMAIN),
                'PrintStatus'           => __('Print Status', WOOCCREDO_TEXT_DOMAIN),
                'PostStatus'            => __('Post Status', WOOCCREDO_TEXT_DOMAIN),
                'PackingSlipNo'         => __('Packing Slip No.', WOOCCREDO_TEXT_DOMAIN),
                'DocumentNo'            => __('Document No.', WOOCCREDO_TEXT_DOMAIN),
                'GrossAmount'           => __('Gross Amount', WOOCCREDO_TEXT_DOMAIN),
            ];
    
            return $columns;
        }

        /**
         * Default columns
         * 
         * @return array
         */
        public function column_default($item, $columnName) {
            switch($columnName) :
                case 'DocumentID':
                    return intval($item[$columnName]);

                case 'DocumentDate' :
                    return $item[$columnName] ? date('F j, Y', strtotime($item[$columnName])) : '';

                case 'DeliveryDate' :
                    return $item[$columnName] ? date('F j, Y', strtotime($item[$columnName])) : '';

                // case 'GrossAmount' :
                //     return wc_price((float) $item[$columnName]);

                default:
                    return $item[$columnName];
            endswitch;
        }

        /**
         * Extra table nav.
         */
        public function extra_tablenav($which) {
            $customer = isset($_GET['customer']) && $_GET['customer'] ? $_GET['customer'] : @$_POST['customer'];
            $printStatus = isset($_GET['print_status']) && $_GET['print_status'] ? $_GET['print_status'] : @$_POST['print_status'];
            $postStatus = isset($_GET['post_status']) && $_GET['post_status'] ? $_GET['post_status'] : @$_POST['post_status'];

            $customers = Wooccredo_Customers::getCustomers();

            if( $which == 'top' ) :
                echo '<input type="hidden" name="page" value="wooccredo-invoices">';
                if( $customers['value'] ) :
                    echo '<select name="customer">';
                        echo '<option value="">Select Customer</option>';
                        foreach( $customers['value'] as $c ) :
                            echo '<option value="'. $c['CustomerCode'] .'" '. selected($customer, $c['CustomerCode'], FALSE) .'>'. $c['CustomerCode'] .'</option>';
                        endforeach;
                    echo '</select>';
                endif;

                echo '<select name="print_status">';
                    echo '<option value="">Select Print Status</option>';
                    echo '<option value="Unprinted" '. selected($printStatus, 'Unprinted', FALSE) .'>Unprinted</option>';
                    echo '<option value="Printed" '. selected($printStatus, 'Printed', FALSE) .'>Printed</option>';
                    echo '<option value="Manual" '. selected($printStatus, 'Manual', FALSE) .'>Manual</option>';
                    echo '<option value="Packing Slip" '. selected($printStatus, 'Packing Slip', FALSE) .'>Packing Slip</option>';
                echo '</select>';

                echo '<select name="post_status">';
                    echo '<option value="">Select Post Status</option>';
                    echo '<option value="Unposted" '. selected($postStatus, 'Unposted', FALSE) .'>Unposted</option>';
                    echo '<option value="Open" '. selected($postStatus, 'Open', FALSE) .'>Open</option>';
                    echo '<option value="Posted" '. selected($postStatus, 'Posted', FALSE) .'>Posted</option>';
                    echo '<option value="Deleted" '. selected($postStatus, 'Deleted', FALSE) .'>Deleted</option>';
                echo '</select>';

                echo '<input type="submit" value="Filter" class="button">';
            endif;
        }

        /**
         * Prepare items
         */
        public function prepare_items() {
            $data = [];
            $perPage = 10;
            $currentPage = $this->get_pagenum();

            $search = isset($_GET['s']) && $_GET['s'] ? $_GET['s'] : @$_POST['s'];

            if( !empty($search) ) : 
                self::$data = array_filter(self::$data, function($d) use ($search) {
                    return (strpos($d['DocumentID'], $search) !== FALSE) || 
                            (strpos($d['OrderNo'], $search) !== FALSE) || 
                            (strpos($d['CustomerCode'], $search) !== FALSE) || 
                            (strpos($d['DeliveryDate'], $search) !== FALSE) || 
                            (strpos($d['PrintStatus'], $search) !== FALSE) || 
                            (strpos($d['PostStatus'], $search) !== FALSE) || 
                            (strpos($d['PackingSlipNo'], $search) !== FALSE) || 
                            (strpos($d['DocumentNo'], $search) !== FALSE) || 
                            (strpos($d['GrossAmount'], $search) !== FALSE);
                });
            endif;

            $data = is_array(self::$data) ? array_slice(self::$data, (($currentPage - 1) * $perPage), $perPage) : [];

            usort($data, __CLASS__ .'::usortReorder');

            $this->_column_headers = [$this->get_columns(), [], self::getSortableColumns()];
            $this->set_pagination_args([
                'total_items' => is_array(self::$data) ? count(self::$data) : 0,
                'per_page'    => $perPage
            ]);
            $this->items = $data;
        }

        public function pagination( $which ) {
            if ( empty( $this->_pagination_args ) ) {
              return;
            }

            $search = isset($_GET['s']) && $_GET['s'] ? $_GET['s'] : @$_POST['s'];
            $customer = isset($_GET['customer']) && $_GET['customer'] ? $_GET['customer'] : @$_POST['customer'];
            $printStatus = isset($_GET['print_status']) && $_GET['print_status'] ? $_GET['print_status'] : @$_POST['print_status'];
            $postStatus = isset($_GET['post_status']) && $_GET['post_status'] ? $_GET['post_status'] : @$_POST['post_status'];
        
            $total_items     = $this->_pagination_args['total_items'];
            $total_pages     = $this->_pagination_args['total_pages'];
            $infinite_scroll = false;
            if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
              $infinite_scroll = $this->_pagination_args['infinite_scroll'];
            }
        
            if ( 'top' === $which && $total_pages > 1 ) {
              $this->screen->render_screen_reader_content( 'heading_pagination' );
            }
        
            $output = '<span class="displaying-num">' . sprintf(
              /* translators: %s: Number of items. */
              _n( '%s item', '%s items', $total_items ),
              number_format_i18n( $total_items )
            ) . '</span>';
        
            $current              = $this->get_pagenum();
            $removable_query_args = wp_removable_query_args();
        
            $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        
            $current_url = remove_query_arg( $removable_query_args, $current_url );

            if( $search ) :
                $current_url = add_query_arg('s', $search, $current_url);
            endif;

            if( $customer ) :
                $current_url = add_query_arg('customer', $customer, $current_url);
            endif;

            if( $printStatus ) :
                $current_url = add_query_arg('print_status', $printStatus, $current_url);
            endif;

            if( $postStatus ) :
                $current_url = add_query_arg('post_status', $postStatus, $current_url);
            endif;
        
            $page_links = array();
        
            $total_pages_before = '<span class="paging-input">';
            $total_pages_after  = '</span></span>';
        
            $disable_first = false;
            $disable_last  = false;
            $disable_prev  = false;
            $disable_next  = false;
        
            if ( $current == 1 ) {
              $disable_first = true;
              $disable_prev  = true;
            }
            if ( $current == 2 ) {
              $disable_first = true;
            }
            if ( $current == $total_pages ) {
              $disable_last = true;
              $disable_next = true;
            }
            if ( $current == $total_pages - 1 ) {
              $disable_last = true;
            }
        
            if ( $disable_first ) {
              $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
            } else {
              $page_links[] = sprintf(
                "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( remove_query_arg( 'paged', $current_url ) ),
                __( 'First page' ),
                '&laquo;'
              );
            }
        
            if ( $disable_prev ) {
              $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
            } else {
              $page_links[] = sprintf(
                "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
                __( 'Previous page' ),
                '&lsaquo;'
              );
            }
        
            if ( 'bottom' === $which ) {
              $html_current_page  = $current;
              $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
            } else {
              $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
              );
            }
            $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
            $page_links[]     = $total_pages_before . sprintf(
              /* translators: 1: Current page, 2: Total pages. */
              _x( '%1$s of %2$s', 'paging' ),
              $html_current_page,
              $html_total_pages
            ) . $total_pages_after;
        
            if ( $disable_next ) {
              $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
            } else {
              $page_links[] = sprintf(
                "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>", esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
                __( 'Next page' ),
                '&rsaquo;'
              );
            }
        
            if ( $disable_last ) {
              $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
            } else {
              $page_links[] = sprintf(
                "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                __( 'Last page' ),
                '&raquo;'
              );
            }
        
            $pagination_links_class = 'pagination-links';
            if ( ! empty( $infinite_scroll ) ) {
              $pagination_links_class .= ' hide-if-js';
            }
            $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';
        
            if ( $total_pages ) {
              $page_class = $total_pages < 2 ? ' one-page' : '';
            } else {
              $page_class = ' no-pages';
            }
            $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";
        
            echo $this->_pagination;
        }
    }
endif;