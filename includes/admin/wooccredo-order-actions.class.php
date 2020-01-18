<?php
defined('ABSPATH') || exit;

if( !class_exists('Wooccredo_Admin_Order_Actions') ) :
    class Wooccredo_Admin_Order_Actions {
        /**
         * Init.
         */
        public static function init() {
            add_action('woocommerce_order_actions', __CLASS__ .'::orderActions');
            add_action('woocommerce_order_action_wc_wooccredo_send_invoice', __CLASS__ .'::sendInvoice');
        }

        /**
         * Actions.
         * 
         * @param $actions  Actions
         * @return array
         */
        public static function orderActions($actions) {
            global $theorder;

            $actions['wc_wooccredo_send_invoice'] = __('Send Invoice to Accredo', WOOCCREDO_TEXT_DOMAIN);

            return $actions;
        }

        /**
         * Send invoice.
         * 
         * @param $order    \WC_Order
         */
        public static function sendInvoice($order) {
            Wooccredo_Invoices::sendInvoice($order);
        }
    }

    Wooccredo_Admin_Order_Actions::init();
endif;