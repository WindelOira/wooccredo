<?php 
defined('ABSPATH') || exit;
?>
<table class="wooccredo-table">
    <tbody>
        <tr>
            <th width="120">Period</th>
            <td width="120"><?php echo get_post_meta(self::$invoice->ID, 'period_name', TRUE); ?></td>
            <th width="20">ID</th>
            <td width="120"><?php echo get_post_meta(self::$invoice->ID, 'document_id', TRUE); ?></td>
        </tr>
        <tr>
            <th width="120">Customer</th>
            <td width="120"><?php echo Wooccredo_Invoice::getCustomer(self::$invoice->ID); ?></td>
        </tr>
    </tbody>
</table>