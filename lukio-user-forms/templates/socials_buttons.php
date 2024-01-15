<?php

/**
 * The template for displaying the socials buttons
 *
 * This template can be overridden by copying it to yourtheme/lukio-user-forms/socials_buttons.php.
 */

defined('ABSPATH') || exit;

?>
<div class="lukio_user_forms_integrations_buttons_wrapper hide_no_js no_js">
    <?php
    if (Lukio_User_Forms_Options_Class::get_google_client()) {
    ?>
        <button class="lukio_user_forms_integrations_button google" type="button">
            <span class="dashicons dashicons-google"></span>
            <div class="lukio_user_forms_google_iframe_wrapper">
                <!-- Any content in this div will be overwritten -->
            </div>
        </button>
    <?php
    }

    if (Lukio_User_Forms_Options_Class::get_facebook_app_id()) {
    ?>
        <button class="lukio_user_forms_integrations_button facebook" type="button">
            <span class="dashicons dashicons-facebook-alt"></span>
        </button>
    <?php
    }
    ?>
</div>