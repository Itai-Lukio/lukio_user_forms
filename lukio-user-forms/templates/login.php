<?php

/**
 * The template for displaying the login form
 *
 * This template can be overridden by copying it to yourtheme/lukio-user-forms/login.php.
 */

defined('ABSPATH') || exit;

?>
<div class="lukio_user_forms_login_content<?php echo $show_lost ? ' hide_content' : ''; ?>">
    <h2 class="lukio_user_forms_title"><?php echo esc_html($active_options['login_title']); ?></h2>

    <p class="lukio_user_forms_result_error"></p>

    <form class="lukio_user_forms_form login" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">

        <div class="lukio_user_forms_input_wrapper luf_required">
            <label class="lukio_user_forms_login_input_label align_start" for="user_login"><?php echo esc_html($active_options['user_login_label']); ?></label>
            <input class="lukio_user_forms_input" type="text" id="user_login" name="log" placeholder="<?php echo esc_attr($active_options['user_login_placeholder']); ?>">
            <?php Lukio_User_Forms_Setup::echo_input_error_span('user_login', $active_options['required_error']); ?>
        </div>

        <div class="lukio_user_forms_input_wrapper luf_required">
            <label class="lukio_user_forms_login_input_label align_start" for="user_password"><?php echo esc_html($active_options['password_label']); ?></label>
            <?php
            Lukio_User_Forms_Setup::echo_password_input('user_password', $active_options['password_placeholder']);
            Lukio_User_Forms_Setup::echo_input_error_span('user_password', $active_options['required_error']);
            ?>
        </div>

        <div class="lukio_user_forms_input_wrapper align_start">
            <input type="checkbox" name="rememberme" id="rememberme" value="forever">
            <label class="lukio_user_forms_login_checkbox_label" for="rememberme"><?php echo esc_html($active_options['login_remember']); ?></label>
        </div>

        <?php do_action('lukio_user_forms_login_before_button'); ?>
        <button class="lukio_user_forms_submit" type="submit" name="login"><?php echo esc_html($active_options['login_submit']); ?></button>
    </form>

    <?php do_action('lukio_user_forms_socials'); ?>

    <a class="lukio_user_forms_login_lost_switch" href="<?php echo esc_url(home_url($_SERVER['REDIRECT_URL'] . '?lostpassword')); ?>"><?php
                                                                                                                                        /* TRANSLATORS: use wordpress defaul. does not need translation */
                                                                                                                                        echo __('Lost your password?');
                                                                                                                                        ?></a>
</div>