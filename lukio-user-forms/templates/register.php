<?php

/**
 * The template for displaying register form
 *
 * This template can be overridden by copying it to yourtheme/lukio-user-forms/register.php.
 */

defined('ABSPATH') || exit;

?>
<div class="lukio_user_forms_register_content">
    <h2 class="lukio_user_forms_title"><?php echo esc_html($active_options['register_title']); ?></h2>

    <p class="lukio_user_forms_result_error<?php if (!empty($posted_errors)) {
                                                echo ' show';
                                            }; ?>"><?php echo $posted_errors; ?></p>

    <form class="lukio_user_forms_form register" action="" method="post">
        <?php
        if ($use_id) {
        ?>
            <div class="lukio_user_forms_input_wrapper luf_required<?php Lukio_User_Forms_Setup::maybe_error_class('user_login', $missing_fields); ?>">
                <label class="lukio_user_forms_login_input_label align_start" for="register_user_login"><?php echo esc_html($active_options['register_user_login']); ?></label>
                <input class="lukio_user_forms_input" type="text" id="register_user_login" name="user_login" placeholder="<?php echo esc_attr($active_options['user_login_placeholder']); ?>">
                <?php Lukio_User_Forms_Setup::echo_input_error_span('register_user_login', $active_options['required_error']); ?>
            </div>
        <?php
        }
        ?>

        <div class="lukio_user_forms_input_wrapper luf_required<?php Lukio_User_Forms_Setup::maybe_error_class('email', $missing_fields); ?>">
            <label class="lukio_user_forms_login_input_label align_start" for="email"><?php echo esc_html($active_options['register_email']); ?></label>
            <input class="lukio_user_forms_input" type="email" id="email" name="email" placeholder="<?php echo esc_attr($active_options['register_email_placeholder']); ?>">
            <?php Lukio_User_Forms_Setup::echo_input_error_span('email', $active_options['required_error']); ?>
        </div>
        <div class="lukio_user_forms_input_wrapper luf_required<?php Lukio_User_Forms_Setup::maybe_error_class('pass1', $missing_fields); ?>">
            <label class="lukio_user_forms_login_input_label align_start" for="register_user_password"><?php echo esc_html($active_options['password_label']); ?></label>
            <?php
            Lukio_User_Forms_Setup::echo_password_input('register_user_password', $active_options['password_placeholder'], 'pass1', true);
            Lukio_User_Forms_Setup::echo_input_error_span('register_user_password', $active_options['required_error']);
            ?>
        </div>

        <?php
        if ($repeat_password) {
        ?>
            <div class="lukio_user_forms_input_wrapper<?php Lukio_User_Forms_Setup::maybe_error_class('pass2', $missing_fields); ?>">
                <label class="lukio_user_forms_login_input_label align_start" for="repeat_password"><?php echo esc_html($active_options['register_pass2']); ?></label>
                <?php
                Lukio_User_Forms_Setup::echo_password_input('repeat_password', $active_options['password_placeholder'], 'pass2');
                Lukio_User_Forms_Setup::echo_input_error_span('repeat_password', $active_options['passwords_dont_match']);
                ?>
            </div>
        <?php
        }

        foreach ($togglable_fields as $togglable => $togglable_data) {
            $attr = esc_attr($togglable);
        ?>
            <div class="lukio_user_forms_input_wrapper<?php
                                                        echo $togglable_data['required'] ? ' luf_required' : '';
                                                        Lukio_User_Forms_Setup::maybe_error_class($attr, $missing_fields);
                                                        ?>">
                <label class="lukio_user_forms_login_input_label align_start" for="<?php echo $attr; ?>"><?php echo esc_html($togglable_data['label']); ?></label>
                <input class="lukio_user_forms_input" type="text" id="<?php echo $attr; ?>" name="<?php echo $attr; ?>" placeholder="<?php echo esc_attr($togglable_data['placeholder']); ?>">
                <?php Lukio_User_Forms_Setup::echo_input_error_span($attr, $active_options['required_error']); ?>
            </div>
        <?php
        }

        foreach ($active_options['extra_checkboxes'] as $checkbox_data) {
        ?>
            <div class="lukio_user_forms_input_wrapper checkbox<?php
                                                                echo $checkbox_data['required'] ? ' luf_required' : '';
                                                                Lukio_User_Forms_Setup::maybe_error_class($checkbox_data['meta'], $missing_fields)
                                                                ?>">
                <input class="lukio_user_forms_meta_checkbox" type="checkbox" name="<?php echo $checkbox_data['meta']; ?>" id="meta_<?php echo $checkbox_data['meta']; ?>">
                <label class="lukio_user_forms_meta_label" for="meta_<?php echo $checkbox_data['meta']; ?>"><?php echo $checkbox_data['description']; ?></label>
            </div>
        <?php
        }
        ?>

        <?php do_action('lukio_user_forms_register_before_button'); ?>
        <button class="lukio_user_forms_submit" type="submit"><?php echo esc_html($active_options['register_submit']); ?></button>
    </form>

    <?php do_action('lukio_user_forms_socials'); ?>

</div>
<?php
