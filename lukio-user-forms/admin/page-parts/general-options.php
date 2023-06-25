<?php

/**
 * The content of the general tab in the admin panel.
 */

defined('ABSPATH') || exit;

$general_fields = array(
    'user_login_label',
    'user_login_placeholder',
    'password_label',
    'password_placeholder',
    'required_error',
    'passwords_dont_match',
    'combo_to_register',
    'combo_to_login',
);
?>
<div class="lukio_user_forms_inputs_wrapper general">
    <?php
    foreach ($general_fields as $option) {
        Lukio_User_Forms_Admin_Class::print_text_option($option, $active_options[$option], $options_schematics[$option]['label']);
    }
    ?>
</div>

<div class="lukio_user_forms_radio_wrapper password_strength">
    <h4 class="lukio_user_forms_subpart_title"><?php echo $options_schematics['password_strength']['label'] ?></h4>
    <?php
    $strengths = array(
        __('No minimum', 'lukio-user-forms'),
        /* TRANSLATORS: use wordpress defaul. does not need translation */
        _x('Very weak', 'password strength'),
        /* TRANSLATORS: use wordpress defaul. does not need translation */
        _x('Weak', 'password strength'),
        /* TRANSLATORS: use wordpress defaul. does not need translation */
        _x('Medium', 'password strength'),
        /* TRANSLATORS: use wordpress defaul. does not need translation */
        _x('Strong', 'password strength'),
    );
    foreach ($strengths as $num => $text) {
        $checked = $num == $active_options['password_strength'] ? ' checked' : '';
    ?>
        <div class="lukio_user_forms_radio_pair">
            <input type="radio" id="password_strength_<?php echo $num ?>" name="password_strength" value="<?php echo $num ?>" autocomplete="off" <?php echo $checked; ?>>
            <label for="password_strength_<?php echo $num ?>"><?php echo $text; ?></label>
        </div>
    <?php
    }
    ?>
</div>

<?php Lukio_User_Forms_Admin_Class::print_switch_input('use_custom_error', $active_options['use_custom_error'], $options_schematics['use_custom_error']['label']); ?>

<div class="lukio_user_forms_inputs_wrapper custom_errors <?php echo $active_options['use_custom_error'] ? '' : ' hide_option'; ?>" data-toggle="use_custom_error">
    <span class="lukio_user_forms_text_instruction errors"><?php echo __('Encapsulate with \'{{\' and \'}}\' to use bold. {{Bold text}}', 'lukio-user-forms'); ?></span>
    <?php
    $custom_error = array(
        'empty_username',
        'empty_password',
        'invalid_username',
        'invalid_email',
        'incorrect_password',
        'lost_empty_username',
        'lost_invalid_email',
        'invalid_key',
        'expired_key',
        'password_reset_empty_space',
        'password_reset_mismatch',
    );
    foreach ($custom_error as $option) {
        Lukio_User_Forms_Admin_Class::print_text_option($option, str_replace(['<strong>', '</strong>'], ['{{', '}}'], $active_options[$option]), $options_schematics[$option]['label']);
    }
    ?>
    <p class="lukio_user_forms_text_instruction footnote"><?php echo __('* Registration errors are not editable due to WordPress not fully separating the error types', 'lukio-user-forms'); ?></p>
</div>