<?php
/**
 * Plugin Name:       Wooccredo
 * Plugin URI:        https://github.com/WindelOira/wooccredo
 * Description:       Push sales to Accredo and manage inventory.
 * Version:           1.0.0
 * Author:            Windel Oira
 * Author URI:        https://github.com/WindelOira
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wooccredo
 */

defined('ABSPATH') || exit;

!defined('WOOCCREDO_TEXT_DOMAIN') ? define('WOOCCREDO_TEXT_DOMAIN', 'wooccredo') : '';
!defined('WOOCCREDO_PLUGIN_FILE') ? define('WOOCCREDO_PLUGIN_FILE', __FILE__) : '';

if( !class_exists('Wooccredo') ) :
    include_once dirname(__FILE__) .'/includes/wooccredo.class.php';
endif;
