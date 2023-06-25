<?php

/**
 * The template for displaying the lost password form
 *
 * This template can be overridden by copying it to yourtheme/lukio-user-forms/lost_password.php.
 */

defined('ABSPATH') || exit;

?>

<div class="lukio_user_forms_lost_content<?php echo !$show_lost ? ' hide_content' : ''; ?>">
    <h2 class="lukio_user_forms_title"><?php echo esc_html($active_options['lost_title']); ?></h2>

    <p class="lukio_user_forms_result_error"></p>

    <form class="lukio_user_forms_form lost_password" action="<?php echo esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post')); ?>" method="post">

        <div class="lukio_user_forms_input_wrapper luf_required">
            <label class="lukio_user_forms_login_input_label align_start" for="user_login"><?php echo esc_html($active_options['user_login_label']); ?></label>
            <input class="lukio_user_forms_input" type="text" id="user_login" name="user_login" placeholder="<?php echo esc_attr($active_options['user_login_placeholder']); ?>">
            <?php Lukio_User_Forms_Setup::echo_input_error_span('user_login', $active_options['required_error']); ?>
        </div>

        <?php do_action('lukio_user_forms_lost_password_before_button'); ?>
        <button class="lukio_user_forms_submit" type="submit"><?php echo esc_html($active_options['lost_submit']); ?></button>

        <p class="lukio_user_forms_success"><?php echo esc_html($active_options['lost_success_message']); ?></p>
    </form>

    <a class="lukio_user_forms_login_lost_switch" href="<?php echo esc_url(home_url($_SERVER['REDIRECT_URL'])); ?>"><?php
                                                                                                                    /* TRANSLATORS: use wordpress defaul. does not need translation */
                                                                                                                    echo __('Log in');
                                                                                                                    ?></a>
</div>