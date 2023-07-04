<?php

defined('ABSPATH') || exit;

/**
 * Lukio user forms plugin option class
 */
class Lukio_User_Forms_Options_Class
{
    /**
     * plugin option meta key
     * @var string meta key
     */
    const OPTIONS_META_KEY = 'lukio_user_forms_plugin_options';

    /**
     * instance of the plugin
     * 
     * @var Lukio_User_Forms_Options_Class|null class instance when running, null before class was first called
     */
    private static $instance = null;

    /**
     * holds the schematics of the options
     * 
     * @var array default options schematics array
     */
    private $default_options_schematics;

    /**
     * holds the default optoins
     * 
     * @var array default optoins
     */
    private $default_options = array();

    /**
     * holds the active optoins
     * 
     * @var array active optoins
     */
    private $active_options;

    /**
     * holds the togglable register options
     * 
     * @var array togglable register options
     */
    private $togglable_register_options = array();

    /**
     * holds the togglable register core options
     * 
     * @var array togglable register core options
     */
    private $togglable_register_core_options = array();

    /**
     * get an instance of the class, create new on first call
     * 
     * @return Lukio_User_Forms_Options_Class class instance
     * 
     * @author Itai Dotan
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * construct action to run when creating a new instance
     * 
     * @author Itai Dotan
     */
    private function __construct()
    {
        $this->set_default_options_schematics();
        $this->set_default_options();
        $this->update_options();
    }

