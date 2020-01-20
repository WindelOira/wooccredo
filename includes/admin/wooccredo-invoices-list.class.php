<?php

defined('ABSPATH') || exit;

if( !class_exists('WP_List_Table') ) :
  require_once(ABSPATH .'wp-admin/includes/class-wp-list-table.php');
endif;

if( !class_exists('Wooccredo_Invoices_List') ) :
  class Wooccredo_Invoices_List extends WP_List_Table {
    /**
     * Data.
     * 
     * @since   1.0.0
     */
    static $data;

    /**
     * Setup class.
     * 
     * @since   1.0.0
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
     * @param   array   $data     Data.
     * @since   1.0.0
     */
    public function setData($data) {
      if( !count($data) )
        return FALSE;

      foreach( $data as $key => $value ) :
        self::$data[] = [
          'document_id'           => $value['document_id'],
          'RecNo'                 => $value['RecNo'],
          'order_number'          => $value['order_number'],
          'customer_code'         => $value['customer_code'],
          'document_date'         => $value['document_date'],
          'delivery_date'         => $value['delivery_date'],
          'print_status'          => $value['print_status'],
          'post_status'           => $value['post_status'],
          'packing_slip_number'   => $value['packing_slip_number'],
          'document_number'       => $value['document_number'],
          'gross_amount'          => $value['gross_amount'],
        ];
      endforeach;
    }

    /**
     * Sortable columns.
     * 
     * @return  array
     * @since   1.0.0
     */
    public static function getSortableColumns() {
      $sortableColumns = [
        'document_id'             => ['document_id', TRUE],
        'order_number'            => ['order_number', TRUE],
        'customer_code'           => ['customer_code', TRUE],
        'document_date'           => ['document_date', TRUE],
        'delivery_date'           => ['delivery_date', TRUE],
        'print_status'            => ['print_status', FALSE],
        'post_status'             => ['post_status', FALSE],
        'packing_slip_number'     => ['packing_slip_number', TRUE],
        'document_number'         => ['document_number', TRUE],
        'gross_amount'            => ['gross_amount', TRUE],
      ];
      
      return $sortableColumns;
    }

    /**
     * Recored number column
     * 
     * @return  string
     * @since   1.0.0
     */
    public function column_document_id($item) {
      $actions = [
          'view'  => sprintf('<a href="?page=%s&action=%s&invoice=%s">View</a>', $_REQUEST['page'], 'view', $item->ID),
      ];

      return sprintf('%1$s %2$s', $item->ID, $this->row_actions($actions) );
    }

    /**
     * No items text.
     * 
     * @since   1.0.0
     */
    public function no_items() {
      _e( 'No invoices found.', WOOCCREDO_TEXT_DOMAIN);
    }

    /**
     * Get columns
     * 
     * @return  array
     * @since   1.0.0
     */
    public function get_columns() {
      $columns = [
        'document_id'           => __('Document ID', WOOCCREDO_TEXT_DOMAIN),
        'order_number'          => __('Order No.', WOOCCREDO_TEXT_DOMAIN),
        'customer_code'         => __('Customer', WOOCCREDO_TEXT_DOMAIN),
        'document_date'         => __('Date', WOOCCREDO_TEXT_DOMAIN),
        'delivery_date'         => __('Delivery Date', WOOCCREDO_TEXT_DOMAIN),
        'print_status'          => __('Print Status', WOOCCREDO_TEXT_DOMAIN),
        'post_status'           => __('Post Status', WOOCCREDO_TEXT_DOMAIN),
        'packing_slip_number'   => __('Packing Slip No.', WOOCCREDO_TEXT_DOMAIN),
        'document_number'       => __('Document No.', WOOCCREDO_TEXT_DOMAIN),
        'gross_amount'          => __('Gross Amount', WOOCCREDO_TEXT_DOMAIN),
      ];

      return $columns;
    }

    /**
     * Default columns
     * 
     * @return  array
     * @since   1.0.0
     */
    public function column_default($item, $columnName) {
      switch($columnName) :
        case 'customer_code' :
          return Wooccredo_Invoice::getCustomer($item->ID);

        case 'document_date' :
          return get_post_meta($item->ID, $columnName, TRUE) ? date('F j, Y', strtotime(get_post_meta($item->ID, $columnName, TRUE))) : '';

        case 'delivery_date' :
          return get_post_meta($item->ID, $columnName, TRUE) ? date('F j, Y', strtotime(get_post_meta($item->ID, $columnName, TRUE))) : '';

        case 'gross_amount' :
          return wc_price((float) get_post_meta($item->ID, $columnName, TRUE)); 

        default :
          return get_post_meta($item->ID, $columnName, TRUE);
      endswitch;
    }

    /**
     * Extra table nav.
     * 
     * @param   string  $which    Nav position.
     * @since   1.0.0
     */
    public function extra_tablenav($which) {
      $customers = Wooccredo_Customers::getCustomers();
      $printStatuses = ['Unprinted', 'Printed', 'Manual', 'Packing Slip'];
      $postStatuses = ['Unposted', 'Open', 'Posted', 'Deleted'];
      
      if( $which == 'top' ) :
        echo '<input type="hidden" name="page" value="wooccredo-invoices">';
        if( $customers ) :
          echo '<select name="customer">';
            echo '<option value="">Select Customer</option>';
            foreach( $customers as $customer ) :
              if( !get_term_meta($customer->term_id, 'customer_code', TRUE) ) continue;

              echo '<option value="'. $customer->term_id .'" '. selected(@$_REQUEST['customer'], $customer->term_id, FALSE) .'>'. $customer->name .'</option>';
            endforeach;
          echo '</select>';
        endif;

        echo '<select name="print_status">';
            echo '<option value="">Select Print Status</option>';
            foreach( $printStatuses as $printStatus ) :
              echo '<option value="'. $printStatus .'" '. selected(@$_REQUEST['print_status'], $printStatus, FALSE) .'>'. $printStatus .'</option>';
            endforeach;
        echo '</select>';

        echo '<select name="post_status">';
            echo '<option value="">Select Post Status</option>';
            foreach( $postStatuses as $postStatus ) :
              echo '<option value="'. $postStatus .'" '. selected(@$_REQUEST['post_status'], $postStatus, FALSE) .'>'. $postStatus .'</option>';
            endforeach;
        echo '</select>';

        echo '<input type="submit" value="Filter" class="button">';
      endif;
    }

    /**
     * Prepare items
     * 
     * @since   1.0.0
     */
    public function prepare_items() {
      $data = [];
      $perPage = 10;
      $currentPage = $this->get_pagenum();
      $total = wp_count_posts(Wooccredo_Invoice::$postType);

      $searchDocNum = @$_REQUEST['s'];
      $customer = @$_REQUEST['customer'];
      $printStatus = @$_REQUEST['print_status'];
      $postStatus = @$_REQUEST['post_status'];

      $args = [
        'post_type'       => Wooccredo_Invoice::$postType,
        'posts_per_page'  => $perPage,
        'paged'           => $currentPage,
        'tax_query'       => [],
        'meta_query'     => []
      ];

      // Search by document number
      if( $searchDocNum ) :
        $args['posts_per_page'] = -1;
        $args['paged'] = NULL;
        $args['meta_query'][] = [
          'key'     => 'document_number',
          'value'   => $searchDocNum,
        ];
      endif;

      // Customer
      if( $customer ) :
        $args['tax_query'][] = [
          'taxonomy'  => Wooccredo_Customers::$taxonomy,
          'terms'     => $customer
        ];
      endif;

      // Print status
      if( $printStatus ) :
        $args['meta_query'][] = [
          'key'     => 'print_status',
          'value'   => $printStatus
        ];
      endif;

      // Post status
      if( $postStatus ) :
        $args['meta_query'][] = [
          'key'     => 'post_status',
          'value'   => $postStatus
        ];
      endif;

      $data = get_posts($args);

      $this->_column_headers = [$this->get_columns(), [], self::getSortableColumns()];
      $this->set_pagination_args([
          'total_items' => $total ? $total->publish : 0,
          'per_page'    => $perPage
      ]);
      $this->items = $data;
    }
  }
endif;