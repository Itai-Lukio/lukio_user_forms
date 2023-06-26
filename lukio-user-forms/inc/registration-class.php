<?php

defined('ABSPATH') || exit;

/**
 * Lukio user forms plugin registration class
 */
class Lukio_User_Forms_registration
{
    /**
     * add the needed actions and shortcode for the class
     * 
     * @author Itai Dotan
     */
    public function __construct()
    {
        add_shortcode('lukio_register_form', array($this, 'register_form'));

        add_action('lukio_user_forms_register_before_button', array($this, 'register_hiddens'));

        $this->register_class_ajaxs();
    }

    /**
     * register ajaxs for the class
     * 
     * @author Itai Dotan
     */
    private function register_class_ajaxs()
    {
        $actions = array(
            'lukio_user_forms_register' => 'register_ajax',
        );

        foreach ($actions as $action => $function) {
            add_action('wp_ajax_' . $action, array($this, $function));
            add_action('wp_ajax_nopriv_' . $action, array($this, $function));
        }
    }

    /**
     * parse errors from wp_error to a string with <br> between errors
     * 
     * @param WP_Error $wp_error wordpress wrror class 
     * 
     * @return string parsed errors
     * 
     * @author Itai Dotan
     */
    private function parse_registration_errors($wp_error)
    {
        return implode('<br>', $wp_error->get_error_messages());
    }

    /**
     * handle registration form setup before calling wp 'edit_user'
     * 
     * all sanitizing happen in edit_user()
     * 
     * @return int|WP_Error user ID of the user or WP_Error on failure
     * 
     * @author Itai Dotan
     */
    private function handle_registration()
    {
        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $active_options = $option_class->get_active_options();

        // populate needed fields when not required field in option
        if (!$active_options['register_user_login_bool']) {
            $_POST['user_login'] = $_POST['email'];
        }
        if (!$active_options['register_pass2_bool']) {
            $_POST['pass2'] = $_POST['pass1'];
        }

        // remove role, make sure 'wp_insert_user' to use get_option('default_role')
        unset($_POST['role']);

        // make sure 'edit_user' will not send reset password email to the user
        unset($_POST['send_user_notification']);

        // keep wp way of populating the user display name dropdown in profile page
        if (isset($_POST['nickname'])) {
            $_POST['display_name'] = $_POST['nickname'];
        }

        $result = edit_user();

        // when user been created add checked checkboxs to user meta
        if (!is_wp_error($result)) {
            foreach ($active_options['extra_checkboxs'] as $checkbox_data) {
                if (isset($_POST[$checkbox_data['meta']])) {
                    update_user_meta($result, $checkbox_data['meta'], true);
                }
            }
        }

        return $result;
    }

    /**
     * check if all registration required field are filled
     * 
     * @return array filled with missing fields name, empty when all valid
     * 
     * @author Itai Dotan
     */
    private function check_registration_required()
    {
        $missing = array();
        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $active_options = $option_class->get_active_options();

        foreach (['email', 'pass1'] as $core_field) {
            if (!isset($_POST[$core_field]) || trim($_POST[$core_field]) == '') {
                $missing[] = $core_field;
            }
        }

        foreach ($option_class->get_togglable_register_core_options() as $togglable_core) {
            $name = str_replace('register_', '', $togglable_core);
            if ($active_options[$togglable_core . '_bool'] && (!isset($_POST[$name]) || trim($_POST[$name]) == '')) {
                $missing[] = $name;
            }
        }

        foreach ($option_class->get_togglable_register_options() as $option) {
            $name = str_replace('register_', '', $option);
            if (
                $active_options[$option . '_bool'] && $active_options[$option . '_required_bool'] &&
                (!isset($_POST[$name]) || trim($_POST[$name]) == '')
            ) {
                $missing[] = $name;
            }
        }

        foreach ($active_options['extra_checkboxs'] as $checkbox_data) {
            if ($checkbox_data['required'] && !isset($_POST[$checkbox_data['meta']])) {
                $missing[] = $checkbox_data['meta'];
            }
        }

        return apply_filters('lukio_user_forms_registration_required', $missing);
    }

    /**
     * handles posted registration not using ajax
     * 
     * @return array indexed with 'fields' when there are missing fields, 'errors' when errors was returned from the handler
     * 
     * @author Itai Dotan
     */
    private function posted_registration()
    {
        if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'luf_register'))) {
            return;
        }

        $check_required = $this->check_registration_required();
        if (!empty($check_required)) {
            return array('fields' => $check_required);
        }

        // make sure the wp user.php is present, by default wp use it only in admin API
        require_once ABSPATH . 'wp-admin/includes/user.php';
        $registration_result = $this->handle_registration();

        if (is_wp_error($registration_result)) {
            return array('errors' => $this->parse_registration_errors($registration_result));
        } else {
            wp_set_current_user($registration_result);
            wp_set_auth_cookie($registration_result, true);
            wp_safe_redirect(sanitize_text_field($_POST['redirect_to']));
            exit;
        }
    }

    /**
     * handles posted registration using ajax
     * 
     * @author Itai Dotan
     */
    public function register_ajax()
    {
        if (!(isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'luf_register'))) {
            return;
        }
        $response = array();

        $check_required = $this->check_registration_required();
        if (!empty($check_required)) {
            $response['status'] = 'error';
            $response['fields'] = $check_required;
            echo json_encode($response);
            die;
        }

        $registration_result = $this->handle_registration();

        if (is_wp_error($registration_result)) {
            $response['status'] = 'error';
            $response['error'] = $this->parse_registration_errors($registration_result);
        } else {
            wp_set_current_user($registration_result);
            wp_set_auth_cookie($registration_result, true);
            $response['status'] = 'success';
            $response['redirect'] = sanitize_text_field($_POST['redirect_to']);
        }
        echo json_encode($response);
        die;
    }

    /**
     * function of the shortcode 'lukio_register_form', output the form markup
     *  
     * @return string form markup
     * 
     * @author Itai Dotan
     */
    public function register_form()
    {
        $posted_result = $this->posted_registration();
        $posted_errors = isset($posted_result['errors']) ? $posted_result['errors'] : array();
        $missing_fields = isset($posted_result['fields']) ? $posted_result['fields'] : array();

        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $active_options = $option_class->get_active_options();
        $use_id = $active_options['register_user_login_bool'];
        $repeat_password = $active_options['register_pass2_bool'];

        $togglable_fields = array();
        foreach ($option_class->get_togglable_register_options() as $option) {
            if ($active_options[$option . '_bool']) {
                $togglable_fields[str_replace('register_', '', $option)] = array(
                    'required' => $active_options[$option . '_required_bool'],
                    'label' => $active_options[$option],
                    'placeholder' => $active_options[$option . '_placeholder']
                );
            }
        }
        ob_start();

        include Lukio_User_Forms_Setup::get_template_path('register');
        Lukio_User_Forms_Setup::add_password_strength_core();

        return ob_get_clean();
    }

    /**
     * print register form hidden inputs
     * 
     * @author Itai Dotan
     */
    public function register_hiddens()
    {
        $actions = array(
            'action' => 'lukio_user_forms_register',
            'redirect_to' => esc_attr(get_site_url()),
            'nonce' => wp_create_nonce('luf_register')
        );

        foreach ($actions as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }
    }
}

new Lukio_User_Forms_registration();
