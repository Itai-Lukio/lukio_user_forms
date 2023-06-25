<?php

/**
 * Plugin Name: Lukio User Forms
 * Plugin URI: https://lukio.pro
 * Author: Itai Dotan @Lukio
 * Author URI: https://lukio.pro/erez
 * Description: Easy login and register forms including lost password functionality.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Text Domain: lukio-user-forms
 * Domain Path: /languages/
 */
defined('ABSPATH') || exit;

define('LUKIO_USER_FORMS_URL', plugin_dir_url(__FILE__));
define('LUKIO_USER_FORMS_DIR', plugin_dir_path(__FILE__));
define('LUKIO_USER_FORMS_PLUGIN_MAIN_FILE', basename(__DIR__) . '/' . basename(__FILE__));

require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/login-class.php';
require_once __DIR__ . '/inc/registration-class.php';
require_once __DIR__ . '/inc/admin-page-functions.php';
require_once __DIR__ . '/inc/options.php';

/**
 * setup the needed plugin parts when activating the plugin
 * 
 * @author Itai Dotan
 */
function lukio_user_forms_activation()
{
    if (!get_option(Lukio_User_Forms_Options_Class::OPTIONS_META_KEY)) {
        // create the option when not set using the default options
        $class_instance = Lukio_User_Forms_Options_Class::get_instance();
        update_option(Lukio_User_Forms_Options_Class::OPTIONS_META_KEY, $class_instance->get_default_options());
    }
}

register_activation_hook(__FILE__, 'lukio_user_forms_activation');
