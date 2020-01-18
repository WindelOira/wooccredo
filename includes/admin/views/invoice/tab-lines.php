<?php
defined('ABSPATH') || exit;

$lines = self::$invoice['Line'] ? self::$invoice['Line'] : [];
?>
<br/>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php _e('Type', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Product', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Description', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('UOM Code', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('UOM Quantity', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Unit', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('UOM Selling Price', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Discount %', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('GST', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Group', WOOCCREDO_TEXT_DOMAIN); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if( 0 < count($lines) ) : ?>
        <?php foreach( $lines as $line ) : ?>
        <tr>
            <td><?php echo $line['LineType']; ?></td>
            <td><?php echo $line['ProductCode']; ?></td>
            <td><?php echo $line['Description']; ?></td>
            <td><?php echo isset($line['UOMCode']) ? $line['UOMCode'] : ''; ?></td>
            <td><?php echo isset($line['UOMQuantitySupplied']) ? $line['UOMQuantitySupplied'] : ''; ?></td>
            <td><?php echo $line['Unit']; ?></td>
            <td><?php echo isset($line['UOMSellingPrice']) ? wc_price($line['UOMSellingPrice']) : ''; ?></td>
            <td><?php echo $line['DiscountPercentage']; ?></td>
            <td><?php echo $line['GstCode']; ?></td>
            <td><?php echo $line['SalesGroupCode']; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="10" align="center">No lines found.</td>
        </tr>
    <?php endif; ?> 
    </tbody>
</table>