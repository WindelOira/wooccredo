<?php
defined('ABSPATH') || exit;

$links = get_post_meta(self::$invoice->ID, 'links', TRUE) ? get_post_meta(self::$invoice->ID, 'links', TRUE) : [];
?>
<br/>
<table class="wp-list-table widefat fixed striped">
    <thead>
        <tr>
            <th><?php _e('Icon', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Link Type', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('File', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Category 1', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Category 2', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Reference', WOOCCREDO_TEXT_DOMAIN); ?></th>
            <th><?php _e('Comment', WOOCCREDO_TEXT_DOMAIN); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if( 0 < count($links) ) : ?>
        <?php foreach( $links as $link ) : ?>
        <tr>
            <td><?php echo $link['ImageIndex']; ?></td>
            <td><?php echo $link['LinkType']; ?></td>
            <td><?php echo $link['FilePath']; ?></td>
            <td><?php echo $link['Category1']; ?></td>
            <td><?php echo $link['Category2']; ?></td>
            <td><?php echo $link['Reference']; ?></td>
            <td><?php echo $link['Comment']; ?></td>
        </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="7" align="center">No links found.</td>
        </tr>
    <?php endif; ?> 
    </tbody>
</table>