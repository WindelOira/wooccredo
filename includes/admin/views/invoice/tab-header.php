<table class="wooccredo-table">
    <tbody>
        <tr>
            <th width="130"><?php _e('Invoice Date', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['DocumentDate'] ? date('F j, Y', strtotime(self::$invoice['DocumentDate'])) : ''; ?></td>
            <th width="130"><?php _e('Branch', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['BranchCode']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Origination Date', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['OriginationDate'] ? date('F j, Y', strtotime(self::$invoice['OriginationDate'])) : ''; ?></td>
            <th width="130"><?php _e('Department', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['DepartmentCode']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Delivery Date', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['DeliveryDate'] ? date('F j, Y', strtotime(self::$invoice['DeliveryDate'])) : ''; ?></td>
            <th width="130"><?php _e('Default Location', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['DefaultLocationCode']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Rate Type', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['RateType']; ?></td>
            <th width="130"><?php _e('Promotion', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Exchange Rate', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['ExchangeRate']; ?></td>
            <th width="130"><?php _e('Category 2', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['Category2']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Invoice No.', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['DocumentNo']; ?></td>
            <th width="130"><?php _e('Custom 1', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['Custom1']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Job', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['DefaultJobCode']; ?></td>
            <th width="130"><?php _e('Custom 2', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['Custom2']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Order Number', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['OrderNo']; ?></td>
            <th width="130"><?php _e('Quotation Ref', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['QuotationReference']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Sales Person', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['SalesPersonCode']; ?></td>
            <th width="130"><?php _e('P/Slip Number', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['PackingSlipNo']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Internal Ref', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['InternalReference']; ?></td>
            <th width="130"><?php _e('Price Code', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['PriceCode']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Basis', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['SellPriceBasis']; ?></td>
            <th width="130"><?php _e('Discount Code', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['DiscountScheduleCode']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Comment', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['Comment']; ?></td>
            <th width="130"><?php _e('Post Status', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['PostStatus']; ?></td>
        </tr>
        <tr>
            <th width="130"><?php _e('Print Status', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130"><?php echo self::$invoice['PrintStatus']; ?></td>
            <th width="130"><?php _e('Contact Email', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <td width="130">
                <a href="mailto:<?php echo self::$invoice['ContactEmail']; ?>"><?php echo self::$invoice['ContactEmail']; ?></a>
            </td>
        </tr>
    </tbody>
</table>