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

<h4 class="lukio_user_forms_subpart_title"><?php echo $options_schematics['extra_checkboxes']['label']; ?><span class="extra_checkboxes_help dashicons dashicons-editor-help"></span></h4>
<div class="extra_checkboxes_help_tooltip_wrappper">
    <ul class="extra_checkboxes_help_tooltip">
        <li class="extra_checkboxes_help_tooltip_li"><?php echo __('Checkbox set with an empty meta or text, will not be saved', 'lukio-user-forms'); ?></li>
        <li class="extra_checkboxes_help_tooltip_li"><?php echo __('Meta can only include lowercase English characters and underscores', 'lukio-user-forms'); ?></li>
        <li class="extra_checkboxes_help_tooltip_li"><?php echo __('Meta containing invalid characters will not be saved', 'lukio-user-forms'); ?></li>
        <li class="extra_checkboxes_help_tooltip_li"><?php echo __('It is highly recommended to prefix metas, {prefix}_{meta_name}', 'lukio-user-forms'); ?></li>
    </ul>
</div>

<div class="lukio_user_forms_extra_checkboxes_wrapper">
    <?php
    Lukio_User_Forms_Admin_Class::print_extra_checkbox(-1, '', '', '', '',);
    foreach ($active_options['extra_checkboxes'] as $index => $checkbox_data) {
        Lukio_User_Forms_Admin_Class::print_extra_checkbox($index, $checkbox_data['required'], $checkbox_data['meta'], $checkbox_data['description']);
    }
    ?>
    <button class="lukio_user_forms_extra_checkboxes_add button button-large" type="button"><?php echo __('Add checkbox', 'lukio-user-forms'); ?></button>
</div>