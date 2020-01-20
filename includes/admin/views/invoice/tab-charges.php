<?php
defined('ABSPATH') || exit;

$charges = get_post_meta(self::$invoice->ID, 'charges', TRUE) ? get_post_meta(self::$invoice->ID, 'charges', TRUE) : [];
?>
<br/>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php _e('Description', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Charge', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('GST Code', WOOCCREDO_TEXT_DOMAIN); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if( 0 < count($charges) ) : ?>
        <?php foreach( $charges as $charge ) : ?>
        <tr>
            <td><?php echo $charge['Description']; ?></td>
            <td><?php echo wc_price($charge['ChargeAmount']); ?></td>
            <td><?php echo $charge['ChargeGstCode']; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="3" align="center">No charges found.</td>
        </tr>
    <?php endif; ?> 
    </tbody>
</table>