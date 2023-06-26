<?php

defined('ABSPATH') || exit;

/**
 * Lukio user forms plugin login class
 */
class Lukio_User_Forms_login
{
    /**
     * add the needed actions and shortcode for the class, run needed setup functions
     * 
     * @author Itai Dotan
     */
    public function __construct()
    {
        add_shortcode('lukio_login_form', array($this, 'login_form'));

        add_action('wp_footer', array($this, 'maybe_print_password_reset'), 10);

        add_action('lukio_user_forms_login_before_button', array($this, 'login_hiddens'));
        add_action('lukio_user_forms_password_reset_before_button', array($this, 'reset_hiddens'));
        add_action('lukio_user_forms_lost_password_before_button', array($this, 'lost_hiddens'));

        $this->register_class_ajaxs();
        $this->fix_no_script_forms_action();
    }

    /**
     * register ajaxs for the class
     * 
     * @author Itai Dotan
     */
    private function register_class_ajaxs()
    {
        $actions = array(
            'lukio_user_forms_login' => 'login_ajax',
            'lukio_user_forms_get_reset' => 'ajax_get_reset_form',
            'lukio_user_forms_lost_password' => 'lost_password_ajax',
            'lukio_user_forms_password_reset' => 'password_reset_ajax',
        );

        foreach ($actions as $action => $function) {
            add_action('wp_ajax_' . $action, array($this, $function));
            add_action('wp_ajax_nopriv_' . $action, array($this, $function));
        }
    }

    /**
     * fix the form action to wordpress base action.
     * 
     * this is needed when client have its javascript disabled.
     * form is sent to be handled by wordpress, not by the plugin ajax function
     * 
     * @author Itai Dotan
     */
    public function fix_no_script_forms_action()
    {
        $forms = array(
            'lukio_user_forms_login' => 'login',
            'lukio_user_forms_lost_password' => 'lost_password',
        );

        foreach ($forms as $form_action => $function) {
            add_action("login_form_{$form_action}", array($this, "{$function}_action_fix"));
        }
    }

    /**
     * fix the action of the login form
     * 
     * @author Itai Dotan
     */
    public function login_action_fix()
    {
        global $action;
        $action = 'login';
    }

    /**
     * fix the action of the lost password form
     * 
     * @author Itai Dotan
     */
    public function lost_password_action_fix()
    {
        global $action;
        $action = 'lostpassword';
    }

    /**
     * print the password reset form when required
     * 
     * @author Itai Dotan
     */
    public function maybe_print_password_reset()
    {
        if (!isset($_REQUEST[Lukio_User_Forms_Setup::PASSWORD_RESET_VAR])) {
            return;
        }

        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $active_options = $option_class->get_active_options();

        include Lukio_User_Forms_Setup::get_template_path('password_reset');

        Lukio_User_Forms_Setup::add_password_strength_core();
    }

    /**
     * send back the password reset form.
     * 
     * will be called with ajax when a page is behind cache system and the form is not present in the page
     * 
     * @author Itai Dotan
     */
    public function ajax_get_reset_form()
    {
        ob_start();
        $this->maybe_print_password_reset();
        $fragment = ob_get_clean();

        echo json_encode(array(
            'fragment' => $fragment,
        ));
        die;
    }

    /**
     * function of the shortcode 'lukio_login_form', output the form markup
     * 
     * @return string form markup
     * 
     * @author Itai Dotan
     */
    public function login_form()
    {
        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $active_options = $option_class->get_active_options();
        $show_lost = isset($_GET['lostpassword']);

        ob_start();

        echo '<div class="lukio_user_forms_login_wrapper">';
        include Lukio_User_Forms_Setup::get_template_path('login');
        include Lukio_User_Forms_Setup::get_template_path('lost_password');
        echo '</div>';

        return ob_get_clean();
    }

    /**
     * parse error message from the given wp_error
     * 
     * @param WP_Error $wp_error instance of WP_Error class
     * @param string $prefix prefix of the error in the active_options
     * 
     * @return string error message
     * 
     * @author Itai Dotan
     */
    private function parse_error($wp_error, $prefix = '')
    {
        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $active_options = $option_class->get_active_options();

        $error_code = $wp_error->get_error_code();
        if ($active_options['use_custom_error'] && isset($active_options[$prefix . $error_code])) {
            $error = $active_options[$error_code];
        } else {
            $error = $wp_error->get_error_message();
            if ($error_code == 'incorrect_password') {
                // remove the lost password link
                $error = trim(preg_replace('/<a.*?>[\s\S]*<\/a>/', '', $error));
            }
        }

        return $error;
    }

    /**
     * handle login using ajax
     * 
     * @author Itai Dotan
     */
    public function login_ajax()
    {
        // sanitizing already happens inside 'wp_signon'
        $user = wp_signon(array(
            'user_login' => $_POST["log"],
            'user_password' => $_POST["pwd"],
            'remember' => isset($_POST['rememberme'])
        ));

        $response = array();
        if (is_wp_error($user)) {
            $response['status'] = 'error';
            $response['error'] = $this->parse_error($user);
        } else {
            $response['status'] = 'success';
            $response['redirect'] = sanitize_text_field($_POST['redirect_to']);
        }

        echo json_encode($response);
        die;
    }


