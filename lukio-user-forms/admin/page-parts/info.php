<?php

/**
 * markup of the plugin admin option tab for info and tips
 */

defined('ABSPATH') || exit;

// include __DIR__ . '/info-text.php';


?>

<div class="lukio_user_forms_options_shortcodes_wrapper">
    <?php
    Lukio_User_Forms_Admin_Class::print_shortcode_copy('[lukio_login_form]');
    Lukio_User_Forms_Admin_Class::print_shortcode_copy('[lukio_register_form]');
    Lukio_User_Forms_Admin_Class::print_shortcode_copy('[lukio_combo_form]');
    ?>
</div>