<?php

/**
 * markup of the plugin admin option tab for socials tab content
 */

defined('ABSPATH') || exit;

$socials = array(
    'use_google' => array('google_client'),
    'use_facebook' => array('facebook_app_id', 'facebook_app_secret'),
);


foreach ($socials  as $social_bool => $social_inputs) {
    Lukio_User_Forms_Admin_Class::print_switch_input($social_bool, $active_options[$social_bool], $options_schematics[$social_bool]['label']);
?>
    <div class="lukio_user_forms_inputs_wrapper socials use_google <?php echo $active_options[$social_bool] ? '' : ' hide_option'; ?>" data-toggle="<?php echo $social_bool; ?>">
        <?php
        foreach ($social_inputs as $social_input) {
            Lukio_User_Forms_Admin_Class::print_text_option($social_input, $active_options[$social_input], $options_schematics[$social_input]['label']);
        }
        ?>
    </div>
<?php
}
?>