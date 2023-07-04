<?php

defined('ABSPATH') || exit;

/**
 * Lukio user forms plugin admin class
 */
class Lukio_User_Forms_Admin_Class
{
    /**
     * add actions of the class
     * 
     * @author Itai Dotan
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
        add_action('admin_footer', array($this, 'enqueue_editor'));

        add_action('plugin_action_links_' . LUKIO_USER_FORMS_PLUGIN_MAIN_FILE, array($this, 'plugin_action_links'));
    }

    /**
     * add the plugin admin menu
     * 
     * @author Itai Dotan
     */
    public function admin_menu()
    {
        add_menu_page(
            __('Lukio user forms', 'lukio-user-forms'),
            __('Lukio user forms', 'lukio-user-forms'),
            'manage_options',
            'lukio_user_forms',
            array($this, 'admin_page_markup'),
            'dashicons-buddicons-buddypress-logo',
            30
        );
    }

    /**
     * enqueue the needed styles and scripts for the admin page
     * 
     * @author Itai Dotan
     */
    public function admin_enqueue()
    {
        if (get_current_screen()->base == 'toplevel_page_lukio_user_forms') {
            // enqueue needed to the admin page
            wp_enqueue_style('lukio_user_forms_admin', LUKIO_USER_FORMS_URL . "assets/css/admin-page.min.css", array(), filemtime(LUKIO_USER_FORMS_DIR . 'assets/css/admin-page.min.css'));
            wp_enqueue_script('lukio_user_forms_admin', LUKIO_USER_FORMS_URL . "assets/js/admin-page.min.js", array(), filemtime(LUKIO_USER_FORMS_DIR . 'assets/js/admin-page.min.js'), true);
        };
    }

    /**
     * enqueue editor style in the footer to keep wp enqueue order. when added to the admin_enqueue the order is mismatched
     * 
     * @author Itai Dotan
     */
    public static function enqueue_editor()
    {
        if (get_current_screen()->base == 'toplevel_page_lukio_user_forms') {
            wp_enqueue_editor();
        }
    }

    /**
     * markup for the plugin admin page
     * 
     * @author Itai Dotan
     */
    public function admin_page_markup()
    {
        // call the save function when posted and the checks are valid
        if (
            isset($_POST['action']) && $_POST['action'] == 'lukio_user_forms_save_options' &&
            isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']), 'lukio_user_forms_save_options')
        ) {
            $this->save_options();
        }
        // include the page markup file
        include LUKIO_USER_FORMS_DIR . 'admin/admin-page.php';
    }

    /**
     * sanitize the posted option before saving to the options
     * 
     * @param string $type type of option
     * @param string $key name of the option
     * @param mix $default_value default value the of the option 
     * 
     * @return mix sanitized option
     * 
     * @author Itai Dotan
     */
    private function sanitize_option($type, $key, $default_value)
    {
        switch ($type) {
            case 'bool':
                $option =  true;
                break;
            case 'text':
                $text = stripslashes(sanitize_text_field($_POST[$key]));
                $option = $text != '' ? $text : $default_value;
                break;
            case 'error':
                $text = stripslashes(str_replace(['{{', '}}'], ['<strong>', '</strong>'], sanitize_text_field($_POST[$key])));
                $option = $text != '' ? $text : $default_value;
                break;
            case 'textarea':
                $option = sanitize_textarea_field($_POST[$key]);
                break;
            case 'absint':
                $option = absint($_POST[$key]);
                break;
            case 'extra_checkboxes':
                $option = array();

                foreach ($_POST[$key] as $index => $data) {
                    // skip the template inputs
                    if ($index === '%d') {
                        continue;
                    }

                    $description = str_replace(['<p>', '</p>'], ['', '<br>'], wp_kses_post($data['description']));
                    $meta = sanitize_text_field($data['meta']);

                    // skip when meta or description is empty or meta includes invalid character
                    if (empty($meta) || preg_match('/[^a-z_]/', $meta) || empty($description)) {
                        continue;
                    }

                    $option[] = array(
                        'required' => isset($data['required']) ? true : false,
                        'meta' => $meta,
                        'description' => $description,
                    );
                }
                break;
        }
        return $option;
    }

    /**
     * save the new posted options
     * 
     * @author Itai Dotan
     */
    private function save_options()
    {
        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $options_schematics = $option_class->get_default_options_schematics();
        $options = $option_class->get_default_options();
        if (!isset($_POST['reset_all'])) {
            foreach ($options as $key => &$option) {
                if ($options_schematics[$key]['type'] == 'bool') {
                    // set all bools to false because form dont post unchecked checkboxes
                    $option = false;
                }

                if (!isset($_POST[$key])) {
                    continue;
                }

                $option = $this->sanitize_option($options_schematics[$key]['type'], $key, $option);
            }
        }

        update_option(Lukio_User_Forms_Options_Class::OPTIONS_META_KEY, $options);
        $option_class->update_options();
    }

