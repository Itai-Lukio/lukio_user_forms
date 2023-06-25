<?php

/*
* The content of the login tab in the admin panel.
*/
defined('ABSPATH') || exit;

$login_fields = array(
    'login_title',
    'login_submit',
    'login_remember',
    'lost_title',
    'lost_submit',
    'password_reset_title',
    'lost_success_message',
    'password_repeat_label',
    'save_password',
    'reset_success_message',
);
?>
<div class="lukio_user_forms_inputs_wrapper login">
    <?php
    foreach ($login_fields as $option) {
        Lukio_User_Forms_Admin_Class::print_text_option($option, $active_options[$option], $options_schematics[$option]['label']);
    }
    ?>
</div>