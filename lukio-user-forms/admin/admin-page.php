<?php

defined('ABSPATH') || exit;

global $lukio_data_base;

$option_class = Lukio_User_Forms_Options_Class::get_instance();
$options_schematics = $option_class->get_default_options_schematics();
$active_options = $option_class->get_active_options();

?>
<div class="wrap">
    <h1 class="lukio-logreg-control-panel-title"><?php echo __('Lukio user forms', 'lukio-user-forms'); ?></h1>
    <form action="" method="POST">
        <input type="hidden" name="action" value="lukio_user_forms_save_options">
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('lukio_user_forms_save_options'); ?>">

        <?php
        $tabs = array(
            array(
                'name' => __('General Settings', 'lukio-user-forms'),
                'file_path' => LUKIO_USER_FORMS_DIR . 'admin/page-parts/general-options.php'
            ),
            array(
                'name' => __('Login Settings', 'lukio-user-forms'),
                'file_path' => LUKIO_USER_FORMS_DIR . 'admin/page-parts/login-options.php'
            ),
            array(
                'name' => __('Register Settings', 'lukio-user-forms'),
                'file_path' => LUKIO_USER_FORMS_DIR . 'admin/page-parts/register-options.php'
            ),
            array(
                'name' => __('Extra css', 'lukio-user-forms'),
                'file_path' => LUKIO_USER_FORMS_DIR . 'admin/page-parts/extra-css.php'
            ),
            array(
                'name' => __('Socials', 'lukio-user-forms'),
                'file_path' => LUKIO_USER_FORMS_DIR . 'admin/page-parts/socials.php'
            ),
            array(
                'name' => __('Info', 'lukio-user-forms'),
                'file_path' => LUKIO_USER_FORMS_DIR . 'admin/page-parts/info.php'
            ),
        );

        $tabs_content_markup = '';

        // check if there is a selected tab to show
        $active_tab_index = isset($_REQUEST['tab']) ? (int)$_REQUEST['tab'] : 0;
        $active_tab_index = $active_tab_index != 0 && $active_tab_index < count($tabs) ? $active_tab_index : 0;
        ?>

        <ul class="lukio_user_forms_options_tab_wrapper">
            <?php
            // loop over the tabs to create their li and content markup
            foreach ($tabs as $index => $tab_data) {
                $active = $index == $active_tab_index ? ' active' : '';
            ?>
                <li class="lukio_user_forms_options_tab<?php echo $active; ?>" data-tab="<?php echo $index; ?>"><?php echo $tab_data['name']; ?></li>

                <?php
                ob_start();
                ?>
                <div class="lukio_user_forms_options_tab_content<?php echo $active; ?>" data-tab="<?php echo $index; ?>">
                    <?php include $tab_data['file_path']; ?>
                </div>
            <?php
                // add the tab content markup to the overall tabs content
                $tabs_content_markup .= ob_get_clean();
            }
            ?>
        </ul>

        <?php
        // print all the tabs content
        echo $tabs_content_markup;
        ?>

        <button class="button button-primary button-large" type="submit"><?php echo __('Save Settings', 'lukio-user-forms'); ?></button>
        <button class="button button-large" type="submit" name="reset_all"><?php echo __('Reset all Settings', 'lukio-user-forms'); ?></button>
    </form>
</div>