<?php

/**
 * uninstall action to take when uninstalling the plugin
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// include to get the class constants of the meta keys
require_once __DIR__ . '/inc/options.php';

// delete plugin options
delete_option(Lukio_User_Forms_Options_Class::OPTIONS_META_KEY);