    /**
     * add link to the plugin option page in wp plugin page when the plugin is active
     * 
     * @param array $actions an array of plugin action links
     * 
     * @return array modified actions link when the plug in is active, un-modified when not active
     * 
     * @author Itai Dotan
     */
    public function plugin_action_links($actions)
    {
        if (isset($actions['deactivate'])) {
            $setting = array(
                'settings' => '<a href ="' . esc_url(add_query_arg('page', 'lukio_user_forms', get_admin_url() . 'admin.php')) . '">' . __('Settings', 'lukio-user-forms') . '</a>',
            );
            $actions = array_merge($setting, $actions);
        }
        return $actions;
    }

    /**
     * print text input with label for the admin page
     * 
     * @param string $name inpout name
     * @param string $value input value
     * @param string $label input label
     * 
     * @author Itai Dotan
     */
    public static function print_text_option($name, $value, $label)
    {
?>
        <label class="lukio_user_forms_label" for="<?php echo $name; ?>">
            <span><?php echo $label; ?></span>
            <input class="lukio_user_forms_text_field" type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr($value); ?>">
        </label>
    <?php
    }

    /**
     * print checkbox switch for the admin page
     * 
     * @param string $name inpout name
     * @param bool $checked true when checked
     * @param string $label input label
     * @param bool $reverse true to print switch and then label, default `false`
     * 
     * @author Itai Dotan
     */
    public static function print_switch_input($name, $checked, $label, $reverse = false)
    {
        ob_start();
    ?>
        <label class="lukio_user_forms_switch<?php echo $reverse ? ' reverse' : ' normal'; ?>" for="<?php echo $name; ?>">
            <input class="lukio_user_forms_switch_input" type="checkbox" name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php echo $checked ? ' checked' : ''; ?> autocomplete="off">
            <span class="lukio_user_forms_switch_slider"></span>
        </label>
        <?php
        $label_markup = ob_get_clean();

        $span_markup = "<span>$label</span>";
        ?>
        <div class="lukio_user_forms_switch_wrapper <?php echo $name; ?>">
            <?php
            if ($reverse) {
                echo $label_markup . $span_markup;
            } else {
                echo $span_markup . $label_markup;
            }
            ?>
        </div>
    <?php
    }

    /**
     * print shortcode with copy button
     * 
     * @param string $shortcode shortcode to print
     * 
     * @author Itai Dotan
     */
    public static function print_shortcode_copy($shortcode)
    {
        $copy = __('Click to copy', 'lukio-user-forms');
        $copied = __('Copied', 'lukio-user-forms');

    ?>
        <div class="lukio_user_forms_options_shortcode_wrapper">
            <code class="lukio_user_forms_options_shortcode"><?php echo $shortcode; ?></code>
            <button type="button" class="lukio_user_forms_options_shortcode_button button" copied="<?php echo esc_attr($copied); ?>"><?php echo $copy; ?></button>
        </div>
    <?php
    }

    /**
     * print register extra checkbox input
     * 
     * @param int $index index of the checkbox in the loop, use `-1` to print the template
     * @param bool $required true when the checkbox is required
     * @param string $meta checkbox meta
     * @param string $description checkbox description
     * 
     * @author Itai Dotan
     */
    public static function print_extra_checkbox($index, $required, $meta, $description)
    {
        $template = $index == -1;
        $output_index = $template ? '%d' : $index;
        $template_class = $template ? ' template' : '';
    ?>
        <div class="lukio_user_forms_extra_checkbox<?php echo $template_class; ?>">
            <?php Lukio_User_Forms_Admin_Class::print_switch_input("extra_checkboxes[$output_index][required]", $template ? false : $required, __('Make checkbox required', 'lukio-user-forms'), true); ?>
            <?php Lukio_User_Forms_Admin_Class::print_text_option("extra_checkboxes[$output_index][meta]", $meta, __('Meta key', 'lukio-user-forms')); ?>
            <label class="lukio_user_forms_label textarea" for="extra_checkboxes_textarea_<?php echo $output_index; ?>">
                <span class="flex_no_shrink"><?php echo __('Checkbox text', 'lukio-user-forms'); ?></span>
                <textarea class="lukio_user_forms_extra_checkbox_text<?php echo $template_class; ?>" id="extra_checkboxes_textarea_<?php echo $output_index; ?>" name="extra_checkboxes[<?php echo $output_index; ?>][description]" cols="30" rows="2"><?php echo $description; ?></textarea>
            </label>
            <button class="lukio_user_forms_extra_checkboxes_remove button button-large<?php echo $template_class; ?>" type="button"><?php echo __('Remove checkbox', 'lukio-user-forms'); ?></button>
        </div>
<?php
    }
}

new Lukio_User_Forms_Admin_Class();