    /**
     * set default options schematics
     * 
     * @author Itai Dotan
     */
    private function set_default_options_schematics()
    {
        $this->default_options_schematics = array(
            // general 
            'user_login_label' => array(
                'type' => 'text',
                'label' => __('User login', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Username or Email Address')
            ),
            'user_login_placeholder' => array(
                'type' => 'text',
                'label' => __('User login placeholder', 'lukio-user-forms'),
                'default' => __('CoolNickname', 'lukio-user-forms')
            ),
            'password_label' => array(
                'type' => 'text',
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'label' => __('Password'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Password')
            ),
            'password_placeholder' => array(
                'type' => 'text',
                'label' => __('Password placeholder', 'lukio-user-forms'),
                'default' => __('ItsASecret', 'lukio-user-forms')
            ),
            'required_error' => array(
                'type' => 'text',
                'label' => __('Required error', 'lukio-user-forms'),
                'default' => __('This input is required', 'lukio-user-forms')
            ),
            'password_repeat_label' => array(
                'type' => 'text',
                'label' => __('Password repeat label', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Confirm new password')
            ),
            'passwords_dont_match' => array(
                'type' => 'text',
                'label' => __('Passwords don\'t match', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => trim(preg_replace('/<(?<tag>.*?)>.*?<\/\g{tag}>/', '', __('<strong>Error:</strong> The passwords do not match.')))
            ),
            'combo_to_register' => array(
                'type' => 'text',
                'label' => __('Switch to register', 'lukio-user-forms'),
                'default' => __('Don\'t have an account?', 'lukio-user-forms')
            ),
            'combo_to_login' => array(
                'type' => 'text',
                'label' => __('Switch to login', 'lukio-user-forms'),
                'default' => __('Already have an account?', 'lukio-user-forms')
            ),
            'password_strength' => array(
                'type' => 'absint',
                'label' => __('Minimum allowed password strength', 'lukio-user-forms'),
                'default' => 3
            ),
            // errors
            'use_custom_error' => array(
                'type' => 'bool',
                'label' => __('Use custom errors', 'lukio-user-forms'),
                'default' => false
            ),
            'empty_username' => array(
                'type' => 'error',
                'label' => __('Empty username error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('<strong>Error:</strong> The username field is empty.')
            ),
            'empty_password' => array(
                'type' => 'error',
                'label' => __('Empty password error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('<strong>Error:</strong> The password field is empty.')
            ),
            'invalid_username' => array(
                'type' => 'error',
                'label' => __('Incorrect username error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('<strong>Error:</strong> Unknown username. Check again or try your email address.')
            ),
            'invalid_email' => array(
                'type' => 'error',
                'label' => __('Incorrect email error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('<strong>Error:</strong> Unknown email address. Check again or try your username.')
            ),
            'incorrect_password' => array(
                'type' => 'error',
                'label' => __('Incorrect password error', 'lukio-user-forms'),
                'default' => __('<strong>Error:</strong> The password you entered is incorrect.', 'lukio-user-forms')
            ),
            'lost_empty_username' => array(
                'type' => 'error',
                'label' => __('Reset password empty error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('<strong>Error:</strong> Please enter a username or email address.')
            ),
            'lost_invalid_email' => array(
                'type' => 'error',
                'label' => __('Reset password no account error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('<strong>Error:</strong> There is no account with that username or email address.')
            ),
            'invalid_key' => array(
                'type' => 'error',
                'label' => __('Invalid reset key error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Invalid key.')
            ),
            'expired_key' => array(
                'type' => 'error',
                'label' => __('Expired reset key error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Invalid key.')
            ),
            'password_reset_empty_space' => array(
                'type' => 'error',
                'label' => __('Password empty space error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('The password cannot be a space or all spaces.')
            ),
            'password_reset_mismatch' => array(
                'type' => 'error',
                'label' => __('Password mismatch error', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('<strong>Error:</strong> The passwords do not match.')
            ),
            // login
            'login_title' => array(
                'type' => 'text',
                'label' => __('Login title', 'lukio-user-forms'),
                'default' => __('Login Form', 'lukio-user-forms')
            ),
            'login_submit' => array(
                'type' => 'text',
                'label' => __('Login submit', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Log In')
            ),
            'login_remember' => array(
                'type' => 'text',
                'label' => __('Login remember label', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Remember Me')
            ),
            'lost_title' => array(
                'type' => 'text',
                'label' => __('Lost password title', 'lukio-user-forms'),
                'default' => __('Lost password', 'lukio-user-forms')
            ),
            'lost_submit' => array(
                'type' => 'text',
                'label' => __('Lost password submit', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Get New Password')
            ),
            'password_reset_title' => array(
                'type' => 'text',
                'label' => __('Reset password title', 'lukio-user-forms'),
                'default' => __('Reset password', 'lukio-user-forms')
            ),
            'lost_success_message' => array(
                'type' => 'text',
                'label' => __('Lost password success message', 'lukio-user-forms'),
                'default' => __('Reset email has been sent to your email address', 'lukio-user-forms')
            ),
            'reset_success_message' => array(
                'type' => 'text',
                'label' => __('Password reset success message', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Your password has been reset.')
            ),
            'save_password' => array(
                'type' => 'text',
                'label' => __('Save Password button', 'lukio-user-forms'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Save Password')
            ),
            // register
            'register_title' => array(
                'type' => 'text',
                'label' => __('Register title', 'lukio-user-forms'),
                'default' => __('Register Form', 'lukio-user-forms')
            ),
            'register_email' => array(
                'type' => 'text',
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'label' => trim(__('Email&nbsp;Address:'), ':'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => trim(__('Email&nbsp;Address:'), ':')
            ),
            'register_email_placeholder' => array(
                'type' => 'text',
                'label' => __('Email placeholder', 'lukio-user-forms'),
                'default' => __('e@mail.com', 'lukio-user-forms')
            ),
            'register_submit' => array(
                'type' => 'text',
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'label' => __('Register submit'),
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'default' => __('Register')
            ),
            'extra_checkboxes' => array(
                'type' => 'extra_checkboxes',
                'label' => __('Extra register checkbox', 'lukio-user-forms'),
                'default' => array()
            ),
            'extra_css' => array(
                'type' => 'textarea',
                'default' => ''
            ),
        );

        $this->create_register_togglable_fields_options();
    }

    /**
     * add togglable register fields to the schematics with there bool, add option index to $togglable_register_options
     * 
     * @author Itai Dotan
     */
    private function create_register_togglable_fields_options()
    {
        $togglable_fields_data = array(
            'register_user_login' => array(
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'name' => __('Username'),
                'core' => true
            ),
            'register_pass2' => array(
                'name' => __('Repeat password', 'lukio-user-forms'),
                'core' => true
            ),
            'register_first_name' => array(
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'name' => __('First Name'),
            ),
            'register_last_name' => array(
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'name' => __('Last Name'),
            ),
            'register_nickname' => array(
                /* TRANSLATORS: use wordpress defaul. does not need translation */
                'name' => __('Nickname'),
            ),
        );

        foreach ($togglable_fields_data as $field_index => $field_data) {
            // add the bool
            $this->default_options_schematics[$field_index . '_bool'] = array(
                'type' => 'bool',
                /* translators: %s: field dsplay_name. */
                'label' => sprintf(__('Active field: %s', 'lukio-user-forms'), $field_data['name']),
                'default' => false
            );

            // add the field
            $this->default_options_schematics[$field_index] = array(
                'type' => 'text',
                'label' => $field_data['name'],
                'default' => $field_data['name']
            );

            // when its a core option continue as it have a specific treatment
            if (isset($field_data['core']) && $field_data['core']) {
                $this->togglable_register_core_options[] = $field_index;
                continue;
            }

            $this->default_options_schematics[$field_index . '_placeholder'] = array(
                'type' => 'text',
                'label' => __('Placeholder', 'lukio-user-forms'),
                'default' => $field_data['name']
            );

            $this->default_options_schematics[$field_index . '_required_bool'] = array(
                'type' => 'bool',
                'label' => __('Make field required', 'lukio-user-forms'),
                'default' => false
            );
            // add opton index to the togglable array
            $this->togglable_register_options[] = $field_index;
        }
    }

    /**
     * populate the default_options from the schematics
     * 
     * @author Itai Dotan
     */
    private function set_default_options()
    {
        foreach ($this->default_options_schematics as $option_index => $option_data) {
            $this->default_options[$option_index] = $option_data['default'];
        }
    }

    /**
     * update active_options with the saved options form OPTIONS_META_KEY
     * 
     * @author Itai Dotan
     */
    public function update_options()
    {
        $this->active_options = array_merge($this->default_options, get_option(Lukio_User_Forms_Options_Class::OPTIONS_META_KEY, array()));
    }

    /**
     * get default plugin options
     * 
     * @return array default plugin options
     * 
     * @author Itai Dotan
     */
    public function get_default_options_schematics()
    {
        return $this->default_options_schematics;
    }

    /**
     * get default plugin options
     * 
     * @return array default plugin options
     * 
     * @author Itai Dotan
     */
    public function get_default_options()
    {
        return $this->default_options;
    }

    /**
     * get active plugin options
     * 
     * @return array active plugin options
     * 
     * @author Itai Dotan
     */
    public function get_active_options()
    {
        return $this->active_options;
    }

    /**
     * get togglable register options
     * 
     * @return array togglable register options
     * 
     * @author Itai Dotan
     */
    public function get_togglable_register_options()
    {
        return $this->togglable_register_options;
    }

    /**
     * get togglable register core options
     * 
     * @return array togglable register core options
     * 
     * @author Itai Dotan
     */
    public function get_togglable_register_core_options()
    {
        return $this->togglable_register_core_options;
    }

    /**
     * get a single active option value
     * 
     * @param string $option option to get
     * 
     * @return mix|null option value on success, `null` on failure
     * 
     * @author Itai Dotan
     */
    public static function get_active_option($option)
    {
        $instance = self::get_instance();
        $active_options = $instance->get_active_options();

        if (isset($active_options[$option])) {
            return $active_options[$option];
        }
        return null;
    }
}
