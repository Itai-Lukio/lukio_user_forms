<?php

/**
 * The template for displaying reset password form
 *
 * This template can be overridden by copying it to yourtheme/lukio-user-forms/password_reset.php.
 */

defined('ABSPATH') || exit;

?>

<div class="lukio_user_forms_popup_wrapper password_reset">
    <div class="lukio_user_forms_popup_content">
        <h4 class="lukio_user_forms_title reset_title"><?php echo esc_html($active_options['password_reset_title']); ?></h4>

        <p class="lukio_user_forms_result_error"></p>

        <form class="lukio_user_forms_form reset_password" action="<?php echo esc_url(network_site_url('wp-login.php?action=resetpass', 'login_post')); ?>" method="post" autocomplete="off">
            <div class="lukio_user_forms_input_wrapper luf_required">
                <label class="lukio_user_forms_login_input_label align_start" for="reset_password"><?php echo esc_html($active_options['password_label']); ?></label>
                <?php
                Lukio_User_Forms_Setup::echo_password_input('reset_password', $active_options['password_placeholder'], 'pass1', true);
                Lukio_User_Forms_Setup::echo_input_error_span('reset_password', $active_options['required_error']);
                ?>
            </div>

            <div class="lukio_user_forms_input_wrapper">
                <label class="lukio_user_forms_login_input_label align_start" for="reset_repeat_password"><?php echo esc_html($active_options['password_repeat_label']); ?></label>
                <?php
                Lukio_User_Forms_Setup::echo_password_input('reset_repeat_password', $active_options['password_placeholder'], 'pass2');
                Lukio_User_Forms_Setup::echo_input_error_span('reset_repeat_password', $active_options['passwords_dont_match']);
                ?>
            </div>

            <?php do_action('lukio_user_forms_password_reset_before_button'); ?>
            <button class="lukio_user_forms_submit" type="submit"><?php echo esc_html($active_options['save_password']); ?></button>

            <p class="lukio_user_forms_success"><?php echo esc_html($active_options['reset_success_message']); ?></p>
        </form>

        <?php Lukio_User_Forms_Setup::echo_popup_close_button(); ?>
    </div>
</div>