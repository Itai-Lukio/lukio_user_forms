<?php

/*
* The content of the register tab in the admin panel.
*/
defined('ABSPATH') || exit;

?>

<div class="lukio_user_forms_inputs_wrapper register">
    <?php
    $login_fields = array(
        'register_title',
        'register_email',
        'register_email_placeholder',
        'register_submit',
    );
    ?>

    <?php
    foreach ($login_fields as $option) {
        Lukio_User_Forms_Admin_Class::print_text_option($option, $active_options[$option], $options_schematics[$option]['label']);
    }
    ?>
</div>

<?php

foreach ($option_class->get_togglable_register_core_options() as $core_index) {
    $option_bool = $core_index . '_bool';
?>
    <div class="lukio_user_forms_togglable_wrapper">
        <?php Lukio_User_Forms_Admin_Class::print_switch_input($option_bool, $active_options[$option_bool], $options_schematics[$option_bool]['label'], true); ?>

        <div class="lukio_user_forms_inputs_wrapper togglable <?php echo $core_index;
                                                                echo $active_options[$option_bool] ? '' : ' hide_option'; ?>" data-toggle="<?php echo $option_bool; ?>">
            <?php Lukio_User_Forms_Admin_Class::print_text_option($core_index, $active_options[$core_index], __('Field label', 'lukio-user-forms')); ?>
        </div>
    </div>
<?php
}

foreach ($option_class->get_togglable_register_options() as $option_index) {
    $option_bool = $option_index . '_bool';
    $option_required = $option_index . '_required_bool';
    $option_placeholder = $option_index . '_placeholder';
?>
    <div class="lukio_user_forms_togglable_wrapper">
        <?php Lukio_User_Forms_Admin_Class::print_switch_input($option_bool, $active_options[$option_bool], $options_schematics[$option_bool]['label'], true); ?>

        <div class="lukio_user_forms_inputs_wrapper togglable <?php echo $option_index;
                                                                echo $active_options[$option_bool] ? '' : ' hide_option'; ?>" data-toggle="<?php echo $option_bool; ?>">
            <?php Lukio_User_Forms_Admin_Class::print_text_option($option_index, $active_options[$option_index], __('Field label', 'lukio-user-forms')); ?>

            <?php Lukio_User_Forms_Admin_Class::print_text_option($option_placeholder, $active_options[$option_placeholder], __('Field placeholder', 'lukio-user-forms')); ?>

            <?php Lukio_User_Forms_Admin_Class::print_switch_input($option_required, $active_options[$option_required], $options_schematics[$option_required]['label'], true); ?>

        </div>
    </div>
<?php
}
?>

<h4 class="lukio_user_forms_subpart_title"><?php echo $options_schematics['extra_checkboxs']['label'] ?></h4>
<div class="lukio_user_forms_extra_checkboxs_wrapper">
    <?php
    Lukio_User_Forms_Admin_Class::print_extra_checkbox(-1, '', '', '', '',);
    foreach ($active_options['extra_checkboxs'] as $index => $checkbox_data) {
        Lukio_User_Forms_Admin_Class::print_extra_checkbox($index, $checkbox_data['required'], $checkbox_data['meta'], $checkbox_data['description']);
    }
    ?>
    <button class="lukio_user_forms_extra_checkboxs_add button button-large" type="button"><?php echo __('Add checkbox', 'lukio-user-forms'); ?></button>
</div>