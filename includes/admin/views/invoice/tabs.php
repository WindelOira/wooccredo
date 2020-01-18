<?php 
defined('ABSPATH') || exit;

$tabs = apply_filters('wooccredo_invoice_tabs', [
    'header'    => __('Header', WOOCCREDO_TEXT_DOMAIN),
    'lines'     => __('Lines', WOOCCREDO_TEXT_DOMAIN),
    'charges'   => __('Charges', WOOCCREDO_TEXT_DOMAIN),
    'links'     => __('Links', WOOCCREDO_TEXT_DOMAIN)
]);
?>
<div id="wooccredo-invoice-tabs">
    <ul class="nav-tab-wrapper">
        <?php foreach( $tabs as $key => $value ) : ?>
        <li>
            <a href="#invoice-tab--<?php echo $key; ?>" class="nav-tab"><?php echo $value; ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
    
    <?php foreach( $tabs as $key => $value ) : ?>
    <div id="invoice-tab--<?php echo $key; ?>">
        <?php include_once WOOCCREDO_ABSPATH .'includes/admin/views/invoice/tab-'. $key .'.php'; ?>     
    </div>
    <?php endforeach; ?>
</div>