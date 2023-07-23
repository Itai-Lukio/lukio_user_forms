<?php

defined('ABSPATH') || exit;

/**
 * Lukio user forms plugin setup class
 */
class Lukio_User_Forms_Setup
{
    /**
     * plugin var used for password reset key
     * @var string password reset var
     */
    const PASSWORD_RESET_VAR = 'luf_reset';

    /**
     * add the needed actions and shortcode for the class
     * 
     * @author Itai Dotan
     */
    public function __construct()
    {
        add_action('init', array($this, 'init'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action('wp_print_footer_scripts', array($this, 'print_socials_scripts'));

        add_action('lukio_user_forms_socials', array($this, 'print_socials_buttons'));

        add_shortcode('lukio_combo_form', array($this, 'combo_form'));

        add_action('wp_ajax_lukio_user_forms_integrations_request', array($this, 'handle_integrations_request'));
        add_action('wp_ajax_nopriv_lukio_user_forms_integrations_request', array($this, 'handle_integrations_request'));
    }

    /**
     * init action to set up the plugin
     * 
     * @author Itai Dotan
     */
    public function init()
    {
        load_plugin_textdomain('lukio-user-forms', false, 'lukio-user-forms/languages');

        Lukio_User_Forms_Options_Class::get_instance();
    }

    /**
     * enqueue and localize the plugin styles and scripts
     * 
     * @author Itai Dotan
     */
    public function enqueue()
    {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('lukio_user_forms_stylesheet', LUKIO_USER_FORMS_URL . 'assets/css/lukio-user-forms.min.css', array(), filemtime(LUKIO_USER_FORMS_DIR . 'assets/css/lukio-user-forms.min.css'));
        wp_add_inline_style('lukio_user_forms_stylesheet', $this->get_extra_css());

        wp_enqueue_script('lukio_user_forms_script', LUKIO_USER_FORMS_URL . 'assets/js/lukio-user-forms.min.js', array('jquery'), filemtime(LUKIO_USER_FORMS_DIR . 'assets/js/lukio-user-forms.min.js'), true);
        wp_localize_script('lukio_user_forms_script', 'lukio_user_forms_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            /* TRANSLATORS: use wordpress defaul. does not need translation */
            'show_password' => __('Show password'),
            /* TRANSLATORS: use wordpress defaul. does not need translation */
            'hide_password' => __('Hide password'),
            'password_reset' => Lukio_User_Forms_Setup::PASSWORD_RESET_VAR,
            'password_strength' => Lukio_User_Forms_Options_Class::get_active_option('password_strength'),
            'integration_redirect' => get_site_url(),
        ));
    }

    /**
     * return css string of the user extra css
     * 
     * @return string css string
     * 
     * @author Itai Dotan
     */
    private function get_extra_css()
    {
        $option_class = Lukio_User_Forms_Options_Class::get_instance();
        $opener = "/*\nCreated by Lukio User Forms Plugin\nExtra css set in the plugin option page\n*/\n";
        //trim and remove multi whitespaces from the user css
        return $opener . trim(preg_replace('/(?:\s\s+)/', '', $option_class->get_active_options()['extra_css']));
    }

    /**
     * function of the shortcode 'lukio_combo_form', output the form markup
     * 
     * @param array $atts user defined attributes in shortcode tag, default `[]`
     * 
     * @return string form markup
     * 
     * @author Itai Dotan
     */
    public function combo_form()
    {
        $show_registration = isset($_GET['registration']);
        $to_login = Lukio_User_Forms_Options_Class::get_active_option('combo_to_login');
        $to_register = Lukio_User_Forms_Options_Class::get_active_option('combo_to_register');

        ob_start();

        include Lukio_User_Forms_Setup::get_template_path('combo_form');

        return ob_get_clean();
    }

    /**
     * print socials_buttons template
     * 
     * @author Itai Dotan
     */
    public function print_socials_buttons()
    {
        include Lukio_User_Forms_Setup::get_template_path('socials_buttons');
    }

    /**
     * print socials external scripts
     * 
     * @author Itai Dotan
     */
    public function print_socials_scripts()
    {
        // print google scripts
        if (Lukio_User_Forms_Options_Class::get_google_client()) {
?>
            <script>
                window.onload = function() {
                    google.accounts.id.initialize({
                        client_id: <?php echo "'" . Lukio_User_Forms_Options_Class::get_google_client() . "'"; ?>,
                        nonce: <?php echo "'" . wp_create_nonce('lukio_user_forms_google') . "'"; ?>,
                        callback: window.handle_google_credential_response,
                    });
                    jQuery('.lukio_user_forms_google_iframe_wrapper').each(function() {
                        google.accounts.id.renderButton(
                            this, {
                                theme: "outline",
                                size: "large"
                            }
                        );
                    });

                };
            </script>

            <script src="https://accounts.google.com/gsi/client" async></script>
        <?php
        }

        // print facebook scripts
        $facebook_app_id = Lukio_User_Forms_Options_Class::get_facebook_app_id();
        if ($facebook_app_id) {
        ?>
            <script>
                window.fbAsyncInit = function() {
                    FB.init({
                        appId: <?php echo "'" . $facebook_app_id . "'"; ?>,
                        autoLogAppEvents: true,
                        xfbml: true,
                        version: 'v17.0'
                    });
                };
            </script>
            <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
        <?php
        }
    }

    /**
     * handle ajax request of login with social integration
     * 
     * @author Itai Dotan
     */
    public function handle_integrations_request()
    {
        $response = array();
        try {
            switch (sanitize_text_field($_POST['integration'])) {
                case 'google':
                    require_once LUKIO_USER_FORMS_DIR . 'integrations/google.php';
                    Lukio_User_Forms_Google::handle_request(sanitize_text_field($_POST['token']));
                    break;
                case 'facebook':
                    require_once LUKIO_USER_FORMS_DIR . 'integrations/facebook.php';
                    Lukio_User_Forms_Facebook::handle_request(sanitize_text_field($_POST['access_token']));
                    break;
                default:
                    throw new Exception('Invalid integration');
                    break;
            }

            // when no exception been thrown the user has been set
            $response['success'] = true;
            $response['redirect'] = sanitize_text_field($_POST['redirect_to']);
        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = $e->getMessage();
        }
        echo json_encode($response);
        die;
    }

    /**
     * get the template path.
     * 
     * get template from the active theme when the template was overridden, plugin template file when not.
     * 
     * @param string $template_name name of the file to get the path for
     * @return string full path to the template file
     * 
     * @author Itai Dotan
     */
    public static function get_template_path($template_name)
    {
        $theme_dir = get_stylesheet_directory();
        if (file_exists("$theme_dir/lukio-user-forms/$template_name.php")) {
            return "$theme_dir/lukio-user-forms/$template_name.php";
        }
        return LUKIO_USER_FORMS_DIR . "templates/$template_name.php";
    }

    /**
     * make sure the needed scripts for password strength will be present
     * 
     * @author Itai Dotan
     */
    public static function add_password_strength_core()
    {
        if (wp_doing_ajax()) {
            global $wp_scripts;

            // remove 'jquery' dependency before printing as it is already in the page
            $dependency = $wp_scripts->query('user-profile', 'registered');
            if ($dependency !== false) {
                $array_key = array_search('jquery', $dependency->deps);
                unset($dependency->deps[$array_key]);
            }

            $wp_scripts->print_scripts('user-profile');
        } else {
            wp_enqueue_script('user-profile');
        }
    }

    /**
     * echo password input
     * 
     * @param string $id id for the input
     * @param string $placeholder placeholder for the input
     * @param string $name name of the input, default `pwd`
     * @param bool $strength_msg true to add password strength check, default `false`
     * @param string $extra_class extra class to add to the wrapper, default ``
     * 
     * @author Itai Dotan
     */
    public static function echo_password_input($id, $placeholder, $name = 'pwd', $strength_msg = false, $extra_class = '')
    {
        // when loaded in ajax no need to check that scripts are enabled
        $no_js = wp_doing_ajax() ? '' : ' no_js';
        ?>
        <div class="lukio_user_forms_password_inpout_wrapper<?php echo $extra_class != '' ? ' ' . esc_attr(trim($extra_class)) : ''; ?>">
            <input class="lukio_user_forms_input password<?php echo $no_js;
                                                            echo $strength_msg ? ' strength_check' : ''; ?>" type="password" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" autocomplete="current-password" spellcheck="false">
            <button type="button" class="lukio_user_forms_password_toggle hide_no_js<?php echo $no_js; ?>" aria-label="<?php
                                                                                                                        /* TRANSLATORS: use wordpress defaul. does not need translation */
                                                                                                                        echo esc_attr(__('Show password'));
                                                                                                                        ?>">
                <span class="lukio_user_forms_password_toggle_icon dashicons  dashicons-visibility" aria-hidden="true"></span>
            </button>
        </div>
        <?php
        if ($strength_msg) {
            echo '<div class="lukio_user_forms_password_strength empty"></div>';
        }
    }

    /**
     * echo input error_span
     * 
     * @param string $input input id, add to the error span class for use in selectors
     * @param string $message message to show in the errror
     * 
     * @author Itai Dotan
     */
    public static function echo_input_error_span($input, $message)
    {
        ?>
        <span class="lukio_user_forms_input_error <?php echo esc_attr($input); ?>"><?php echo esc_html($message); ?></span>
    <?php
    }

    /**
     * echo popup close button
     * 
     * @author Itai Dotan
     */
    public static function echo_popup_close_button()
    {
    ?>
        <button class="lukio_user_forms_popup_close dashicons dashicons-no" type="button">

            <span class="screen-reader-text"><?php
                                                /* TRANSLATORS: use wordpress defaul. does not need translation */
                                                echo __('Close');
                                                ?></span>
        </button>
<?php
    }

    /**
     * echo error class when the field is one of the missing fields
     * 
     * @param string $field field name
     * @param array $missing_fields array of missing fields
     * 
     * @author Itai Dotan
     */
    public static function maybe_error_class($field, $missing_fields)
    {
        if (in_array($field, $missing_fields)) {
            echo ' error';
        };
    }
}

new Lukio_User_Forms_Setup();
