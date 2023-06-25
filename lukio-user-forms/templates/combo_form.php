<?php

/**
 * The template for displaying the combo form
 *
 * This template can be overridden by copying it to yourtheme/lukio-user-forms/combo_form.php.
 */

defined('ABSPATH') || exit;
?>

<div class="lukio_user_forms_combo_wrapper">
    <div class="lukio_user_forms_combo_form_wrapper login<?php echo $show_registration ? ' hide_content' : ''; ?>">
        <?php echo do_shortcode('[lukio_login_form]'); ?>

        <a class="lukio_user_forms_combo_switch" href="<?php echo esc_url(home_url($_SERVER['REDIRECT_URL'] . '?registration')); ?>"><?php echo $to_register; ?></a>
    </div>
    <div class="lukio_user_forms_combo_form_wrapper registration<?php echo $show_registration ? '' : ' hide_content'; ?>">
        <?php echo do_shortcode('[lukio_register_form]'); ?>

        <a class="lukio_user_forms_combo_switch" href="<?php echo esc_url(home_url($_SERVER['REDIRECT_URL'])); ?>"><?php echo $to_login; ?></a>
    </div>
</div>