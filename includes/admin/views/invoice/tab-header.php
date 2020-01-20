<table class="wooccredo-table">
    <tbody>
        <tr>
            <th width="130"><?php _e('Invoice Date', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'document_date', TRUE) ? date('F j, Y', strtotime(get_post_meta(self::$invoice->ID, 'document_date', TRUE))) : ''; ?></td>
            <th width="130"><?php _e('Branch', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo Wooccredo_Invoice::getBranch(self::$invoice->ID); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Origination Date', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'origination_date', TRUE) ? date('F j, Y', strtotime(get_post_meta(self::$invoice->ID, 'origination_date', TRUE))) : ''; ?></td>
            <th width="130"><?php _e('Department', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo Wooccredo_Invoice::getDepartment(self::$invoice->ID); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Delivery Date', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'delivery_date', TRUE) ? date('F j, Y', strtotime(get_post_meta(self::$invoice->ID, 'delivery_date', TRUE))) : ''; ?></td>
            <th width="130"><?php _e('Default Location', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo Wooccredo_Invoice::getLocation(self::$invoice->ID); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Rate Type', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php get_post_meta(self::$invoice->ID, 'rate_type', TRUE); ?></td>
            <th width="130"><?php _e('Promotion', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php get_post_meta(self::$invoice->ID, 'promotion', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Exchange Rate', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'exchange_rate', TRUE); ?></td>
            <th width="130"><?php _e('Category 2', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'category_2', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Invoice No.', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'document_number', TRUE); ?></td>
            <th width="130"><?php _e('Custom 1', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'custom_1', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Job', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'job', TRUE); ?></td>
            <th width="130"><?php _e('Custom 2', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'custom_2', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Order Number', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'order_number', TRUE); ?></td>
            <th width="130"><?php _e('Quotation Ref', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'quotation_reference', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Sales Person', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo Wooccredo_Invoice::getSalesPerson(self::$invoice->ID); ?></td>
            <th width="130"><?php _e('P/Slip Number', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'packing_slip_number', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Internal Ref', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php  get_post_meta(self::$invoice->ID, 'internal_ref', TRUE); ?></td>
            <th width="130"><?php _e('Price Code', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'price_code', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Basis', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'sell_price_basis', TRUE); ?></td>
            <th width="130"><?php _e('Discount Code', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'discount_code', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Comment', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'comment', TRUE); ?></td>
            <th width="130"><?php _e('Post Status', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'post_status', TRUE); ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Print Status', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo get_post_meta(self::$invoice->ID, 'print_status', TRUE); ?></td>
            <th width="130"><?php _e('Contact Email', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130">
                <a href="mailto:<?php echo get_post_meta(self::$invoice->ID, 'contact_email', TRUE); ?>"><?php echo get_post_meta(self::$invoice->ID, 'contact_email', TRUE); ?></a>
            </td>
        </tr>
    </tbody>
</table>