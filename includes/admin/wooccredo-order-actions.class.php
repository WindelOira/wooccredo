<?php

defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Admin_Order_Actions') ) :
    class Wooccredo_Admin_Order_Actions {
        /**
         * Init.
         * 
         * @since   1.0.0
         */
        public static function init() {
            add_action('woocommerce_order_actions', __CLASS__ .'::orderActions');
            add_action('woocommerce_order_action_wc_wooccredo_send_invoice', __CLASS__ .'::sendInvoice');
        }

        /**
         * Actions.
         * 
         * @param   array   $actions        Actions
         * @return  array
         * @since   1.0.0
         */
        public static function orderActions($actions) {
            global $theorder;

            $actions['wc_wooccredo_send_invoice'] = __('Send Invoice to Accredo', WOOCCREDO_TEXT_DOMAIN);

            return $actions;
        }

        /**
         * Send invoice.
         * 
         * @param   object  $order      \WC_Order
         * @since   1.0.0
         */
        public static function sendInvoice($order) {
            Wooccredo_Invoices::sendInvoice($order);
        }
    }

    Wooccredo_Admin_Order_Actions::init();
endif;