    /**
     * set the message to be sent in the reset password message
     * 
     * @param string $message email message
     * @param string $key the activation key
     * @param string $user_login the username for the user
     * 
     * @return string new email message
     * 
     * @author Itai Dotan
     */
    public function set_reset_email_message($message, $key, $user_login)
    {
        if (is_multisite()) {
            $site_name = get_network()->site_name;
        } else {
            $site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }
        $site_name = apply_filters('lukio_user_forms_lost_email_site_name', $site_name);

        $password_var = Lukio_User_Forms_Setup::PASSWORD_RESET_VAR;
        $reset_link = network_site_url("?$password_var=$key&login=" . rawurlencode($user_login), 'login');

        if (apply_filters('lukio_user_forms_lost_email_add_requester_ip', true)) {
            if (!is_user_logged_in()) {
                $requester_ip = $_SERVER['REMOTE_ADDR'];
                if ($requester_ip) {
                    $reset_link .= '<br><br>' . sprintf(
                        /* TRANSLATORS: use wordpress defaul. does not need translation */
                        __('This password reset request originated from the IP address %s.'),
                        $requester_ip
                    ) . "\r\n";
                }
            }
        }

        ob_start();
        include Lukio_User_Forms_Setup::get_template_path('lost_email');
        $message = ob_get_clean();
        return $message;
    }

    /**
     * set email headers to text/html UTF-8
     * 
     * @param array $atts array of the wp_mail() arguments
     * 
     * @author Itai Dotan
     */
    public function set_email_headers($atts)
    {
        $atts['headers'] = array('Content-Type: text/html; charset=UTF-8');
        return $atts;
    }

    /**
     * handle lost password form using ajax
     * 
     * @author Itai Dotan
     */
    public function lost_password_ajax()
    {
        add_filter('retrieve_password_message', array($this, 'set_reset_email_message'), 10, 3);

        add_filter('wp_mail', array($this, 'set_email_headers'));

        $retrieve_result = retrieve_password();
        $response = array();
        if (is_wp_error($retrieve_result)) {
            $response['status'] = 'error';
            $response['error'] = $this->parse_error($retrieve_result, 'lost');
        } else {
            $response['status'] = 'success';
            $response['redirect'] = 'no_redirect';
        }
        echo json_encode($response);
        die;
    }

    /**
     * handle reset password form using ajax
     * 
     * @author Itai Dotan
     */
    public function password_reset_ajax()
    {
        if (!isset($_POST['key']) || !isset($_POST['login'])) {
        }
        $key = wp_unslash($_POST['key']);
        $login = wp_unslash($_POST['login']);
        $user = check_password_reset_key($key, $login);

        $response = array();
        if (is_wp_error($user)) {
            $response['status'] = 'error';
            $response['error'] = $this->parse_error($user, 'lost');

            echo json_encode($response);
            die;
        }

        $errors = new WP_Error();
        // Check if password is one or all empty spaces.
        if (!empty($_POST['pass1'])) {
            $_POST['pass1'] = trim($_POST['pass1']);

            if (empty($_POST['pass1'])) {
                $errors->add('password_reset_empty_space', __('The password cannot be a space or all spaces.'));
            }
        }

        // Check if password fields do not match.
        if (!empty($_POST['pass1']) && trim($_POST['pass2']) !== $_POST['pass1']) {
            $errors->add('password_reset_mismatch', __('<strong>Error:</strong> The passwords do not match.'));
        }

        if ($errors->has_errors()) {
            $response['status'] = 'error';
            $response['error'] = $this->parse_error($errors, 'lost');
        } else {
            reset_password($user, $_POST['pass1']);
            $response['status'] = 'success';
            $response['redirect'] = 'no_redirect';
        }
        echo json_encode($response);
        die;
    }

    /**
     * print login form hidden inputs
     * 
     * @author Itai Dotan
     */
    public function login_hiddens()
    {
        $this->print_hiddens(array(
            'action' => 'lukio_user_forms_login',
            'redirect_to' => esc_attr(get_site_url())
        ));
    }

    /**
     * print password reset form hidden inputs
     * 
     * @author Itai Dotan
     */
    public function reset_hiddens()
    {
        $this->print_hiddens(array(
            'action' => 'lukio_user_forms_password_reset',
            'key' => esc_attr(sanitize_text_field($_GET[Lukio_User_Forms_Setup::PASSWORD_RESET_VAR])),
            'login' => esc_attr(sanitize_text_field($_GET['login']))
        ));
    }

    /**
     * print lost password form hidden inputs
     * 
     * @author Itai Dotan
     */
    public function lost_hiddens()
    {
        $this->print_hiddens(array(
            'action' => 'lukio_user_forms_lost_password'
        ));
    }

    /**
     * print form hidden inputs
     * 
     * @param array $actions array of hiddens to print
     * 
     * @author Itai Dotan
     */
    private function print_hiddens($actions)
    {
        foreach ($actions as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }
    }
}

new Lukio_User_Forms_login();
